<?php
/**
 * Astra Addon BSF Analytics class helps to connect BSF Analytics.
 *
 * @package astra.
 */

defined( 'ABSPATH' ) or exit;

/**
 * Astra Addon BSF Analytics class.
 *
 * @since 4.10.0
 */
class Astra_Addon_BSF_Analytics {
	/**
	 * Instance object.
	 *
	 * @var self|null Class Instance.
	 */
	private static $instance = null;

	/**
	 * Events tracker instance.
	 *
	 * @var \BSF_Analytics_Events
	 * @since 4.12.5
	 */
	private static $events;

	/**
	 * Class constructor.
	 *
	 * @return void
	 * @since 4.10.0
	 */
	public function __construct() {
		/*
		* BSF Analytics.
		*/
		if ( ASTRA_ADDON_BSF_PACKAGE && ! class_exists( 'BSF_Analytics_Loader' ) ) {
			require_once ASTRA_EXT_DIR . 'admin/bsf-analytics/class-bsf-analytics-loader.php';
		}
		// Events class may already be loaded by the theme's bsf-analytics library.
		if ( ASTRA_ADDON_BSF_PACKAGE && ! class_exists( 'BSF_Analytics_Events' ) ) {
			require_once ASTRA_EXT_DIR . 'admin/bsf-analytics/class-bsf-analytics-events.php';
		}
		// Gracefully skip event tracking when the class is unavailable.
		if ( class_exists( 'BSF_Analytics_Events' ) ) {
			self::$events = new \BSF_Analytics_Events( 'astra' );
		}

		add_action( 'init', array( $this, 'init_bsf_analytics' ), 5 );
		add_filter( 'bsf_core_stats', array( $this, 'add_astra_addon_analytics_data' ), 20 );
		add_filter( 'astra_deactivation_survey_data', array( $this, 'addon_deactivation_survey_data' ) );

		// Skip event hook registration when events tracking is unavailable.
		if ( ! self::$events ) {
			return;
		}

		// Hook-based events.
		add_action( 'transition_post_status', array( $this, 'track_first_custom_layout_published' ), 10, 3 );
		add_action( 'update_option__astra_ext_enabled_extensions', array( $this, 'track_pro_module_toggled' ), 10, 2 );
		add_action( 'astra_addon_update_after', array( $this, 'track_pro_addon_updated' ) );
		add_action( 'updated_post_meta', array( $this, 'track_first_mega_menu_configured' ), 10, 4 );
		add_action( 'added_post_meta', array( $this, 'track_first_mega_menu_configured' ), 10, 4 );
		add_action( 'update_option__astra_beta_updates', array( $this, 'track_beta_updates_toggled' ), 10, 2 );
		add_action( 'update_option__astra_file_generation', array( $this, 'track_file_generation_toggled' ), 10, 2 );
		add_action( 'update_option__astra_ext_white_label', array( $this, 'track_white_label_toggled' ), 10, 2 );

		// License activation/deactivation events.
		add_action( 'bsf_activate_license_astra-addon_after_success', array( $this, 'track_pro_license_activated' ) );
		add_action( 'bsf_deactivate_license_astra-addon_after_success', array( $this, 'track_pro_license_deactivated' ) );
	}

	/**
	 * Initializes BSF Analytics.
	 *
	 * @since 4.10.0
	 * @return void
	 */
	public function init_bsf_analytics() {
		// Bail early if BSF_Analytics_Loader::get_instance is not callable and if Astra white labelling is enabled.
		if ( ! is_callable( '\BSF_Analytics_Loader::get_instance' ) || ! Astra_Ext_White_Label_Markup::show_branding() ) {
			return;
		}

		// Only initialize when the Astra theme (4.10.0+) already has analytics integrated.
		if ( ! defined( 'ASTRA_THEME_VERSION' ) || ! version_compare( ASTRA_THEME_VERSION, '4.10.0', '>=' ) ) {
			return;
		}

		$astra_addon_bsf_analytics = \BSF_Analytics_Loader::get_instance();
		$astra_addon_bsf_analytics->set_entity(
			array(
				'astra' => array(
					'product_name'        => 'Astra Pro',
					'path'                => ASTRA_EXT_DIR . 'admin/bsf-analytics',
					'author'              => 'brainstormforce',
					'time_to_display'     => '+24 hours',
					'hide_optin_checkbox' => true,

					/* Deactivation Survey */
					'deactivation_survey' => apply_filters(
						'astra_deactivation_survey_data',
						$this->addon_deactivation_survey_data( array() )
					),
				),
			)
		);
	}

