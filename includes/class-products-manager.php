<?php

class VkCommerce_Products_Manager {
	const NO_PUBLISH_REASON_NOT_SUPPORTED_PRODUCT_TYPE = 'not_supported_product_type';
	const NO_PUBLISH_REASON_NO_MAIN_PHOTO = 'no_main_photo';
	const NO_PUBLISH_REASON_INVALID_MAIN_PHOTO = 'invalid_main_photo';
	const NO_PUBLISH_REASON_DESCRIPTION_TOO_SHORT = 'description_too_short';
	const NO_PUBLISH_REASON_NO_CATEGORY = 'no_category';

	/**
	 * @var VkCommerce_Products_Manager
	 */
	private static $_instance = null;

	/**
	 * @var VkCommerce_Api_Client
	 */
	private $api_client;

	/**
	 * @var VkCommerce_Product_Categories_Manager
	 */
	private $categories_manager;

	/**
	 * @var VkCommerce_Photos_Manager
	 */
	private $photos_manager;

	/**
	 * @var int
	 */
	private $group_id;

	/**
	 * @var VkCommerce_Logger
	 */
	private $logger;

	/**
	 * @return VkCommerce_Products_Manager
	 */
	public static function instance() {
		if ( is_null( self::$_instance ) ) {
			self::$_instance = new self(
				VkCommerce_Api_Client::instance(),
				VkCommerce_Product_Categories_Manager::instance(),
				VkCommerce_Photos_Manager::instance(),
				VkCommerce_Settings::get_api_group_id()
			);
		}

		return self::$_instance;
	}

	/**
	 * @param VkCommerce_Api_Client $api_client
	 * @param VkCommerce_Product_Categories_Manager $categories_manager
	 * @param VkCommerce_Photos_Manager $photos_manager
	 * @param int $group_id
	 */
	public function __construct( $api_client, $categories_manager, $photos_manager, $group_id ) {
		$this->api_client         = $api_client;
		$this->categories_manager = $categories_manager;
		$this->photos_manager     = $photos_manager;
		$this->group_id           = $group_id;

		$this->logger = VkCommerce_Logger::instance();
	}

	/**
	 * @param WC_Product $product
	 * @param bool $general_only
	 *
	 * @return int|null
	 */
	public function get_category_id( $product, $general_only = false ) {
		if ( ! $general_only ) {
			$vk_product = $this->get_product( $product->get_id() );

			if ( $vk_product && null !== $vk_product->get_config_category_id() ) {
				return $vk_product->get_config_category_id();
			}
		}

		$categories_manager = $this->categories_manager;
		$categories_ids     = $product->get_category_ids();
		$categories         = array_filter(
			get_terms( array( 'taxonomy' => 'product_cat' ) ),
			function ( $category ) use ( $categories_manager, $categories_ids ) {
				/** @var WP_Term $category */
				if ( ! in_array( $category->term_id, $categories_ids ) ) {
					return false;
				}

				return null !== $categories_manager->get_category_id( $category->term_id );
			}
		);

		if ( empty( $categories ) ) {
			return null;
		}

		$deepest_category = null;
		$deep             = 0;

		foreach ( $categories as $category ) {
			$category_deep = $this->get_category_deep( $category );

			if ( $category_deep > $deep ) {
				$deep             = $category_deep;
				$deepest_category = $category;
			}
		}

		return $categories_manager->get_category_id( $deepest_category->term_id );
	}

	/**
	 * @param WP_Term $category
	 * @param int $deep
	 *
	 * @return int
	 */
	private function get_category_deep( $category, $deep = 1 ) {
		if ( ! $category->parent || $category->parent === $category->term_id ) {
			return $deep;
		}

		$parent_category = get_term( $category->parent );

		return $this->get_category_deep( $parent_category, $deep + 1 );
	}

	/**
	 * @param WC_Product $product
	 * @param bool $first
	 *
	 * @return array
	 */
	public function no_publish_reasons( $product, $first = false ) {
		$reasons = array();

		if ( ! $this->is_supported( $product ) ) {
			$reasons[] = static::NO_PUBLISH_REASON_NOT_SUPPORTED_PRODUCT_TYPE;

			if ( $first ) {
				return $reasons;
			}
		}

		if ( ! $product->get_image_id() ) {
			$reasons[] = static::NO_PUBLISH_REASON_NO_MAIN_PHOTO;

			if ( $first ) {
				return $reasons;
			}
		} elseif ( ! $this->photos_manager->is_valid_photo( $product->get_image_id() ) ) {
			$reasons[] = static::NO_PUBLISH_REASON_INVALID_MAIN_PHOTO;

			if ( $first ) {
				return $reasons;
			}
		}

		$product_description = $this->build_product_description( $product );

		if ( mb_strlen( $product_description ) < 10 ) {
			$reasons[] = static::NO_PUBLISH_REASON_DESCRIPTION_TOO_SHORT;

			if ( $first ) {
				return $reasons;
			}
		}

		if ( ! $this->get_category_id( $product ) ) {
			$reasons[] = static::NO_PUBLISH_REASON_NO_CATEGORY;

			if ( $first ) {
				return $reasons;
			}
		}

		return $reasons;
	}

