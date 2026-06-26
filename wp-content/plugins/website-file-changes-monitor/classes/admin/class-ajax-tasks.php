<?php
/**
 * AJAX_Tasks
 *
 * @package MFM
 * @since 2.0.0
 */

declare(strict_types=1);

namespace MFM\Admin;

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

use MFM\DB_Handler;
use MFM\Helpers\Settings_Helper;
use MFM\Admin\Admin_Manager;
use MFM\Helpers\Setting_Validator;

/**
 * Utility file and directory functions.
 *
 * @since 2.0.0
 */
class AJAX_Tasks {

	/**
	 * Purge all present data.
	 *
	 * @param boolean $is_internal_command - Function is fired from inside our plugin so skip nonce.
	 *
	 * @return void
	 *
	 * @since 2.0.0
	 */
	public static function purge_data( $is_internal_command = true ) {
		if ( ! current_user_can( 'manage_options' ) ) {
			return;
		}
		$nonce = isset( $_POST['nonce'] ) ? sanitize_key( wp_unslash( $_POST['nonce'] ) ) : '';

		if ( ! $is_internal_command ) {
			if ( empty( $nonce ) || ! wp_verify_nonce( $nonce, MFM_PREFIX . 'purge_data_nonce' ) ) {
				wp_send_json_error( array( 'message' => __( 'Failed nonce check', 'website-file-changes-monitor' ) ) );
				return;
			}
		} else {
			$nonce = wp_create_nonce( MFM_PREFIX . 'purge_data_nonce' );
		}

		do_action( MFM_PREFIX . 'setting_purged' );

		DB_Handler::do_data_purge( $nonce );

		if ( $is_internal_command ) {
			return;
		}

		DB_Handler::install();

		$return = array(
			'message' => __( 'Data successfully purged, you will now to taken to the initial setup wizard.', 'website-file-changes-monitor' ),
		);
		wp_send_json_success( $return );
	}

	/**
	 * Update a setting to a new provided value.
	 *
	 * @return array - Response message.
	 *
	 * @since 2.0.0
	 */
	public static function update_setting() {
		$nonce = isset( $_POST['nonce'] ) ? sanitize_key( wp_unslash( $_POST['nonce'] ) ) : '';

		if ( empty( $nonce ) || ! wp_verify_nonce( $nonce, MFM_PREFIX . 'inline_settings_update' ) ) {
			wp_send_json_error( array( 'message' => __( 'Failed nonce check', 'website-file-changes-monitor' ) ) );
			return;
		}

		$event_type            = '';
		$event_target          = '';
		$already_found         = false;
		$event_message_context = __( 'excluded directories', 'website-file-changes-monitor' );

		if ( ! isset( $_POST['event_type'] ) || ! isset( $_POST['event_target'] ) ) {
			$return = array(
				'message' => __( 'Failed', 'website-file-changes-monitor' ),
			);
			wp_send_json_error( $return );
		} else {
			$event_type       = sanitize_textarea_field( wp_unslash( $_POST['event_type'] ) );
			$event_target     = sanitize_textarea_field( wp_unslash( $_POST['event_target'] ) );
			$current_settings = Settings_Helper::get_mfm_settings();
		}

		if ( 'exclude-directory' === $event_type ) {
			$current = $current_settings['excluded_directories'];
			if ( in_array( $event_target, $current, true ) ) {
				$already_found = true;
			} else {
				array_push( $current, $event_target );
				Settings_Helper::save_setting( 'excluded_directories', Setting_Validator::validate( 'excluded_directories', $current ) );
			}
		} elseif ( 'exclude-file' === $event_type ) {
			$event_message_context = __( 'excluded files', 'website-file-changes-monitor' );
			$current               = $current_settings['excluded_files'];
			if ( in_array( $event_target, $current, true ) ) {
				$already_found = true;
			} else {
				array_push( $current, $event_target );
				Settings_Helper::save_setting( 'excluded_files', Setting_Validator::validate( 'excluded_files', $current ) );
			}
		} else {
			Settings_Helper::save_setting( $event_type, Setting_Validator::validate( $event_type, $event_target ) );
		}

		if ( $already_found ) {
			$return = array(
				'message' => __( 'already added to', 'website-file-changes-monitor' ) . ' ' . $event_message_context,
			);
			wp_send_json_error( $return );
		} else {
			$return = array(
				'message' => __( 'saved to ', 'website-file-changes-monitor' ) . ' ' . $event_message_context,
			);
			wp_send_json_success( $return );
		}
	}

