<?php
/**
 * Custom Alerts for My custom sensor.
 *
 * @package MFM
 * @since 2.0.0
 */

declare(strict_types=1);

namespace WSAL\Custom_Alerts;

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( ! class_exists( '\WSAL\Custom_Alerts\MFM_Alerts' ) ) {
	/**
	 * Custom alerts array for WSAL plugin.
	 *
	 * @since 2.0.0
	 */
	class MFM_Alerts {

		/**
		 * Returns the structure of the alerts for extension.
		 *
		 * @return array
		 *
		 * @since 2.0.0
		 */
		public static function get_custom_alerts(): array {
			return array(
				esc_html__( 'Melapress File Monitor', 'website-file-changes-monitor' ) => array(
					esc_html__( 'Melapress File Monitor Content', 'website-file-changes-monitor' ) => array(

						array(
							6030,
							WSAL_HIGH,
							esc_html__( 'Melapress File Monitor file / directory change detected', 'website-file-changes-monitor' ),
							__( 'Melapress File Monitor file / directory change detected.', 'website-file-changes-monitor' ),
							array(
								esc_html__( 'Change type(s)', 'website-file-changes-monitor' ) => '%event_type%',
							),
							wsaldefaults_build_links( array( 'RevisionLink' ) ),
							'file_monitor',
							'modified',
						),

						array(
							6032,
							WSAL_HIGH,
							esc_html__( 'Melapress File Monitor core file change detected', 'website-file-changes-monitor' ),
							__( 'Melapress File Monitor detected a change within a WordPress core file', 'website-file-changes-monitor' ),
							array(),
							wsaldefaults_build_links( array( 'RevisionLink' ) ),
							'file_monitor',
							'modified',
						),

						array(
							6031,
							WSAL_HIGH,
							esc_html__( 'Melapress File Monitor file skipped due to size', 'website-file-changes-monitor' ),
							__( 'File not scanned for changes because they are bigger than the maximum file size limit.', 'website-file-changes-monitor' ),
							array(
								esc_html__( 'File path', 'website-file-changes-monitor' ) => '%file_path%',
							),
							wsaldefaults_build_links( array( 'RevisionLink' ) ),
							'file_monitor',
							'modified',
						),

						array(
							6033,
							WSAL_HIGH,
							esc_html__( 'Melapress File Monitor scan started / finished', 'website-file-changes-monitor' ),
							/* Translators: %EventType% - Type variable */
							__( 'Melapress File Monitor scan %EventType%.', 'website-file-changes-monitor' ),
							array(),
							'',
							'file_monitor',
							'started',
						),

						array(
							6073,
							WSAL_LOW,
							esc_html__( 'Melapress File Monitor scan frequency modified', 'website-file-changes-monitor' ),
							/* Translators: %new% - Type variable */
							__( 'Changed the scan frequency to %new%.', 'website-file-changes-monitor' ),
							array(
								esc_html__( 'Previous scan frequency', 'website-file-changes-monitor' ) => '%old%',
							),
							'',
							'file_monitor',
							'modified',
						),

						array(
							6074,
							WSAL_LOW,
							esc_html__( 'Melapress File Monitor scan time modified', 'website-file-changes-monitor' ),
							/* Translators: %new_time% - Type variable */
							__( 'Changed the scan time to %new_time%.', 'website-file-changes-monitor' ),
							array(
								esc_html__( 'Previous configured scan time', 'website-file-changes-monitor' ) => '%old_time%',
							),
							'',
							'file_monitor',
							'modified',
						),

						array(
							6075,
							WSAL_MEDIUM,
							esc_html__( 'Melapress File Monitor WordPress core file integrity check was enabled / disabled', 'website-file-changes-monitor' ),
							/* Translators: %Enabled_Disabled% - Is event disabled */
							__( '%Enabled_Disabled% the WordPress core file integrity check.', 'website-file-changes-monitor' ),
							array(),
							'',
							'file_monitor',
							'enabled',
						),

						array(
							6076,
							WSAL_HIGH,
							esc_html__( 'Added an allowed file in the WordPress core file integrity check', 'website-file-changes-monitor' ),
							/* Translators: %filename% - File name variable */
							__( 'Added the file %filename% as allowed file in the WordPress core file integrity check.', 'website-file-changes-monitor' ),
							array(),
							'',
							'file_monitor',
							'added',
						),

						array(
							6077,
							WSAL_HIGH,
							esc_html__( 'Removed an allowed file in the WordPress core file integrity check', 'website-file-changes-monitor' ),
							/* Translators: %filename% - File name variable */
							__( 'Removed the file %filename% as allowed file in the WordPress core file integrity check.', 'website-file-changes-monitor' ),
							array(),
							'',
							'file_monitor',
							'removed',
						),

						array(
							6078,
							WSAL_MEDIUM,
							esc_html__( 'Melapress File Monitor settings reset default', 'website-file-changes-monitor' ),
							__( 'Purged the settings of the plugin <strong>Melapress File Monitor</strong> and reset them to default.', 'website-file-changes-monitor' ),
							array(),
							'',
							'file_monitor',
							'purged',
						),
					),
				),
			);
		}
	}
}
