<?php

final class VkCommerce {
	/**
	 * @var string
	 */
	private $version = '1.1.1';

	/**
	 * @var bool
	 */
	private $is_woocommerce_activated;

	/**
	 * @var VkCommerce
	 */
	protected static $_instance = null;

	public static function instance() {
		if ( is_null( self::$_instance ) ) {
			self::$_instance = new self();
		}

		return self::$_instance;
	}

	public function __construct() {
		$this->define_constants();
		$this->includes();
		$this->init_hooks();
	}

	/**
	 * @param $name
	 * @param $value
	 *
	 * @return void
	 */
	private function define( $name, $value ) {
		if ( ! defined( $name ) ) {
			define( $name, $value );
		}
	}

	/**
	 * @return void
	 */
	private function define_constants() {
		$this->define( 'VKCOMMERCE_ABSPATH', dirname( VKCOMMERCE_PLUGIN_FILE ) . '/' );
		$this->define( 'VKCOMMERCE_PLUGIN_BASENAME', plugin_basename( VKCOMMERCE_PLUGIN_FILE ) );
		$this->define( 'VKCOMMERCE_TRANSLATIONS_DIR', dirname( VKCOMMERCE_PLUGIN_FILE ) . '/i18n' );
		$this->define( 'VKCOMMERCE_VERSION', $this->version );
		$this->define( 'VKCOMMERCE_TABLE_PRODUCTS', 'vkcommerce_products' );

		$upload_dir = wp_upload_dir( null, false );
		$this->define( 'VKCOMMERCE_LOG_DIR', $upload_dir['basedir'] . '/vkcommerce-logs/' );
	}

	private function includes() {
		require_once __DIR__ . '/class-autoloader.php';
		require_once __DIR__ . '/class-install.php';

		if ( $this->is_admin_request() ) {
			require_once __DIR__ . '/admin/class-admin.php';
		}

		VkCommerce_Worker::instance();
	}

	private function init_hooks() {
		add_action( 'init', array( $this, 'init' ) );
	}

	public function init() {
		load_plugin_textdomain( 'vkcommerce', false, plugin_basename( dirname( VKCOMMERCE_PLUGIN_FILE ) ) . '/i18n' );
	}

	/**
	 * @return bool
	 */
	private function is_admin_request() {
		return is_admin();
	}

	/**
	 * @param string $file
	 *
	 * @return string
	 */
	public function plugin_url( $file ) {
		$file = trim( $file, '/' );

		return plugins_url( '/' . $file, VKCOMMERCE_PLUGIN_FILE );
	}

	/**
	 * @return bool
	 */
	public function is_woocommerce_activated() {
		if ( null === $this->is_woocommerce_activated ) {
			$this->is_woocommerce_activated = is_plugin_active( 'woocommerce/woocommerce.php' );
		}

		return $this->is_woocommerce_activated;
	}
}
