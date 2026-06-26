<?php
/**
 * UAEL Sticky Header functionality.
 *
 * @package UAEL
 */

namespace UltimateElementor\Modules\StickyHeader;

use Elementor\Controls_Manager;
use Elementor\Controls_Stack;

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

/**
 * Class Sticky_Header
 *
 * @package UltimateElementor\Modules\StickyHeader
 */
class Sticky_Header {

	/**
	 * Constructor.
	 *
	 * @since 1.40.0
	 */
	public function __construct() {
		$this->init_hooks();
	}

	/**
	 * Initialize hooks.
	 *
	 * @since 1.40.0
	 */
	private function init_hooks() {
		// Ensure settings are passed to frontend.
		add_action( 'elementor/frontend/section/before_render', array( $this, 'before_render' ) );
		add_action( 'elementor/frontend/container/before_render', array( $this, 'before_render' ) );
	}

	/**
	 * Add all controls to the element.
	 *
	 * @since 1.40.0
	 * @param Controls_Stack $element Current element.
	 */
	public function add_controls( Controls_Stack $element ) {
		$this->add_main_controls( $element );
		$this->add_visual_effects_controls( $element );
		$this->add_border_shadow_controls( $element );
		$this->add_behavior_controls( $element );
	}

	/**
	 * Add main sticky header controls.
	 *
	 * @since 1.40.0
	 * @param Controls_Stack $element Current element.
	 */
	private function add_main_controls( Controls_Stack $element ) {
		// Enable sticky header.
		$element->add_control(
			'uae_sticky_header_enable',
			array(
				'label'              => __( 'Enable Sticky Header', 'uael' ),
				'type'               => Controls_Manager::SWITCHER,
				'label_on'           => __( 'Yes', 'uael' ),
				'label_off'          => __( 'No', 'uael' ),
				'return_value'       => 'yes',
				'default'            => '',
				'frontend_available' => true,
				'prefix_class'       => 'uae-sticky-header-',
				'render_type'        => 'none',
			)
		);

		// Device controls.
		$element->add_control(
			'uae_sticky_devices',
			array(
				'label'              => __( 'Enable On', 'uael' ),
				'type'               => Controls_Manager::SELECT2,
				'multiple'           => true,
				'label_block'        => true,
				'default'            => array( 'desktop', 'tablet', 'mobile' ),
				'options'            => array(
					'desktop' => __( 'Desktop', 'uael' ),
					'tablet'  => __( 'Tablet', 'uael' ),
					'mobile'  => __( 'Mobile', 'uael' ),
				),
				'condition'          => array(
					'uae_sticky_header_enable' => 'yes',
				),
				'frontend_available' => true,
				'render_type'        => 'none',
			)
		);

		// Scroll distance.
		$element->add_responsive_control(
			'uae_sticky_scroll_distance',
			array(
				'label'              => __( 'Scroll Distance', 'uael' ),
				'type'               => Controls_Manager::SLIDER,
				'size_units'         => array( 'px', '%' ),
				'range'              => array(
					'px' => array(
						'min' => 0,
						'max' => 500,
					),
					'%'  => array(
						'min' => 0,
						'max' => 100,
					),
				),
				'default'            => array(
					'size' => 100,
					'unit' => 'px',
				),
				'condition'          => array(
					'uae_sticky_header_enable' => 'yes',
				),
				'frontend_available' => true,
				'render_type'        => 'none',
			)
		);
	}

