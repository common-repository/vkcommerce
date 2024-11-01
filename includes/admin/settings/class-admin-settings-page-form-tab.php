<?php

abstract class VkCommerce_Admin_Settings_Page_Form_Tab extends VkCommerce_Admin_Page_Tab {
	const SETTINGS_GROUP_TEMPLATE = 'vkcommerce_admin_settings_%s_%s';

	/**
	 * @inerhitDoc
	 */
	protected function get_template( $current_section_id ) {
		return __DIR__ . '/views/html-form-tab.php';
	}

	public function get_settings_group( $current_section_id ) {
		return sprintf( self::SETTINGS_GROUP_TEMPLATE, $this->get_id(), $current_section_id );
	}

	final public function output( $current_section_id ) {
		if ( ! VkCommerce::instance()->is_woocommerce_activated() ) {
			return;
		}

		$settings_group = $this->get_settings_group( $current_section_id );

		require $this->get_template( $current_section_id );
	}

	/**
	 * @param string $section_id
	 *
	 * @return array
	 */
	final public function get_settings_for_section( $section_id = 'default' ) {
		$method_name = "get_settings_for_{$section_id}_section";

		$settings = array();

		if ( method_exists( $this, $method_name ) ) {
			$settings = $this->$method_name();
		}

		return $settings;
	}
}