	/**
	 * Callback function to add Astra Addon specific analytics data.
	 *
	 * @param array $stats_data existing stats_data.
	 *
	 * @since 4.10.0
	 * @return array
	 */
	public function add_astra_addon_analytics_data( $stats_data ) {
		$license_enabled         = ASTRA_ADDON_BSF_PACKAGE && class_exists( 'BSF_License_Manager' ) && BSF_License_Manager::bsf_is_active_license( bsf_extract_product_id( ASTRA_EXT_DIR ) );
		$is_using_color_switcher = class_exists( 'Astra_Addon_Builder_Helper' ) && Astra_Addon_Builder_Helper::is_component_loaded( 'color-switcher', 'header' );

		if ( ! isset( $stats_data['plugin_data']['astra'] ) ) {
			$stats_data['plugin_data']['astra'] = array();
		}

		$astra_addon_stats = array(
			'pro_version'     => ASTRA_EXT_VER,
			'boolean_values'  => array(
				'license_enabled'         => $license_enabled,
				'is_using_color_switcher' => $is_using_color_switcher,
			),
			'file_generation' => get_option( '_astra_file_generation', 'disable' ),
			'beta'            => get_option( '_astra_beta_updates', 'disable' ),
		);

		self::add_addon_modules_analytics_data( $astra_addon_stats );

		$stats_data['plugin_data']['astra'] = array_merge_recursive( $stats_data['plugin_data']['astra'], $astra_addon_stats );

		// Merge events after array_merge_recursive — recursive merge corrupts
		// numeric-indexed event arrays by merging inner keys at the same index.
		self::add_events_tracking_data( $stats_data['plugin_data']['astra'] );

		return $stats_data;
	}

	/**
	 * Method to updates the analytics data with the enabled status of these modules.
	 *     advanced-hooks (Site Builder)
	 *     blog-pro
	 *     colors-and-background
	 *     advanced-footer
	 *     mobile-header
	 *     header-sections
	 *     lifterlms
	 *     learndash
	 *     advanced-headers (Page Headers)
	 *     site-layouts
	 *     spacing
	 *     sticky-header
	 *     transparent-header
	 *     typography
	 *     woocommerce
	 *     edd
	 *     nav-menu
	 *
	 * @param array $astra_addon_stats The analytics data array to be updated.
	 *
	 * @since 4.10.0
	 * @return void
	 */
	public static function add_addon_modules_analytics_data( &$astra_addon_stats ) {
		// Fetch enabled modules.
		$active_modules = Astra_Ext_Extension::get_enabled_addons();
		foreach ( $active_modules as $module => $status ) {
			$key = 'module_' . str_replace( '-', '_', $module ) . '_enabled';
			$astra_addon_stats['boolean_values'][ $key ] = boolval( $status );
		}
	}

	// ============================================
	// Event Tracking Methods
	// ============================================

	/**
	 * Flush pending events into the analytics payload.
	 *
	 * All events are tracked via real-time hooks. This method flushes the
	 * pending queue into the stats array for delivery.
	 *
	 * @param array $astra_addon_stats Reference to the addon stats data.
	 * @since 4.12.5
	 * @return void
	 */
	private static function add_events_tracking_data( &$astra_addon_stats ) {
		// Bail if events tracking is unavailable — analytics payload is still sent without events.
		if ( ! self::$events ) {
			return;
		}

		$days_since_install = self::get_days_since_install();

		// pro_activated: track once when Pro addon is active.
		self::$events->track(
			'pro_activated',
			ASTRA_EXT_VER,
			array( 'days_since_install' => (string) $days_since_install )
		);

		// Ensure events_record always exists in payload.
		if ( ! isset( $astra_addon_stats['events_record'] ) ) {
			$astra_addon_stats['events_record'] = array();
		}

		$existing = isset( $astra_addon_stats['events_record'] ) ? $astra_addon_stats['events_record'] : array();
		$flushed  = self::$events->flush_pending();

		if ( ! empty( $existing ) || ! empty( $flushed ) ) {
			$astra_addon_stats['events_record'] = array_merge( $existing, $flushed );
		}
	}