	/**
	 * Validate an input.
	 *
	 * @return array - Response message.
	 *
	 * @since 2.0.0
	 */
	public static function validate_setting() {
		$nonce = isset( $_POST['nonce'] ) ? sanitize_key( wp_unslash( $_POST['nonce'] ) ) : '';

		if ( empty( $nonce ) || ! wp_verify_nonce( $nonce, MFM_PREFIX . 'validate_setting_nonce' ) ) {
			wp_send_json_error( array( 'message' => __( 'Failed nonce check', 'website-file-changes-monitor' ) ) );
			return;
		}

		if ( ! isset( $_POST['event_type'] ) ) {
			$return = array(
				'message' => __( 'Failed', 'website-file-changes-monitor' ),
			);
			wp_send_json_error( $return );
		}

		$return = array(
			'message' => __( 'saved to ', 'website-file-changes-monitor' ),
		);
		wp_send_json_success( $return );
	}

	/**
	 * Reset a setting to default value.
	 *
	 * @return array - Response message.
	 *
	 * @since 2.0.0
	 */
	public static function reset_setting() {
		$nonce = isset( $_POST['nonce'] ) ? sanitize_key( wp_unslash( $_POST['nonce'] ) ) : '';

		if ( empty( $nonce ) || ! wp_verify_nonce( $nonce, MFM_PREFIX . 'reset_setting_nonce' ) ) {
			wp_send_json_error( array( 'message' => __( 'Failed nonce check', 'website-file-changes-monitor' ) ) );
			return;
		}

		if ( ! isset( $_POST['target'] ) ) {
			$return = array(
				'message' => __( 'Setting not provided', 'website-file-changes-monitor' ),
			);
			wp_send_json_error( $return );
			return;
		}

		$settings = Settings_Helper::get_mfm_settings();
		$target   = sanitize_textarea_field( wp_unslash( $_POST['target'] ) );

		if ( ! in_array( $target, array_keys( $settings ), true ) ) {
			$return = array(
				'message' => __( 'Setting not found ', 'website-file-changes-monitor' ) . $target,
			);
			wp_send_json_error( $return );
			return;
		}

		Settings_Helper::save_setting( $target, Settings_Helper::get_settings_default_value( $target ) );

		$return = array(
			'message' => $target . ' ' . __( 'reset to default', 'website-file-changes-monitor' ),
		);
		wp_send_json_success( $return );
	}

	/**
	 * Mark an item as read and remove it (and all metadata) from the DB
	 *
	 * @return array - Response message.
	 *
	 * @since 2.0.0
	 */
	public static function mark_as_read() {
		$nonce = isset( $_POST['nonce'] ) ? sanitize_key( wp_unslash( $_POST['nonce'] ) ) : '';

		if ( empty( $nonce ) || ! wp_verify_nonce( $nonce, MFM_PREFIX . 'inline_settings_update' ) ) {
			wp_send_json_error( array( 'message' => __( 'Failed nonce check', 'website-file-changes-monitor' ) ) );
			return;
		}

		if ( ! isset( $_POST['target'] ) ) {
			$return = array(
				'message' => __( 'No target given', 'website-file-changes-monitor' ),
			);
			wp_send_json_error( $return );
			return;
		}

		$target = sanitize_textarea_field( wp_unslash( $_POST['target'] ) );
		$nonce  = wp_create_nonce( MFM_PREFIX . 'delete_data' );

		if ( 'all' === $target ) {
			DB_Handler::truncate_table( $nonce, DB_Handler::$events_table_name );
			DB_Handler::truncate_table( $nonce, DB_Handler::$events_meta_table_name );
		} else {
			DB_Handler::delete_from_where( $nonce, DB_Handler::$events_table_name, 'id', $target );
			DB_Handler::delete_from_where( $nonce, DB_Handler::$events_meta_table_name, 'event_id', $target );
		}

		$return = array(
			'message' => __( 'All done', 'website-file-changes-monitor' ),
		);
		wp_send_json_success( $return );
	}

