<?php
/**
 * UAEL Sticky Header Module.
 *
 * @package UAEL
 */

namespace UltimateElementor\Modules\StickyHeader;

use UltimateElementor\Base\Module_Base;
use UltimateElementor\Classes\UAEL_Helper;
use Elementor\Controls_Manager;
use Elementor\Controls_Stack;

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

/**
 * Class Module
 */
class Module extends Module_Base {


	/**
	 * Module should load or not.
	 *
	 * @since 1.40.0
	 * @access public
	 *
	 * @return bool true|false.
	 */
	public static function is_enable() {
		return true;
	}

	/**
	 * Get Module Name.
	 *
	 * @since 1.40.0
	 * @access public
	 *
	 * @return string Module name.
	 */
	public function get_name() {
		return 'uael-sticky-header';
	}

	/**
	 * Check if this is a widget.
	 *
	 * @since 1.40.0
	 * @access public
	 *
	 * @return bool true|false.
	 */
	public function is_widget() {
		return false;
	}

	/**
	 * Get Widgets.
	 *
	 * @since 1.40.0
	 * @access public
	 *
	 * @return array Widgets.
	 */
	public function get_widgets() {
		return array();
	}

	/**
	 * Constructor.
	 */
	public function __construct() {
		parent::__construct();
		
		// Check if the sticky header feature is active.
		if ( UAEL_Helper::is_widget_active( 'StickyHeader' ) ) {
			// Check if HFE is active.
			if ( ! defined( 'HFE_VER' ) ) {
				return;
			}

			// Always initialize Sticky_Header to hook frontend filters.
			new Sticky_Header();

			// Only add controls if editing your custom header post type.
			if ( is_admin() && isset( $_GET['post'] ) ) { //phpcs:ignore WordPress.Security.NonceVerification.Recommended -- Nonce verification not required as this is a read-only operation and data is already sanitized.
				$post_id       = absint( $_GET['post'] ); //phpcs:ignore WordPress.Security.NonceVerification.Recommended -- Nonce verification not required as this is a read-only operation and data is already sanitized.
				$post_type     = get_post_type( $post_id );
				$post_hfe_type = get_post_meta( $post_id, 'ehf_template_type', true );
				if ( 'elementor-hf' === $post_type && 'type_header' === $post_hfe_type ) {
					add_action( 'elementor/element/section/section_advanced/after_section_end', array( __CLASS__, 'add_controls_sections' ), 10 );
					add_action( 'elementor/element/container/section_layout/after_section_end', array( __CLASS__, 'add_controls_sections' ), 10 );
				}
			}
			
			// Frontend assets.
			add_action( 'wp_enqueue_scripts', array( $this, 'enqueue_frontend_scripts' ) );
			
			// Add body class if sticky header is enabled.
			add_filter( 'body_class', array( $this, 'add_body_class' ) );
		}
	}

	/**
	 * Add controls sections.
	 *
	 * @since 1.40.0
	 * @param Controls_Stack $element Current element.
	 */
	public static function add_controls_sections( Controls_Stack $element ) {
		// Only show sticky header settings for HFE headers.
		$element->start_controls_section(
			'uae_sticky_header_section',
			array(
				'tab'   => Controls_Manager::TAB_ADVANCED,
				/* translators: %s admin link */
				'label' => sprintf( __( '%1s - Sticky Header', 'uael' ), UAEL_PLUGIN_SHORT_NAME ),
			)
		);

			include_once 'sticky-header.php';

			$sticky_header = new Sticky_Header();
			$sticky_header->add_controls( $element );

		$element->end_controls_section();
	}

	/**
	 * Enqueue frontend scripts.
	 *
	 * @since 1.40.0
	 */
	public function enqueue_frontend_scripts() {
		wp_enqueue_script(
			'uae-sticky-header',
			UAEL_URL . 'assets/js/uael-sticky-header.js',
			array( 'jquery', 'elementor-frontend' ),
			UAEL_VER,
			true
		);
	}

	/**
	 * Add body class if sticky header is enabled.
	 *
	 * @since 1.40.0
	 * @param array $classes Body classes.
	 * @return array Modified body classes.
	 */
	public function add_body_class( $classes ) {
		$classes[] = 'uae-sticky-header-enabled';
		return $classes;
	}
}
