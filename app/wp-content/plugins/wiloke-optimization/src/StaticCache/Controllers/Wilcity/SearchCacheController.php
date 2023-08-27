<?php


namespace WilokeOptimization\StaticCache\Controllers\Wilcity;


use WilokeListingTools\Framework\Helpers\GetWilokeSubmission;
use WilokeOptimization\Shared\Controllers\OptimizationController;
use WilokeOptimization\StaticCache\Controllers\TraitPostStatusListener;

class SearchCacheController extends OptimizationController
{
	use TraitPostStatusListener;

	private static string $cacheDir                     = 'wilcity-cache';
	private string        $scheduleClearSearchCacheHook = 'wiloke_perform_clear_search_cache';

	protected array $aExcludeWilokeSubmissionPages
		= [
			'become_an_author_page',
			'dashboard_page',
			'add_listing_mode',
			'addlisting',
			'checkout',
			'thankyou',
			'cancel',
		];

	protected array $aExcludeThemeOptionPages
		= [
			'confirmation_page',
			'reset_password_page'
		];

	public function __construct()
	{
		$this->postListener();

		add_filter(
			WILOKE_HOOK_PREFIX . 'src/StaticCache/Controllers/StaticPageController/getDefaultExcludeRules',
			[
				$this, 'addExcludeFromCache'
			]
		);

		add_action(
			'wilcity/wiloke-listing-tools/app/Controllers/SearchFormController/fetchListings/results',
			[$this, 'cacheSearchListings'],
			10,
			2
		);

		add_filter(
			'wilcity/filter/wiloke-listing-tools/app/Controllers/SearchFormController/fetchListings',
			[
				$this, 'getSearchListingsCache'
			],
			10,
			2
		);

		add_action(
			$this->scheduleClearSearchCacheHook,
			[
				$this, 'clearSearchListingsCache'
			]
		);

		add_action(
			WILOKE_HOOK_PREFIX . 'StaticCache/Controllers/StaticPageCacheController/updated-general-settings',
			[
				$this, 'mkSearchCacheFolder'
			]
		);

		add_action('wiloke-optimization/purge/all_static_pages', [$this, 'clearSearchListingsCache']);
		add_action('wiloke-optimization/purge/all_cache', [$this, 'clearSearchListingsCache']);
		add_action('wiloke-optimization/purge/current_page', [$this, 'clearSearchListingsCache']);
	}

	public function addExcludeFromCache($aExcludes)
	{
		foreach ($this->aExcludeWilokeSubmissionPages as $id) {
			$url = GetWilokeSubmission::getField($id, true);
			if ($url) {
				$aExcludes[] = $url;
			}
		}

		foreach ($this->aExcludeThemeOptionPages as $id) {
			$id = \WilokeThemeOptions::getOptionDetail($id);
			if ($id) {
				$url = untrailingslashit(get_permalink($id));
				if ($url) {
					$aExcludes[] = $url;
				}
			}
		}

		return $aExcludes;
	}

	private function scheduleCache($post): bool
	{
		if (wp_is_post_revision($post)) {
			return false;
		}

		wp_clear_scheduled_hook(
			$this->scheduleClearSearchCacheHook
		);

		wp_schedule_single_event(
			time() + 60,
			$this->scheduleClearSearchCacheHook
		);

		return true;
	}

	private function encodeFileName($aRequest, $extension = 'json'): string
	{
		return md5(json_encode($aRequest)) . '.' . $extension;
	}

	private function encodeSearchFileName($aRequest, $extension = 'json'): string
	{
		return 'search-' . md5(json_encode($aRequest)) . '.' . $extension;
	}

	private function addLangToRequest(array $aRequest): array
	{
		global $sitepress;
		if (!empty($sitepress)) {
			$lang = $sitepress->get_current_language();
			$aRequest['lang'] = $lang;
		}

		return $aRequest;
	}

	private function getSearchCacheDir($path = ''): string
	{
		$dir = trailingslashit(trim($this->getCacheDir(), '/')) . 'search-api';

		if (empty($path)) {
			return $dir;
		}

		return trim(trailingslashit($dir) . $path, '/');
	}

	public function mkSearchCacheFolder()
	{
		$status = wp_mkdir_p($this->getSearchCacheDir());
	}

	public function getSearchListingsCache($aCache, $aRequest)
	{
		$filePath = $this->getSearchCacheDir($this->encodeSearchFileName($this->addLangToRequest($aRequest)));
		if (is_file($filePath)) {

			$content = $this->getFileSystem()->get_contents($filePath);

			if (!empty($content)) {
				$aCache = json_decode($content, true);
				$maybeError = json_last_error();

				if ($maybeError === JSON_ERROR_NONE) {
					return $aCache;
				}
			}
		}

		return $aCache;
	}

	public function cacheSearchListings($aResponse, $aRequest): bool
	{
		$fileDir = $this->getSearchCacheDir($this->encodeSearchFileName($this->addLangToRequest($aRequest)));

		$aResponse['cache'] = 'HIT';

		return (bool)$this->getFileSystem()->put_contents($fileDir, json_encode($aResponse), FS_CHMOD_FILE);
	}

	public function clearSearchListingsCache(): bool
	{
		if (!$this->isLocalDirectoryExists()) {
			return false;
		}

		$aCacheFiles = glob($this->getSearchCacheDir('search-*'));
		foreach ($aCacheFiles as $fileDir) {
			unlink($fileDir);
		}

		return true;
	}
}