	/**
	 * Finish setup wizard and update settings.
	 *
	 * @return array - Response message.
	 *
	 * @since 2.0.0
	 */
	public static function finish_setup_wizard() {
		$nonce = isset( $_POST['nonce'] ) ? sanitize_key( wp_unslash( $_POST['nonce'] ) ) : '';

		if ( ! isset( $_POST['form_data'] ) || empty( $nonce ) || ! wp_verify_nonce( $nonce, MFM_PREFIX . 'finish_setup_wizard' ) ) {
			wp_send_json_error( array( 'message' => __( 'Failed nonce check', 'website-file-changes-monitor' ) ) );
			return;
		}

		$excluded_dirs  = array();
		$excluded_files = array();
		$excluded_exts  = array();
		$ignored_dirs   = array();

		foreach ( wp_unslash( $_POST['form_data'] ) as $item ) { // phpcs:ignore WordPress.Security.ValidatedSanitizedInput.InputNotSanitized
			preg_match_all( '/\[([^\]]*)\]/', $item['name'], $matches );

			if ( isset( $matches[1][0] ) ) {
				if ( 'excluded_file_extensions' === $matches[1][0] ) {
					$excluded_exts[] = $item['value'];
				} elseif ( 'excluded_directories' === $matches[1][0] ) {
					$excluded_dirs[] = $item['value'];
				} elseif ( 'ignored_directories' === $matches[1][0] ) {
					$ignored_dirs[] = $item['value'];
				} elseif ( 'excluded_files' === $matches[1][0] ) {
					$excluded_files[] = $item['value'];
				} else {
					Settings_Helper::save_setting( $matches[1][0], Setting_Validator::validate( $matches[1][0], $item['value'] ) );
				}
			}
		}

		Settings_Helper::save_setting( 'excluded_file_extensions', Setting_Validator::validate( 'excluded_file_extensions', self::clean_wizard_settings( $excluded_exts ) ) );
		Settings_Helper::save_setting( 'excluded_directories', Setting_Validator::validate( 'excluded_directories', self::clean_wizard_settings( $excluded_dirs ) ) );
		Settings_Helper::save_setting( 'excluded_files', Setting_Validator::validate( 'excluded_files', self::clean_wizard_settings( $excluded_files ) ) );
		Settings_Helper::save_setting( 'ignored_directories', Setting_Validator::validate( 'ignored_directories', self::clean_wizard_settings( $ignored_dirs ) ) );

		if ( isset( $_POST['remove_old_data'] ) && ! empty( sanitize_key( wp_unslash( $_POST['remove_old_data'] ) ) ) ) {
			DB_Handler::purge_wfcm_data();
		}

		update_site_option( MFM_PREFIX . 'initial_setup_needed', false );

		$return = array(
			'message' => __( 'All done', 'website-file-changes-monitor' ),
		);
		wp_send_json_success( $return );
	}

	/**
	 * Remove unwanted items from data POSTed from wizard.
	 *
	 * @param array $inccoming_array - Array to do.
	 *
	 * @return array - Tidied items.
	 *
	 * @since 2.1.0
	 */
	public static function clean_wizard_settings( $inccoming_array ) {
		$output_array = array();
		foreach ( $inccoming_array as $item ) {
			if ( 'false' === $item ) {
				continue;
			}
			$output_array[] = $item;
		}

		return $output_array;
	}

