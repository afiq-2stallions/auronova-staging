<?php
/**
 * UAEL Analytics.
 *
 * @package UAEL
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

if ( ! class_exists( 'UAEL_Analytics' ) ) {
	/**
	 * Class UAEL_Analytics
	 *
	 * Handles analytics-related functionality for the Ultimate Addons for Elementor plugin.
	 *
	 * @since 1.39.3
	 */
	class UAEL_Analytics {

		/**
		 * UAEL Analytics constructor.
		 *
		 * Initializing UAEL Analytics.
		 *
		 * @since 1.39.3
		 * @access public
		 */
		public function __construct() {
			add_action( 'admin_init', array( $this, 'maybe_migrate_analytics_tracking' ) );

			// Load analytics events class.
			if ( ! class_exists( 'UAEL_Analytics_Events' ) ) {
				require_once UAEL_DIR . 'classes/class-uael-analytics-events.php';
			}

			// BSF Analytics Tracker.
			if ( ! class_exists( 'BSF_Analytics_Loader' ) ) {
				require_once UAEL_DIR . 'admin/bsf-analytics/class-bsf-analytics-loader.php';
			}

			$bsf_analytics = BSF_Analytics_Loader::get_instance();

			$bsf_analytics->set_entity(
				array(
					'uae' => array(
						'product_name'        => 'Ultimate Addons for Elementor Pro',
						'path'                => UAEL_DIR . 'admin/bsf-analytics',
						'author'              => 'Ultimate Addons for Elementor',
						'time_to_display'     => '+24 hours',
						'deactivation_survey' => array(
							array(
								'id'                => 'deactivation-survey-ultimate-elementor', // 'deactivation-survey-<your-plugin-slug>'
								'popup_logo'        => UAEL_URL . 'assets/images/settings/logo.svg',
								'plugin_slug'       => 'ultimate-elementor', // <your-plugin-slug>
								'plugin_version'    => UAEL_VER,
								'popup_title'       => 'Quick Feedback',
								'support_url'       => 'https://ultimateelementor.com/contact/',
								'popup_description' => 'If you have a moment, please share why you are deactivating Ultimate Addons for Elementor Pro:',
								'show_on_screens'   => array( 'plugins' ),
							),
						),
						'hide_optin_checkbox' => true,
					),
				)
			);

			add_filter( 'bsf_core_stats', array( $this, 'add_uae_analytics_data' ) );

			// Real-time first widget detection on Elementor save.
			if ( ! UAEL_Analytics_Events::is_tracked( 'first_widget_used' ) ) {
				add_action( 'elementor/editor/after_save', array( $this, 'track_first_widget_on_save' ), 10, 2 );
			}

			// Detect state-based events only in admin context, throttled to once per day.
			if ( is_admin() && false === get_transient( 'uael_state_events_checked' ) ) {
				$this->detect_state_events();
				set_transient( 'uael_state_events_checked', 1, DAY_IN_SECONDS );
			}
		}

		/**
		 * Migrates analytics tracking option from 'bsf_usage_optin' to 'uae_usage_optin'.
		 *
		 * @since 1.39.8
		 * @access public
		 * @return void
		 */
		public function maybe_migrate_analytics_tracking() {
			// Skip if already migrated or new option already set.
			if ( false !== get_site_option( 'uae_usage_optin', false ) ) {
				return;
			}

			$old_tracking = get_site_option( 'bsf_usage_optin', false );
			if ( 'yes' === $old_tracking ) {
				update_site_option( 'uae_usage_optin', 'yes' );
				$time = get_site_option( 'bsf_usage_installed_time' );
				if ( false !== $time ) {
					update_site_option( 'uae_usage_installed_time', $time );
				}
			}
		}

		/**
		 * Callback function to add specific analytics data.
		 *
		 * @param array $stats_data existing stats_data.
		 * @since 1.39.3
		 * @return array
		 */
		public function add_uae_analytics_data( $stats_data ) {
			$stats_data['plugin_data']['uae'] = array(
				'free_version'          => ( defined( 'HFE_VER' ) ? HFE_VER : '' ),
				'pro_version'           => UAEL_VER,
				'site_language'         => get_locale(),
				'elementor_version'     => ( defined( 'ELEMENTOR_VERSION' ) ? ELEMENTOR_VERSION : '' ),
				'elementor_pro_version' => ( defined( 'ELEMENTOR_PRO_VERSION' ) ? ELEMENTOR_PRO_VERSION : '' ),
				'onboarding_triggered'  => ( 'yes' === get_option( 'uaepro_onboarding_triggered' ) ) ? 'yes' : 'no',
				'active_theme'          => get_template(),
				'is_white_labeled'      => $this->is_white_labeled(),
				'onboarding_analytics'  => get_option( 'uaepro_onboarding_analytics', array() ),
			);

			$fetch_elementor_data = $this->uael_get_widgets_usage();
			foreach ( $fetch_elementor_data as $key => $value ) {
				$stats_data['plugin_data']['uae']['numeric_values'][ $key ] = $value;
			}

			// Add KPI tracking data.
			$kpi_data = $this->get_kpi_tracking_data();
			if ( ! empty( $kpi_data ) ) {
				$stats_data['plugin_data']['uae']['kpi_records'] = $kpi_data;
			}

			// Flush pending events into payload (only if any exist).
			$pending_events = UAEL_Analytics_Events::flush_pending();
			if ( ! empty( $pending_events ) ) {
				$stats_data['plugin_data']['uae']['events_record'] = $pending_events;
			}
			return $stats_data;
		}

		/**
		 * Track first UAE Pro widget usage on Elementor post save.
		 *
		 * Fires on elementor/editor/after_save. Checks if the saved post
		 * contains any UAE Pro widget (uael- prefix) and tracks the
		 * first_widget_used event immediately instead of waiting for the
		 * daily cron scan.
		 *
		 * @since 1.44.3
		 * @param int   $post_id     Post ID.
		 * @param array $editor_data Elementor editor data.
		 * @return void
		 */
		public function track_first_widget_on_save( $post_id, $editor_data ) {
			// Skip if already tracked — zero overhead after first detection.
			if ( UAEL_Analytics_Events::is_tracked( 'first_widget_used' ) ) {
				return;
			}

			$elementor_data = get_post_meta( $post_id, '_elementor_data', true );
			if ( empty( $elementor_data ) ) {
				return;
			}

			// _elementor_data is normally a JSON string, but some setups return it
			// as an array. preg_match() is strictly typed on PHP 8+, so coerce to string.
			if ( is_array( $elementor_data ) ) {
				$elementor_data = wp_json_encode( $elementor_data );
			}

			if ( ! is_string( $elementor_data ) || '' === $elementor_data ) {
				return;
			}

			// Extract the first UAE Pro widget name from Elementor data.
			$first_widget = '';
			if ( preg_match( '/"widgetType":"(uael-[^"]+)"/', $elementor_data, $matches ) ) {
				$first_widget = $matches[1];
			}

			if ( empty( $first_widget ) ) {
				return;
			}

			$install_time       = get_option( 'uae_usage_installed_time', 0 );
			$days_since_install = 0;
			if ( $install_time > 0 ) {
				$days_since_install = (int) floor( ( time() - (int) $install_time ) / DAY_IN_SECONDS );
			}

			UAEL_Analytics_Events::track(
				'first_widget_used',
				$first_widget,
				array(
					'days_since_install' => (string) $days_since_install,
				)
			);
		}

		/**
		 * Detect state-based events that can't use direct hooks.
		 * Uses dedup in UAEL_Analytics_Events::track() — safe to call repeatedly.
		 *
		 * @since 1.44.2
		 * @return void
		 */
		private function detect_state_events() {
			// Read pushed + pending once to avoid repeated get_option calls per event.
			$pushed  = get_option( 'uael_usage_events_pushed', array() );
			$pushed  = is_array( $pushed ) ? $pushed : array();
			$pending = get_option( 'uael_usage_events_pending', array() );
			$pending = is_array( $pending ) ? $pending : array();

			$tracked_names = array_merge( $pushed, array_column( $pending, 'event_name' ) );

			// onboarding_completed: detect completed or early-exit state from the analytics blob.
			if ( ! in_array( 'onboarding_completed', $tracked_names, true ) ) {
				$onboarding_analytics = get_option( 'uaepro_onboarding_analytics', array() );
				$onboarding_done      = 'yes' === get_option( 'uaepro_onboarding_triggered' );
				$onboarding_skipped   = ! empty( $onboarding_analytics['exited_early'] ) && empty( $onboarding_analytics['completed'] );

				if ( $onboarding_done || $onboarding_skipped ) {
					UAEL_Analytics_Events::track(
						'onboarding_completed',
						$onboarding_skipped ? 'no' : 'yes',
						array( 'skipped' => (string) (int) $onboarding_skipped )
					);
				}
			}

			// first_widget_used: tracked in real-time via elementor/editor/after_save hook.

			// white_label_enabled: fires once when white label branding is first configured.
			if ( ! in_array( 'white_label_enabled', $tracked_names, true ) ) {
				if ( $this->is_white_labeled() ) {
					$install_time       = get_option( 'uae_usage_installed_time', 0 );
					$days_since_install = 0;
					if ( $install_time > 0 ) {
						$days_since_install = (int) floor( ( time() - (int) $install_time ) / DAY_IN_SECONDS );
					}
					UAEL_Analytics_Events::track(
						'white_label_enabled',
						'yes',
						array( 'days_since_install' => (string) $days_since_install )
					);
				}
			}

			// display_conditions_used: fires once when display conditions are first configured on any element.
			if ( ! in_array( 'display_conditions_used', $tracked_names, true ) ) {
				if ( get_option( 'uael_display_conditions_ever_used', false ) ) {
					$install_time       = get_option( 'uae_usage_installed_time', 0 );
					$days_since_install = 0;
					if ( $install_time > 0 ) {
						$days_since_install = (int) floor( ( time() - (int) $install_time ) / DAY_IN_SECONDS );
					}
					UAEL_Analytics_Events::track(
						'display_conditions_used',
						'yes',
						array( 'days_since_install' => (string) $days_since_install )
					);
				}
			}

			// post_duplicator_used: fires once when the post duplicator feature is first used.
			if ( ! in_array( 'post_duplicator_used', $tracked_names, true ) ) {
				if ( (int) get_option( 'uae_duplicator_count', 0 ) > 0 ) {
					$install_time       = get_option( 'uae_usage_installed_time', 0 );
					$days_since_install = 0;
					if ( $install_time > 0 ) {
						$days_since_install = (int) floor( ( time() - (int) $install_time ) / DAY_IN_SECONDS );
					}
					UAEL_Analytics_Events::track(
						'post_duplicator_used',
						'yes',
						array( 'days_since_install' => (string) $days_since_install )
					);
				}
			}
		}

		/**
		 * Check if the plugin is white labeled.
		 *
		 * @since 1.44.2
		 * @return bool
		 */
		private function is_white_labeled() {
			$branding = get_option( '_uael_white_label', array() );
			return ! empty( $branding['plugin']['name'] );
		}

		/**
		 * Get KPI tracking data for the last 2 days (excluding today).
		 *
		 * Reads pre-recorded daily snapshots from the database.
		 * Each day's data was captured on that actual day via cron.
		 *
		 * @since 1.43.1
		 * @return array KPI data organized by date.
		 */
		private function get_kpi_tracking_data() {
			$snapshots = get_option( 'uael_kpi_daily_snapshots', array() );

			if ( empty( $snapshots ) || ! is_array( $snapshots ) ) {
				return array();
			}

			$kpi_data = array();
			$today    = current_time( 'Y-m-d' );

			// Only send data for dates that have actual per-day snapshots.
			for ( $i = 1; $i <= 2; $i++ ) {
				$date = wp_date( 'Y-m-d', strtotime( $today . ' -' . $i . ' days' ) );

				if ( ! isset( $snapshots[ $date ]['numeric_values'] ) ) {
					continue;
				}

				$kpi_data[ $date ] = $snapshots[ $date ];
			}

			return $kpi_data;
		}

		/**
		 * Fetch Elementor widget usage data.
		 *
		 * @since 1.39.3
		 * @return array Widget usage data.
		 */
		private function uael_get_widgets_usage() {
				$get_widgets = get_option( 'uaepro_widgets_usage_data_option', array() );
				return $get_widgets;
		}
	}
}
new UAEL_Analytics();
