<?php

namespace WilokeOptimization\StaticCache\Controllers;

use RecursiveDirectoryIterator;
use RecursiveIteratorIterator;
use WilokeOptimization\Shared\Controllers\OptimizationController;
use WilokeOptimization\StaticCache\Database\StaticFileTBL;
use WilokeOptimization\StaticCache\Models\StaticFileModel;
use WooCommerce;

class StaticPageCacheController extends OptimizationController
{
	use TraitPostStatusListener;

	const COULD_NOT_WRITE_FILE = 'could_not_write_file';
	const CACHED_FILE          = 'cached';
	const WILL_CACHE           = 'will_cache';
	const NO_FOLLOW            = 'no_follow';
	const COULD_NOT_CRAWL      = 'could_not_crawl';
	private array $aExcludePostTypes
		= [
			'attachment',
			'custom_css',
			'revision',
			'nav_menu_item',
			'customize_changeset',
			'user_request',
			'oembed_cache',
			'wp_block',
			'report',
			'kc-section',
			'listing_plan',
			'promotion',
			'discount',
			'review',
			'event_comment',
			'jp_mem_plan',
			'jp_pay_order',
			'jp_pay_product',
			'feedback'
		];

	protected array $aStaticPageSteps
		= [
			'clear_schedule',
			'clear_db',
			'find_static_pages',
			'perform_generate_static_pages',
			'waiting',
			'almost_done',
			'done'
		];

	public function __construct()
	{
		$this->postListener();

		add_action(
			$this->scheduleStaticSinglePageCache,
			[
				$this, 'performStaticSinglePageCache'
			],
			10,
			3
		);

		add_action(
			'wiloke-optimization/src/Shared/Controllers/OptimizationController/renderOptimizationSettings',
			[$this, 'renderStaticPageCache'],
			1
		);

		add_action(
			'wiloke-optimization/src/WilcityCache/Controllers/settings-page/general_settings',
			[$this, 'renderStaticPageGeneralSettings']
		);

		add_action(
			'wiloke-optimization/src/WilcityCache/Controllers/settings-page/generate_static_files',
			[$this, 'renderStaticFilesSettings'],
			1
		);

		add_action('wp_ajax_generate_static_files', [$this, 'setupGenerateStaticFiles']);
		add_action('wp_ajax_cancel_generate_static_files', [$this, 'cancelGenerateStaticFiles']);
		add_action($this->scheduleStaticPagesInterval, [$this, 'performStaticPages']);
		add_filter('cron_schedules', [$this, 'addEveryFileMinutesToSchedules']);
		add_action('admin_enqueue_scripts', [$this, 'enqueueScripts']);
		add_action('add_meta_boxes', [$this, 'addMetaBoxes']);

		add_action('wiloke-optimization/purge/all_static_pages', [$this, 'purgeAllStaticFiles']);
		add_action('wiloke-optimization/purge/all_cache', [$this, 'purgeAllStaticFiles']);
		add_action('wiloke-optimization/purge/current_page', [$this, 'purgeSinglePost']);
		add_action('admin_init', [$this, 'createTablesIfNotExists']);
	}

	public function createTablesIfNotExists()
	{
		if ($this->isOptimizationArea()) {
			StaticFileTBL::createTable();
		}
	}

	public function addMetaBoxes()
	{
		if (isset($_GET['post'])) {
			add_meta_box(
				'wiloke-generate-static-file',
				__('Create Static Page', 'wiloke-optimization'),
				[$this, 'renderStaticFileCreationBox'],
				[$this->getAllowedPostTypes()],
				'side'
			);
		}
	}

