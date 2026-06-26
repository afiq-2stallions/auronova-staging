<?php
/**
 * Helper class for file and directory tasks.
 *
 * @package MFM
 * @since 2.0.0
 */

declare(strict_types=1);

namespace MFM\Crons;

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

use MFM\Helpers\Settings_Helper;

/**
 * Utility file and directory functions.
 *
 * @since 2.0.0
 */
class Cron_Handler {

	/**
	 * Instance.
	 *
	 * @var array
	 *
	 * @since 2.0.0
	 */

	protected static $instance = null;

	/**
	 * View settings.
	 *
	 * @var array
	 *
	 * @since 2.0.0
	 */
	public static $scan_settings = array();

	/**
	 * Frequency daily hour.
	 *
	 * For testing change hour here [01 to 23]
	 *
	 * @var array
	 *
	 * @since 2.0.0
	 */
	private static $daily_hour = array( '04' );

	/**
	 * Frequency weekly date.
	 *
	 * For testing change date here [1 (for Monday) through 7 (for Sunday)]
	 *
	 * @var string
	 *
	 * @since 2.0.0
	 */
	private static $weekly_day = '1';

	/**
	 * Schedule hook name.
	 *
	 * @var string
	 *
	 * @since 2.0.0
	 */
	public static $schedule_hook = MFM_PREFIX . 'monitor_file_changes';

	/**
	 * Used to hold the max length we are willing to run a scan part for in
	 * seconds.
	 *
	 * This will be set to 4 minutes is there is no time saved in database.
	 *
	 * @var int
	 *
	 * @since 2.0.0
	 */
	private static $scan_max_execution_time;

	/**
	 * Class constants.
	 *
	 * @since 2.0.0
	 */
	const SCAN_HOURLY       = 'hourly';
	const SCAN_DAILY        = 'daily';
	const SCAN_WEEKLY       = 'weekly';
	const SCAN_FILE_LIMIT   = 200000;
	const HASHING_ALGORITHM = 'sha256';


	/**
	 * Constructor.
	 *
	 * @return void
	 *
	 * @since 2.0.0
	 */
	public static function load_crons_handler() {
		self::register_hooks();
		self::load_settings();
		self::schedule_file_changes_monitor();

		// try get a max scan length from database otherwise default to 4 mins.
		// NOTE: this code could be adjusted to allow user configuration.
		self::$scan_max_execution_time = (int) get_site_option( MFM_PREFIX . 'max_scan_time', 4 * MINUTE_IN_SECONDS );
	}

	/**
	 * Register Hooks.
	 *
	 * @return void
	 *
	 * @since 2.0.0
	 */
	public static function register_hooks() {
		add_filter( 'cron_schedules', array( __CLASS__, 'add_recurring_schedules' ) ); // phpcs:ignore WordPress.WP.CronInterval.CronSchedulesInterval
	}

	/**
	 * Load File Change Monitor Settings.
	 *
	 * @return void
	 *
	 * @since 2.0.0
	 */
	public static function load_settings() {
		self::$scan_settings = \MFM\Helpers\Settings_Helper::get_mfm_settings();

		// Set the scan hours.
		if ( ! empty( self::$scan_settings['scan-hour'] ) ) {
			$saved_hour = (int) self::$scan_settings['scan-hour'];
			$next_hour  = $saved_hour + 1;
			$hours      = array( $saved_hour, $next_hour );
			foreach ( $hours as $hour ) {
				$daily_hour[] = str_pad( (string) $hour, 2, '0', STR_PAD_LEFT );
			}
			self::$daily_hour = $daily_hour;
		}

		// Set weekly day.
		if ( ! empty( self::$scan_settings['scan-day'] ) ) {
			self::$weekly_day = self::$scan_settings['scan-day'];
		}
	}

