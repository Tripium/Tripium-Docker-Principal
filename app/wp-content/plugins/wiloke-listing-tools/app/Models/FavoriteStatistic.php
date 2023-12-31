<?php

namespace WilokeListingTools\Models;

use WilokeListingTools\AlterTable\AlterTableFavoritesStatistic;
use WilokeListingTools\Framework\Helpers\GetSettings;
use WilokeListingTools\Framework\Helpers\Time;
use WilokeListingTools\Framework\Helpers\General;

class FavoriteStatistic
{
    protected static $tableName;

    public static function tableName()
    {
        global $wpdb;
        self::$tableName = $wpdb->prefix.AlterTableFavoritesStatistic::$tblName;
    }

    public static function compareFavoritesByWeek($authorID, $postID = null)
    {
        $mondayThisWeek = Time::mysqlDate(strtotime('monday this week'));
        $sundayThisWeek = Time::mysqlDate(strtotime('sunday this week'));

        $mondayLastWeek = Time::mysqlDate(strtotime('monday last week'));
        $sundayLastWeek = Time::mysqlDate(strtotime('sunday last week'));

        $totalFavoritesLastWeek =
            self::getTotalFavoritesInRange($authorID, $mondayLastWeek, $sundayLastWeek, $postID);
        $totalFavoritesThisWeek =
            self::getTotalFavoritesInRange($authorID, $mondayThisWeek, $sundayThisWeek, $postID);

        return [
            'current' => $totalFavoritesThisWeek,
            'past'    => $totalFavoritesLastWeek
        ];
    }

    public static function compare($authorID, $postID = null, $compareBy = 'week')
    {
        $totalFavorites = self::getTotalFavoritesOfAuthor($authorID);

        switch ($compareBy) {
            case 'week':
                $aStatistic = self::compareFavoritesByWeek($authorID, $postID);
                break;
        }
        $changing = $aStatistic['current'] - $aStatistic['past'];

        $status = 'up';
        if ($changing == 0) {
            $representColor = '';
        } else if ($changing > 0) {
            $representColor = 'green';
        } else {
            $representColor = 'red';
            $status         = 'down';
        }

        return [
            'total'          => $totalFavorites,
            'totalCurrent'   => $aStatistic['current'], // EG: Total views on this week
            'diff'           => $changing,
            'representColor' => $representColor,
            'status'         => $status
        ];
    }

    public static function countMyFavorites($userID = '')
    {

        $userID = empty($userID) ? get_current_user_id() : $userID;

        $aFavorites = GetSettings::getUserMeta($userID, 'my_favorites');

        if (empty($aFavorites)) {
            return 0;
        }

        $post_types = General::getPostTypeKeys(false, false);

        $query = new \WP_Query([
            'post_type'   => $post_types,
            'post_status' => 'publish',
            'post__in'    => $aFavorites
        ]);

        return absint($query->found_posts);
    }

    public static function countFavorites($postID)
    {
        global $wpdb;
        self::tableName();

        $count = $wpdb->get_var(
            $wpdb->prepare(
                "SELECT sum(countLoved) FROM ".self::$tableName." WHERE objectID=%d",
                $postID
            )
        );

        return absint($count);
    }

    public static function getTotalFavoritesOfAuthorInDay($userID, $day)
    {
        global $wpdb;
        $postsTbl = $wpdb->posts;
        self::tableName();
        $statisticTbl = self::$tableName;

        $total = $wpdb->get_var(
            $wpdb->prepare(
                "SELECT $statisticTbl.countLoved FROM $statisticTbl LEFT JOIN $postsTbl ON ($postsTbl.ID = $statisticTbl.objectID) WHERE $postsTbl.post_status=%s AND $postsTbl.post_author=%d AND $statisticTbl.date=%s",
                'publish', $userID, $day
            )
        );

        return $total ? absint($total) : 0;
    }

    public static function getTotalFavoritesInRange($userID, $start, $end, $postID = null)
    {
        global $wpdb;
        $postsTbl = $wpdb->posts;
        self::tableName();
        $statisticTbl = self::$tableName;

        $query =
            "SELECT $statisticTbl.countLoved FROM $statisticTbl LEFT JOIN $postsTbl ON ($postsTbl.ID = $statisticTbl.objectID) WHERE $postsTbl.post_status=%s AND $postsTbl.post_author=%d AND $statisticTbl.date BETWEEN %s AND %s";

        if (!empty($postID)) {
            $query .= " AND $statisticTbl.objectID=%d";
            $total = $wpdb->get_var(
                $wpdb->prepare(
                    $query,
                    'publish', $userID, $start, $end, $postID
                )
            );
        } else {
            $total = $wpdb->get_var(
                $wpdb->prepare(
                    $query,
                    'publish', $userID, $start, $end
                )
            );
        }

        return $total ? absint($total) : 0;
    }

    public static function getTotalFavoritesOfAuthor($userID)
    {
        global $wpdb;
        $postsTbl = $wpdb->posts;
        self::tableName();
        $statisticTbl = self::$tableName;

        $post_types = \WilokeListingTools\Framework\Helpers\General::getPostTypeKeys(false, false);
        $post_types = implode("','", $post_types);

        $total = $wpdb->get_var(
            $wpdb->prepare(
                "SELECT sum($statisticTbl.countLoved) FROM $statisticTbl LEFT JOIN $postsTbl ON ($postsTbl.ID = $statisticTbl.objectID) WHERE $postsTbl.post_status=%s AND $postsTbl.post_type IN ('".
                $post_types."') AND $postsTbl.post_author=%d",
                'publish', $userID
            )
        );

        return $total ? absint($total) : 0;
    }

    public static function insert($postID)
    {
        global $wpdb;
        self::tableName();

        $status = $wpdb->insert(
            self::$tableName,
            [
                'objectID'   => $postID,
                'countLoved' => 1,
                'date'       => current_time('mysql')
            ],
            [
                '%d',
                '%d',
                '%s'
            ]
        );

        return $status ? $wpdb->insert_id : false;
    }

    public static function update($postID, $plus = true)
    {
        global $wpdb;
        self::tableName();

        $aData = self::isTodayCreated($postID);

        if (!$aData) {
            $insertID = self::insert($postID);
            if ($insertID) {
                do_action('wiloke-listing-tools/notification', get_post_field('post_author', $postID), $insertID,
                    'like', 'add', ['postID' => $postID]);
            }

            return $insertID;
        } else {
            $countLoved = absint($aData['countLoved']);
            $countLoved = $plus ? $countLoved + 1 : $countLoved - 1;
            $countLoved = absint($countLoved);
            $status     = $wpdb->update(
                self::$tableName,
                [
                    'countLoved' => $countLoved
                ],
                [
                    'ID' => $aData['ID']
                ],
                [
                    '%d'
                ],
                [
                    '%d'
                ]
            );

            if ($status && !$plus) {
                do_action('wiloke-listing-tools/notification', get_post_field('post_author', $postID), $aData['ID'],
                    'remove', 'add', []);
            }

            return $status;
        }
    }

    public static function isTodayCreated($postID)
    {
        self::tableName();
        global $wpdb;

        return $wpdb->get_row(
            $wpdb->prepare(
                "SELECT ID, countLoved FROM ".self::$tableName." WHERE objectID=%d AND date=%s",
                $postID, Time::mysqlDate(current_time('timestamp'))
            ),
            ARRAY_A
        );
    }
}
