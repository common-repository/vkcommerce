<?php

class VkCommerce_Admin_Product_Data_Tabs {
	/**
	 * @var VkCommerce_Products_Manager
	 */
	private $products_manager;

	/**
	 * @var VkCommerce_Product_Categories_Manager
	 */
	private $categories_manager;

	public function __construct() {
		$this->products_manager   = VkCommerce_Products_Manager::instance();
		$this->categories_manager = VkCommerce_Product_Categories_Manager::instance();

		add_filter( 'woocommerce_product_data_tabs', array( $this, 'add_data_tabs' ) );
		add_action( 'woocommerce_product_data_panels', array( $this, 'output_data_panel' ) );
		add_action( 'woocommerce_process_product_meta', array( $this, 'save_product_settings' ), 10 );
	}

	public function add_data_tabs( $tabs ) {
		$class = array( 'show_if_simple', 'show_if_external' );

		if ( ! VkCommerce_Settings::is_api_ready() ) {
			$class[] = 'vkcommerce-warning';
		}

		$tabs['vkontakte'] = array(
			'label'    => __( 'VKontakte', 'vkcommerce' ),
			'target'   => 'vkontakte_product_data',
			'class'    => $class,
			'priority' => 100,
		);

		return $tabs;
	}

	public function output_data_panel() {
		global $product_object;
		/** @var WC_Product $product_object */

		$is_api_ready = VkCommerce_Settings::is_api_ready();

		if ( $is_api_ready ) {
			$vk_product = $this->products_manager->get_product( $product_object->get_id() );

			$auto_publishing_field = $this->get_auto_publishing_field( $vk_product );

			$categories = $this->categories_manager->get_categories_list( true );
			if ( ! $categories ) {
				$categories_warning_message = sprintf(
					__( 'There was a problem getting the list of categories. Please, <a href="%s">verify</a> the integration status.', 'vkcommerce' ),
					VkCommerce_Admin_Settings_Page::get_url( array( 'verify_integration_status' => 1 ) )
				);
			}

			$category_field = $this->get_category_field( $vk_product, $categories, $product_object );
		}

		require 'views/html-product-data-panel.php';
	}

	/**
	 * @param VkCommerce_Product $vk_product
	 *
	 * @return array
	 */
	private function get_auto_publishing_field( $vk_product ) {
		$custom_value    = $vk_product ? $vk_product->get_config_auto_publishing() : null;
		$general_value   = (int) VkCommerce_Settings::is_products_auto_publish_enabled();
		$is_custom_value = null !== $custom_value;

		$attributes = array(
			'data-general-value' => $general_value,
		);

		if ( ! $is_custom_value ) {
			$attributes['disabled'] = 'disabled';
		}

		return array(
			'no_option'        => true,
			'name'             => 'vkcommerce_product_auto_publishing',
			'type'             => VkCommerce_Admin_Form::FIELD_SELECT,
			'options'          => array(
				__( 'publishing disabled', 'vkcommerce' ),
				__( 'publishing enabled', 'vkcommerce' ),
			),
			'value'            => $is_custom_value ? $custom_value : $general_value,
			'id'               => 'product-config-auto-publishing',
			'is_general_value' => ! $is_custom_value,
			'attributes'       => $attributes,
		);
	}

	/**
	 * @param VkCommerce_Product $vk_product
	 * @param array $categories
	 * @param WC_Product $product
	 *
	 * @return array
	 */
	private function get_category_field( $vk_product, $categories, $product ) {
		$options = array( '' => __( 'category is not defined', 'vkcommerce' ) );

		if ( $categories && is_array( $categories ) ) {
			$options = $options + $categories;
		}

		$custom_value    = $vk_product ? $vk_product->get_config_category_id() : null;
		$general_value   = $this->products_manager->get_category_id( $product, true ) ?: '';
		$is_custom_value = null !== $custom_value;

		$attributes = array(
			'data-general-value' => $general_value,
		);

		if ( ! $is_custom_value ) {
			$attributes['disabled'] = 'disabled';
		}

		return array(
			'no_option'        => true,
			'name'             => 'vkcommerce_product_category_id',
			'type'             => VkCommerce_Admin_Form::FIELD_SELECT,
			'options'          => $options,
			'attributes'       => $attributes,
			'value'            => $is_custom_value ? $custom_value : $general_value,
			'is_general_value' => ! $is_custom_value,
			'id'               => 'product-config-category-id',
		);
	}

	/**
	 * @param int $post_id
	 *
	 * @return void
	 */
	public function save_product_settings( $post_id ) {
		$settings_keys   = array(
			'vkcommerce_product_auto_publishing',
			'vkcommerce_product_category_id',
		);
		$vk_product_data = array();
		$vk_product      = $this->products_manager->get_product( $post_id );

		foreach ( $settings_keys as $key ) {
			if ( isset( $_REQUEST[ $key ] ) ) {
				switch ( $key ) {
					case 'vkcommerce_product_auto_publishing':
						$vk_product_data[ $key ] = (int) $_REQUEST[ $key ];

						break;
					case 'vkcommerce_product_category_id':
						$vk_product_data[ $key ] = is_numeric( $_REQUEST[ $key ] ) ? (int) $_REQUEST[ $key ] : '';

						break;
				}
			}
		}

		if ( ! empty( $vk_product_data ) ) {
			if ( ! $vk_product ) {
				$vk_product = $this->products_manager->create_product( $post_id );
			}

			foreach ( $settings_keys as $key ) {
				$value = isset( $vk_product_data[ $key ] ) && '' !== $vk_product_data[ $key ]
					? $vk_product_data[ $key ]
					: null;

				switch ( $key ) {
					case 'vkcommerce_product_auto_publishing':
						$vk_product->set_config_auto_publishing( $value );

						break;
					case 'vkcommerce_product_category_id':
						$vk_product->set_config_category_id( $value );

						break;
				}
			}

			$vk_product->save();
		} elseif ( $vk_product ) {
			if ( ! $vk_product->is_exported() ) {
				$vk_product->delete( true );
			} else {
				$vk_product->clear_config()->save();
			}
		}
	}
}

return new VkCommerce_Admin_Product_Data_Tabs();