	/**
	 * Add visual effects controls.
	 *
	 * @since 1.40.0
	 * @param Controls_Stack $element Current element.
	 */
	private function add_visual_effects_controls( Controls_Stack $element ) {
		// Visual Effects Heading.
		$element->add_control(
			'uae_sticky_effects_heading',
			array(
				'label'     => __( 'Visual Effects', 'uael' ),
				'type'      => Controls_Manager::HEADING,
				'separator' => 'before',
				'condition' => array(
					'uae_sticky_header_enable' => 'yes',
				),
			)
		);

		// Transparent header.
		$element->add_control(
			'uae_sticky_transparent_enable',
			array(
				'label'              => __( 'Transparent Header', 'uael' ),
				'description'        => __( 'Enable transparent header. If no background color is set below, the header will remain transparent when sticky.', 'uael' ),
				'type'               => Controls_Manager::SWITCHER,
				'label_on'           => __( 'Yes', 'uael' ),
				'label_off'          => __( 'No', 'uael' ),
				'return_value'       => 'yes',
				'default'            => '',
				'frontend_available' => true,
				'prefix_class'       => 'uae-sticky-transparent-',
				'condition'          => array(
					'uae_sticky_header_enable' => 'yes',
				),
			)
		);

		// Transparency level.
		$element->add_control(
			'uae_sticky_transparency_level',
			array(
				'label'              => __( 'Transparency Level', 'uael' ),
				'type'               => Controls_Manager::SLIDER,
				'size_units'         => array( '%' ),
				'range'              => array(
					'%' => array(
						'min' => 0,
						'max' => 100,
					),
				),
				'default'            => array(
					'size' => 100,
					'unit' => '%',
				),
				'condition'          => array(
					'uae_sticky_header_enable'      => 'yes',
					'uae_sticky_transparent_enable' => 'yes',
				),
				'frontend_available' => true,
				'render_type'        => 'none',
			)
		);

		// Background color change.
		$element->add_control(
			'uae_sticky_background_enable',
			array(
				'label'              => __( 'Background Color', 'uael' ),
				'description'        => __( 'Set a background color for the sticky header. Leave disabled to maintain transparency when sticky.', 'uael' ),
				'type'               => Controls_Manager::SWITCHER,
				'label_on'           => __( 'Yes', 'uael' ),
				'label_off'          => __( 'No', 'uael' ),
				'return_value'       => 'yes',
				'default'            => '',
				'frontend_available' => true,
				'condition'          => array(
					'uae_sticky_header_enable' => 'yes',
				),
			)
		);

		// Background type.
		$element->add_control(
			'uae_sticky_background_type',
			array(
				'label'              => __( 'Background Type', 'uael' ),
				'type'               => Controls_Manager::CHOOSE,
				'options'            => array(
					'solid'    => array(
						'title' => __( 'Solid', 'uael' ),
						'icon'  => 'eicon-paint-brush',
					),
					'gradient' => array(
						'title' => __( 'Gradient', 'uael' ),
						'icon'  => 'eicon-barcode',
					),
				),
				'default'            => 'solid',
				'condition'          => array(
					'uae_sticky_header_enable'     => 'yes',
					'uae_sticky_background_enable' => 'yes',
				),
				'frontend_available' => true,
				'render_type'        => 'ui',
			)
		);

		// Solid background color.
		$element->add_control(
			'uae_sticky_background_color',
			array(
				'label'              => __( 'Color', 'uael' ),
				'type'               => Controls_Manager::COLOR,
				'default'            => '#ffffff',
				'condition'          => array(
					'uae_sticky_header_enable'     => 'yes',
					'uae_sticky_background_enable' => 'yes',
					'uae_sticky_background_type'   => 'solid',
				),
				'frontend_available' => true,
				'render_type'        => 'none',
			)
		);

		// Gradient controls.
		$this->add_gradient_controls( $element );
	}

