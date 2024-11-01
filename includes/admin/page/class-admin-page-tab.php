<?php

abstract class VkCommerce_Admin_Page_Tab {
	const SECTIONS_FILTER = 'vkcommerce_admin_page_%s_tab_%s_sections';
	const SECTION_DEFAULT = 'default';

	/**
	 * @var string
	 */
	protected $page_slug;

	/**
	 * @var string
	 */
	protected $id = 'default';

	/**
	 * @var string
	 */
	protected $label = '';

	/**
	 * @var array
	 */
	protected $sections;

	/**
	 * @var string
	 */
	protected $template;

	/**
	 * @param string $current_section_id
	 *
	 * @return string
	 */
	abstract protected function get_template( $current_section_id );

	/**
	 * @param string $page_slug
	 */
	public function __construct( $page_slug ) {
		$this->page_slug = $page_slug;

		$this->init();
	}

	/**
	 * @return void
	 */
	protected function init() {
	}

	/**
	 * @return string
	 */
	public function get_id() {
		return $this->id;
	}

	/**
	 * @return string
	 */
	public function get_label() {
		return $this->label;
	}

	/**
	 * @param string $current_section_id
	 *
	 * @return void
	 */
	public function output( $current_section_id ) {
		require $this->get_template( $current_section_id );
	}

	protected function get_own_sections() {
		return array( static::SECTION_DEFAULT => '' );
	}

	/**
	 * @return array
	 */
	public function get_sections() {
		if ( null === $this->sections ) {
			$sections = $this->get_own_sections();

			$this->sections = apply_filters( sprintf( self::SECTIONS_FILTER, $this->page_slug, $this->id ), $sections );
		}

		return $this->sections;
	}

	/**
	 * @param string $section_id
	 *
	 * @return bool
	 */
	public function has_section( $section_id ) {
		$sections = $this->get_sections();

		return array_key_exists( $section_id, $sections );
	}

	public function get_section_label( $section_id ) {
		if ( ! $this->has_section( $section_id ) ) {
			return '';
		}

		$sections = $this->get_sections();

		return $sections[ $section_id ];
	}

	/**
	 * @param $query_params
	 *
	 * @return string
	 */
	public function get_url( $query_params = array() ) {
		$http_params = array(
			'page' => $this->page_slug,
		);

		if ( 'default' !== $this->get_id() ) {
			$http_params['tab'] = $this->get_id();
		}

		if ( isset( $query_params['tab'] ) && 'default' === $query_params['tab'] ) {
			unset( $query_params['tab'] );
		}

		if ( isset( $query_params['section'] ) && 'default' === $query_params['section'] ) {
			unset( $query_params['section'] );
		}

		return admin_url( 'admin.php?' . http_build_query( array_merge(
				$http_params,
				$query_params
			) ) );
	}
}
