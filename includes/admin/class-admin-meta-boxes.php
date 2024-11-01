<?php

class VkCommerce_Admin_Meta_Boxes {
	const PUBLISH_BOX_ID = 'vkcommerce-publish-meta-box';

	public function __construct() {
		add_action( 'add_meta_boxes_product', array( $this, 'add_boxes' ), 10 );
		// submitpost_box is not proper action to do sorting, but I have no idea about better solution
		add_action( 'submitpost_box', array( $this, 'sort_boxes' ), 10 );
	}

	public function add_boxes() {
		add_meta_box(
			self::PUBLISH_BOX_ID,
			__( 'VKontakte publishing', 'vkcommerce' ),
			array( $this, 'output_publish_box' ),
			'product',
			'side',
			'core'
		);
	}

	/**
	 * @param WP_Post $post
	 *
	 * @return void
	 */
	public function sort_boxes( $post ) {
		if ( 'product' !== $post->post_type ) {
			return;
		};

		global $wp_meta_boxes;

		if ( ! empty( $wp_meta_boxes['product']['side']['core'][ self::PUBLISH_BOX_ID ] )
		     && ! empty( $wp_meta_boxes['product']['side']['core']['submitdiv'] )
		) {
			$boxes = $wp_meta_boxes['product']['side']['core'];

			$vkcommerce_publish_box = $boxes[ self::PUBLISH_BOX_ID ];
			unset( $boxes[ self::PUBLISH_BOX_ID ] );

			$native_publish_box_index = array_search( 'submitdiv', array_keys( $boxes ) );

			$sorted_boxes                         = array_slice( $boxes, 0, ( $native_publish_box_index + 1 ) );
			$sorted_boxes[ self::PUBLISH_BOX_ID ] = $vkcommerce_publish_box;

			$sorted_boxes = array_merge( $sorted_boxes, array_slice( $boxes, $native_publish_box_index + 1 ) );

			$wp_meta_boxes['product']['side']['core'] = $sorted_boxes;
		}
	}

	public function output_publish_box() {
		global $product_object;
		/** @var WC_Product $product_object */

		$box = new VkCommerce_Admin_Meta_Publish_Box( $product_object );
		$box->output();
	}
}

return new VkCommerce_Admin_Meta_Boxes();
