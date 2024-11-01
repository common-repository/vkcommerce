<?php

class VkCommerce_Api_Client {
	/**
	 * @var VkCommerce_Api_Client
	 */
	private static $_instance = null;

	/**
	 * @var string
	 */
	private $application_id;

	/**
	 * @var string
	 */
	private $access_token;

	/**
	 * @var string
	 */
	private $secret_key;

	/**
	 * @var string
	 */
	private $api_base_url = 'https://api.vk.com/method/';

	/**
	 * @var string
	 */
	private $api_version = '5.131';

	/**
	 * @var VkCommerce_Logger
	 */
	private $logger;

	/**
	 * @return VkCommerce_Api_Client
	 */
	public static function instance() {
		if ( is_null( self::$_instance ) ) {
			self::$_instance = new self(
				get_option( VkCommerce_Settings::API_APPLICATION_ID, '' ),
				get_option( VkCommerce_Settings::API_ACCESS_TOKEN, '' ),
				get_option( VkCommerce_Settings::API_SECRET_KEY, '' )
			);
		}

		return self::$_instance;
	}

	/**
	 * @param $application_id
	 * @param $access_token
	 * @param $secret_key
	 */
	protected function __construct( $application_id, $access_token, $secret_key ) {
		$this->application_id = $application_id;
		$this->access_token   = $access_token;
		$this->secret_key     = $secret_key;

		$this->logger = VkCommerce_Logger::instance();
	}

	/**
	 * @param $redirect_url
	 * @param $code
	 *
	 * @return string|bool
	 */
	public function get_access_token( $redirect_url, $code ) {
		try {
			$data = $this->make_request(
				'https://oauth.vk.com/access_token',
				'GET',
				array(
					'client_id'     => $this->application_id,
					'client_secret' => $this->secret_key,
					'redirect_uri'  => $redirect_url,
					'code'          => $code,
				)
			);

			if ( empty( $data['access_token'] ) ) {
				$this->throwException( 'invalid access token response, no access token' );
			}

			if ( empty( $data['user_id'] ) ) {
				$this->throwException( 'invalid access token response, no user ID' );
			}

			$access_token = trim( $data['access_token'] );

			update_option( VkCommerce_Settings::API_ACCESS_TOKEN, $access_token );
			update_option( VkCommerce_Settings::API_USER_ID, trim( $data['user_id'] ) );

			return $access_token;
		} catch ( Exception $e ) {
			$this->logger->error( 'Failed obtaining VK access token: ' . $e->getMessage() );

			return false;
		}
	}

	/**
	 * @return array|false
	 */
	public function get_user_data() {
		try {
			$data = $this->make_api_request( 'users.get', array(
				'user_id' => VkCommerce_Settings::get_api_user_id(),
			) );

			if ( empty( $data['response'] ) || empty( $data['response'][0] ) || empty( $data['response'][0]['id'] ) ) {
				$this->throwException( 'invalid user data response' );
			}

			return $data['response'][0];
		} catch ( Exception $e ) {
			$this->logger->error( 'Failed get VK user data: ' . $e->getMessage() );

			return false;
		}
	}

	/**
	 * @return array|false
	 */
	public function get_user_groups() {
		try {
			$data = $this->make_api_request( 'groups.get', array(
				'user_id'  => VkCommerce_Settings::get_api_user_id(),
				'extended' => '1',
				'filter'   => 'admin',
				'fields'   => 'market',
				'count'    => 100,
			) );

			if ( empty( $data['response'] ) || empty( $data['response']['items'] ) ) {
				$this->throwException( 'invalid user groups response' );
			}

			return $data['response']['items'];
		} catch ( Exception $e ) {
			$this->logger->error( 'Failed get VK user groups: ' . $e->getMessage() );

			return false;
		}
	}

	/**
	 * @return array|false
	 */
	public function get_categories() {
		try {
			$data = $this->make_api_market_request( 'getCategories', array(
				'count'  => 1000,
				'offset' => 0,
			) );

			if ( empty( $data['response'] ) || empty( $data['response']['items'] ) ) {
				$this->throwException( 'invalid categories response' );
			}

			$categories = array();

			foreach ( $data['response']['items'] as $item ) {
				$item_id = (int) $item['id'];
				if ( ! empty( $item['section'] ) ) {
					$section_id = (int) $item['section']['id'];
					if ( ! array_key_exists( $section_id, $categories ) ) {
						$categories[ $section_id ] = array(
							'id'       => $item['section']['id'],
							'name'     => $item['section']['name'],
							'children' => array(),
						);
					}

					$categories[ $section_id ]['children'][ $item_id ] = $item['name'];
				} else {
					$categories[ $item_id ] = array(
						'id'       => $item_id,
						'name'     => $item['name'],
						'children' => array(),
					);
				}
			}

			return $categories;
		} catch ( Exception $e ) {
			$this->logger->error( 'Failed get VK categories: ' . $e->getMessage() );

			return false;
		}
	}

