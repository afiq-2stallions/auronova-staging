<?php
/**
 * Helper class for file and directory tasks.
 *
 * @package MFM
 * @since 2.0.0
 */

declare(strict_types=1);

namespace MFM\WSAL;

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Utility file and directory functions.
 *
 * @since 2.0.0
 */
class Init_Sensor {

	/**
	 * Init this class
	 *
	 * @return void
	 *
	 * @since 2.0.0
	 */
	public static function init() {

		if ( class_exists( '\WSAL\Helpers\Classes_Helper' ) ) {
			add_action(
				'wsal_sensors_manager_add',
				/**
				* Adds sensors classes to the Class Helper
				*
				* @return void
				*
				* @since 2.0.0
				*/
				function () {
					\WSAL\Helpers\Classes_Helper::add_to_class_map(
						array(
							'WSAL\\Plugin_Sensors\\MFM_Sensor' => __DIR__ . '/class-mfm-sensor.php',
						)
					);
				}
			);

			add_action(
				'wsal_custom_alerts_register',
				/**
				* Adds sensors classes to the Class Helper
				*
				* @return void
				*
				* @since 2.0.0
				*/
				function () {
					\WSAL\Helpers\Classes_Helper::add_to_class_map(
						array(
							'WSAL\\Custom_Alerts\\MFM_Alerts' => __DIR__ . '/class-mfm-alerts.php',
						)
					);
				}
			);

			add_filter(
				'wsal_event_type_data',
				/**
				* Adds new type to array.
				*
				* @return array
				*
				* @since 2.0.0
				 */
				function ( $types ) {
					$new_types = array(
						'finished' => esc_html__( 'Finished', 'website-file-changes-monitor' ),
						'purged'   => esc_html__( 'Purged', 'website-file-changes-monitor' ),
					);

					// combine the two arrays.
					$types = array_merge( $types, $new_types );
					return $types;
				}
			);

			add_filter(
				'wsal_event_objects',
				/**
				* Adds new type to array.
				*
				* @return array
				*
				* @since 2.0.0
				 */
				function ( $objects ) {
					$new_objects = array(
						'file_monitor' => __( 'File Monitor', 'website-file-changes-monitor' ),
					);

					// combine the two arrays.
					$objects = array_merge( $objects, $new_objects );

					return $objects;
				}
			);

			add_filter(
				/**
				* Adds new obsolete item to array.
				*
				* @return array
				*
				* @since 2.0.0
				 */
				'wsal_togglealerts_obsolete_events',
				function ( $obsolete_events ) {
					$new_events      = array(
						6028,
						6029,
					);
					$obsolete_events = array_merge( $obsolete_events, $new_events );
					return $obsolete_events;
				}
			);
			\WSAL\Plugin_Sensors\MFM_Sensor::init();
		}
	}
}
