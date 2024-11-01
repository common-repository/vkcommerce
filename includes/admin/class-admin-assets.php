<?php

class VkCommerce_Admin_Assets {
	const STYLES = 'vkcommerce_admin_styles';
	const SCRIPTS_META_BOXES = 'vkcommerce-admin-product-meta-boxes';

	public function __construct() {
		add_action( 'admin_enqueue_scripts', array( $this, 'admin_styles' ) );
		add_action( 'admin_enqueue_scripts', array( $this, 'admin_scripts' ) );
	}

	public function admin_styles() {
		wp_register_style( self::STYLES, VkCommerce::instance()->plugin_url( 'assets/css/admin.css' ) );

		wp_enqueue_style( self::STYLES );
	}

	public function admin_scripts() {
		$screen    = get_current_screen();
		$screen_id = $screen ? $screen->id : '';

		// Meta boxes.
		if ( in_array( $screen_id, array( 'product', 'edit-product' ) ) ) {
			wp_register_script(
				self::SCRIPTS_META_BOXES,
				VkCommerce::instance()->plugin_url( '/assets/js/admin/meta-boxes-product.js' ),
				array(
					'jquery',
					'wp-i18n',
				)
			);

			wp_enqueue_script( self::SCRIPTS_META_BOXES );
			wp_set_script_translations( self::SCRIPTS_META_BOXES, 'vkcommerce', VKCOMMERCE_TRANSLATIONS_DIR );
		}
	}
}

return new VkCommerce_Admin_Assets();