	protected function getDefaultExcludeRules(): ?string
	{
		$aExcludes = [];

		global $woocommerce;
		if ($woocommerce instanceof WooCommerce) {
			$myAccountPageId = get_option('woocommerce_myaccount_page_id');
			if ($myAccountPageId) {
				$aExcludes[] = get_permalink($myAccountPageId);
			}

			$cartUrl = wc_get_cart_url();
			if ($cartUrl) {
				$aExcludes[] = $cartUrl;
			}

			$checkoutUrl = wc_get_checkout_url();
			if ($checkoutUrl) {
				$aExcludes[] = $checkoutUrl;
			}

			$shopUrl = get_permalink( wc_get_page_id( 'shop' ) );
			if ($shopUrl) {
				$aExcludes[] = $shopUrl;
			}
		}

		if (function_exists('dokan')) {
			$aDokanPages = get_option('dokan_pages');

			if (isset($aDokanPages['dashboard']) && !empty($aDokanPages['dashboard'])) {
				$aExcludes[] = get_permalink($aDokanPages['dashboard']);
			}

			if (isset($aDokanPages['my_orders']) && !empty($aDokanPages['my_orders'])) {
				$aExcludes[] = get_permalink($aDokanPages['my_orders']);
			}

			if (isset($aDokanPages['store_listing']) && !empty($aDokanPages['store_listing'])) {
				$aExcludes[] = get_permalink($aDokanPages['store_listing']);
			}
		}


		return implode(
			',',
			apply_filters(
				WILOKE_HOOK_PREFIX . 'src/StaticCache/Controllers/StaticPageController/getDefaultExcludeRules',
				$aExcludes
			)
		);
	}

	public function renderStaticFileCreationBox()
	{
		$aResponse = $this->regenerateStaticFileOnSinglePage();
		if (isset($aResponse['msg'])) {
			?>
            <p><?php echo $aResponse['msg']; ?></p>
			<?php
		}
		$url = add_query_arg(
			[
				'action' => 'edit',
				'post'   => $_GET['post'],
				'route'  => 're-generate-static-file'
			],
			admin_url('post.php')
		);
		?>
        <p><?php esc_html_e('Click on the below button to re-generate Static Page of this page',
				'wiloke-optimization'); ?></p>
        <p style="margin-top: 20px;">
            <a class="button button-primary"
               href="<?php echo esc_url($url) ?>">
				<?php esc_html_e('Re-generate Static Page', 'wiloke-optimization'); ?>
            </a>
        </p>
		<?php
	}

	public function enqueueScripts($hook)
	{
		if (strpos($hook, $this->slug) !== false) {
			wp_enqueue_script(
				'wilcity-static-file',
				plugin_dir_url(__FILE__) . 'assets/script.js',
				['jquery'],
				WILOKE_OPTIMIZATION_VERSION,
				true
			);
		}
	}

	public function addEveryFileMinutesToSchedules($aSchedules)
	{
		$aSchedules['every_five_minutes'] = [
			'interval' => 300,
			'display'  => esc_html__('Every 5 Minutes', 'wiloke-optimization')
		];

		return $aSchedules;
	}

	public function cancelGenerateStaticFiles()
	{
		if (!current_user_can('administrator')) {
			wp_send_json_error(['msg' => esc_html__('You do not have permission to perform this action',
				'wiloke-optimization')]);
		}

		wp_clear_scheduled_hook($this->scheduleStaticPagesInterval);

		wp_send_json_success(['msg' => esc_html__('The action has been cancelled', 'wiloke-optimization')]);
	}

