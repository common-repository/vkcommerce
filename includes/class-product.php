<?php

/**
 * @see VkCommerce_Meta_Product
 */
class VkCommerce_Product {
	const STATUS_SYNCED = 'synced';
	const STATUS_OUTDATED = 'outdated';
	const STATUS_QUEUED_TO_PUBLISH = 'queued_to_publish';
	const STATUS_QUEUED_TO_UNPUBLISH = 'queued_to_unpublish';

	/**
	 * @var int
	 */
	private $id;

	/**
	 * @var string
	 */
	private $group_id;

	/**
	 * @var int
	 */
	private $post_id;

	/**
	 * @var string|null
	 */
	private $product_id;

	/**
	 * @var int|null
	 */
	private $main_photo_attachment_id;

	/**
	 * @var string|null
	 */
	private $main_photo_id;

	/**
	 * @var string|null
	 */
	private $main_photo_hash;

	/**
	 * @var array|null
	 */
	private $photos_data;

	/**
	 * @var string|null
	 */
	private $status;

	/**
	 * @var int|null
	 */
	private $config_category_id;

	/**
	 * @var int|null
	 */
	private $config_auto_publishing;

	/**
	 * @var string|null
	 */
	private $date_published;

	/**
	 * @var string|null
	 */
	private $date_published_gmt;

	/**
	 * @var string|null
	 */
	private $date_updated;

	/**
	 * @var string|null
	 */
	private $date_updated_gmt;

	/**
	 * @var string|null
	 */
	private $date_queued;

	/**
	 * @var string|null
	 */
	private $date_queued_gmt;

	/**
	 * @param string $group_id
	 * @param int $post_id
	 *
	 * @return VkCommerce_Product
	 */
	public static function create( $group_id, $post_id ) {
		return new static( $group_id, $post_id );
	}

	/**
	 * @param string $group_id
	 * @param int $post_id
	 *
	 * @return VkCommerce_Product|null
	 */
	public static function get( $group_id, $post_id ) {
		global $wpdb;

		$data = $wpdb->get_row(
			$wpdb->prepare(
				'SELECT * FROM ' . self::get_table_name() . ' WHERE group_id = %s AND post_id = %d;',
				$group_id,
				$post_id
			),
			ARRAY_A
		);

		if ( ! $data ) {
			return null;
		}

		$product = new static(
			$data['group_id'],
			$data['post_id'],
			$data['vkcommerce_id']
		);

		$product->set_config_auto_publishing( $data['config_auto_publishing'] );
		$product->set_config_category_id( $data['config_category_id'] );

		if ( ! empty( $data['status'] ) ) {
			$product->set_status( $data['status'] );
		}

		if ( ! empty( $data['product_id'] ) ) {
			$product->set_product_id( $data['product_id'] );

			$product->set_main_photo_data(
				$data['main_photo_id'],
				$data['main_photo_hash'],
				$data['main_photo_attachment_id']
			);

			if ( ! empty( $data['photos_data'] ) ) {
				$photos_data = json_decode( $data['photos_data'], true );

				if ( $photos_data ) {
					$product->set_photos_data( $photos_data );
				}
			}

			$product->set_dates(
				$data['date_published'],
				$data['date_published_gmt'],
				$data['date_updated'],
				$data['date_updated_gmt']
			);
		}

		if ( ! empty( $data['date_queued'] ) ) {
			$product->set_dates_queued(
				$data['date_queued'],
				$data['date_queued_gmt']
			);
		}

		return $product;
	}

	/**
	 * @param string $group_id
	 * @param int $post_id
	 * @param int|null $id
	 */
	private function __construct(
		$group_id,
		$post_id,
		$id = null
	) {
		if ( empty( $group_id ) ) {
			static::throwException( 'no group ID' );
		}
		if ( empty( $post_id ) ) {
			static::throwException( 'no post ID' );
		}

		$this->group_id = (string) $group_id;
		$this->post_id  = (int) $post_id;

		if ( $id ) {
			$this->id = (int) $id;
		}
	}

