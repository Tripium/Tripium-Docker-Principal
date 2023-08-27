<?php


namespace WilokeOptimization\Nginx\Controllers;


use WilokeOptimization\Shared\Controllers\OptimizationController;
use WP_Error;

class CacheController extends OptimizationController
{

	private $screen    = 'wiloke-tools_page_wiloke-optimization';
	private $optionKey = 'wiloke_nginx_cache';

	public function __construct()
	{
		add_action(
			'wiloke-optimization/src/Shared/Controllers/OptimizationController/renderOptimizationSettings',
			[$this, 'showSettingsPage'],
			100
		);
		add_action('load-' . $this->screen, [$this, 'doAdminActions']);
		add_action('load-' . $this->screen, [$this, 'addSettingsNotices']);
		add_action('admin_init', [$this, 'saveConfiguration']);

		add_action('save_post', [$this, 'purgeCacheAfterPostSaved'], 10, 3);
		add_action('wp_trash_post', [$this, 'purgeCacheAfterPostMovedToTrash'], 10);
		add_action('wp_update_nav_menu', [$this, 'setPurgeCacheSchedule']);
		add_action('switch_theme', [$this, 'setPurgeCacheSchedule']);
		add_action('edit_user_profile_update', [$this, 'setPurgeCacheSchedule']);
		add_action('wiloke_optimization_check_cache_daily', [$this, 'setPurgeCacheSchedule']);
		add_action('update_option_wiloke_themeoptions', [$this, 'setPurgeCacheSchedule']);
		add_action('wp_after_insert_post', [$this, 'purgeCacheAfterPostPublished'], 10, 4);
		add_action('wiloke_optimization_prepare_purge_cache', [$this, 'purgeZoneOnce']);

		add_action('wiloke-optimization/purge/nginx', [$this, 'purgeZoneOnce']);
		add_action('wiloke-optimization/purge/all_cache', [$this, 'purgeZoneOnce']);
		add_action('wiloke-optimization/purge/current_page', [$this, 'purgeZoneOnce']);

//		$this->testFlushCache();
	}

	public function testFlushCache()
	{
		if (isset($_REQUEST['test_cache'])) {
			$aParseUrl = parse_url($_REQUEST['test_cache']);
			$uri = $aParseUrl['scheme'] . $_SERVER['REQUEST_METHOD'] . $aParseUrl['host'] .
				$aParseUrl['path'] . $aParseUrl['query'];
			$md5 = md5($uri);
			$parent = substr($md5, -1);
			$sub = substr($md5, -3, 2);

			if (@unlink('/tmp/wilcitytheme/' . $parent . '/' . $sub . '/' . $md5)) {
				echo 1;
			} else {
				echo 0;
			}
			die;
		}
	}

	private function _setPurgeCacheSchedule()
	{
		wp_clear_scheduled_hook('wiloke_optimization_prepare_purge_cache');
		wp_schedule_single_event(
			time() + 120,
			'wiloke_optimization_prepare_purge_cache'
		);
	}

	public function setPurgeCacheSchedule()
	{
		$this->_setPurgeCacheSchedule();
	}

	public function purgeCacheAfterPostPublished($postId, $post, $update, $oPostBefore)
	{
		if ($update && $post->post_status == 'publish') {
			$this->_setPurgeCacheSchedule();
		}
	}

	public function purgeCacheAfterPostSaved($postId, $post, $isUpdated)
	{
		if (!$isUpdated) {
			if ($post->post_status == 'publish') {
				$this->_setPurgeCacheSchedule();
			}
		}
	}

	public function purgeCacheAfterPostMovedToTrash($postId)
	{
		$this->setPurgeCacheSchedule();
	}

	public function getField($field, $default = '')
	{
		$aOptions = get_option($this->optionKey);
		return isset($aOptions[$field]) ? $aOptions[$field] : $default;
	}

	public function saveConfiguration(): bool
	{
		if (!isset($_POST['nginx_cache']) || !current_user_can('administrator')) {
			return false;
		}

		if (!empty($_POST['nginx_cache'])) {
			$aOption['path'] = isset($_POST['nginx_cache']['path']) ?
				sanitize_text_field($_POST['nginx_cache']['path']) : '';
			$aOption['auto_purge'] = isset($_POST['nginx_cache']['auto_purge']) ?
				abs($_POST['nginx_cache']['auto_purge']) : 0;
			update_option($this->optionKey, $aOption);
		}

		return true;
	}

	public function showSettingsPage()
	{
		require_once plugin_dir_path(__FILE__) . 'settings-page.php';
	}

