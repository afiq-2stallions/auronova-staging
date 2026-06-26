<?php
/**
 * Button Styling Loader for Astra theme.
 *
 * @package     Astra Builder
 * @link        https://www.brainstormforce.com
 * @since       Astra 3.1.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

/**
 * Customizer Initialization
 *
 * @since 3.1.0
 */
// @codingStandardsIgnoreStart
class Astra_Ext_Footer_Button_Component_Loader {
	// @codingStandardsIgnoreEnd

	/**
	 * Constructor
	 *
	 * @since 3.1.0
	 */
	public function __construct() {
		add_filter( 'astra_theme_defaults', array( $this, 'theme_defaults' ) );
	}

	/**
	 * Default customizer configs.
	 *
	 * @param  array $defaults  Astra options default value array.
	 *
	 * @since 3.1.0
	 */
	public function theme_defaults( $defaults ) {
		// Button footer defaults.
		$component_limit         = astra_addon_builder_helper()->component_limit;
		$builder_button_stylings = is_callable( 'Astra_Dynamic_CSS::astra_4_6_4_compatibility' ) ? Astra_Dynamic_CSS::astra_4_6_4_compatibility() : false;
		for ( $index = 1; $index <= $component_limit; $index++ ) {

			$_prefix = 'button' . $index;

			$defaults[ 'footer-' . $_prefix . '-size' ] = $builder_button_stylings ? 'default' : 'sm';

			$defaults[ 'footer-' . $_prefix . '-box-shadow-control' ]  = array(
				'x'      => '0',
				'y'      => '0',
				'blur'   => '0',
				'spread' => '0',
			);
			$defaults[ 'footer-' . $_prefix . '-box-shadow-color' ]    = 'rgba(0,0,0,0.1)';
			$defaults[ 'footer-' . $_prefix . '-box-shadow-position' ] = 'outline';

		}

		return $defaults;
	}

}

/**
 *  Kicking this off by creating the object of the class.
 */
new Astra_Ext_Footer_Button_Component_Loader();