	/**
	 * Dismiss new events notice from admin.
	 *
	 * @return void
	 *
	 * @since 2.0.0
	 */
	public static function dismiss_events_notice() {
		$nonce = isset( $_POST['nonce'] ) ? sanitize_key( wp_unslash( $_POST['nonce'] ) ) : '';

		if ( empty( $nonce ) || ! wp_verify_nonce( $nonce, MFM_PREFIX . 'dismiss_notice_nonce' ) ) {
			wp_send_json_error( array( 'message' => __( 'Failed nonce check', 'website-file-changes-monitor' ) ) );
			return;
		}

		update_site_option( MFM_PREFIX . 'event_notification_dismissed', true );

		$return = array(
			'message' => __( 'All done', 'website-file-changes-monitor' ),
		);
		wp_send_json_success( $return );
	}

	/**
	 * Abort active scan check.
	 *
	 * @return void
	 *
	 * @since 2.0.0
	 */
	public static function abort_scan() {
		$return = array(
			'message' => get_site_option( MFM_PREFIX . 'monitor_status' ),
		);
		wp_send_json_success( $return );
	}

	/**
	 * Looking event by string.
	 *
	 * @return void
	 *
	 * @since 2.0.0
	 */
	public static function event_lookup() {
		$nonce = isset( $_POST['nonce'] ) ? sanitize_key( wp_unslash( $_POST['nonce'] ) ) : '';

		if ( ! isset( $_POST['lookup_target'] ) ) {
			wp_send_json_error( array( 'message' => __( 'No search term provided', 'website-file-changes-monitor' ) ) );
			return;
		}
		if ( empty( $nonce ) || ! wp_verify_nonce( $nonce, MFM_PREFIX . 'event_lookup_nonce' ) ) {
			wp_send_json_error( array( 'message' => __( 'Failed nonce check', 'website-file-changes-monitor' ) ) );
			return;
		}
		$event_lookup = sanitize_textarea_field( wp_unslash( $_POST['lookup_target'] ) );

		$found = DB_Handler::lookup_event( Settings_Helper::sanitize_search_input( $event_lookup ) );

		if ( isset( $found[0] ) ) {
			$markup = Admin_Manager::create_events_list_markup( $found );
			$return = array(
				'message'    => __( 'found, displaying below', 'website-file-changes-monitor' ),
				'event_data' => $markup,
			);
			wp_send_json_success( $return );
		} else {
			$return = array(
				'message' => __( 'not found', 'website-file-changes-monitor' ),
			);
			wp_send_json_error( $return );
		}
	}

	/**
	 * Cancel scan in progress.
	 *
	 * @return void
	 *
	 * @since 2.0.0
	 */
	public static function cancel_scan() {
		$nonce = isset( $_POST['nonce'] ) ? sanitize_key( wp_unslash( $_POST['nonce'] ) ) : '';

		if ( empty( $nonce ) || ! wp_verify_nonce( $nonce, MFM_PREFIX . 'cancel_scan_nonce' ) ) {
			wp_send_json_error( array( 'message' => __( 'Failed nonce check', 'website-file-changes-monitor' ) ) );
			return;
		}

		DB_Handler::cancel_current_scan();

		$return = array(
			'message' => __( 'Scan cancelled', 'website-file-changes-monitor' ),
		);
		wp_send_json_success( $return );
	}

	/**
	 * Finish setup wizard and update settings.
	 *
	 * @return array - Response message.
	 *
	 * @since 2.0.0
	 */
	public static function cancel_setup_wizard() {
		$nonce = isset( $_POST['nonce'] ) ? sanitize_key( wp_unslash( $_POST['nonce'] ) ) : '';

		if ( empty( $nonce ) || ! wp_verify_nonce( $nonce, MFM_PREFIX . 'cancel_setup_wizard' ) ) {
			wp_send_json_error( array( 'message' => __( 'Failed nonce check', 'website-file-changes-monitor' ) ) );
			return;
		}

		update_site_option( MFM_PREFIX . 'initial_setup_needed', false );

		$return = array(
			'message' => __( 'All done', 'website-file-changes-monitor' ),
		);
		wp_send_json_success( $return );
	}
}
