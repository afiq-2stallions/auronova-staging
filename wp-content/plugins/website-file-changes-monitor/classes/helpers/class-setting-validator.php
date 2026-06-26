<?php
/**
 * Handle logging during scans.
 *
 * @package MFM
 * @since 2.0.0
 */

declare(strict_types=1);

namespace MFM\Helpers;

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

use MFM\Helpers\Settings_Helper;

/**
 * Utility to validate and sanitize settings.
 *
 * @since 2.0.0
 */
class Setting_Validator {

	/**
	 * Check and sanitize a setting.
	 *
	 * @param string $setting_to_validate - Setting to validate.
	 * @param mixed  $incoming - Value.
	 *
	 * @return mixed
	 *
	 * @since 2.0.0
	 */
	public static function validate( $setting_to_validate, $incoming ) {
		$array_settings = array(
			'excluded_file_extensions',
			'excluded_files',
			'allowed-in-core-files',
			'enabled-notifications',
		);

		$string_settings = array(
			'base_paths_to_scan',
			'email_notice_type',
			'from-display-name',
			'use_custom_from_email',
			'scan-frequency',
			'scan-hour-am',
		);

		$int_settings = array(
			'scan-hour',
			'scan-day',
			'scan-date',
			'email-changes-limit',
			'max-file-size',
			'purge-length',
			'events-view-per-page',
		);

		$yes_no_settings = array(
			'logging-enabled',
			'send-email-upon-changes',
			'empty-email-allowed',
			'debug-logging-enabled',
			'delete-data-enabled',
			'core-scan-enabled',
			'scan-files-with-no-extension',
		);

		$current_excluded_directories = Settings_Helper::get_setting( 'excluded_directories' );

		if ( 'from-email' === $setting_to_validate ) {
			if ( ! empty( $incoming ) && ! filter_var( $incoming, FILTER_VALIDATE_EMAIL ) ) {
				return false;
			}
			return sanitize_email( $incoming );
		}

		if ( 'custom_email_address' === $setting_to_validate ) {
			if ( strpos( $incoming, ',' ) !== false ) {
				$incoming_addresses = explode( ',', $incoming );
				$return_string      = array();

				foreach ( $incoming_addresses as $incoming_address ) {
					if ( ! filter_var( $incoming_address, FILTER_VALIDATE_EMAIL ) ) {
						return false;
					} else {
						$return_string[] = sanitize_email( $incoming_address );
					}
				}

				return implode( ',', $return_string );

			} else {
				if ( ! empty( $incoming ) && ! filter_var( $incoming, FILTER_VALIDATE_EMAIL ) ) {
					return false;
				}

				return sanitize_email( $incoming );

			}
		}

		if ( 'excluded_directories' === $setting_to_validate || 'ignored_directories' === $setting_to_validate ) {
			$sanitized = array();
			if ( ! is_array( $incoming ) ) {
				$incoming = array();
			}
			foreach ( $incoming as $item ) {
				if ( strpbrk( $item, '\\%:|\"<>' ) ) {
					return false;
				}

				if ( 'ignored_directories' === $setting_to_validate && ! empty( $current_excluded_directories ) ) {
					if ( in_array( ABSPATH . $item, $current_excluded_directories, true ) ) {
						return false;
					}
				}
				$sanitized[] = sanitize_text_field( $item );
			}
			return $sanitized;
		}

		if ( in_array( $setting_to_validate, $array_settings, true ) ) {
			$sanitized = array();
			if ( ! is_array( $incoming ) ) {
				$incoming = array();
			}
			foreach ( $incoming as $item ) {
				if ( 'excluded_file_extensions' === $setting_to_validate || 'allowed-in-core-files' === $setting_to_validate ) {
					$sanitized[] = sanitize_text_field( $item );
				} else {
					$sanitized[] = Settings_Helper::sanitize_search_input( $item );
				}
			}
			if ( 'enabled-notifications' === $setting_to_validate && empty( $incoming ) ) {
				return false;
			} else {
				return array_unique( $sanitized );
			}
		}

		if ( in_array( $setting_to_validate, $string_settings, true ) ) {
			$incoming = sanitize_text_field( $incoming );
			return $incoming;
		}

		if ( in_array( $setting_to_validate, $int_settings, true ) ) {
			$incoming = sanitize_text_field( $incoming );
			return (int) $incoming;
		}

		if ( in_array( $setting_to_validate, $yes_no_settings, true ) ) {
			if ( 'yes' === $incoming || 'no' === $incoming ) {
				return $incoming;
			}
		}

		return false;
	}

	/**
	 * Get error message based on key.
	 *
	 * @param string $setting_key - Lookup.
	 *
	 * @return string
	 *
	 * @since 2.0.0
	 */
	public static function get_validation_error_message( $setting_key ) {
		$defaults = array(
			'enabled-notifications' => esc_html__( 'Please ensure at least one Notification type is enabled', 'website-file-changes-monitor' ),
			'custom_email_address'  => esc_html__( 'Please provide a valid email address', 'website-file-changes-monitor' ),
			'excluded_directories'  => esc_html__( 'Directory is not valid', 'website-file-changes-monitor' ),
			'ignored_directories'   => esc_html__( 'Ignored directory already in excluded list, please remove it to continue', 'website-file-changes-monitor' ),
		);

		return isset( $defaults[ $setting_key ] ) ? $defaults[ $setting_key ] : '';
	}
}
