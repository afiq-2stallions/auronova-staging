<?php
/**
 * UAEL SVG Animator.
 *
 * @package UAEL
 */

namespace UltimateElementor\Modules\SvgAnimator\Widgets;

// Elementor Classes.
use Elementor\Controls_Manager;
use Elementor\Core\Kits\Documents\Tabs\Global_Colors;
use Elementor\Icons_Manager;

// UltimateElementor Classes.
use UltimateElementor\Base\Common_Widget;

if ( ! defined( 'ABSPATH' ) ) {
	exit;   // Exit if accessed directly.
}

/**
 * Class SVG_Animator.
 */
class SVG_Animator extends Common_Widget {


	/**
	 * Retrieve SVG Animator Widget name.
	 *
	 * @since 1.41.0
	 * @access public
	 *
	 * @return string Widget name.
	 */
	public function get_name() {
		return parent::get_widget_slug( 'SVG_Animator' );
	}

	/**
	 * Retrieve SVG Animator Widget title.
	 *
	 * @since 1.41.0
	 * @access public
	 *
	 * @return string Widget title.
	 */
	public function get_title() {
		return parent::get_widget_title( 'SVG_Animator' );
	}

	/**
	 * Retrieve SVG Animator Widget icon.
	 *
	 * @since 1.41.0
	 * @access public
	 *
	 * @return string Widget icon.
	 */
	public function get_icon() {
		return parent::get_widget_icon( 'SVG_Animator' );
	}

	/**
	 * Retrieve Widget Keywords.
	 *
	 * @since 1.41.0
	 * @access public
	 *
	 * @return string Widget keywords.
	 */
	public function get_keywords() {
		return parent::get_widget_keywords( 'SVG_Animator' );
	}

	/**
	 * Retrieve the list of styles needed for SVG Animator.
	 *
	 * Used to set styles dependencies required to run the widget.
	 *
	 * @since 1.41.0
	 * @access public
	 *
	 * @return array Widget styles dependencies.
	 */
	public function get_style_depends() {
		return array( 'uael-svg-animator' );
	}

	/**
	 * Retrieve the list of scripts the SVG Animator widget depended on.
	 *
	 * Used to set scripts dependencies required to run the widget.
	 *
	 * @since 1.41.0
	 * @access public
	 *
	 * @return array Widget scripts dependencies.
	 */
	public function get_script_depends() {
		return array( 'uael-svg-animator' );
	}

	/**
	 * Register SVG Animator controls.
	 *
	 * @since 1.41.0
	 * @access protected
	 */
	protected function register_controls() {

		// Content Tab.
		$this->register_svg_content_controls();
		$this->register_animation_settings_controls();

		// Style Tab.
		$this->register_svg_styling_controls();
	}