	/**
	 * Schedule file changes monitor cron.
	 *
	 * @return void
	 *
	 * @since 2.0.0
	 */
	public static function schedule_file_changes_monitor() {
		// Schedule file changes if the feature is enabled.
		if ( is_multisite() && ! is_main_site() ) {
			// Clear the scheduled hook if feature is disabled.
			wp_clear_scheduled_hook( self::$schedule_hook );
		} elseif ( 'yes' === Settings_Helper::get_setting( 'logging-enabled' ) ) {
			// Hook scheduled method.
			add_action( self::$schedule_hook, array( __CLASS__, 'scan_file_changes' ) );
			// Schedule event if there isn't any already.
			if ( ! wp_next_scheduled( self::$schedule_hook ) ) {
				$frequency_option = Settings_Helper::get_setting( 'scan-frequency', 'daily' );
				// figure out the NEXT schedule time to recur from.
				$time = self::get_next_cron_schedule_time( $frequency_option );
				wp_schedule_event(
					$time,               // Timestamp.
					$frequency_option,   // Frequency.
					self::$schedule_hook // Scheduled event.
				);
			}
		} else {
			// Clear the scheduled hook if feature is disabled.
			wp_clear_scheduled_hook( self::$schedule_hook );
		}
	}

	/**
	 * Given a frequency formulates the next time that occurs and returns a
	 * timestamp for that time to use when scheduling initial cron jobs.
	 *
	 * @param  string $frequency_option an option of hourly/daily/weekly.
	 *
	 * @return int
	 *
	 * @since 2.0.0
	 */
	private static function get_next_cron_schedule_time( $frequency_option ) {
		$time = current_time( 'timestamp' ); // phpcs:ignore WordPress.DateTime.CurrentTimeTimestamp.Requested

		// Allow for local timezones.
		$local_timezone = wp_timezone_string();

		switch ( $frequency_option ) {
			case self::SCAN_HOURLY:
				// hourly scans start at the beginning of the next hour.
				$date = new \DateTime();

				// Adjust for timezone.
				$date->setTimezone( wp_timezone() );

				$minutes = $date->format( 'i' );

				$date->modify( '+1 hour' );
				// if we had any minutes then remove them.
				if ( $minutes > 0 ) {
					$date->modify( '-' . $minutes . ' minutes' );
				}

				$time = $date->getTimestamp();
				break;
			case self::SCAN_DAILY:
				// daily starts on a given hour of the first day it occurs.
				$hour      = (int) Settings_Helper::get_setting( 'scan-hour' );
				$next_time = strtotime( 'today ' . $hour . ':00 ' . $local_timezone );

				// if already passed today then add 1 day to timestamp.
				if ( $next_time < $time ) {
					$next_time = strtotime( '+1 day', $next_time );
				}

				$time = $next_time;
				break;
			case self::SCAN_WEEKLY:
				// weekly runs on a given day each week at a given hour.
				$hour    = (int) Settings_Helper::get_setting( 'scan-hour' );
				$day_num = (int) Settings_Helper::get_setting( 'scan-day' );
				$day     = Settings_Helper::convert_to_day_string( $day_num );

				$next_time = strtotime( $day . ' ' . $hour . ':00 ' . ' ' . $local_timezone ); // phpcs:ignore Generic.Strings.UnnecessaryStringConcat.Found
				// if that day has passed this week already then add 1 week.
				if ( $next_time < $time ) {
					$next_time = strtotime( '+1 week', $next_time );
				}

				$time = $next_time;
				break;
			default:
				// no other scan frequencies supported.
		}
		return ( false === $time ) ? time() : $time;
	}



	/**
	 * Add time intervals for scheduling.
	 *
	 * @param  array $schedules - Array of schedules.
	 *
	 * @return array
	 *
	 * @since 2.0.0
	 */
	public static function add_recurring_schedules( $schedules ) {
		$schedule_options = array(
			MFM_PREFIX . 'directory_runner_cron_interval' => array(
				'display'  => '60 Seconds',
				'interval' => '60',
			),
		);
		foreach ( $schedule_options as $schedule_key => $schedule ) {
			$schedules[ MFM_PREFIX . 'directory_runner_cron_interval' ] = array(
				'interval' => $schedule['interval'],
				'display'  => 'Every ' . $schedule['display'],
			);
		}

		$schedules['tenminutes'] = array(
			'interval' => 600,
			'display'  => 'Every 10 minutes',
		);
		$schedules['weekly']     = array(
			'interval' => 7 * DAY_IN_SECONDS,
			'display'  => 'Once a week',
		);

		return $schedules;
	}

	/**
	 * Scan File Changes.
	 *
	 * @requires void
	 *
	 * @since 2.0.0
	 */
	public static function scan_file_changes() {
		\MFM::start_directory_runner( wp_create_nonce( MFM_PREFIX . 'start_scan_nonce' ) );
	}
}
