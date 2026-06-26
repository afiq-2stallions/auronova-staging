<?php
/**
 * Plugin Name: Melapress File Monitor
 *
 * @copyright Copyright (C) 2026, Melapress - support@melapress.com
 * @license   http://www.gnu.org/licenses/gpl-3.0.html GNU General Public License, version 3 or higher
 *
 * Plugin URI: http://melapress.com/
 * Description: A hassle-free way to get alerted of file changes on your WordPress site & boost security.
 * Author: Melapress
 * Contributors: Melapress
 * Version: 2.3.0
 * Text Domain: website-file-changes-monitor
 * Author URI: http://melapress.com/
 * License: GPL2
 *
 * @package MelapressLoginSecurity
 * @since 2.0.0
 *
 * Requires PHP: 8.0
 * Network: true
 */

/*
	Website Files Monitor
	Copyright(c) 2024 Melapress  (email : info@melapress.com)
	This program is free software; you can redistribute it and/or modify
	it under the terms of the GNU General Public License, version 3, as
	published by the Free Software Foundation.
	This program is distributed in the hope that it will be useful,
	but WITHOUT ANY WARRANTY; without even the implied warranty of
	MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
	GNU General Public License for more details.
	You should have received a copy of the GNU General Public License
	along with this program; if not, write to the Free Software
	Foundation, Inc., 51 Franklin St, Fifth Floor, Boston, MA  02110-1301  USA
*/

declare(strict_types=1);

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}


define( 'MFM_WP_PATH', \plugin_dir_path( __FILE__ ) );
define( 'MFM_WP_URL', \plugin_dir_url( __FILE__ ) );
define( 'MFM_WP_FILE', __FILE__ );
define( 'MFM_BASE_NAME', \plugin_basename( MFM_WP_FILE ) );
define( 'MFM_UPLOADS_DIR', \trailingslashit( \wp_upload_dir()['basedir'] ) );
define( 'MFM_LOGS_DIR', 'mfm-logs' );
define( 'MFM_BASE_URL', \trailingslashit( \plugin_dir_url( MFM_WP_FILE ) ) );
define( 'MFM_PREFIX', 'mfm_' );
define( 'MFM_MAX_DEPTH', 200 );
define( 'MFM_MIN_PHP_VERSION', '8.0' );
define( 'MFM_WP_VERSION', '2.3.0' );
define( 'MFM_NAME', 'File Changes Monitor' );

if ( version_compare( PHP_VERSION, MFM_MIN_PHP_VERSION, '<=' ) ) {
	add_action(
		'admin_init',
		static function () {
			deactivate_plugins( plugin_basename( __FILE__ ) );
		}
	);
	add_action(
		'admin_notices',
		static function () {
			echo wp_kses_post(
				sprintf(
					'<div class="notice notice-error"><p>%s</p></div>',
					sprintf(
						// translators: the minimum version of the PHP required by the plugin.
						__(
							'"%1$s" requires PHP %2$s or newer. Plugin is automatically deactivated.',
							'website-file-changes-monitor'
						),
						MFM_NAME,
						MFM_MIN_PHP_VERSION
					)
				)
			);
		}
	);

	// Return early to prevent loading the plugin.
	return;
}

if ( file_exists( plugin_dir_path( __FILE__ ) . 'vendor/autoload.php' ) ) {
	include_once plugin_dir_path( __FILE__ ) . 'vendor/autoload.php';
}

MFM::on_load();

register_activation_hook( MFM_WP_FILE, array( '\MFM\DB_Handler', 'install' ) );
register_activation_hook( MFM_WP_FILE, array( '\MFM\Admin\Admin_Manager', 'setup_admin_redirect' ) );
register_deactivation_hook( MFM_WP_FILE, array( '\MFM\DB_Handler', 'on_mfm_deactivation' ) );
