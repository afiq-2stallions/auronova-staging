<?php
/**
 * UAEL Analytics Events — thin static wrapper around BSF_Analytics_Events.
 *
 * Delegates to the shared library class while preserving UAEL's existing
 * option keys (`uael_usage_events_pending`, `uael_usage_events_pushed`)
 * via the library's custom option resolver. This keeps all call sites
 * unchanged and avoids any data migration.
 *
 * @package UAEL
 * @since 1.44.2
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

if ( ! class_exists( 'UAEL_Analytics_Events' ) ) {

	/**
	 * UAEL Analytics Events Class.
	 *
	 * @since 1.44.2
	 */
	class UAEL_Analytics_Events {

		/**
		 * Cached BSF_Analytics_Events instance.
		 *
		 * @var \BSF_Analytics_Events|null
		 */
		private static $instance = null;

		/**
		 * Get the underlying BSF_Analytics_Events instance with UAEL's option resolver.
		 *
		 * Resolver maps library keys (`usage_events_pending`, `usage_events_pushed`)
		 * to UAEL's existing option names (`uael_usage_events_pending`, `uael_usage_events_pushed`).
		 * The pushed dedup flag is autoloaded since it's read on every admin page load.
		 *
		 * @since 1.44.3
		 * @return \BSF_Analytics_Events|null Instance, or null if library is unavailable.
		 */
		private static function instance() {
			if ( null !== self::$instance ) {
				return self::$instance;
			}

			if ( ! class_exists( 'BSF_Analytics_Events' ) ) {
				$lib_path = defined( 'UAEL_DIR' ) ? UAEL_DIR . 'admin/bsf-analytics/class-bsf-analytics-events.php' : '';
				if ( '' !== $lib_path && file_exists( $lib_path ) ) {
					require_once $lib_path;
				}
			}

			if ( ! class_exists( 'BSF_Analytics_Events' ) ) {
				return null;
			}

			self::$instance = new \BSF_Analytics_Events(
				'uael',
				array(
					'get'    => static function ( $key, $default ) {
						return get_option( 'uael_' . $key, $default );
					},
					'update' => static function ( $key, $value ) {
						$autoload = ( 'usage_events_pushed' === $key );
						update_option( 'uael_' . $key, $value, $autoload );
					},
				)
			);

			return self::$instance;
		}

		/**
		 * Track an event. See BSF_Analytics_Events::track() for behavior.
		 *
		 * @param string $event_name  Event identifier.
		 * @param string $event_value Primary value (version, mode, etc.).
		 * @param array  $properties  Additional context as key-value pairs.
		 * @param bool   $force       When true, bypass pushed dedup and overwrite pending entry.
		 * @since 1.44.2
		 * @since 1.44.3 Added the $force parameter.
		 * @return void
		 */
		public static function track( $event_name, $event_value = '', $properties = array(), $force = false ) {
			$events = self::instance();
			if ( null === $events ) {
				return;
			}
			$events->track( $event_name, $event_value, $properties, $force );
		}

		/**
		 * Flush pending events into payload, then clean up.
		 *
		 * @since 1.44.2
		 * @return array Pending events. Empty array if none or library unavailable.
		 */
		public static function flush_pending() {
			$events = self::instance();
			if ( null === $events ) {
				return array();
			}
			return $events->flush_pending();
		}

		/**
		 * Check if an event has already been tracked (sent or pending).
		 *
		 * @param string $event_name Event identifier.
		 * @since 1.44.2
		 * @return bool
		 */
		public static function is_tracked( $event_name ) {
			$events = self::instance();
			if ( null === $events ) {
				return false;
			}
			return $events->is_tracked( $event_name );
		}

		/**
		 * Remove specific event names from the pushed dedup flag, allowing them to be re-tracked.
		 * Empty array clears all pushed events.
		 *
		 * @param array<string> $event_names Event names to remove.
		 * @since 1.44.3
		 * @return void
		 */
		public static function flush_pushed( $event_names = array() ) {
			$events = self::instance();
			if ( null === $events ) {
				return;
			}
			$events->flush_pushed( $event_names );
		}
	}
}