	/**
	 * Register SVG Content Controls.
	 *
	 * @since 1.41.0
	 * @access protected
	 */
	protected function register_svg_content_controls() {

		$this->start_controls_section(
			'svg_content',
			array(
				'label' => __( 'General', 'uael' ),
			)
		);

		$this->add_control(
			'svg_icon',
			array(
				'label'       => __( 'Choose SVG Icon', 'uael' ),
				'type'        => Controls_Manager::ICONS,
				'default'     => array(
					'value'   => 'fas fa-rocket',
					'library' => 'fa-solid',
				),
				'description' => __( 'Choose an SVG from the available icon libraries for animation. Most icon libraries provide SVG-based icons perfect for path animation.', 'uael' ),
			)
		);

		$this->add_control(
			'svg_file_note',
			array(
				'type'            => Controls_Manager::RAW_HTML,
				'raw'             => '<p><strong>' . __( 'Important:', 'uael' ) . '</strong> ' . __( 'The SVG Animator works best with SVG icons that have stroke-based paths rather than filled shapes. For optimal results, choose icons with clean, simple outlines from the icon libraries.', 'uael' ) . '</p>',
				'content_classes' => 'uael-editor-doc',
			)
		);

		$this->end_controls_section();

		// Fill Settings Section.
		$this->start_controls_section(
			'fill_settings',
			array(
				'label' => __( 'Fill Settings', 'uael' ),
			)
		);

		$this->add_control(
			'fill_mode',
			array(
				'label'   => __( 'Fill Mode', 'uael' ),
				'type'    => Controls_Manager::SELECT,
				'options' => array(
					'none'   => __( 'No Fill', 'uael' ),
					'before' => __( 'Before Stroke', 'uael' ),
					'after'  => __( 'After Stroke', 'uael' ),
					'always' => __( 'Always Visible', 'uael' ),
				),
				'default' => 'none',
			)
		);

		$this->add_control(
			'fill_color',
			array(
				'label'       => __( 'Fill Color', 'uael' ),
				'type'        => Controls_Manager::COLOR,
				'default'     => '',
				'condition'   => array(
					'fill_mode!' => 'none',
				),
				'selectors'   => array(
					'{{WRAPPER}} .uael-svg-container svg path, {{WRAPPER}} .uael-svg-container svg circle, {{WRAPPER}} .uael-svg-container svg rect, {{WRAPPER}} .uael-svg-container svg line, {{WRAPPER}} .uael-svg-container svg polyline' => 'fill: {{VALUE}};',
				),
				'description' => __( 'Choose the color to fill the SVG paths during animation.', 'uael' ),
			)
		);

		$this->add_control(
			'fill_duration',
			array(
				'label'       => __( 'Fill Duration (seconds)', 'uael' ),
				'type'        => Controls_Manager::SLIDER,
				'range'       => array(
					'px' => array(
						'min'  => 0.1,
						'max'  => 10,
						'step' => 0.1,
					),
				),
				'default'     => array(
					'size' => 1,
				),
				'condition'   => array(
					'fill_mode!' => 'none',
				),
				'description' => __( 'How long the fill animation should take.', 'uael' ),
			)
		);

		$this->end_controls_section();
	}

