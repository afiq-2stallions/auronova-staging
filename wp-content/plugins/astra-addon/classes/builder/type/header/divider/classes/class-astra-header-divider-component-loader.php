<?php
/**
 * Divider Styling Loader for Astra theme.
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
class Astra_Header_Divider_Component_Loader {
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
		// Divider header defaults.
		$component_limit = astra_addon_builder_helper()->component_limit;
		for ( $index = 1; $index <= $component_limit; $index++ ) {

			$defaults[ 'header-divider-' . $index . '-layout' ] = 'vertical';
			$defaults[ 'header-divider-' . $index . '-style' ]  = 'solid';
			$defaults[ 'header-divider-' . $index . '-color' ]  = '#000000';

			$defaults[ 'header-divider-' . $index . '-size' ] = array(
				'desktop' => '50',
				'tablet'  => '50',
				'mobile'  => '50',
			);

			$defaults[ 'header-horizontal-divider-' . $index . '-size' ] = array(
				'desktop' => '50',
				'tablet'  => '50',
				'mobile'  => '50',
			);

			$defaults[ 'header-divider-' . $index . '-thickness' ] = array(
				'desktop' => '1',
				'tablet'  => '1',
				'mobile'  => '1',
			);
		}

		return $defaults;
	}

}

/**
 *  Kicking this off by creating the object of the class.
 */
new Astra_Header_Divider_Component_Loader();
