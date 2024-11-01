<?php

class VkCommerce_Admin {
	public function __construct() {
		add_action( 'init', array( $this, 'includes' ) );
		add_action( 'load-options.php', array( 'VkCommerce_Admin_Settings_Page', 'register_settings' ) );
	}

	public function includes() {
		require_once __DIR__ . '/class-admin-menus.php';
		require_once __DIR__ . '/class-admin-assets.php';
		require_once __DIR__ . '/class-admin-product-data-tabs.php';
		require_once __DIR__ . '/class-admin-meta-boxes.php';
		require_once __DIR__ . '/class-admin-products.php';
		require_once __DIR__ . '/class-admin-product-categories.php';
		require_once __DIR__ . '/class-admin-ajax-handler.php';
	}

	public static function add_message( $code, $message, $redirect_url = null, $type = 'success' ) {
		add_settings_error(
			'vkcommerce_settings',
			$code,
			$message,
			$type
		);
		set_transient( 'settings_errors', get_settings_errors(), 30 );

		if ( $redirect_url ) {
			wp_safe_redirect( $redirect_url . '&settings-updated=true' );

			exit;
		}
	}

	public static function add_error( $code, $message, $redirect_url = null ) {
		self::add_message( $code, $message, $redirect_url, 'error' );
	}
}

return new VkCommerce_Admin();
