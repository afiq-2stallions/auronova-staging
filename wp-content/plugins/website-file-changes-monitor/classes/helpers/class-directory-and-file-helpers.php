<?php
/**
 * Helper class for file and directory tasks.
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

use MFM\DB_Handler;
use MFM\Helpers\Logger;
use MFM\Helpers\Settings_Helper;

/**
 * Utility file and directory functions.
 *
 * @since 2.0.0
 */
class Directory_And_File_Helpers {

	/**
	 * Gather directory info.
	 *
	 * @param  string  $path - Lookup path.
	 * @param  boolean $recursive - Is recursive.
	 * @param  array   $filtered - Skip items.
	 *
	 * @return array - array of absolute, normalized directory paths (with forward slashes)
	 *
	 * @throws \RuntimeException - Error.
	 *
	 * @since 2.0.0
	 * @since 2.3.0 - Normalized paths with wp_normalize_path() for consistent forward slashes on all OS.
	 */
	public static function get_directories_from_path( $path, $recursive = false, array $filtered = array() ) {

		if ( ! is_dir( $path ) ) {
			$msg  = Logger::get_log_timestamp() . ' PATH DOES NOT EXIST' . " \n";
			$msg .= Logger::get_log_timestamp() . ' ' . $path . " \n";
			Logger::write_to_log( $msg );
			return array();
		}

		$filtered += array( '.', '..' );

		$dirs = array();
		$d    = dir( $path );
		if ( false !== $d ) {
			while ( ( $entry = $d->read() ) !== false ) { // phpcs:ignore Generic.CodeAnalysis.AssignmentInCondition.FoundInWhileCondition
				if ( ! in_array( $entry, $filtered, true ) ) {
					if ( is_dir( $path . DIRECTORY_SEPARATOR . $entry ) ) {

						$depth = explode( DIRECTORY_SEPARATOR, $path . DIRECTORY_SEPARATOR . $entry );
						$depth = count( $depth ) - 1;
						if ( MFM_MAX_DEPTH < $depth ) {
							$msg  = Logger::get_log_timestamp() . ' PATH BEYOND DEPTH LIMIT' . " \n";
							$msg .= Logger::get_log_timestamp() . ' ' . $path . " \n";
							Logger::write_to_log( $msg );
							continue;
						}

						$path_name = \wp_normalize_path( realpath( $path . DIRECTORY_SEPARATOR . $entry ) );
						$dirs[]    = $path_name;

						if ( $recursive ) {
							$new_dirs = self::get_directories_from_path( $path . DIRECTORY_SEPARATOR . $entry );
							foreach ( $new_dirs as $new_dir ) {
								$dirs[] = $entry . DIRECTORY_SEPARATOR . $new_dir;
							}
						}
					}
				}
			}
		}

		return $dirs;
	}

