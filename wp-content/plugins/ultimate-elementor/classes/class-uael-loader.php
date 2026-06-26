<?php
/**
 * UAEL Loader.
 *
 * @package UAEL
 */

use UltimateElementor\Classes\UAEL_Helper;
if ( ! class_exists( 'UAEL_Loader' ) ) {

	/**
	 * Class UAEL_Loader.
	 */
	final class UAEL_Loader {

		/**
		 * Member Variable
		 *
		 * @var instance
		 */
		private static $instance;

		/**
		 *  Initiator
		 */
		public static function get_instance() {
			if ( ! isset( self::$instance ) ) {
				self::$instance = new self();
			}
			return self::$instance;
		}

		/**
		 * Constructor
		 */
		public function __construct() {

			// Activation hook.
			register_activation_hook( UAEL_FILE, array( $this, 'activation_reset' ) );

			// deActivation hook.
			register_deactivation_hook( UAEL_FILE, array( $this, 'deactivation_reset' ) );

			$this->define_constants();

			add_action( 'plugins_loaded', array( $this, 'load_plugin' ) );

			// Filter to change Astra menu positon.
			add_filter( 'astra_menu_priority', array( $this, 'update_admin_menu_position' ) );

			// Hook the load_textdomain function to the init action.
			add_action( 'init', array( $this, 'load_textdomain' ) );
		}

		/**
		 * Update Astra's menu priority to show after Dashboard menu.
		 *
		 * @param int $menu_priority top level menu priority.
		 */
		public function update_admin_menu_position( $menu_priority ) {
			return 2.1;
		}

		/**
		 * Save daily KPI snapshot when widget usage data is refreshed.
		 *
		 * Stores today's metrics keyed by date. Called alongside the daily
		 * widget usage transient refresh — no separate cron needed.
		 *
		 * @param array $widgets_usage Fresh widget usage data.
		 * @since 1.43.1
		 * @return void
		 */
		public function save_kpi_snapshot( $widgets_usage ) {
			$today     = current_time( 'Y-m-d' );
			$snapshots = get_option( 'uael_kpi_daily_snapshots', array() );

			$total_instances = 0;

			if ( is_array( $widgets_usage ) ) {
				foreach ( $widgets_usage as $slug => $count ) {
					$total_instances += (int) $count;
				}
			}

			$unique_widgets = is_array( $widgets_usage ) ? count( array_filter( $widgets_usage ) ) : 0;

			$snapshots[ $today ] = array(
				'numeric_values' => array(
					'total_widget_instances' => $total_instances,
					'unique_widgets_used'    => $unique_widgets,
				),
			);

			// Keep only the last 3 days of snapshots.
			krsort( $snapshots );
			$snapshots = array_slice( $snapshots, 0, 3, true );

			update_option( 'uael_kpi_daily_snapshots', $snapshots, false );
		}

		/**
		 * Defines all constants
		 *
		 * @since 0.0.1
		 */
		public function define_constants() {
			define( 'UAEL_BASE', plugin_basename( UAEL_FILE ) );
			define( 'UAEL_DIR', plugin_dir_path( UAEL_FILE ) );
			define( 'UAEL_URL', plugins_url( '/', UAEL_FILE ) );
			define( 'UAEL_VER', '1.44.4' );
			define( 'UAEL_MODULES_DIR', UAEL_DIR . 'modules/' );
			define( 'UAEL_MODULES_URL', UAEL_URL . 'modules/' );
			define( 'UAEL_SLUG', 'uae' );
			define( 'UAEL_CATEGORY', 'Ultimate Addons Pro' );
			define( 'UAEL_DOMAIN', trailingslashit( 'https://ultimateelementor.com' ) );
			define( 'UAEL_FACEBOOK_GRAPH_API_ENDPOINT', trailingslashit( 'https://graph.facebook.com/v2.12' ) );
			define( 'UAEL_BSF_PACKAGE', file_exists( UAEL_DIR . 'class-brainstorm-update-uael.php' ) );
			define( 'UAEL_PRO', true );
		}

		/**
		 * Loads plugin files.
		 *
		 * @since 0.0.1
		 *
		 * @return void
		 */
		public function load_plugin() {

			if ( ! did_action( 'elementor/loaded' ) ) {
				/* TO DO */
				add_action( 'admin_notices', array( $this, 'uael_fails_to_load' ) );
				add_action( 'network_admin_notices', array( $this, 'uael_fails_to_load' ) );
				return;
			}

			$required_elementor_version = '3.5.0';

			if ( defined( 'ELEMENTOR_VERSION' ) && ( ! version_compare( ELEMENTOR_VERSION, $required_elementor_version, '>=' ) ) ) {
				add_action( 'admin_notices', array( $this, 'elementor_outdated' ) );
				add_action( 'network_admin_notices', array( $this, 'elementor_outdated' ) );
				return;
			}

			if ( ! defined( 'FS_CHMOD_FILE' ) ) {
				define( 'FS_CHMOD_FILE', ( fileperms( ABSPATH . 'index.php' ) & 0777 | 0644 ) );
			}

			if ( ! function_exists( 'get_plugins' ) ) {
				require_once ABSPATH . 'wp-admin/includes/plugin.php';
			}
			
			
			if ( is_admin() && ! wp_doing_ajax() ) {
				require_once UAEL_DIR . 'class-brainstorm-update-uael.php';
				require_once UAEL_DIR . 'classes/class-uael-update.php';

				/**
				 * Load Post Duplicator only if UAE Lite is not active.
				 * UAE_LITE (defined as `true`) is set by header-footer-elementor.php at
				 * file-load time — before plugins_loaded fires — so this check is reliable
				 * regardless of plugin activation order.
				 * When UAE Lite is active it owns the Duplicator; Pro defers to it entirely.
				 *
				 * This feature is opt-in: it loads only when the admin has explicitly enabled
				 * Post_Duplicator from the UAE settings page. On a fresh install the
				 * _uael_widgets option will not contain this key, so the feature stays off
				 * until the user enables it. This is intentional — default is false in config.
				 */
				if ( ! defined( 'UAE_LITE' ) ) {
					$uael_widgets = get_option( '_uael_widgets', array() );
					if ( isset( $uael_widgets['Post_Duplicator'] ) && 'disabled' !== $uael_widgets['Post_Duplicator'] ) {
						require_once UAEL_DIR . 'modules/post-duplicator/class-uael-post-duplicator.php';
						UAEL_Post_Duplicator::instance();
					}
				}
			}
			
			// Hook the onboarding redirect function to admin_init action.
			add_action( 'admin_init', __CLASS__ . '::uael_redirect_to_onboarding' );

			add_action( 'admin_notices', __CLASS__ . '::hide_admin_notices', 1 );
			add_action( 'all_admin_notices', __CLASS__ . '::hide_admin_notices', 1 );

			// Load the NPS Survey library.
			if ( ! class_exists( 'Uae_Pro_Nps_Survey' ) ) {
				require_once UAEL_DIR . 'lib/class-uae-pro-nps-survey.php';
			}

			require_once UAEL_DIR . 'lib/astra-notices/class-bsf-admin-notices.php';
			
			// Always register cron hooks so WP-Cron callbacks fire in non-admin context.
			if ( 'yes' === get_option( 'uae_usage_optin', false ) ) {
				add_action( 'init', array( $this, 'uael_schedule_usage_cron' ) );
				add_action( 'uael_widgets_usage_cron', array( $this, 'uae_check_widgets_data_usage' ) );
			}

			// Analytics only needed in admin — avoids unnecessary DB reads on frontend.
			if ( is_admin() ) {
				if ( ! class_exists( 'BSF_Analytics_Loader' ) ) {
					require_once UAEL_DIR . 'admin/bsf-analytics/class-bsf-analytics-loader.php';
				}
				require_once UAEL_DIR . 'classes/class-uael-analytics.php';
			}
			require_once UAEL_DIR . 'classes/class-uael-core-plugin.php';
			require_once UAEL_DIR . 'includes/admin/settings-api.php';

			// Load Command Palette integration (WordPress 6.9+).
			if ( is_admin() ) {
				require_once UAEL_DIR . 'classes/class-uael-command-palette.php';
			}

			// License activation notice — shown when UAEL license is inactive.
			if ( is_admin() ) {
				require_once UAEL_DIR . 'classes/class-uael-license-notice.php';
			}

			// Register hooks for modal popup tracking.
			add_action( 'wp_footer', array( $this, 'uael_global_page_view_tracking' ) );
			add_action( 'elementor/widget/before_render_content', array( $this, 'uael_check_for_modal_popups' ) );
		}
		
		/**
		 * Schedule the daily cron event for widget usage data collection.
		 *
		 * @since 1.44.1
		 * @return void
		 */
		public function uael_schedule_usage_cron() {
			if ( ! wp_next_scheduled( 'uael_widgets_usage_cron' ) ) {
				wp_schedule_event( time(), 'daily', 'uael_widgets_usage_cron' );
			}
		}

		/**
		 * Collect widgets usage data via WP-Cron.
		 *
		 * Runs in a background cron context instead of on every page load,
		 * preventing timeouts on content-heavy sites.
		 *
		 * @since 1.39.3
		 * @since 1.44.1 Moved from shutdown hook to WP-Cron.
		 */
		public function uae_check_widgets_data_usage() {
			$transient_key = 'uaepro_widgets_usage_data';
			$widgets_usage = get_transient( $transient_key );

			$kpi_snapshots        = get_option( 'uael_kpi_daily_snapshots', false );
			$needs_kpi_init       = false === $kpi_snapshots;
			$needs_widget_refresh = false === $widgets_usage || false === get_option( 'uaepro_widgets_usage_data_option' );

			if ( $needs_widget_refresh || $needs_kpi_init ) {
				$filtered_widgets_usage = UAEL_Helper::uaepro_get_used_widget();
				set_transient( $transient_key, $filtered_widgets_usage, DAY_IN_SECONDS );
				update_option( 'uaepro_widgets_usage_data_option', $filtered_widgets_usage );

				$this->save_kpi_snapshot( $filtered_widgets_usage );
			}
		}

		/**
		 * Onboarding redirect function.
		 */
		public static function uael_redirect_to_onboarding() {

			if ( ! get_option( 'uaepro_start_onboarding', false ) ) {
				return;
			}

			$is_onboarding_triggered = ( 'yes' === get_option( 'uaepro_onboarding_triggered' ) ) ? true : false;
			$is_lite_triggered       = ( 'yes' === get_option( 'hfe_onboarding_triggered' ) ) ? true : false; // Check if onboarding is already triggered for lite version.
			$show_onboarding         = ( 'yes' === get_option( 'uaepro_show_onboarding' ) ) ? true : false; // Check if old user.
			
			// IMPORTANT: Comment out this code before release - Show onboarding only for new users only once.
			if ( $is_onboarding_triggered || $is_lite_triggered || ! $show_onboarding ) {
				return;
			}
			
			delete_option( 'uaepro_start_onboarding' );

			if ( ! defined( 'WP_CLI' ) || ! WP_CLI ) {
				update_option( 'uaepro_onboarding_triggered', 'yes' );
				wp_safe_redirect( admin_url( 'admin.php?page=uaepro#onboarding' ) );
				exit();
			}
		}

		/**
		 * Hide admin notices on the custom settings page.
		 *
		 * @since 1.37.0
		 * @return void
		 */
		public static function hide_admin_notices() {
			$screen = get_current_screen();
			if ( 'toplevel_page_uaepro' === $screen->id || 'edit-elementor-hf' === $screen->id ) {
				remove_all_actions( 'admin_notices' );
				remove_all_actions( 'all_admin_notices' );
			}
		}

		/**
		 * Load Ultimate Elementor Text Domain.
		 * This will load the translation textdomain depending on the file priorities.
		 *      1. Global Languages /wp-content/languages/ultimate-elementor/ folder
		 *      2. Local directory /wp-content/plugins/ultimate-elementor/languages/ folder
		 *
		 * @since  1.0.0
		 * @return void
		 */
		public function load_textdomain() {
			// Default languages directory for "ultimate-elementor".
			$lang_dir = UAEL_DIR . 'languages/';

			/**
			 * Filters the languages directory path to use for AffiliateWP.
			 *
			 * @param string $lang_dir The languages directory path.
			 */
			$lang_dir = apply_filters( 'uael_languages_directory', $lang_dir );

			// Traditional WordPress plugin locale filter.
			global $wp_version;

			$get_locale = get_locale();

			if ( $wp_version >= 4.7 ) {
				$get_locale = get_user_locale();
			}

			/**
			 * Language Locale for Ultimate Elementor
			 *
			 * @var $get_locale The locale to use. Uses get_user_locale()` in WordPress 4.7 or greater,
			 *                  otherwise uses `get_locale()`.
			 */
			$locale = apply_filters( 'plugin_locale', $get_locale, 'uael' );
			$mofile = sprintf( '%1$s-%2$s.mo', 'uael', $locale );

			// Setup paths to current locale file.
			$mofile_local  = $lang_dir . $mofile;
			$mofile_global = WP_LANG_DIR . '/ultimate-elementor/' . $mofile;

			if ( file_exists( $mofile_global ) ) {
				// Look in global /wp-content/languages/ultimate-elementor/ folder.
				load_textdomain( 'uael', $mofile_global );
			} elseif ( file_exists( $mofile_local ) ) {
				// Look in local /wp-content/plugins/ultimate-elementor/languages/ folder.
				load_textdomain( 'uael', $mofile_local );
			} else {
				// Load the default language files.
				load_plugin_textdomain( 'uael', false, $lang_dir );
			}
		}
		/**
		 * Fires admin notice when Elementor is not installed and activated.
		 *
		 * @since 0.0.1
		 *
		 * @return void
		 */
		public function uael_fails_to_load() {

			$screen = get_current_screen();
			if ( isset( $screen->parent_file ) && 'plugins.php' === $screen->parent_file && 'update' === $screen->id ) {
				return;
			}

			$class = 'notice notice-error';
			/* translators: %s: html tags */
			$message = sprintf( __( 'The %1$sUltimate Addons for Elementor Pro%2$s plugin requires %1$sElementor%2$s plugin installed & activated.', 'uael' ), '<strong>', '</strong>' );

			$plugin = 'elementor/elementor.php';

			if ( _is_elementor_installed() ) {
				if ( ! current_user_can( 'activate_plugins' ) ) {
					return;
				}

				$action_url   = wp_nonce_url( 'plugins.php?action=activate&amp;plugin=' . $plugin . '&amp;plugin_status=all&amp;paged=1&amp;s', 'activate-plugin_' . $plugin );
				$button_label = __( 'Activate Elementor', 'uael' );

			} else {
				if ( ! current_user_can( 'install_plugins' ) ) {
					return;
				}

				$action_url   = wp_nonce_url( self_admin_url( 'update.php?action=install-plugin&plugin=elementor' ), 'install-plugin_elementor' );
				$button_label = __( 'Install Elementor', 'uael' );
			}

			$button = '<a href="' . $action_url . '" class="button-primary" style="margin-left: 10px; margin-left: 10px;">' . $button_label . '</a>';
		
			printf( '<div class="%1$s" style="display: flex; align-items: center; padding: 15px 0;"><p style="margin: 0; padding-left: 10px; padding-right: 10px;">%2$s</p>%3$s</div>', esc_attr( $class ), wp_kses_post( $message ), wp_kses_post( $button ) );
		}


		/**
		 * Fires admin notice when Elementor version is outdated.
		 *
		 * @since 1.30.1
		 *
		 * @return void
		 */
		public function elementor_outdated() {
			$class = 'notice notice-error';
			/* translators: %s: html tags */
			$message = sprintf( __( 'The %1$sUltimate Addons for Elementor Pro%2$s plugin has stopped working because you are using an older version of %1$sElementor%2$s plugin.', 'uael' ), '<strong>', '</strong>' );

			$plugin = 'elementor/elementor.php';

			if ( _is_elementor_installed() ) {
				if ( ! current_user_can( 'install_plugins' ) ) {
					return;
				}

				$action_url = wp_nonce_url( self_admin_url( 'update.php?action=upgrade-plugin&amp;plugin=' ) . $plugin . '&amp;', 'upgrade-plugin_' . $plugin );

				$button_label = __( 'Update Elementor', 'uael' );

			} else {
				if ( ! current_user_can( 'install_plugins' ) ) {
					return;
				}

				$action_url   = wp_nonce_url( self_admin_url( 'update.php?action=install-plugin&plugin=elementor' ), 'install-plugin_elementor' );
				$button_label = __( 'Install Elementor', 'uael' );
			}

			$button = '<p><a href="' . $action_url . '" class="button-primary">' . $button_label . '</a></p><p></p>';

			printf( '<div class="%1$s"><p>%2$s</p>%3$s</div>', esc_attr( $class ), wp_kses_post( $message ), wp_kses_post( $button ) );
		}

		/**
		 * Activation Reset
		 */
		public function activation_reset() {

			update_option( 'uaepro_start_onboarding', true );

			// Default analytics consent to yes for new users.
			if ( false === get_option( 'uae_usage_optin', false ) ) {
				update_option( 'uae_usage_optin', 'yes' );
			}

			// Track plugin activation event.
			if ( ! class_exists( 'UAEL_Analytics_Events' ) ) {
				require_once UAEL_DIR . 'classes/class-uael-analytics-events.php';
			}
			$bsf_referrers = get_option( 'bsf_product_referers', array() );
			$source        = ! empty( $bsf_referrers['ultimate-elementor'] ) ? $bsf_referrers['ultimate-elementor'] : 'self';
			UAEL_Analytics_Events::track( 'plugin_activated', UAEL_VER, array( 'source' => $source ) );

			// Force check graupi bundled products.
			update_site_option( 'bsf_force_check_extensions', true );

			if ( is_network_admin() ) {
				$branding = get_site_option( '_uael_white_label' );
			} else {
				$branding = get_option( '_uael_white_label' );
			}

			if ( isset( $branding['agency']['hide_branding'] ) && false !== $branding['agency']['hide_branding'] ) {

				$branding['agency']['hide_branding'] = false;

				if ( is_network_admin() ) {

					update_site_option( '_uael_white_label', $branding );

				} else {
					update_option( '_uael_white_label', $branding );
				}
			}
		}

		/**
		 * Deactivation Reset
		 */
		public function deactivation_reset() {
			$timestamp = wp_next_scheduled( 'uael_widgets_usage_cron' );
			if ( $timestamp ) {
				wp_unschedule_event( $timestamp, 'uael_widgets_usage_cron' );
			}
		}

		/**
		 * Global Page View Tracking for Modal Popup Feature
		 * This function tracks page views globally and per page for modal popups.
		 *
		 * It uses localStorage to store the view counts and logs debug information if enabled.
		 */
		public function uael_global_page_view_tracking() {
			// Only run on frontend, not in admin or during AJAX requests.
			if ( is_admin() || wp_doing_ajax() || wp_doing_cron() ) {
				return;
			}
			
			// Check if any modal popup widgets exist on the site (to avoid unnecessary tracking).
			$has_modal_popups = get_option( 'uael_has_modal_popups', false );
			
			// If we haven't checked yet or if there are modal popups, add the tracking script.
			if ( false === $has_modal_popups || $has_modal_popups ) {
				// Check if any popup on the site uses global tracking.
				$has_global_tracking  = get_option( 'uael_has_global_tracking', false );
				$has_session_tracking = get_option( 'uael_has_session_tracking', false );
				?>
				<script type="text/javascript">
				(function() {
					// Global page view and session tracking for UAEL Modal Popup feature
					try {
						// Session tracking: increment if this is a new session
						<?php if ( $has_session_tracking ) : ?>
						var activeSessionKey = 'uael_active_session';
						var isActiveSession = sessionStorage.getItem(activeSessionKey);
						if (null === isActiveSession) {
							// This is a new session, increment the counter
							var currentSessions = parseInt(localStorage.getItem('uael_sessions') || '0');
							currentSessions++;
							localStorage.setItem('uael_sessions', currentSessions.toString());
							// Mark this session as active
							sessionStorage.setItem(activeSessionKey, 'true');
						}
						<?php endif; ?>

						// Check if any popup on this page uses current page tracking
						var hasCurrentPageTracking = false;
						var currentPagePopups = [];
						// Check all modal popups on this page for current page tracking
						if (typeof jQuery !== 'undefined') {
							jQuery('.uael-modal-parent-wrapper').each(function() {
								var scope = jQuery(this).data('page-views-scope');
								var enabled = jQuery(this).data('page-views-enabled');
								var popupId = jQuery(this).attr('id').replace('-overlay', '');	
								if (enabled === 'yes' && scope === 'current') {
									hasCurrentPageTracking = true;
									currentPagePopups.push(popupId);
								}
							});
						}
						// Global tracking: ALWAYS increment if ANY popup on the site uses global tracking
						<?php if ( $has_global_tracking ) : ?>
						var currentViews = parseInt(localStorage.getItem('uael_page_views') || '0');
						currentViews++;
						localStorage.setItem('uael_page_views', currentViews.toString());
						<?php endif; ?>
						// Current page tracking: increment per-page counters
						if (hasCurrentPageTracking && currentPagePopups.length > 0) {
							var currentUrl = window.location.href;
							var urlKey = 'uael_page_views_' + btoa(currentUrl).replace(/[^a-zA-Z0-9]/g, '').substring(0, 50);
							var currentPageViews = parseInt(localStorage.getItem(urlKey) || '0');
							currentPageViews++;
							localStorage.setItem(urlKey, currentPageViews.toString());
							// Store URL mapping for each popup
							for (var i = 0; i < currentPagePopups.length; i++) {
								var popupUrlKey = 'uael_popup_' + currentPagePopups[i] + '_url_key';
								localStorage.setItem(popupUrlKey, urlKey);
							}
						}
					} catch (e) {
						// Silently fail if localStorage is not available
					}
				})();
				</script>
				<?php
			}
		}

		/**
		 * Optimize by checking if site has modal popup widgets (optimization)
		 *
		 * @param object $widget The widget object.
		 */
		public function uael_check_for_modal_popups( $widget ) {
			if ( 'uael-modal-popup' === $widget->get_name() ) {
				update_option( 'uael_has_modal_popups', true );
				
				// Check if this popup uses global tracking.
				$settings           = $widget->get_settings_for_display();
				$page_views_enabled = isset( $settings['enable_page_views'] ) ? $settings['enable_page_views'] : 'no';
				$tracking_scope     = isset( $settings['page_views_scope'] ) ? $settings['page_views_scope'] : 'global';
				$sessions_enabled   = isset( $settings['enable_sessions'] ) ? $settings['enable_sessions'] : 'no';
				
				if ( 'yes' === $page_views_enabled && 'global' === $tracking_scope ) {
					update_option( 'uael_has_global_tracking', true );
				}
				
				if ( 'yes' === $sessions_enabled ) {
					update_option( 'uael_has_session_tracking', true );
				}
			}
		}
	}

	/**
	 *  Prepare if class 'UAEL_Loader' exist.
	 *  Kicking this off by calling 'get_instance()' method
	 */
	UAEL_Loader::get_instance();
}

/**
 * Is elementor plugin installed.
 */
if ( ! function_exists( '_is_elementor_installed' ) ) {

	/**
	 * Check if Elementor Pro is installed
	 *
	 * @since 1.0.0
	 *
	 * @access public
	 */
	function _is_elementor_installed() {
		$path    = 'elementor/elementor.php';
		$plugins = get_plugins();

		return isset( $plugins[ $path ] );
	}
}

/**
 * Is WPML String Translation is active.
 */
if ( ! function_exists( 'is_wpml_string_translation_active' ) ) {

	/**
	 * Check if WPML String Translation plugin is active.
	 *
	 * @since 1.2.0
	 */
	function is_wpml_string_translation_active() {
		include_once ABSPATH . 'wp-admin/includes/plugin.php';

		return is_plugin_active( 'wpml-string-translation/plugin.php' );
	}
}
