<?php
/**
 * Settings View.
 *
 * @package MFM
 * @since 2.0.0
 */

declare(strict_types=1);

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

use MFM\Admin\Template_Parts\Wildcard_Hint;

$mfm_settings      = \MFM\Helpers\Settings_Helper::get_mfm_settings();
$email_notice_type = $mfm_settings['email_notice_type'];

/**
 * Scan Frequencies.
 */
$frequency_options = array(
	'hourly' => __( 'Hourly', 'website-file-changes-monitor' ),
	'daily'  => __( 'Daily', 'website-file-changes-monitor' ),
	'weekly' => __( 'Weekly', 'website-file-changes-monitor' ),
);

// Scan hours option.
$scan_hours = array(
	'00' => _x( '00:00', 'a time string representing midnight', 'website-file-changes-monitor' ),
	'01' => _x( '01:00', 'a time string of hour followed by minutes', 'website-file-changes-monitor' ),
	'02' => _x( '02:00', 'a time string of hour followed by minutes', 'website-file-changes-monitor' ),
	'03' => _x( '03:00', 'a time string of hour followed by minutes', 'website-file-changes-monitor' ),
	'04' => _x( '04:00', 'a time string of hour followed by minutes', 'website-file-changes-monitor' ),
	'05' => _x( '05:00', 'a time string of hour followed by minutes', 'website-file-changes-monitor' ),
	'06' => _x( '06:00', 'a time string of hour followed by minutes', 'website-file-changes-monitor' ),
	'07' => _x( '07:00', 'a time string of hour followed by minutes', 'website-file-changes-monitor' ),
	'08' => _x( '08:00', 'a time string of hour followed by minutes', 'website-file-changes-monitor' ),
	'09' => _x( '09:00', 'a time string of hour followed by minutes', 'website-file-changes-monitor' ),
	'10' => _x( '10:00', 'a time string of hour followed by minutes', 'website-file-changes-monitor' ),
	'11' => _x( '11:00', 'a time string of hour followed by minutes', 'website-file-changes-monitor' ),
	'12' => _x( '12:00', 'a time string representing midday', 'website-file-changes-monitor' ),
	'13' => _x( '13:00', 'a time string of hour followed by minutes', 'website-file-changes-monitor' ),
	'14' => _x( '14:00', 'a time string of hour followed by minutes', 'website-file-changes-monitor' ),
	'15' => _x( '15:00', 'a time string of hour followed by minutes', 'website-file-changes-monitor' ),
	'16' => _x( '16:00', 'a time string of hour followed by minutes', 'website-file-changes-monitor' ),
	'17' => _x( '17:00', 'a time string of hour followed by minutes', 'website-file-changes-monitor' ),
	'18' => _x( '18:00', 'a time string of hour followed by minutes', 'website-file-changes-monitor' ),
	'19' => _x( '19:00', 'a time string of hour followed by minutes', 'website-file-changes-monitor' ),
	'20' => _x( '20:00', 'a time string of hour followed by minutes', 'website-file-changes-monitor' ),
	'21' => _x( '21:00', 'a time string of hour followed by minutes', 'website-file-changes-monitor' ),
	'22' => _x( '22:00', 'a time string of hour followed by minutes', 'website-file-changes-monitor' ),
	'23' => _x( '23:00', 'a time string of hour followed by minutes', 'website-file-changes-monitor' ),
);

// Scan days option.
$scan_days = array(
	'7' => _x( 'Sunday', 'the last day of the week and last day of the weekend', 'website-file-changes-monitor' ),
	'1' => _x( 'Monday', 'the first day of the week and first day of the work week', 'website-file-changes-monitor' ),
	'2' => _x( 'Tuesday', 'the second day of the week', 'website-file-changes-monitor' ),
	'3' => _x( 'Wednesday', 'the third day of the week', 'website-file-changes-monitor' ),
	'4' => _x( 'Thursday', 'the fourth day of the week', 'website-file-changes-monitor' ),
	'5' => _x( 'Friday', 'the fith day of the week, last day of the work week', 'website-file-changes-monitor' ),
	'6' => _x( 'Saturday', 'the first day of the weekend', 'website-file-changes-monitor' ),
);

// Scan date option.
$scan_date = array(
	'01' => _x( '01', 'a day number in a given month', 'website-file-changes-monitor' ),
	'02' => _x( '02', 'a day number in a given month', 'website-file-changes-monitor' ),
	'03' => _x( '03', 'a day number in a given month', 'website-file-changes-monitor' ),
	'04' => _x( '04', 'a day number in a given month', 'website-file-changes-monitor' ),
	'05' => _x( '05', 'a day number in a given month', 'website-file-changes-monitor' ),
	'06' => _x( '06', 'a day number in a given month', 'website-file-changes-monitor' ),
	'07' => _x( '07', 'a day number in a given month', 'website-file-changes-monitor' ),
	'08' => _x( '08', 'a day number in a given month', 'website-file-changes-monitor' ),
	'09' => _x( '09', 'a day number in a given month', 'website-file-changes-monitor' ),
	'10' => _x( '10', 'a day number in a given month', 'website-file-changes-monitor' ),
	'11' => _x( '11', 'a day number in a given month', 'website-file-changes-monitor' ),
	'12' => _x( '12', 'a day number in a given month', 'website-file-changes-monitor' ),
	'13' => _x( '13', 'a day number in a given month', 'website-file-changes-monitor' ),
	'14' => _x( '14', 'a day number in a given month', 'website-file-changes-monitor' ),
	'15' => _x( '15', 'a day number in a given month', 'website-file-changes-monitor' ),
	'16' => _x( '16', 'a day number in a given month', 'website-file-changes-monitor' ),
	'17' => _x( '17', 'a day number in a given month', 'website-file-changes-monitor' ),
	'18' => _x( '18', 'a day number in a given month', 'website-file-changes-monitor' ),
	'19' => _x( '19', 'a day number in a given month', 'website-file-changes-monitor' ),
	'20' => _x( '20', 'a day number in a given month', 'website-file-changes-monitor' ),
	'21' => _x( '21', 'a day number in a given month', 'website-file-changes-monitor' ),
	'22' => _x( '22', 'a day number in a given month', 'website-file-changes-monitor' ),
	'23' => _x( '23', 'a day number in a given month', 'website-file-changes-monitor' ),
	'24' => _x( '24', 'a day number in a given month', 'website-file-changes-monitor' ),
	'25' => _x( '25', 'a day number in a given month', 'website-file-changes-monitor' ),
	'26' => _x( '26', 'a day number in a given month', 'website-file-changes-monitor' ),
	'27' => _x( '27', 'a day number in a given month', 'website-file-changes-monitor' ),
	'28' => _x( '28', 'a day number in a given month', 'website-file-changes-monitor' ),
	'29' => _x( '29', 'a day number in a given month', 'website-file-changes-monitor' ),
	'30' => _x( '30', 'a day number in a given month', 'website-file-changes-monitor' ),
);

