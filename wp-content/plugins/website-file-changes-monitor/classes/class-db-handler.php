<?php
/**
 * Handle and setup the plugins DB.
 *
 * @package MFM
 * @since 2.0.0
 */

declare(strict_types=1);

namespace MFM;

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

use MFM\Helpers\Logger;
use MFM\Admin\AJAX_Tasks;
use MFM\Crons\Cron_Handler;
use MFM\Scan_Status_Monitor;
use MFM\Helpers\Settings_Helper;
use MFM\Plugins_And_Themes_Monitor;
use MFM\Helpers\Directory_And_File_Helpers;

/**
 * Main DB class.
 *
 * @since 2.0.0
 */
class DB_Handler {

	/**
	 * Name for stored dir table.
	 *
	 * @var string
	 *
	 * @since 2.0.0
	 */
	public static $stored_directories_table_name = MFM_PREFIX . 'stored_directories';

	/**
	 * Name for stored scanned dir table.
	 *
	 * @var string
	 *
	 * @since 2.0.0
	 */
	public static $scanned_directories_table_name = MFM_PREFIX . 'scanned_directories';

	/**
	 * Name for stored files table.
	 *
	 * @var string
	 *
	 * @since 2.0.0
	 */
	public static $stored_files_table_name = MFM_PREFIX . 'stored_files';

	/**
	 * Name for stored scanned files table.
	 *
	 * @var string
	 *
	 * @since 2.0.0
	 */
	public static $scanned_files_table_name = MFM_PREFIX . 'scanned_files';

	/**
	 * Name for stored events table.
	 *
	 * @var string
	 *
	 * @since 2.0.0
	 */
	public static $events_table_name = MFM_PREFIX . 'events';

	/**
	 * Name for stored events meta table.
	 *
	 * @var string
	 *
	 * @since 2.0.0
	 */
	public static $events_meta_table_name = MFM_PREFIX . 'events_metadata';

	/**
	 * Setup and install required tables.
	 *
	 * @param boolean $setup_scan_dbs_only - Setup scanning only.
	 *
	 * @return void
	 *
	 * @since 2.0.0
	 */
	public static function install( $setup_scan_dbs_only = false ) {
		global $wpdb;

		$needed = get_site_option( MFM_PREFIX . 'db_setup_complete' );

		if ( $needed ) {
			return;
		}

		$db_names = array(
			self::$stored_directories_table_name,
			self::$scanned_directories_table_name,
		);

		$file_db_names = array(
			self::$stored_files_table_name,
			self::$scanned_files_table_name,
		);

		if ( $setup_scan_dbs_only ) {
			unset( $db_names[0] );
			unset( $file_db_names[0] );
		}

		foreach ( $db_names as $name ) {
			$table_name      = $wpdb->prefix . $name;
			$charset_collate = $wpdb->get_charset_collate();

			$sql = "CREATE TABLE $table_name (
                id mediumint(9) NOT NULL AUTO_INCREMENT,
                path TEXT NOT NULL,
                time TEXT NOT NULL,
                permissions TEXT NOT NULL,
                PRIMARY KEY (id)
            ) $charset_collate;";

			require_once ABSPATH . 'wp-admin/includes/upgrade.php';
			dbDelta( $sql );
		}

		foreach ( $file_db_names as $name ) {
			$table_name      = $wpdb->prefix . $name;
			$charset_collate = $wpdb->get_charset_collate();

			$sql = "CREATE TABLE $table_name (
                id mediumint(9) NOT NULL AUTO_INCREMENT,
                path TEXT NOT NULL,
                file_paths TEXT NOT NULL,
                file_hashes TEXT NOT NULL,
                file_timestamps TEXT NOT NULL,
                file_permissions TEXT NOT NULL,
                data_hash TEXT NOT NULL,
                time TEXT NOT NULL,
                PRIMARY KEY (id)
            ) $charset_collate;";