	/**
	 * Register Animation Settings Controls.
	 *
	 * @since 1.41.0
	 * @access protected
	 */
	protected function register_animation_settings_controls() {

		$this->start_controls_section(
			'animation_settings',
			array(
				'label' => __( 'Animation Settings', 'uael' ),
			)
		);

		$this->add_control(
			'lazy_load',
			array(
				'label'        => __( 'Lazy Load', 'uael' ),
				'type'         => Controls_Manager::SWITCHER,
				'label_on'     => __( 'Yes', 'uael' ),
				'label_off'    => __( 'No', 'uael' ),
				'return_value' => 'yes',
				'default'      => 'no',
				'description'  => __( 'Load animation only when SVG enters viewport for better performance.', 'uael' ),
			)
		);

		$this->add_control(
			'advanced_animation_settings',
			array(
				'label'        => __( 'Advanced Animation', 'uael' ),
				'type'         => Controls_Manager::SWITCHER,
				'label_on'     => __( 'Show', 'uael' ),
				'label_off'    => __( 'Hide', 'uael' ),
				'return_value' => 'yes',
				'default'      => 'no',
				'description'  => __( 'Enable to access advanced animation options.', 'uael' ),
			)
		);

		$this->add_control(
			'animation_type',
			array(
				'label'       => __( 'Animation', 'uael' ),
				'type'        => Controls_Manager::SELECT,
				'options'     => array(
					'sync'       => __( 'All Together', 'uael' ),
					'delayed'    => __( 'Slowed', 'uael' ),
					'one-by-one' => __( 'One by One', 'uael' ),
				),
				'default'     => 'sync',
				'condition'   => array(
					'advanced_animation_settings' => 'yes',
				),
				'description' => __( 'Choose how the SVG paths should animate: All Together, Slowed, or One by One (sequential).', 'uael' ),
			)
		);

		$this->add_control(
			'animation_duration',
			array(
				'label'       => __( 'Animation Duration (seconds)', 'uael' ),
				'type'        => Controls_Manager::NUMBER,
				'min'         => 0.1,
				'max'         => 20,
				'step'        => 0.1,
				'default'     => 3,
				'condition'   => array(
					'advanced_animation_settings' => 'yes',
				),
				'description' => __( 'How long the animation should take to complete.', 'uael' ),
			)
		);

		$this->add_control(
			'animation_trigger',
			array(
				'label'       => __( 'Start Trigger', 'uael' ),
				'type'        => Controls_Manager::SELECT,
				'options'     => array(
					'auto'     => __( 'On Page Load', 'uael' ),
					'viewport' => __( 'On Scroll Into View', 'uael' ),
					'hover'    => __( 'On Hover', 'uael' ),
					'click'    => __( 'On Click', 'uael' ),
					'delay'    => __( 'After Delay', 'uael' ),
				),
				'default'     => 'viewport',
				'condition'   => array(
					'advanced_animation_settings' => 'yes',
				),
				'description' => __( 'Choose when the animation should start.', 'uael' ),
			)
		);

		$this->add_control(
			'animation_delay',
			array(
				'label'       => __( 'Start Delay (seconds)', 'uael' ),
				'type'        => Controls_Manager::NUMBER,
				'min'         => 0,
				'max'         => 30,
				'step'        => 0.1,
				'default'     => 2,
				'condition'   => array(
					'advanced_animation_settings' => 'yes',
					'animation_trigger'           => 'delay',
				),
				'description' => __( 'Delay before animation starts (only for "After Delay" trigger).', 'uael' ),
			)
		);

		$this->add_control(
			'auto_start_animation',
			array(
				'label'        => __( 'Auto Start Animation', 'uael' ),
				'type'         => Controls_Manager::SWITCHER,
				'label_on'     => __( 'Yes', 'uael' ),
				'label_off'    => __( 'No', 'uael' ),
				'return_value' => 'yes',
				'default'      => 'yes',
				'condition'    => array(
					'advanced_animation_settings' => 'yes',
				),
				'description'  => __( 'Start animation automatically based on the trigger setting.', 'uael' ),
			)
		);

		$this->add_control(
			'replay_on_click',
			array(
				'label'        => __( 'Replay Animation On Click', 'uael' ),
				'type'         => Controls_Manager::SWITCHER,
				'label_on'     => __( 'Yes', 'uael' ),
				'label_off'    => __( 'No', 'uael' ),
				'return_value' => 'yes',
				'default'      => 'no',
				'condition'    => array(
					'advanced_animation_settings' => 'yes',
				),
				'description'  => __( 'Allow users to replay the animation by clicking on the SVG.', 'uael' ),
			)
		);

		$this->add_control(
			'looping',
			array(
				'label'       => __( 'Looping', 'uael' ),
				'type'        => Controls_Manager::SELECT,
				'options'     => array(
					'none'     => __( 'No Loop', 'uael' ),
					'infinite' => __( 'Loop Forever', 'uael' ),
					'count'    => __( 'Loop Count', 'uael' ),
				),
				'default'     => 'none',
				'condition'   => array(
					'advanced_animation_settings' => 'yes',
				),
				'description' => __( 'Set the animation to repeat.', 'uael' ),
			)
		);

		$this->add_control(
			'loop_count',
			array(
				'label'       => __( 'Loop Count', 'uael' ),
				'type'        => Controls_Manager::NUMBER,
				'min'         => 2,
				'max'         => 100,
				'default'     => 3,
				'condition'   => array(
					'advanced_animation_settings' => 'yes',
					'looping'                     => 'count',
				),
				'description' => __( 'Number of times to repeat the animation.', 'uael' ),
			)
		);

		$this->add_control(
			'direction',
			array(
				'label'       => __( 'Direction', 'uael' ),
				'type'        => Controls_Manager::SELECT,
				'options'     => array(
					'forward'  => __( 'Forward (Start to End)', 'uael' ),
					'backward' => __( 'Backward (End to Start)', 'uael' ),
				),
				'default'     => 'forward',
				'condition'   => array(
					'advanced_animation_settings' => 'yes',
				),
				'description' => __( 'Animation direction for drawing paths.', 'uael' ),
			)
		);

		$this->add_control(
			'path_timing_function',
			array(
				'label'       => __( 'Motion', 'uael' ),
				'type'        => Controls_Manager::SELECT,
				'options'     => array(
					'linear'   => __( 'Linear', 'uael' ),
					'ease'     => __( 'Ease', 'uael' ),
					'ease-in'  => __( 'Ease In', 'uael' ),
					'ease-out' => __( 'Ease Out', 'uael' ),
					'bounce'   => __( 'Bounce', 'uael' ),
				),
				'default'     => 'ease-out',
				'condition'   => array(
					'advanced_animation_settings' => 'yes',
				),
				'description' => __( 'The timing function for path animations.', 'uael' ),
			)
		);

		$this->add_control(
			'stagger_delay',
			array(
				'label'       => __( 'Stagger Delay (ms)', 'uael' ),
				'type'        => Controls_Manager::SLIDER,
				'range'       => array(
					'px' => array(
						'min' => 0,
						'max' => 2000,
					),
				),
				'default'     => array(
					'size' => 100,
				),
				'condition'   => array(
					'advanced_animation_settings' => 'yes',
					'animation_type'              => array( 'delayed', 'one-by-one' ),
				),
				'description' => __( 'Delay between each path animation for staggered effects.', 'uael' ),
			)
		);

		$this->end_controls_section();
	}