	/**
	 * Gather relevant file info for a given path.
	 *
	 * @param  string $root_dir - Gather files and store info.
	 * @param  array  $file_data - Incoming data.
	 *
	 * @return array $file_data - Associative array with keys 'paths', 'hashs', 'timestamps', 'permissions', each containing indexed arrays of file data.
	 *
	 * @since 2.0.0
	 * @since 2.3.0 - Added extension exclusion to avoid storing files of excluded extensions and files with no extension, depending on settings. Normalized paths with wp_normalize_path() for consistent forward slashes on all OS.
	 */
	public static function scan_and_store_files( $root_dir, $file_data = array() ): array {
		$invisible_file_names = array( '.', '..', '.htaccess', '.htpasswd' );
		// Run through content of root directory.
		$dir_content = scandir( $root_dir );

		if ( ! is_array( $dir_content ) ) {
			return $file_data;
		}

		$ignore_files = Settings_Helper::get_setting_cached( 'excluded_files' );
		$ignore_files = is_array( $ignore_files ) ? $ignore_files : array();

		$excluded_file_extensions = Settings_Helper::get_setting_cached( 'excluded_file_extensions' );
		$excluded_file_extensions = is_array( $excluded_file_extensions ) ? $excluded_file_extensions : array();

		$scan_no_extension = 'yes' === Settings_Helper::get_setting_cached( 'scan-files-with-no-extension', 'yes' );

		// Convert max file size from MB to bytes for comparison against filesize(), which returns bytes.
		$max_file_size_mb      = (int) Settings_Helper::get_setting_cached( 'max-file-size', 5 );
		$file_size_limit_bytes = $max_file_size_mb * 1048576;

		foreach ( $dir_content as $content ) {
			if ( in_array( $content, $invisible_file_names, true ) ) {
				continue;
			}

			// Check extension exclusions first (cheap string ops) before filesystem calls.
			$file_extension = strrchr( $content, '.' );
			$file_extension = ( false !== $file_extension ) ? substr( $file_extension, 1 ) : false;

			// Check setting, are we scanning files with no extension? If not then skip.
			if ( false === $file_extension && ! $scan_no_extension ) {
				continue;
			}

			// Check if this file has an excluded extension, if yes, skip.
			if ( false !== $file_extension && in_array( $file_extension, $excluded_file_extensions, true ) ) {
				continue;
			}

			$path = \wp_normalize_path( $root_dir . DIRECTORY_SEPARATOR . $content );

			if ( is_file( $path ) && is_readable( $path ) ) {
				// Check if this file is in the list of specific files to ignore, if yes, skip.
				if ( in_array( str_replace( ABSPATH, '', $path ), $ignore_files, true ) ) {
					continue;
				}

				/**
				 * Skip files that exceed the configured size limit.
				 * Enforcing here means large files never enter the DB, so they
				 * cannot trigger added/removed/modified events regardless of how they change.
				 */
				if ( filesize( $path ) > $file_size_limit_bytes ) {
					$msg  = Logger::get_log_timestamp() . ' File limit exceeded:' . " \n";
					$msg .= Logger::get_log_timestamp() . ' ' . $path . " \n";

					Logger::write_to_log( $msg );

					\do_action( MFM_PREFIX . 'file_exceeded_size_event_created', str_replace( ABSPATH, '', $path ) );
					continue;
				}

				// save file name with path.
				$file_data['paths'][]       = str_replace( ABSPATH, '', $path );
				$file_data['hashs'][]       = md5_file( $path );
				$file_data['timestamps'][]  = filemtime( $path );
				$file_data['permissions'][] = substr( sprintf( '%o', fileperms( $path ) ), -4 );
			}
		}

		return $file_data;
	}

	/**
	 * Gather current WP file hashes for comparison.
	 *
	 * @return array
	 *
	 * @since 2.0.0
	 */
	public static function get_core_files_hashes() {
		$version = $GLOBALS['wp_version'];
		$locale  = get_locale();

		// try to load checksum from transient cache.
		$cache_key        = MFM_PREFIX . 'wp_org_checksums_' . $version . '_' . $locale;
		$cached_checksums = get_transient( $cache_key );
		if ( false === $cached_checksums ) {
			$endpoint_url = add_query_arg(
				array(
					'version' => $version,
					'locale'  => $locale,
				),
				'https://api.wordpress.org/core/checksums/1.0/'
			);
			$response     = wp_remote_get( $endpoint_url );
			if ( is_wp_error( $response ) ) {
				return array();
			}

			// plugins/info/1.0/{slug} https://api.wordpress.org/plugins/checksums/1.0/wp-2fa/.

			$body = json_decode( $response['body'], true );
			if ( empty( $body['checksums'] ) || ! is_array( $body['checksums'] ) ) {
				return array();
			}

			$checksums = $body['checksums'];
			set_transient( $cache_key, wp_json_encode( $body['checksums'] ), WEEK_IN_SECONDS );
		} else {
			// cached value need to be decoded first.
			$checksums = json_decode( $cached_checksums, true );
			if ( ! is_array( $checksums ) ) {
				// empty array is returned if the data is malformed in any way and cannot be decoded as JSON.
				return array();
			}
		}

		return $checksums;
	}