	/**
	 * @param WC_Product $product
	 *
	 * @return bool
	 */
	public function is_supported( $product ) {
		return in_array( $product->get_type(), array( 'simple', 'external' ) );
	}

	/**
	 * @param WC_Product $product
	 *
	 * @return bool
	 */
	public function can_be_published( $product ) {
		return count( $this->no_publish_reasons( $product, true ) ) === 0;
	}

	/**
	 * @param WC_Product $product
	 *
	 * @return VkCommerce_Product|bool
	 */
	public function publish_product( $product ) {
		$vk_product = VkCommerce_Product::get( $this->group_id, $product->get_id() );

		if ( ! $vk_product || ! $vk_product->is_exported() ) {
			return $this->add_product( $product );
		}

		return $this->edit_product( $product, $vk_product );
	}

	/**
	 * @param WC_Product $product
	 * @param string $main_photo_id
	 * @param array $photos_data
	 *
	 * @return array
	 */
	private function prepare_product_request_data( $product, $main_photo_id, $photos_data = [] ) {
		$data = array(
			'owner_id'      => '-' . $this->group_id,
			'name'          => $product->get_name(),
			'description'   => $this->build_product_description( $product ),
			'category_id'   => $this->get_category_id( $product ),
			'price'         => $product->get_regular_price(),
			'main_photo_id' => $main_photo_id,
			'url'           => $product->get_permalink(),
		);

		if ( ! empty( $photos_data ) ) {
			$data['photo_ids'] = implode( ',', array_map( function ( $photo_data ) {
				return $photo_data['id'];
			}, $photos_data ) );
		}

		if ( $product->get_sale_price() ) {
			$data['price']     = $product->get_sale_price();
			$data['old_price'] = $product->get_regular_price();
		}

		if ( $product->get_sku() ) {
			$data['sku'] = $product->get_sku();
		}

		if ( ! $product->is_in_stock() ) {
			$data['deleted'] = 1;
		}

		$weight = $this->get_product_weight( $product );
		if ( ! empty( $weight ) ) {
			$data['weight'] = $weight;
		}

		$width = $this->get_product_dimension( $product->get_width() );
		if ( ! empty( $width ) ) {
			$data['dimension_width'] = $width;
		}

		$height = $this->get_product_dimension( $product->get_height() );
		if ( ! empty( $height ) ) {
			$data['dimension_height'] = $height;
		}

		$length = $this->get_product_dimension( $product->get_length() );
		if ( ! empty( $length ) ) {
			$data['dimension_length'] = $height;
		}

		return $data;
	}

	/**
	 * @param WC_Product $product
	 *
	 * @return string
	 */
	private function build_product_description( $product ) {
		$description       = trim( strip_tags( $product->get_description() ) );
		$short_description = trim( strip_tags( $product->get_short_description() ) );

		$vk_description = $description;
		if ( mb_strlen( $vk_description ) < 10 && mb_strlen( $short_description ) >= 10 ) {
			$vk_description = $short_description;
		}

		$template = VkCommerce_Settings::get_products_publishing_description_template();
		if ( ! empty( $template ) ) {
			$vk_description = trim( strip_tags( str_replace(
				array(
					'[description]',
					'[short_description]',
					'[url]',
				),
				array(
					$description,
					$short_description,
					$product->get_permalink(),
				),
				$template
			) ) );
		}

		return $vk_description;
	}

	/**
	 * @param WC_Product $product
	 *
	 * @return int|null
	 */
	private function get_product_weight( $product ) {
		$weight = $product->get_weight();

		if ( empty( $weight ) ) {
			return null;
		}

		$weight = (float) $weight;

		switch ( get_option( 'woocommerce_weight_unit', 'kg' ) ) {
			case 'g':
				return (int) ceil( $weight );

			case 'kg':
				return (int) ceil( $weight * 1000 );

			case 'oz':
				return (int) ceil( $weight * 28.3495 );

			case 'lbs':
				return (int) ceil( $weight * 453.59237 );
		}

		return null;
	}