	/**
	 * Add gradient controls.
	 *
	 * @since 1.40.0
	 * @param Controls_Stack $element Current element.
	 */
	private function add_gradient_controls( Controls_Stack $element ) {
		// Gradient color 1.
		$element->add_control(
			'uae_sticky_gradient_color_1',
			array(
				'label'              => __( 'Color 1', 'uael' ),
				'type'               => Controls_Manager::COLOR,
				'default'            => '#ffffff',
				'condition'          => array(
					'uae_sticky_header_enable'     => 'yes',
					'uae_sticky_background_enable' => 'yes',
					'uae_sticky_background_type'   => 'gradient',
				),
				'frontend_available' => true,
				'render_type'        => 'none',
			)
		);

		// Gradient location 1.
		$element->add_control(
			'uae_sticky_gradient_location_1',
			array(
				'label'              => __( 'Location', 'uael' ),
				'type'               => Controls_Manager::SLIDER,
				'size_units'         => array( '%' ),
				'default'            => array(
					'size' => 0,
					'unit' => '%',
				),
				'condition'          => array(
					'uae_sticky_header_enable'     => 'yes',
					'uae_sticky_background_enable' => 'yes',
					'uae_sticky_background_type'   => 'gradient',
				),
				'frontend_available' => true,
			)
		);

		// Gradient color 2.
		$element->add_control(
			'uae_sticky_gradient_color_2',
			array(
				'label'              => __( 'Color 2', 'uael' ),
				'type'               => Controls_Manager::COLOR,
				'default'            => '#f0f0f0',
				'condition'          => array(
					'uae_sticky_header_enable'     => 'yes',
					'uae_sticky_background_enable' => 'yes',
					'uae_sticky_background_type'   => 'gradient',
				),
				'frontend_available' => true,
				'render_type'        => 'none',
			)
		);

		// Gradient location 2.
		$element->add_control(
			'uae_sticky_gradient_location_2',
			array(
				'label'              => __( 'Location', 'uael' ),
				'type'               => Controls_Manager::SLIDER,
				'size_units'         => array( '%' ),
				'default'            => array(
					'size' => 100,
					'unit' => '%',
				),
				'condition'          => array(
					'uae_sticky_header_enable'     => 'yes',
					'uae_sticky_background_enable' => 'yes',
					'uae_sticky_background_type'   => 'gradient',
				),
				'frontend_available' => true,
			)
		);

		// Gradient type.
		$element->add_control(
			'uae_sticky_gradient_type',
			array(
				'label'              => __( 'Gradient Type', 'uael' ),
				'type'               => Controls_Manager::SELECT,
				'options'            => array(
					'linear' => __( 'Linear', 'uael' ),
					'radial' => __( 'Radial', 'uael' ),
				),
				'default'            => 'linear',
				'condition'          => array(
					'uae_sticky_header_enable'     => 'yes',
					'uae_sticky_background_enable' => 'yes',
					'uae_sticky_background_type'   => 'gradient',
				),
				'frontend_available' => true,
			)
		);

		// Gradient angle.
		$element->add_control(
			'uae_sticky_gradient_angle',
			array(
				'label'              => __( 'Angle', 'uael' ),
				'type'               => Controls_Manager::SLIDER,
				'size_units'         => array( 'deg' ),
				'default'            => array(
					'size' => 180,
					'unit' => 'deg',
				),
				'range'              => array(
					'deg' => array(
						'step' => 10,
					),
				),
				'condition'          => array(
					'uae_sticky_header_enable'     => 'yes',
					'uae_sticky_background_enable' => 'yes',
					'uae_sticky_background_type'   => 'gradient',
					'uae_sticky_gradient_type'     => 'linear',
				),
				'frontend_available' => true,
			)
		);
	}

	/**
	 * Add border and shadow controls.
	 *
	 * @since 1.40.0
	 * @param Controls_Stack $element Current element.
	 */
	private function add_border_shadow_controls( Controls_Stack $element ) {
		// Border & Shadow Heading.
		$element->add_control(
			'uae_sticky_border_shadow_heading',
			array(
				'label'     => __( 'Border & Shadow', 'uael' ),
				'type'      => Controls_Manager::HEADING,
				'separator' => 'before',
				'condition' => array(
					'uae_sticky_header_enable' => 'yes',
				),
			)
		);

		// Bottom border.
		$element->add_control(
			'uae_sticky_border_enable',
			array(
				'label'              => __( 'Bottom Border', 'uael' ),
				'type'               => Controls_Manager::SWITCHER,
				'label_on'           => __( 'Yes', 'uael' ),
				'label_off'          => __( 'No', 'uael' ),
				'return_value'       => 'yes',
				'default'            => '',
				'frontend_available' => true,
				'condition'          => array(
					'uae_sticky_header_enable' => 'yes',
				),
			)
		);

		// Border color.
		$element->add_control(
			'uae_sticky_border_color',
			array(
				'label'              => __( 'Border Color', 'uael' ),
				'type'               => Controls_Manager::COLOR,
				'default'            => '#e0e0e0',
				'condition'          => array(
					'uae_sticky_header_enable' => 'yes',
					'uae_sticky_border_enable' => 'yes',
				),
				'frontend_available' => true,
				'render_type'        => 'none',
			)
		);

		// Border thickness.
		$element->add_control(
			'uae_sticky_border_thickness',
			array(
				'label'              => __( 'Border Thickness (px)', 'uael' ),
				'type'               => Controls_Manager::SLIDER,
				'size_units'         => array( 'px' ),
				'range'              => array(
					'px' => array(
						'min' => 0,
						'max' => 10,
					),
				),
				'default'            => array(
					'size' => 1,
					'unit' => 'px',
				),
				'condition'          => array(
					'uae_sticky_header_enable' => 'yes',
					'uae_sticky_border_enable' => 'yes',
				),
				'frontend_available' => true,
			)
		);

		// Drop shadow.
		$element->add_control(
			'uae_sticky_shadow_enable',
			array(
				'label'              => __( 'Drop Shadow', 'uael' ),
				'type'               => Controls_Manager::SWITCHER,
				'label_on'           => __( 'Yes', 'uael' ),
				'label_off'          => __( 'No', 'uael' ),
				'return_value'       => 'yes',
				'default'            => '',
				'frontend_available' => true,
				'condition'          => array(
					'uae_sticky_header_enable' => 'yes',
				),
			)
		);

		// Shadow controls.
		$this->add_shadow_controls( $element );
	}