	public function setupGenerateStaticFiles()
	{
		if (!current_user_can('administrator')) {
			wp_send_json_error(['msg' => esc_html__('You do not have permission to perform this action',
				'wiloke-optimization')]);
		}

		$currentTask = isset($_POST['currentTask']) ? $_POST['currentTask'] : 'clear_schedule';
		$currentPosition = array_search($currentTask, $this->aStaticPageSteps);
		$nextTask = $this->aStaticPageSteps[$currentPosition + 1];
		$msg = '';

		switch ($currentTask) {
			case 'clear_schedule':
				wp_clear_scheduled_hook($this->scheduleStaticPagesInterval);
				delete_option('next_find_static_pages');
				delete_option('last_perform_static_page_id');
				$msg = esc_html__('Cleared previous schedule event', 'wiloke-optimization');
				break;
			case 'clear_db':
				$this->clearDB();
				$msg = esc_html__('Cleaned previous data', 'wiloke-optimization');
				break;
			case 'find_static_pages':
				$page = get_option('next_find_static_pages');
				$aResponse = $this->findAndSaveStaticPages(empty($page) ? 1 : abs($page));
				if (!$aResponse['isCompleted']) {
					update_option('next_find_static_pages', $aResponse['nextPage']);
					$nextTask = 'find_static_pages';

					foreach ((array)$aResponse['ids'] as $id) {
						$msg .= sprintf(
							__('Found <a href="%s" target="_blank">%s</a> and saved - %s <br />', 'wiloke-optimize'),
							get_permalink($id),
							get_the_title($id),
							$this->getURLInstruction(get_permalink($id)
							)
						);
					}
				} else {
					if ($page == 1) {
						$msg = esc_html__('We found no static pages', 'wiloke-optimization');
						$nextTask = 'almost_done';
					}
				}
				break;
			case 'perform_generate_static_pages':
				$msg = esc_html__('Generating Static Pages', 'wiloke-optimization');
				if (!wp_next_scheduled($this->scheduleStaticPagesInterval)) {
					$this->performStaticPages();
					wp_schedule_event(time(), 'every_five_minutes', $this->scheduleStaticPagesInterval);
				}
				break;
			case 'almost_done':
				$msg = esc_html__('All pages have been generated to Static Pages successfully', 'wiloke-optimization');
				break;
			default:
				$msg = '';
				if (wp_next_scheduled($this->scheduleStaticPagesInterval)) {
					$nextTask = 'waiting';
					$lastTime = get_option('last_time_check_cached');
					$lastTime = empty($lastTime) ? time() - 10000 : $lastTime;

					$aStaticFilesStatus = StaticFileModel::getStaticPagePagesStatus(
						$lastTime,
						[self::CACHED_FILE, self::COULD_NOT_WRITE_FILE]
					);

					if (!empty($aStaticFilesStatus)) {
						$aLastStatus = end($aStaticFilesStatus);
						update_option('last_time_check_cached', strtotime($aLastStatus['updated_at']));

						foreach ($aStaticFilesStatus as $aStaticFileStatus) {
							$msg .= sprintf(
								__('<a href="%s" target="_blank">%s</a> status: %s <br />', 'wiloke-optimization'),
								get_permalink($aStaticFileStatus['post_id']),
								get_the_title($aStaticFileStatus['post_id']),
								$aStaticFileStatus['status_message']
							);
						}
					}
				}
				break;
		}

		wp_send_json_success(
			[
				'isGeneratingFiles' => $currentTask === 'almost_done',
				'msg'               => $msg,
				'nextTask'          => $nextTask
			]
		);
	}

	private function getExcludeUrls(): array
	{
		if (!empty($this->aExcludeUrls)) {
			return $this->aExcludeUrls;
		}

		$urls = $this->getField('exclude_urls');
		$aParsedUrls = explode(',', $urls);
		$this->aExcludeUrls = array_map(function ($url) {
			return trim($url);
		}, $aParsedUrls);

		return $this->aExcludeUrls;
	}

	private function getPostTypes(): array
	{
		$aPostTypes = get_post_types();
		return array_diff($aPostTypes, $this->aExcludePostTypes);
	}

	private function getURLInstruction($url): string
	{
		$this->getExcludeUrls();

		if (empty($this->aExcludeUrls)) {
			return self::WILL_CACHE;
		}

		foreach ($this->aExcludeUrls as $excludeUrl) {
			if (preg_match('#' . $excludeUrl . '#', $url)) {
				return self::NO_FOLLOW;
			}
		}

		$content = file_get_contents($url);
		if (strpos($content, 'wc-bookings-booking-form') !== false) {
			return self::NO_FOLLOW;
		}

		return self::WILL_CACHE;
	}

	private function getFilePath($url)
	{
		if (trailingslashit($url) == home_url('/')) {
			return 'index.html';
		}

		return str_replace(home_url('/'), '', $url);
	}