	/**
	 * @param bool $update_dates
	 *
	 * @return bool
	 */
	public function save( $update_dates = false ) {
		global $wpdb;

		$current_date     = current_time( 'mysql' );
		$current_date_gmt = get_gmt_from_date( $current_date );

		if ( $update_dates ) {
			if ( $this->get_product_id() && ! $this->get_date_published() ) {
				$this->date_published     = $current_date;
				$this->date_published_gmt = $current_date_gmt;
			}

			$this->date_updated     = $current_date;
			$this->date_updated_gmt = $current_date_gmt;
		}

		$data        = array(
			'group_id'                 => $this->group_id,
			'post_id'                  => $this->post_id,
			'product_id'               => $this->product_id,
			'main_photo_attachment_id' => $this->main_photo_attachment_id,
			'main_photo_id'            => $this->main_photo_id,
			'main_photo_hash'          => $this->main_photo_hash,
			'photos_data'              => $this->photos_data ? json_encode( $this->photos_data ) : null,
			'status'                   => $this->status,
			'config_auto_publishing'   => $this->config_auto_publishing,
			'config_category_id'       => $this->config_category_id,
			'date_published'           => $this->date_published,
			'date_published_gmt'       => $this->date_published_gmt,
			'date_updated'             => $this->date_updated,
			'date_updated_gmt'         => $this->date_updated_gmt,
			'date_queued'              => $this->date_queued,
			'date_queued_gmt'          => $this->date_queued_gmt,
		);
		$data_format = array(
			'%s',
			'%d',
			'%s',
			'%d',
			'%s',
			'%s',
			'%s',
			'%s',
			'%s',
			'%s',
			'%s',
			'%s',
			'%s',
			'%s',
			'%s',
			'%s',
		);

		if ( $this->id ) {
			$result = $wpdb->update(
				self::get_table_name(),
				$data,
				array(
					'vkcommerce_id' => $this->id,
				),
				$data_format,
				array(
					'%d',
				)
			);

			return false !== $result;
		}

		$result = $wpdb->insert( self::get_table_name(), $data, $data_format );
		if ( false === $result ) {
			return false;
		}

		$this->id = $wpdb->insert_id;

		return true;
	}

	/**
	 * @param bool $force
	 *
	 * @return bool
	 */
	public function delete( $force = false ) {
		global $wpdb;

		if ( $this->is_configured() && ! $force ) {
			$this->clear_data();

			return $this->save();
		}

		return false !== $wpdb->delete( self::get_table_name(), array( 'vkcommerce_id' => $this->id ), array( '%d' ) );
	}

	/**
	 * @return string
	 */
	public function get_group_id() {
		return $this->group_id;
	}

	/**
	 * @return int
	 */
	public function get_post_id() {
		return $this->post_id;
	}

	/**
	 * @return string|null
	 */
	public function get_product_id() {
		return $this->product_id;
	}

	/**
	 * @return int|null
	 */
	public function get_main_photo_attachment_id() {
		return $this->main_photo_attachment_id;
	}

	/**
	 * @return string|null
	 */
	public function get_main_photo_id() {
		return $this->main_photo_id;
	}

	/**
	 * @return string|null
	 */
	public function get_main_photo_hash() {
		return $this->main_photo_hash;
	}

	/**
	 * @return array|null
	 */
	public function get_photos_data() {
		return $this->photos_data;
	}

	/**
	 * @return string|null
	 */
	public function get_status() {
		return $this->status;
	}

	/**
	 * @return int|null
	 */
	public function get_config_category_id() {
		return null === $this->config_category_id ? null : (int) $this->config_category_id;
	}

	/**
	 * @return bool|null
	 */
	public function get_config_auto_publishing() {
		return null !== $this->config_auto_publishing ? (bool) $this->config_auto_publishing : null;
	}

	/**
	 * @return string|null
	 */
	public function get_date_published() {
		return $this->date_published;
	}

	/**
	 * @return string|null
	 */
	public function get_date_published_gmt() {
		return $this->date_published_gmt;
	}

	/**
	 * @return string|null
	 */
	public function get_date_updated() {
		return $this->date_updated;
	}

	/**
	 * @return string|null
	 */
	public function get_date_updated_gmt() {
		return $this->date_updated_gmt;
	}

	/**
	 * @return string|null
	 */
	public function get_date_queued() {
		return $this->date_queued;
	}

	/**
	 * @return string|null
	 */
	public function get_date_queued_gmt() {
		return $this->date_queued_gmt;
	}

	/**
	 * @param string|null $product_id
	 *
	 * @return VkCommerce_Product
	 */
	public function set_product_id( $product_id ) {
		$this->product_id = $product_id ? (string) $product_id : null;

		return $this;
	}

	/**
	 * @param string $main_photo_id
	 * @param string $main_photo_hash
	 * @param int|null $main_photo_attachment_id
	 *
	 * @return VkCommerce_Product
	 */
	public function set_main_photo_data( $main_photo_id, $main_photo_hash, $main_photo_attachment_id = null ) {
		if ( empty( $main_photo_id ) ) {
			static::throwException( 'no main photo ID' );
		}
		if ( empty( $main_photo_hash ) ) {
			static::throwException( 'no main photo hash' );
		}

		$this->main_photo_id            = (string) $main_photo_id;
		$this->main_photo_hash          = (string) $main_photo_hash;
		$this->main_photo_attachment_id = $main_photo_attachment_id ? (int) $main_photo_attachment_id : null;

		return $this;
	}

