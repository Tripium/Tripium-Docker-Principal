<?php

namespace WooKit\Insight\Subscribers\Models;

use WooKit\Insight\Subscribers\Database\SubscriberStatisticTbl;

class SubscriberStatisticModel
{
	/**
	 * @return int
	 */
	public static function countAll(): int
	{
		global $wpdb;
		return $wpdb->get_var("SELECT COUNT(ID) as total FROM " . SubscriberStatisticTbl::getTblName());
	}

	/**
	 * @param string $postID
	 * @return int
	 */
	public static function countAllWithPostID(string $postID): int
	{
		global $wpdb;
		return $wpdb->get_var($wpdb->prepare("SELECT COUNT(ID) as total FROM " . SubscriberStatisticTbl::getTblName() .
			" WHERE postID=%s", $postID));
	}

	/**
	 * @param string $postID
	 * @param int $limit
	 * @param int $page
	 * @return array
	 */
	public static function getEmailAndCreatedDateAndPagination(
		int    $limit = 1000,
		int    $page = 1,
		string $postID = '',
		array  $aFilter = [],
		string $order = 'DESC'
	): array
	{
		global $wpdb;
		$aRawWhere = [];
		$start = ($page - 1) * $limit;
		if (!empty($postID)) {
			$aRawWhere[] = "postID =" . $wpdb->_real_escape($postID);
		}
		if (!empty($aFilter)) {
			$aRawWhere = array_merge($aRawWhere, $aFilter);
		}
		$where = (!empty($aRawWhere)) ? " Where " . trim(implode(" AND ", $aRawWhere), " AND ") : "";
		$sql = $wpdb->prepare("SELECT postID,email,createdDate as date,info FROM " . SubscriberStatisticTbl::getTblName
			() . " " . $where . " ORDER BY createdDate " . $wpdb->_real_escape($order) . " LIMIT %d,%d", $start,
			$limit);

		$result = $wpdb->get_results($sql, ARRAY_A);
		return !empty($result) ? $result : [];
	}

	/**
	 * @param string $email
	 * @param int $postID
	 * @return bool
	 */
	public static function isEmailExist(string $email, int $postID): bool
	{
		global $wpdb;
		$sql = $wpdb->prepare("SELECT ID FROM " . SubscriberStatisticTbl::getTblName() .
			" WHERE email=%s AND postID=%d", $email, $postID);
		$result = $wpdb->get_var($sql);
		return !empty($result);
	}

	public static function getEmailID(string $email, int $postID): string
	{
		global $wpdb;
		$sql = $wpdb->prepare("SELECT ID FROM " . SubscriberStatisticTbl::getTblName() .
			" WHERE email=%s AND postID=%d", $email, $postID);
		$result = $wpdb->get_var($sql);
		return !empty($result) ? $result : '';
	}

	/**
	 * @param $ID
	 * @return bool
	 */
	public static function isExist($ID): bool
	{
		$result = self::getField($ID, 'ID');
		return !empty($result);
	}

	/**
	 * @param $ID
	 * @param $field
	 * @return int|string
	 */
	public static function getField($ID, $field)
	{
		global $wpdb;
		$field = $wpdb->_real_escape($field);
		$query = $wpdb->get_var($wpdb->prepare("SELECT " . $field . " FROM " . SubscriberStatisticTbl::getTblName() .
			" WHERE ID=%d", $ID));
		return !empty($query) ? $query : 0;
	}

	/**
	 * @param $postID
	 * @param $email
	 * @return int
	 */
	public static function getIDWithPostID($postID, $email): int
	{
		global $wpdb;
		$query = $wpdb->get_var($wpdb->prepare("SELECT ID FROM " . SubscriberStatisticTbl::getTblName() .
			" WHERE postID=%s AND email=%s ", $postID, $email));
		return !empty($query) ? $query : 0;
	}

	/**
	 * @param array{postID: int,userID: int,shopID: int,email:string} $aData
	 * @return int
	 */
	public static function insert(array $aData): int
	{
		global $wpdb;
		$query = $wpdb->insert(
			SubscriberStatisticTbl::getTblName(),
			[
				'postID' => $aData['postID'],
				'userID' => $aData['userID'],
				'email'  => $aData['email'],
				'info'   => $aData['info'],
			],
			[
				'%d',
				'%d',
				'%s',
				'%s'
			]
		);
		return !empty($query) ? $wpdb->insert_id : 0;
	}

	/**
	 * @param @param array{id: int} $aData
	 * @return bool|int
	 */
	public static function delete($id)
	{
		global $wpdb;
		return $wpdb->delete(
			SubscriberStatisticTbl::getTblName(),
			[
				'ID' => $id,
			],
			[
				'%d'
			]
		);
	}
}
