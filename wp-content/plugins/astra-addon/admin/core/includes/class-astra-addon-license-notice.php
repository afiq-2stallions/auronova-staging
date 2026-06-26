<?php
/**
 * Astra Addon License Activation Notice
 *
 * @package Astra Addon
 * @since 4.12.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

/**
 * Class Astra_Addon_License_Notice
 *
 * Displays a license activation notice using Astra's notice library
 * when the Astra Pro license is not active.
 *
 * @since 4.12.0
 */
class Astra_Addon_License_Notice {
	/**
	 * Instance
	 *
	 * @var object Class object.
	 * @since 4.12.0
	 */
	private static $instance;

	/**
	 * Initiator
	 *
	 * @since 4.12.0
	 * @return object initialized object of class.
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
	 * @since 4.12.0
	 */
	public function __construct() {
		add_action( 'admin_init', array( $this, 'register_license_notice' ) );
		add_action( 'admin_enqueue_scripts', array( $this, 'enqueue_notice_styles' ) );
	}

	/**
	 * Add inline padding to license notices via the astra-notices stylesheet handle.
	 *
	 * @since 4.13.4
	 * @return void
	 */
	public function enqueue_notice_styles() {
		wp_add_inline_style( 'astra-notices', '.astra-addon-license-notice { padding: 12px; }' );
	}

	/**
	 * Register license activation notice
	 *
	 * @since 4.12.0
	 * @return void
	 */
	public function register_license_notice() {
		// Check if BSF_Admin_Notices class is available.
		if ( ! class_exists( 'BSF_Admin_Notices' ) ) {
			return;
		}

		if ( ! ASTRA_ADDON_BSF_PACKAGE ) {
			return;
		}

		$product_id        = $this->get_product_id();
		$renew_url         = 'https://store.brainstormforce.com/upgrades/?utm_source=wp&utm_medium=dashboard&utm_campaign=license-expiry';
		$learn_more_url    = 'https://wpastra.com/?utm_source=wp&utm_medium=dashboard&utm_campaign=license-expiry';
		$is_license_active = is_callable( 'BSF_License_Manager::bsf_is_active_license' )
			? call_user_func( 'BSF_License_Manager::bsf_is_active_license', $product_id )
			: false;

		if ( ! $is_license_active ) {
			$this->add_license_notice(
				'astra-addon-license-inactive',
				'error',
				$this->build_notice_html(
					esc_html__( 'Your Astra Pro license isn\'t active', 'astra-addon' ),
					esc_html__( 'Please activate your license to enable premium features, automatic updates, and access to support.', 'astra-addon' ),
					esc_html__( 'Activate License', 'astra-addon' ),
					admin_url( 'admin.php?page=astra&path=settings' ),
					false,
					esc_html__( 'Learn More', 'astra-addon' ),
					'https://wpastra.com/?utm_source=wp&utm_medium=dashboard&utm_campaign=license-activation'
				),
				8
			);
			return;
		}

		if ( function_exists( 'bsf_is_license_expired' ) && bsf_is_license_expired( $product_id ) ) {
			$this->add_license_notice(
				'astra-addon-license-expired',
				'error',
				$this->build_notice_html(
					esc_html__( 'Your Astra Pro license has expired', 'astra-addon' ),
					esc_html__( 'Renew your license to continue receiving automatic updates and access to premium support.', 'astra-addon' ),
					esc_html__( 'Renew License', 'astra-addon' ),
					$renew_url,
					true,
					esc_html__( 'Learn More', 'astra-addon' ),
					$learn_more_url
				)
			);
			return;
		}

		// Expiring-soon notice hidden pending auto-renewal detection (see GitHub issue #2672).
		// Re-enable once is_auto_renewal flag is available from the license status API.
		// if ( function_exists( 'bsf_is_license_expiring_soon' ) && bsf_is_license_expiring_soon( $product_id, 14 ) ) {
		// $expires   = function_exists( 'bsf_get_license_expires' ) ? bsf_get_license_expires( $product_id ) : '';
		// $days_left = $expires ? max( 0, (int) ceil( ( strtotime( $expires ) - time() ) / DAY_IN_SECONDS ) ) : 0;
		// $this->add_license_notice(
		// 'astra-addon-license-expiring-soon',
		// 'warning',
		// $this->build_notice_html(
		// * translators: %d: number of days until license expires */
		// sprintf( esc_html__( 'Your Astra Pro license expires in %d day(s)', 'astra-addon' ), $days_left ),
		// esc_html__( 'Renew your license before it expires to avoid losing access to automatic updates and premium support.', 'astra-addon' ),
		// esc_html__( 'Renew License', 'astra-addon' ),
		// $renew_url,
		// true,
		// esc_html__( 'Learn More', 'astra-addon' ),
		// $learn_more_url
		// )
		// );
		// }
	}

