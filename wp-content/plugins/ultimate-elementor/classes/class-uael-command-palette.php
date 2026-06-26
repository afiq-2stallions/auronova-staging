<?php
/**
 * UAEL Command Palette Integration
 *
 * Integrates customizer shortcuts with WordPress 6.9+ Command Palette (Global Search)
 * Accessible admin-wide via Ctrl/Cmd + K
 *
 * @package UAEL
 * @since 1.42.2
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

/**
 * Class UAEL_Command_Palette
 *
 * Handles the registration and enqueuing of command palette scripts
 *
 * @since 1.42.2
 */
class UAEL_Command_Palette {

	/**
	 * Instance
	 *
	 * @var UAEL_Command_Palette|null
	 * @since 1.42.2
	 */
	private static $instance = null;

	/**
	 * Get Instance
	 *
	 * @since 1.42.2
	 * @return UAEL_Command_Palette
	 */
	public static function get_instance() {
		if ( null === self::$instance ) {
			self::$instance = new self();
		}
		return self::$instance;
	}

	/**
	 * Constructor
	 *
	 * @since 1.42.2
	 */
	private function __construct() {
		// Only load on WordPress 6.9+ where command palette is available admin-wide.
		if ( version_compare( get_bloginfo( 'version' ), '6.9', '>=' ) ) {
			add_action( 'admin_enqueue_scripts', array( $this, 'enqueue_command_palette_scripts' ) );
		}
	}

	/**
	 * Enqueue Command Palette Scripts
	 *
	 * Loads the JavaScript file that registers UAEL commands with WordPress Command Palette
	 *
	 * @since 1.42.2
	 * @return void
	 */
	public function enqueue_command_palette_scripts() {
		// Check if user has admin capabilities.
		if ( ! current_user_can( 'manage_options' ) ) {
			return;
		}

		// Prevent duplicate enqueuing.
		if ( wp_script_is( 'uael-command-palette', 'enqueued' ) || wp_script_is( 'uael-command-palette', 'registered' ) ) {
			return;
		}

		$script_path       = UAEL_DIR . 'build/command-palette.asset.php';
		$script_asset_file = file_exists( $script_path ) ? include $script_path : array(
			'dependencies' => array( 'wp-commands', 'wp-data', 'wp-i18n', 'wp-element' ),
			'version'      => UAEL_VER,
		);

		// Enqueue the command palette script.
		wp_enqueue_script(
			'uael-command-palette',
			UAEL_URL . 'build/command-palette.js',
			$script_asset_file['dependencies'],
			$script_asset_file['version'],
			true
		);

		// Get branding settings for white-label support.
		$branding          = \UltimateElementor\Classes\UAEL_Helper::get_white_labels();
		$plugin_name       = ! empty( $branding['plugin']['name'] ) ? $branding['plugin']['name'] : __( 'Ultimate Addons for Elementor', 'uael' );
		$plugin_short_name = ! empty( $branding['plugin']['short_name'] ) ? $branding['plugin']['short_name'] : __( 'Ultimate Addons', 'uael' );
		$enable_kb         = isset( $branding['enable_knowledgebase'] ) ? $branding['enable_knowledgebase'] : 'enable';
		$kb_url            = ! empty( $branding['knowledgebase_url'] ) ? $branding['knowledgebase_url'] : 'https://ultimateelementor.com/docs/';
		$enable_support    = isset( $branding['enable_support'] ) ? $branding['enable_support'] : 'enable';
		$support_url       = ! empty( $branding['support_url'] ) ? $branding['support_url'] : 'https://ultimateelementor.com/support/';

		// Localize script with customizer URLs and settings.
		wp_localize_script(
			'uael-command-palette',
			'uaelCommandPalette',
			array(
				'adminUrl'        => admin_url(),
				'customizerUrl'   => admin_url( 'customize.php' ),
				'pluginName'      => $plugin_name,
				'pluginShortName' => $plugin_short_name,
				'enableKb'        => $enable_kb,
				'kbUrl'           => $kb_url,
				'enableSupport'   => $enable_support,
				'supportUrl'      => $support_url,
			)
		);

		// Set script translations.
		wp_set_script_translations( 'uael-command-palette', 'uael' );
	}
}

// Initialize the class.
UAEL_Command_Palette::get_instance();
