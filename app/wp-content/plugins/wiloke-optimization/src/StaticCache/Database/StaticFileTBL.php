<?php


namespace WilokeOptimization\StaticCache\Database;


use WilokeListingTools\AlterTable\TableExists;

class StaticFileTBL
{
	public static string $tblName = 'wiloke_static_file_tbl_version';
	public static string $version = '1.0';
	use TableExists;

	public static function generateTableName(): string
	{
		global $wpdb;
		return $wpdb->prefix . self::$tblName;
	}

	public static function createTable()
	{
		global $wpdb;
		$tblName = self::generateTableName();
		$postTbl = $wpdb->posts;

		$charsetCollect = $wpdb->get_charset_collate();

		// instruction: wil_cache | no_follow
		// status_message: cached | ignore | failed | ''
		$sql = "CREATE TABLE IF NOT EXISTS $tblName(
	  		ID bigint(20) NOT NULL AUTO_INCREMENT,
			post_id bigint(20) UNSIGNED NOT NULL,
			url VARCHAR (200) NOT NULL,
			file_path VARCHAR (200) NOT NULL,
          	instruction VARCHAR(10) NOT NULL DEFAULT 'wil_cache',
          	status_message VARCHAR(50)  NULL,
          	created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
          	updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
			FOREIGN KEY (post_id) REFERENCES $postTbl(ID) ON DELETE CASCADE,
			PRIMARY KEY (ID)
		) $charsetCollect";

		require_once ABSPATH . 'wp-admin/includes/upgrade.php';
		$status = dbDelta($sql);

		update_option(self::$tblName, self::$version);
	}

	public function deleteTable()
	{
		// TODO: Implement deleteTable() method.
	}
}