$restorable_settings = array(
	'scan-frequency'           => __( 'Scan frequency', 'website-file-changes-monitor' ),
	'scan-hour'                => __( 'Scan hour', 'website-file-changes-monitor' ),
	'scan-day'                 => __( 'Scan day of week', 'website-file-changes-monitor' ),
	'scan-hour-am'             => __( 'Scan frequency AM/PM', 'website-file-changes-monitor' ),
	'base_paths_to_scan'       => __( 'Base paths to scan', 'website-file-changes-monitor' ),
	'excluded_file_extensions' => __( 'Ignored file extensions', 'website-file-changes-monitor' ),
	'excluded_directories'     => __( 'Ignored directories', 'website-file-changes-monitor' ),
	'excluded_files'           => __( 'Ignored files', 'website-file-changes-monitor' ),
	'ignored_directories'      => __( 'Excluded directories', 'website-file-changes-monitor' ),
	'allowed-in-core-files'    => __( 'Files allow in core', 'website-file-changes-monitor' ),
	'enabled-notifications'    => __( 'Enabled notifications', 'website-file-changes-monitor' ),
	'email_notice_type'        => __( 'Use admin or custom email address', 'website-file-changes-monitor' ),
	'email-changes-limit'      => __( 'Number of changes reported in email', 'website-file-changes-monitor' ),
	'send-email-upon-changes'  => __( 'Send notification if changes found', 'website-file-changes-monitor' ),
	'empty-email-allowed'      => __( 'Send notification if no changes found', 'website-file-changes-monitor' ),
	'core-scan-enabled'        => __( 'Core scanning enabled', 'website-file-changes-monitor' ),
	'max-file-size'            => __( 'Max file size to scan', 'website-file-changes-monitor' ),
	'purge-length'             => __( 'Number of scans to store', 'website-file-changes-monitor' ),
	'use_custom_from_email'    => __( 'Use custom from address', 'website-file-changes-monitor' ),
);

$validate_nonce = wp_create_nonce( MFM_PREFIX . 'validate_setting_nonce' );
$scaner_running = ( get_site_option( MFM_PREFIX . 'scanner_running', false ) ) ? 'mfm-scan-is-active' : 'mfm-scan-is-idle';
?>

