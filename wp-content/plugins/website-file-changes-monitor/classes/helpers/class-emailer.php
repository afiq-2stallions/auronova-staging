<?php
/**
 * Handle emails.
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
use MFM\Helpers\Logger;
use MFM\DB_Handler;
use MFM\Helpers\Events_Helper;

/**
 * Utility file and directory functions.
 *
 * @since 2.0.0
 */
class Emailer {

	/**
	 * Send a summary of any changes of last scan.
	 *
	 * @param integer $scan_run_id - Scan ID.
	 *
	 * @return void
	 *
	 * @since 2.0.0
	 */
	public static function send_scan_summary( $scan_run_id = 0 ) {

		$msg  = Logger::get_log_timestamp() . ' EMAIL SUMMARY SEND' . " \n";
		$msg .= Logger::get_log_timestamp() . ' ' . Settings_Helper::get_notification_email() . " \n";
		$msg .= Logger::get_log_timestamp() . ' ' . $scan_run_id . " \n";
		Logger::write_to_log( $msg );

		if ( 0 === $scan_run_id ) {
			$scan_run_id = get_site_option( MFM_PREFIX . 'active_scan_id' );
		}

		$enabled_notifications = Settings_Helper::get_setting( 'enabled-notifications' );
		$events                = DB_Handler::get_events_for_specific_run( $scan_run_id, $enabled_notifications );

		$to      = Settings_Helper::get_notification_email();
		$subject = __( 'File changes from file integrity scan', 'website-file-changes-monitor' );

		$limit      = (int) Settings_Helper::get_setting( 'email-changes-limit', 10 );
		$send_empty = ( 'yes' === Settings_Helper::get_setting( 'empty-email-allowed' ) ) ? true : false;

		if ( empty( $events ) && ! $send_empty ) {
			return;
		}

		if ( ! empty( $events ) ) {
			$message = '<p>' . __( 'The below is a list file change events from your most recent scan', 'website-file-changes-monitor' ) . '</p>' . " \n";

			$type_labels = array(
				'added'    => \esc_html__( 'File added', 'website-file-changes-monitor' ),
				'modified' => \esc_html__( 'File modified', 'website-file-changes-monitor' ),
				'removed'  => \esc_html__( 'File removed', 'website-file-changes-monitor' ),
			);

			foreach ( $events as $event ) {
				$message .= " \n";
				$message .= '<p><strong>' . Events_Helper::create_event_type_label( $event['event_type'], 'all', true ) . '</strong></p>' . " \n";

				$items = explode( ',', $event['data'] );

				$message .= '<p><strong>' . __( 'Directory:', 'website-file-changes-monitor' ) . ' </strong>' . $event['path'] . '</p>' . " \n";

				foreach ( $items as $item => $value ) {
					$is_arr = maybe_unserialize( $value );

					if ( ! is_array( $is_arr ) ) {
						$msg  = Logger::get_log_timestamp() . ' Email item skipped, not array' . " \n";
						$msg .= Logger::get_log_timestamp() . $is_arr . " \n";
						Logger::write_to_log( $msg );
						continue;
					}

					$val_i = 0;

					foreach ( $is_arr as $type => $item ) {
						if ( $val_i < $limit ) {
							if ( is_array( $item ) ) {
								$i = 0;
								foreach ( $item as $file ) {
									if ( $i < $limit ) {
										if ( 'permissions_changed' === $type && is_array( $file ) ) {
											$file_path = $file['file'] ?? '';
											$previous  = $file['previous'] ?? '';
											$current   = $file['current'] ?? '';

											$message .= '<p>'
												. \esc_html__( 'File Permissions changed', 'website-file-changes-monitor' ) . ': ' . \esc_html( $file_path )
												. ' (' . \esc_html__( 'previous', 'website-file-changes-monitor' ) . ': ' . \esc_html( $previous )
												. ', ' . \esc_html__( 'current', 'website-file-changes-monitor' ) . ': ' . \esc_html( $current )
												. ')</p>' . " \n";
										} else {
											$type_label = $type_labels[ $type ] ?? \esc_html( ucfirst( $type ) );

											$message .= '<p>' . $type_label . ': ' . \esc_html( $file ) . '</p>' . " \n";
										}
									}

									++$i;

								}
							} else {
								$message .= '<p>File ' . ucfirst( $type ) . ': ' . str_replace( ABSPATH, '', $item ) . '</p>' . " \n";
							}
						}
						++$val_i;
					}
				}
			}
		} else {
			$message = '<p>' . __( 'There were no file changes detected during the last file integrity scan', 'website-file-changes-monitor' ) . '</p>' . " \n";
		}

		$message .= " \n" . '<p>' . __( 'Visit the File Monitor in the WordPress dashboard to check the file changes.', 'website-file-changes-monitor' ) . '</p>' . " \n";

		/* Translators: %s: Plugin WP Hyperlink */
		$message .= '<p>' . sprintf( __( 'This file integrity scan was done with %s.', 'website-file-changes-monitor' ), '<a href="https://melapress.com/wordpress-file-monitor/?utm_source=plugin&utm_medium=link&utm_campaign=mfm" target="_blank">' . __( 'Melapress File Monitor', 'website-file-changes-monitor' ) . '</a>' ) . '</p>';

		$message = apply_filters( MFM_PREFIX . 'file_changes_notification_message', $message );

		$result = self::send_email( $to, $subject, $message );

		return $result;
	}