	private function mkdirFilePath($filePath): bool
	{
		if ($filePath === 'index.html') {
			return true;
		}

		$filePath = $this->getCacheDir($filePath);
		if (is_dir($filePath)) {
			return true;
		}

		return wp_mkdir_p($filePath);
	}

	private function writeCache($content, $filePath): bool
	{
		if ($filePath == 'index.html') {
			$status = file_put_contents($this->getCacheDir($filePath), $content);
		} else {
			$status = file_put_contents(trailingslashit($this->getCacheDir($filePath)) . 'index.html', $content);
		}

		if (!$status) {
			error_log(sprintf('We could not write cache for %s', $filePath));
		}

		return $status;
	}

	protected function focusReGenerateStaticOfSinglePage($url): array
	{
		if ($this->getURLInstruction($url) !== self::WILL_CACHE) {
			return [
				'status' => 'error',
				'msg'    => esc_html__('We could not generate Static Page because this page is excluded from cache',
					'wiloke-optimization')
			];
		}

		$content = file_get_contents($url);
		if (!$content) {
			return [
				'status' => 'error',
				'msg'    => esc_html__('We could not crawl this page', 'wiloke-optimization')
			];
		}

		$this->mkdirFilePath($this->getFilePath($url));
		$status = $this->writeCache($content, $this->getFilePath($url));
		if (!$status) {
			return [
				'status' => 'error',
				'msg'    => esc_html__('We could not write cache of this page', 'wiloke-optimization')
			];
		}

		return [
			'status' => 'error',
			'msg'    => esc_html__('The Static Page has been created', 'wiloke-optimization')
		];
	}

	protected function deleteStaticFileOfSinglePage($url): array
	{
		$filePath = $this->getFilePath($url);
		return $this->deleteStaticFiles($this->getCacheDir($filePath));
	}

	public function performStaticSinglePageCache($link, $id, $action): array
	{
		$homeUrl = home_url('/');
		if (trailingslashit($link) === $homeUrl) {
			$this->deleteStaticFileOfSinglePage($homeUrl);
			$this->focusReGenerateStaticOfSinglePage($homeUrl);
		}

		$aResponse = $this->deleteStaticFileOfSinglePage($link);
		if ($action == 'create') {
			return $this->focusReGenerateStaticOfSinglePage($link);
		}

		return $aResponse;
	}

	public function regenerateStaticFileOnSinglePage(): array
	{
		if (!current_user_can('administrator')) {
			return [
				'status' => 'success',
				'msg'    => ''
			];
		}

		if (!isset($_GET['route']) || $_GET['route'] !== 're-generate-static-file') {
			return [
				'status' => 'success',
				'msg'    => ''
			];
		}

		if (get_post_status($_GET['post']) !== 'publish') {
			return [
				'status' => 'error',
				'msg'    => esc_html__('We could not generate an un-publish page', 'wiloke-optimization')
			];
		}

		$url = get_permalink($_GET['post']);
		return $this->focusReGenerateStaticOfSinglePage($url);
	}

	/**
	 * @param array{url: string, file_path: string, ID: int, post_id: int} $aInfo
	 * @return string[]
	 */
	protected function performStaticFile(array $aInfo): array
	{
		$status = false;
		$content = file_get_contents($aInfo['url']);
		if (!$content) {
			$msg = self::COULD_NOT_CRAWL;

			error_log(sprintf(esc_html__('Could not crawl %s at %s', 'wiloke-optimization'), get_the_title
			($aInfo['post_id']), date(get_option('date_format') . ' ' .
				get_option('time_format'))));
		} else {
			if ($aInfo['file_path'] !== 'index.html') {
				if ($this->mkdirFilePath($aInfo['file_path'])) {
					$status = $this->writeCache($content, $aInfo['file_path']);
				}
			} else {
				$status = $this->writeCache($content, $aInfo['file_path']);
			}

			if (!$status) {
				$msg = self::COULD_NOT_WRITE_FILE;
				error_log(sprintf(esc_html__('Could not cache %s at %s', 'wiloke-optimization'), get_the_title
				($aInfo['post_id']),
					date(get_option('date_format') . ' ' .
						get_option('time_format'))));
			} else {
				$msg = self::CACHED_FILE;
			}
		}

		if (isset($aInfo['ID'])) {
			StaticFileModel::updateStatusCacheMessage($aInfo['ID'], $msg);
		}

		return [
			'status' => $status ? 'success' : 'error',
			'msg'    => $msg
		];
	}

