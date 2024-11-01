<?php

class VkCommerce_Admin_Meta_Publish_Box {
	/**
	 * @var WC_Product
	 */
	private $product;

	public function __construct( $product ) {
		$this->product = $product;
	}

	public function output() {
		$product_id           = $this->product->get_id();
		$is_published_on_site = 'publish' === $this->product->get_status();
		$is_api_ready         = VkCommerce_Settings::is_api_ready();

		$products_manager = VkCommerce_Products_Manager::instance();
		$vk_product       = $products_manager->get_product( $this->product->get_id() );

		if ( $is_published_on_site ) {
			$no_publish_reasons = array_map(
				function ( $reason_code ) {
					switch ( $reason_code ) {
						case VkCommerce_Products_Manager::NO_PUBLISH_REASON_NOT_SUPPORTED_PRODUCT_TYPE:
							return __( 'product type is not supported', 'vkcommerce' );
						case VkCommerce_Products_Manager::NO_PUBLISH_REASON_NO_MAIN_PHOTO:
							return __( 'product does not have main photo', 'vkcommerce' );
						case VkCommerce_Products_Manager::NO_PUBLISH_REASON_INVALID_MAIN_PHOTO:
							return __( 'product\'s main photo is invalid', 'vkcommerce' );
						case VkCommerce_Products_Manager::NO_PUBLISH_REASON_DESCRIPTION_TOO_SHORT:
							return __( 'product description is too short', 'vkcommerce' );
						case VkCommerce_Products_Manager::NO_PUBLISH_REASON_NO_CATEGORY:
							return __( 'product does not have VKontakte category', 'vkcommerce' );
					}

					return __( 'unknown reason', 'vkcommerce' );
				},
				$products_manager->no_publish_reasons( $this->product )
			);
		}

		require __DIR__ . '/views/html-publish-box.php';
	}
}
