<?php

class VkCommerce_Install {
	const DB_VERSION = 'vkcommerce_db_version';

	/**
	 * @var
	 */
	private $version;

	public function __construct() {
		$this->version = VKCOMMERCE_VERSION;

		register_activation_hook( VKCOMMERCE_PLUGIN_FILE, array( $this, 'activate' ) );
		add_action( 'plugins_loaded', array( $this, 'make_tables' ) );
	}

	public function activate() {
		$this->make_tables();

		$this->create_files();
	}

	public function make_tables() {
		if ( get_option( self::DB_VERSION ) == $this->version ) {
			return;
		}

		require_once( ABSPATH . 'wp-admin/includes/upgrade.php' );

		$this->make_products_table();

		update_option( self::DB_VERSION, $this->version );
	}

	private function make_products_table() {
		global $wpdb;

		$table_name = $wpdb->prefix . VKCOMMERCE_TABLE_PRODUCTS;

		$charset_collate = $wpdb->has_cap( 'collation' ) ? $wpdb->get_charset_collate() : '';

		$sql = sprintf( 'CREATE TABLE %s (', $table_name ) . PHP_EOL
			   . 'vkcommerce_id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,' . PHP_EOL
			   . 'group_id varchar(20) NOT NULL,' . PHP_EOL
			   . 'post_id BIGINT UNSIGNED NOT NULL,' . PHP_EOL
			   . 'product_id varchar(20) DEFAULT NULL,' . PHP_EOL
			   . 'main_photo_attachment_id BIGINT UNSIGNED DEFAULT NULL,' . PHP_EOL
			   . 'main_photo_id varchar(20) DEFAULT NULL,' . PHP_EOL
			   . 'main_photo_hash varchar(32) DEFAULT NULL,' . PHP_EOL
			   . 'photos_data varchar(255) DEFAULT NULL,' . PHP_EOL
			   . 'status varchar(20) DEFAULT NULL,' . PHP_EOL
			   . 'config_auto_publishing tinyint(1) DEFAULT NULL,' . PHP_EOL
			   . 'config_category_id varchar(20) DEFAULT NULL,' . PHP_EOL
			   . "date_published datetime DEFAULT NULL," . PHP_EOL
			   . "date_published_gmt datetime DEFAULT NULL," . PHP_EOL
			   . "date_updated datetime DEFAULT NULL," . PHP_EOL
			   . "date_updated_gmt datetime DEFAULT NULL," . PHP_EOL
			   . "date_queued datetime DEFAULT NULL," . PHP_EOL
			   . "date_queued_gmt datetime DEFAULT NULL," . PHP_EOL
			   . 'PRIMARY KEY  (vkcommerce_id),' . PHP_EOL
			   . 'KEY post_id (post_id),' . PHP_EOL
			   . 'KEY main_photo_attachment_id (main_photo_attachment_id)' . PHP_EOL
			   . sprintf( ')%s;', ' ' . $charset_collate );

		dbDelta( $sql );
	}

	private function create_files() {
		$files = array(
			array(
				'base'    => VKCOMMERCE_LOG_DIR,
				'file'    => '.htaccess',
				'content' => 'deny from all',
			),
			array(
				'base'    => VKCOMMERCE_LOG_DIR,
				'file'    => 'index.html',
				'content' => '',
			),
		);

		foreach ( $files as $file ) {
			if ( wp_mkdir_p( $file['base'] ) && ! file_exists( trailingslashit( $file['base'] ) . $file['file'] ) ) {
				$file_handle = @fopen( trailingslashit( $file['base'] ) . $file['file'], 'wb' );
				if ( $file_handle ) {
					fwrite( $file_handle, $file['content'] );
					fclose( $file_handle );
				}
			}
		}
	}
}

return new VkCommerce_Install();