	/**
	 * Find one-by-one Static Page in the list and then perform Static Page
	 * @param int $lastId
	 * @return array|string[]
	 */
	protected function performStaticFiles(int $lastId): array
	{
		$aPages = StaticFileModel::getCacheByInstruction(self::WILL_CACHE, $lastId);

		if (!empty($aPages)) {
			$msg = '';
			foreach ($aPages as $aPage) {
				$aResponse = $this->performStaticFile($aPage);

				$msg = $aResponse['msg'] . '<br />';
			}

			$aLast = end($aPages);

			return [
				'isCompleted' => false,
				'lastId'      => $aLast['ID'],
				'msg'         => $msg
			];
		}

		return [
			'isCompleted' => true
		];
	}

	public function performStaticPages(): array
	{
		$lastPerformStaticPageID = get_option('last_perform_static_page_id');
		$lastPerformStaticPageID = empty($lastPerformStaticPageID) ? 0 : abs($lastPerformStaticPageID);

		$aResponse = $this->performStaticFiles($lastPerformStaticPageID);

		if ($aResponse['isCompleted']) {
			wp_clear_scheduled_hook($this->scheduleStaticPagesInterval);
		} else {
			update_option('last_perform_static_page_id', $aResponse['lastId']);
		}

		return $aResponse;
	}

	/**
	 * Find and save list of needed Static Pages to db
	 * @param int $paged
	 * @return array|string[]
	 */
	protected function findAndSaveStaticPages(int $paged): array
	{
		global $wpdb;

		$postsTbl = $wpdb->posts;
		$staticFilesTbl = StaticFileTBL::generateTableName();
		$aPostTypes = $this->getAllowedPostTypes();

		$postTypes = "";
		foreach ($aPostTypes as $postType) {
			$postTypes .= '"' . $wpdb->_real_escape($postType) . '",';
		}
		$postTypes = trim($postTypes, ",");

		$aIds = $wpdb->get_results(
			$wpdb->prepare(
				"SELECT posts.ID FROM $postsTbl as posts WHERE posts.ID NOT IN (SELECT $staticFilesTbl.post_id FROM $staticFilesTbl) AND posts.post_status=%s AND posts.post_type IN ($postTypes) ORDER BY posts.ID DESC LIMIT %d",
				'publish', 20
			),
			ARRAY_A
		);

		if (empty($aIds)) {
			return [
				'isCompleted' => true
			];
		}

		$aPostIds = [];
		foreach ($aIds as $aId) {
			$permalink = get_permalink($aId['ID']);
			$aPostIds[] = $aId['ID'];

			StaticFileModel::insert([
				'post_id'        => $aId['ID'],
				'url'            => $permalink,
				'file_path'      => $this->getFilePath($permalink),
				'instruction'    => $this->getURLInstruction($permalink),
				'status_message' => ''
			]);
		}

		return [
			'isCompleted' => false,
			'nextPage'    => $paged + 1,
			'ids'         => $aPostIds
		];
	}

	public function clearDB(): bool
	{
		return StaticFileModel::deleteAll();
	}

	private function getTabUrl($tab): string
	{
		return add_query_arg([
			'tab'  => $tab,
			'page' => $this->slug
		], admin_url('admin.php'));
	}

	private function getStaticSinglePageCacheArgs(\WP_Post $post): array
	{
		return [
			'link'   => get_permalink($post->ID),
			'ID'     => (int)$post->ID,
			'action' => $post->post_status == 'publish' ? 'create' : 'delete'
		];
	}

