<?php

class VkCommerce_Product_Categories_Manager {
	/**
	 * @var VkCommerce_Product_Categories_Manager
	 */
	private static $_instance = null;

	/**
	 * @var VkCommerce_Api_Client
	 */
	private $api_client;

	/**
	 * @var int
	 */
	private $group_id;

	/**
	 * @var array
	 */
	private $raw_categories;

	/**
	 * @var array
	 */
	private $categories;

	/**
	 * @return VkCommerce_Product_Categories_Manager
	 */
	public static function instance() {
		if ( is_null( self::$_instance ) ) {
			self::$_instance = new self(
				VkCommerce_Api_Client::instance(),
				VkCommerce_Settings::get_api_group_id()
			);
		}

		return self::$_instance;
	}

	/**
	 * @param VkCommerce_Api_Client $api_client
	 * @param int $group_id
	 */
	public function __construct( $api_client, $group_id ) {
		$this->api_client = $api_client;
		$this->group_id   = $group_id;
	}

	/**
	 * @return array|false
	 */
	private function get_raw_categories() {
		if ( null === $this->raw_categories ) {
			$categories = $this->api_client->get_categories();

			if ( $categories ) {
				$this->raw_categories = $categories;
			}
		}

		return $this->raw_categories ?: false;
	}

	public function get_categories() {
		if ( null === $this->categories ) {
			$raw_categories = $this->get_raw_categories();

			if ( $raw_categories ) {
				$this->categories = array();

				foreach ( $raw_categories as $category_id => $category_data ) {
					$parent_category                  = array(
						'id'     => $category_id,
						'name'   => $category_data['name'],
						'parent' => null,
					);
					$this->categories[ $category_id ] = $parent_category;

					if ( ! empty( $category_data['children'] ) ) {
						foreach ( $category_data['children'] as $child_category_id => $child_category_name ) {
							$this->categories[ $child_category_id ] = array(
								'id'     => $child_category_id,
								'name'   => $child_category_name,
								'parent' => $parent_category,
							);
						}
					}
				}
			}
		}

		return $this->categories ?: false;
	}

	/**
	 * @param boolean $with_parent_name
	 *
	 * @return array|false
	 */
	public function get_categories_list( $with_parent_name = false ) {
		$categories = $this->get_categories();

		if ( ! $categories ) {
			return false;
		}

		$categories_list = array();

		foreach ( $categories as $category_id => $category_data ) {
			$category_name = $category_data['name'];

			if ( $with_parent_name && ! empty( $category_data['parent'] ) ) {
				$category_name = sprintf( '%s -> %s', $category_data['parent']['name'], $category_name );
			}

			$categories_list[ $category_id ] = $category_name;
		}

		return $categories_list;
	}

	/**
	 * @param int $category_id
	 * @param boolean $with_parent_name
	 *
	 * @return string|null
	 */
	public function get_category_name( $category_id, $with_parent_name = false ) {
		$categories = $this->get_categories();

		if ( ! array_key_exists( $category_id, $categories ) ) {
			return null;
		}

		$category_name = $categories[ $category_id ]['name'];

		if ( $with_parent_name && ! empty( $categories[ $category_id ]['parent'] ) ) {
			$category_name = sprintf( '%s -> %s', $categories[ $category_id ]['parent']['name'], $category_name );
		}

		return $category_name;
	}

	/**
	 * @param int $term_id
	 * @param boolean $with_parent_name
	 *
	 * @return string|null
	 */
	public function get_category_name_by_term_id( $term_id, $with_parent_name = false ) {
		$category_id = $this->get_category_id( $term_id );

		if ( null === $category_id ) {
			return null;
		}

		return $this->get_category_name( $category_id, $with_parent_name );
	}

	/**
	 * @param int $term_id
	 *
	 * @return int|null
	 */
	public function get_category_id( $term_id ) {
		$meta = VkCommerce_Meta_Product_Category::get( $term_id, $this->group_id );

		return $meta ? (int) $meta->get_category_id() : null;
	}

	/**
	 * @param int $term_id
	 * @param int $category_id
	 *
	 * @return void
	 */
	public function save_category_id( $term_id, $category_id ) {
		$meta = new VkCommerce_Meta_Product_Category( $term_id, $this->group_id, $category_id );

		$meta->save();
	}

	public function delete_category_id( $term_id ) {
		$meta = VkCommerce_Meta_Product_Category::get( $term_id, $this->group_id );

		if ( $meta ) {
			$meta->delete();
		}
	}
}