	/**
	 * @param string|int|float|null $value
	 *
	 * @return int|null
	 */
	private function get_product_dimension( $value ) {
		if ( empty( $value ) ) {
			return null;
		}

		$value = (float) $value;

		switch ( get_option( 'woocommerce_dimension_unit', 'kg' ) ) {
			case 'mm':
				return (int) ceil( $value );

			case 'cm':
				return (int) ceil( $value * 10 );

			case 'm':
				return (int) ceil( $value * 1000 );

			case 'in':
				return (int) ceil( $value * 25.4 );

			case 'yd':
				return (int) ceil( $value * 914.4 );
		}

		return null;
	}

	/**
	 * @param WC_Product $product
	 *
	 * @return VkCommerce_Product|bool
	 */
	private function add_product( $product ) {
		try {
			if ( ! $product->get_image_id() ) {
				$this->throwException( sprintf( 'no product #%d main photo', $product->get_id() ) );
			}

			$vk_product_main_photo_attachment_id = (int) $product->get_image_id();

			$photo = $this->photos_manager->upload_photo( $vk_product_main_photo_attachment_id, true );
			if ( ! $photo ) {
				$this->throwException( sprintf( 'failed upload of product #%d main photo', $product->get_id() ) );
			}

			$vk_product_main_photo_id   = (string) $photo['id'];
			$vk_product_main_photo_hash = (string) $photo['hash'];

			$photos_data = $this->photos_manager->upload_product_photos( $product->get_gallery_image_ids() );

			$request_data = $this->prepare_product_request_data(
				$product,
				$vk_product_main_photo_id,
				$photos_data
			);

			$data = $this->api_client->make_api_market_request( 'add', $request_data );

			if ( empty( $data['response']['market_item_id'] ) ) {
				$this->throwException( sprintf( 'failed to add product #%d', $product->get_id() ) );
			}
			$vk_product_product_id = (string) $data['response']['market_item_id'];

			$vk_product = $this->create_product( $product->get_id() );
			$vk_product->set_product_id( $vk_product_product_id )
					   ->set_main_photo_data(
						   $vk_product_main_photo_id,
						   $vk_product_main_photo_hash,
						   $vk_product_main_photo_attachment_id
					   )
					   ->set_status( VkCommerce_Product::STATUS_SYNCED );

			if ( ! empty( $photos_data ) ) {
				$vk_product->set_photos_data( $photos_data );
			}

			if ( false === $vk_product->save( true ) ) {
				$this->throwException( sprintf( 'failed to save product #%d to db', $product->get_id() ) );
			};

			return $vk_product;
		} catch ( Exception $e ) {
			$this->logger->error( 'Failed add product: ' . $e->getMessage() );

			return false;
		}
	}

	/**
	 * @param WC_Product $product
	 * @param VkCommerce_Product $vk_product
	 *
	 * @return VkCommerce_Product|bool
	 */
	private function edit_product( $product, $vk_product ) {
		try {
			if ( ! $product->get_image_id() ) {
				$this->throwException( sprintf( 'no product #%d main photo', $product->get_id() ) );
			}

			$main_photo_attachment_id = (int) $product->get_image_id();

			if ( ! $this->photos_manager->update_product_main_photo( $vk_product, $main_photo_attachment_id ) ) {
				$this->throwException( sprintf( 'failed update of product #%d main photo', $product->get_id() ) );
			}

			$photos_data = $this->photos_manager->upload_product_photos(
				$product->get_gallery_image_ids(),
				$vk_product->get_photos_data()
			);

			$request_data            = $this->prepare_product_request_data(
				$product,
				$vk_product->get_main_photo_id(),
				$photos_data
			);
			$request_data['item_id'] = $vk_product->get_product_id();

			$response = $this->api_client->make_api_market_request( 'edit', $request_data );

			if ( empty( $response['response'] ) ) {
				$this->throwException( sprintf(
					'failed to edit product #%s [%s]',
					$product->get_id(),
					$vk_product->get_product_id()
				) );
			}

			$vk_product->set_status( VkCommerce_Product::STATUS_SYNCED );

			if ( ! empty( $photos_data ) ) {
				$vk_product->set_photos_data( $photos_data );
			}

			if ( false === $vk_product->save( true ) ) {
				$this->throwException( sprintf( 'failed to update product #%d in db', $product->get_id() ) );
			};

			return $vk_product;
		} catch ( Exception $e ) {
			$this->logger->error( 'Failed edit product: ' . $e->getMessage() );

			return false;
		}
	}

	/**
	 * @param int $post_id
	 *
	 * @return VkCommerce_Product
	 */
	public function create_product( $post_id ) {
		$vk_product = $this->get_product( $post_id );

		return $vk_product ?: VkCommerce_Product::create( $this->group_id, $post_id );
	}