	public function addSettingsNotices()
	{
		$oPathError = $this->isValidPath();
		if (isset($_GET['message']) && !isset($_GET['settings-updated'])) {
			// show cache purge success message
			if ($_GET['message'] === 'cache-purged') {
				add_settings_error('nginx_cache', 'nginx_cache_path', __('Cache purged.', 'nginx-cache'), 'updated');
			}
			// show cache purge failure message
			if ($_GET['message'] === 'purge-cache-failed') {
				add_settings_error('nginx_cache', 'nginx_cache_path',
					sprintf(__('Cache could not be purged. %s', 'nginx-cache'),
						wptexturize($oPathError->get_error_message())));
			}

		} elseif (is_wp_error($oPathError)) {
			// show cache path problem message
			if ($oPathError->get_error_code() === 'fs') {
				add_settings_error('nginx_cache', 'nginx_cache_path',
					wptexturize($oPathError->get_error_message('fs')));
			}
		}
	}

	public function doAdminActions()
	{
		// purge cache
		if (isset($_GET['action']) && $_GET['action'] === 'purge-nginx-cache' &&
			wp_verify_nonce($_GET['_wpnonce'], 'purge-nginx-cache')) {
			$result = $this->purgeZone();
			wp_safe_redirect(
				add_query_arg(
					[
						'message' => is_wp_error($result) ? 'purge-cache-failed' : 'cache-purged'
					],
					$this->getAdminPage()
				)
			);
			exit;
		}
	}

	public function addPluginActionsLinks($links): array
	{
		// add settings link to plugin actions
		return array_merge(
			['<a href="' . esc_url($this->getAdminPage()) . '">' . __('Settings', 'wiloke-optimization') . '</a>'],
			$links
		);
	}

	private function isValidPath()
	{
		global $wp_filesystem;
		$path = $this->getField('path');

		if (empty($path)) {
			return new WP_Error('empty', __('"Cache Zone Path" is not set.', 'nginx-cache'));
		}

		if ($this->initializeFileSystem()) {

			if (!$wp_filesystem->exists($path)) {
				return new WP_Error('fs', __('"Cache Zone Path" does not exist.', 'nginx-cache'));
			}

			if (!$wp_filesystem->is_dir($path)) {
				return new WP_Error('fs', __('"Cache Zone Path" is not a directory.', 'nginx-cache'));
			}

			$list = $wp_filesystem->dirlist($path, true, true);

			if (is_array($list) && !$this->validateDirList($list)) {
				return new WP_Error('fs',
					__('"Cache Zone Path" does not appear to be a Nginx cache zone directory.', 'nginx-cache'));
			}

			if (!$wp_filesystem->is_writable($path)) {
				return new WP_Error('fs', __('"Cache Zone Path" is not writable.', 'nginx-cache'));
			}

			return true;

		}

		return new WP_Error('fs', __('Filesystem API could not be initialized.', 'nginx-cache'));

	}

	private function validateDirList($aList): bool
	{
		foreach ($aList as $aItem) {
			// abort if file is not a MD5 hash
			if ($aItem['type'] === 'f' && (strlen($aItem['name']) !== 32 || !ctype_xdigit($aItem['name']))) {
				return false;
			}

			// validate subdirectories recursively
			if ($aItem['type'] === 'd' && !$this->validateDirList($aItem['files'])) {
				return false;
			}
		}

		return true;
	}

	public function purgeZoneOnce(): bool
	{
		if (!$this->getField('auto_purge')) {
			return false;
		}

		static $completed = false;
		if (!$completed) {
			$this->purgeZone();
			$completed = true;
		}

		return true;
	}

	private function purgeZone()
	{
		global $wp_filesystem;

		if (!$this->shouldPurge()) {
			return false;
		}

		$path = $this->getField('path');
		$path_error = $this->isValidPath();

		// abort if cache zone path is not valid
		if (is_wp_error($path_error)) {
			return $path_error;
		}

		// delete cache directory (recursively)
		$wp_filesystem->rmdir($path, true);

		// recreate empty cache directory
		$wp_filesystem->mkdir($path);

		do_action('nginx_cache_zone_purged', $path);

		return true;

	}

	private function shouldPurge(): bool
	{
		$post_type = get_post_type();

		if (!$post_type) {
			return true;
		}

		if (!in_array($post_type, (array)apply_filters('nginx_cache_excluded_post_types', []))) {
			return true;
		}

		return false;
	}

	private function initializeFileSystem(): bool
	{
		$path = $this->getField('path');
		// if the cache directory doesn't exist, try to create it
		if (!file_exists($path)) {
			mkdir($path);
		}

		// load WordPress file API?
		if (!function_exists('request_filesystem_credentials')) {
			require_once ABSPATH . 'wp-admin/includes/file.php';
		}

		ob_start();
		$credentials = request_filesystem_credentials('', '', false, $path, null, true);
		ob_end_clean();

		if ($credentials === false) {
			return false;
		}

		if (!WP_Filesystem($credentials, $path, true)) {
			return false;
		}

		return true;
	}
}
