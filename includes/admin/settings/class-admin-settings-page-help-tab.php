<?php

class VkCommerce_Admin_Settings_Page_Help_Tab extends VkCommerce_Admin_Help_Page_Settings_Tab {
	/**
	 * @var string
	 */
	protected $id = 'help';

	/**
	 * @inerhitDoc
	 */
	protected function init() {
		$this->label = __( 'Help', 'vkcommerce' );
	}
}