	/**
	 * @param int $post_id
	 *
	 * @return VkCommerce_Product|null
	 */
	public function get_product( $post_id ) {
		return VkCommerce_Product::get( $this->group_id, $post_id );
	}

	/**
	 * @param VkCommerce_Product $vk_product
	 * @param bool $force
	 *
	 * @return bool
	 */
	public function delete_product( $vk_product, $force = false ) {
		try {
			if ( $vk_product->is_exported() ) {
				$response = $this->api_client->make_api_market_request( 'delete', array(
					'owner_id' => '-' . $this->group_id,
					'item_id'  => $vk_product->get_product_id(),
				) );

				if ( empty( $response['response'] ) ) {
					$this->throwException( sprintf(
						'failed delete product #%s [%s]',
						$vk_product->get_post_id(),
						$vk_product->get_product_id()
					) );
				}

				$vk_product->clear_data();
			}

			return $vk_product->delete( $force );
		} catch ( Exception $e ) {
			$this->logger->error( 'Failed delete product: ' . $e->getMessage() );

			return false;
		}
	}

	/**
	 * @param VkCommerce_Product|WC_Product|null $product
	 *
	 * @return bool
	 */
	public function is_auto_publish_enabled( $product ) {
		if ( $product instanceof WC_Product ) {
			$product = $this->get_product( $product->get_id() );
		}

		if ( $product instanceof VkCommerce_Product ) {
			$config = $product->get_config_auto_publishing();

			if ( null !== $config ) {
				return (bool) $config;
			}
		}

		return VkCommerce_Settings::is_products_auto_publish_enabled();
	}

	/**
	 * @param string $message
	 *
	 * @throws Exception
	 */
	private function throwException( $message ) {
		throw new Exception( sprintf( 'VkCom Products Manager: %s', $message ) );
	}


	/**
	 * @param VkCommerce_Product|int $vk_product
	 *
	 * @return void
	 */
	public function mark_outdated( $vk_product ) {
		if ( is_int( $vk_product ) ) {
			$vk_product = $this->get_product( $vk_product );
		}

		if ( $vk_product instanceof VkCommerce_Product ) {
			$vk_product->set_status( VkCommerce_Product::STATUS_OUTDATED );
			$vk_product->save();
		}
	}

	/**
	 * @param VkCommerce_Product|int $vk_product
	 *
	 * @return void
	 */
	public function enqueue_to_publish( $vk_product ) {
		try {
			if ( is_int( $vk_product ) ) {
				$vk_product = $this->create_product( $vk_product );
			}

			if ( $vk_product instanceof VkCommerce_Product ) {
				$vk_product->set_status( VkCommerce_Product::STATUS_QUEUED_TO_PUBLISH );
				$vk_product->set_date_queued();

				if ( $vk_product->save() ) {
					as_enqueue_async_action( VkCommerce_Worker::ACTION_PUBLISH, array( $vk_product->get_post_id() ) );
				};
			}
		} catch ( Exception $e ) {
			$this->logger->error( 'Failed enqueue product to publish: ' . $e->getMessage() );
		}
	}

	/**
	 * @param VkCommerce_Product|int $vk_product
	 *
	 * @return void
	 */
	public function enqueue_to_unpublish( $vk_product ) {
		try {
			if ( is_int( $vk_product ) ) {
				$vk_product = $this->get_product( $vk_product );
			}

			if ( $vk_product instanceof VkCommerce_Product && $vk_product->is_exported() ) {
				$vk_product->set_status( VkCommerce_Product::STATUS_QUEUED_TO_UNPUBLISH );
				$vk_product->set_date_queued();

				if ( $vk_product->save() ) {
					as_enqueue_async_action( VkCommerce_Worker::ACTION_UNPUBLISH, array( $vk_product->get_post_id() ) );
				}
			}
		} catch ( Exception $e ) {
			$this->logger->error( 'Failed enqueue product to unpublish: ' . $e->getMessage() );
		}
	}

	/**
	 * @param VkCommerce_Product|int $vk_product
	 *
	 * @return void
	 */
	public function enqueue_to_delete( $vk_product ) {
		try {
			if ( is_int( $vk_product ) ) {
				$vk_product = $this->get_product( $vk_product );
			}

			if ( $vk_product instanceof VkCommerce_Product ) {
				as_enqueue_async_action( VkCommerce_Worker::ACTION_DELETE, array( $vk_product->get_post_id() ) );
			}
		} catch ( Exception $e ) {
			$this->logger->error( 'Failed enqueue product to delete: ' . $e->getMessage() );
		}
	}
}
