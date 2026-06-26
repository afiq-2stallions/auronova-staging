<?php
/**
 * UAEL License Activation Notice
 *
 * @package UAEL
 * @since 1.44.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Class UAEL_License_Notice
 *
 * Displays a license activation notice using BSF_Admin_Notices
 * when the UAEL license is not active.
 *
 * @since 1.44.0
 */
class UAEL_License_Notice {

	/**
	 * Instance
	 *
	 * @var object
	 * @since 1.44.0
	 */
	private static $instance;

	/**
	 * Initiator
	 *
	 * @since 1.44.0
	 * @return self
	 */
	public static function get_instance() {
		if ( ! isset( self::$instance ) ) {
			self::$instance = new self();
		}
		return self::$instance;
	}

	/**
	 * Constructor
	 *
	 * @since 1.44.0
	 */
	public function __construct() {
		add_action( 'admin_init', array( $this, 'register_license_notice' ) );
	}

	/**
	 * Register the license activation notice.
	 *
	 * @since 1.44.0
	 * @return void
	 */
	public function register_license_notice() {

		// Bail if BSF_Admin_Notices is not available.
		if ( ! class_exists( 'BSF_Admin_Notices' ) ) {
			return;
		}

		// Only run for BSF package builds.
		if ( ! UAEL_BSF_PACKAGE ) {
			return;
		}

		// Hide notice when white-label branding is active.
		if ( class_exists( 'UltimateElementor\Classes\UAEL_Helper' ) ) {
			$branding = \UltimateElementor\Classes\UAEL_Helper::get_white_labels();

			if ( ! empty( $branding['plugin']['name'] ) || ! empty( $branding['agency']['author'] ) ) {
				return;
			}
		}

		// Bail if license is already active.
		if ( ! class_exists( 'BSF_License_Manager' ) || BSF_License_Manager::bsf_is_active_license( 'uael' ) ) {
			return;
		}

		BSF_Admin_Notices::add_notice(
			array(
				'id'                         => 'uael-license-inactive',
				'type'                       => 'error',
				'message'                    => $this->get_notice_html(),
				'show_if'                    => true,
				'repeat-notice-after'        => false,
				'display-with-other-notices' => true,
				'is_dismissible'             => true,
				'capability'                 => 'manage_options',
				'priority'                   => 8,
				'class'                      => 'uael-license-notice',
			)
		);
	}

	/**
	 * Build the notice HTML.
	 *
	 * @since 1.44.0
	 * @return string
	 */
	private function get_notice_html() {

		$activate_url   = admin_url( 'admin.php?page=uaepro#settings' );
		$learn_more_url = 'https://ultimateelementor.com/pricing/?utm_source=wp&utm_medium=dashboard&utm_campaign=license-activation';
		$logo_url       = UAEL_URL . 'assets/images/settings/logo.svg';

		// Respect white-label plugin name.
		$branding    = class_exists( 'UltimateElementor\Classes\UAEL_Helper' ) ? \UltimateElementor\Classes\UAEL_Helper::get_white_labels() : array();
		$plugin_name = ! empty( $branding['plugin']['name'] ) ? $branding['plugin']['name'] : 'Ultimate Addons for Elementor Pro';

		return sprintf(
			'<div class="uael-license-notice-content">
				<div class="uael-license-notice-logo">
					<img src="%1$s" alt="%2$s" width="40px" height="40px" />
				</div>
				<div class="uael-license-notice-body-wrapper">
					<div class="uael-license-notice-header">
						<strong>%3$s</strong>
					</div>
					<div class="uael-license-notice-body">
						<p>%4$s</p>
					</div>
					<div class="uael-license-notice-actions">
						<a href="%5$s" class="button button-primary" style="margin-right:10px;">%6$s</a>
						<a href="%7$s" target="_blank" rel="noopener noreferrer" class="button">%8$s</a>
					</div>
				</div>
			</div>',
			esc_url( $logo_url ),
			esc_attr( $plugin_name ),
			/* translators: %s: Plugin name */
			esc_html( sprintf( __( "Your %s license isn't active", 'uael' ), $plugin_name ) ),
			esc_html__( 'Please activate your license to enable premium features, automatic updates, and access to support.', 'uael' ),
			esc_url( $activate_url ),
			esc_html__( 'Activate License', 'uael' ),
			esc_url( $learn_more_url ),
			esc_html__( 'Learn More', 'uael' )
		);
	}
}

UAEL_License_Notice::get_instance();
