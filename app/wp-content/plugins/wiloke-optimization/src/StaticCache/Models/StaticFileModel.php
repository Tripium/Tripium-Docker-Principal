<?php


namespace WilokeOptimization\StaticCache\Models;


use WilokeOptimization\StaticCache\Database\StaticFileTBL;

class StaticFileModel
{
	const CACHED_FILE = 'cached';

	public static function deleteAll()
	{
		global $wpdb;

		return $wpdb->query("DELETE FROM " . StaticFileTBL::generateTableName());
	}

	public static function getCacheByInstruction($instruction, $lastId = 0, $limit = 5)
	{
		global $wpdb;

		return $wpdb->get_results(
			$wpdb->prepare(
				"SELECT * FROM " . StaticFileTBL::generateTableName() .
				" WHERE ID > %d AND instruction=%s ORDER BY ID ASC LIMIT %d",
				$lastId, $instruction, $limit
			),
			ARRAY_A
		);
	}

	/**
	 * @param int $lastTimeChecked Timestamp
	 * @param mixed $rawStatusMsg
	 * @param int $limit
	 * @return array|object|null
	 */
	public static function getStaticPagePagesStatus(int $lastTimeChecked, $rawStatusMsg = "cached", $limit = 20)
	{
		global $wpdb;

		$statusMsg = '';
		if (is_string($rawStatusMsg)) {
			$statusMsg = "'" . $wpdb->_real_escape($rawStatusMsg) . "'";
		} else {
			foreach ($rawStatusMsg as $item) {
				$statusMsg .= "'" . $wpdb->_real_escape($item) . "',";
			}

			$statusMsg = trim($statusMsg, ',');
		}

		return $wpdb->get_results(
			$wpdb->prepare(
				"SELECT * FROM " . StaticFileTBL::generateTableName() .
				" WHERE updated_at > %s AND status_message IN (" . $statusMsg . ") ORDER BY ID ASC LIMIT %d",
				date('Y-m-d H:i:s', $lastTimeChecked), $limit
			),
			ARRAY_A
		);
	}

	/**
	 * @param $aInfo {post_id: int, url: string, file_path: string, instruction: 'wil_cache'|'no_follow',
	 * status_message: 'cached'|'ignored'|''}
	 * @return int
	 */
	public static function insert($aInfo): int
	{
		global $wpdb;

		$status = $wpdb->insert(
			StaticFileTBL::generateTableName(),
			[
				'post_id'        => $aInfo['post_id'],
				'url'            => $aInfo['url'],
				'file_path'      => $aInfo['file_path'],
				'instruction'    => $aInfo['instruction'],
				'status_message' => $aInfo['status_message']
			],
			[
				'%d',
				'%s',
				'%s',
				'%s',
				'%s'
			]
		);

		return $status ? $wpdb->insert_id : 0;
	}

	public static function updateStatusCacheMessage($id, $statusMessage): int
	{
		global $wpdb;

		return $wpdb->update(
			StaticFileTBL::generateTableName(),
			[
				'status_message' => $statusMessage
			],
			[
				'ID' => $id
			],
			[
				'%s'
			],
			[
				'%d'
			]
		);
	}
}
