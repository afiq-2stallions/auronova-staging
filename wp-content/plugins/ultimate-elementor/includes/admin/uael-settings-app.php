<?php
/**
 * Single page settings page
 */

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

use UltimateElementor\Classes\UAEL_Helper;

$language_list = UAEL_Helper::get_google_map_languages();

?>
<script type="text/javascript">
	window.uaeLanguagesData = <?php echo wp_json_encode( $language_list ); ?>;
</script>

<div id="uae-settings-app" class="uae-settings-app"></div>