	/**
	 * @param string $market_method
	 * @param array $query_params
	 *
	 * @return array
	 *
	 * @throws Exception
	 */
	public function make_api_market_request( $market_method, $query_params = array() ) {
		$http_method = 'GET';

		$data = $this->make_api_request(
			'market.' . $market_method,
			array_merge(
				$query_params,
				array(
					'access_token' => $this->access_token,
				)
			),
			$http_method
		);

		if ( ! empty( $data['error'] ) ) {
			$this->throwException( sprintf(
				'VK API: failed market.%s request - %s',
				$market_method,
				! empty( $data['error']['error_msg'] ) ? $data['error']['error_msg'] : 'unknown'
			) );
		}

		return $data;
	}

	/**
	 * @param int $group_id
	 * @param string $file_path
	 * @param bool $is_main_photo
	 *
	 * @return array
	 *
	 * @throws Exception
	 */
	public function upload_market_photo( $group_id, $file_path, $is_main_photo = false ) {
		$data = $this->make_api_request(
			'photos.getMarketUploadServer',
			array(
				'group_id'   => $group_id,
				'main_photo' => $is_main_photo ? 1 : 0,
			)
		);

		if ( empty( $data['response']['upload_url'] ) ) {
			$this->throwException( 'failed to get server url to upload market photo' );
		}

		$upload_url = $data['response']['upload_url'];

		$curl     = new Wp_Http_Curl();
		$response = $curl->request( $upload_url, array(
			'body'    => array(
				'photo' => version_compare( PHP_VERSION, '5.5', '>=' )
					? new CURLFile( $file_path )
					: '@' . $file_path,
			),
			'method'  => 'POST',
			'headers' => array(
				'Content-Type' => 'multipart/form-data',
			),
		) );

		if ( $response instanceof WP_Error ) {
			$this->throwException( sprintf( 'failed curl request to %s', $upload_url ) );
		}

		$data = json_decode( wp_remote_retrieve_body( $response ), true );

		if ( false === $data || empty( $data['photo'] ) ) {
			$this->throwException( sprintf( 'Invalid upload photo response: %s', var_export( $data, true ) ) );
		}

		$data['photo']    = stripslashes( $data['photo'] );
		$data['group_id'] = $group_id;

		$data = $this->make_api_request( 'photos.saveMarketPhoto', $data );

		if ( empty( $data['response'][0] ) ) {
			$this->throwException( 'failed to save uploaded market photo' );
		}

		return $data['response'][0];
	}

	/**
	 * @param string $api_method
	 * @param array $query_params
	 * @param string $http_method
	 *
	 * @return array
	 *
	 * @throws Exception
	 */
	public function make_api_request( $api_method, $query_params = array(), $http_method = 'GET' ) {
		return $this->make_request(
			$this->api_base_url . $api_method,
			$http_method,
			array_merge(
				$query_params,
				array(
					'access_token' => $this->access_token,
					'v'            => $this->api_version,
				)
			)
		);
	}

	/**
	 * @param string $url
	 * @param string $method
	 * @param array $query_params
	 * @param array $body_params
	 *
	 * @return array
	 *
	 * @throws Exception
	 */
	private function make_request( $url, $method, $query_params = array(), $body_params = array() ) {
		$request_options = array(
			'method' => $method,
		);

		if ( $body_params ) {
			$request_options['body'] = $body_params;
		}

		$response = wp_remote_request( $url . '?' . http_build_query( $query_params ), $request_options );

		if ( $response instanceof WP_Error ) {
			$this->throwException( sprintf( 'Failed http request to %s', $url ) );
		}

		$data = json_decode( wp_remote_retrieve_body( $response ), true );

		if ( false === $data ) {
			$this->throwException( 'Invalid http json response' );
		}

		return $data;
	}

	/**
	 * @param string $message
	 *
	 * @throws Exception
	 */
	private function throwException( $message ) {
		throw new Exception( sprintf( 'VkCom API Client: %s', $message ) );
	}
}
