<?php
/**
 * Handles checking and reporting of file changes.
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
use MFM\Plugins_And_Themes_Monitor;
use MFM\Helpers\Settings_Helper;

/**
 * Main comparison runner.
 *
 * @since 2.0.0
 */
class File_Comparison_Runner extends \MFM\WP_Background_Process {

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
	protected $action = 'file_comparison_runner';

	/**
	 * Undocumented function
	 *
	 * @param array $item - Incoming.
	 *
	 * @return bool
	 *
	 * @since 2.0.0
	 */
	protected function task( $item ) {
		$changes = DB_Handler::compare_file_changes( $item['path'], $item['data_hash'], $item['file_paths'], $item['file_hashes'], $item['file_permissions'] );

		if ( ! empty( $changes ) ) {
			$is_known         = false;
			$is_active_plugin = false;
			$is_update        = false;

			$known_plugins_and_themes = Settings_Helper::get_site_option_cached( MFM_PREFIX . 'plugins_and_themes_history' );
			$current_id               = Settings_Helper::get_site_option_cached( MFM_PREFIX . 'active_scan_id' );

			foreach ( $known_plugins_and_themes as $known ) {
				if ( str_contains( $item['path'], $known ) || $item['path'] === $known ) {
					$is_known     = true;
					$item['path'] = $known;
				}
			}

			if ( $is_known ) {
				$is_active_plugin = Plugins_And_Themes_Monitor::is_currently_active_plugin( $item['path'] );
			}

			if ( $is_active_plugin ) {
				$updates = Settings_Helper::get_site_option_cached( MFM_PREFIX . 'plugins_and_themes_recent_updates', array() );
				foreach ( $updates as $key => $updated ) {
					if ( str_contains( $item['path'], $updated ) ) {
						$is_update = true;
						unset( $updates[ $key ] );
					}
				}
				if ( $is_update ) {
					update_site_option( MFM_PREFIX . 'plugins_and_themes_recent_updates', $updates );
					wp_cache_delete( MFM_PREFIX . 'plugins_and_themes_recent_updates' );
				}
			}

			$context_of_changes = array();
			if ( isset( $changes['modified'] ) ) {
				array_push( $context_of_changes, 'modified' );
			}
			if ( isset( $changes['renamed'] ) ) {
				array_push( $context_of_changes, 'renamed' );
				foreach ( $changes['renamed'] as $renamed ) {
					$changes['modified'][] = $renamed;
				}
				unset( $changes['renamed'] );
			}
			if ( isset( $changes['added'] ) ) {
				array_push( $context_of_changes, 'added' );
			}
			if ( isset( $changes['removed'] ) ) {
				array_push( $context_of_changes, 'removed' );
			}
			if ( isset( $changes['permissions_changed'] ) ) {
				array_push( $context_of_changes, 'permissions-changed' );
			}

			if ( ! empty( $changes ) ) {
				$data = array(
					'path'        => trailingslashit( $item['path'] ),
					'event_type'  => ( ! $is_update ) ? strtolower( Directory_And_File_Helpers::determine_directory_context( $item['path'] ) ) . '-file-' . implode( ',', $context_of_changes ) : strtolower( Directory_And_File_Helpers::determine_directory_context( $item['path'] ) ) . '-updated',
					'time'        => current_time( 'timestamp' ), // phpcs:ignore WordPress.DateTime.CurrentTimeTimestamp.Requested
					'is_read'     => 'no',
					'data'        => maybe_serialize( $changes ),
					'scan_run_id' => $current_id,
				);

				DB_Handler::add_event( $data, $is_known );
			}
		}

		return false;
	}

	/**
	 * Background process chain ID.
	 *
	 * @var string
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