	/**
	 * Create list of paths for all core files.
	 *
	 * @param boolean $return_just_paths - Return paths only.
	 * @param boolean $include_abspath - Include Abspath in result.
	 *
	 * @return array - Result.
	 *
	 * @since 2.0.0
	 */
	public static function create_core_file_keys( $return_just_paths = false, $include_abspath = true ) {
		$final = array();

		foreach ( self::get_core_files_hashes() as $core_file => $val ) {
			if ( $include_abspath ) {
				$final[] = ( $return_just_paths ) ? ABSPATH . $core_file : ABSPATH . $core_file . '|' . $val;
			} else {
				$final[] = ( $return_just_paths ) ? $core_file : $core_file . '|' . $val;
			}
		}

		return $final;
	}

	/**
	 * Gather current plugin info.
	 *
	 * @return array $plugins - Result.
	 *
	 * @since 2.0.0
	 */
	public static function get_installed_plugin_info() {
		include_once ABSPATH . 'wp-admin/includes/plugin.php';
		$plugins = get_plugins();

		return $plugins;
	}

	/**
	 * Create array of paths for current plugin info.
	 *
	 * @param boolean $add_wp_dir - Add WP dir to result.
	 *
	 * @return array - Indexed array of absolute plugin directory paths (e.g., '/var/www/html/wp-content/plugins/akismet').
	 *
	 * @since 2.0.0
	 * @since 2.3.0 - Normalized paths with wp_normalize_path() for consistent forward slashes on all OS.
	 */
	public static function create_plugin_keys( $add_wp_dir = true ) {
		$plugin_keys_cache = wp_cache_get( MFM_PREFIX . 'plugin_keys' );

		if ( false === $plugin_keys_cache ) {
			$plugin_keys_cache = array();
			$info              = self::get_installed_plugin_info();

			foreach ( array_keys( $info ) as $plugin ) {
				$plugin_keys_cache[] = ( $add_wp_dir ) ? \wp_normalize_path( dirname( WP_PLUGIN_DIR . DIRECTORY_SEPARATOR . $plugin ) ) : \wp_normalize_path( dirname( $plugin ) );
			}

			wp_cache_set( MFM_PREFIX . 'plugin_keys', $plugin_keys_cache, '', 60 );
		}
		return $plugin_keys_cache;
	}

	/**
	 * Turn UNIX stamp into a nice string.
	 *
	 * @param int $date - Current time.
	 *
	 * @return int time.
	 *
	 * @since 2.0.0
	 */
	public static function timeago( $date ) {
		$datetime_format = Settings_Helper::get_datetime_format( false );
		$date            = gmdate( $datetime_format, (int) $date );

		return $date;
	}

	/**
	 * Determine if path is for a plugin or theme etc.
	 *
	 * @param  string $path - Incoming dir.
	 * @param  bool   $return_ucwords - Capitalize.
	 *
	 * @return string - Result.
	 *
	 * @since 2.0.0
	 */
	public static function determine_directory_context( $path, $return_ucwords = false ) {
		$context   = 'other';
		$theme_dir = dirname( get_template_directory() );

		// Is this something within the plugins directory?
		if ( strpos( $path, WP_PLUGIN_DIR ) !== false ) {
			$context = 'plugin';
		} elseif ( strpos( (string) $path, (string) $theme_dir ) !== false ) {
			$context = 'theme';
		} elseif ( ABSPATH === (string) $path || ABSPATH . 'wp-includes/' === trailingslashit( (string) $path ) || ABSPATH . 'wp-admin/' === trailingslashit( (string) $path ) ) {
			$context = 'core';
		} elseif ( ABSPATH === trailingslashit( (string) dirname( $path ) ) || ABSPATH . 'wp-includes/' === trailingslashit( (string) dirname( $path ) ) || ABSPATH . 'wp-admin/' === trailingslashit( (string) dirname( $path ) ) ) {
			$context = 'core';
		}

		return ( $return_ucwords ) ? ucwords( $context ) : $context;
	}