	/**
	 * Setup a Schedule to prepare for makeing / deleting a Static Page
	 *
	 * @param $post
	 * @return bool
	 */
	private function scheduleCache($post): bool
	{
		if (wp_is_post_revision($post)) {
			return false;
		}

		wp_clear_scheduled_hook(
			$this->scheduleStaticSinglePageCache,
			$this->getStaticSinglePageCacheArgs($post)
		);

		wp_schedule_single_event(
			time() + 60,
			$this->scheduleStaticSinglePageCache,
			$this->getStaticSinglePageCacheArgs($post)
		);

		return true;
	}

	private function saveCacheData()
	{
		if (current_user_can('administrator')) {
			if (isset($_POST['wiloke_static_cache']) && !empty($_POST['wiloke_static_cache'])) {
				$aData = [];
				foreach ($_POST['wiloke_static_cache'] as $key => $val) {
					if (is_array($val)) {
						$val = array_map('sanitize_text_field', $val);
					} else {
						$val = sanitize_text_field($val);
					}
					$aData[sanitize_text_field($key)] = $val;
				}

				update_option($this->optionKey, $aData);

				$mkdirRootFolder = $this->mkLocalDirectoryFolder();

				if (!$mkdirRootFolder) {
					add_settings_error(
						'general_static_page_settings',
						'wiloke_cache_path',
						sprintf(
							esc_html__(
								'Oops! %s could not be created, please recheck write permission',
								'wiloke-optimization'
							),
							$this->getField('local_directory')
						)
					);
				} else {
					do_action(WILOKE_HOOK_PREFIX .
						'StaticCache/Controllers/StaticPageCacheController/updated-general-settings');
				}
			}
		}
	}

	public function purgeAllStaticFiles()
	{
		$this->deleteStaticFiles();
	}

	public function purgeSinglePost($postId)
	{
		$url = get_permalink($postId);
		if ($url) {
			$this->deleteStaticFileOfSinglePage($url);
		}
	}

	protected function deleteStaticFiles($dir = ''): array
	{
		if (!$this->isLocalDirectoryExists()) {
			return [
				'msg'     => esc_html__('The local directory must not empty', 'wiloke-optimization'),
				'success' => true
			];
		}

		$dir = empty($dir) ? ABSPATH . $this->getField('local_directory') : $dir;
		if (is_dir($dir)) {
			$it = new RecursiveDirectoryIterator($dir, RecursiveDirectoryIterator::SKIP_DOTS);
			$aFiles = new RecursiveIteratorIterator($it, RecursiveIteratorIterator::CHILD_FIRST);

			$aErrors = [];
			foreach ($aFiles as $oFile) {
				if ($oFile->isDir()) {
					$status = rmdir($oFile->getRealPath());
					if (!$status) {
						$aErrors[] = sprintf(__('We could not remove %s', 'wiloke-optimization'),
							$oFile->getRealPath());
					}
				} else {
					$status = unlink($oFile->getRealPath());
					if (!$status) {
						$aErrors[] = sprintf(__('We could not remove %s', 'wiloke-optimization'),
							$oFile->getRealPath());
					}
				}
			}

			if (!empty($aErrors)) {
				return [
					'msg'     => implode('<br />', $aErrors),
					'success' => false
				];
			}
		} else if (is_file($dir)) {
			unlink($dir);
		}

		return [
			'msg'     => esc_html__('All files have been removed', 'wiloke-optimization'),
			'success' => true
		];
	}

	public function renderStaticPageCache()
	{
		$this->saveCacheData();
		include plugin_dir_path(__FILE__) . 'settings-page.php';
	}

	public function renderStaticFilesSettings()
	{
		$this->saveCacheData();
		include plugin_dir_path(__FILE__) . 'static-files-creation.php';
	}

	public function renderStaticPageGeneralSettings()
	{
		include plugin_dir_path(__FILE__) . 'general-settings.php';
	}
}
