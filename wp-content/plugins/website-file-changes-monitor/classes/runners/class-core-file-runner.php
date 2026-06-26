<?php
/**
 * Handles checking and reporting of core file changes.
 *
 * @package MFM
 * @since 2.0.0
 */

declare(strict_types=1);

namespace MFM\Runners;

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

use MFM\Helpers\Directory_And_File_Helpers;
use MFM\DB_Handler;
use MFM\Helpers\Settings_Helper;
use MFM\Helpers\Logger;

/**
 * Main runner for each file.
 *
 * @since 2.0.0
 */
class Core_File_Runner extends \MFM\WP_Background_Process {

	/**
	 * Runner prefix.
	 *
	 * @var string
	 *
	 * @since 2.0.0
	 */
	protected $prefix = 'mfm';

	/**
	 * Runner action name.
	 *
	 * @var string
	 *
	 * @since 2.0.0
	 */
	protected $action = 'core_file_runner';

	/**
	 * Main task logic.
	 *
	 * @param string $item - Input.
	 *
	 * @return bool
	 *
	 * @since 2.0.0
	 */
	protected function task( $item ) {
		$path          = explode( '|', $item )[0];
		$expected_hash = explode( '|', $item )[1];

		$context = Directory_And_File_Helpers::determine_directory_context( dirname( $path ), false );

		if ( 'plugin' === $context || 'theme' === $context ) {
			return false;
		}

		$ignore_files = Settings_Helper::get_setting_cached( 'excluded_files' );
		$ignore_files = ( is_array( $ignore_files ) ) ? $ignore_files : array();

		$allowed_core_files = Settings_Helper::get_setting_cached( 'allowed-in-core-files' );
		$allowed_core_files = ( is_array( $allowed_core_files ) ) ? $allowed_core_files : array();

		if ( ! empty( $ignore_files ) && in_array( basename( $path ), $ignore_files, true ) ) {
			$msg  = Logger::get_log_timestamp() . ' Attempted to check file, but skipped as ignored:' . " \n";
			$msg .= Logger::get_log_timestamp() . ' ' . $path . " \n";
			Logger::write_to_log( $msg );
			return false;
		} elseif ( ! empty( $allowed_core_files ) && in_array( basename( $path ), $allowed_core_files, true ) ) {
			$msg  = Logger::get_log_timestamp() . ' Attempted to check file, but skipped as allowed in core:' . " \n";
			$msg .= Logger::get_log_timestamp() . ' ' . $path . " \n";
			Logger::write_to_log( $msg );
			return false;
		}

		if ( ! file_exists( $path ) ) {
			$current_id = get_site_option( MFM_PREFIX . 'active_scan_id' );
			$new_data   = array(
				'removed' => array( $path ),
			);
			$data       = array(
				'path'        => trailingslashit( dirname( $path ) ),
				'event_type'  => 'core-file-removed',
				'time'        => current_time( 'timestamp' ), // phpcs:ignore WordPress.DateTime.CurrentTimeTimestamp.Requested
				'is_read'     => 'no',
				'data'        => maybe_serialize( $new_data ),
				'scan_run_id' => $current_id,
			);

			DB_Handler::add_event( $data );
			return false;
		}

		$current_hash = md5_file( $path );

		$new_data = array(
			'modified' => array( $path ),
		);

		if ( $expected_hash !== $current_hash ) {
			$is_same_as_last_scan = Directory_And_File_Helpers::check_stored_file_hash( $path, $current_hash );

			// No change since last scan, don't alert.
			if ( $is_same_as_last_scan ) {
				$msg  = Logger::get_log_timestamp() . ' Core file is same as last scan, skipping' . " \n";
				$msg .= Logger::get_log_timestamp() . ' ' . $path . " \n";
				Logger::write_to_log( $msg );
				return false;
			}

			$excluded_file_extensions = Settings_Helper::get_setting_cached( 'excluded_file_extensions' );
			$excluded_file_extensions = ( is_array( $excluded_file_extensions ) ) ? $excluded_file_extensions : array();
			$file_extension           = substr( strrchr( $path, '.' ), 1 );
			$ignore_files             = Settings_Helper::get_setting_cached( 'excluded_files' );
			$ignore_files             = ( is_array( $ignore_files ) ) ? $ignore_files : array();

			if ( $file_extension && in_array( $file_extension, $excluded_file_extensions, true ) ) {
				$msg  = Logger::get_log_timestamp() . ' Attempted to check core file as modified, but ignored extension:' . " \n";
				$msg .= Logger::get_log_timestamp() . ' ' . $file_extension . " \n";
				$msg .= Logger::get_log_timestamp() . ' ' . $path . " \n";
				Logger::write_to_log( $msg );
				return false;
			}

			$current_id = get_site_option( MFM_PREFIX . 'active_scan_id' );
			$data       = array(
				'path'        => trailingslashit( dirname( $path ) ),
				'event_type'  => 'core-file-modified',
				'time'        => current_time( 'timestamp' ), // phpcs:ignore WordPress.DateTime.CurrentTimeTimestamp.Requested
				'is_read'     => 'no',
				'data'        => maybe_serialize( $new_data ),
				'scan_run_id' => $current_id,
			);

			DB_Handler::add_event( $data );
		}

		return false;
	}

	/**
	 * Background process chain ID.
	 *
	 * @var string
	 *
	 * @since 2.3.0
	 */
	private $mfm_chain_id = '';

	/**
	 * Get chain ID without cross-runner nonce verification.
	 *
	 * Overrides parent to avoid check_ajax_referer() call that fails
	 * when dispatching one runner from another runner's AJAX context.
	 *
	 * @return string
	 *
	 * @since 2.3.0
	 */
	public function get_chain_id() {
		if ( empty( $this->mfm_chain_id ) ) {
			$this->mfm_chain_id = \wp_generate_uuid4();
		}
		return $this->mfm_chain_id;
	}

	/**
	 * Unlock.
	 *
	 * @return $this
	 *
	 * @since 2.0.0
	 */
	protected function unlock_process() {
		delete_site_transient( $this->identifier . '_process_lock' );
		return $this;
	}

	/**
	 * Should the process exit with wp_die?
	 *
	 * @param mixed $should_return What to return if filter says don't die, default is null.
	 *
	 * @return void|mixed
	 *
	 * @since 2.0.0
	 */
	protected function maybe_wp_die( $should_return = null ) {
		/**
		 * Should wp_die be used?
		 *
		 * @return bool
		 *
		 * @since 2.0.0
		 */
		if ( apply_filters( $this->identifier . '_wp_die', true ) ) {
			wp_die();
		}

		return $should_return;
	}
}
