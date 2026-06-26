<?php
/**
 * Apply Presets.
 *
 * @package UAEL
 */

namespace UltimateElementor\Modules\PresetsSelect;

use UltimateElementor\Base\Module_Base;
use UltimateElementor\Classes\UAEL_Helper;

use UltimateElementor\Modules\PresetsSelect\Controls\Presets_Select;

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

/**
 * Class Module.
 */
class Module extends Module_Base {

	const QUERY_CONTROL_ID = 'uael-presets-select';

	/**
	 * Module should load or not.
	 *
	 * @since 1.33.0
	 * @access public
	 *
	 * @return bool true|false.
	 */
	public static function is_enable() {
		return true;
	}

	/**
	 * Constructer.
	 *
	 * @since 1.33.0
	 * @access public
	 */
	public function __construct() {
		parent::__construct();

		if ( UAEL_Helper::is_widget_active( 'Presets' ) ) {

			$this->add_actions();
		}
	}

	/**
	 * Get Module Name.
	 *
	 * @since 1.33.0
	 * @access public
	 *
	 * @return string Module name.
	 */
	public function get_name() {
		return 'presets-select';
	}

	/**
	 * Fetch the presets.
	 *
	 * @param string $preset_name Widget preset.
	 * @since 1.33.0
	 */
	public static function get_presets( $preset_name ) {

		// Prevent path traversal — only allow alphanumeric, hyphens, and underscores.
		if ( ! preg_match( '/^[a-zA-Z0-9_-]+$/', $preset_name ) ) {
			return false;
		}

		$design = UAEL_DIR . 'assets/presets/' . $preset_name . '.json';
		if ( ! is_readable( $design ) ) {
			return false;
		}
		return file_get_contents( $design ); // phpcs:ignore WordPressVIPMinimum.Performance.FetchingRemoteData.FileGetContentsUnknown -- Reading local file is OK.
	}

	/**
	 * Apply the presets.
	 *
	 * @since 1.33.0
	 */
	public static function apply_preset() {

		check_ajax_referer( 'uael-presets-nonce', 'nonce' );

		if ( ! current_user_can( 'edit_posts' ) ) {
			wp_send_json_error( 'Permission denied.', 403 );
		}

		if ( ! isset( $_POST['widget'] ) ) {
			wp_send_json_error( 'Missing widget parameter.', 400 );
		}

		$widget_name = sanitize_text_field( wp_unslash( $_POST['widget'] ) );
		$presets     = self::get_presets( substr( $widget_name, 5 ) );

		// Track preset applied event.
		if ( ! empty( $widget_name ) && class_exists( '\UAEL_Analytics_Events' ) ) {
			$install_time       = get_option( 'uae_usage_installed_time', 0 );
			$days_since_install = 0;
			if ( $install_time > 0 ) {
				$days_since_install = (int) floor( ( time() - (int) $install_time ) / DAY_IN_SECONDS );
			}
			\UAEL_Analytics_Events::track( 'preset_applied', $widget_name, array( 'days_since_install' => (string) $days_since_install ) );
		}

		if ( false === $presets ) {
			wp_send_json_error( 'Preset not found.', 404 );
		}

		wp_send_json_success( $presets, 200 );
	}

	/**
	 * Register Control
	 *
	 * @since 1.33.0
	 */
	public function register_controls() {
		$controls_manager = \Elementor\Plugin::$instance->controls_manager;

		$controls_manager->register( new Presets_Select() );

	}

	/**
	 * Add actions
	 *
	 * @since 1.33.0
	 */
	protected function add_actions() {

		add_action( 'wp_ajax_uael_widget_presets', array( $this, 'apply_preset' ) );
	}
}
