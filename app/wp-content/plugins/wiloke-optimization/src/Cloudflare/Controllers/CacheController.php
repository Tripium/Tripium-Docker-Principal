<?php


namespace WilokeOptimization\Cloudflare\Controllers;


use WilokeOptimization\Cloudflare\Models\LogModel;
use WilokeOptimization\Shared\Controllers\OptimizationController;

class CacheController extends OptimizationController
{
	private        $optionKey                     = 'wiloke_cf';
	private static $isPurgingCache                = false;
	private        $aPaths                        = [];
	private string $scheduleStaticSinglePageCache = 'wiloke_perform_static_single_page_cache';

	public function __construct()
	{
		add_action('save_post', [$this, 'purgeCacheAfterSaving']);
		add_action('clean_post_cache', [$this, 'purgeCacheAfterSaving']);
		add_action('before_delete_post', [$this, 'purgeCacheAfterSaving']);
		add_action('wp_trash_post', [$this, 'purgeCacheAfterSaving']);
		add_action('edit_comment', [$this, 'purgeCacheAfterCommentChanged'], 10, 2);
		add_action('delete_comment', [$this, 'purgeCacheAfterCommentChanged'], 10, 2);
		add_action('edit_user_profile_update', [$this, 'purgeCacheAfterProfileUpdated'], 10);
		add_action('wp_update_nav_menu', [$this, 'purgeCacheAfterUpdateNavMenu'], 10);
		add_action('admin_init', [$this, 'testPurgeCache']);
		add_action('wiloke_optimization_check_cache_daily', [$this, 'purgeCacheAfterSaving']);
		add_action(
			'wiloke-optimization/src/Shared/Controllers/OptimizationController/renderOptimizationSettings',
			[$this, 'renderCloudFlareSettings']
		);
		add_action('admin_init', [$this, 'saveConfiguration']);
		add_action('wp_login', [$this, 'purgeDashboardCache']);
		add_action('wp_logout', [$this, 'purgeDashboardCache']);
		add_action('update_option_wiloke_themeoptions', [$this, 'purgeDashboardCache']);
		add_action('wiloke-optimization/purge/cloudflare', [$this, 'purgeDashboardCache']);
		add_action('wiloke-optimization/purge/all_cache', [$this, 'purgeDashboardCache']);
		add_action('wiloke-optimization/purge/current_page', [$this, 'purgeCacheAfterSaving']);

		add_action(
			$this->scheduleStaticSinglePageCache,
			[
				$this, 'purgeSingleStaticCacheWhenScheduleTrigger'
			],
			10,
			2
		);

		add_action('admin_init', [$this, 'createTablesIfNotExists']);
	}

	public function createTablesIfNotExists() {
		if ($this->isOptimizationArea()) {
			// Creating table if not exists
			\WilokeOptimization\Cloudflare\Database\LogTable::createTable();
		}
	}

	public function purgeSingleStaticCacheWhenScheduleTrigger($link, $postId): bool
	{
		return $this->purgeCacheAfterSaving($postId);
	}

	public function saveConfiguration(): bool
	{
		if (!isset($_POST['cf_cache']) || !current_user_can('administrator')) {
			return false;
		}

		$aSettings = $_POST['cf_cache'];

		if (empty($aSettings)) {
			delete_option('nginx_cache');
		} else {
			$aOption['email'] = isset($aSettings['email']) ? sanitize_email(trim($aSettings['email'])) : '';
			$aOption['global_api'] = isset($aSettings['global_api']) ?
				sanitize_text_field(trim($aSettings['global_api'])) :
				'';
			$aOption['purge_mode'] = isset($aSettings['purge_mode']) ?
				sanitize_text_field(trim($aSettings['purge_mode'])) :
				'';
			$aOption['custom_urls'] = isset($aSettings['custom_urls']) ?
				sanitize_text_field(trim($aSettings['custom_urls'])) :
				'';
			update_option($this->optionKey, $aOption);
		}

		return true;
	}

	public function renderCloudFlareSettings()
	{
		include plugin_dir_path(__FILE__) . 'settings-page.php';
	}

	protected function getHTTPHeader(): array
	{
		return [
			'X-Auth-Email' => $this->getField('email'),
			'X-Auth-Key'   => $this->getField('global_api'),
			'Content-Type' => 'application/json'
		];
	}

	protected function getField($field, $default = ''): string
	{
		$aOptions = get_option($this->optionKey);
		return isset($aOptions[$field]) ? $aOptions[$field] : $default;
	}

