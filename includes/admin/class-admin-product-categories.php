<?php

class VkCommerce_Admin_Product_Categories {
	/**
	 * @var VkCommerce_Product_Categories_Manager
	 */
	private $categories_manager;

	public function __construct() {
		$this->categories_manager = VkCommerce_Product_Categories_Manager::instance();

		add_action( 'product_cat_add_form_fields', array( $this, 'output_category_field_in_create_form' ) );
		add_action( 'product_cat_edit_form_fields', array( $this, 'output_category_field_in_edit_form' ) );
		add_action( 'created_product_cat', array( $this, 'save_category' ) );
		add_action( 'saved_product_cat', array( $this, 'save_category' ) );

		add_filter( 'manage_edit-product_cat_columns', array( $this, 'add_table_header' ) );
		add_filter( 'manage_product_cat_custom_column', array( $this, 'get_table_column' ), 10, 3 );
	}

	public function output_category_field_in_create_form() {
		$this->output_category_input( '', __DIR__ . '/views/category/html-create-category-field.php' );
	}

	/**
	 * @param WP_Term $category
	 *
	 * @return void
	 */
	public function output_category_field_in_edit_form( $category ) {
		$category_id = $this->categories_manager->get_category_id( $category->term_id );

		$this->output_category_input( null !== $category_id ? $category_id : '', __DIR__ . '/views/category/html-edit-category-field.php' );
	}

	public function save_category( $term_id ) {
		if ( ! isset( $_POST['vkcommerce_product_category_id'] ) ) {
			return;
		}

		$category_id = null;

		if ( 0 < strlen( $_POST['vkcommerce_product_category_id'] ) ) {
			$category_id = (int) $_POST['vkcommerce_product_category_id'];
		}

		$current_category_id = $this->categories_manager->get_category_id( $term_id );

		if ( null !== $category_id ) {
			if ( $category_id !== $current_category_id ) {
				$this->categories_manager->save_category_id( $term_id, $category_id );
			}
		} elseif ( $current_category_id ) {
			$this->categories_manager->delete_category_id( $term_id );
		}
	}

	/**
	 * @param array $headers
	 *
	 * @return array
	 */
	public function add_table_header( $headers ) {
		$headers['vkcommerce_category'] = __( 'VKontakte category', 'vkcommerce' );

		return $headers;
	}

	/**
	 * @param string $html
	 * @param string $column_name
	 * @param int $term_id
	 *
	 * @return string
	 */
	public function get_table_column( $html, $column_name, $term_id ) {
		switch ( $column_name ) {
			case 'vkcommerce_category':
				$category_name = $this->categories_manager->get_category_name_by_term_id( $term_id );

				return $category_name ?: '-';
			default:
				return $html;
		}
	}

	private function output_category_input( $value, $template ) {
		$is_api_ready = VkCommerce_Settings::is_api_ready();
		$options      = array();

		if ( $is_api_ready ) {
			$categories = $this->categories_manager->get_categories_list( true );

			if ( ! $categories ) {
				$warning_message = sprintf(
					__( 'There was a problem getting the list of categories. Please, <a href="%s">verify</a> the integration status.', 'vkcommerce' ),
					VkCommerce_Admin_Settings_Page::get_url( array( 'verify_integration_status' => 1 ) )
				);
			} else {
				$options = array( '' => '' ) + $categories;
			}

		}

		$category_field = array(
			'type'        => VkCommerce_Admin_Form::FIELD_SELECT,
			'name'        => 'vkcommerce_product_category_id',
			'label'       => __( 'VKontakte category', 'vkcommerce' ),
			'value'       => $value,
			'id'        => 'vkcommerce-product-category-id',
			'options'     => $options,
			'description' => sprintf(
				__( 'If the value is specified, then products from this category of the site will be published in the selected VKontakte category. Lear more about <a href="%s">VKontakte categories</a>.', 'vkcommerce' ),
				VkCommerce_Admin_Help_Page::get_url( array( 'section' => 'categories' ) )
			)
		);

		require $template;
	}
}

return new VkCommerce_Admin_Product_Categories();
