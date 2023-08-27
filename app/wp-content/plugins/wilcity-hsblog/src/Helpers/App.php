<?php


namespace WilcityHsBlog\Helpers;


class App
{
	public static function getEndpoint($endpoint = '', $isDefaultWP = false): string
	{
		if ($isDefaultWP) {
			return trailingslashit(\WilokeThemeOptions::getOptionDetail('hsblog_base_url')) . 'wp-json/wp/v2/' .
				$endpoint;
		}

		return trailingslashit(\WilokeThemeOptions::getOptionDetail('hsblog_base_url')) . 'wp-json/hsblog/v1/' . $endpoint;
	}

	public static function fetchPost($postId): array
	{
		$endpoint = trailingslashit(\WilokeThemeOptions::getOptionDetail('hsblog_base_url')) .
			trailingslashit(WILCITY_HSBLOG_NAMESPACE) .
			trailingslashit(WILCITY_HSBLOG_WILCITY_ENDPOINT) . 'posts/' . $postId;
		$rawResponse = wp_remote_get($endpoint);

		$aResults = [
			'status' => 'error',
			'msg'    => esc_html__('There is no posts', 'wilcity-mobile-app')
		];

		if (!empty($rawResponse) && !is_wp_error($rawResponse)) {
			$aResults = json_decode(wp_remote_retrieve_body($rawResponse), true);
		}

		return empty($aResults) ? [] : $aResults;
	}

	public static function fetchPosts($aArgs): array
	{
		$endpoint = trailingslashit(\WilokeThemeOptions::getOptionDetail('hsblog_base_url')) .
			trailingslashit(WILCITY_HSBLOG_NAMESPACE) .
			trailingslashit(WILCITY_HSBLOG_WILCITY_ENDPOINT) . 'articles';

		$aArgs = wp_parse_args($aArgs, [
			'paged'          => 1,
			'posts_per_page' => 10
		]);

		$rawResponse = wp_remote_get(
			$endpoint,
			[
				'body' => $aArgs
			]
		);

		$aResults = [
			'status' => 'error',
			'msg'    => esc_html__('There is no posts', 'wilcity-mobile-app')
		];

		if (!empty($rawResponse) && !is_wp_error($rawResponse)) {
			$aResults = json_decode(wp_remote_retrieve_body($rawResponse), true);
		}

		return empty($aResults) ? [] : $aResults;
	}
}