	/**
	 * Track first custom layout published.
	 *
	 * This is the Astra Pro activation event — the single milestone that signals
	 * "Pro user got value from their purchase."
	 *
	 * @since 4.12.5
	 * @param string  $new_status New post status.
	 * @param string  $old_status Old post status.
	 * @param WP_Post $post       Post object.
	 * @return void
	 */
	public function track_first_custom_layout_published( $new_status, $old_status, $post ) {
		if ( ! defined( 'ASTRA_ADVANCED_HOOKS_POST_TYPE' ) || ASTRA_ADVANCED_HOOKS_POST_TYPE !== $post->post_type ) {
			return;
		}

		// Only transition TO publish.
		if ( 'publish' !== $new_status || 'publish' === $old_status ) {
			return;
		}

		$layout_type = get_post_meta( $post->ID, 'ast-advanced-hook-layout', true );
		$editor_type = get_post_meta( $post->ID, 'editor_type', true );

		$days_since_install = self::get_days_since_install();

		self::$events->track(
			'first_custom_layout_published',
			ASTRA_EXT_VER,
			array(
				'layout_type'        => sanitize_text_field( $layout_type ),
				'editor_type'        => ! empty( $editor_type ) ? sanitize_text_field( $editor_type ) : 'wordpress_editor',
				'days_since_install' => (string) $days_since_install,
			)
		);
	}

	/**
	 * Track first mega menu configuration.
	 *
	 * Fired by `updated_post_meta` / `added_post_meta` hooks when the
	 * `_menu_item_megamenu` meta is set to a truthy value on a nav menu item.
	 * One-time event — dedup handled by BSF_Analytics_Events::track().
	 *
	 * @param int    $meta_id    Meta ID.
	 * @param int    $post_id    Post ID.
	 * @param string $meta_key   Meta key.
	 * @param mixed  $meta_value Meta value.
	 * @since 4.12.5
	 * @return void
	 */
	public function track_first_mega_menu_configured( $meta_id, $post_id, $meta_key, $meta_value ) {
		if ( '_menu_item_megamenu' !== $meta_key ) {
			return;
		}

		if ( empty( $meta_value ) || '0' === $meta_value ) {
			return;
		}

		self::$events->track( 'first_mega_menu_configured', ASTRA_EXT_VER );
	}

	/**
	 * Track Pro module toggle.
	 *
	 * Fired by `update_option__astra_ext_enabled_extensions` hook when modules
	 * are activated or deactivated. Re-trackable since users can toggle modules
	 * multiple times.
	 *
	 * @param array $old_value Previous extensions array.
	 * @param array $new_value Updated extensions array.
	 * @since 4.12.5
	 * @return void
	 */
	public function track_pro_module_toggled( $old_value, $new_value ) {
		if ( ! is_array( $old_value ) || ! is_array( $new_value ) ) {
			return;
		}

		// Identify which modules changed.
		$toggled_modules = array();
		foreach ( $new_value as $module => $status ) {
			$old_status = isset( $old_value[ $module ] ) ? $old_value[ $module ] : false;
			if ( (bool) $old_status !== (bool) $status ) {
				$toggled_modules[] = sanitize_key( $module );
			}
		}

		if ( empty( $toggled_modules ) ) {
			return;
		}

		// Build cumulative snapshot of all module states.
		$properties = array();
		foreach ( $new_value as $module => $status ) {
			$properties[ sanitize_key( $module ) ] = ! empty( $status ) ? 'enabled' : 'disabled';
		}

		// Add which modules were just toggled.
		$properties['toggled_modules'] = $toggled_modules;

		self::$events->track(
			'pro_module_toggled',
			ASTRA_EXT_VER,
			$properties,
			true
		);
	}

	/**
	 * Track Pro addon version update as a re-trackable event.
	 *
	 * Fired by `astra_addon_update_after` hook after the background updater
	 * completes migrations.
	 *
	 * @param string $previous_version The addon version before the update.
	 * @since 4.12.5
	 * @return void
	 */
	public function track_pro_addon_updated( $previous_version ) {
		if ( empty( $previous_version ) || ASTRA_EXT_VER === $previous_version ) {
			return;
		}

		self::$events->track(
			'pro_addon_updated',
			ASTRA_EXT_VER,
			array( 'from_version' => $previous_version ),
			true
		);
	}

	/**
	 * Track Pro license activation.
	 *
	 * Fired by `bsf_activate_license_{$product_id}_after_success` hook.
	 * Re-trackable since a license can be reactivated after deactivation.
	 *
	 * @since 4.12.5
	 * @return void
	 */
	public function track_pro_license_activated() {
		self::$events->track( 'pro_license_activated', ASTRA_EXT_VER, array(), true );
	}

