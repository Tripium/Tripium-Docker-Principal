<?php


namespace WilokeOptimization\Cloudflare\Database;


class LogTable
{
	private static $version = '1.0';
	private static $tblName = 'wiloke_cachepurger_log_tbl_version';

	public static function getTableName(): string
	{
		global $wpdb;

		return $wpdb->prefix . self::$tblName;
	}

	public static function createTable()
	{
		global $wpdb;

		$tblName = self::getTableName();

		$charset_collate = $wpdb->get_charset_collate();

		$sql = "CREATE TABLE IF NOT EXISTS $tblName (
		      id bigint(20) NOT NULL AUTO_INCREMENT,
		      created_at datetime DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP NOT NULL,
		      log text NOT NULL,
		      PRIMARY KEY  (id)
	       ) $charset_collate;";

		require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
		dbDelta($sql);

		update_option("wiloke_cachepurger_log_version", self::$version);
	}
}
