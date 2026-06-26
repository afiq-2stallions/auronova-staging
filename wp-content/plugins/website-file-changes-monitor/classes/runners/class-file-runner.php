<?php
/**
 * Handles gathering of file data..
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

/**
 * Main file discovery.
 *
 * @since 2.0.0
 */
class File_Runner extends \MFM\WP_Background_Process {

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
	protected $action = 'file_runner';

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
		$path = $item['path'];

		if ( is_dir( $path ) ) {

			$files = Directory_And_File_Helpers::scan_and_store_files( $path );

			/**
			 * Always insert a row, even for empty directories. Without a row in mfm_scanned_files,
			 * compare_file_changes() is never called for this directory, so files that were deleted
			 * leaving it empty would never be detected as "removed".
			 */
			$data = array(
				'path'             => $path,
				'file_paths'       => \maybe_serialize( $files['paths'] ?? array() ),
				'file_hashes'      => \maybe_serialize( $files['hashs'] ?? array() ),
				'file_timestamps'  => \maybe_serialize( $files['timestamps'] ?? array() ),
				'data_hash'        => md5( \maybe_serialize( $files ) ),
				'file_permissions' => \maybe_serialize( $files['permissions'] ?? array() ),
			);

			DB_Handler::insert_data( DB_Handler::$scanned_files_table_name, $data );
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
