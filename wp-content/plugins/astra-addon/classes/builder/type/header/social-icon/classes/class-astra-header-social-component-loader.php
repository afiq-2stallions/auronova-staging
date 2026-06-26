<?php
/**
 * Social Styling Loader for Astra theme.
 *
 * @package     Astra Builder
 * @link        https://www.brainstormforce.com
 * @since       Astra 3.0.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

/**
 * Customizer Initialization
 *
 * @since 3.0.0
 */
// @codingStandardsIgnoreStart
class Astra_Header_Social_Component_Loader {
 // phpcs:ignore WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedClassFound
	// @codingStandardsIgnoreEnd

	/**
	 * Constructor
	 *
	 * @since 3.0.0
	 */
	public function __construct() {
		add_filter( 'astra_theme_defaults', array( $this, 'theme_defaults' ) );
	}

	/**
	 * Default customizer configs.
	 *
	 * @param  array $defaults  Astra options default value array.
	 *
	 * @since 3.0.0
	 */
	public function theme_defaults( $defaults ) {

		$num_of_header_social_icons = astra_addon_builder_helper()->num_of_header_social_icons;

		// Divider header defaults.
		for ( $index = 1; $index <= $num_of_header_social_icons; $index++ ) {

			$defaults[ 'header-social-' . $index . '-stack' ] = 'none';
		}

		return $defaults;
	}

}

/**
 *  Kicking this off by creating the object of the class.
 */
new Astra_Header_Social_Component_Loader();