	/**
	 * Add shadow controls.
	 *
	 * @since 1.40.0
	 * @param Controls_Stack $element Current element.
	 */
	private function add_shadow_controls( Controls_Stack $element ) {
		// Shadow color.
		$element->add_control(
			'uae_sticky_shadow_color',
			array(
				'label'              => __( 'Shadow Color', 'uael' ),
				'type'               => Controls_Manager::COLOR,
				'default'            => 'rgba(0, 0, 0, 0.1)',
				'condition'          => array(
					'uae_sticky_header_enable' => 'yes',
					'uae_sticky_shadow_enable' => 'yes',
				),
				'frontend_available' => true,
				'render_type'        => 'none',
			)
		);

		// Shadow vertical offset.
		$element->add_control(
			'uae_sticky_shadow_vertical',
			array(
				'label'              => __( 'Vertical Offset', 'uael' ),
				'type'               => Controls_Manager::SLIDER,
				'size_units'         => array( 'px' ),
				'range'              => array(
					'px' => array(
						'min' => -20,
						'max' => 20,
					),
				),
				'default'            => array(
					'size' => 0,
					'unit' => 'px',
				),
				'condition'          => array(
					'uae_sticky_header_enable' => 'yes',
					'uae_sticky_shadow_enable' => 'yes',
				),
				'frontend_available' => true,
			)
		);

		// Shadow blur.
		$element->add_control(
			'uae_sticky_shadow_blur',
			array(
				'label'              => __( 'Blur Radius', 'uael' ),
				'type'               => Controls_Manager::SLIDER,
				'size_units'         => array( 'px' ),
				'range'              => array(
					'px' => array(
						'min' => 0,
						'max' => 50,
					),
				),
				'default'            => array(
					'size' => 10,
					'unit' => 'px',
				),
				'condition'          => array(
					'uae_sticky_header_enable' => 'yes',
					'uae_sticky_shadow_enable' => 'yes',
				),
				'frontend_available' => true,
			)
		);

		// Shadow spread.
		$element->add_control(
			'uae_sticky_shadow_spread',
			array(
				'label'              => __( 'Spread', 'uael' ),
				'type'               => Controls_Manager::SLIDER,
				'size_units'         => array( 'px' ),
				'range'              => array(
					'px' => array(
						'min' => -20,
						'max' => 20,
					),
				),
				'default'            => array(
					'size' => 0,
					'unit' => 'px',
				),
				'condition'          => array(
					'uae_sticky_header_enable' => 'yes',
					'uae_sticky_shadow_enable' => 'yes',
				),
				'frontend_available' => true,
			)
		);
	}

	/**
	 * Add behavior controls.
	 *
	 * @since 1.40.0
	 * @param Controls_Stack $element Current element.
	 */
	private function add_behavior_controls( Controls_Stack $element ) {
		// Advanced Behavior Heading.
		$element->add_control(
			'uae_sticky_behavior_heading',
			array(
				'label'     => __( 'Advanced Behavior', 'uael' ),
				'type'      => Controls_Manager::HEADING,
				'separator' => 'before',
				'condition' => array(
					'uae_sticky_header_enable' => 'yes',
				),
			)
		);

		// Hide on scroll down.
		$element->add_control(
			'uae_sticky_hide_on_scroll_down',
			array(
				'label'              => __( 'Hide on Scroll Down', 'uael' ),
				'type'               => Controls_Manager::SWITCHER,
				'label_on'           => __( 'Yes', 'uael' ),
				'label_off'          => __( 'No', 'uael' ),
				'return_value'       => 'yes',
				'default'            => '',
				'frontend_available' => true,
				'condition'          => array(
					'uae_sticky_header_enable' => 'yes',
				),
			)
		);

		// Hide scroll threshold.
		$element->add_responsive_control(
			'uae_sticky_hide_threshold',
			array(
				'label'              => __( 'Hide Threshold', 'uael' ),
				'type'               => Controls_Manager::SLIDER,
				'size_units'         => array( 'px', '%' ),
				'range'              => array(
					'px' => array(
						'min' => 0,
						'max' => 1000,
					),
					'%'  => array(
						'min' => 0,
						'max' => 100,
					),
				),
				'default'            => array(
					'size' => 10,
					'unit' => '%',
				),
				'condition'          => array(
					'uae_sticky_header_enable'       => 'yes',
					'uae_sticky_hide_on_scroll_down' => 'yes',
				),
				'frontend_available' => true,
			)
		);

		// Notice about scroll behavior.
		$element->add_control(
			'uae_sticky_scroll_notice',
			array(
				'type'      => Controls_Manager::RAW_HTML,
				'raw'       => sprintf(
					'<div class="elementor-panel-alert elementor-panel-alert-warning">%s</div>',
					__( 'The header will hide when scrolling down and show when scrolling up.', 'uael' )
				),
				'condition' => array(
					'uae_sticky_header_enable'       => 'yes',
					'uae_sticky_hide_on_scroll_down' => 'yes',
				),
			)
		);
	}