	/**
	 * Get the product ID for license check
	 *
	 * @since 4.12.0
	 * @return string Product ID
	 */
	private function get_product_id() {
		if ( is_callable( 'bsf_extract_product_id' ) ) {
			return call_user_func( 'bsf_extract_product_id', ASTRA_EXT_DIR );
		}
		return '';
	}

	/**
	 * Register a license admin notice via BSF_Admin_Notices.
	 *
	 * @since 4.13.4
	 * @param string $id       Notice ID.
	 * @param string $type     Notice type (error|warning).
	 * @param string $message  Notice HTML.
	 * @param int    $priority Notice priority.
	 * @return void
	 */
	private function add_license_notice( $id, $type, $message, $priority = 9 ) {
		BSF_Admin_Notices::add_notice(
			array(
				'id'                         => $id,
				'type'                       => $type,
				'message'                    => $message,
				'show_if'                    => true,
				'repeat-notice-after'        => false,
				'display-with-other-notices' => true,
				'is_dismissible'             => true,
				'capability'                 => 'manage_options',
				'priority'                   => $priority,
				'class'                      => 'astra-addon-license-notice',
			)
		);
	}

	/**
	 * Build the shared HTML template for all license notices.
	 *
	 * @since 4.13.4
	 * @param string $heading         Notice heading.
	 * @param string $body            Notice body text.
	 * @param string $primary_label   Primary CTA button label.
	 * @param string $primary_url     Primary CTA button URL.
	 * @param bool   $primary_external Whether the primary CTA opens in a new tab.
	 * @param string $secondary_label Optional secondary link label (e.g. "Learn More").
	 * @param string $secondary_url   Optional secondary link URL.
	 * @return string HTML markup.
	 */
	private function build_notice_html( $heading, $body, $primary_label, $primary_url, $primary_external = true, $secondary_label = '', $secondary_url = '' ) {
		$logo_url       = ASTRA_THEME_URI . 'inc/assets/images/astra-logo.svg';
		$primary_target = $primary_external ? ' target="_blank" rel="noopener noreferrer"' : '';
		$secondary_html = $secondary_label && $secondary_url
			? sprintf(
				'<a href="%s" target="_blank" rel="noopener noreferrer" class="button">%s</a>',
				esc_url( $secondary_url ),
				$secondary_label
			)
			: '';

		return sprintf(
			'<div class="astra-addon-license-notice-content" style="padding: 12px 0;">
				<div class="astra-addon-license-notice-logo">
					<img src="%1$s" alt="Astra Logo" />
				</div>
				<div class="astra-addon-license-notice-body-wrapper">
					<div class="astra-addon-license-notice-header">
						<strong>%2$s</strong>
					</div>
					<div class="astra-addon-license-notice-body">
						<p>%3$s</p>
					</div>
					<div class="astra-addon-license-notice-actions">
						<a href="%4$s"%5$s class="button button-primary" style="margin-right: 10px;">%6$s</a>
						%7$s
					</div>
				</div>
			</div>',
			esc_url( $logo_url ),
			$heading,
			$body,
			esc_url( $primary_url ),
			$primary_target,
			$primary_label,
			$secondary_html
		);
	}
}

// Initialize the class.
Astra_Addon_License_Notice::get_instance();
