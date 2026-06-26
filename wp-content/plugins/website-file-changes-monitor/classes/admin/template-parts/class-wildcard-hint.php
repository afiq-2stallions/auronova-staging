<?php
/**
 * Wildcard hint component for directory exclusion settings.
 *
 * @package MFM
 * @since 2.3.0
 */

declare(strict_types=1);

namespace MFM\Admin\Template_Parts;

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Renders the wildcard hint for directory exclusion/ignore settings fields.
 *
 * @since 2.3.0
 */
class Wildcard_Hint {

	/**
	 * Render the wildcard hint markup.
	 *
	 * @return void
	 *
	 * @since 2.3.0
	 */
	public static function render() {
		?>
		<p class="description"><?php \esc_html_e( 'You can use wildcards to match multiple folders without listing each one. * matches any name within one folder level. ** matches across any number of levels. ? matches any single character.', 'website-file-changes-monitor' ); ?></p>
		<ul class="description">
			<li><code>wp-content/uploads/2024</code> <?php \esc_html_e( 'Exclude the 2024 folder and everything inside it.', 'website-file-changes-monitor' ); ?></li>
			<li><code>wp-content/uploads/*/cache</code> <?php \esc_html_e( 'Exclude any folder named "cache" sitting directly inside a subfolder of uploads (e.g. uploads/2024/cache, uploads/2025/cache).', 'website-file-changes-monitor' ); ?></li>
			<li><code>wp-content/uploads/sites/*/cache</code> <?php \esc_html_e( 'Multisite: exclude the cache folder for each site without knowing the site IDs.', 'website-file-changes-monitor' ); ?></li>
			<li><code>wp-content/uploads/**/cache</code> <?php \esc_html_e( 'Exclude any folder named "cache" found at any depth under uploads.', 'website-file-changes-monitor' ); ?></li>
		</ul>
		<?php
	}
}