	/**
	 * @param $photos_data
	 *
	 * @return $this
	 */
	public function set_photos_data( $photos_data ) {
		$this->photos_data = ! empty( $photos_data ) && is_array( $photos_data )
			? $photos_data
			: null;

		return $this;
	}

	/**
	 * @param string|null $status
	 *
	 * @return VkCommerce_Product
	 */
	public function set_status( $status ) {
		$this->status = $status;

		return $this;
	}

	/**
	 * @param int|null $category_id
	 *
	 * @return VkCommerce_Product
	 */
	public function set_config_category_id( $category_id ) {
		$this->config_category_id = null !== $category_id ? (int) $category_id : null;

		return $this;
	}

	/**
	 * @param int|bool|null $auto_publishing
	 *
	 * @return VkCommerce_Product
	 */
	public function set_config_auto_publishing( $auto_publishing ) {
		$this->config_auto_publishing = null !== $auto_publishing ? (int) ( (bool) $auto_publishing ) : null;

		return $this;
	}

	/**
	 * @param string $date_published
	 * @param string $date_published_gmt
	 * @param string $date_updated
	 * @param string $date_updated_gmt
	 *
	 * @return VkCommerce_Product
	 */
	public function set_dates(
		$date_published,
		$date_published_gmt,
		$date_updated,
		$date_updated_gmt
	) {
		$this->date_published     = (string) $date_published;
		$this->date_published_gmt = (string) $date_published_gmt;
		$this->date_updated       = (string) $date_updated;
		$this->date_updated_gmt   = (string) $date_updated_gmt;

		return $this;
	}

	/**
	 * @param string $date_queued
	 * @param string $date_queued_gmt
	 *
	 * @return $this
	 */
	public function set_dates_queued( $date_queued, $date_queued_gmt ) {
		$this->date_queued     = (string) $date_queued;
		$this->date_queued_gmt = (string) $date_queued_gmt;

		return $this;
	}

	/**
	 * @return $this
	 */
	public function clear_dates_queued() {
		$this->date_queued     = null;
		$this->date_queued_gmt = null;

		return $this;
	}

	/**
	 * @param string|null $date_queued
	 *
	 * @return $this
	 */
	public function set_date_queued( $date_queued = null ) {
		if ( empty( $date_queued ) ) {
			$date_queued = current_time( 'mysql' );
		}

		$this->date_queued     = (string) $date_queued;
		$this->date_queued_gmt = get_gmt_from_date( $date_queued );

		return $this;
	}

	/**
	 * @param int|null $category_id
	 * @param int|bool|null $auto_publishing
	 *
	 * @return VkCommerce_Product
	 */
	public function set_configs( $category_id, $auto_publishing ) {
		$this->set_config_category_id( $category_id );
		$this->set_config_auto_publishing( $auto_publishing );

		return $this;
	}

	/**
	 * @return VkCommerce_Product
	 */
	public function clear_data() {
		$this->product_id               = null;
		$this->main_photo_attachment_id = null;
		$this->main_photo_id            = null;
		$this->main_photo_hash          = null;
		$this->photos_data              = null;
		$this->status                   = null;
		$this->date_published           = null;
		$this->date_published_gmt       = null;
		$this->date_updated             = null;
		$this->date_updated_gmt         = null;
		$this->date_queued              = null;
		$this->date_queued_gmt          = null;

		return $this;
	}

	/**
	 * @return VkCommerce_Product
	 */
	public function clear_config() {
		$this->config_auto_publishing = null;
		$this->config_category_id     = null;

		return $this;
	}

	/**
	 * @return bool
	 */
	public function is_exported() {
		return ! empty( $this->product_id );
	}

	/**
	 * @return bool
	 */
	public function is_synced() {
		return self::STATUS_SYNCED === $this->status;
	}

	/**
	 * @return bool
	 */
	public function is_outdated() {
		return self::STATUS_OUTDATED === $this->status;
	}

	/**
	 * @return bool
	 */
	public function is_queued_to_publish() {
		return self::STATUS_QUEUED_TO_PUBLISH === $this->status;
	}

	/**
	 * @return bool
	 */
	public function is_queued_to_unpublish() {
		return self::STATUS_QUEUED_TO_UNPUBLISH === $this->status;
	}

	/**
	 * @return bool
	 */
	public function is_configured() {
		return null !== $this->config_category_id
			   || null !== $this->config_auto_publishing;
	}

	/**
	 * @return string
	 */
	private static function get_table_name() {
		global $wpdb;

		return $wpdb->prefix . VKCOMMERCE_TABLE_PRODUCTS;
	}

	/**
	 * @param $message
	 *
	 * @throws Exception
	 */
	private static function throwException( $message ) {
		throw new Exception( sprintf( 'VK Product: %s', $message ) );
	}
}
