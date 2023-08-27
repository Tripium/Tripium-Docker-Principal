<?php

namespace WooKit\Insight\Views\Database;

class ViewStatisticTbl
{
    private static string $customerShopsTbl='customer_shops';
    public static string $tblName = WOOKIT_PREFIX . 'view_statistic';
    protected string     $version = '1.0';

    public function __construct()
    {
        add_action('admin_init', [$this, 'createTable']);
    }

    public function createTable()
    {
        global $wpdb;
        $tblName = self::getTblName();
        $postTbl = $wpdb->posts;
        $customerShopsTbl = $wpdb->prefix.self::$customerShopsTbl;
        $charsetCollect = $wpdb->get_charset_collate();

        $sql = "CREATE TABLE IF NOT EXISTS $tblName(
	  		ID bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT,
	  		postID bigint(20) UNSIGNED NOT NULL,
			total bigint(20)  NOT NULL,
			createdDate TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
			FOREIGN KEY (postID) REFERENCES $postTbl(ID) ON DELETE CASCADE,
			PRIMARY KEY (ID)
		) $charsetCollect";

        require_once ABSPATH . 'wp-admin/includes/upgrade.php';
        dbDelta($sql);

        update_option(self::$tblName, $this->version);
    }

    public static function getTblName(): string
    {
        global $wpdb;
        return $wpdb->prefix . self::$tblName;
    }
}