	/**
	 * Track Pro license deactivation.
	 *
	 * Fired by `bsf_deactivate_license_{$product_id}_after_success` hook.
	 * Re-trackable since a license can be toggled multiple times.
	 *
	 * @since 4.12.5
	 * @return void
	 */
	public function track_pro_license_deactivated() {
		self::$events->track( 'pro_license_deactivated', ASTRA_EXT_VER, array(), true );
	}

	/**
	 * Track beta updates toggle.
	 *
	 * Fired by `update_option__astra_beta_updates` hook.
	 * Re-trackable since users can toggle beta updates multiple times.
	 *
	 * @param string $old_value Previous value.
	 * @param string $new_value Updated value.
	 * @since 4.12.5
	 * @return void
	 */
	public function track_beta_updates_toggled( $old_value, $new_value ) {
		if ( $old_value === $new_value ) {
			return;
		}

		self::$events->track(
			'beta_updates_toggled',
			ASTRA_EXT_VER,
			array( 'enabled' => 'enable' === $new_value ? 'yes' : 'no' ),
			true
		);
	}

	/**
	 * Track file generation toggle.
	 *
	 * Fired by `update_option__astra_file_generation` hook.
	 * Re-trackable since users can toggle file generation multiple times.
	 *
	 * @param string $old_value Previous value.
	 * @param string $new_value Updated value.
	 * @since 4.12.5
	 * @return void
	 */
	public function track_file_generation_toggled( $old_value, $new_value ) {
		if ( $old_value === $new_value ) {
			return;
		}

		self::$events->track(
			'file_generation_toggled',
			ASTRA_EXT_VER,
			array( 'enabled' => 'enable' === $new_value ? 'yes' : 'no' ),
			true
		);
	}

	/**
	 * Track white label settings update.
	 *
	 * Fired by `update_option__astra_ext_white_label` hook.
	 * Re-trackable since white label settings can be changed multiple times.
	 *
	 * @param array $old_value Previous white label settings.
	 * @param array $new_value Updated white label settings.
	 * @since 4.12.5
	 * @return void
	 */
	public function track_white_label_toggled( $old_value, $new_value ) {
		if ( ! is_array( $new_value ) ) {
			return;
		}

		$is_configured = false;
		if ( ! empty( $new_value['astra-agency']['author'] ) || ! empty( $new_value['astra-agency']['author_url'] ) || ! empty( $new_value['astra-agency']['hide_branding'] ) ) {
			$is_configured = true;
		}
		if ( ! empty( $new_value['astra']['name'] ) || ! empty( $new_value['astra-pro']['name'] ) ) {
			$is_configured = true;
		}

		self::$events->track(
			'white_label_toggled',
			ASTRA_EXT_VER,
			array( 'enabled' => $is_configured ? 'yes' : 'no' ),
			true
		);
	}

	// ============================================
	// Helper Methods
	// ============================================

	/**
	 * Get days since Astra was installed.
	 *
	 * @since 4.12.5
	 * @return int Number of days since install.
	 */
	private static function get_days_since_install() {
		$install_time = get_site_option( 'astra_usage_installed_time', 0 );
		if ( $install_time > 0 ) {
			return (int) floor( ( time() - $install_time ) / DAY_IN_SECONDS );
		}
		return 0;
	}

	/**
	 * Adds Astra Addon specific deactivation survey data to the existing array.
	 *
	 * @param array $deactivation_data Existing deactivation survey data.
	 *
	 * @since 4.10.0
	 * @return array Updated array including Astra Addon deactivation survey data.
	 */
	public function addon_deactivation_survey_data( $deactivation_data ) {
		$deactivation_data[] = array(
			'id'                => 'deactivation-survey-astra-addon',
			'popup_logo'        => ASTRA_THEME_URI . 'inc/assets/images/astra-logo.svg',
			'plugin_slug'       => 'astra-addon',
			'popup_title'       => __( 'Quick Feedback', 'astra-addon' ),
			'support_url'       => 'https://wpastra.com/contact/',
			'popup_description' => __( 'If you have a moment, please share why you are deactivating Astra Pro:', 'astra-addon' ),
			'show_on_screens'   => array( 'plugins' ),
			'plugin_version'    => ASTRA_EXT_VER,
		);

		return $deactivation_data;
	}

	/**
	 * Initiator.
	 *
	 * @since 4.10.0
	 * @return self initialized object of class.
	 */
	public static function get_instance() {
		if ( null === self::$instance ) {
			self::$instance = new self();
		}
		return self::$instance;
	}
}

/**
 * Initiates the Astra_Addon_BSF_Analytics class instance.
 */
Astra_Addon_BSF_Analytics::get_instance();
