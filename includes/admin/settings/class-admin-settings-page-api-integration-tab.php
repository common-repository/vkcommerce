<?php

class VkCommerce_Admin_Settings_Page_Api_Integration_Tab extends VkCommerce_Admin_Settings_Page_Form_Tab {
	/**
	 * @inerhitDoc
	 */
	protected function init() {
		$this->label = __( 'API Integration', 'vkcommerce' );

		add_action(
			sprintf( VkCommerce_Admin_Page::TAB_SECTION_LOAD_ACTION, $this->page_slug, $this->get_id(), 'default' ),
			array( $this, 'load_default_section' )
		);
	}

	/**
	 * @inerhitDoc
	 */
	protected function get_own_sections() {
		return array( 'default' => __( 'API Integration', 'vkcommerce' ) );
	}

	public function get_settings_for_default_section() {
		$application_id = VkCommerce_Settings::get_api_application_id();
		$secret_key     = VkCommerce_Settings::get_api_secret_key();

		$settings = array(
			array(
				'name'        => VkCommerce_Settings::API_APPLICATION_ID,
				'type'        => VkCommerce_Admin_Form::FIELD_TEXT,
				'label'       => __( 'Application ID', 'vkcommerce' ),
				'id'          => 'vk-app-id',
				'label_for'   => 'vk-app-id',
				'description' => empty( $application_id )
					? __( 'First, enter an <strong>application ID</strong> and save the settings.', 'vkcommerce' )
					: '',
			),
			array(
				'name'        => VkCommerce_Settings::API_SECRET_KEY,
				'type'        => VkCommerce_Admin_Form::FIELD_TEXT,
				'label'       => __( 'Secret key', 'vkcommerce' ),
				'id'          => 'vk-secret-key',
				'label_for'   => 'vk-secret-key',
				'description' => empty( $secret_key )
					? __( 'Then, enter a <strong>secret key</strong> and save the settings.', 'vkcommerce' )
					: '',
			),
		);

		if ( $application_id && $secret_key ) {
			$settings[] = array(
				'no_option'       => true,
				'label'           => __( 'Access token', 'vkcommerce' ),
				'output_function' => array( $this, 'output_api_access_token' ),
			);

			if ( VkCommerce_Settings::get_api_access_token() ) {
				$settings[] = array(
					'name'            => VkCommerce_Settings::API_GROUP_ID,
					'type'            => VkCommerce_Admin_Form::FIELD_SELECT,
					'label'           => __( 'Group', 'vkcommerce' ),
					'options'         => array( '' => __( 'Please, select a group', 'vkcommerce' ) ),
					'id'              => 'vk-group',
					'label_for'       => 'vk-group',
					'output_function' => array( $this, 'output_group' ),
					'description'     => __( 'The group, where products will be published.', 'vkcommerce' ),
				);
			}
		}

		return $settings;
	}

	public function output_api_access_token( $field ) {
		extract( $field );

		$access_token     = VkCommerce_Settings::get_api_access_token();
		$has_access_token = ! empty( $access_token );
		$get_token_link   = $this->get_url( array( 'get_token' => 1 ) );

		if ( $has_access_token ) {
			$user_link   = 'https://vk.com/id' . VkCommerce_Settings::get_api_user_id();
			$verify_link = $this->get_url( array( 'verify_integration_status' => 1 ) );
		}

		require __DIR__ . '/views/html-access-token.php';
	}

	public function output_group( $field ) {
		$groups = VkCommerce_Api_Client::instance()->get_user_groups();

		if ( false === $groups ) {
			$groups_error = __( 'An error occurred while getting the list of groups.', 'vkcommerce' );
		} else {
			$groups = array_filter( $groups, function ( $group ) {
				return ( empty( $group['deactivated'] )
				         && ! empty( $group['market'] )
				         && ! empty( $group['market']['enabled'] )
				);
			} );

			foreach ( $groups as $group ) {
				$field['options'][ $group['id'] ] = $group['name'];
			}
		}

		$current_group_id = VkCommerce_Settings::get_api_group_id();

		require __DIR__ . '/views/html-group.php';
	}

	public function load_default_section() {
		switch ( true ) {
			case ! empty( $_GET['auth_callback'] ):
				$this->api_authentication();
				break;

			case ! empty( $_GET['get_token'] ):
				$this->get_token_redirect();
				break;

			case ! empty( $_GET['verify_integration_status'] ):
				$this->verify_integration_status();
				break;
		}
	}

	private function get_token_redirect() {
		$auth_request_hash = wp_generate_password( 16, false );
		set_transient( VkCommerce_Settings::API_AUTH_REQUEST_HASH, $auth_request_hash, 60 * 5 );

		$params    = array(
			'client_id'     => VkCommerce_Settings::get_api_application_id(),
			'redirect_uri'  => $this->get_auth_redirect_uri(),
			'display'       => 'page',
			'scope'         => 'offline,photos,market',
			'response_type' => 'code',
			'state'         => $auth_request_hash,
		);
		$auth_link = 'https://oauth.vk.com/authorize?' . http_build_query( $params );

		wp_redirect( $auth_link );
		exit;
	}

	private function api_authentication() {
		$redirect_url = $this->get_url();
		$fail_massage = __( 'Failed to get access token.', 'vkcommerce' );

		$auth_request_hash = get_transient( VkCommerce_Settings::API_AUTH_REQUEST_HASH );

		if ( empty( $_GET['state'] ) || $auth_request_hash !== $_GET['state'] ) {
			VkCommerce_Admin::add_error(
				'access_token',
				$fail_massage,
				$redirect_url
			);

			exit;
		}

		delete_transient( VkCommerce_Settings::API_AUTH_REQUEST_HASH );

		if ( empty( $_GET['code'] ) ) {
			VkCommerce_Admin::add_error(
				'access_token',
				$fail_massage,
				$redirect_url
			);

			exit;
		}

		$access_token = VkCommerce_Api_Client::instance()->get_access_token( $this->get_auth_redirect_uri(), $_GET['code'] );

		if ( ! $access_token ) {
			VkCommerce_Admin::add_error(
				'access_token',
				$fail_massage,
				$redirect_url
			);

			exit;
		}

		VkCommerce_Admin::add_message(
			'access_token',
			__( 'The access token has been successfully obtained.', 'vkcommerce' ),
			$redirect_url
		);

		exit;
	}

	private function verify_integration_status() {
		$user_data    = VkCommerce_Api_Client::instance()->get_user_data();
		$redirect_url = $this->get_url();

		if ( ! $user_data ) {
			VkCommerce_Admin::add_error(
				'integration_status',
				__( 'Failed to make API request. Please, try to refresh an access token, or contact plugin author.', 'vkcommerce' ),
				$redirect_url
			);

			exit;
		}

		VkCommerce_Admin::add_message(
			'integration_status',
			sprintf(
				__( 'The API integration works fine on behalf of user <a target="_blank" href="%s">%s</a>.', 'vkcommerce' ),
				'https://vk.com/id' . $user_data['id'],
				$user_data['last_name'] . ' ' . $user_data['first_name']
			),
			$redirect_url
		);

		exit;
	}

	/**
	 * @return string
	 */
	private function get_auth_redirect_uri() {
		return $this->get_url( array(
			'auth_callback' => 1,
		) );
	}
}
