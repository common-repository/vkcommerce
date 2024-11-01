<?php

class VkCommerce_Admin_Ajax_Handler {
	/**
	 * @var VkCommerce_Products_Manager
	 */
	private $products_manager;

	public function __construct() {
		$this->products_manager = VkCommerce_Products_Manager::instance();

		add_action( 'wp_ajax_vkcommerce_publish_product', array( $this, 'publish_product' ) );
		add_action( 'wp_ajax_vkcommerce_delete_product', array( $this, 'delete_product' ) );
	}

	public function publish_product() {
		$product = $this->get_product();

		$response = array(
			'message' => __( 'It was not possible to publish the product VKontakte.', 'vkcommerce' ),
		);

		if ( current_user_can( 'publish_posts' ) ) {
			$is_published = $this->products_manager->publish_product( $product );

			if ( $is_published ) {
				unset( $response['message'] );
			}
		}

		$box = new VkCommerce_Admin_Meta_Publish_Box( $product );
		ob_start();
		$box->output();
		$response['html'] = ob_get_clean();

		wp_send_json( $response );
	}

	public function delete_product() {
		$product    = $this->get_product();
		$vk_product = $this->products_manager->get_product( $product->get_id() );

		$response = array();

		if ( $vk_product ) {
			$response['message'] = __( 'It was not possible to unpublish the product VKontakte.', 'vkcommerce' );

			if ( current_user_can( 'delete_published_posts' ) ) {
				$is_deleted = $this->products_manager->delete_product( $vk_product );

				if ( $is_deleted ) {
					unset( $response['message'] );
				}
			}
		}

		$box = new VkCommerce_Admin_Meta_Publish_Box( $product );
		ob_start();
		$box->output();
		$response['html'] = ob_get_clean();

		wp_send_json( $response );
	}

	/**
	 * @return WC_Product
	 *
	 * @throws Exception
	 */
	private function get_product() {
		if ( empty( $_REQUEST['product_id'] ) ) {
			throw new Exception( 'The product does not exist' );
		}

		$product_id = (int) $_REQUEST['product_id'];
		/** @var WC_Product $product */
		$product = wc_get_product( $product_id );

		if ( ! $product ) {
			throw new Exception( sprintf( 'The product #%s does not exist', $product_id ) );
		}

		return $product;
	}
}

return new VkCommerce_Admin_Ajax_Handler();