	/**
	 * Register SVG Styling Controls.
	 *
	 * @since 1.41.0
	 * @access protected
	 */
	protected function register_svg_styling_controls() {

		$this->start_controls_section(
			'svg_styling',
			array(
				'label' => __( 'Styles', 'uael' ),
				'tab'   => Controls_Manager::TAB_STYLE,
			)
		);

		$this->add_responsive_control(
			'svg_size',
			array(
				'label'          => __( 'Size', 'uael' ),
				'type'           => Controls_Manager::SLIDER,
				'size_units'     => array( 'px', '%', 'em', 'rem', 'vw' ),
				'range'          => array(
					'px' => array(
						'min' => 0,
						'max' => 2000,
					),
					'%'  => array(
						'min' => 0,
						'max' => 100,
					),
				),
				'default'        => array(
					'unit' => 'px',
					'size' => 300,
				),
				'tablet_default' => array(
					'unit' => 'px',
					'size' => 250,
				),
				'mobile_default' => array(
					'unit' => 'px',
					'size' => 200,
				),
				'selectors'      => array(
					'{{WRAPPER}} .uael-svg-container svg' => 'width: {{SIZE}}{{UNIT}}; height: {{SIZE}}{{UNIT}};',
				),
			)
		);

		$this->add_responsive_control(
			'svg_alignment',
			array(
				'label'     => __( 'Alignment', 'uael' ),
				'type'      => Controls_Manager::CHOOSE,
				'options'   => array(
					'left'   => array(
						'title' => __( 'Left', 'uael' ),
						'icon'  => 'eicon-text-align-left',
					),
					'center' => array(
						'title' => __( 'Center', 'uael' ),
						'icon'  => 'eicon-text-align-center',
					),
					'right'  => array(
						'title' => __( 'Right', 'uael' ),
						'icon'  => 'eicon-text-align-right',
					),
				),
				'default'   => 'center',
				'selectors' => array(
					'{{WRAPPER}} .uael-svg-animator' => 'text-align: {{VALUE}};',
				),
			)
		);

		$this->add_control(
			'stroke_width',
			array(
				'label'       => __( 'Stroke Width', 'uael' ),
				'type'        => Controls_Manager::NUMBER,
				'min'         => 0.1,
				'max'         => 50,
				'step'        => 0.1,
				'default'     => 1,
				'selectors'   => array(
					'{{WRAPPER}} .uael-svg-container svg path, {{WRAPPER}} .uael-svg-container svg circle, {{WRAPPER}} .uael-svg-container svg rect, {{WRAPPER}} .uael-svg-container svg line, {{WRAPPER}} .uael-svg-container svg polyline' => 'stroke-width: {{VALUE}}px;',
				),
				'description' => __( 'Control the thickness of the SVG outline.', 'uael' ),
			)
		);

		$this->add_control(
			'stroke_color',
			array(
				'label'     => __( 'Stroke Color', 'uael' ),
				'type'      => Controls_Manager::COLOR,
				'global'    => array(
					'default' => Global_Colors::COLOR_PRIMARY,
				),
				'default'   => '#333333',
				'selectors' => array(
					'{{WRAPPER}} .uael-svg-container svg path, {{WRAPPER}} .uael-svg-container svg circle, {{WRAPPER}} .uael-svg-container svg rect, {{WRAPPER}} .uael-svg-container svg line, {{WRAPPER}} .uael-svg-container svg polyline' => 'stroke: {{VALUE}};',
				),
			)
		);

		$this->end_controls_section();
	}


