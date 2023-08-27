<?php

namespace WooKit\Insight\Clicks\Models;

use WooKit\Insight\Clicks\Database\ClickStatisticTbl;

class ClickStatisticModel
{
    private static string $postType = 'myshopkit_popup';

    public static function getTotalWithPost($select, $query): array
    {
        global $wpdb;
        $post = $wpdb->posts;
        $query = "SELECT " . $wpdb->_real_escape($select) . ",SUM(total) as sum FROM ".ClickStatisticTbl::getTblName() . " as clicks JOIN " . $wpdb->_real_escape($post) . " as posts ON posts.ID=clicks.postID WHERE posts.post_type='" . self::$postType . "' AND posts.post_status='publish' AND " . $query . " group by " . $wpdb->_real_escape($select);
        return !empty($wpdb->get_results($query, ARRAY_A)) ? $wpdb->get_results($query, ARRAY_A) :
            [['date' => 0, 'sum' => 0]];
    }

    /**
     * @param $postID
     * @return bool
     */
    public static function isClickedToday( $postID): bool
    {
        global $wpdb;
        $result = $wpdb->get_var($wpdb->prepare("SELECT ID FROM " . ClickStatisticTbl::getTblName() .
            " WHERE (DATE(createdDate) = CURDATE()) AND  postID=%d", $postID));
        return !empty($result);
    }

    /**
     * @param array{ID: int} $aData
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
        $query = $wpdb->get_var($wpdb->prepare("SELECT " . $field . " FROM " . ClickStatisticTbl::getTblName() .
            " WHERE ID=%d", $ID));
        return !empty($query) ? $query : 0;
    }

    /**
     * @param $postID
     * @return int|string
     */
    public static function getIDWithPostID($postID)
    {
        global $wpdb;
        $query = $wpdb->get_var($wpdb->prepare("SELECT ID FROM " . ClickStatisticTbl::getTblName() .
            " WHERE postID=%s AND (DATE(createdDate) = CURDATE())", $postID));
        return !empty($query) ? $query : 0;
    }
    public static function getClicksWithPostID(string $postID):int
    {
        global $wpdb;
        $query = $wpdb->get_var($wpdb->prepare("SELECT SUM(total) FROM " . ClickStatisticTbl::getTblName() .
            " WHERE postID=%s", $postID));
        return !empty($query) ? $query : 0;
    }
    /**
     * @param array{postID: int} $aData
     */
    public static function insert($aData): int
    {
        global $wpdb;
        $query = $wpdb->insert(
            ClickStatisticTbl::getTblName(),
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
    public static function update($aData)
    {
        global $wpdb;
        return $wpdb->update(
            ClickStatisticTbl::getTblName(),
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
            ClickStatisticTbl::getTblName(),
            [
                'postID' => $postID,
            ],
            [
                '%d',
            ]
        );
    }
}