	public function testPurgeCache(): bool
	{
		if (!isset($_GET['action']) || $_GET['action'] !== 'purge-cf-cache' || !current_user_can('administrator')) {
			return false;
		}

		if (!wp_verify_nonce($_GET['_wpnonce'], 'purge-cf-cache')) {
			return false;
		}

		return $this->purgeEverything();
	}

	public function purgeCacheAfterUpdateNavMenu()
	{
		return $this->purgeCacheAfterSaving();
	}

	public function purgeCacheAfterProfileUpdated()
	{
		return $this->purgeCacheAfterSaving();
	}

	public function purgeCacheAfterCommentChanged($commentId, $aData)
	{
		$aData = is_object($aData) ? get_object_vars($aData) : $aData;
		return $this->purgeCacheAfterSaving($aData['comment_post_ID']);
	}

	public function purgeCacheAfterSaving($postId = ''): bool
	{
		if (self::$isPurgingCache || $this->getField('purge_mode') == 'disable') {
			return false;
		}

		if (!empty($postId) && (wp_is_post_revision($postId) || wp_is_post_autosave($postId))) {
			return false;
		}

		self::$isPurgingCache = true;

		if ($this->getField('purge_mode') === 'purge_all') {
			$status = $this->purgeEverything();
		} else {
			$status = $this->purgePaths($postId);
		}

		self::$isPurgingCache = false;
		return $status;
	}

	private function getDomain(): string
	{
		$aParseUrl = parse_url(home_url('/'));
		return str_replace('www.', '', $aParseUrl['host']);
	}

	private function getZoneId(): string
	{
		$result = wp_remote_get(
			"https://api.cloudflare.com/client/v4/zones",
			[
				'headers' => $this->getHTTPHeader()
			]
		);

		if (is_wp_error($result)) {
			LogModel::write(var_export($result, true));
			return '';
		}

		$aResults = json_decode($result['body'], true);
//		var_export($aResults);die;
		if (isset($aResults['success']) && $aResults['success']) {
			foreach ($aResults['result'] as $aDomain) {
				if ($aDomain['name'] == $this->getDomain()) {
					return $aDomain['id'];
				}
			}
		} else {
			LogModel::write(var_export($aResults, true));
		}

		return '';
	}

	private function purgeEverything(): bool
	{
		LogModel::write('Paths: - entire cache -');
		$aResponse = $this->purgeCache(['purge_everything' => true]);

		if ($aResponse['status'] == 'error') {
			LogModel::write(var_export($aResponse['msg'], true));
			return false;
		} else {
			LogModel::write('All cache have been purged');
			return true;
		}
	}

	public function purgeDashboardCache()
	{
		$this->aPaths = $this->getWilcityDashboardUrl();
		$this->purgePaths();
	}

	private function purgePaths($postId = ''): bool
	{
		$this->aPaths[] = rtrim(home_url(), '/');
		if (!empty($postId)) {
			$this->aPaths[] = rtrim(get_permalink($postId), '/');
		}

		$customUrls = trim($this->getField('custom_urls'));
		if ($customUrls) {
			$aCustomPaths = explode(',', $customUrls);
			foreach ($aCustomPaths as $url) {
				$url = trim($url, '/');
				$url = trim($url);
				$this->aPaths[] = $url;
			}
		}

		if (empty($this->aPaths)) {
			LogModel::write('Nothing to purge');
		}

		$aResponse = $this->purgeCache(['files' => $this->aPaths]);

		if ($aResponse['status'] == 'error') {
			LogModel::write(var_export($aResponse['msg'], true));

			return false;
		} else {
			LogModel::write('Cache Purged');

			return true;
		}
	}

	private function purgeCache(array $aData): array
	{
		$result = wp_remote_post("https://api.cloudflare.com/client/v4/zones/" . $this->getZoneId() . "/purge_cache",
			[
				'body'    => json_encode($aData),
				'headers' => $this->getHTTPHeader()
			]);

		$aResult = json_decode($result['body'], true);
		if (isset($aResult['success']) && $aResult['success']) {
			return [
				'status' => 'success'
			];
		}

		return [
			'status' => 'error',
			'msg'    => $aResult['errors']
		];
	}

	public function isValidConfiguration(): bool
	{
		return empty($this->getField('email')) || empty($this->getField('global_api')) ||
			empty($this->getField('purge_mode'));
	}
}