<div class="wrap mfm-admin-wrap <?php echo esc_attr( $scaner_running ); ?>">

	<nav class="nav-tab-wrapper mfm-nav-tab-wrapper">
		<a href="?page=file-monitor-settings&tab=scanning-preferences" data-target-id="scanning-preferences" class="nav-tab nav-tab-active" style="margin-left: 0"><?php esc_html_e( 'General scanning', 'website-file-changes-monitor' ); ?></a>
		<a href="?page=file-monitor-settings&tab=core-preferences" data-target-id="core-preferences" class="nav-tab"><?php esc_html_e( 'WordPress core', 'website-file-changes-monitor' ); ?></a>
		<a href="?page=file-monitor-settings&tab=notification-preferences" data-target-id="notification-preferences" class="nav-tab"><?php esc_html_e( 'Notifications', 'website-file-changes-monitor' ); ?></a>
		<a href="?page=file-monitor-settings&tab=debugging-preferences" data-target-id="debugging-preferences" class="nav-tab"><?php esc_html_e( 'Logging & Debugging', 'website-file-changes-monitor' ); ?></a>
	</nav>

	<br>

	<form method="post" action="" enctype="multipart/form-data">

		<div data-settings-section id="scanning-preferences">
			<div id="mfm-wizard-frequency">
				<h3><?php esc_html_e( 'When should the plugin scan your website for file changes?', 'website-file-changes-monitor' ); ?></h3>
				<table class="form-table mfm-table">
					<tr>
						<th><label for="mfm-settings-frequency"><?php esc_html_e( 'Scan frequency', 'website-file-changes-monitor' ); ?></label></th>
						<td>
							<fieldset>
								<select name="mfm-settings[scan-frequency]">
									<?php foreach ( $frequency_options as $value => $html ) : ?>
										<option value="<?php echo esc_attr( $value ); ?>" <?php selected( $value, $mfm_settings['scan-frequency'] ); ?>><?php echo esc_html( $html ); ?></option>
									<?php endforeach; ?>
								</select>
							</fieldset>
						</td>
					</tr>
					<tr id="scan-time-row">
						<th><label for="mfm-settings-scan-hour"><?php esc_html_e( 'Scan time', 'website-file-changes-monitor' ); ?></label></th>
						<td>
							<fieldset>
								<label>
									<?php
									$use_am_pm_select  = \MFM\Helpers\Settings_Helper::is_time_format_am_pm();
									$selected_hour     = intval( $mfm_settings['scan-hour'] );
									$selected_day_part = $mfm_settings['scan-hour-am'];
									$selected_hour     = str_pad( (string) $selected_hour, 2, '0', STR_PAD_LEFT );
									if ( $use_am_pm_select ) {
										$scan_hours = array_slice( $scan_hours, 0, 12, true );
									}
									?>
									<select name="mfm-settings[scan-hour]">
										<?php foreach ( $scan_hours as $value => $html ) : ?>
											<option value="<?php echo esc_attr( $value ); ?>" <?php selected( $value, $selected_hour ); ?>><?php echo esc_html( $html ); ?></option>
										<?php endforeach; ?>
									</select>
									<?php if ( $use_am_pm_select ) : ?>
										<select name="mfm-settings[scan-hour-am]">
											<?php foreach ( array( 'AM', 'PM' ) as $value ) : ?>
												<option value="<?php echo esc_attr( strtolower( $value ) ); ?>" <?php selected( $value, strtoupper( $selected_day_part ) ); ?>><?php echo esc_html( $value ); ?></option>
											<?php endforeach; ?>
										</select>
									<?php endif; ?>
									<br />
									<span class="description"><?php esc_html_e( 'Hour', 'website-file-changes-monitor' ); ?></span>
								</label>

								<label>
									<select name="mfm-settings[scan-day]">
										<?php foreach ( $scan_days as $value => $html ) : ?>
											<option value="<?php echo esc_attr( $value ); ?>" <?php selected( $value, $mfm_settings['scan-day'] ); ?>><?php echo esc_html( $html ); ?></option>
										<?php endforeach; ?>
									</select>
									<br />
									<span class="description"><?php esc_html_e( 'Day', 'website-file-changes-monitor' ); ?></span>
								</label>
							</fieldset>
						</td>
					</tr>
				</table>
			</div>

			<h3><?php esc_html_e( 'What is the website\'s absolute path?', 'website-file-changes-monitor' ); ?></h3>
			<p class="description"><?php esc_html_e( 'This is just for your reference. You do not need to specify it in any of the settings below.', 'website-file-changes-monitor' ); ?></p>
			<p><?php esc_html_e( 'Website absolute path:', 'website-file-changes-monitor' ); ?> <code><?php echo esc_attr( ABSPATH ); ?></code></p>
			<br>

			<div id="mfm-wizard-ignore">
				<h3><?php esc_html_e( 'Which files and directories should the plugin ignore?', 'website-file-changes-monitor' ); ?></h3>
				<p class="description"><?php esc_html_e( 'Use the settings below to specify the files or directories that the plugin should ignore. Ignored files and directories are still included in the scan but the plugin won’t report any detected changes.', 'website-file-changes-monitor' ); ?></p>

				<table class="form-table mfm-table">
					<tbody>
						<tr>
							<th><label for="mfm-settings-exclude-dirs"><?php esc_html_e( 'Ignore all files in these directories', 'website-file-changes-monitor' ); ?></label></th>
								<td>
								<fieldset>
									<?php Wildcard_Hint::render(); ?>
									<br>
									<div class="mfm-files-container">
										<input class="name" type="text" data-input-for="excluded_directories">
										<input class="button add" data-list-type="excluded_directories" type="button" value="Add" data-validate-setting-nonce="<?php echo esc_attr( $validate_nonce ); ?>">
									</div>		

									<div class="mfm-removals mfm-validation-response" data-validation-response-for="excluded_directories"><span></span></div>
									<br>

									<div class="mfm-files-container" data-list-items-wrapper-for="excluded_directories">
										<?php
										if ( is_array( $mfm_settings['excluded_directories'] ) && ! empty( $mfm_settings['excluded_directories'] ) ) {
											foreach ( $mfm_settings['excluded_directories'] as $extension => $label ) {
												echo '<span ><input type="checkbox" name="mfm-settings[excluded_directories][]" id="' . esc_attr( $label ) . '" value="' . esc_attr( $label ) . '" checked=""><label for="' . esc_attr( $label ) . '">' . esc_attr( str_replace( ABSPATH, '', $label ) ) . '</label></span><br>';
											}
										}

										?>
									</div>	
									
									<div class="mfm-removals" data-marked-for-removal-for="excluded_directories"><span></span> <?php esc_html_e( 'will be removed on next save.', 'website-file-changes-monitor' ); ?></div>
								</fieldset>
							</td>
						</tr>
					</tbody>
				</table>
			</div>

			<div id="mfm-wizard-ignore-step-2">
				<h3 class="wizard-only"><?php esc_html_e( 'Which specific file(s) should the plugin ignore during a scan?', 'website-file-changes-monitor' ); ?></h3>
				<p class="description wizard-only"><?php esc_html_e( 'Use the setting below to specify the file and extension of the files the plugin should ignore during a scan and not report the changes. This setting is also available in the plugin\'s settings page, so it can be changed at a later stage.', 'website-file-changes-monitor' ); ?></p>

				<table class="form-table mfm-table">
					<tbody>
						<tr>
							<th><label for="mfm-settings-exclude-filenames"><?php esc_html_e( 'Ignore these files', 'website-file-changes-monitor' ); ?></label></th>
							<td>
								<fieldset>
									<p class="description"><?php esc_html_e( 'Add the filename and extension of the file(s) you want to exclude from the scan. When you specify a filename and extension, all the files that match that name will be excluded. If you would like to exclude a specific file, specify the location and the filename. The directory path should be in relation to the website\'s root. For example:', 'website-file-changes-monitor' ); ?> <code>dir1/dir2/file.php</code></p><br>

									<div class="mfm-files-container">
										<input class="name" type="text" data-input-for="excluded_files">
										<input class="button add" data-list-type="excluded_files" type="button" value="Add" data-validate-setting-nonce="<?php echo esc_attr( $validate_nonce ); ?>">
									</div>

									<div class="mfm-removals mfm-validation-response" data-validation-response-for="excluded_files"><span></span></div>

									<br>

									<div class="mfm-files-container" data-list-items-wrapper-for="excluded_files">
										<?php
										if ( is_array( $mfm_settings['excluded_files'] ) && ! empty( $mfm_settings['excluded_files'] ) ) {
											foreach ( $mfm_settings['excluded_files'] as $extension => $label ) {
												echo '<span ><input type="checkbox" name="mfm-settings[excluded_files][]" id="' . esc_attr( $label ) . '" value="' . esc_attr( $label ) . '" checked=""><label for="' . esc_attr( $label ) . '">' . esc_attr( $label ) . '</label></span><br>';
											}
										}

										?>
									</div>
									
									<div class="mfm-removals" data-marked-for-removal-for="excluded_files"><span></span> <?php esc_html_e( 'will be removed on next save.', 'website-file-changes-monitor' ); ?></div>
								</fieldset>
							</td>
						</tr>
					</tbody>
				</table>
			</div>

			<div id="mfm-wizard-ignore-step-3">
				<h3 class="wizard-only"><?php esc_html_e( 'Which group of files should the plugin ignore during a scan?', 'website-file-changes-monitor' ); ?></h3>
				<p class="description wizard-only"><?php esc_html_e( 'Use the setting below to specify the extension of files the plugin should ignore during a scan and not report the changes. This setting is also available in the plugin\'s settings page, so it can be changed at a later stage.', 'website-file-changes-monitor' ); ?></p>

				<table class="form-table mfm-table">
					<tbody>
						<tr>	
							<th><label for="mfm-settings-exclude-extensions"><?php esc_html_e( 'Ignore all files with these extensions', 'website-file-changes-monitor' ); ?></label></th></label></th>
							<td>
								<fieldset>
									<p class="description"><?php esc_html_e( 'Specify the extension of the file types you want to exclude. You should exclude any type of logs and backup files that tend to be very big.', 'website-file-changes-monitor' ); ?></p><br>
									<div class="mfm-files-container">
										<input class="name" type="text" data-input-for="excluded_file_extensions">
										<input class="button add" data-list-type="excluded_file_extensions"  type="button" value="Add" data-validate-setting-nonce="<?php echo esc_attr( $validate_nonce ); ?>">
									</div>

									<div class="mfm-removals mfm-validation-response" data-validation-response-for="excluded_file_extensions"><span></span></div>

									<br>
									<div class="mfm-settings-grid" data-list-items-wrapper-for="excluded_file_extensions">
										<?php
										if ( is_array( $mfm_settings['excluded_file_extensions'] ) && ! empty( $mfm_settings['excluded_file_extensions'] ) ) {
											foreach ( $mfm_settings['excluded_file_extensions'] as $extension => $label ) {
												echo '<span ><input type="checkbox" name="mfm-settings[excluded_file_extensions][]" id="' . esc_attr( $label ) . '" value="' . esc_attr( $label ) . '" checked=""><label for="' . esc_attr( $label ) . '">' . esc_attr( $label ) . '</label></span>';
											}
										}
										?>
									</div>

									<div class="mfm-removals" data-marked-for-removal-for="excluded_file_extensions"><span></span> <?php esc_html_e( 'will be removed on next save.', 'website-file-changes-monitor' ); ?></div>
								</fieldset>
							</td>
						</tr>
					</tbody>
				</table>
			</div>

			<div id="mfm-wizard-ignore-step-4">
				<h3><?php esc_html_e( 'Which directories should be excluded from the scan?', 'website-file-changes-monitor' ); ?></h3>
				<p class="description"><?php esc_html_e( 'Use the settings below to specify the directories that the plugin should exclude from the file scans. When a directory is excluded, the plugin won’t scan any of the files within that directory and won’t be aware of any changes within that directory. This setting is ideal for directories with a large number of files, for example, where images, videos and other digital media is stored.', 'website-file-changes-monitor' ); ?></p>

				<table class="form-table mfm-table">
					<tbody>
						<tr>
							<th><label for="mfm-settings-exclude-dirs"><?php esc_html_e( 'Exclude all files in these directories', 'website-file-changes-monitor' ); ?></label></th>
							<td>
							<fieldset>
									<?php Wildcard_Hint::render(); ?>
									<br>
									<div class="mfm-files-container">
										<input class="name" type="text" data-input-for="ignored_directories">
										<input class="button add" data-list-type="ignored_directories" type="button" value="Add" data-validate-setting-nonce="<?php echo esc_attr( $validate_nonce ); ?>">
									</div>		

									<div class="mfm-removals mfm-validation-response" data-validation-response-for="ignored_directories"><span></span></div>
									<br>

									<div class="mfm-files-container" data-list-items-wrapper-for="ignored_directories">
										<?php
										if ( is_array( $mfm_settings['ignored_directories'] ) && ! empty( $mfm_settings['ignored_directories'] ) ) {
											foreach ( $mfm_settings['ignored_directories'] as $extension => $label ) {
												echo '<span ><input type="checkbox" name="mfm-settings[ignored_directories][]" id="' . esc_attr( $label ) . '" value="' . esc_attr( $label ) . '" checked=""><label for="' . esc_attr( $label ) . '">' . esc_attr( str_replace( ABSPATH, '', $label ) ) . '</label></span><br>';
											}
										}
										?>
									</div>	
									
									<div class="mfm-removals" data-marked-for-removal-for="ignored_directories"><span></span> <?php \esc_html_e( 'will be removed on next save.', 'website-file-changes-monitor' ); ?></div>
								</fieldset>
							</td>
						</tr>

					</tbody>
				</table>
			</div>
			<!-- Exclude extensions -->			

			<h3><?php esc_html_e( 'How should the plugin handle files with no extension?', 'website-file-changes-monitor' ); ?></h3>
			<p class="description"><?php esc_html_e( 'By default the plugin also scans files with no extension. You can disable this feature using the setting below.', 'website-file-changes-monitor' ); ?></p>
			<table class="form-table">
				<tr>
					<th><label for="mfm-scan-files-with-no-extension"><?php esc_html_e( 'Scan files without extension', 'website-file-changes-monitor' ); ?></label></th>
					<td>
						<fieldset>
							<label class="mfm-toggle">
								<input class="mfm-toggle-checkbox" type="checkbox" name="mfm-settings[scan-files-with-no-extension]" <?php checked( $mfm_settings['scan-files-with-no-extension'], 'yes' ); ?> value="yes">
								<div class="mfm-toggle-switch"></div>
								<span class="mfm-toggle-label"></span>
							</label>
						</fieldset>
					</td>
				</tr>
			</table>	

			<h3><?php esc_html_e( 'What is the biggest file size the plugin should scan?', 'website-file-changes-monitor' ); ?></h3>
			<p class="description"><?php esc_html_e( 'Use the setting below to configure the maximum file size the plugin should exclude from the scan. Files exceeding this size will not be scanned. Tip: files containing source code are rarely this large. Large file sizes typically apply to images, videos, PDFs, and other media files.', 'website-file-changes-monitor' ); ?></p>
			<table class="form-table mfm-table">
				<tr>
					<th><label for="mfm-settings-file-size"><?php esc_html_e( 'File size limit', 'website-file-changes-monitor' ); ?></label></th>
					<td>
						<fieldset>
							<input type="number" name="mfm-settings[max-file-size]" min="1" max="100" value="<?php echo esc_attr( $mfm_settings['max-file-size'] ); ?>" /> <?php esc_html_e( 'MB', 'website-file-changes-monitor' ); ?>
						</fieldset>
					</td>
				</tr>
			</table>

			<div id="mfm-wizard-purging">
				<h3><?php esc_html_e( 'How much scan data would like you the plugin to keep?', 'website-file-changes-monitor' ); ?></h3>
				<p class="description"><?php esc_html_e( 'By default, Melapress File Monitor retains only the scan results from the last scan, which are overwritten with each new scan. If you\'d like to keep data from multiple scans, please specify how many sets of results you wish to retain below.', 'website-file-changes-monitor' ); ?></p>
				
				<table class="form-table mfm-table">
					<tr>
						<th><label for="mfm-settings-purge-length"><?php esc_html_e( 'Events purging', 'website-file-changes-monitor' ); ?></label></th>
						<td>
							<fieldset>
								<?php esc_html_e( 'Scan events purging: Remove file changes events older than', 'website-file-changes-monitor' ); ?> <input type="number" name="mfm-settings[purge-length]" min="1" max="100" value="<?php echo esc_attr( $mfm_settings['purge-length'] ); ?>" /> <?php esc_html_e( 'scan(s)', 'website-file-changes-monitor' ); ?>
							</fieldset>
						</td>
					</tr>
				</table>
				</div>
		</div>

		<div data-settings-section id="core-preferences">
			
			<div id="mfm-wizard-enable-core">
				<h3><?php esc_html_e( 'Should the plugin cross check the WordPress core files?', 'website-file-changes-monitor' ); ?></h3>
				<p class="description">
					<?php esc_html_e( 'During the scan, the plugin cross checks (verifies the integrity) of your WordPress core files by comparing them to the files in the official WordPress repository. If any discrepancies are found, the plugin will notify you of the changes.', 'website-file-changes-monitor' ); ?> 
				</p>
				<table class="form-table">
					<tr>
						<th><label for="mfm-enable-core-scan"><?php esc_html_e( 'Cross check WordPress', 'website-file-changes-monitor' ); ?></label></th>
						<td>
							<fieldset>
								<label class="mfm-toggle">
									<input class="mfm-toggle-checkbox" type="checkbox" name="mfm-settings[core-scan-enabled]" <?php checked( $mfm_settings['core-scan-enabled'], 'yes' ); ?> value="yes">
									<div class="mfm-toggle-switch"></div>
									<span class="mfm-toggle-label"></span>
								</label>
							</fieldset>
						</td>
					</tr>
				</table>	
			</div>

			<h3><?php esc_html_e( 'Which files are allowed in the WordPress core directories (root, wp-admin, wp-includes)?', 'website-file-changes-monitor' ); ?></h3>
			<p class="description">
				<?php
					// translators: path names.
					printf( esc_html__( 'In a default installation all non-core WordPress files are stored within the %1$1s directory. However, if your website contains custom or third-party files in the root or core directories like %2$2s and %3$3s, you can add them to the list below to mark them as legitimate. Otherwise, the plugin will flag them during each scan.', 'website-file-changes-monitor' ), '<code>/wp-content</code>', '<code>wp-admin</code>', '<code>wp-includes</code>' );
				?>
			</p><br>
			<p class="description"><?php esc_html_e( 'Please note, adding files to this list ensures they won\'t be flagged, but the plugin will still alert you of any modifications. If you don\'t want to receive alerts of changes in these files or directories, consider excluding them from scans entirely.', 'website-file-changes-monitor' ); ?></p>

				<table class="form-table mfm-table">
				<tr>
					<th>
						<label for="mfm-settings-allowed-in-core-filenames"><?php esc_html_e( 'Allow these files', 'website-file-changes-monitor' ); ?></label>
					</th>
					<td>
						<fieldset>
							<p class="description"><?php \esc_html_e( 'Only specify the name and extension of the file(s) you want to allow in the website root and core directories. There is no need to specify the path of the file. Wildcards are not supported.', 'website-file-changes-monitor' ); ?></p>
							<br>

							<div class="mfm-files-container">
								<input class="name" type="text" data-input-for="allowed-in-core-files">
								<input class="button add" data-list-type="allowed-in-core-files" data-object-type="files" type="button" value="<?php esc_html_e( 'Add', 'website-file-changes-monitor' ); ?>" data-validate-setting-nonce="<?php echo esc_attr( $validate_nonce ); ?>" 
									data-trigger-popup="<?php esc_html_e( 'When files are added to this list, the plugin will consider them as part of your website\'s WordPress core. Therefore it will scan them during the normal file integrity scans and will alert you if they are modified or deleted. If you do not want to be alerted of such changes about this file, exclude it from the scan.', 'website-file-changes-monitor' ); ?>"
									data-trigger-popup-title="<?php esc_html_e( 'Adding an allowed file', 'website-file-changes-monitor' ); ?>"
								/>
							</div>

							<div class="mfm-removals mfm-validation-response" data-validation-response-for="allowed-in-core-files"><span></span></div>

							<br>
							
							<div class="mfm-files-container">
								<div class="item-list allowed-in-core-list" id="mfm-allowed-in-core-files-list" data-list-items-wrapper-for="allowed-in-core-files">
									<?php if ( is_array( $mfm_settings['allowed-in-core-files'] ) ) : ?>
										<?php foreach ( $mfm_settings['allowed-in-core-files'] as $file ) : ?>
											<span>
												<input type="checkbox" name="mfm-settings[allowed-in-core-files][]" id="allowed-in-core-files-<?php echo esc_attr( $file ); ?>" value="<?php echo esc_attr( $file ); ?>" checked />
												<label for="allowed-in-core-files-<?php echo esc_attr( $file ); ?>"><?php echo esc_html( $file ); ?></label>
											</span>
											<br>
										<?php endforeach; ?>
									<?php endif; ?>
								</div>
							</div>

							<div class="mfm-removals" data-marked-for-removal-for="allowed-in-core-files"><span></span> <?php esc_html_e( 'will be removed on next save.', 'website-file-changes-monitor' ); ?></div>
						</fieldset>
					</td>
				</tr>
				<!-- Files allowed in site root and WP core -->
			</table>
		</div>

		<div data-settings-section id="notification-preferences">
			<h3><?php esc_html_e( 'Which file changes do you want to be notified of?', 'website-file-changes-monitor' ); ?></h3>
			<!-- Type of Changes -->
			<table class="form-table mfm-table">
				<tr>
					<th><label for="mfm-file-changes-type"><?php esc_html_e( 'Notify me when files are', 'website-file-changes-monitor' ); ?></label></th>
					<td>
						<fieldset>
							<label for="added">
								<input type="checkbox" name="mfm-settings[enabled-notifications][]" value="added" <?php echo in_array( 'added', $mfm_settings['enabled-notifications'], true ) ? 'checked' : false; ?>>
								<span><?php esc_html_e( 'Added', 'website-file-changes-monitor' ); ?></span>
							</label>
							<br>
							<label for="deleted">
								<input type="checkbox" name="mfm-settings[enabled-notifications][]" value="deleted" <?php echo in_array( 'deleted', $mfm_settings['enabled-notifications'], true ) ? 'checked' : false; ?>>
								<span><?php esc_html_e( 'Deleted', 'website-file-changes-monitor' ); ?></span>
							</label>
							<br>
							<label for="modified">
								<input type="checkbox" name="mfm-settings[enabled-notifications][]" value="modified" <?php echo in_array( 'modified', $mfm_settings['enabled-notifications'], true ) ? 'checked' : false; ?>>
								<span><?php esc_html_e( 'Modified', 'website-file-changes-monitor' ); ?></span>
							</label>
							<br>
							<label for="permissions_changed">
								<input type="checkbox" name="mfm-settings[enabled-notifications][]" value="permissions_changed" <?php echo in_array( 'permissions_changed', $mfm_settings['enabled-notifications'], true ) ? 'checked' : false; ?>>
								<span><?php esc_html_e( 'Permissions modified', 'website-file-changes-monitor' ); ?></span>
							</label>
						</fieldset>
					</td>
				</tr>
			</table>

			<!-- Email to send changes notices to -->
			<div id="mfm-wizard-notification">
				<h3><?php esc_html_e( 'Where should the plugin send the file changes notification?', 'website-file-changes-monitor' ); ?></h3>
				<p class="description"><?php esc_html_e( 'By default the plugin sends the email notifications to the administrator email address configured in the WordPress settings. Use the below setting to send the email notification to a different email address. You can specify multiple email addresses by separating them with a comma.', 'website-file-changes-monitor' ); ?></p>
				<table class="form-table mfm-table">
					<tr>
						<th><label for="mfm-notification-email-address"><?php esc_html_e( 'Notify this address', 'website-file-changes-monitor' ); ?></label></th>
						<td>
							<fieldset>
								<label for="email-notice-admin">
									<input type="radio" id="email-notice-admin" name="mfm-settings[email_notice_type]" value="admin"<?php echo ( 'custom' !== $email_notice_type ) ? ' checked' : ''; ?>>
									<span><?php esc_html_e( 'Use admin email address in website settings.', 'website-file-changes-monitor' ); ?></span>
								</label>
								<br />
								<label for="email-notice-custom">
									<input type="radio" id="email-notice-custom" name="mfm-settings[email_notice_type]" value="custom"<?php echo ( 'custom' === $email_notice_type ) ? ' checked' : ''; ?>>
									<span><?php esc_html_e( 'Use a different email:', 'website-file-changes-monitor' ); ?></span>
									<input type="email" id="notice-email-address" name="mfm-settings[custom_email_address]" multiple value="<?php echo esc_attr( $mfm_settings['custom_email_address'] ); ?>" placeholder="<?php esc_html_e( 'Enter email', 'website-file-changes-monitor' ); ?>">
								</label>
								<br />
								<br />
								<input type="button" class="button button-primary" id="mfm-send-test-email" data-nonce="<?php echo esc_attr( wp_create_nonce( MFM_PREFIX . 'test_email_nonce' ) ); ?>" value="<?php esc_attr_e( 'Send a test email', 'website-file-changes-monitor' ); ?>"/>
								<div id="mfm-test-email-response" class="hidden">
									<?php /* Translators: Contact us hyperlink */ ?>
									<p><?php printf( esc_html__( 'Oops! Email sending failed. Please %s for assistance.', 'website-file-changes-monitor' ), '<a href="https://melapress.com/support/?utm_source=plugin&utm_medium=link&utm_campaign=mfm" target="_blank">' . esc_html__( 'contact us', 'website-file-changes-monitor' ) . '</a>' ); ?></p>
								</div>
							</fieldset>
						</td>
					</tr>
				</table>
			</div>

			<div id="mfm-wizard-notification">
				<h3><?php esc_html_e( 'Which email address should the plugin use as a from address?', 'website-file-changes-monitor' ); ?></h3>
				<p class="description"><?php esc_html_e( 'By default when the plugin sends an email notification it uses the email address specified in this website’s general settings. Though you can change the email address and display name from this section.', 'website-file-changes-monitor' ); ?></p>
				<table class="form-table mfm-table">
					<tr>
						<th><label for="mfm-notification-email-address"><?php esc_html_e( 'From Email & Name', 'website-file-changes-monitor' ); ?></label></th>
						<td>
							<fieldset>
								<?php $use_email = $mfm_settings['use_custom_from_email']; ?>
								<label for="default_email">
									<input type="radio" name="mfm-settings[use_custom_from_email]" id="default_email" value="default_email" <?php checked( $use_email, 'default_email' ); ?> />
									<?php esc_html_e( 'Use the email address from the WordPress general settings', 'website-file-changes-monitor' ); ?>
								</label>
								<br>
								<label for="custom_email">
									<input type="radio" name="mfm-settings[use_custom_from_email]" id="custom_email" value="custom_email" <?php checked( $use_email, 'custom_email' ); ?> />
									<?php esc_html_e( 'Use another email address', 'website-file-changes-monitor' ); ?>
								</label>
								<br>
								<label for="from-email">
									<?php esc_html_e( 'Email Address', 'website-file-changes-monitor' ); ?>
									<input type="email" id="from-email" name="mfm-settings[from-email]" value="<?php echo esc_attr( $mfm_settings['from-email'] ); ?>" />
								</label>
								<br>
								<label for="from-display-name">
									<?php esc_html_e( 'Display Name', 'website-file-changes-monitor' ); ?>&nbsp;
									<input type="text" id="from-display-name" name="mfm-settings[from-display-name]" value="<?php echo esc_attr( $mfm_settings['from-display-name'] ); ?>" />
								</label>
							</fieldset>
						</td>
					</tr>
				</table>
			</div>

			<!-- Scan results email -->
			<h3><?php esc_html_e( 'How many file changes should the plugin report in the email?', 'website-file-changes-monitor' ); ?></h3>
			<p class="description"><?php esc_html_e( 'To avoid long emails, by default the plugin only reports up to 10 changes per file type change. You can increase or decrease the number of reported file changes in the email from the below setting:', 'website-file-changes-monitor' ); ?></p>
			<table class="form-table mfm-table">
				<tr>
					<th><label for="mfm-settings-email-changes-limit"><?php esc_html_e( 'Number of file changes', 'website-file-changes-monitor' ); ?></label></th>
					<td>
						<fieldset>
							<input type="number" name="mfm-settings[email-changes-limit]" min="5" max="1000" value="<?php echo esc_attr( $mfm_settings['email-changes-limit'] ); ?>" />
						</fieldset>
					</td>
				</tr>
			</table>

			<!-- Enable/Disable empty notification email -->
			<div id="mfm-wizard-notification-when">
				<h3><?php esc_html_e( 'When should the plugin send you an email?', 'website-file-changes-monitor' ); ?></h3>
				<p class="description"><?php esc_html_e( 'Use the settings below to specify when the plugin should send you an email.', 'website-file-changes-monitor' ); ?></p>
				<table class="form-table mfm-table">
					<tr>
						<th><label for="mfm-send-email-upon-changes"><?php esc_html_e( 'Email settings', 'website-file-changes-monitor' ); ?></label></th>
						<td>
							<fieldset>
								<label for="mfm-send-email-upon-changes">
									<input id="mfm-send-email-upon-changes" type="checkbox" name="mfm-settings[send-email-upon-changes]" value="yes" <?php checked( 'yes', $mfm_settings['send-email-upon-changes'], true ); ?>>
									<?php esc_html_e( 'Send me an email when file changes are detected', 'website-file-changes-monitor' ); ?>
								</label>
							</fieldset>
							<fieldset>
								<label for="mfm-empty-email-allowed">
									<input id="mfm-empty-email-allowed" type="checkbox" name="mfm-settings[empty-email-allowed]" value="yes" <?php checked( 'yes', $mfm_settings['empty-email-allowed'], true ); ?>>
									<?php esc_html_e( 'Send me an email even when a scan finishes and no file changes are detected', 'website-file-changes-monitor' ); ?>
								</label>
							</fieldset>
						</td>
					</tr>
				</table>
			</div>
		</div>

		<div data-settings-section id="debugging-preferences">
		
			<h3><?php esc_html_e( 'Temporarily disable file scanning', 'website-file-changes-monitor' ); ?></h3>
			<p class="description"><?php esc_html_e( 'Use the below switch to disable file scanning. When you disable and re-enable scanning, the plugin will compare the file scan to those of the last scan before it was disabled.', 'website-file-changes-monitor' ); ?></p>
			<table class="form-table">
				<tr>
					<th><label for="mfm-file-changes"><?php esc_html_e( 'File scanning', 'website-file-changes-monitor' ); ?></label></th>
					<td>
						<fieldset>
							<label class="mfm-toggle">
								<input class="mfm-toggle-checkbox" type="checkbox" name="mfm-settings[logging-enabled]" <?php checked( $mfm_settings['logging-enabled'], 'yes' ); ?> value="yes">
								<div class="mfm-toggle-switch"></div>
								<span class="mfm-toggle-label"></span>
							</label>
						</fieldset>

						<div id="mfm-disable-logging-warning"><?php esc_html_e( 'A scan is in progress, disabling logging will abort this scan.', 'website-file-changes-monitor' ); ?> </div>
					</td>
				</tr>
			</table>
			<!-- Disable File Changes -->

			<?php if ( get_site_option( MFM_PREFIX . 'scanner_running', false ) ) { ?>
				<h3><?php esc_html_e( 'Cancel scan in-progress', 'website-file-changes-monitor' ); ?></h3>
				<table class="form-table">
					<tr>
						<th><label for="mfm-file-changes"><?php esc_html_e( 'Cancel active scan', 'website-file-changes-monitor' ); ?></label></th>
						<td>
							<fieldset>
								<input type="button" class="button button-primary" id="mfm-cancel-in-progress" value="<?php esc_attr_e( 'Cancel scan', 'website-file-changes-monitor' ); ?>"/>
								
								<div id="mfm-cancel-proceed"><?php esc_html_e( 'Are you sure?', 'website-file-changes-monitor' ); ?> <a data-nonce="<?php echo esc_attr( wp_create_nonce( MFM_PREFIX . 'cancel_scan_nonce' ) ); ?>" href="#proceed"><?php esc_html_e( 'Proceed', 'website-file-changes-monitor' ); ?></a> <a href="#cancel"><?php esc_html_e( 'Cancel', 'website-file-changes-monitor' ); ?></a> </div>
								<div id="mfm-cancel-response"></div>
							</fieldset>
						</td>
					</tr>
				</table>
			<?php } ?>

			<h3><?php esc_html_e( 'Debug & deactivation settings', 'website-file-changes-monitor' ); ?></h3>
			<p class="description">
				<?php esc_html_e( 'Enable the debug logging when requested by support. This is used for support.', 'website-file-changes-monitor' ); ?> <?php esc_html_e( 'The debug log file is saved in the /wp-content/uploads/mfm-logs/ folder on your website.', 'website-file-changes-monitor' ); ?>
			</p>
			<table class="form-table">
				<tr>
					<th><label for="mfm-debug-logging"><?php esc_html_e( 'Debug logs', 'website-file-changes-monitor' ); ?></label></th>
					<td>
						<fieldset>
							<label class="mfm-toggle">
								<input class="mfm-toggle-checkbox" type="checkbox" name="mfm-settings[debug-logging-enabled]" <?php checked( $mfm_settings['debug-logging-enabled'], 'yes' ); ?> value="yes">
								<div class="mfm-toggle-switch"></div>
								<span class="mfm-toggle-label"></span>
							</label>
						</fieldset>
					</td>
				</tr>
			</table>			

			<table class="form-table mfm-settings-danger">
				<tr>
					<th><label for="mfm-delete-data"><?php esc_html_e( 'Delete plugin data upon deactivation', 'website-file-changes-monitor' ); ?></label></th>
					<td>
						<fieldset>

							<label class="mfm-toggle">
								<input class="mfm-toggle-checkbox" type="checkbox" name="mfm-settings[delete-data-enabled]" <?php checked( $mfm_settings['delete-data-enabled'], 'yes' ); ?> value="yes">
								<div class="mfm-toggle-switch"></div>
								<span class="mfm-toggle-label"></span>
							</label>
						</fieldset>
					</td>
				</tr>
			</table>

			<table class="form-table">
				<tr>
					<th><label for="mfm-debug-logging"><?php esc_html_e( 'Purge plugin data', 'website-file-changes-monitor' ); ?></label></th>
					<td>
						<fieldset>
							<input type="button" class="button button-primary" id="mfm-perform-purge" value="<?php esc_attr_e( 'Purge plugin data', 'website-file-changes-monitor' ); ?>"/>
							
							<div id="mfm-purge-proceed"><?php esc_html_e( 'Are you sure? This will remove ALL data, settings and events.', 'website-file-changes-monitor' ); ?> <a data-nonce="<?php echo esc_attr( wp_create_nonce( MFM_PREFIX . 'purge_data_nonce' ) ); ?>" href="#proceed"><?php esc_html_e( 'Proceed', 'website-file-changes-monitor' ); ?></a> <a href="#cancel"><?php esc_html_e( 'Cancel', 'website-file-changes-monitor' ); ?></a> </div>
							<div id="mfm-purge-response"></div>
						</fieldset>
					</td>
				</tr>
			</table>

			<table class="form-table">
				<tr>
					<th><label for="mfm-debug-logging"><?php esc_html_e( 'Restore plugin setting', 'website-file-changes-monitor' ); ?></label></th>
					<td>
						<fieldset>
							<select id="selected-setting">
								<?php
								foreach ( $restorable_settings as $setting => $label ) {
									echo '<option value="' . esc_attr( $setting ) . '">' . esc_attr( $label ) . '</option>';
								}
								?>
							</select>
							<input type="button" class="button button-primary" id="mfm-perform-setting-reset" value="<?php esc_attr_e( 'Restore setting to default', 'website-file-changes-monitor' ); ?>"/>
							
							<div id="mfm-reset-proceed"><?php esc_html_e( 'Are you sure? This reset this setting to its default value.', 'website-file-changes-monitor' ); ?> <a data-nonce="<?php echo esc_attr( wp_create_nonce( MFM_PREFIX . 'reset_setting_nonce' ) ); ?>" href="#proceed"><?php esc_html_e( 'Proceed', 'website-file-changes-monitor' ); ?></a> <a href="#cancel"><?php esc_html_e( 'Cancel', 'website-file-changes-monitor' ); ?></a> </div>
							<div id="mfm-reset-response"></div>
						</fieldset>
					</td>
				</tr>
			</table>

		</div>


		<?php submit_button( 'Save' ); ?>
		<?php wp_nonce_field( 'mfm-settings-save', 'mfm-settings-save-nonce' ); ?>


	</form>

</div>

<?php
