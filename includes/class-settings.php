<?php

class VkCommerce_Settings {
	const API_APPLICATION_ID = 'vkcommerce_api_application_id';
	const API_SECRET_KEY = 'vkcommerce_api_secret_key';
	const API_ACCESS_TOKEN = 'vkcommerce_api_access_token';
	const API_GROUP_ID = 'vkcommerce_api_group_id';
	const API_USER_ID = 'vkcommerce_api_user_id';
	const API_AUTH_REQUEST_HASH = 'vkcommerce_api_auth_request_hash';

	const PRODUCTS_PUBLISHING_AUTO_PUBLISH = 'vkcommerce_products_publishing_auto_publish';
	const PRODUCTS_PUBLISHING_DESCRIPTION_TEMPLATE = 'vkcommerce_products_publishing_description_template';

	public static function get_api_application_id() {
		return get_option( self::API_APPLICATION_ID );
	}

	public static function get_api_secret_key() {
		return get_option( self::API_SECRET_KEY );
	}

	public static function get_api_access_token() {
		return get_option( self::API_ACCESS_TOKEN );
	}

	public static function get_api_group_id() {
		return get_option( self::API_GROUP_ID );
	}

	public static function get_api_user_id() {
		return get_option( self::API_USER_ID );
	}

	/**
	 * @return bool
	 */
	public static function is_api_ready() {
		return self::get_api_application_id()
		       && self::get_api_secret_key()
		       && self::get_api_access_token()
		       && self::get_api_group_id();
	}

	/**
	 * @return bool
	 */
	public static function is_products_auto_publish_enabled() {
		return (bool) get_option( self::PRODUCTS_PUBLISHING_AUTO_PUBLISH );
	}

	/**
	 * @return string
	 */
	public static function get_products_publishing_description_template() {
		return (string) get_option( self::PRODUCTS_PUBLISHING_DESCRIPTION_TEMPLATE, '' );
	}
}