	/**
	 * Filter the mail content type.
	 *
	 * @return string
	 *
	 * @since 2.0.0
	 */
	public static function set_html_content_type() {
		return 'text/html';
	}

	/**
	 * Send an email test to provided address.
	 *
	 * @return array - Response.
	 *
	 * @since 2.0.0
	 */
	public static function send_test_email() {
		$nonce = isset( $_POST['nonce'] ) ? sanitize_key( wp_unslash( $_POST['nonce'] ) ) : '';
		if ( empty( $nonce ) || ! wp_verify_nonce( $nonce, MFM_PREFIX . 'test_email_nonce' ) ) {
			wp_send_json_error( array( 'message' => __( 'Failed nonce check', 'website-file-changes-monitor' ) ) );
			return;
		}

		$email_address = wp_unslash( $_POST['email_address'] );

		if ( str_contains( $email_address, ',' ) ) {
			$to = array();
			foreach ( explode( ',', $email_address ) as $email ) {
				$to[] = sanitize_email( $email );
			}
		} else {
			$to = isset( $_POST['email_address'] ) ? sanitize_email( $email_address ) : false;
		}

		if ( ! $to ) {
			$return = array(
				'message' => __( 'No email supplied', 'website-file-changes-monitor' ),
			);
			wp_send_json_error( $return );
		}

		$subject = esc_html__( 'MFM Email Test', 'website-file-changes-monitor' );
		$message = esc_html__( 'This is a test message, sent from the Melapress File Monitor plugin', 'website-file-changes-monitor' );

		$result = self::send_email( $to, $subject, $message );

		if ( ! $result ) {
			$return = array(
				'message' => __( 'Test failed', 'website-file-changes-monitor' ),
			);
			wp_send_json_error( $return );
		}

		$return = array(
			'message' => __( 'Test email successfully sent', 'website-file-changes-monitor' ),
		);
		wp_send_json_success( $return );
	}

	/**
	 * Send Email.
	 *
	 * @param string $email_address - Email Address.
	 * @param string $subject       - Email subject.
	 * @param string $content       - Email content.
	 * @param string $headers       Email headers.
	 * @param array  $attachments   Email attachments.
	 *
	 * @return bool
	 *
	 * self::send_email
	 */
	public static function send_email( $email_address, $subject, $content, $headers = '', $attachments = array() ) {

		if ( ! empty( $headers ) ) {
			$headers = array_merge_recursive( (array) $headers, array( 'Content-Type: ' . self::set_html_content_type() . '; charset=UTF-8' ) );
		} else {
			$headers = array( 'Content-Type: ' . self::set_html_content_type() . '; charset=UTF-8' );
		}

		// @see: http://codex.wordpress.org/Function_Reference/wp_mail
		\add_filter( 'wp_mail_from', array( __CLASS__, 'custom_wp_mail_from' ), PHP_INT_MAX );
		\add_filter( 'wp_mail_from_name', array( __CLASS__, 'custom_wp_mail_from_name' ) );

		$result = \wp_mail( $email_address, $subject, $content, $headers, $attachments );

		/**
		 * Reset content-type to avoid conflicts.
		 *
		 * @see http://core.trac.wordpress.org/ticket/23578
		 */
		\remove_filter( 'wp_mail_from', array( __CLASS__, 'custom_wp_mail_from' ), PHP_INT_MAX );
		\remove_filter( 'wp_mail_from_name', array( __CLASS__, 'custom_wp_mail_from_name' ) );

		return $result;
	}

	/**
	 * Return if there is a from-email in the setting or the original passed.
	 *
	 * @param string $original_email_from – Original passed.
	 *
	 * @return string
	 *
	 * @since 2.1.0
	 */
	public static function custom_wp_mail_from( $original_email_from ) {
		$use_email  = Settings_Helper::get_setting( 'use_custom_from_email', 'default_email' );
		$email_from = Settings_Helper::get_setting( 'from-email' );
		if ( ! empty( $email_from ) && 'custom_email' === $use_email ) {
			return $email_from;
		} else {
			return $original_email_from;
		}
	}

	/**
	 * Return if there is a display-name in the setting or the original passed.
	 *
	 * @param string $original_email_from_name – Original passed.
	 *
	 * @return string
	 *
	 * @since 2.1.0
	 */
	public static function custom_wp_mail_from_name( $original_email_from_name ) {
		$use_email       = Settings_Helper::get_setting( 'use_custom_from_email', 'default_email' );
		$email_from_name = Settings_Helper::get_setting( 'from-display-name' );
		if ( ! empty( $email_from_name ) && 'custom_email' === $use_email ) {
			return $email_from_name;
		} else {
			if ( ! empty( self::get_default_email_address() ) ) {
				return self::get_default_email_address();
			}

			return $original_email_from_name;
		}
	}

	/**
	 * Builds and returns the default email address used for the "from" email address when email is send
	 *
	 * @return string
	 *
	 * @since 2.1.0
	 */
	public static function get_default_email_address(): string {
		$sitename = \wp_parse_url( \network_home_url(), PHP_URL_HOST );

		$from_email = '';

		if ( null !== $sitename ) {
			$from_email = 'mfm@';
			if ( \str_starts_with( $sitename, 'www.' ) ) {
				$sitename = substr( $sitename, 4 );
			}

			$from_email .= $sitename;
		}

		return $from_email;
	}
}
