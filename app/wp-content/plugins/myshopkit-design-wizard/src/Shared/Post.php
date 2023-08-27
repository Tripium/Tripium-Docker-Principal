<?php
namespace MyshopKitDesignWizard\Shared;

class Post
{
	public static function countChildren($parentId, $postType, $postStatuses = ['publish', 'pending', 'draft']): int {
		global $wpdb;
		$postTbl = $wpdb->posts;

		if (!is_array($postStatuses)) {
			$postStatuses = [$postStatuses];
		}
		$postStatuses = array_map(function ($item) {
			global $wpdb;
			return $wpdb->_real_escape($item);
		}, $postStatuses);
		$postStatuses = '"' . implode('","', $postStatuses) . '"';

		return (int)$wpdb->get_var(
			$wpdb->prepare(
				"SELECT COUNT(DISTINCT ID) FROM $postTbl WHERE post_parent=%d AND post_type = %s AND post_status IN (" . $postStatuses . ")",
				$parentId, $postType
			)
		);
	}

	public static function filterContent($content)
	{
		$content = str_replace(['<!-- wp:paragraph -->', '<!-- /wp:paragraph -->'], '', $content);
		$content = str_replace(['<p>', '</p>'], "", $content);

		return $content;
	}
}
