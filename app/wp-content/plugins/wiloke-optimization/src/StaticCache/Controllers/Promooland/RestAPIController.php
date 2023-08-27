<?php


namespace WilokeOptimization\StaticCache\Controllers\Promooland;


use WilokeOptimization\StaticCache\Controllers\TraitPostStatusListener;

class RestAPIController
{
	private array $aCacheResponse = [];
	use TraitPostStatusListener;

	private string $scheduleClearSearchCacheHook = 'wiloke_optimization_clear_cache';


	public function __construct()
	{
		$this->postListener();

		add_filter('pl/src/Project/Templates/PostQueryHandler/before/query', [$this, 'fetchCacheQuery'], 10, 2);
		add_filter('pl/src/Project/Templates/PostQueryHandler/after/query', [$this, 'makeCacheQuery'], 10, 3);
		add_action(
			$this->scheduleClearSearchCacheHook,
			[
				$this, 'clearSearchListingsCache'
			]
		);

		add_action(
			WILOKE_HOOK_PREFIX . 'StaticCache/Controllers/StaticPageCacheController/updated-general-settings',
			[
				$this, 'mkdirCacheFolder'
			]
		);
	}


	public function clearSearchListingsCache($postId): bool
	{
		if (!$this->isLocalDirectoryExists()) {
			return true;
		}

		$postType = get_post_type($postId);
		$aCacheFiles = glob($this->getSubCacheDir($postType . '*'));
		foreach ($aCacheFiles as $fileDir) {
			unlink($fileDir);
		}

		return true;
	}

	private function scheduleCache($post): bool
	{
		if (wp_is_post_revision($post)) {
			return false;
		}

		$postId = abs($post->ID);

		wp_clear_scheduled_hook(
			$this->scheduleClearSearchCacheHook,
			[
				$postId
			]
		);

		wp_schedule_single_event(
			time() + 60,
			$this->scheduleClearSearchCacheHook,
			[
				$postId
			]
		);

		return true;
	}

	private function getSubCacheDir($path = ''): string
	{
		$dir = $this->getCacheDir('pl-api');

		if (empty($path)) {
			return $dir;
		}

		$dir = trailingslashit($dir);

		return '/' . trim(trailingslashit($dir) . $path, '/');
	}


	public function mkdirCacheFolder()
	{
		wp_mkdir_p($this->getSubCacheDir());
	}

	private function getFilePath($fileName): string
	{
		return trailingslashit($this->getSubCacheDir()) . $fileName;
	}

	private function isFileExists($fileName): bool
	{
		return is_file($this->getFilePath($fileName));
	}

	private function getCacheContent($fileName): ?array
	{
		$content = $this->getFileSystem()->get_contents($this->getFilePath($fileName));

		if (!empty($content)) {
			$aCache = json_decode($content, true);
			$maybeError = json_last_error();

			if ($maybeError === JSON_ERROR_NONE) {
				return $aCache;
			}
		}

		return null;
	}

	public function fetchCacheQuery($aResponse, $aArgs): array
	{
		if (isset($aArgs['post_type'])) {
			$aPostTypes = is_string($aArgs['post_type']) ? [$aArgs['post_type']] : $aArgs['post_type'];
			foreach ($aPostTypes as $postType) {
				if (!$this->isAllowedStaticFiles($postType)) {
					return $aResponse;
				}
			}
		}
		$fileName = $this->createKeyFromArray($aArgs);

		if ($this->isFileExists($fileName)) {
			$content = $this->getCacheContent($fileName);
			if (!empty($content)) {
				$aResponse = $content;
			}
		}

		return $aResponse;
	}

	public function makeCacheQuery(array $aResponse, $aMaxPostsAndPages, $aArgs): array
	{
		$aCache['items'] = $aResponse;
		$aCache = array_merge($aCache, $aMaxPostsAndPages);

		$fileDir = $this->getFilePath($this->createKeyFromArray($aArgs));
		$this->getFileSystem()->put_contents($fileDir, json_encode($aCache), FS_CHMOD_FILE);

		return $aResponse;
	}
}
