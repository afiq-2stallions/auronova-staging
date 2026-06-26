<?php
/**
 * MFM custom sensor.
 *
 * @package MFM
 * @since 2.0.0
 */

declare(strict_types=1);

namespace WSAL\Plugin_Sensors;

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

use WSAL\Controllers\Alert_Manager;
use MFM\Helpers\Events_Helper;
use MFM\Helpers\Settings_Helper;

if ( ! class_exists( '\WSAL\Plugin_Sensors\MFM_Sensor' ) ) {
	/**
	 * Custom sensor for MFM plugin.
	 *
	 * @since 2.0.0
	 */
	class MFM_Sensor {

		/**
		 * Listening to events using hooks.
		 * Here you can code your own custom sensors for triggering your custom events.
		 *
		 * @return void
		 *
		 * @since 2.0.0
		 */
		public static function init() {
			add_action( MFM_PREFIX . 'file_change_event_created', array( __CLASS__, 'file_change_event' ) );
			add_action( MFM_PREFIX . 'file_exceeded_size_event_created', array( __CLASS__, 'file_beyond_max_size' ) );
			add_action( MFM_PREFIX . 'setting_updated', array( __CLASS__, 'setting_updated' ), 10, 3 );
			add_action( MFM_PREFIX . 'scan_time_updated', array( __CLASS__, 'scan_time_updated' ), 10, 2 );
			add_action( MFM_PREFIX . 'setting_purged', array( __CLASS__, 'settings_purged' ) );
		}

		/**
		 * Trigger alert if setting has been updated.
		 *
		 * @param string $setting_name - Setting being updated.
		 * @param mixed  $old_value - Previous value.
		 * @param mixed  $new_value - New value.
		 *
		 * @return void
		 *
		 * @since 2.2.0
		 */
		public static function setting_updated( $setting_name, $old_value, $new_value ) {
			if ( 'scan-frequency' === $setting_name ) {
				$event_id  = 6073;
				$variables = array(
					'old' => ucfirst( $old_value ),
					'new' => ucfirst( $new_value ),
				);

				Alert_Manager::trigger_event( $event_id, $variables );

			} elseif ( 'core-scan-enabled' === $setting_name ) {
				$event_id  = 6075;
				$variables = array();
				if ( 'yes' === $new_value ) {
					$variables['EventType']        = 'enabled';
					$variables['Enabled_Disabled'] = 'Enabled';
				} else {
					$variables['EventType']        = 'disabled';
					$variables['Enabled_Disabled'] = 'Disabled';
				}

				Alert_Manager::trigger_event( $event_id, $variables );

			} elseif ( 'allowed-in-core-files' === $setting_name ) {
				$new_items = array_diff( $old_value, $new_value );
				$old_items = array_diff( $new_value, $old_value );
				foreach ( $new_items as $new ) {
					$variables              = array(
						'filename' => $new,
					);
					$variables['EventType'] = 'removed';
					Alert_Manager::trigger_event( 6077, $variables );
				}
				foreach ( $old_items as $old ) {
					$variables              = array(
						'filename' => $old,
					);
					$variables['EventType'] = 'added';

					Alert_Manager::trigger_event( 6076, $variables );
				}
			}
		}

		/**
		 * Trigger alert when scan time has fully updated.
		 *
		 * @param string $old_value - Previous value.
		 * @param string $new_value - New value.
		 *
		 * @return void
		 *
		 * @since 2.2.0
		 */
		public static function scan_time_updated( $old_value, $new_value ) {
			$event_id  = 6074;
			$variables = array(
				'old_time' => $old_value,
				'new_time' => $new_value,
			);

			if ( ! Alert_Manager::was_triggered_recently( $event_id ) ) {
				Alert_Manager::trigger_event( $event_id, $variables );
			}
		}

		/**
		 * Trigger alert when settings are purged.
		 *
		 * @return void
		 *
		 * @since 2.2.0
		 */
		public static function settings_purged() {
			$variables['EventType'] = 'purged';
			Alert_Manager::trigger_event( 6078, $variables );
		}

		/**
		 * Trigger a WSAL event.
		 *
		 * @param array $data - Event data.
		 *
		 * @return void
		 *
		 * @since 2.0.0
		 */
		public static function file_change_event( $data ) {
			if ( isset( $data['event_type'] ) ) {
				$editor_link = esc_url( admin_url( 'admin.php?page=file-monitor-admin' ) );
				$event_id    = 6030;

				$variables = array(
					'event_type'   => Events_Helper::create_event_type_label( $data['event_type'], 'all', true ),
					'RevisionLink' => $editor_link,
				);

				if ( 'file-scan-started' === $data['event_type'] || 'file-scan-complete' === $data['event_type'] || 'file-scan-aborted' === $data['event_type'] ) {
					$event_id = 6033;
				} elseif ( 'core-file-modified' === $data['event_type'] ) {
					$event_id = 6032;
				}

				if ( 'file-scan-complete' === $data['event_type'] ) {
					$variables['EventType'] = 'finished';
				}

				if ( 'file-scan-aborted' === $data['event_type'] ) {
					$variables['EventType'] = 'stopped';
				}

				Alert_Manager::trigger_event( $event_id, $variables );
			}
		}

		/**
		 * Trigger WSAL event when a file exceeds max size.
		 *
		 * @param  string $path - File path.
		 *
		 * @return void
		 *
		 * @since 2.0.0
		 */
		public static function file_beyond_max_size( $path ) {
			if ( isset( $data['event_type'] ) ) {
				$editor_link = esc_url( admin_url( 'admin.php?page=file-monitor-admin' ) );
				$event_id    = 6031;
				$variables   = array(
					'file_path'    => $path,
					'RevisionLink' => $editor_link,
				);
				Alert_Manager::trigger_event( $event_id, $variables );
			}
		}
	}
}
