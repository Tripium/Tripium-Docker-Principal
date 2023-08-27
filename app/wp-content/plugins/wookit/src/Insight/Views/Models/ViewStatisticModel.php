<?php

namespace WooKit\Insight\Views\Models;

use WooKit\Insight\Views\Database\ViewStatisticTbl;

class ViewStatisticModel
{
    private static string $postType = 'myshopkit_popup';

    public static function getViewsWithPostID(string $postID): int
    {
        global $wpdb;
        $query = $wpdb->get_var($wpdb->prepare("SELECT SUM(total) FROM " . ViewStatisticTbl::getTblName() .
            " WHERE postID=%s", $postID));
        return !empty($query) ? $query : 0;
    }

    /**
     * @param $postID
     * @return bool
     */
    public static function isViewedToday($postID): bool
    {
        global $wpdb;
        $result = $wpdb->get_var($wpdb->prepare("SELECT ID FROM " . ViewStatisticTbl::getTblName() .
            " WHERE (DATE(createdDate) = CURDATE()) AND postID=%d", $postID));
        return !empty($result);
    }

    /**
     * @param $ID : int
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
        $query = $wpdb->get_var($wpdb->prepare("SELECT " . $field . " FROM " . ViewStatisticTbl::getTblName() .
            " WHERE ID=%d", $ID));
        return !empty($query) ? $query : 0;
    }

    /**
     * @param $postID
     * @return int
     */
    public static function getIDWithPostID($postID): int
    {
        global $wpdb;
        $query = $wpdb->get_var($wpdb->prepare("SELECT ID FROM " . ViewStatisticTbl::getTblName() .
            " WHERE postID=%s AND (DATE(createdDate) = CURDATE())", $postID));
        return !empty($query) ? $query : 0;
    }

    /**
     * @param array{postID: int,shopID: int} $aData
     * @return int
     */
    public static function insert(array $aData): int
    {
        global $wpdb;
        $query = $wpdb->insert(
            ViewStatisticTbl::getTblName(),
            [
                'postID' => $aData['postID'],
                'total'  => 1
            ],
            [
                '%d',
                '%d'
            ]
        );
        return !empty($query) ? $wpdb->insert_id : 0;
    }

    /**
     * @param array{postID: int,total: int} $aData
     * @return bool|int
     */
    public static function update(array $aData)
    {
        global $wpdb;
        return $wpdb->update(
            ViewStatisticTbl::getTblName(),
            [
                'total' => $aData['total']
            ],
            [
                'ID' => $aData['ID'],
            ],
            [
                '%d'
            ],
            [
                '%d'
            ]
        );
    }

    /**
     * @param @param array{postID: int,total: int} $aData
     * @return bool|int
     */
    public static function delete($postID)
    {
        global $wpdb;
        return $wpdb->delete(
            ViewStatisticTbl::getTblName(),
            [
                'postID' => $postID,
            ],
            [
                '%d'
            ]
        );
    }
}
