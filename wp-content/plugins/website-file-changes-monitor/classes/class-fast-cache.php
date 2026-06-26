<?php
/**
 * Caching class file.
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

use MFM\DB_Handler;
use MFM\Helpers\Logger;
use Phpfastcache\CacheManager;
use Phpfastcache\Drivers\Files\Config as FastCacheFilesConfig;

/**
 * Class for caching during a run.
 *
 * @since 2.0.0
 */
class MFM_Fast_Cache {

	/**
	 * Default directory.
	 *
	 * @var string
	 *
	 * @since 2.0.0
	 */
	public static $caching_directory = '';

	/**
	 * Set the caching directory location
	 *
	 * @return void
	 *
	 * @since 2.0.0
	 * @since 2.3.0 - move tmp folders created by phpfastcache under: wp-content/uploads/melapress-file-monitor/mfm-tmp/
	 */
	public static function setup_cache_path() {
		self::$caching_directory = \wp_normalize_path( MFM_UPLOADS_DIR . 'melapress-file-monitor/mfm-tmp' );
	}

	/**
	 * Returns a configured phpfastcache Files driver instance.
	 *
	 * @return \Phpfastcache\Core\Pool\ExtendedCacheItemPoolInterface
	 *
	 * @since 2.3.0
	 */
	public static function get_instance() {
		$config = new FastCacheFilesConfig(
			array(
				'path'                   => self::$caching_directory,
				'secureFileManipulation' => true,
			)
		);

		return CacheManager::getInstance( 'files', $config );
	}

	/**
	 * Add data to cache.
	 *
	 * @param string $data_string - Incoming data.
	 * @param string $target_cache - Where is it going.
	 *
	 * @return void
	 *
	 * @since 2.0.0
	 * @since 2.3.0 - Wrap cache operations in try-catch to handle corrupted cache files without fatal errors.
	 */
	public static function add_to_cache( $data_string, $target_cache = 'directory_runner_cache' ) {
		try {
			$obj_files_cache = self::get_instance();

			try {
				$current_cache = $obj_files_cache->getItem( $target_cache );
			} catch ( \TypeError $e ) {
				$obj_files_cache->clear();

				$current_cache = $obj_files_cache->getItem( $target_cache );

				Logger::write_to_log( Logger::get_log_timestamp() . ' Cache corruption detected and pool cleared for: ' . $target_cache );
			}

			$data_string = $data_string . ',';
			$current_cache->append( $data_string );
			$obj_files_cache->save( $current_cache );

			if ( $current_cache->getLength() >= 50000 ) {
				DB_Handler::dump_into_db( 'directory_runner_cache' );
			}
		} catch ( \Throwable $e ) {
			Logger::write_to_log( Logger::get_log_timestamp() . ' Cache error in add_to_cache: ' . $e->getMessage() );
		}
	}
}
