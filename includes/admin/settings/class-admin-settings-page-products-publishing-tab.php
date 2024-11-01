<?php

class VkCommerce_Admin_Settings_Page_Products_Publishing_Tab extends VkCommerce_Admin_Settings_Page_Form_Tab {
	/**
	 * @var string
	 */
	protected $id = 'products-publishing';

	/**
	 * @inerhitDoc
	 */
	protected function init() {
		$this->label = __( 'Products Publishing', 'vkcommerce' );
	}

	/**
	 * @inerhitDoc
	 */
	protected function get_own_sections() {
		return array( 'default' => __( 'Products Publishing', 'vkcommerce' ) );
	}

	public function get_settings_for_default_section() {
		return array(
			array(
				'name'        => VkCommerce_Settings::PRODUCTS_PUBLISHING_AUTO_PUBLISH,
				'type'        => VkCommerce_Admin_Form::FIELD_CHECKBOX,
				'label'       => __( 'Auto-publishing', 'vkcommerce' ),
				'id'          => 'auto-publishing',
				'label_for'   => 'auto-publishing',
				'description' => __( 'The product will be published VKontakte every time you save.', 'vkcommerce' ),
			),
			array(
				'name'            => VkCommerce_Settings::PRODUCTS_PUBLISHING_DESCRIPTION_TEMPLATE,
				'type'            => VkCommerce_Admin_Form::FIELD_TEXTAREA,
				'label'           => __( 'Product description', 'vkcommerce' ),
				'id'              => 'product-description-template',
				'label_for'       => 'product-description-template',
				'description'     => __( 'Template of VKontakte product description. If empty, the product description will be used.', 'vkcommerce' ),
				'output_function' => array( $this, 'output_product_description' ),
			),
		);
	}

	public function output_product_description( $field ) {
		$current_description_template = VkCommerce_Settings::get_products_publishing_description_template();

		require __DIR__ . '/views/html-product-description.php';
	}
}
