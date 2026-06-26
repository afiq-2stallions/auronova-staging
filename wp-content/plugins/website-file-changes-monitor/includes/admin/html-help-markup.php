<?php
/**
 * Help tab.
 *
 * @package MFM
 * @since 2.0.0
 */

declare(strict_types=1);

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

?>

<div class="wrap mfm-admin-wrap">

	<nav class="nav-tab-wrapper mfm-nav-tab-wrapper">
		<a href="?page=file-monitor-help&tab=help" data-target-id="help" class="nav-tab nav-tab-active" style="margin-left: 0"><?php esc_html_e( 'Help', 'website-file-changes-monitor' ); ?></a>
		<a href="?page=file-monitor-help&tab=about" data-target-id="about" class="nav-tab"><?php esc_html_e( 'About Us', 'website-file-changes-monitor' ); ?></a>
		<a href="?page=file-monitor-help&tab=system-info" data-target-id="system-info" class="nav-tab"><?php esc_html_e( 'System Info', 'website-file-changes-monitor' ); ?></a>
	</nav>

	<br>

	<div data-settings-section id="help">
		<h2><?php esc_html_e( 'Getting Started', 'website-file-changes-monitor' ); ?></h2>
		
		<p class="description"><?php esc_html_e( 'Getting alerted of file changes on your WordPress website is as easy as 1 2 3 with Melapress File Monitor plugin. This can be easily done through the install wizard or the plugin settings. If you are stuck, no problem! Below are a few links of guides to help you get started:', 'website-file-changes-monitor' ); ?></p>
		<br>

		<a href="https://melapress.com/support/kb/website-file-changes-monitor-getting-started-plugin/?utm_source=plugin&utm_medium=link&utm_campaign=mfm" target="_blank"><?php esc_html_e( 'Getting started with Melapress File Monitor', 'website-file-changes-monitor' ); ?></a><br>
		<a href="https://melapress.com/support/kb/website-file-changes-monitor-change-file-changes-notification-email-address/?utm_source=plugin&utm_medium=link&utm_campaign=mfm" target="_blank"><?php esc_html_e( 'How to change the email address where notifications are sent', 'website-file-changes-monitor' ); ?></a>
		<br>
		<br>
		
		<p class="description"><?php esc_html_e( 'Once you install the plugin it will automatically scan for file changes. When the plugin detects a new, modified or deleted file it will notify you with an orange icon as shown below:', 'website-file-changes-monitor' ); ?></p>
		<br>

		<p class="description"><img style="max-width: calc(100% - 30px); box-shadow: 0 10px 16px 0 rgba(0,0,0,0.2), 0 6px 20px 0 rgba(0,0,0,0.19) !important; margin: 0 15px;;" src="<?php echo esc_url( MFM_BASE_URL . 'assets/img/website-file-changes-monitor-view.png' ); ?>" alt="<?php esc_html_e( 'Melapress File Monitor View', 'website-file-changes-monitor' ); ?>"></p>
		<br>

			<!-- Plugin documentation -->
	<div class="title">
		<h2 style="padding-left: 0;"><?php esc_html_e( 'Plugin Documentation', 'website-file-changes-monitor' ); ?></h2>
	</div>
	<p><?php esc_html_e( 'For more technical information about the Melapress File Monitor plugin please visit the plugin’s knowledge base.', 'website-file-changes-monitor' ); ?></p>
	<div class="btn">
		<a href="<?php echo esc_url( 'https://melapress.com/support/kb/?utm_source=plugin&utm_medium=link&utm_campaign=mfm' ); ?>" class="button" target="_blank"><?php esc_html_e( 'Knowledge Base', 'website-file-changes-monitor' ); ?></a>

		<div class="title">
		<h2 style="padding-left: 0;"><?php esc_html_e( 'Plugin Support', 'website-file-changes-monitor' ); ?></h2>
	</div>
	<p><?php esc_html_e( 'You can post your question on our support forum or send us an email for 1 to 1 support. Email support is provided to both free and premium plugin users.', 'website-file-changes-monitor' ); ?></p>
	<div class="btn">
		<a href="<?php echo esc_url( 'https://wordpress.org/support/plugin/website-file-changes-monitor' ); ?>" class="button" target="_blank"><?php esc_html_e( 'Free support forum', 'website-file-changes-monitor' ); ?></a>
		<a href="<?php echo esc_url( 'https://melapress.com/support/submit-ticket/?utm_source=plugin&utm_medium=referral&utm_campaign=MFM&utm_content=free+support+email' ); ?>" class="button" target="_blank"><?php esc_html_e( 'Free email support', 'website-file-changes-monitor' ); ?></a>
	</div>
	<!-- End -->

	<br>

	</div>
		
		<h3><?php esc_html_e( 'Rate Melapress File Monitor', 'website-file-changes-monitor' ); ?></h3>
		<p class="description"><?php esc_html_e( 'We work really hard to develop a good plugin with which you can be notified of file changes. It involves thousands of man-hours and an endless amount of dedication to research, develop, and maintain this plugin. Therefore if you like what you see, and find Melapress File Monitor useful we ask you nothing more than to please rate our plugin. We appreciate every star!', 'website-file-changes-monitor' ); ?></p>
		<br>

		<p class="description">
			<a href="https://wordpress.org/plugins/website-file-changes-monitor/#reviews" class="rating-link" target="_blank">
				<span class="dashicons dashicons-star-filled"></span>
				<span class="dashicons dashicons-star-filled"></span>
				<span class="dashicons dashicons-star-filled"></span>
				<span class="dashicons dashicons-star-filled"></span>
				<span class="dashicons dashicons-star-filled"></span>
			</a>
			<a href="https://wordpress.org/plugins/website-file-changes-monitor/#reviews" class="button" target="_blank"><?php esc_html_e( 'Rate Plugin', 'website-file-changes-monitor' ); ?></a>
		</p>
	</div>

	<div data-settings-section id="about">
		<?php
			$plugins_data = array(
				array(
					'img'  => trailingslashit( MFM_BASE_URL ) . 'assets/img/wp-security-audit-log-img.jpg',
					'desc' => __( 'Keep a log of users and under the hood site activity.', 'website-file-changes-monitor' ),
					'alt'  => 'WP Activity Log', // this is a name and intentionally not translated.
					'link' => 'https://wpactivitylog.com/?utm_source=plugin&utm_medium=link&utm_campaign=mfm',
				),
				array(
					'img'  => trailingslashit( MFM_BASE_URL ) . 'assets/img/wp-2fa.jpg',
					'desc' => __( 'Add an extra layer of security to your login pages with 2FA & require your users to use it.', 'website-file-changes-monitor' ),
					'alt'  => 'WP 2FA', // this is a name and intentionally not translated.
					'link' => 'https://melapress.com/wordpress-2fa/?utm_source=plugin&utm_medium=link&utm_campaign=mfm',
				),
				array(
					'img'  => trailingslashit( MFM_BASE_URL ) . 'assets/img/password-policy-manager.jpeg',
					'desc' => __( 'Boost WordPress security with login & password policies.', 'website-file-changes-monitor' ),
					'alt'  => 'WPassword', // this is a name and intentionally not translated.
					'link' => 'https://melapress.com/wordpress-login-security/?utm_source=plugin&utm_medium=link&utm_campaign=mfm',
				),
			);

			?>
		

		<h2><?php esc_html_e( 'About us', 'website-file-changes-monitor' ); ?></h2>
		<br>

		<div class="mfm-about-hero">
			<div class="mfm-about-logo"><a href="https://melapress.com/?utm_source=plugin&utm_medium=referral&utm_campaign=MFM&utm_content=help+page" target="_blank"><img src="<?php echo esc_url( MFM_BASE_URL . 'assets/img/melapress-logo-horiz.svg' ); ?>" alt="<?php esc_attr_e( 'Melapress', 'website-file-changes-monitor' ); ?>"></a></div>
			<p><?php /* Translators: 1. WP plugins hyperlink 2. Contact form hyperlink */ printf( esc_html__( 'The WP File Changes Monitor plugin is developed by Melapress, developers of %1$s. If you would like to get in touch with us, please use our %2$s.', 'website-file-changes-monitor' ), '<a href="https://melapress.com/?utm_source=plugin&utm_medium=link&utm_campaign=mfm" target="_blank">' . esc_html__( 'high-quality niche WordPress security and admin plugins', 'website-file-changes-monitor' ) . '</a>', '<a href="https://melapress.com/contact/?utm_source=plugin&utm_medium=link&utm_campaign=mfm" target="_blank">' . esc_html__( 'contact form', 'website-file-changes-monitor' ) . '</a>' ); ?></p>
			</div>
		<br>
		
		<h3><?php esc_html_e( 'Our WordPress Plugins', 'website-file-changes-monitor' ); ?></h3>
		<div class="our-wordpress-plugins full">
			<?php foreach ( $plugins_data as $data ) : ?>
				<div class="plugin-box">
					<div class="plugin-img">
						<img src="<?php echo esc_url( $data['img'] ); ?>" alt="<?php echo esc_attr( $data['alt'] ); ?>">
					</div>
					<div class="plugin-desc">
						<p><?php echo esc_html( $data['desc'] ); ?></p>
						<div class="cta-btn">
							<a href="<?php echo esc_url( $data['link'] ); ?>"  class="button" target="_blank"><?php esc_html_e( 'LEARN MORE', 'website-file-changes-monitor' ); ?></a>
						</div>
					</div>
				</div>
			<?php endforeach; ?>
		</div>                
	</div>

	<div data-settings-section id="system-info">
	<h2><?php esc_html_e( 'System Information', 'website-file-changes-monitor' ); ?></h2>
		<p>
			<textarea id="mfm-system-info-textarea" readonly="readonly" onclick="this.focus(); this.select()"><?php echo esc_html( \MFM\Helpers\Settings_Helper::get_system_info() ); ?></textarea>			
		</p>
		<a href="#" class="button button-primary" id="mfm-download-sysinfo"><?php echo esc_html__( 'Download System Info File', 'website-file-changes-monitor' ); ?></a>
		<?php if ( 'yes' === \MFM\Helpers\Settings_Helper::get_setting( 'debug-logging-enabled', 'no' ) ) { ?>
			<?php
			$upload_dir = wp_upload_dir();
			?>
		<a href="<?php echo esc_url( trailingslashit( $upload_dir['baseurl'] ) . trailingslashit( MFM_LOGS_DIR ) . 'mfm-debug.log' ); ?>" class="button button-primary" id="mfm-download-logs" target="_blank"><?php echo esc_html__( 'Download MFM Debug logs', 'website-file-changes-monitor' ); ?></a>
		<?php } ?>
		<script type="text/javascript">
		/**
		 * Create and download a temporary file.
		 *
		 * @param {string} filename - File name.
		 * @param {string} text - File content.
		 */
		function download( filename, text ) {
			// Create temporary element.
			var element = document.createElement( 'a' );
			element.setAttribute( 'href', 'data:text/plain;charset=utf-8,' + encodeURIComponent( text ) );
			element.setAttribute( 'download', filename );

			// Set the element to not display.
			element.style.display = 'none';
			document.body.appendChild( element );

			// Simlate click on the element.
			element.click();

			// Remove temporary element.
			document.body.removeChild( element );
		}

		window.addEventListener( 'load', function() {
			document.getElementById('mfm-download-sysinfo').addEventListener( 'click', function() {
				download( 'mfm-system-info.txt', jQuery( '#mfm-system-info-textarea' ).val() );
			});
		});
		</script>
	</div>       
</div>
