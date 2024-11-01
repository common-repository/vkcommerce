<?php

class VkCommerce_Admin_Help_Page_Guide_Tab extends VkCommerce_Admin_Page_Tab {
	const SECTION_INTERFACE = 'interface';
	const SECTION_PUBLISHING_STRATEGIES = 'publishing-strategies';
	const SECTION_CATEGORIES = 'categories';

	/**
	 * @inerhitDoc
	 */
	protected function init() {
		$this->label = __( 'Guide', 'vkcommerce' );
	}

	/**
	 * @inerhitDoc
	 */
	protected function get_own_sections() {
		return array(
			static::SECTION_DEFAULT               => __( 'Overview', 'vkcommerce' ),
			static::SECTION_INTERFACE             => __( 'Interface', 'vkcommerce' ),
			static::SECTION_PUBLISHING_STRATEGIES => __( 'Publishing Strategies', 'vkcommerce' ),
			static::SECTION_CATEGORIES            => __( 'Categories', 'vkcommerce' ),
		);
	}

	/**
	 * @inerhitDoc
	 */
	protected function get_template( $current_section_id ) {
		switch ( $current_section_id ) {
			case static::SECTION_INTERFACE:
				return __DIR__ . '/views/html-guide-interface.php';
			case static::SECTION_PUBLISHING_STRATEGIES:
				return __DIR__ . '/views/html-guide-publishing-strategies.php';
			case static::SECTION_CATEGORIES:
				return __DIR__ . '/views/html-guide-categories.php';
			default:
				return __DIR__ . '/views/html-guide-overview.php';
		}
	}
}