			require_once ABSPATH . 'wp-admin/includes/upgrade.php';
			dbDelta( $sql );
		}

		$table_name      = $wpdb->prefix . self::$events_table_name;
		$charset_collate = $wpdb->get_charset_collate();

		$sql = "CREATE TABLE $table_name (
            id mediumint(9) NOT NULL AUTO_INCREMENT,
            path TEXT NOT NULL,
            data TEXT NOT NULL,
            event_type TEXT NOT NULL,
            is_read TEXT NOT NULL,
            time TEXT NOT NULL,
            scan_run_id TEXT NOT NULL,
            PRIMARY KEY (id)
        ) $charset_collate;";

		require_once ABSPATH . 'wp-admin/includes/upgrade.php';
		dbDelta( $sql );

		$table_name      = $wpdb->prefix . self::$events_meta_table_name;
		$charset_collate = $wpdb->get_charset_collate();

		$sql = "CREATE TABLE $table_name (
            id mediumint(9) NOT NULL AUTO_INCREMENT,
            event_id mediumint(9),
            data TEXT NOT NULL,
            event_type TEXT NOT NULL,
            scan_run_id TEXT NOT NULL,
            PRIMARY KEY (id)
        ) $charset_collate;";

		require_once ABSPATH . 'wp-admin/includes/upgrade.php';
		dbDelta( $sql );

		// Create new perms column.
		self::create_permissions_column();

		if ( ! get_site_option( MFM_PREFIX . 'last_scan_time', false ) ) {
			update_site_option( MFM_PREFIX . 'initial_setup_needed', true );
		}
	}

	/**
	 * Dump temp data into stored tables.
	 *
	 * @return void
	 *
	 * @since 2.0.0
	 */
	public static function store_scanned_data() {
		global $wpdb;

		self::drop_table( self::$stored_directories_table_name );
		self::drop_table( self::$stored_files_table_name );

		$charset_collate = $wpdb->get_charset_collate();

		$wpdb->query( $wpdb->prepare( 'CREATE TABLE %1s %2s (SELECT * FROM %3s)', $wpdb->prefix . self::$stored_files_table_name, $charset_collate, $wpdb->prefix . self::$scanned_files_table_name ) ); // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching, WordPress.DB.DirectDatabaseQuery.SchemaChange, WordPress.DB.PreparedSQLPlaceholders.UnquotedComplexPlaceholder
		$wpdb->query( $wpdb->prepare( 'CREATE TABLE %1s %2s (SELECT * FROM %3s)', $wpdb->prefix . self::$stored_directories_table_name, $charset_collate, $wpdb->prefix . self::$scanned_directories_table_name ) ); // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching, WordPress.DB.DirectDatabaseQuery.SchemaChange, WordPress.DB.PreparedSQLPlaceholders.UnquotedComplexPlaceholder
	}

	/**
	 * Insert data into given table.
	 *
	 * @param string $table_to_insert_to - Destination.
	 * @param array  $data_to_insert - Data.
	 *
	 * @return int - Resulting ID.
	 *
	 * @since 2.0.0
	 */
	public static function insert_data( $table_to_insert_to, $data_to_insert ) {

		// Check if given correct table.
		if ( ! self::check_table_name( $table_to_insert_to ) ) {
			return;
		}

		global $wpdb;
		$path       = $data_to_insert['path'];
		$table_name = $wpdb->prefix . $table_to_insert_to;

		if ( $table_to_insert_to !== self::$events_table_name && $table_to_insert_to !== self::$events_meta_table_name ) {
			self::delete_from_where_like( wp_create_nonce( MFM_PREFIX . 'delete_data' ), $table_to_insert_to, 'path', $path );
		}

		$wpdb->insert( $table_name, $data_to_insert ); // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery

		return $wpdb->insert_id;
	}

	/**
	 * Gather and report directory changes.
	 *
	 * @return void
	 *
	 * @since 2.0.0
	 */
	public static function compare_and_report_directory_changes() {
		$is_known                 = false;
		$is_known_update          = false;
		$final                    = array();
		$known_plugins_and_themes = Settings_Helper::get_site_option_cached( MFM_PREFIX . 'plugins_and_themes_history' );
		$new_event                = 0;
		$ignore_dirs              = Settings_Helper::get_setting_cached( 'excluded_directories' );

		// Compare Dirs.
		$missing_since_last_scan = self::get_paths_not_in_table( self::$stored_directories_table_name, self::$scanned_directories_table_name );
		$added_since_last_scan   = self::get_paths_not_in_table( self::$scanned_directories_table_name, self::$stored_directories_table_name );

		$current_id = get_site_option( MFM_PREFIX . 'active_scan_id' );

		// Directory has been removed since last scan.
		foreach ( $missing_since_last_scan as $item ) {
			$old_data = self::get_held_file_paths( self::$stored_files_table_name, $item['path'] );

			// Is path a child of an ignored path?
			$lookup = false;
			foreach ( $ignore_dirs as $ignored ) {
				$lookup = strpos( $item['path'], $ignored );
			}

			if ( false !== $lookup ) {
				$msg  = Logger::get_log_timestamp() . ' Directory was removed but is child of ignored, so skipping:' . " \n";
				$msg .= Logger::get_log_timestamp() . ' ' . $item['path'] . " \n";
				Logger::write_to_log( $msg );
				continue;
			}

			// Check if is a known theme or plugin.
			foreach ( $known_plugins_and_themes as $known ) {
				if ( str_contains( $item['path'], $known ) ) {
					if ( Plugins_And_Themes_Monitor::is_currently_active_plugin( $item['path'] ) ) {
						continue;
					}

					$final = self::prepare_file_data( self::get_held_file_paths_fuzzy( self::$stored_files_table_name, $known ) );

					$old_data = ( count( $final ) >= 500 ) ? 'external_metadata' : implode( ',', $final );

					// If large number of files found, send oversplit to metadata.
					if ( count( $final ) > 500 ) {
						$part_a = array_slice( $final, 0, 500, true );
						$part_b = array_slice( $final, 500, count( $final ), true );
						array_push( $part_a, 'additional_external_data' );
						$old_data = implode( ',', $part_a );
					}

					$is_known = $known;
				}
			}

			if ( Plugins_And_Themes_Monitor::is_currently_active_plugin( $item['path'] ) || Directory_And_File_Helpers::is_path_ignored( $item['path'] ) || Directory_And_File_Helpers::is_path_excluded( $item['path'] ) ) {
				continue;
			}

			$data = array(
				'path'        => ( $is_known ) ? $is_known : $item['path'],
				'event_type'  => strtolower( Directory_And_File_Helpers::determine_directory_context( $item['path'] ) ) . '-directory-removed',
				'time'        => current_time( 'timestamp' ), // phpcs:ignore WordPress.DateTime.CurrentTimeTimestamp.Requested
				'is_read'     => 'no',
				'data'        => maybe_serialize( self::format_event_data_string( $old_data, 'removed' ) ),
				'scan_run_id' => $current_id,
			);

			// Fire off an event for this change.
			$check = ( $is_known ) ? $is_known : $item['path'];
			if ( ! self::was_event_reported( $check, $current_id ) ) {
				$new_event = self::add_event( $data );

				// Fire off additional metadata if needed.
				if ( count( $final ) > 500 && $new_event > 0 ) {
					self::insert_event_metadata( $part_b, $new_event, $current_id, 'removed' );
				}
			}
		}

		foreach ( $added_since_last_scan as $item ) {
			$old_data = self::get_held_file_paths( self::$scanned_files_table_name, $item['path'] );

			// Is path a child of an ignored path?
			$lookup = false;
			foreach ( $ignore_dirs as $ignored ) {
				$lookup = strpos( $item['path'], $ignored );
			}

			if ( false !== $lookup ) {
				$msg  = Logger::get_log_timestamp() . ' Directory was added but is child of ignored, so skipping:' . " \n";
				$msg .= Logger::get_log_timestamp() . ' ' . $item['path'] . " \n";
				Logger::write_to_log( $msg );
				continue;
			}

			$event_suffix = 'added';

			// Check if is a known theme or plugin.
			foreach ( $known_plugins_and_themes as $known ) {
				if ( str_contains( $item['path'], $known ) ) {

					// Gather results held for this theme or plugin.
					$final = self::prepare_file_data( self::get_held_file_paths_fuzzy( self::$scanned_files_table_name, $known ) );

					$old_data = ( count( $final ) >= 500 ) ? 'external_metadata' : implode( ',', $final );

					// If large number of files found, send oversplit to metadata.
					if ( count( $final ) > 500 ) {
						$part_a = array_slice( $final, 0, 500, true );
						$part_b = array_slice( $final, 500, count( $final ), true );
						array_push( $part_a, 'additional_external_data' );
						$old_data = implode( ',', $part_a );
					}

					$is_known = $known;

					if ( $is_known ) {
						$test = self::get_held_file_paths_fuzzy( self::$stored_files_table_name, $known );

						if ( ! empty( $test ) ) {
							$event_suffix    = 'updated';
							$is_known_update = true;
						}
					}
				}
			}

			if ( Directory_And_File_Helpers::is_path_ignored( $item['path'] ) || Directory_And_File_Helpers::is_path_excluded( $item['path'] ) ) {
				continue;
			}

			$data = array(
				'path'        => ( $is_known ) ? $is_known : $item['path'],
				'event_type'  => strtolower( Directory_And_File_Helpers::determine_directory_context( $item['path'] ) ) . '-directory-' . $event_suffix,
				'time'        => current_time( 'timestamp' ), // phpcs:ignore WordPress.DateTime.CurrentTimeTimestamp.Requested
				'is_read'     => 'no',
				'data'        => maybe_serialize( self::format_event_data_string( $old_data, 'added' ) ),
				'scan_run_id' => $current_id,
			);

			// Fire off an event for this change.
			$check = ( $is_known ) ? $is_known : $item['path'];
			if ( ! self::was_event_reported( $check, $current_id ) ) {
				if ( ! $is_known_update ) {
					$new_event = self::add_event( $data );
				}

				// Fire off additional metadata if needed.
				if ( count( $final ) > 500 && $new_event > 0 ) {
					self::insert_event_metadata( $part_b, $new_event, $current_id, 'added' );
				}
			}
		}

		$scanned_dirs      = self::get_directory_runner_results( false );
		$stored_dirs       = self::get_directory_runner_results( false, false, true );
		$stored_dirs_paths = array_column( $stored_dirs, 'path' );

		// Check permissions changes.
		foreach ( $scanned_dirs as $dir_info ) {

			// Is path a child of an ignored path?
			$lookup = false;
			foreach ( $ignore_dirs as $ignored ) {
				$lookup = strpos( $dir_info['path'], $ignored );
			}

			if ( false !== $lookup ) {
				$msg  = Logger::get_log_timestamp() . ' Directory had permissions change, but is child of ignored, so skipping:' . " \n";
				$msg .= Logger::get_log_timestamp() . ' ' . $dir_info['path'] . " \n";
				Logger::write_to_log( $msg );
				continue;
			}

			if ( Directory_And_File_Helpers::is_path_ignored( $dir_info['path'] ) || Directory_And_File_Helpers::is_path_excluded( $dir_info['path'] ) ) {
				continue;
			}

			if ( ! isset( $stored_dirs[ array_search( $dir_info['path'], $stored_dirs_paths, true ) ]['permissions'] ) ) {
				continue;
			}

			$stored_permission = $stored_dirs[ array_search( $dir_info['path'], $stored_dirs_paths, true ) ]['permissions'];

			if ( empty( $dir_info['permissions'] ) || empty( $stored_permission ) ) {
				continue;
			}

			if ( $dir_info['permissions'] !== $stored_permission ) {
				$data = array(
					'path'        => $dir_info['path'],
					'event_type'  => strtolower( Directory_And_File_Helpers::determine_directory_context( $dir_info['path'] ) ) . '-directory-permissions-changed',
					'time'        => current_time( 'timestamp' ), // phpcs:ignore WordPress.DateTime.CurrentTimeTimestamp.Requested
					'is_read'     => 'no',
					'data'        => maybe_serialize( esc_html__( 'Previous: ', 'website-file-changes-monitor' ) . '<b>' . $stored_permission . '</b>' . ' ' . esc_html__( 'Current: ', 'website-file-changes-monitor' ) . '<b>' . $dir_info['permissions'] . '</b>' ), // phpcs:ignore Generic.Strings.UnnecessaryStringConcat.Found
					'scan_run_id' => $current_id,
				);

				self::add_event( $data, false, true );
			}
		}

		\MFM::start_file_comparison_runner();
	}

	/**
	 * Compare stored file data against current scan data to detect changes.
	 *
	 * Detects: added, removed, modified, renamed, and permissions-changed files.
	 * Called once per directory from File_Comparison_Runner::task().
	 *
	 * @param string $directory_path            - Absolute directory path (e.g. /var/www/html/wp-content/themes).
	 * @param string $current_data_hash         - Hash of the current scan data for this directory.
	 * @param array  $current_file_paths        - Current scan relative file paths with filename (e.g. wp-content/themes/index.php).
	 * @param array  $current_file_hashes       - Current scan file hashes (parallel to paths).
	 * @param array  $current_file_permissions  - Current scan file permissions (parallel to paths).
	 *
	 * @return array - Example: array( 'added' => array( 'wp-content/file.php' ), 'modified' => array( 'index.php' ) ).
	 *
	 * @since 2.0.0
	 * @since 2.3.0 - refactored to fix a bunch of issues and optimize for large directories.
	 */
	public static function compare_file_changes( $directory_path, $current_data_hash, $current_file_paths, $current_file_hashes, $current_file_permissions ) {

		// Exit early if path is a symbolic link (to avoid endless loops) or if it's an excluded/ignored path.
		if ( is_link( $directory_path ) || Directory_And_File_Helpers::is_path_excluded( $directory_path ) || Directory_And_File_Helpers::is_path_ignored( $directory_path ) ) {
			return array();
		}

		global $wpdb;
		$stored_files_table = $wpdb->prefix . self::$stored_files_table_name;

		/**
		 * Each row in mfm_stored_files represents a directory (not a single file).
		 * Fetch only data_hash first: if unchanged we skip loading the full row
		 * (which contains serialized arrays of every file inside the directory).
		 */
		$stored_hash_row_of_folder = $wpdb->get_row( $wpdb->prepare( 'SELECT data_hash FROM %1s WHERE path = %s', $stored_files_table, $directory_path ), ARRAY_A ); // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching, WordPress.DB.PreparedSQLPlaceholders.UnquotedComplexPlaceholder

		if ( null === $stored_hash_row_of_folder ) {
			/**
			 * No baseline row for this directory: MFM has never scanned it before.
			 * Treat every file currently inside it as newly added.
			 */
			$current_file_paths_array = is_array( $current_file_paths ) ? $current_file_paths : \maybe_unserialize( $current_file_paths );

			// If not an array, return early to avoid warnings or errors.
			if ( ! is_array( $current_file_paths_array ) ) {
				return array();
			}

			$file_changes_found = array();

			foreach ( $current_file_paths_array as $current_relative_file_path ) {
				if ( Directory_And_File_Helpers::should_file_check_continue( $current_relative_file_path ) ) {
					$file_changes_found['added'][] = $current_relative_file_path;
				}
			}

			return $file_changes_found;
		}

		if ( $stored_hash_row_of_folder['data_hash'] === $current_data_hash ) {
			// Directory is known and unchanged since last scan: nothing to report.
			return array();
		}

		// Hash changed: fetch the full row now to compare file-level details.
		$stored_directory_row = $wpdb->get_results( $wpdb->prepare( 'SELECT * FROM %1s WHERE path = %s', $stored_files_table, $directory_path ), ARRAY_A ); // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching, WordPress.DB.PreparedSQLPlaceholders.UnquotedComplexPlaceholder

		// Deserialize stored data from DB (serialized strings).
		$stored_file_paths       = \maybe_unserialize( $stored_directory_row[0]['file_paths'] );
		$stored_file_hashes      = \maybe_unserialize( $stored_directory_row[0]['file_hashes'] );
		$stored_file_permissions = \maybe_unserialize( $stored_directory_row[0]['file_permissions'] );

		// Current data arrives as arrays from the background process queue. Guard against double-serialization.
		$current_file_paths_array       = is_array( $current_file_paths ) ? $current_file_paths : maybe_unserialize( $current_file_paths );
		$current_file_hashes_array      = is_array( $current_file_hashes ) ? $current_file_hashes : maybe_unserialize( $current_file_hashes );
		$current_file_permissions_array = is_array( $current_file_permissions ) ? $current_file_permissions : maybe_unserialize( $current_file_permissions );

		/**
		 * Build lookup maps. Use paths as index because they are unique and we avoid issues
		 * that we would have with regular indexes: e.g. /var/www/html/wp-content/plugins
		 * may be index 2 in one scan, and could be it's index 5 in another.
		 */
		$stored_path_to_index  = array_flip( $stored_file_paths );
		$current_path_to_index = array_flip( $current_file_paths_array );

		// Hash => list of paths with that hash (for rename detection).
		$stored_hash_to_paths = array();

		foreach ( $stored_file_paths as $i => $stored_path ) {
			if ( isset( $stored_file_hashes[ $i ] ) ) {
				$stored_hash_to_paths[ $stored_file_hashes[ $i ] ][] = $stored_path;
			}
		}

		$current_hash_to_paths = array();

		foreach ( $current_file_paths_array as $i => $current_path ) {
			if ( isset( $current_file_hashes_array[ $i ] ) ) {
				$current_hash_to_paths[ $current_file_hashes_array[ $i ] ][] = $current_path;
			}
		}

		// Core files set for lookup.
		$core_files_set        = array_flip( Directory_And_File_Helpers::create_core_file_keys( true, false ) );
		$max_file_size_setting = Settings_Helper::get_setting_cached( 'max-file-size', 5 );

		// 1048576 is 1MB in bytes (1024 × 1024). So it converts the setting (stored in MB) to bytes for comparison against filesize(), which returns bytes.
		$file_size_limit_bytes = $max_file_size_setting * 1048576;

		$file_changes_found = array();

		/**
		 * FIRST LOOP: Iterate stored file paths.
		 * Detect: removed, renamed (old path), modified, permissions_changed.
		 */
		foreach ( $stored_file_paths as $stored_index => $stored_relative_file_path_with_name ) {

			// Skip excluded files/extensions. Pass true for $is_check_on_removal since stored files may no longer exist on disk.
			if ( ! Directory_And_File_Helpers::should_file_check_continue( $stored_relative_file_path_with_name, true ) ) {
				continue;
			}

			// Skip files that exceed the size limit (only if the file still exists).
			$absolute_file_path = ABSPATH . $stored_relative_file_path_with_name;

			if ( is_file( $absolute_file_path ) && filesize( $absolute_file_path ) > $file_size_limit_bytes ) {
				continue;
			}

			$file_exists_in_current_scan = isset( $current_path_to_index[ $stored_relative_file_path_with_name ] );

			if ( ! $file_exists_in_current_scan ) {
				// File path is gone from current scan.
				if ( ! is_file( $absolute_file_path ) ) {
					// File no longer exists on disk: it was removed.
					$file_changes_found['removed'][] = $stored_relative_file_path_with_name;
				} else {
					// File still exists but path is not in current scan. Check for rename via hash.
					$stored_hash_for_this_file = $stored_file_hashes[ $stored_index ] ?? null;

					if ( $stored_hash_for_this_file && isset( $current_hash_to_paths[ $stored_hash_for_this_file ] ) ) {
						$file_changes_found['renamed'][] = $stored_relative_file_path_with_name;
					}
				}
				continue;
			}

			// File exists in both stored and current scans. Compare hashes and permissions.
			$current_index_for_this_file = $current_path_to_index[ $stored_relative_file_path_with_name ];

			$stored_hash_for_this_file  = $stored_file_hashes[ $stored_index ] ?? null;
			$current_hash_for_this_file = $current_file_hashes_array[ $current_index_for_this_file ] ?? null;

			$stored_permission_for_this_file  = $stored_file_permissions[ $stored_index ] ?? null;
			$current_permission_for_this_file = $current_file_permissions_array[ $current_index_for_this_file ] ?? null;

			$is_core_file       = isset( $core_files_set[ ltrim( $stored_relative_file_path_with_name, '/' ) ] );
			$hash_changed       = ( null !== $stored_hash_for_this_file && null !== $current_hash_for_this_file && $stored_hash_for_this_file !== $current_hash_for_this_file );
			$permission_changed = ( ! empty( $stored_permission_for_this_file ) && ! empty( $current_permission_for_this_file ) && $stored_permission_for_this_file !== $current_permission_for_this_file );

			if ( ! $is_core_file && $hash_changed ) {
				$file_changes_found['modified'][] = $stored_relative_file_path_with_name;
			}

			if ( $permission_changed ) {
				$file_changes_found['permissions_changed'][] = array(
					'file'     => $stored_relative_file_path_with_name,
					'previous' => $stored_permission_for_this_file,
					'current'  => $current_permission_for_this_file,
				);
			}
		}

		/**
		 * SECOND LOOP: Iterate current file paths.
		 * Detect: added, renamed (new path).
		 */
		foreach ( $current_file_paths_array as $current_index => $current_relative_file_path_with_name ) {

			if ( ! Directory_And_File_Helpers::should_file_check_continue( $current_relative_file_path_with_name ) ) {
				continue;
			}

			$file_existed_in_stored_scan = isset( $stored_path_to_index[ $current_relative_file_path_with_name ] );

			if ( ! $file_existed_in_stored_scan ) {
				// New path not in stored scan. Check if it's the new-name side of a rename.
				$current_hash_for_this_file = $current_file_hashes_array[ $current_index ] ?? null;

				$is_rename_new_path = false;

				if ( $current_hash_for_this_file && isset( $stored_hash_to_paths[ $current_hash_for_this_file ] ) ) {
					// Same hash existed in stored scan. Verify old path is gone from current scan (true rename).
					foreach ( $stored_hash_to_paths[ $current_hash_for_this_file ] as $old_path_with_same_hash ) {
						if ( ! isset( $current_path_to_index[ $old_path_with_same_hash ] ) ) {
							$is_rename_new_path = true;
							break;
						}
					}
				}

				if ( $is_rename_new_path ) {
					$file_changes_found['renamed'][] = $current_relative_file_path_with_name;
				} else {
					$file_changes_found['added'][] = $current_relative_file_path_with_name;
				}
			}
		}

		return $file_changes_found;
	}

	/**
	 * Add a new events.
	 *
	 * @param array $data - Incoming data.
	 * @param bool  $update_if_found - Update.
	 * @param bool  $skip_cleaning - Skip cleaning data.
	 *
	 * @return $insert_id - Event ID.
	 *
	 * @since 2.0.0
	 */
	public static function add_event( $data, $update_if_found = false, $skip_cleaning = false ) {
		global $wpdb;
		$table_name = $wpdb->prefix . self::$events_table_name;
		$insert_id  = 0;

		if ( ! $skip_cleaning ) {
			$data['data'] = self::clean_event_file_data( $data['data'] );
		}

		$found = $wpdb->get_results( $wpdb->prepare( 'SELECT * FROM %1s WHERE path = %s AND scan_run_id = %d', $table_name, $data['path'], $data['scan_run_id'] ), ARRAY_A ); // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching, WordPress.DB.PreparedSQLPlaceholders.UnquotedComplexPlaceholder

		if ( isset( $found[0] ) ) {
			$difference = $data['time'] - $found[0]['time'];

			if ( $update_if_found ) {
				$input_data = serialize( unserialize( $found[0]['data'] ) + unserialize( $data['data'] ) ); // phpcs:ignore WordPress.PHP.DiscouragedPHPFunctions.serialize_serialize, WordPress.PHP.DiscouragedPHPFunctions.serialize_unserialize
				$wpdb->query( $wpdb->prepare( "UPDATE $table_name SET data = %s WHERE id = %d", $input_data, $found[0]['id'] ) ); // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared, WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching
			} elseif ( $difference > 10 ) {
				$insert_id = self::insert_data( self::$events_table_name, $data );
			}
		} else {
			$insert_id = self::insert_data( self::$events_table_name, $data );
		}

		if ( $insert_id > 0 ) {
			do_action( MFM_PREFIX . 'file_change_event_created', $data );
		}

		wp_cache_delete( MFM_PREFIX . 'events_cache' );

		return $insert_id;
	}

	/**
	 * Filters out files with excluded extensions from scan event data before it gets stored in the database.
	 *
	 * @param string $data - Serialized event file data to clean.
	 *
	 * @return string Serialized cleaned data.
	 *
	 * @since 2.1.0
	 * @since 2.3.0 - Refactored to handle various data formats and edge cases.
	 */
	public static function clean_event_file_data( $data ): string {
		$return_data = array();

		if ( ! $data ) {
			return \maybe_serialize( array() );
		}

		$excluded_file_extensions = Settings_Helper::get_setting_cached( 'excluded_file_extensions' );

		$excluded_file_extensions = is_array( $excluded_file_extensions ) ? $excluded_file_extensions : array();

		$data = \maybe_unserialize( $data );

		if ( ! is_array( $data ) ) {
			return maybe_serialize( array() );
		}

		foreach ( $data as $type => $files ) {

			if ( ! is_array( $files ) ) {
				continue;
			}

			foreach ( $files as $file ) {
				if ( is_array( $file ) && isset( $file['file'] ) ) {
					$file_path_check = (string) $file['file'];
				} else {
					$file_path_check = (string) $file;
				}

				$file_extension = false;

				if ( $file_path_check && ! empty( $file_path_check ) ) {
					$dot_position = strrchr( $file_path_check, '.' );

					if ( false !== $dot_position ) {
						$file_extension = substr( $dot_position, 1 );
					}
				}

				if ( $file_extension && in_array( $file_extension, $excluded_file_extensions, true ) ) {
					continue;
				}

				$return_data[ $type ][] = $file;
			}
		}

		return \maybe_serialize( $return_data );
	}

	/**
	 * Check if event has handled.
	 *
	 * @param string $path - Path.
	 * @param string $scan_run_id - Current scan ID.
	 * @return bool - Was reported.
	 */
	public static function was_event_reported( $path, $scan_run_id ) {
		global $wpdb;
		$table_name = $wpdb->prefix . self::$events_table_name;
		$found      = $wpdb->get_results( $wpdb->prepare( 'SELECT * FROM %1s WHERE path = %s AND scan_run_id = %d', $table_name, $path, $scan_run_id ), ARRAY_A );  // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching, WordPress.DB.PreparedSQLPlaceholders.UnquotedComplexPlaceholder

		return isset( $found[0] );
	}

	/**
	 * Insert over spill into table
	 *
	 * @param array  $part_b - Data.
	 * @param int    $new_event_id - Event ID.
	 * @param int    $current_id - Scan ID.
	 * @param string $event_type - Event type.
	 *
	 * @return int - Result ID.
	 *
	 * @since 2.0.0
	 * @since 2.3.0 Replaced raw string concatenation with $wpdb->prepare() for values.
	 */
	public static function insert_event_metadata( $part_b, $new_event_id, $current_id, $event_type = 'removed' ) {
		global $wpdb;
		$metadata_table_name = $wpdb->prefix . self::$events_meta_table_name;

		$values = array();

		foreach ( $part_b as $item => $val ) {
			$values[] = $wpdb->prepare( '(%d, %s, %s, %d)', (int) $new_event_id, $event_type, $val, (int) $current_id );
		}

		$values_string = implode( ', ', $values );

		$setting_save = $wpdb->query( $wpdb->prepare( 'INSERT INTO %1s ( event_id, event_type, data, scan_run_id ) VALUES ', $metadata_table_name ) . $values_string ); // phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared, WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching, WordPress.DB.PreparedSQLPlaceholders.UnquotedComplexPlaceholder

		return $setting_save;
	}

	/**
	 * Format event data.
	 *
	 * @param mixed  $data - Incoming data.
	 * @param string $event_type - Event type.
	 *
	 * @return array - Result.
	 *
	 * @since 2.0.0
	 */
	public static function format_event_data_string( $data, $event_type = 'modified' ) {
		$data = maybe_unserialize( maybe_unserialize( $data ) );

		$incoming  = is_string( $data ) && '' !== $data ? explode( ',', $data ) : $data;
		$formatted = array();

		if ( ! is_array( $incoming ) ) {
			return $formatted;
		}

		foreach ( $incoming as $item ) {
			$formatted[ $event_type ][] = maybe_unserialize( $item );
		}

		return $formatted;
	}

	/**
	 * Retrieve additional event metadata.
	 *
	 * @param integer $offset - Offset.
	 *
	 * @return void
	 *
	 * @since 2.0.0
	 */
	public static function get_event_metadata( $offset = 0 ) {
		$nonce = isset( $_POST['nonce'] ) ? sanitize_key( wp_unslash( $_POST['nonce'] ) ) : '';
		if ( empty( $nonce ) || ! wp_verify_nonce( $nonce, MFM_PREFIX . 'load_extra_metadata' ) ) {
			wp_send_json_error( array( 'message' => __( 'Failed nonce check', 'website-file-changes-monitor' ) ) );
			return;
		}

		if ( ! isset( $_POST['event_target'] ) ) {
			$return = array(
				'message' => __( 'target ID no provided', 'website-file-changes-monitor' ),
			);
			wp_send_json_error( $return );
			return;
		}

		global $wpdb;
		$metadata_table_name = $wpdb->prefix . self::$events_meta_table_name;
		$lookup_id           = sanitize_key( wp_unslash( $_POST['event_target'] ) );
		$offset              = isset( $_POST['offset'] ) ? sanitize_key( wp_unslash( $_POST['offset'] ) ) : $offset;
		$total_available     = count( $wpdb->get_results( $wpdb->prepare( 'SELECT id FROM %1s WHERE event_id = %d', $metadata_table_name, $lookup_id ), ARRAY_A ) ); // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching,  WordPress.DB.PreparedSQLPlaceholders.UnquotedComplexPlaceholder
		$data                = $wpdb->get_results( $wpdb->prepare( 'SELECT * FROM %1s WHERE event_id = %d LIMIT 500 OFFSET %d', $metadata_table_name, $lookup_id, $offset ), ARRAY_A ); // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching,  WordPress.DB.PreparedSQLPlaceholders.UnquotedComplexPlaceholder

		$return = array(
			'message'     => __( 'Done! ', 'website-file-changes-monitor' ),
			'event_data'  => $data,
			'remaining'   => ( $total_available > count( $data ) ) ? $total_available - count( $data ) - $offset : 0,
			'next_offset' => $offset + 500,
		);
		wp_send_json_success( $return );
	}

	/**
	 * Dump all scanned data ready for fresh scan.
	 *
	 * @return void
	 *
	 * @since 2.0.0
	 */
	public static function pre_scan_dump() {
		// Clear old data.
		global $wpdb;

		$nonce = wp_create_nonce( MFM_PREFIX . 'delete_data' );
		self::truncate_table( $nonce, self::$scanned_files_table_name );
		self::truncate_table( $nonce, self::$scanned_directories_table_name );
		self::delete_from_options( $nonce, '%_directory_transient_%' );
		self::delete_from_options( $nonce, '%_directory_runner_%' );
		self::delete_from_options( $nonce, '%_file_transient_%' );
		self::delete_from_options( $nonce, '%_file_runner_%' );

		$clear_event_data_size = Settings_Helper::get_setting( 'purge-length', 1 );
		$clear_event_data_size = --$clear_event_data_size;
		$current_id            = get_site_option( MFM_PREFIX . 'active_scan_id', 0 );

		$wanted_amount   = $current_id - $clear_event_data_size;
		$table_name      = $wpdb->prefix . self::$events_table_name;
		$meta_table_name = $wpdb->prefix . self::$events_meta_table_name;
		$wpdb->query( $wpdb->prepare( 'DELETE FROM %1s WHERE scan_run_id < %s', $table_name, $wanted_amount ) ); // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching, WordPress.DB.PreparedSQLPlaceholders.UnquotedComplexPlaceholder
		$wpdb->query( $wpdb->prepare( 'DELETE FROM %1s WHERE scan_run_id < %s', $meta_table_name, $wanted_amount ) ); // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching, WordPress.DB.PreparedSQLPlaceholders.UnquotedComplexPlaceholder
	}

	/**
	 * Remove all data on deactivation.
	 *
	 * @return void
	 *
	 * @since 2.0.0
	 * @since 2.3.0 Fixed issue where wp_cron was not removed on plugin deactivation or full uninstall.
	 */
	public static function on_mfm_deactivation() {
		// Clear monitor_file_changes wp_cron in case it is still scheduled.
		wp_clear_scheduled_hook( Cron_Handler::$schedule_hook );

		$needed = get_site_option( MFM_PREFIX . 'delete-data-enabled' );

		if ( 'yes' === $needed ) {
			AJAX_Tasks::purge_data( true );
		}
	}

	/**
	 * Cancel in-progress scan.
	 *
	 * @return void
	 *
	 * @since 2.0.0
	 */
	public static function cancel_current_scan() {

		if ( method_exists( \MFM::$dir_runner, 'delete_all' ) ) {
			\MFM::$dir_runner->delete_all();
		}

		if ( method_exists( \MFM::$file_runner, 'delete_all' ) ) {
			\MFM::$file_runner->delete_all();
		}

		if ( method_exists( \MFM::$file_comparison_runner, 'delete_all' ) ) {
			\MFM::$file_comparison_runner->delete_all();
		}

		if ( method_exists( \MFM::$core_runner, 'delete_all' ) ) {
			\MFM::$core_runner->delete_all();
		}

		self::delete_from_options( wp_create_nonce( MFM_PREFIX . 'delete_data' ), '%_runner_%' );

		$data = array(
			'path'        => '',
			'event_type'  => 'file-scan-aborted',
			'time'        => current_time( 'timestamp' ), // phpcs:ignore WordPress.DateTime.CurrentTimeTimestamp.Requested
			'is_read'     => 'no',
			'data'        => false,
			'scan_run_id' => get_site_option( MFM_PREFIX . 'active_scan_id', 0 ),
		);

		// Update Monitoring.
		$details = array(
			'status'               => 'scan_complete',
			'start_time'           => current_time( 'timestamp' ), // phpcs:ignore WordPress.DateTime.CurrentTimeTimestamp.Requested
			'current_step'         => 'Scan Aborted',
			'current_events_count' => 0,
		);

		self::add_event( $data );
		update_site_option( MFM_PREFIX . 'scanner_running', false );
		Scan_Status_Monitor::update_status( $details );
	}

	/**
	 * Get results from directory runner.
	 *
	 * @param boolean $return_count - Return just a count.
	 * @param integer $limit - Limit results.
	 * @param boolean $get_stored - Get stored or scanned (temp) results.
	 *
	 * @return int|array - Results.
	 *
	 * @since 2.0.0
	 */
	public static function get_directory_runner_results( $return_count = false, $limit = 0, $get_stored = false ) {
		global $wpdb;
		$table_name = ( $get_stored ) ? $wpdb->prefix . self::$stored_directories_table_name : $wpdb->prefix . self::$scanned_directories_table_name;

		if ( self::check_table_exists( $table_name ) ) {
			$sql = $wpdb->prepare( 'SELECT * FROM %1s', $table_name );  // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching, WordPress.DB.PreparedSQLPlaceholders.UnquotedComplexPlaceholder

			if ( $limit > 0 ) {
				$sql .= $wpdb->prepare( ' LIMIT %d', $limit );
			}
			$bg_jobs = $wpdb->get_results( $sql, ARRAY_A ); // phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared, WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching, WordPress.DB.PreparedSQLPlaceholders.UnquotedComplexPlaceholder
			return ( $return_count ) ? count( $bg_jobs ) : $bg_jobs;
		} else {
			return ( $return_count ) ? 0 : array();
		}
	}

	/**
	 * Get results from file runner.
	 *
	 * @param boolean $return_count - Return just a count.
	 * @param integer $limit - Limit results.
	 *
	 * @return int|array - Results.
	 *
	 * @since 2.0.0
	 */
	public static function get_file_runner_results( $return_count = false, $limit = 0 ) {
		global $wpdb;
		$table_name = $wpdb->prefix . self::$scanned_files_table_name;
		$sql        = $wpdb->prepare( 'SELECT * FROM %1s', $table_name ); // phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared, WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching, WordPress.DB.PreparedSQLPlaceholders.UnquotedComplexPlaceholder

		if ( $limit > 0 ) {
			$sql .= $wpdb->prepare( ' LIMIT %d', $limit );
		}
		$bg_jobs = $wpdb->get_results( $sql, ARRAY_A ); // phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared, WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching, WordPress.DB.PreparedSQLPlaceholders.UnquotedComplexPlaceholder
		return ( $return_count ) ? count( $bg_jobs ) : $bg_jobs;
	}

	/**
	 * Get events from the db.
	 *
	 * @param boolean $return_count - Amount to get.
	 * @param integer $limit - Limit.
	 * @param integer $offset - Offset.
	 * @param string  $events_type - Event type.
	 *
	 * @return int|array - Results.
	 *
	 * @since 2.0.0
	 */
	public static function get_events( $return_count = false, $limit = 0, $offset = 0, $events_type = 'all' ) {
		global $wpdb;
		$table_name = $wpdb->prefix . self::$events_table_name;

		if ( ! self::check_table_exists( $table_name ) ) {
			$bg_jobs = array();
			self::install();
			return ( $return_count ) ? count( $bg_jobs ) : $bg_jobs;
		}

		$sql = $wpdb->prepare( 'SELECT * FROM %1s', $table_name ); // phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared, WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching, WordPress.DB.PreparedSQLPlaceholders.UnquotedComplexPlaceholder

		if ( 'all' !== $events_type ) {
			$sql .= $wpdb->prepare( ' WHERE event_type LIKE %s', '%' . $events_type . '%' );
		}

		$sql .= ' ORDER BY time DESC';

		if ( $limit > 0 ) {
			$sql .= $wpdb->prepare( ' LIMIT %d OFFSET %d', $limit, $offset );
		}

		$events = $wpdb->get_results( $sql, ARRAY_A ); // phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared, WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching, WordPress.DB.PreparedSQLPlaceholders.UnquotedComplexPlaceholder

		return ( $return_count ) ? count( $events ) : $events;
	}

	/**
	 * Get a count of events.
	 *
	 * @param boolean $skip_non_file_events - Skip basic events.
	 *
	 * @return int - Result.
	 *
	 * @since 2.0.0
	 */
	public static function get_events_count( $skip_non_file_events = true ) {
		global $wpdb;
		$table_name = $wpdb->prefix . self::$events_table_name;

		if ( ! self::check_table_exists( $table_name ) ) {
			self::install();
			return 0;
		}

		if ( $skip_non_file_events ) {
			$sql = $wpdb->prepare( "SELECT COUNT(*) FROM %1s WHERE event_type != 'file-scan-started' AND event_type != 'file-scan-complete'", $table_name ); // phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared, WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching, WordPress.DB.PreparedSQLPlaceholders.UnquotedComplexPlaceholder
		} else {
			$sql = $wpdb->prepare( 'SELECT COUNT(*) FROM %1s', $table_name ); // phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared, WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching, WordPress.DB.PreparedSQLPlaceholders.UnquotedComplexPlaceholder
		}

		$num_rows = $wpdb->get_results( $sql, ARRAY_A ); // phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared, WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching, WordPress.DB.PreparedSQLPlaceholders.UnquotedComplexPlaceholder

		$count = array_values( $num_rows[0] );
		return (int) $count[0];
	}

	/**
	 * Get events based on a specific run ID.
	 *
	 * @param int   $scan_run_id - ID to lookup.
	 * @param array $event_types_wanted - Type lookup.
	 *
	 * @return array - Results.
	 *
	 * @since 2.0.0
	 * @since 2.3.0 Use $wpdb->esc_like() and $wpdb->prepare() for LIKE clauses. Map UI notification type values to their corresponding DB event type substrings.
	 */
	public static function get_events_for_specific_run( $scan_run_id, $event_types_wanted ): array {

		if ( empty( $event_types_wanted ) || ! is_array( $event_types_wanted ) ) {
			return array();
		}

		global $wpdb;

		$table_name = $wpdb->prefix . self::$events_table_name;
		$sql        = $wpdb->prepare( 'SELECT * FROM %1s', $table_name ); // phpcs:ignore WordPress.DB.PreparedSQLPlaceholders.UnquotedComplexPlaceholder
		$sql       .= $wpdb->prepare( ' WHERE scan_run_id = %s', $scan_run_id );
		$sql       .= $wpdb->prepare( ' AND data != %s', '' );

		$like_clauses = array();

		$type_map = array(
			'deleted'             => 'removed',
			'permissions_changed' => 'permissions-changed',
		);

		foreach ( $event_types_wanted as $v ) {
			$v = $type_map[ $v ] ?? $v;

			$like_clauses[] = $wpdb->prepare( 'event_type LIKE %s', '%' . $wpdb->esc_like( $v ) . '%' );
		}

		$sql .= ' AND (' . implode( ' OR ', $like_clauses ) . ')';

		$bg_jobs = $wpdb->get_results( $sql, ARRAY_A ); // phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared, WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching, WordPress.DB.PreparedSQLPlaceholders.UnquotedComplexPlaceholder

		return $bg_jobs;
	}

	/**
	 * Purge old WFCM data.
	 *
	 * @return void
	 *
	 * @since 2.0.0
	 */
	public static function purge_wfcm_data() {
		// Delete wfcm options.
		$nonce = wp_create_nonce( MFM_PREFIX . 'delete_data' );
		self::delete_from_options( $nonce, 'wfcm-%', 'wfcm_%' );
		self::delete_from_options( $nonce, '_transient_wfcm%', '_transient_timeout_wfcm%' );

		// Delete wfcm_file_event posts + data.
		self::drop_table( 'wfcm_file_events' );
	}

	/**
	 * Delete from WP options.
	 *
	 * @param string $nonce - Nonce.
	 * @param string $like_a - First compare.
	 * @param string $like_b - Second compare.
	 *
	 * @return void
	 *
	 * @since 2.0.0
	 */
	public static function delete_from_options( $nonce, $like_a, $like_b = '' ) {
		if ( ! wp_verify_nonce( $nonce, MFM_PREFIX . 'delete_data' ) ) {
			return;
		}

		global $wpdb;
		if ( ! empty( $like_b ) ) {
			$wpdb->query( $wpdb->prepare( 'DELETE FROM %1s WHERE ( option_name LIKE %s OR option_name LIKE %s )', $wpdb->options, $like_a, $like_b ) ); // phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared, WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching, WordPress.DB.PreparedSQLPlaceholders.UnquotedComplexPlaceholder
		} else {
			$wpdb->query( $wpdb->prepare( 'DELETE FROM %1s WHERE option_name LIKE %s', $wpdb->options, $like_a ) ); // phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared, WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching, WordPress.DB.PreparedSQLPlaceholders.UnquotedComplexPlaceholder
		}
	}

	/**
	 * Delete from variable place.
	 *
	 * @param string $nonce - Nonce.
	 * @param string $table_name - Target table.
	 * @param string $lookup - lookup.
	 * @param string $target - Like target.
	 * @param bool   $target_is_string - Handle string targets.
	 *
	 * @return void
	 *
	 * @since 2.0.0
	 */
	public static function delete_from_where( $nonce, $table_name, $lookup, $target, $target_is_string = false ) {
		if ( ! wp_verify_nonce( $nonce, MFM_PREFIX . 'delete_data' ) || empty( $table_name ) ) {
			return;
		}

		// Check if given correct table.
		if ( ! self::check_table_name( $table_name ) ) {
			return;
		}

		global $wpdb;
		$statement = $wpdb->prepare( 'DELETE FROM %1s WHERE %2s = %3d', $wpdb->prefix . $table_name, $lookup, $target ); // phpcs:ignore WordPress.DB.PreparedSQLPlaceholders.UnquotedComplexPlaceholder

		if ( $target_is_string ) {
			$statement = $wpdb->prepare( "DELETE FROM %1s WHERE `%2s` = '%3s'", $wpdb->prefix . $table_name, $lookup, $target ); // phpcs:ignore WordPress.DB.PreparedSQLPlaceholders.UnquotedComplexPlaceholder
		}

		$wpdb->get_results( $statement ); // phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared, WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching
	}

	/**
	 * Fuzzy delete from variable place.
	 *
	 * @param string $nonce - Nonce.
	 * @param string $table_name - Target table.
	 * @param string $lookup - lookup.
	 * @param string $target - Like target.
	 *
	 * @return void
	 *
	 * @since 2.0.0
	 */
	public static function delete_from_where_like( $nonce, $table_name, $lookup, $target ) {
		if ( ! wp_verify_nonce( $nonce, MFM_PREFIX . 'delete_data' ) || empty( $table_name ) ) {
			return;
		}

		// Check if given correct table.
		if ( ! self::check_table_name( $table_name ) ) {
			return;
		}

		global $wpdb;
		$wpdb->get_results( $wpdb->prepare( 'DELETE FROM %1s WHERE %s LIKE %s', $wpdb->prefix . $table_name, $lookup, $target ) ); // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared, WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching, WordPress.DB.PreparedSQLPlaceholders.UnquotedComplexPlaceholder
	}

	/**
	 * Delete MFM-specific directories from the uploads folder.
	 *
	 * Paths are hard-coded to prevent accidental deletion of unrelated directories.
	 *
	 * @return void
	 *
	 * @since 2.3.0
	 */
	private static function delete_mfm_directories() {
		$mfm_directories = array(
			MFM_UPLOADS_DIR . MFM_LOGS_DIR,
			MFM_UPLOADS_DIR . 'melapress-file-monitor',
		);

		global $wp_filesystem;

		if ( ! function_exists( 'WP_Filesystem' ) ) {
			require_once ABSPATH . 'wp-admin/includes/file.php';
		}

		if ( empty( $wp_filesystem ) ) {
			$result = \WP_Filesystem();

			// WP_Filesystem() can fail without setting $wp_filesystem: guard against fatal errors on ->delete().
			if ( false === $result || empty( $wp_filesystem ) ) {
				return;
			}
		}

		foreach ( $mfm_directories as $dir ) {
			if ( is_dir( $dir ) ) {
				$wp_filesystem->delete( $dir, true );
			}
		}
	}

	/**
	 * Drop a plugin database table.
	 *
	 * @param string $table_name - Target table.
	 *
	 * @return void
	 *
	 * @since 2.0.0
	 */
	private static function drop_table( $table_name ) {
		// Check if given correct table.
		if ( empty( $table_name ) || ! self::check_table_name( $table_name ) ) {
			return;
		}

		global $wpdb;
		$wpdb->query( $wpdb->prepare( 'DROP TABLE IF EXISTS %1s', $wpdb->prefix . $table_name ) ); // phpcs:ignore WordPress.DB.DirectDatabaseQuery.SchemaChange, WordPress.DB.PreparedSQLPlaceholders.UnquotedComplexPlaceholder, WordPress.DB.DirectDatabaseQuery.NoCaching, WordPress.DB.DirectDatabaseQuery.DirectQuery
	}

	/**
	 * Clear table.
	 *
	 * @param string $nonce - Nonce.
	 * @param string $table_name - Table to clear.
	 *
	 * @return void
	 *
	 * @since 2.0.0
	 */
	public static function truncate_table( $nonce, $table_name ) {
		if ( ! wp_verify_nonce( $nonce, MFM_PREFIX . 'delete_data' ) || empty( $table_name ) ) {
			return;
		}

		// Check if given correct table.
		if ( ! self::check_table_name( $table_name ) ) {
			return;
		}

		global $wpdb;
		$wpdb->get_results( $wpdb->prepare( 'TRUNCATE TABLE %1s', $wpdb->prefix . $table_name ) ); // phpcs:ignore WordPress.DB.PreparedSQLPlaceholders.UnquotedComplexPlaceholder
	}

	/**
	 * Perform the purge actin.
	 *
	 * @param string $nonce - Nonce.
	 *
	 * @return void
	 *
	 * @since 2.0.0
	 */
	public static function do_data_purge( $nonce ) {
		if ( ! current_user_can( 'manage_options' ) || empty( $nonce ) || ! wp_verify_nonce( $nonce, MFM_PREFIX . 'purge_data_nonce' ) ) {
			return;
		}

		global $wpdb;

		self::drop_table( self::$stored_directories_table_name );
		self::drop_table( self::$stored_files_table_name );
		self::drop_table( self::$scanned_directories_table_name );
		self::drop_table( self::$scanned_files_table_name );
		self::drop_table( self::$events_table_name );
		self::drop_table( self::$events_meta_table_name );

		$prefix = MFM_PREFIX . '%';

		$plugin_options = $wpdb->get_results( $wpdb->prepare( 'SELECT option_name FROM %1s WHERE option_name LIKE %s', $wpdb->options, $prefix ) ); // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching, WordPress.DB.PreparedSQLPlaceholders.UnquotedComplexPlaceholder

		foreach ( $plugin_options as $option ) {
			delete_option( $option->option_name );
		}

		// Clean up any file system folders created by MFM.
		self::delete_mfm_directories();
	}

	/**
	 * Check operation is only performed where we want it.
	 *
	 * @param string $name_to_check - Table name to check.
	 *
	 * @return bool - Is allowed.
	 *
	 * @since 2.0.0
	 */
	private static function check_table_name( $name_to_check ) {
		if ( empty( $name_to_check ) ) {
			return false;
		}

		$allowed_names = array(
			self::$events_meta_table_name,
			self::$stored_directories_table_name,
			self::$scanned_directories_table_name,
			self::$stored_files_table_name,
			self::$scanned_files_table_name,
			self::$events_table_name,
			'wfcm_file_events',
		);
		return in_array( $name_to_check, $allowed_names, true );
	}

	/**
	 * Check if we have a table or not.
	 *
	 * @param string $table_name - Table name.
	 *
	 * @return bool
	 *
	 * @since 2.0.0
	 */
	private static function check_table_exists( $table_name ) {
		global $wpdb;
		if ( $wpdb->get_var( $wpdb->prepare( 'SHOW TABLES LIKE %s', $table_name ) ) === $table_name ) { // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching 
			return true;
		}

		return false;
	}

	/**
	 * Get held data for a specific path.
	 *
	 * @param string $table_to_check - Table to check.
	 * @param string $path - Path to check.
	 *
	 * @return array|string
	 *
	 * @since 2.1.0
	 */
	public static function get_held_file_paths( $table_to_check, $path ) {
		global $wpdb;
		$old_data = $wpdb->get_results( $wpdb->prepare( 'SELECT file_paths FROM %1s WHERE path = %s', $wpdb->prefix . $table_to_check, $path ), ARRAY_A ); // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching, WordPress.DB.PreparedSQLPlaceholders.UnquotedComplexPlaceholder
		$old_data = isset( $old_data[0]['file_paths'] ) ? maybe_serialize( $old_data[0]['file_paths'] ) : '';

		return $old_data;
	}

	/**
	 * Get held data for a specific path using a wooly search.
	 *
	 * @param string $table_to_check - Table to check.
	 * @param string $path - Path to check.
	 *
	 * @return array|string
	 *
	 * @since 2.1.0
	 */
	public static function get_held_file_paths_fuzzy( $table_to_check, $path ) {
		global $wpdb;
		$held_data = $wpdb->get_results( $wpdb->prepare( 'SELECT file_paths FROM %1s WHERE path LIKE %s', $wpdb->prefix . $table_to_check, '%' . $path . '%' ), ARRAY_A ); // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching, WordPress.DB.PreparedSQLPlaceholders.UnquotedComplexPlaceholder
		return $held_data;
	}

	/**
	 * Prepare file data to check if extra data handling is needed (dir has 500+ items).
	 *
	 * @param array $held_data - Input data.
	 *
	 * @return array
	 *
	 * @since 2.1.0
	 */
	public static function prepare_file_data( $held_data ) {
		$final_data_array = array();

		foreach ( $held_data as $data_item => $value ) {
			array_push( $final_data_array, maybe_unserialize( $value['file_paths'] ) );
		}
		$final = array_merge( ...$final_data_array );
		return $final;
	}

	/**
	 * Get paths which are not present in one table compared to the other.
	 *
	 * @param string $table_a - Initial table.
	 * @param string $table_b - Comparison table.
	 *
	 * @return array
	 *
	 * @since 2.1.0
	 * @since 2.3.0 Use $wpdb->prepare() for both table names.
	 */
	private static function get_paths_not_in_table( $table_a, $table_b ) {
		global $wpdb;
		$table_a = $wpdb->prefix . $table_a;
		$table_b = $wpdb->prefix . $table_b;
		$data    = $wpdb->get_results( $wpdb->prepare( 'SELECT * FROM %1s WHERE path NOT IN (SELECT path FROM %1s)', $table_a, $table_b ), ARRAY_A ); // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching, WordPress.DB.PreparedSQLPlaceholders.UnquotedComplexPlaceholder
		return $data;
	}

	/**
	 * Lookup an event by path.
	 *
	 * @param string $lookup - Target.
	 *
	 * @return array
	 *
	 * @since 2.1.0
	 * @since 2.3.0 Use $wpdb->prepare() and $wpdb->esc_like() to prevent SQL injection.
	 */
	public static function lookup_event( $lookup ) {
		global $wpdb;

		$table_name = $wpdb->prefix . self::$events_table_name;
		$like       = '%' . $wpdb->esc_like( $lookup ) . '%';

		$found = $wpdb->get_results( $wpdb->prepare( 'SELECT * FROM %1s WHERE path LIKE %s OR data LIKE %s', $table_name, $like, $like ), ARRAY_A ); // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching, WordPress.DB.PreparedSQLPlaceholders.UnquotedComplexPlaceholder

		return $found;
	}

	/**
	 * Get data from stored files based on path.
	 *
	 * @param string $path - Target path.
	 *
	 * @return array
	 *
	 * @since 2.1.0
	 */
	public static function get_stored_files_by_path( $path ) {
		global $wpdb;

		$stored_files = $wpdb->prefix . self::$stored_files_table_name;

		/**
		 * Safe to ignore UnquotedComplexPlaceholder: %1s is used intentionally for the table name,
		 * which must not be quoted and is derived from a trusted source ($wpdb->prefix + class constant).
		 */
		$found = $wpdb->get_results( $wpdb->prepare( 'SELECT * FROM %1s WHERE path = %s', $stored_files, $path ), ARRAY_A ); // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching, WordPress.DB.PreparedSQLPlaceholders.UnquotedComplexPlaceholder

		return $found;
	}

	/**
	 * Empty cache content into the DB.
	 *
	 * @param string $target_cache - Where is it.
	 *
	 * @return void
	 *
	 * @since 2.0.0
	 * @since 2.3.0 Use $wpdb->prepare() for table name references. Wrap cache operations in try-catch to handle corrupted cache files without fatal errors.
	 */
	public static function dump_into_db( $target_cache = 'directory_runner_cache' ) {
		try {
			$obj_files_cache = MFM_Fast_Cache::get_instance();
			$current_cache   = $obj_files_cache->getItem( $target_cache );
			$data            = $current_cache->get();

			if ( ! is_null( $data ) ) {
				$data = rtrim( $data, ',' );

				global $wpdb;

				$table_name = $wpdb->prefix . self::$scanned_directories_table_name;

				// Bulk insert all scanned directories from the cache into the DB in a single query.
				$wpdb->query( $wpdb->prepare( 'INSERT INTO %1s ( path, time, permissions ) VALUES ', $table_name ) . $data ); // phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared, WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching, WordPress.DB.PreparedSQLPlaceholders.UnquotedComplexPlaceholder

				// Remove duplicate paths, keeping only the row with the highest ID (most recent entry).
				$wpdb->query( $wpdb->prepare( 'DELETE t1 FROM %1s t1, %1s t2 WHERE t1.id < t2.id AND t1.path = t2.path', $table_name, $table_name ) ); // phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared, WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching, WordPress.DB.PreparedSQLPlaceholders.UnquotedComplexPlaceholder

				$obj_files_cache->clear();
			}
		} catch ( \TypeError $e ) {
			try {
				MFM_Fast_Cache::get_instance()->clear();
			} catch ( \Throwable $e ) { // phpcs:ignore Generic.CodeAnalysis.EmptyStatement.DetectedCatch
				// Cache directory may already be gone, nothing to do.
			}
			Logger::write_to_log( Logger::get_log_timestamp() . ' Cache corruption detected in dump_into_db, cache pool cleared' );
		} catch ( \Throwable $e ) {
			Logger::write_to_log( Logger::get_log_timestamp() . ' Cache error in dump_into_db: ' . $e->getMessage() );
		}
	}

	/**
	 * Create new column for file perms.
	 *
	 * @return void
	 *
	 * @since 2.2.0
	 */
	public static function create_permissions_column() {
		global $wpdb;

		$scanned_files_table_name       = $wpdb->prefix . self::$scanned_files_table_name;
		$stored_files_table_name        = $wpdb->prefix . self::$stored_files_table_name;
		$scanned_directories_table_name = $wpdb->prefix . self::$scanned_directories_table_name;
		$stored_directories_table_name  = $wpdb->prefix . self::$stored_directories_table_name;

		if ( ! function_exists( '\maybe_add_column' ) ) {
			require_once ABSPATH . 'wp-admin/includes/upgrade.php';
		}

		if ( self::check_table_exists( $scanned_files_table_name ) ) {
			$create_ddl = "ALTER TABLE $scanned_files_table_name ADD file_permissions TEXT NOT NULL";
			\maybe_add_column( $scanned_files_table_name, 'file_permissions', $create_ddl );
		}

		if ( self::check_table_exists( $stored_files_table_name ) ) {
			$create_ddl = "ALTER TABLE $stored_files_table_name ADD file_permissions TEXT NOT NULL";
			\maybe_add_column( $stored_files_table_name, 'file_permissions', $create_ddl );
		}

		if ( self::check_table_exists( $scanned_directories_table_name ) ) {
			$create_ddl = "ALTER TABLE $scanned_directories_table_name ADD permissions TEXT NOT NULL";
			\maybe_add_column( $scanned_directories_table_name, 'permissions', $create_ddl );
		}

		if ( self::check_table_exists( $stored_directories_table_name ) ) {
			$create_ddl = "ALTER TABLE $stored_directories_table_name ADD permissions TEXT NOT NULL";
			\maybe_add_column( $stored_directories_table_name, 'permissions', $create_ddl );
		}

		update_site_option( MFM_PREFIX . 'permissions_column_created', true );
	}
}
