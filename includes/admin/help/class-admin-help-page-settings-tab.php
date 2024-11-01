<?php

class VkCommerce_Admin_Help_Page_Settings_Tab extends VkCommerce_Admin_Page_Tab {
	const SECTION_PRODUCTS_PUBLISHING = 'products-publishing';

	/**
	 * @var string
	 */
	protected $id = 'settings';

	/**
	 * @inerhitDoc
	 */
	protected function init() {
		$this->label = __( 'Settings', 'vkcommerce' );
	}

	/**
	 * @inerhitDoc
	 */
	protected function get_own_sections() {
		return array(
			static::SECTION_DEFAULT             => __( 'API Integration', 'vkcommerce' ),
			static::SECTION_PRODUCTS_PUBLISHING => __( 'Products Publishing', 'vkcommerce' ),
		);
	}

	/**
	 * @inerhitDoc
	 */
	protected function get_template( $current_section_id ) {
		switch ( $current_section_id ) {
			case static::SECTION_PRODUCTS_PUBLISHING:
				return __DIR__ . '/views/html-settings-products-publishing.php';
			default:
				return __DIR__ . '/views/html-settings-api-integration.php';
		}
	}
}
