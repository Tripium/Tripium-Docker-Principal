<?php


namespace WilokeOptimization\StaticCache\Controllers;


trait TraitPostStatusListener
{
	private string $scheduleStaticSinglePageCache = 'wiloke_perform_static_single_page_cache';
	private string $scheduleStaticPagesInterval   = 'wiloke_perform_static_single_interval';
	private string $optionKey                     = 'wiloke_static_files';
	/**
	 * @var array|string[]
	 */
	private array $aExcludeUrls;

	protected function getAllowedPostTypes(): array
	{
		return $this->getField('allowed_post_types', ['page']);
	}

	protected function isAllowedStaticFiles(string $postType): bool
	{
		return in_array($postType, $this->getAllowedPostTypes());
	}

	private function createKeyFromArray(array $aArgs, $extension = '.json'): string
	{
		$fileName = md5(serialize($aArgs)) . $extension;

		if (isset($aArgs['post_type'])) {
			if (is_array($aArgs['post_type'])) {
				if (count($aArgs['post_type']) === 1) {
					$postType = $aArgs['post_type'][0];
				}
			} else {
				$postType = $aArgs['post_type'];
			}

			if (isset($postType)) {
				$fileName = $postType . '-' . $fileName;
			}
		}

		return $fileName;
	}

	private function getFileSystem()
	{
		if (!function_exists('WP_Filesystem')) {
			require_once(ABSPATH . 'wp-admin/includes/file.php');
		}
		WP_Filesystem();
		global $wp_filesystem;

		return $wp_filesystem;
	}

	protected function getField($field, $default = '', $isUsingDefaultIfEmpty = false)
	{
		$aSettings = get_option($this->optionKey);

		if (!is_array($aSettings) || !isset($aSettings[$field])) {
			return $default;
		}

		if (empty($aSettings[$field]) && $isUsingDefaultIfEmpty) {
			return $default;
		}

		return $aSettings[$field];
	}

	private function mkLocalDirectoryFolder(): bool
	{
		if (!$this->getField('local_directory')) {
			return false;
		}

		if (is_dir($this->getCacheDir())) {
			return true;
		}

		return wp_mkdir_p($this->getCacheDir());
	}

	private function getLocalDirectory(): string
	{
		return trim(trim($this->getField('local_directory')), '/');
	}

	private function isLocalDirectoryExists(): bool
	{
		return !empty($this->getLocalDirectory());
	}

	private function getCacheDir(string $filePath = ''): string
	{
		$localDirectory = $this->getLocalDirectory();
		if (empty($localDirectory)) {
			return '';
		}

		$extension = pathinfo($filePath, PATHINFO_EXTENSION);
		if ($extension == 'html') {
			return trailingslashit(ABSPATH) . trailingslashit($localDirectory) . rtrim($filePath);
		} else {
			return trailingslashit(ABSPATH) . trailingslashit($localDirectory) . trailingslashit(rtrim($filePath, '/'));
		}
	}

	public function postListener()
	{
		add_action(
			'wp_after_insert_post',
			[$this, 'maybeScheduleCacheAfterUpdating'],
			10,
			4
		);

		add_action(
			'before_delete_post',
			[
				$this, 'maybeScheduleCacheBeforeDeleting'
			],
			10,
			2
		);

		add_action(
			'wp_insert_comment',
			[
				$this, 'maybeScheduleParentCacheAfterUpdating'
			],
			10,
			2
		);

		add_action(
			'deleted_comment',
			[
				$this, 'maybeScheduleParentCacheBeforeDeleting'
			],
			10,
			2
		);
	}

	public function maybeScheduleParentCacheAfterUpdating($commentId, $oComment)
	{
		$postId = $oComment->comment_post_ID;
		$oPost = get_post($postId);
		if (!is_wp_error($oPost)) {
			$this->scheduleCache(get_post($postId));
		}
	}

	public function maybeScheduleParentCacheBeforeDeleting($commentId, $oComment)
	{
		$postId = $oComment->comment_post_ID;
		$oPost = get_post($postId);
		if (!is_wp_error($oPost)) {
			$this->scheduleCache(get_post($postId));
		}
	}

	public function maybeScheduleCacheAfterUpdating($postId, $post, $update, $oPostBefore)
	{
		if ($update) {
			if ($oPostBefore->post_status == 'publish') {
				$this->scheduleCache($post);
			}
		} else {
			if ($post->post_status == 'publish') {
				$this->scheduleCache($post);
			}
		}
	}

	public function maybeScheduleCacheBeforeDeleting($postId, $post)
	{
		if ($post->post_status == 'publish') {
			$this->scheduleCache($post);
		}
	}
}
