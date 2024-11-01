<?php

class VkCommerce_Meta_Product_Category {
	const KEY = 'vkcommerce_%s_product_category';

	/**
	 * @var int
	 */
	private $term_id;

	/**
	 * @var int
	 */
	private $group_id;

	/**
	 * @var string
	 */
	private $category_id;

	/**
	 * @param int $group_id
	 *
	 * @return string
	 */
	private static function get_key( $group_id ) {
		return sprintf( self::KEY, $group_id );
	}

	/**
	 * @param int $term_id
	 * @param int $group_id
	 * @param int $category_id
	 */
	public function __construct( $term_id, $group_id, $category_id ) {
		$this->term_id     = $term_id;
		$this->group_id    = $group_id;
		$this->category_id = $category_id;
	}

	/**
	 * @param int $term_id
	 * @param int $group_id
	 *
	 * @return VkCommerce_Meta_Product_Category|null
	 */
	public static function get( $term_id, $group_id ) {
		$category_id = get_term_meta( $term_id, static::get_key( $group_id ), true );

		if ( ! is_numeric( $category_id ) ) {
			return null;
		}

		return new static( $term_id, $group_id, (int) $category_id );
	}

	/**
	 * @return void
	 */
	public function save() {
		update_term_meta(
			$this->term_id,
			self::get_key( $this->group_id ),
			$this->category_id
		);
	}

	public function delete() {
		delete_term_meta( $this->term_id, self::get_key( $this->group_id ) );
	}

	/**
	 * @return int
	 */
	public function get_category_id() {
		return $this->category_id;
	}
}
