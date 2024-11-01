<?php

class VkCommerce_Worker {
	const ACTION_PUBLISH = 'vkcommerce_product_publish';
	const ACTION_UNPUBLISH = 'vkcommerce_product_unpublish';
	const ACTION_DELETE = 'vkcommerce_product_delete';

	/**
	 * @var VkCommerce_Worker
	 */
	private static $_instance = null;

	/**
	 * @var VkCommerce_Products_Manager
	 */
	private $products_manager;

	/**
	 * @var VkCommerce_Logger
	 */
	private $logger;

	/**
	 * @return VkCommerce_Worker
	 */
	public static function instance() {
		if ( is_null( self::$_instance ) ) {
			self::$_instance = new self();
		}

		return self::$_instance;
	}

	private function __construct() {
		$this->products_manager = VkCommerce_Products_Manager::instance();
		$this->logger           = VkCommerce_Logger::instance();

		add_action( self::ACTION_PUBLISH, array( $this, 'publish_product' ), 10 );
		add_action( self::ACTION_UNPUBLISH, array( $this, 'unpublish_product' ), 10 );
		add_action( self::ACTION_DELETE, array( $this, 'delete_product' ), 10 );
	}

	/**
	 * @param int $post_id
	 *
	 * @return void
	 */
	public function publish_product( $post_id ) {
		$post = get_post( $post_id );

		try {
			if ( ! $post ) {
				throw new Exception( 'post does not exist' );
			}

			if ( 'product' !== $post->post_type ) {
				throw new Exception( 'post is not a product' );
			}

			$product = wc_get_product( $post_id );

			$is_published = 'publish' === $product->get_status();
			if ( ! $is_published ) {
				throw new Exception( 'product is not published' );
			}

			$is_supported = $this->products_manager->is_supported( $product );
			if ( ! $is_supported ) {
				throw new Exception( 'product is not supported' );
			}

			$vk_product = $this->products_manager->get_product( $post_id );
			if ( $vk_product && $vk_product->is_exported() ) {
				$this->products_manager->mark_outdated( $vk_product );
			}

			if ( $this->products_manager->can_be_published( $product ) ) {
				$vk_product = $this->products_manager->publish_product( $product );

				if ( ! $vk_product ) {
					throw new Exception( 'products manager has not published the product' );
				}

				$vk_product->clear_dates_queued()->save();
			}
		} catch ( Exception $e ) {
			$this->logger->error( sprintf( 'Failed publish product #%s from queue: %s', $post_id, $e->getMessage() ) );
		}
	}

	/**
	 * @param int $post_id
	 *
	 * @return void
	 */
	public function unpublish_product( $post_id ) {
		$post = get_post( $post_id );

		if ( ! $post || 'product' !== $post->post_type ) {
			return;
		}

		try {
			$vk_product = $this->products_manager->get_product( $post_id );

			if ( ! $vk_product ) {
				return;
			}

			if ( $vk_product->is_exported() ) {
				$is_deleted = $this->products_manager->delete_product( $vk_product );

				if ( ! $is_deleted ) {
					$this->products_manager->mark_outdated( $vk_product );

					throw new Exception( 'products manager has not deleted the product' );
				}
			} elseif ( $vk_product->is_queued_to_unpublish() ) {
				$vk_product->set_status( null );
				$vk_product->clear_dates_queued();

				if ( ! $vk_product->save() ) {
					throw new Exception( 'product has not been saved' );
				}
			}
		} catch ( Exception $e ) {
			$this->logger->error( sprintf( 'Failed unpublish product #%s from queue: %s ', $post_id, $e->getMessage() ) );
		}
	}

	/**
	 * @param int $post_id
	 *
	 * @return void
	 */
	public function delete_product( $post_id ) {
		try {
			$vk_product = $this->products_manager->get_product( $post_id );

			if ( ! $vk_product ) {
				return;
			}

			$is_deleted = $this->products_manager->delete_product( $vk_product, true );

			if ( ! $is_deleted ) {
				throw new Exception( 'products manager has not deleted the product' );
			}
		} catch ( Exception $e ) {
			$this->logger->error( sprintf( 'Failed delete product #%s from queue: %s ', $post_id, $e->getMessage() ) );
		}
	}
}