	/**
	 * Check hash against currently stored hash.
	 *
	 * @param string $file_path - Lookup path of the file to check.
	 * @param string $incoming_hash - Hash to check against stored value.
	 *
	 * @return bool - Result.
	 *
	 * @since 2.0.0
	 */
	public static function check_stored_file_hash( $file_path, $incoming_hash ) {
		$absolute_containing_dir_path = \wp_normalize_path( dirname( $file_path ) );
		$absolute_containing_dir_path = \untrailingslashit( $absolute_containing_dir_path );

		$found = DB_Handler::get_stored_files_by_path( $absolute_containing_dir_path );

		// Directory path may be stored with or without trailing slash, retry if needed.
		if ( empty( $found ) ) {
			$found = DB_Handler::get_stored_files_by_path( \trailingslashit( $absolute_containing_dir_path ) );
		}

		// Relative path from wp root to file, e.g. wp-admin/admin.php.
		$relative_file_path_with_name = str_replace( ABSPATH, '', $file_path );

		if ( isset( $found[0] ) ) {
			$stored_hash             = false;
			$found_file_paths_array  = maybe_unserialize( $found[0]['file_paths'] );
			$found_file_hashes_array = maybe_unserialize( $found[0]['file_hashes'] );
			$index                   = 0;
			foreach ( $found_file_paths_array as $stored_relative_path_with_name ) {
				// $stored_relative_path_with_name format is a relative path, e.g. wp-admin/about.php.
				if ( $stored_relative_path_with_name === $relative_file_path_with_name ) {
					$stored_hash = $found_file_hashes_array[ $index ];
				}
				++$index;
			}

			if ( $stored_hash === $incoming_hash ) {
				return true;
			}
		}

		return false;
	}

	/**
	 * Check if a given file should be ignored before further processing.
	 *
	 * @param string $path - Path to check.
	 * @param bool   $is_check_on_removal - Path to check.
	 *
	 * @return boolean
	 *
	 * @since 2.1.0
	 */
	public static function should_file_check_continue( $path, $is_check_on_removal = false ) {
		$can_continue             = true;
		$ignore_files             = Settings_Helper::get_setting_cached( 'excluded_files' );
		$ignore_files             = ( is_array( $ignore_files ) ) ? $ignore_files : array();
		$excluded_file_extensions = Settings_Helper::get_setting_cached( 'excluded_file_extensions' );
		$excluded_file_extensions = ( is_array( $excluded_file_extensions ) ) ? $excluded_file_extensions : array();
		$max_size                 = Settings_Helper::get_setting_cached( 'max-file-size', 5 );
		$file_size_limit          = $max_size * 1048576;

		$path_is_in_ignored_files = in_array( $path, $ignore_files, true );

		if ( $path_is_in_ignored_files ) {
			$msg  = Logger::get_log_timestamp() . ' Attempted to check file, but skipped as ignored:' . " \n";
			$msg .= Logger::get_log_timestamp() . ' ' . $path . " \n";
			Logger::write_to_log( $msg );
			$can_continue = false;
		}

		$file_extension = ( $path && ! empty( (string) $path ) ) ? substr( (string) strrchr( (string) $path, '.' ), 1 ) : false;

		if ( $file_extension && in_array( $file_extension, $excluded_file_extensions, true ) ) {
			$msg  = Logger::get_log_timestamp() . ' Attempted to check file, but skipped as ignored extension:' . " \n";
			$msg .= Logger::get_log_timestamp() . ' ' . $file_extension . " \n";
			$msg .= Logger::get_log_timestamp() . ' ' . $path . " \n";
			Logger::write_to_log( $msg );
			$can_continue = false;
		}

		if ( ! $file_extension ) {
			if ( 'yes' !== Settings_Helper::get_setting_cached( 'scan-files-with-no-extension', 'yes' ) ) {
				$msg  = Logger::get_log_timestamp() . ' Attempted to check file, but skipped as has no extension:' . " \n";
				$msg .= Logger::get_log_timestamp() . ' ' . $path . " \n";
				Logger::write_to_log( $msg );
				$can_continue = false;
			}
		}

		if ( ! $is_check_on_removal ) {
			if ( ! is_file( ABSPATH . $path ) ) {
				$msg  = Logger::get_log_timestamp() . ' Attempted to check file as added, but failed is_file check:' . " \n";
				$msg .= Logger::get_log_timestamp() . ' ' . $path . " \n";
				Logger::write_to_log( $msg );
				$can_continue = false;
			}

			if ( is_file( ABSPATH . $path ) && filesize( ABSPATH . $path ) > $file_size_limit ) {
				$msg  = Logger::get_log_timestamp() . ' File limit exceeded:' . " \n";
				$msg .= Logger::get_log_timestamp() . ' ' . $path . " \n";

				Logger::write_to_log( $msg );

				do_action( MFM_PREFIX . 'file_exceeded_size_event_created', $path );

				$can_continue = false;
			}
		}

		if ( $path_is_in_ignored_files ) {
			$msg  = Logger::get_log_timestamp() . ' Attempted to check file as added, but ignored:' . " \n";
			$msg .= Logger::get_log_timestamp() . ' ' . $path . " \n";
			Logger::write_to_log( $msg );
			$can_continue = false;
		}

		return $can_continue;
	}

