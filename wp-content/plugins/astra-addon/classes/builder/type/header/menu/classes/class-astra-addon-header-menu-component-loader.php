<?php
/**
 * Menu Styling Loader for Astra Addon.
 *
 * @package     Astra Addon
 * @link        https://www.brainstormforce.com
 * @since       Astra 3.3.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

/**
 * Customizer Initialization
 *
 * @since 3.3.0
 */
class Astra_Addon_Header_Menu_Component_Loader {
	/**
	 * Constructor
	 *
	 * @since 3.3.0
	 */
	public function __construct() {
		add_filter( 'astra_theme_defaults', array( $this, 'theme_defaults' ) );
	}

	/**
	 * Default customizer configs.
	 *
	 * @param  array $defaults  Astra options default value array.
	 *
	 * @since 3.3.0
	 */
	public function theme_defaults( $defaults ) {

		// Menu - Box Shadow defaults.
		$component_limit = astra_addon_builder_helper()->component_limit;

		for ( $index = 1; $index <= $component_limit; $index++ ) {

			$_prefix = 'menu' . $index;

			$defaults[ 'header-' . $_prefix . '-box-shadow-control' ]  = array(
				'x'      => '0',
				'y'      => '4',
				'blur'   => '10',
				'spread' => '-2',
			);
			$defaults[ 'header-' . $_prefix . '-box-shadow-color' ]    = 'rgba(0,0,0,0.1)';
			$defaults[ 'header-' . $_prefix . '-box-shadow-position' ] = 'outline';
		}

		return $defaults;
	}

}

/**
 *  Kicking this off by creating the object of the class.
 */
new Astra_Addon_Header_Menu_Component_Loader();
