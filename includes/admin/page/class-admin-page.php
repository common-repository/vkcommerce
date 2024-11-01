<?php

abstract class VkCommerce_Admin_Page {
	const TABS_FILTER = 'vkcommerce_admin_page_%s_tabs';
	const TAB_SECTION_LOAD_ACTION = 'vkcommerce_admin_page_%s_%s_%s_load';

	/**
	 * @var VkCommerce_Admin_Page_Tab[]
	 */
	private static $tabs = array();

	/**
	 * @noinspection PhpUnhandledExceptionInspection
	 * @noinspection PhpDocMissingThrowsInspection
	 *
	 * @return string
	 */
	public static function get_slug() {
		throw new Exception( 'VkCommerce page slug must be defined' );
	}

	/**
	 * @return string[]
	 */
	protected static function get_tabs_classes() {
		return array();
	}

	/**
	 * @noinspection PhpUnhandledExceptionInspection
	 * @noinspection PhpDocMissingThrowsInspection
	 *
	 * @return VkCommerce_Admin_Page_Tab[]
	 */
	protected static function get_tabs() {
		if ( empty( self::$tabs ) ) {
			$tabs = array();

			$tabs_classes = static::get_tabs_classes();

			foreach ( $tabs_classes as $tab_class ) {
				/** @var VkCommerce_Admin_Page_Tab $page */
				$tab = new $tab_class( static::get_slug() );

				$tabs[ $tab->get_id() ] = $tab;
			}

			self::$tabs = apply_filters( sprintf( static::TABS_FILTER, static::get_slug() ), $tabs );
		}

		return self::$tabs;
	}

	/**
	 * @param string $tab
	 *
	 * @return bool
	 */
	protected static function has_tab( $tab ) {
		return array_key_exists( $tab, static::get_tabs() );
	}

	/**
	 * @param string $tab
	 *
	 * @return VkCommerce_Admin_Page_Tab|null
	 */
	protected static function get_tab( $tab ) {
		if ( ! static::has_tab( $tab ) ) {
			return null;
		}

		$tabs = static::get_tabs();

		return $tabs[ $tab ];
	}

	/**
	 * @return VkCommerce_Admin_Page_Tab|null
	 */
	protected static function get_current_tab() {
		global $current_tab_id;

		return static::get_tab( $current_tab_id );
	}

	public static function load() {
		global $current_tab_id, $current_section_id;

		// Get current tab/section.
		$current_tab_id     = empty( $_GET['tab'] ) ? 'default' : sanitize_key( $_GET['tab'] );
		$current_section_id = empty( $_GET['section'] ) ? 'default' : sanitize_key( $_GET['section'] );

		if ( ! static::has_tab( $current_tab_id ) ) {
			wp_safe_redirect( static::get_url() );

			exit;
		}

		$current_tab = static::get_current_tab();

		if ( ! $current_tab->has_section( $current_section_id ) ) {
			wp_safe_redirect( $current_tab->get_url() );

			exit;
		}

		do_action( sprintf( static::TAB_SECTION_LOAD_ACTION, static::get_slug(), $current_tab_id, $current_section_id ) );
	}

	public static function output() {
		global $current_section_id;

		$tabs        = static::get_tabs();
		$current_tab = static::get_current_tab();

		require __DIR__ . '/views/html-page.php';
	}

	public static function get_url( $query_params = array() ) {
		if ( isset( $query_params['section'] ) && 'default' === $query_params['section'] ) {
			unset( $query_params['section'] );
		}

		if ( isset( $query_params['tab'] ) && 'default' === $query_params['tab'] ) {
			unset( $query_params['tab'] );
		}

		return admin_url( 'admin.php?' . http_build_query(
				array_merge(
					array(
						'page' => static::get_slug(),
					),
					$query_params
				)
			)
		);
	}
}