	/**
	 * Check whether a path matches a single pattern, supporting *, ** and ? wildcards.
	 *
	 * ** matches any number of characters including directory separators (any depth).
	 * *  matches any number of characters within a single path segment (no slash crossing).
	 * ?  matches exactly one character within a single path segment.
	 *
	 * For plain patterns (no wildcards), a regex with path boundary anchors is used
	 * to prevent partial segment matches (e.g. "uploads" will not match "uploads-backup").
	 *
	 * @param string $path    - Absolute path being tested.
	 * @param string $pattern - Stored pattern, may contain **, * or ?.
	 *
	 * @return bool
	 *
	 * @since 2.3.0
	 */
	private static function matches_path_pattern( string $path, string $pattern ): bool {
		// Remove trailing slash from pattern for consistency to avoid mismatches.
		$pattern = rtrim( $pattern, '/' );

		$is_wildcard = false !== strpbrk( $pattern, '*?' );

		if ( $is_wildcard ) {
			// ** must be substituted before * to avoid double-replacement.
			$escaped = str_replace(
				array( '\*\*', '\*', '\?' ),
				array( '.*', '[^/]*', '[^/]' ),
				preg_quote( $pattern, '#' )
			);

			$regex = '#' . $escaped . '(/|$)#';

			$result = (bool) preg_match( $regex, $path );

			return $result;
		}

		// Ensure the pattern is bounded by path separators to avoid partial matches (e.g. "uploads" matching "uploads-backup").
		$match = (bool) preg_match(
			'#(?:^|/)' . preg_quote( $pattern, '#' ) . '(?:/|$)#',
			$path
		);

		return $match;
	}

	/**
	 * Check if path should be totally disregarded form all activity.
	 *
	 * @param string $path - Path to check.
	 *
	 * @return boolean - Is to be ignored.
	 *
	 * @since 2.1.0
	 */
	public static function is_path_ignored( $path ) {
		$ignored_dirs = Settings_Helper::get_setting_cached( 'ignored_directories' );
		$ignored_dirs = apply_filters( MFM_PREFIX . 'append_ignored_dirs', $ignored_dirs );

		if ( empty( $ignored_dirs ) ) {
			return false;
		}

		foreach ( $ignored_dirs as $ignored ) {
			if ( self::matches_path_pattern( $path, $ignored ) ) {
				$msg  = Logger::get_log_timestamp() . ' PATH IS IGNORED, SKIPPING' . " \n";
				$msg .= Logger::get_log_timestamp() . ' ' . $path . " \n";

				Logger::write_to_log( $msg );

				return true;
			}
		}

		return false;
	}

	/**
	 * Check if path should be reported as might be excluded.
	 *
	 * @param string $path - Path to check.
	 *
	 * @return boolean - Is to be ignored.
	 *
	 * @since 2.1.0
	 */
	public static function is_path_excluded( $path ) {
		$ignore_dirs = Settings_Helper::get_setting_cached( 'excluded_directories' );

		if ( ! empty( $ignore_dirs ) ) {
			foreach ( $ignore_dirs as $ignored ) {
				if ( self::matches_path_pattern( $path, $ignored ) ) {
					$msg  = Logger::get_log_timestamp() . ' PATH IS CHILD OF EXCLUDED, SKIPPING' . " \n";
					$msg .= Logger::get_log_timestamp() . ' ' . $path . " \n";

					Logger::write_to_log( $msg );

					return true;
				}
			}
		}

		return false;
	}
}
