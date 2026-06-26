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
class Astra_Footer_Divider_Component_Loader {
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
		// Divider Footer defaults.
		$num_of_footer_divider = astra_addon_builder_helper()->num_of_footer_divider;
		for ( $index = 1; $index <= $num_of_footer_divider; $index++ ) {

			$defaults[ 'footer-divider-' . $index . '-layout' ] = 'horizontal';
			$defaults[ 'footer-divider-' . $index . '-style' ]  = 'solid';
			$defaults[ 'footer-divider-' . $index . '-color' ]  = '';

			$defaults[ 'footer-divider-' . $index . '-size' ] = array(
				'desktop' => '50',
				'tablet'  => '50',
				'mobile'  => '50',
			);

			$defaults[ 'footer-vertical-divider-' . $index . '-size' ] = array(
				'desktop' => '50',
				'tablet'  => '50',
				'mobile'  => '50',
			);

			$defaults[ 'footer-divider-' . $index . '-thickness' ] = array(
				'desktop' => '1',
				'tablet'  => '1',
				'mobile'  => '1',
			);

			$defaults[ 'footer-divider-' . $index . '-alignment' ] = array(
				'desktop' => 'center',
				'tablet'  => 'center',
				'mobile'  => 'center',
			);
		}

		return $defaults;
	}

}

/**
 *  Kicking this off by creating the object of the class.
 */
new Astra_Footer_Divider_Component_Loader();