	/**
	 * Before render.
	 *
	 * @since 1.40.0
	 * @param \Elementor\Element_Base $element The element.
	 */
	public function before_render( $element ) {
		if ( ! $element->get_settings( 'uae_sticky_header_enable' ) ) {
			return;
		}

		// Get all sticky settings.
		$settings = array(
			'uae_sticky_header_enable'          => $element->get_settings( 'uae_sticky_header_enable' ),
			'uae_sticky_devices'                => $element->get_settings( 'uae_sticky_devices' ),
			'uae_sticky_scroll_distance'        => $element->get_settings( 'uae_sticky_scroll_distance' ),
			'uae_sticky_scroll_distance_tablet' => $element->get_settings( 'uae_sticky_scroll_distance_tablet' ),
			'uae_sticky_scroll_distance_mobile' => $element->get_settings( 'uae_sticky_scroll_distance_mobile' ),
			'uae_sticky_transparent_enable'     => $element->get_settings( 'uae_sticky_transparent_enable' ),
			'uae_sticky_transparency_level'     => $element->get_settings( 'uae_sticky_transparency_level' ),
			'uae_sticky_background_enable'      => $element->get_settings( 'uae_sticky_background_enable' ),
			'uae_sticky_background_type'        => $element->get_settings( 'uae_sticky_background_type' ),
			'uae_sticky_background_color'       => $element->get_settings( 'uae_sticky_background_color' ),
			'uae_sticky_gradient_color_1'       => $element->get_settings( 'uae_sticky_gradient_color_1' ),
			'uae_sticky_gradient_location_1'    => $element->get_settings( 'uae_sticky_gradient_location_1' ),
			'uae_sticky_gradient_color_2'       => $element->get_settings( 'uae_sticky_gradient_color_2' ),
			'uae_sticky_gradient_location_2'    => $element->get_settings( 'uae_sticky_gradient_location_2' ),
			'uae_sticky_gradient_type'          => $element->get_settings( 'uae_sticky_gradient_type' ),
			'uae_sticky_gradient_angle'         => $element->get_settings( 'uae_sticky_gradient_angle' ),
			'uae_sticky_border_enable'          => $element->get_settings( 'uae_sticky_border_enable' ),
			'uae_sticky_border_color'           => $element->get_settings( 'uae_sticky_border_color' ),
			'uae_sticky_border_thickness'       => $element->get_settings( 'uae_sticky_border_thickness' ),
			'uae_sticky_shadow_enable'          => $element->get_settings( 'uae_sticky_shadow_enable' ),
			'uae_sticky_shadow_color'           => $element->get_settings( 'uae_sticky_shadow_color' ),
			'uae_sticky_shadow_vertical'        => $element->get_settings( 'uae_sticky_shadow_vertical' ),
			'uae_sticky_shadow_blur'            => $element->get_settings( 'uae_sticky_shadow_blur' ),
			'uae_sticky_shadow_spread'          => $element->get_settings( 'uae_sticky_shadow_spread' ),
			'uae_sticky_hide_on_scroll_down'    => $element->get_settings( 'uae_sticky_hide_on_scroll_down' ),
			'uae_sticky_hide_threshold'         => $element->get_settings( 'uae_sticky_hide_threshold' ),
			'uae_sticky_hide_threshold_tablet'  => $element->get_settings( 'uae_sticky_hide_threshold_tablet' ),
			'uae_sticky_hide_threshold_mobile'  => $element->get_settings( 'uae_sticky_hide_threshold_mobile' ),
		);

		// Add settings as data attribute.
		$element->add_render_attribute( '_wrapper', 'data-uae-sticky-settings', wp_json_encode( $settings ) );
	}
}
