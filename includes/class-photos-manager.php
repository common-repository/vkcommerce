<?php

class VkCommerce_Photos_Manager {
	const MIN_PHOTO_WIDTH = 400;
	const MIN_PHOTO_HEIGHT = 400;
	const MAX_PHOTO_WIDTH_HEIGHT_SUM = 14000;
	const MAX_PHOTO_WIDTH_HEIGHT_RATIO = 20;
	const MAX_PHOTO_SIZE_MB = 50;

	/**
	 * @var VkCommerce_Products_Manager
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

		$this->logger = VkCommerce_Logger::instance();
	}

	/**
	 * @param int $attachment_id
	 *
	 * @return bool
	 */
	public function is_valid_photo( $attachment_id ) {
		try {
			$image_path = $this->get_image_path( $attachment_id );

			$sizes = @getimagesize( $image_path );
			if ( false !== $sizes ) {
				$width  = $sizes[0];
				$height = $sizes[1];

				if ( $width < self::MIN_PHOTO_WIDTH
					 || $height < self::MIN_PHOTO_HEIGHT
					 || ( $width + $height ) > self::MAX_PHOTO_WIDTH_HEIGHT_SUM
				) {
					return false;
				}

				$min_size = min( $width, $height );
				$max_size = max( $width, $height );

				if ( $min_size > 0 && ( $max_size / $min_size > 20 ) ) {
					return false;
				}
			}

			$file_size = @filesize( $image_path );
			if ( false !== $file_size && $file_size > ( self::MAX_PHOTO_SIZE_MB * 1024 * 1024 ) ) {
				return false;
			}

			return true;
		} catch ( Exception $e ) {
			$this->logger->error( 'Failed photo validation: ' . $e->getMessage() );

			return false;
		}
	}

	/**
	 * @param int $attachment_id
	 * @param bool $is_main_photo
	 *
	 * @return array|boolean
	 */
	public function upload_photo( $attachment_id, $is_main_photo = false ) {
		if ( ! $this->is_valid_photo( $attachment_id ) ) {
			return false;
		}

		try {
			$image_path = $this->get_image_path( $attachment_id );

			$photo = $this->api_client->upload_market_photo( $this->group_id, $image_path, $is_main_photo );

			$photo['hash'] = md5_file( $image_path );

			return $photo;
		} catch ( Exception $e ) {
			$this->logger->error( 'Failed upload photo: ' . $e->getMessage() );

			return false;
		}
	}

	/**
	 * @param VkCommerce_Product $vk_product
	 * @param int $attachment_id
	 *
	 * @return bool
	 */
	public function update_product_main_photo( $vk_product, $attachment_id ) {
		$attachment_id = (int) $attachment_id;

		try {
			if ( $vk_product->get_main_photo_attachment_id() === $attachment_id ) {
				return true;
			}

			$image_path = $this->get_image_path( $attachment_id );

			if ( md5_file( $image_path ) === $vk_product->get_main_photo_hash() ) {
				return true;
			}

			$photo = $this->upload_photo( $attachment_id, true );

			if ( ! $photo ) {
				$this->throwException( sprintf( 'failed upload of product #%d main photo', $vk_product->get_post_id() ) );
			}

			$photo_id   = (string) $photo['id'];
			$photo_hash = (string) $photo['hash'];

			$vk_product->set_main_photo_data(
				$attachment_id,
				$photo_id,
				$photo_hash
			);

			return true;
		} catch ( Exception $e ) {
			$this->logger->error( 'Failed update product main photo: ' . $e->getMessage() );

			return false;
		}
	}

	/**
	 * @param array $attachments_ids
	 * @param array $current_photos_data
	 *
	 * @return array
	 */
	public function upload_product_photos( $attachments_ids, $current_photos_data = [] ) {
		if ( ! is_array( $current_photos_data ) ) {
			$current_photos_data = [];
		}

		$photos_data = [];

		foreach ( $attachments_ids as $attachment_id ) {
			$photo_data = null;

			if ( array_key_exists( $attachment_id, $current_photos_data ) ) {
				$photo_data = $current_photos_data[ $attachment_id ];
			}

			try {
				$image_path = $this->get_image_path( $attachment_id );

				if ( $photo_data && md5_file( $image_path ) === $photo_data['hash'] ) {
					unset( $current_photos_data[ $attachment_id ] );
				} else {
					$uploaded_photo_data = $this->upload_photo( $attachment_id );

					$photo_data = $uploaded_photo_data
						? array(
							'id'   => $uploaded_photo_data['id'],
							'hash' => $uploaded_photo_data['hash'],
						)
						: null;
				}

				if ( $photo_data ) {
					$photos_data[ $attachment_id ] = $photo_data;
				}
			} catch ( Exception $e ) {
				$this->logger->error( 'Failed upload product photo: ' . $e->getMessage() );
			}

			if ( 4 === count( $photos_data ) ) {
				break;
			}
		}

		return $photos_data;
	}

	/**
	 * @param int $attachment_id
	 *
	 * @return string
	 *
	 * @throws Exception
	 */
	private function get_image_path( $attachment_id ) {
		$path = get_attached_file( $attachment_id, true );

		if ( ! $path ) {
			$this->throwException( sprintf( 'image #%s file does not exist', $attachment_id ) );
		}

		if ( ! is_readable( $path ) ) {
			$this->throwException( sprintf( 'image #%s path %s is not readable', $attachment_id, $path ) );
		}

		return $path;
	}

	/**
	 * @param string $message
	 *
	 * @throws Exception
	 */
	private function throwException( $message ) {
		throw new Exception( sprintf( 'VkCom Photos Manager: %s', $message ) );
	}
}
