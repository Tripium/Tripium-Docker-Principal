<?php


namespace WilokeOptimization\Cloudflare\Models;


use WilokeOptimization\Cloudflare\Database\LogTable;

class LogModel
{
	public static function write($message)
	{
		global $wpdb;

		return $wpdb->insert(
			LogTable::getTableName(),
			[
				'log' => $message
			],
			[
				'%s'
			]
		);
	}

	public static function get($limit = 100): array
	{
		global $wpdb;

		$aRecords = $wpdb->get_results(
			"SELECT * FROM " . LogTable::getTableName() . " ORDER BY id DESC LIMIT " . abs($limit)
		);

		if (empty($aRecords) || is_wp_error($aRecords)) {
			return [];
		}

		return $aRecords;
	}
}
