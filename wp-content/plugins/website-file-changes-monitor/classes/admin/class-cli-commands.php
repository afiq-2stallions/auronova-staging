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
use MFM\Helpers\Events_Helper;

/**
 * MFM CLI Commands.
 *
 * @since 2.3.0
 */
class CLI_Commands extends \WP_CLI_Command {

	/**
	 * Mark file events as read (remove them).
	 *
	 * ## OPTIONS
	 *
	 * [--type=<type>]
	 * : Which type of event to mark as read.
	 * ---
	 * default: all
	 * options:
	 *  - all
	 *  - file-scan-started
	 *  - core-file-modified
	 *  - core-file-renamed
	 *  - core-file-permissions-changed
	 *  - core-directory-permissions-changed
	 *  - core-file-added
	 *  - core-file-removed
	 *  - core-directory-added
	 *  - core-directory-modified
	 *  - core-directory-removed
	 *  - file-scan-complete
	 *  - file-scan-aborted
	 *  - other-file-added
	 *  - other-file-modified
	 *  - other-file-removed
	 *  - other-file-renamed
	 *  - other-file-permissions-changed
	 *  - other-directory-permissions-changed
	 *  - other-directory-added
	 *  - other-directory-modified
	 *  - other-directory-removed
	 *  - plugin-file-added
	 *  - plugin-file-modified
	 *  - plugin-file-renamed
	 *  - plugin-file-permissions-changed
	 *  - plugin-directory-permissions-changed
	 *  - plugin-directory-removed
	 *  - plugin-directory-added
	 *  - plugin-updated
	 *  - theme-file-added
	 *  - theme-file-modified
	 *  - theme-file-renamed
	 *  - theme-directory-permissions-changed
	 *  - theme-directory-removed
	 *  - theme-directory-added
	 *  - theme-updated
	 * ---
	 *
	 * ## EXAMPLES
	 *
	 *  # Delete all plugin modified events.
	 *  $ wp mfm_mark_as_read --type="plugin-file-modified"
	 *  Success: All plugin-file-modified items marked as read.
	 *
	 * @since 2.3.0
	 */
	public static function mark_read( $args, $assoc_args ) {
		if ( isset( $assoc_args['type'] ) ) {
			$types = array_keys( Events_Helper::get_event_label_array() );
			if ( in_array( strtolower( $assoc_args['type'] ), $types, true ) ) {
				DB_Handler::delete_from_where( wp_create_nonce( MFM_PREFIX . 'delete_data' ), DB_Handler::$events_table_name, 'event_type', strtolower( $assoc_args['type'] ), true );
				\WP_CLI::success( 'All ' . $assoc_args['type'] . ' items marked as read.' );
			} else {
				\WP_CLI::line( 'Supplied event type "' . $assoc_args['type'] . '" not found' );
			}
		} else {
			\WP_CLI::success( 'All items marked as read.' );
		}
	}
}
