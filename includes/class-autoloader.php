<?php

class VkCommerce_Autoloader {
	/**
	 * @var string
	 */
	private $include_path = '';

	public function __construct() {
		if ( function_exists( '__autoload' ) ) {
			spl_autoload_register( '__autoload' );
		}

		spl_autoload_register( array( $this, 'autoload' ) );

		$this->include_path = untrailingslashit( plugin_dir_path( VKCOMMERCE_PLUGIN_FILE ) ) . '/includes/';
	}

	/**
	 * @param string $class
	 *
	 * @return string
	 */
	private function get_file_name_from_class( $class ) {
		$class = str_replace( 'vkcommerce_', 'class-', $class );

		return str_replace( '_', '-', $class ) . '.php';
	}

	/**
	 * @param $path
	 *
	 * @return bool
	 */
	private function load_file( $path ) {
		if ( $path && is_readable( $path ) ) {
			require_once $path;

			return true;
		}

		return false;
	}

	/**
	 * @param $class
	 *
	 * @return void
	 */
	public function autoload( $class ) {
		$class = strtolower( $class );

		if ( 0 !== strpos( $class, 'vkcommerce_' ) ) {
			return;
		}

		$file = $this->get_file_name_from_class( $class );
		$path = $this->include_path;

		if ( 0 === strpos( $class, 'vkcommerce_admin_page' ) ) {
			$path = $this->include_path . 'admin/page/';
		} elseif ( 0 === strpos( $class, 'vkcommerce_admin_settings_' ) ) {
			$path = $this->include_path . 'admin/settings/';
		} elseif ( 0 === strpos( $class, 'vkcommerce_admin_help_' ) ) {
			$path = $this->include_path . 'admin/help/';
		} elseif ( 0 === strpos( $class, 'vkcommerce_admin_meta_' ) ) {
			$path = $this->include_path . 'admin/meta/';
		} elseif ( 0 === strpos( $class, 'vkcommerce_admin_' ) ) {
			$path = $this->include_path . 'admin/';
		} elseif ( 0 === strpos( $class, 'vkcommerce_meta_' ) ) {
			$path = $this->include_path . 'meta/';
		}

		if ( empty( $path ) || ! $this->load_file( $path . $file ) ) {
			$this->load_file( $file );
		}
	}
}

new VkCommerce_Autoloader();