	/**
	 * Render SVG Animator widget output on the frontend.
	 *
	 * Written in PHP and used to generate the final HTML.
	 *
	 * @since 1.41.0
	 * @access protected
	 */
	protected function render() {

		$settings = $this->get_settings_for_display();

		// Check if we have an SVG icon selected.
		if ( empty( $settings['svg_icon'] ) || empty( $settings['svg_icon']['value'] ) ) {
			echo '<div class="uael-svg-placeholder" style="text-align: center; padding: 20px; border: 2px dashed #ccc; color: #666;">';
			echo '<p>' . esc_html__( 'Please choose an SVG icon from the icon library in the widget settings.', 'uael' ) . '</p>';
			echo '</div>';
			return;
		}

		// Render the SVG icon.
		$this->render_icon_svg( $settings );
	}


	/**
	 * Convert Font Awesome icon to SVG format for UAEL SVG Animator.
	 *
	 * @since 1.41.0
	 * @access public
	 * @static
	 *
	 * @param array $icon Elementor icon data array.
	 * @param array $attributes Optional SVG element attributes.
	 * @return string Generated SVG HTML markup.
	 */
	public static function uael_get_svg_by_icon( $icon, $attributes = array() ) {
		if ( empty( $icon ) || empty( $icon['value'] ) || empty( $icon['library'] ) ) {
			return '';
		}

		$uael_svg_output = '';

		$fa_icon_name   = str_replace( array( 'fas fa-', 'fab fa-', 'far fa-' ), '', $icon['value'] );
		$fa_library     = str_replace( 'fa-', '', $icon['library'] );
		$icon_file_path = UAEL_DIR . "assets/lib/uael-svg-icons/{$fa_library}.json";
		
		if ( ! file_exists( $icon_file_path ) ) {
			return '';
		}
		
		$uael_icon_data = file_get_contents( $icon_file_path ); // phpcs:ignore WordPressVIPMinimum.Performance.FetchingRemoteData.FileGetContentsUnknown
		$uael_icon_data = json_decode( $uael_icon_data, true );
		$uael_css_class = str_replace( ' ', '-', $icon['value'] );

		if ( empty( $uael_icon_data['icons'][ $fa_icon_name ] ) ) {
			return $uael_svg_output;
		}

		$uael_icon_info   = $uael_icon_data['icons'][ $fa_icon_name ];
		$uael_viewbox     = "0 0 {$uael_icon_info[0]} {$uael_icon_info[1]}";
		$uael_svg_output .= '<svg ';

		$uael_fill_color = '';
		if ( ! empty( $attributes ) ) {
			$uael_fill_color = $attributes['fill'] ?? '';
			unset( $attributes['fill'] );
			foreach ( $attributes as $attr_key => $attr_value ) {
				$uael_svg_output .= $attr_value ? "{$attr_key}='{$attr_value}' " : '';
			}
		}
		$uael_svg_output .= " class='uael-fa-svg--{$uael_css_class} uael-svg-icon' aria-hidden='true' role='img' xmlns='http://www.w3.org/2000/svg' viewBox='{$uael_viewbox}'>";
		$uael_svg_output .= "<path fill='{$uael_fill_color}' d='{$uael_icon_info[4]}'></path>";
		$uael_svg_output .= '</svg>';

		return $uael_svg_output;
	}

