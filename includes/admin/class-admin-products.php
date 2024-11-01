<?php

class VkCommerce_Admin_Products {
	/**
	 * @var VkCommerce_Products_Manager
	 */
	private $products_manager;

	/**
	 * @var VkCommerce_Logger
	 */
	private $logger;

	public function __construct() {
		$this->products_manager = VkCommerce_Products_Manager::instance();

		add_action( 'wp_insert_post', array( $this, 'update_product_after_save' ), 10, 2 );

		add_action( 'trashed_post', array( $this, 'enqueue_product_to_unpublish' ) );
		add_action( 'deleted_post', array( $this, 'enqueue_product_to_delete' ), 20, 2 );

		add_filter( 'woocommerce_products_admin_list_table_filters', array( $this, 'add_products_table_filter' ) );
		add_filter( 'posts_clauses', array( $this, 'apply_products_table_filter' ) );
		add_filter( 'manage_product_posts_columns', array( $this, 'add_products_table_column' ), 20 );
		add_filter( 'manage_product_posts_custom_column', array( $this, 'output_products_table_column' ), 10, 2 );

		$this->logger = VkCommerce_Logger::instance();
	}

	public function add_products_table_filter( $filters ) {
		if ( ! isset( $_REQUEST['post_status'] ) || 'trash' !== $_REQUEST['post_status'] ) {
			$filters['vkontakte_status'] = array( $this, 'output_products_table_status_filter' );
		}

		return $filters;
	}

	public function output_products_table_status_filter() {
		$current_status = isset( $_REQUEST['vkontakte'] )
			? sanitize_key( $_REQUEST['vkontakte'] )
			: '';

		$field = array(
			'type'    => VkCommerce_Admin_Form::FIELD_SELECT,
			'name'    => 'vkontakte',
			'value'   => $current_status,
			'options' => array(
				''                    => __( 'Filter by VKontakte status', 'vkcommerce' ),
				'published'           => __( 'Published', 'vkcommerce' ),
				'outdated'            => __( 'Published, but outdated', 'vkcommerce' ),
				'queued_to_publish'   => __( 'Queued to publish', 'vkcommerce' ),
				'queued_to_update'    => __( 'Queued to update', 'vkcommerce' ),
				'queued_to_unpublish' => __( 'Queued to unpublish', 'vkcommerce' ),
				'not_published'       => __( 'Not published', 'vkcommerce' ),
			),
		);

		VkCommerce_Admin_Form::output_field( $field );
	}

	public function apply_products_table_filter( $args ) {
		global $wpdb;

		if ( ! empty( $_REQUEST['vkontakte'] ) ) {
			$args['join'] .= sprintf(
				" LEFT JOIN %s vk_product ON %s.ID = vk_product.post_id ",
				$wpdb->prefix . VKCOMMERCE_TABLE_PRODUCTS,
				$wpdb->posts
			);

			switch ( sanitize_key( $_REQUEST['vkontakte'] ) ) {
				case 'published':
					$args['where'] .= sprintf( " AND vk_product.status = '%s' ", VkCommerce_Product::STATUS_SYNCED );
					break;
				case 'outdated':
					$args['where'] .= sprintf( " AND vk_product.status = '%s' ", VkCommerce_Product::STATUS_OUTDATED );
					break;
				case 'not_published':
					$args['where'] .= " AND vk_product.status IS NULL AND vk_product.product_id IS NULL ";
					break;
				case 'queued_to_publish':
					$args['where'] .= sprintf(
						" AND vk_product.status  = '%s' AND vk_product.product_id IS NULL ",
						VkCommerce_Product::STATUS_QUEUED_TO_PUBLISH
					);
					break;
				case 'queued_to_update':
					$args['where'] .= sprintf(
						" AND vk_product.status  = '%s' AND vk_product.product_id IS NOT NULL ",
						VkCommerce_Product::STATUS_QUEUED_TO_PUBLISH
					);
					break;
				case 'queued_to_unpublish':
					$args['where'] .= sprintf(
						" AND vk_product.status  = '%s' ",
						VkCommerce_Product::STATUS_QUEUED_TO_UNPUBLISH
					);
					break;
			}

		}

		return $args;
	}

