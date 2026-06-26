<?php
/**
 * Handles recording directory data.
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

use MFM\MFM_Fast_Cache;
use MFM\Helpers\Directory_And_File_Helpers;

/**
 * Background runner for dirs.
 *
 * @since 2.0.0
 */
class Directory_Runner extends \MFM\WP_Background_Process {

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
	protected $action = 'directory_runner';

	/**
	 * Main task logic.
	 *
	 * @param string $incoming_item - Incoming.
	 *
	 * @return bool
	 *
	 * @since 2.0.0
	 */
	protected function task( $incoming_item ) {
		if ( is_dir( $incoming_item ) ) {

			$items = Directory_And_File_Helpers::get_directories_from_path( $incoming_item );

			if ( ! empty( $items ) ) {

				foreach ( $items as $item ) {
					if ( ! Directory_And_File_Helpers::is_path_excluded( $item ) ) {
						\MFM::push_item_to_list( $item );
					}
				}
			}
		}

		if ( $incoming_item ) {
			MFM_Fast_Cache::add_to_cache( "('" . esc_sql( $incoming_item ) . "', '" . current_time( 'timestamp' ) . "', '" . substr( sprintf( '%o', fileperms( $incoming_item ) ), -4 ) . "')" );  // phpcs:ignore WordPress.DateTime.CurrentTimeTimestamp.Requested
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