	/**
	 * Render icon-based SVG content.
	 *
	 * @since 1.41.0
	 * @access protected
	 *
	 * @param array $settings Widget settings.
	 */
	protected function render_icon_svg( $settings ) {
		
		// Build data attributes for JavaScript using Elementor's recommended approach.
		$this->add_render_attribute( 'svg-animator', 'class', 'uael-svg-animator' );
		$this->add_render_attribute( 'svg-animator', 'data-animation-type', $settings['animation_type'] ?? 'sync' );
		$this->add_render_attribute( 'svg-animator', 'data-animation-trigger', $settings['animation_trigger'] ?? 'viewport' );
		$this->add_render_attribute( 'svg-animator', 'data-animation-duration', absint( $settings['animation_duration'] ?? 3 ) );
		$this->add_render_attribute( 'svg-animator', 'data-animation-delay', absint( $settings['animation_delay'] ?? 0 ) );
		$this->add_render_attribute( 'svg-animator', 'data-path-timing-function', $settings['path_timing_function'] ?? 'ease-out' );
		$this->add_render_attribute( 'svg-animator', 'data-auto-start', $settings['auto_start_animation'] ?? 'yes' );
		$this->add_render_attribute( 'svg-animator', 'data-replay-on-click', $settings['replay_on_click'] ?? 'no' );
		$this->add_render_attribute( 'svg-animator', 'data-looping', $settings['looping'] ?? 'none' );
		$this->add_render_attribute( 'svg-animator', 'data-loop-count', max( 1, absint( $settings['loop_count'] ?? 1 ) ) );
		$this->add_render_attribute( 'svg-animator', 'data-direction', $settings['direction'] ?? 'forward' );
		$this->add_render_attribute( 'svg-animator', 'data-fill-mode', $settings['fill_mode'] ?? 'none' );
		$this->add_render_attribute( 'svg-animator', 'data-fill-duration', max( 0.1, floatval( $settings['fill_duration']['size'] ?? 1 ) ) );
		$this->add_render_attribute( 'svg-animator', 'data-stagger-delay', max( 0, absint( $settings['stagger_delay']['size'] ?? 100 ) ) );
		$this->add_render_attribute( 'svg-animator', 'data-lazy-load', $settings['lazy_load'] ?? 'no' );

		?>
		<div <?php $this->print_render_attribute_string( 'svg-animator' ); ?>>
			<div class="uael-svg-container">
				<?php
				// Check if this is a Font Awesome icon and try to convert to SVG.
				$is_fa_icon = isset( $settings['svg_icon']['library'] ) && 
					strpos( $settings['svg_icon']['library'], 'fa-' ) === 0;
				if ( $is_fa_icon ) {
					$svg_content = self::uael_get_svg_by_icon( $settings['svg_icon'], array( 'class' => 'uael-svg-element' ) );
					if ( ! empty( $svg_content ) ) {
						echo $svg_content; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
					} else {
						// Fallback to default icon rendering.
						Icons_Manager::render_icon(
							$settings['svg_icon'],
							array(
								'aria-hidden' => 'true',
								'class'       => 'uael-svg-element',
							)
						);
					}
				} else {
					// Use default icon rendering for non-FA icons.
					Icons_Manager::render_icon(
						$settings['svg_icon'],
						array(
							'aria-hidden' => 'true',
							'class'       => 'uael-svg-element',
						)
					);
				}
				?>
			</div>
		</div>
		<?php
	}

}