	/**
	 * @param int $post_id
	 * @param WP_Post $post
	 *
	 * @return void
	 */
	public function update_product_after_save( $post_id, $post ) {
		if ( 'product' !== $post->post_type ) {
			return;
		}

		try {
			$product    = wc_get_product( $post_id );
			$vk_product = $this->products_manager->get_product( $post_id );

			if ( ! $this->products_manager->is_auto_publish_enabled( $vk_product ?: $product ) ) {
				if ( $vk_product && $vk_product->is_exported() ) {
					$this->products_manager->mark_outdated( $vk_product );
				}

				return;
			}

			$is_published = 'publish' === $product->get_status();
			$is_supported = $this->products_manager->is_supported( $product );

			if ( ( ! $is_published || ! $is_supported ) ) {
				if ( $vk_product && $vk_product->is_exported() ) {
					$this->products_manager->enqueue_to_unpublish( $vk_product );
				}
			} elseif ( $this->products_manager->can_be_published( $product ) ) {
				$this->products_manager->enqueue_to_publish( $vk_product ?: $post_id );
			}
		} catch ( Exception $e ) {
			$this->logger->error( 'Failed update product after saving: ' . $e->getMessage() );
		}
	}

	/**
	 * @param int $post_id
	 *
	 * @return void
	 */
	public function enqueue_product_to_unpublish( $post_id ) {
		try {
			$post = get_post( $post_id );

			if ( 'product' !== $post->post_type ) {
				return;
			}

			$this->products_manager->enqueue_to_unpublish( $post_id );
		} catch ( Exception $e ) {
			$this->logger->error(
				sprintf( 'Failed enqueue product #%s to unpublish after trashing: %s', $post_id, $e->getMessage() )
			);
		}
	}

	/**
	 * @param int $post_id
	 * @param WP_Post $post
	 *
	 * @return void
	 */
	public function enqueue_product_to_delete( $post_id, $post ) {
		try {
			if ( 'product' !== $post->post_type ) {
				return;
			}

			$this->products_manager->enqueue_to_delete( $post_id );
		} catch ( Exception $e ) {
			$this->logger->error(
				sprintf( 'Failed enqueue product #%s to delete: %s', $post_id, $e->getMessage() )
			);
		}
	}

	public function add_products_table_column( $columns ) {
		$last_columns         = array_slice( $columns, - 2 );
		$columns              = array_slice( $columns, 0, count( $columns ) - 2 );
		$columns['vkontakte'] = __( 'VKontakte', 'vkcommerce' );

		return array_merge( $columns, $last_columns );
	}

	public function output_products_table_column( $column, $post_id ) {
		if ( 'vkontakte' !== $column ) {
			return;
		}
		$vk_product = $this->products_manager->get_product( $post_id );

		if ( ! $vk_product || ! $vk_product->get_status() ) {
			echo '<span>' . __( 'Not published', 'vkcommerce' ) . '</span>';
		} elseif ( $vk_product->get_product_id() && $vk_product->is_queued_to_publish() ) {
			echo '<span class="vkcommerce-success">' . __( 'Queued to update', 'vkcommerce' ) . '</span>';
		} elseif ( $vk_product->is_queued_to_publish() ) {
			echo '<span class="vkcommerce-warning">' . __( 'Queued to publish', 'vkcommerce' ) . '</span>';
		} elseif ( $vk_product->is_queued_to_unpublish() ) {
			echo '<span class="vkcommerce-warning">' . __( 'Queued to unpublish', 'vkcommerce' ) . '</span>';
		} elseif ( $vk_product->is_synced() ) {
			echo '<span class="vkcommerce-success">' . __( 'Published', 'vkcommerce' ) . '</span>';
		} elseif ( $vk_product->is_outdated() ) {
			echo '<span class="vkcommerce-warning">' . __( 'Published, but outdated', 'vkcommerce' ) . '</span>';
		}
	}
}

return new VkCommerce_Admin_Products();
