<?php

class VkCommerce_Admin_Help_Page extends VkCommerce_Admin_Page {
	public static function get_slug() {
		return 'vkcommerce-help';
	}

	protected static function get_tabs_classes() {
		return array(
			'VkCommerce_Admin_Help_Page_Guide_Tab',
			'VkCommerce_Admin_Help_Page_Settings_Tab',
		);
	}
}
