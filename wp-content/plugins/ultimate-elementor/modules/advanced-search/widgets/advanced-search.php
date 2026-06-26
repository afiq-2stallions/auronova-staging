<?php
/**
 * UAEL Advanced Search Widget.
 *
 * @package UAEL
 */

namespace UltimateElementor\Modules\AdvancedSearch\Widgets;

use UltimateElementor\Base\Common_Widget;
use UltimateElementor\Classes\UAEL_Helper;
use UltimateElementor\Classes\UAEL_Posts_Helper;

use Elementor\Controls_Manager;
use Elementor\Group_Control_Background;
use Elementor\Group_Control_Border;
use Elementor\Group_Control_Box_Shadow;
use Elementor\Group_Control_Typography;
use Elementor\Core\Kits\Documents\Tabs\Global_Typography;

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

/**
 * Class Advanced_Search.
 */
class Advanced_Search extends Common_Widget {

	/**
	 * Retrieve Advanced Search Widget name.
	 *
	 * @since 1.43.0
	 * @access public
	 *
	 * @return string Widget name.
	 */
	public function get_name() {
		return parent::get_widget_slug( 'Advanced_Search' );
	}

	/**
	 * Retrieve Advanced Search Widget title.
	 *
	 * @since 1.43.0
	 * @access public
	 *
	 * @return string Widget title.
	 */
	public function get_title() {
		return parent::get_widget_title( 'Advanced_Search' );
	}

	/**
	 * Retrieve Advanced Search Widget icon.
	 *
	 * @since 1.43.0
	 * @access public
	 *
	 * @return string Widget icon.
	 */
	public function get_icon() {
		return 'uael-icon-advanced-search';
	}

	/**
	 * Retrieve Widget Keywords.
	 *
	 * @since 1.43.0
	 * @access public
	 *
	 * @return string Widget keywords.
	 */
	public function get_keywords() {
		return array( 'uael', 'search', 'advanced', 'advanced search', 'ajax search', 'live search' );
	}

	/**
	 * Retrieve the list of scripts the Advanced Search widget depended on.
	 *
	 * Used to set scripts dependencies required to run the widget.
	 *
	 * @since 1.43.0
	 * @access public
	 *
	 * @return array Widget scripts dependencies.
	 */
	public function get_script_depends() {
		return array( 'uael-advanced-search' );
	}

	/**
	 * Retrieve the list of styles the Advanced Search widget depended on.
	 *
	 * Used to set style dependencies required to run the widget.
	 *
	 * @since 1.43.0
	 * @access public
	 *
	 * @return array Widget styles dependencies.
	 */
	public function get_style_depends() {
		return array( 'uael-advanced-search' );
	}

	/**
	 * Register Advanced Search controls.
	 *
	 * @since 1.43.0
	 * @access protected
	 */
	protected function register_controls() {
		$this->register_content_search_options();
		$this->register_content_display_settings();
		$this->register_content_query_controls();
		$this->register_content_layout_controls();
		$this->register_content_enhanced_search_controls();

		$this->register_style_search_input_controls();
		$this->register_style_action_button_controls();
		$this->register_style_results_container_controls();
		$this->register_style_trending_keywords_controls();
		$this->register_style_result_items_controls();
	}

	/**
	 * Register Search Options Controls.
	 *
	 * @since 1.43.0
	 * @access protected
	 */
	protected function register_content_search_options() {
		$this->start_controls_section(
			'uael_search_configuration',
			array(
				'label' => __( 'Search Box', 'uael' ),
			)
		);

		$this->add_control(
			'search_interface_style',
			array(
				'label'              => __( 'Layout', 'uael' ),
				'type'               => Controls_Manager::SELECT,
				'default'            => 'text',
				'options'            => array(
					'text'      => __( 'Input Box', 'uael' ),
					'icon'      => __( 'Icon', 'uael' ),
					'icon_text' => __( 'Input Box With Button', 'uael' ),
				),
				'prefix_class'       => 'uael-search-layout-',
				'render_type'        => 'template',
				'frontend_available' => true,
			)
		);

		$this->add_control(
			'input_field_placeholder',
			array(
				'label'              => __( 'Placeholder', 'uael' ),
				'type'               => Controls_Manager::TEXT,
				'default'            => __( 'What are you looking for?', 'uael' ),
				'input_type'         => 'text',
				'label_block'        => true,
				'description'        => __( 'Maximum 50 characters to prevent overlap with icon', 'uael' ),
				'frontend_available' => true,
			)
		);

		$this->add_control(
			'action_button_label',
			array(
				'label'     => __( 'Button Text', 'uael' ),
				'type'      => Controls_Manager::TEXT,
				'default'   => __( 'Find Results', 'uael' ),
				'condition' => array(
					'search_interface_style' => 'icon_text',
				),
			)
		);

		if ( UAEL_Helper::is_elementor_updated() ) {
			$this->add_control(
				'new_action_button_icon',
				array(
					'label'            => __( 'Button Icon', 'uael' ),
					'type'             => Controls_Manager::ICONS,
					'fa4compatibility' => 'action_button_icon',
					'default'          => array(
						'value'   => 'fas fa-search',
						'library' => 'fa-solid',
					),
					'condition'        => array(
						'search_interface_style' => 'icon_text',
					),
				)
			);
		} else {
			$this->add_control(
				'action_button_icon',
				array(
					'label'     => __( 'Button Icon', 'uael' ),
					'type'      => Controls_Manager::ICON,
					'default'   => 'fa fa-search',
					'condition' => array(
						'search_interface_style' => 'icon_text',
					),
				)
			);
		}

		$this->add_control(
			'action_button_icon_position',
			array(
				'label'       => __( 'Icon Position', 'uael' ),
				'type'        => Controls_Manager::SELECT,
				'default'     => 'after',
				'label_block' => false,
				'options'     => array(
					'after'  => __( 'After Text', 'uael' ),
					'before' => __( 'Before Text', 'uael' ),
				),
				'condition'   => array(
					'search_interface_style' => 'icon_text',
				),
			)
		);

		$this->add_control(
			'display_search_icon',
			array(
				'label'        => __( 'Show Icon', 'uael' ),
				'type'         => Controls_Manager::SWITCHER,
				'label_on'     => __( 'Yes', 'uael' ),
				'label_off'    => __( 'No', 'uael' ),
				'return_value' => 'yes',
				'default'      => 'yes',
				'condition'    => array(
					'search_interface_style' => 'icon',
				),
			)
		);

		$this->add_control(
			'search_icon_alignment',
			array(
				'label'     => __( 'Icon Position', 'uael' ),
				'type'      => Controls_Manager::SELECT,
				'default'   => 'left',
				'options'   => array(
					'left'  => __( 'Left', 'uael' ),
					'right' => __( 'Right', 'uael' ),
				),
				'condition' => array(
					'search_interface_style' => 'icon',
					'display_search_icon'    => 'yes',
				),
			)
		);

		$this->end_controls_section();
	}

	/**
	 * Register Display Settings Controls.
	 *
	 * @since 1.43.0
	 * @access protected
	 */
	protected function register_content_display_settings() {
		$this->start_controls_section(
			'uael_display_preferences',
			array(
				'label' => __( 'Trending Searches', 'uael' ),
			)
		);

		$this->add_control(
			'enable_trending_searches',
			array(
				'label'        => __( 'Enable Section', 'uael' ),
				'type'         => Controls_Manager::SWITCHER,
				'label_on'     => __( 'Yes', 'uael' ),
				'label_off'    => __( 'No', 'uael' ),
				'return_value' => 'yes',
				'default'      => 'no',
			)
		);

		$this->add_control(
			'trending_searches_heading',
			array(
				'label'       => __( 'Heading', 'uael' ),
				'type'        => Controls_Manager::TEXT,
				'label_block' => true,
				'default'     => __( 'Trending Searches', 'uael' ),
				'condition'   => array(
					'enable_trending_searches' => 'yes',
				),
			)
		);

		$this->add_control(
			'trending_search_terms',
			array(
				'label'       => __( 'Trending Search Terms', 'uael' ),
				'type'        => Controls_Manager::TEXTAREA,
				'placeholder' => __( 'Tshirts, Tops, Jeans', 'uael' ),
				'description' => __( 'Add items separated by comma', 'uael' ),
				'condition'   => array(
					'enable_trending_searches' => 'yes',
				),
			)
		);

		$this->end_controls_section();
	}

	/**
	 * Register Query Controls.
	 *
	 * @since 1.43.0
	 * @access protected
	 */
	protected function register_content_query_controls() {
		$this->start_controls_section(
			'uael_query_parameters',
			array(
				'label' => __( 'Content Sources', 'uael' ),
			)
		);

		$this->add_control(
			'content_sources',
			array(
				'label'       => __( 'Select Your Search Sources', 'uael' ),
				'type'        => Controls_Manager::SELECT2,
				'multiple'    => true,
				'label_block' => true,
				'default'     => array( 'post' ),
				'options'     => $this->get_post_types(),
			)
		);

		$this->add_control(
			'enable_taxonomy_search',
			array(
				'label'        => __( 'Enable Taxonomy Search', 'uael' ),
				'type'         => Controls_Manager::SWITCHER,
				'label_on'     => __( 'Yes', 'uael' ),
				'label_off'    => __( 'No', 'uael' ),
				'return_value' => 'yes',
				'default'      => 'no',
				'description'  => __( 'Search using categories and tags', 'uael' ),
			)
		);

		$this->add_control(
			'taxonomies_to_search',
			array(
				'label'       => __( 'Select Categories & Tags', 'uael' ),
				'type'        => Controls_Manager::SELECT2,
				'multiple'    => true,
				'label_block' => true,
				'default'     => array( 'category', 'post_tag' ),
				'options'     => $this->get_taxonomies(),
				'description' => __( 'Users will search for term names like "Stark" and the system will find posts with those terms.', 'uael' ),
				'condition'   => array(
					'enable_taxonomy_search' => 'yes',
				),
			)
		);

		$this->end_controls_section();
	}

	/**
	 * Register Layout Controls.
	 *
	 * @since 1.43.0
	 * @access protected
	 */
	protected function register_content_layout_controls() {
		$this->start_controls_section(
			'uael_layout_configuration',
			array(
				'label' => __( 'Layout', 'uael' ),
			)
		);

		$this->add_control(
			'layout_presentation_mode',
			array(
				'label'   => __( 'Select Layout', 'uael' ),
				'type'    => Controls_Manager::SELECT,
				'default' => 'rows',
				'options' => array(
					'rows'    => __( 'List', 'uael' ),
					'columns' => __( 'Grid', 'uael' ),
				),
			)
		);

		$this->add_control(
			'initial_items_count',
			array(
				'label'   => __( 'Initial Items Count', 'uael' ),
				'type'    => Controls_Manager::NUMBER,
				'min'     => 1,
				'max'     => 20,
				'default' => 5,
			)
		);

		$this->add_control(
			'include_featured_images',
			array(
				'label'        => __( 'Featured Images', 'uael' ),
				'type'         => Controls_Manager::SWITCHER,
				'label_on'     => __( 'Show', 'uael' ),
				'label_off'    => __( 'Hide', 'uael' ),
				'return_value' => 'yes',
				'default'      => 'yes',
			)
		);

		$this->add_control(
			'display_results_counter',
			array(
				'label'        => __( 'Results Counter', 'uael' ),
				'type'         => Controls_Manager::SWITCHER,
				'label_on'     => __( 'Show', 'uael' ),
				'label_off'    => __( 'Hide', 'uael' ),
				'return_value' => 'yes',
				'default'      => 'yes',
			)
		);

		$this->add_control(
			'results_counter_template',
			array(
				'label'       => __( 'Total Result Text', 'uael' ),
				'type'        => Controls_Manager::TEXT,
				'label_block' => true,
				'default'     => __( 'Found [count] Results', 'uael' ),
				'description' => __( 'Total result count will be displayed on [count]', 'uael' ),
				'condition'   => array(
					'display_results_counter' => 'yes',
				),
				'ai'          => array(
					'active' => false,
				),
			)
		);

		$this->add_control(
			'enable_load_more_feature',
			array(
				'label'        => __( 'Enable Load More', 'uael' ),
				'type'         => Controls_Manager::SWITCHER,
				'label_on'     => __( 'Yes', 'uael' ),
				'label_off'    => __( 'No', 'uael' ),
				'return_value' => 'yes',
				'default'      => 'no',
				'separator'    => 'before',
			)
		);

		$this->add_control(
			'items_per_load_batch',
			array(
				'label'     => __( 'Results per load', 'uael' ),
				'type'      => Controls_Manager::NUMBER,
				'min'       => 1,
				'max'       => 50,
				'default'   => 10,
				'condition' => array(
					'enable_load_more_feature' => 'yes',
				),
			)
		);

		$this->add_control(
			'load_more_button_text',
			array(
				'label'     => __( 'Text', 'uael' ),
				'type'      => Controls_Manager::TEXT,
				'default'   => __( 'Show More Results', 'uael' ),
				'condition' => array(
					'enable_load_more_feature' => 'yes',
				),
				'ai'        => array(
					'active' => false,
				),
			)
		);

		$this->add_control(
			'empty_results_message',
			array(
				'label'       => __( 'No Results Found', 'uael' ),
				'type'        => Controls_Manager::TEXT,
				'label_block' => true,
				'separator'   => 'before',
				'default'     => __( 'No matching results found', 'uael' ),
				'ai'          => array(
					'active' => false,
				),
			)
		);

		$this->end_controls_section();
	}

	/**
	 * Register Enhanced Search Controls.
	 *
	 * @since 1.43.0
	 * @access protected
	 */
	protected function register_content_enhanced_search_controls() {
		$this->start_controls_section(
			'uael_enhanced_search_features',
			array(
				'label' => __( 'Enhanced Search Features', 'uael' ),
			)
		);

		$this->add_control(
			'enable_advanced_search_features',
			array(
				'label'        => __( 'Advanced Search Features', 'uael' ),
				'type'         => Controls_Manager::SWITCHER,
				'label_on'     => __( 'Yes', 'uael' ),
				'label_off'    => __( 'No', 'uael' ),
				'return_value' => 'yes',
				'default'      => 'no',
				'description'  => __( 'Enable to access advanced search options and features.', 'uael' ),
			)
		);

		$this->add_control(
			'search_ordering',
			array(
				'label'     => __( 'Sort Search Results', 'uael' ),
				'type'      => Controls_Manager::SELECT,
				'default'   => 'relevance',
				'options'   => array(
					'relevance'     => __( 'Relevance', 'uael' ),
					'date'          => __( 'Date (Newest First)', 'uael' ),
					'date_asc'      => __( 'Date (Oldest First)', 'uael' ),
					'title'         => __( 'Title (A-Z)', 'uael' ),
					'title_desc'    => __( 'Title (Z-A)', 'uael' ),
					'comment_count' => __( 'Most Commented', 'uael' ),
					'menu_order'    => __( 'Menu Order', 'uael' ),
					'modified'      => __( 'Recently Modified', 'uael' ),
				),
				'condition' => array(
					'enable_advanced_search_features' => 'yes',
				),
			)
		);

		// Note: These filters are automatically enabled when Advanced Search Features is turned on.
		// The controls are hidden but the backend will use them for enhanced search.

		$this->add_control(
			'minimum_search_length',
			array(
				'label'       => __( 'Minimum Search Length', 'uael' ),
				'type'        => Controls_Manager::NUMBER,
				'min'         => 1,
				'max'         => 10,
				'default'     => 2,
				'description' => __( 'Minimum number of characters required before search starts', 'uael' ),
				'condition'   => array(
					'enable_advanced_search_features' => 'yes',
				),
			)
		);

		$this->add_control(
			'search_debounce_delay',
			array(
				'label'       => __( 'Search Delay (ms)', 'uael' ),
				'type'        => Controls_Manager::NUMBER,
				'min'         => 100,
				'max'         => 2000,
				'default'     => 300,
				'description' => __( 'Delay in milliseconds before search triggers (debounce)', 'uael' ),
				'condition'   => array(
					'enable_advanced_search_features' => 'yes',
				),
			)
		);

		$this->end_controls_section();
	}
	

	/**
	 * Register Search Input Style Controls.
	 *
	 * @since 1.43.0
	 * @access protected
	 */
	protected function register_style_search_input_controls() {
		$this->start_controls_section(
			'uael_search_input_styling',
			array(
				'label' => __( 'Search Box', 'uael' ),
				'tab'   => Controls_Manager::TAB_STYLE,
			)
		);

		$this->add_group_control(
			Group_Control_Typography::get_type(),
			array(
				'name'     => 'input_typography',
				'selector' => '{{WRAPPER}} .uael-advanced-search-input',
				'global'   => array(
					'default' => Global_Typography::TYPOGRAPHY_PRIMARY,
				),
			)
		);

		$this->add_responsive_control(
			'size',
			array(
				'label'              => __( 'Size', 'uael' ),
				'type'               => Controls_Manager::SLIDER,
				'default'            => array(
					'size' => 50,
				),
				'selectors'          => array(
					'{{WRAPPER}} .uael-advanced-search-container'     => 'min-height: {{SIZE}}{{UNIT}}',
					'{{WRAPPER}} .uael-advanced-search-input'         => 'height: {{SIZE}}{{UNIT}}; padding-left: calc({{SIZE}}{{UNIT}} / 5); padding-right: calc({{SIZE}}{{UNIT}} / 5)',
					'{{WRAPPER}} .uael-advanced-search-input-wrapper .uael-icon-left ~ .uael-advanced-search-input' => 'padding-left: 51px',
					'{{WRAPPER}} .uael-advanced-search-input-wrapper .uael-icon-right ~ .uael-advanced-search-input' => 'padding-right: 45px',
					'{{WRAPPER}} .uael-advanced-search-button'        => 'height: {{SIZE}}{{UNIT}}; min-width: {{SIZE}}{{UNIT}}',
					'{{WRAPPER}} .uael-advanced-search-input-wrapper' => 'height: {{SIZE}}{{UNIT}}',
				),
				'render_type'        => 'template',
				'frontend_available' => true,
			)
		);

		$this->start_controls_tabs( 'tabs_input_colors' );

		$this->start_controls_tab(
			'tab_input_normal',
			array(
				'label' => __( 'Normal', 'uael' ),
			)
		);

		$this->add_control(
			'input_text_color',
			array(
				'label'     => __( 'Text Color', 'uael' ),
				'type'      => Controls_Manager::COLOR,
				'selectors' => array(
					'{{WRAPPER}} .uael-advanced-search-input' => 'color: {{VALUE}}',
				),
			)
		);

		$this->add_control(
			'input_placeholder_color',
			array(
				'label'     => __( 'Placeholder Color', 'uael' ),
				'type'      => Controls_Manager::COLOR,
				'selectors' => array(
					'{{WRAPPER}} .uael-advanced-search-input::placeholder' => 'color: {{VALUE}}',
				),
				'default'   => '#7A7A7A6B',
			)
		);

		$this->add_control(
			'input_background_color',
			array(
				'label'     => __( 'Background Color', 'uael' ),
				'type'      => Controls_Manager::COLOR,
				'default'   => '#ededed',
				'selectors' => array(
					'{{WRAPPER}} .uael-advanced-search-input' => 'background-color: {{VALUE}}',
				),
			)
		);

		$this->add_group_control(
			Group_Control_Box_Shadow::get_type(),
			array(
				'name'     => 'input_box_shadow',
				'selector' => '{{WRAPPER}} .uael-advanced-search-input',
			)
		);

		$this->add_control(
			'border_style',
			array(
				'label'       => __( 'Border Style', 'uael' ),
				'type'        => Controls_Manager::SELECT,
				'default'     => 'none',
				'label_block' => false,
				'options'     => array(
					'none'   => __( 'None', 'uael' ),
					'solid'  => __( 'Solid', 'uael' ),
					'double' => __( 'Double', 'uael' ),
					'dotted' => __( 'Dotted', 'uael' ),
					'dashed' => __( 'Dashed', 'uael' ),
				),
				'selectors'   => array(
					'{{WRAPPER}} .uael-advanced-search-input' => 'border-style: {{VALUE}};',
				),
				'condition'   => array(
					'search_interface_style!' => 'icon_text',
				),
			)
		);

		$this->add_control(
			'border_color',
			array(
				'label'     => __( 'Border Color', 'uael' ),
				'type'      => Controls_Manager::COLOR,
				'condition' => array(
					'border_style!'           => 'none',
					'search_interface_style!' => 'icon_text',
				),
				'default'   => '',
				'selectors' => array(
					'{{WRAPPER}} .uael-advanced-search-input' => 'border-color: {{VALUE}};',
				),
			)
		);

		$this->add_control(
			'border_width',
			array(
				'label'      => __( 'Border Width', 'uael' ),
				'type'       => Controls_Manager::DIMENSIONS,
				'size_units' => array( 'px' ),
				'default'    => array(
					'top'    => '1',
					'bottom' => '1',
					'left'   => '1',
					'right'  => '1',
					'unit'   => 'px',
				),
				'condition'  => array(
					'border_style!'           => 'none',
					'search_interface_style!' => 'icon_text',
				),
				'selectors'  => array(
					'{{WRAPPER}} .uael-advanced-search-input' => 'border-width: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
				),
			)
		);

		$this->add_control(
			'border_radius',
			array(
				'label'     => __( 'Border Radius', 'uael' ),
				'type'      => Controls_Manager::SLIDER,
				'range'     => array(
					'px' => array(
						'min' => 0,
						'max' => 200,
					),
				),
				'default'   => array(
					'size' => 3,
					'unit' => 'px',
				),
				'selectors' => array(
					'{{WRAPPER}} .uael-advanced-search-input' => 'border-radius: {{SIZE}}{{UNIT}}',
				),
				'separator' => 'before',
				'condition' => array(
					'search_interface_style!' => 'icon_text',
				),
			)
		);

		$this->end_controls_tab();

		$this->start_controls_tab(
			'tab_input_focus',
			array(
				'label' => __( 'Focus', 'uael' ),
			)
		);

		$this->add_control(
			'input_text_color_focus',
			array(
				'label'     => __( 'Text Color', 'uael' ),
				'type'      => Controls_Manager::COLOR,
				'selectors' => array(
					'{{WRAPPER}} .uael-advanced-search-input:focus' => 'color: {{VALUE}}',
				),
			)
		);

		$this->add_control(
			'input_placeholder_hover_color',
			array(
				'label'     => __( 'Placeholder Color', 'uael' ),
				'type'      => Controls_Manager::COLOR,
				'selectors' => array(
					'{{WRAPPER}} .uael-advanced-search-input:focus::placeholder' => 'color: {{VALUE}}',
				),
			)
		);

		$this->add_control(
			'input_background_color_focus',
			array(
				'label'     => __( 'Background Color', 'uael' ),
				'type'      => Controls_Manager::COLOR,
				'selectors' => array(
					'{{WRAPPER}} .uael-advanced-search-input:focus' => 'background-color: {{VALUE}}',
				),
			)
		);

		$this->add_group_control(
			Group_Control_Box_Shadow::get_type(),
			array(
				'name'           => 'input_box_shadow_focus',
				'selector'       => '{{WRAPPER}} .uael-advanced-search-input:focus',
				'fields_options' => array(
					'box_shadow_type' => array(
						'separator' => 'default',
					),
				),
			)
		);

		$this->add_control(
			'input_border_color_focus',
			array(
				'label'     => __( 'Border Color', 'uael' ),
				'type'      => Controls_Manager::COLOR,
				'selectors' => array(
					'{{WRAPPER}} .uael-advanced-search-input:focus' => 'border-color: {{VALUE}}',
				),
			)
		);

		$this->end_controls_tab();

		$this->end_controls_tabs();

		$this->end_controls_section();
	}

	/**
	 * Register Action Button Style Controls.
	 *
	 * @since 1.43.0
	 * @access protected
	 */
	protected function register_style_action_button_controls() {
		$this->start_controls_section(
			'uael_action_button_styling',
			array(
				'label'     => __( 'Button', 'uael' ),
				'tab'       => Controls_Manager::TAB_STYLE,
				'condition' => array(
					'search_interface_style' => 'icon_text',
				),
			)
		);

		$this->start_controls_tabs( 'tabs_button_colors' );

		$this->start_controls_tab(
			'tab_button_normal',
			array(
				'label' => __( 'Normal', 'uael' ),
			)
		);

		$this->add_control(
			'button_text_color',
			array(
				'label'     => __( 'Text Color', 'uael' ),
				'type'      => Controls_Manager::COLOR,
				'default'   => '#fff',
				'selectors' => array(
					'{{WRAPPER}} .uael-advanced-search-button .elementor-button-text' => 'color: {{VALUE}}',
				),
			)
		);

		$this->add_control(
			'button_icon_color',
			array(
				'label'     => __( 'Icon Color', 'uael' ),
				'type'      => Controls_Manager::COLOR,
				'default'   => '#fff',
				'selectors' => array(
					'{{WRAPPER}} .uael-advanced-search-button .elementor-button-icon' => 'color: {{VALUE}}',
					'{{WRAPPER}} .uael-advanced-search-button .elementor-button-icon svg' => 'fill: {{VALUE}}',
				),
			)
		);

		$this->add_group_control(
			Group_Control_Background::get_type(),
			array(
				'name'           => 'button_background',
				'label'          => __( 'Background', 'uael' ),
				'types'          => array( 'classic', 'gradient' ),
				'exclude'        => array( 'image' ),
				'selector'       => '{{WRAPPER}} .uael-advanced-search-button',
				'fields_options' => array(
					'background' => array(
						'default' => 'classic',
					),
					'color'      => array(
						'default' => '#5124B3',
					),
				),
			)
		);

		$this->end_controls_tab();

		$this->start_controls_tab(
			'tab_button_hover',
			array(
				'label' => __( 'Hover', 'uael' ),
			)
		);

		$this->add_control(
			'button_text_color_hover',
			array(
				'label'     => __( 'Text Color', 'uael' ),
				'type'      => Controls_Manager::COLOR,
				'selectors' => array(
					'{{WRAPPER}} .uael-advanced-search-button:hover .elementor-button-text' => 'color: {{VALUE}}',
				),
			)
		);

		$this->add_control(
			'button_icon_color_hover',
			array(
				'label'     => __( 'Icon Color', 'uael' ),
				'type'      => Controls_Manager::COLOR,
				'selectors' => array(
					'{{WRAPPER}} .uael-advanced-search-button:hover .elementor-button-icon' => 'color: {{VALUE}}',
					'{{WRAPPER}} .uael-advanced-search-button:hover .elementor-button-icon svg' => 'fill: {{VALUE}}',
				),
			)
		);

		$this->add_control(
			'button_background_color_hover',
			array(
				'label'     => __( 'Background Color', 'uael' ),
				'type'      => Controls_Manager::COLOR,
				'selectors' => array(
					'{{WRAPPER}} .uael-advanced-search-button:hover' => 'background-color: {{VALUE}}',
				),
				'condition' => array(
					'button_background_color_hover!' => '',
				),
			)
		);

		$this->add_group_control(
			Group_Control_Background::get_type(),
			array(
				'name'      => 'button_background_hover',
				'label'     => __( 'Background', 'uael' ),
				'types'     => array( 'classic', 'gradient' ),
				'exclude'   => array( 'image' ),
				'selector'  => '{{WRAPPER}} .uael-advanced-search-button:hover',
				'condition' => array(
					'button_background_color_hover' => '',
				),
			)
		);

		$this->end_controls_tab();

		$this->end_controls_tabs();

		$this->add_responsive_control(
			'icon_size',
			array(
				'label'              => __( 'Icon Size', 'uael' ),
				'type'               => Controls_Manager::SLIDER,
				'range'              => array(
					'px' => array(
						'min' => 0,
						'max' => 100,
					),
				),
				'default'            => array(
					'size' => '16',
					'unit' => 'px',
				),
				'selectors'          => array(
					'{{WRAPPER}} .uael-advanced-search-button .elementor-button-icon' => 'font-size: {{SIZE}}{{UNIT}}',
				),
				'separator'          => 'before',
				'render_type'        => 'template',
				'frontend_available' => true,
			)
		);

		$this->add_responsive_control(
			'icon_spacing',
			array(
				'label'       => __( 'Icon Spacing', 'uael' ),
				'type'        => Controls_Manager::SLIDER,
				'range'       => array(
					'px' => array(
						'min' => 0,
						'max' => 50,
					),
				),
				'default'     => array(
					'size' => '8',
					'unit' => 'px',
				),
				'selectors'   => array(
					'{{WRAPPER}} .uael-advanced-search-button .elementor-align-icon-before' => 'margin-right: {{SIZE}}{{UNIT}};',
					'{{WRAPPER}} .uael-advanced-search-button .elementor-align-icon-after'  => 'margin-left: {{SIZE}}{{UNIT}};',
				),
				'render_type' => 'template',
			)
		);

		$this->add_responsive_control(
			'button_width',
			array(
				'label'              => __( 'Width', 'uael' ),
				'type'               => Controls_Manager::SLIDER,
				'range'              => array(
					'px' => array(
						'max'  => 500,
						'step' => 5,
					),
				),
				'selectors'          => array(
					'{{WRAPPER}} .uael-advanced-search-container .uael-advanced-search-button' => 'width: {{SIZE}}{{UNIT}}',
				),
				'render_type'        => 'template',
				'frontend_available' => true,
			)
		);

		$this->end_controls_section();

		$this->start_controls_section(
			'section_toggle_style',
			array(
				'label'     => __( 'Icon', 'uael' ),
				'tab'       => Controls_Manager::TAB_STYLE,
				'condition' => array(
					'search_interface_style' => 'icon',
				),
			)
		);

		$this->start_controls_tabs( 'tabs_toggle_color' );

		$this->start_controls_tab(
			'tab_toggle_normal',
			array(
				'label' => __( 'Normal', 'uael' ),
			)
		);

		$this->add_control(
			'toggle_color',
			array(
				'label'     => __( 'Color', 'uael' ),
				'type'      => Controls_Manager::COLOR,
				'selectors' => array(
					'{{WRAPPER}} .uael-advanced-search-icon' => 'color: {{VALUE}};',
					'{{WRAPPER}} .uael-advanced-search-icon svg' => 'fill: {{VALUE}};',
				),
			)
		);

		$this->end_controls_tab();

		$this->start_controls_tab(
			'tab_toggle_hover',
			array(
				'label' => __( 'Hover', 'uael' ),
			)
		);

		$this->add_control(
			'toggle_color_hover',
			array(
				'label'     => __( 'Color', 'uael' ),
				'type'      => Controls_Manager::COLOR,
				'selectors' => array(
					'{{WRAPPER}} .uael-advanced-search-input-wrapper:hover .uael-advanced-search-icon' => 'color: {{VALUE}};',
					'{{WRAPPER}} .uael-advanced-search-input-wrapper:hover .uael-advanced-search-icon svg' => 'fill: {{VALUE}};',
					'{{WRAPPER}} .uael-advanced-search-input:focus ~ .uael-advanced-search-icon' => 'color: {{VALUE}};',
					'{{WRAPPER}} .uael-advanced-search-input:focus ~ .uael-advanced-search-icon svg' => 'fill: {{VALUE}};',
				),
			)
		);

		$this->end_controls_tab();

		$this->end_controls_tabs();

		$this->add_responsive_control(
			'toggle_icon_size',
			array(
				'label'              => __( 'Icon Size', 'uael' ),
				'type'               => Controls_Manager::SLIDER,
				'default'            => array(
					'size' => 15,
				),
				'range'              => array(
					'px' => array(
						'min' => 10,
						'max' => 50,
					),
				),
				'selectors'          => array(
					'{{WRAPPER}} .uael-advanced-search-icon' => 'font-size: {{SIZE}}{{UNIT}};',
					'{{WRAPPER}} .uael-advanced-search-icon svg' => 'width: {{SIZE}}{{UNIT}}; height: {{SIZE}}{{UNIT}};',
				),
				'condition'          => array(
					'search_interface_style' => 'icon',
				),
				'separator'          => 'before',
				'render_type'        => 'template',
				'frontend_available' => true,
			)
		);

		$this->end_controls_section();
	}

	/**
	 * Register Results Container Style Controls.
	 *
	 * @since 1.43.0
	 * @access protected
	 */
	protected function register_style_results_container_controls() {
		$this->start_controls_section(
			'uael_results_container_styling',
			array(
				'label' => __( 'Results Container', 'uael' ),
				'tab'   => Controls_Manager::TAB_STYLE,
			)
		);

		$this->add_control(
			'results_background_color',
			array(
				'label'     => __( 'Background Color', 'uael' ),
				'type'      => Controls_Manager::COLOR,
				'selectors' => array(
					'{{WRAPPER}} .uael-advanced-search-results' => 'background-color: {{VALUE}};',
				),
			)
		);

		$this->add_responsive_control(
			'results_padding',
			array(
				'label'      => __( 'Padding', 'uael' ),
				'type'       => Controls_Manager::DIMENSIONS,
				'size_units' => array( 'px', 'em', '%' ),
				'selectors'  => array(
					'{{WRAPPER}} .uael-advanced-search-results' => 'padding: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
				),
			)
		);

		$this->add_group_control(
			Group_Control_Border::get_type(),
			array(
				'name'     => 'results_border',
				'selector' => '{{WRAPPER}} .uael-advanced-search-results',
			)
		);

		$this->add_responsive_control(
			'results_border_radius',
			array(
				'label'      => __( 'Border Radius', 'uael' ),
				'type'       => Controls_Manager::DIMENSIONS,
				'size_units' => array( 'px', 'em', '%' ),
				'selectors'  => array(
					'{{WRAPPER}} .uael-advanced-search-results' => 'border-radius: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
				),
			)
		);

		$this->add_group_control(
			Group_Control_Box_Shadow::get_type(),
			array(
				'name'     => 'results_shadow',
				'selector' => '{{WRAPPER}} .uael-advanced-search-results',
			)
		);

		$this->end_controls_section();
	}

	/**
	 * Register Trending Keywords Style Controls.
	 *
	 * @since 1.43.0
	 * @access protected
	 */
	protected function register_style_trending_keywords_controls() {
		$this->start_controls_section(
			'uael_trending_keywords_styling',
			array(
				'label'     => __( 'Trending Searches', 'uael' ),
				'tab'       => Controls_Manager::TAB_STYLE,
				'condition' => array(
					'enable_trending_searches' => 'yes',
				),
			)
		);

		// Heading section.
		$this->add_control(
			'trending_heading_style',
			array(
				'label'     => __( 'Heading', 'uael' ),
				'type'      => Controls_Manager::HEADING,
				'separator' => 'before',
			)
		);

		$this->add_control(
			'trending_heading_color',
			array(
				'label'     => __( 'Color', 'uael' ),
				'type'      => Controls_Manager::COLOR,
				'selectors' => array(
					'{{WRAPPER}} .uael-advanced-search-popular-keywords h4' => 'color: {{VALUE}};',
				),
			)
		);

		$this->add_group_control(
			Group_Control_Typography::get_type(),
			array(
				'name'     => 'trending_heading_typography',
				'selector' => '{{WRAPPER}} .uael-advanced-search-popular-keywords h4',
				'global'   => array(
					'default' => Global_Typography::TYPOGRAPHY_ACCENT,
				),
			)
		);

		$this->add_responsive_control(
			'trending_heading_spacing',
			array(
				'label'      => __( 'Spacing', 'uael' ),
				'type'       => Controls_Manager::SLIDER,
				'size_units' => array( 'px', 'em' ),
				'range'      => array(
					'px' => array(
						'min' => 0,
						'max' => 50,
					),
					'em' => array(
						'min' => 0,
						'max' => 5,
					),
				),
				'default'    => array(
					'unit' => 'px',
					'size' => 10,
				),
				'selectors'  => array(
					'{{WRAPPER}} .uael-advanced-search-popular-keywords h4' => 'padding: {{SIZE}}{{UNIT}};',
				),
			)
		);

		// Badges section.
		$this->add_control(
			'trending_badges_section',
			array(
				'label'     => __( 'Badges', 'uael' ),
				'type'      => Controls_Manager::HEADING,
				'separator' => 'before',
			)
		);

		$this->start_controls_tabs( 'trending_badges_tabs' );

		// Normal Tab.
		$this->start_controls_tab(
			'trending_badges_normal_tab',
			array(
				'label' => __( 'Normal', 'uael' ),
			)
		);

		$this->add_control(
			'trending_badge_text_color',
			array(
				'label'     => __( 'Text Color', 'uael' ),
				'type'      => Controls_Manager::COLOR,
				'selectors' => array(
					'{{WRAPPER}} .uael-popular-keyword' => 'color: {{VALUE}};',
				),
			)
		);

		$this->add_control(
			'trending_badge_background',
			array(
				'label'     => __( 'Background Color', 'uael' ),
				'type'      => Controls_Manager::COLOR,
				'selectors' => array(
					'{{WRAPPER}} .uael-popular-keyword' => 'background-color: {{VALUE}};',
				),
			)
		);

		$this->add_group_control(
			Group_Control_Border::get_type(),
			array(
				'name'     => 'trending_badge_border',
				'selector' => '{{WRAPPER}} .uael-popular-keyword',
			)
		);

		$this->end_controls_tab();

		// Hover Tab.
		$this->start_controls_tab(
			'trending_badges_hover_tab',
			array(
				'label' => __( 'Hover', 'uael' ),
			)
		);

		$this->add_control(
			'trending_badge_hover_background',
			array(
				'label'     => __( 'Background Color', 'uael' ),
				'type'      => Controls_Manager::COLOR,
				'selectors' => array(
					'{{WRAPPER}} .uael-popular-keyword:hover' => 'background-color: {{VALUE}};',
				),
			)
		);

		$this->add_control(
			'trending_badge_hover_text_color',
			array(
				'label'     => __( 'Text Color', 'uael' ),
				'type'      => Controls_Manager::COLOR,
				'selectors' => array(
					'{{WRAPPER}} .uael-popular-keyword:hover' => 'color: {{VALUE}};',
				),
			)
		);

		$this->add_group_control(
			Group_Control_Border::get_type(),
			array(
				'name'     => 'trending_badge_hover_border',
				'selector' => '{{WRAPPER}} .uael-popular-keyword:hover',
			)
		);

		$this->add_group_control(
			Group_Control_Box_Shadow::get_type(),
			array(
				'name'     => 'trending_badge_hover_shadow',
				'selector' => '{{WRAPPER}} .uael-popular-keyword:hover',
			)
		);

		$this->end_controls_tab();

		$this->end_controls_tabs();

		$this->add_responsive_control(
			'trending_badge_border_radius',
			array(
				'label'      => __( 'Border Radius', 'uael' ),
				'type'       => Controls_Manager::DIMENSIONS,
				'size_units' => array( 'px', 'em', '%' ),
				'selectors'  => array(
					'{{WRAPPER}} .uael-popular-keyword' => 'border-radius: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
				),
			)
		);

		// Common badge settings.
		$this->add_group_control(
			Group_Control_Typography::get_type(),
			array(
				'name'      => 'trending_badge_typography',
				'selector'  => '{{WRAPPER}} .uael-popular-keyword',
				'global'    => array(
					'default' => Global_Typography::TYPOGRAPHY_TEXT,
				),
				'separator' => 'before',
			)
		);

		$this->add_responsive_control(
			'trending_badge_padding',
			array(
				'label'      => __( 'Padding', 'uael' ),
				'type'       => Controls_Manager::DIMENSIONS,
				'size_units' => array( 'px', 'em', '%' ),
				'selectors'  => array(
					'{{WRAPPER}} .uael-popular-keyword' => 'padding: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
				),
			)
		);

		$this->add_responsive_control(
			'trending_badges_list_gap',
			array(
				'label'      => __( 'Gap', 'uael' ),
				'type'       => Controls_Manager::SLIDER,
				'size_units' => array( 'px', 'em' ),
				'range'      => array(
					'px' => array(
						'min' => 0,
						'max' => 50,
					),
					'em' => array(
						'min' => 0,
						'max' => 5,
					),
				),
				'default'    => array(
					'unit' => 'px',
					'size' => 10,
				),
				'selectors'  => array(
					'{{WRAPPER}} .uael-popular-keywords-list' => 'gap: {{SIZE}}{{UNIT}};',
				),
			)
		);

		$this->add_responsive_control(
			'trending_badges_list_display',
			array(
				'label'                => __( 'Display', 'uael' ),
				'type'                 => Controls_Manager::SELECT,
				'default'              => 'inline',
				'options'              => array(
					'inline' => __( 'Inline', 'uael' ),
					'stack'  => __( 'Block', 'uael' ),
				),
				'selectors_dictionary' => array(
					'inline' => 'display: flex; flex-direction: row; flex-wrap: wrap; row-gap: inherit; align-items: center;',
					'stack'  => 'display: flex; flex-direction: column; flex-wrap: nowrap; align-items: flex-start; row-gap: 10px;',
				),
				'selectors'            => array(
					'{{WRAPPER}} .uael-popular-keywords-list' => '{{VALUE}}',
				),
			)
		);

		$this->end_controls_section();
	} 

	/**
	 * Register Result Items Style Controls.
	 *
	 * @since 1.43.0
	 * @access protected
	 */
	protected function register_style_result_items_controls() {
		$this->start_controls_section(
			'uael_load_more_button_styling',
			array(
				'label'     => __( 'Load More Button', 'uael' ),
				'tab'       => Controls_Manager::TAB_STYLE,
				'condition' => array(
					'enable_load_more_feature' => 'yes',
				),
			)
		);

		$this->start_controls_tabs( 'load_more_button_tabs' );

		// Normal state tab.
		$this->start_controls_tab(
			'load_more_button_normal',
			array(
				'label' => __( 'Normal', 'uael' ),
			)
		);

		$this->add_control(
			'load_more_button_text_color',
			array(
				'label'     => __( 'Text Color', 'uael' ),
				'type'      => Controls_Manager::COLOR,
				'default'   => '#fff',
				'selectors' => array(
					'{{WRAPPER}} .uael-load-more-button' => 'color: {{VALUE}};',
				),
			)
		);

		$this->add_group_control(
			Group_Control_Background::get_type(),
			array(
				'name'           => 'load_more_button_background',
				'types'          => array( 'classic' ),
				'exclude'        => array( 'image' ),
				'selector'       => '{{WRAPPER}} .uael-load-more-button',
				'fields_options' => array(
					'background' => array(
						'default' => 'classic',
					),
					'color'      => array(
						'default' => '#5124B3',
					),
				),
			)
		);

		$this->end_controls_tab();

		// Hover state tab.
		$this->start_controls_tab(
			'load_more_button_hover',
			array(
				'label' => __( 'Hover', 'uael' ),
			)
		);

		$this->add_control(
			'load_more_button_hover_text_color',
			array(
				'label'     => __( 'Text Color', 'uael' ),
				'type'      => Controls_Manager::COLOR,
				'selectors' => array(
					'{{WRAPPER}} .uael-load-more-button:hover' => 'color: {{VALUE}};',
				),
			)
		);

		$this->add_group_control(
			Group_Control_Background::get_type(),
			array(
				'name'     => 'load_more_button_hover_background',
				'types'    => array( 'classic' ),
				'exclude'  => array( 'image' ),
				'selector' => '{{WRAPPER}} .uael-load-more-button:hover',
			)
		);

		$this->add_control(
			'load_more_button_hover_animation',
			array(
				'label'              => __( 'Hover Animation', 'uael' ),
				'type'               => Controls_Manager::SELECT,
				'default'            => 'none',
				'options'            => array(
					'none'                   => __( 'None', 'uael' ),
					'fade'                   => __( 'Fade', 'uael' ),
					'slide'                  => __( 'Slide', 'uael' ),
					'grow'                   => __( 'Grow', 'uael' ),
					'shrink'                 => __( 'Shrink', 'uael' ),
					'pulse'                  => __( 'Pulse', 'uael' ),
					'float'                  => __( 'Float', 'uael' ),
					'sink'                   => __( 'Sink', 'uael' ),
					'bob'                    => __( 'Bob', 'uael' ),
					'hang'                   => __( 'Hang', 'uael' ),
					'skew'                   => __( 'Skew', 'uael' ),
					'skew-forward'           => __( 'Skew Forward', 'uael' ),
					'skew-backward'          => __( 'Skew Backward', 'uael' ),
					'wobble-horizontal'      => __( 'Wobble Horizontal', 'uael' ),
					'wobble-vertical'        => __( 'Wobble Vertical', 'uael' ),
					'wobble-to-bottom-right' => __( 'Wobble To Bottom Right', 'uael' ),
					'wobble-to-top-right'    => __( 'Wobble To Top Right', 'uael' ),
					'wobble-top'             => __( 'Wobble Top', 'uael' ),
					'wobble-bottom'          => __( 'Wobble Bottom', 'uael' ),
					'wobble-skew'            => __( 'Wobble Skew', 'uael' ),
					'buzz'                   => __( 'Buzz', 'uael' ),
					'buzz-out'               => __( 'Buzz Out', 'uael' ),
				),
				'frontend_available' => true,
			)
		);

		$this->end_controls_tab();

		$this->end_controls_tabs();

		// Typography.
		$this->add_group_control(
			Group_Control_Typography::get_type(),
			array(
				'name'      => 'load_more_button_typography',
				'selector'  => '{{WRAPPER}} .uael-load-more-button',
				'separator' => 'before',
				'global'    => array(
					'default' => Global_Typography::TYPOGRAPHY_ACCENT,
				),
			)
		);

		// Padding.
		$this->add_responsive_control(
			'load_more_button_padding',
			array(
				'label'      => __( 'Padding', 'uael' ),
				'type'       => Controls_Manager::DIMENSIONS,
				'size_units' => array( 'px', 'em', '%' ),
				'selectors'  => array(
					'{{WRAPPER}} .uael-load-more-button' => 'padding: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
				),
			)
		);

		// Border.
		$this->add_group_control(
			Group_Control_Border::get_type(),
			array(
				'name'     => 'load_more_button_border',
				'selector' => '{{WRAPPER}} .uael-load-more-button',
			)
		);

		// Border Radius.
		$this->add_responsive_control(
			'load_more_button_border_radius',
			array(
				'label'      => __( 'Border Radius', 'uael' ),
				'type'       => Controls_Manager::DIMENSIONS,
				'size_units' => array( 'px', 'em', '%' ),
				'selectors'  => array(
					'{{WRAPPER}} .uael-load-more-button' => 'border-radius: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
				),
			)
		);

		// Box Shadow.
		$this->add_group_control(
			Group_Control_Box_Shadow::get_type(),
			array(
				'name'     => 'load_more_button_shadow',
				'selector' => '{{WRAPPER}} .uael-load-more-button',
			)
		);

		// Container alignment.
		$this->add_responsive_control(
			'load_more_button_alignment',
			array(
				'label'     => __( 'Alignment', 'uael' ),
				'type'      => Controls_Manager::CHOOSE,
				'default'   => 'center',
				'options'   => array(
					'flex-start' => array(
						'title' => __( 'Left', 'uael' ),
						'icon'  => 'eicon-h-align-left',
					),
					'center'     => array(
						'title' => __( 'Center', 'uael' ),
						'icon'  => 'eicon-h-align-center',
					),
					'flex-end'   => array(
						'title' => __( 'Right', 'uael' ),
						'icon'  => 'eicon-h-align-right',
					),
				),
				'selectors' => array(
					'{{WRAPPER}} .uael-search-results-pagination' => 'justify-content: {{VALUE}}; display: flex;',
				),
			)
		);

		$this->end_controls_section();
	}

	/**
	 * Get post types for post type control.
	 *
	 * @since 1.43.0
	 * @return array
	 */
	protected function get_post_types() {
		// Get post types from helper.
		$options = UAEL_Posts_Helper::get_post_types();

		// Exclude post types that should not be searchable.
		$excluded_types = array(
			'elementor_library',      // Elementor templates/library items.
			'wpforms',                // WP Forms.
			'e-floating-buttons',     // Elementor floating buttons/elements.
			'elementor-hf',           // Header Footer Elementor post type.
		);

		foreach ( $excluded_types as $excluded_type ) {
			if ( isset( $options[ $excluded_type ] ) ) {
				unset( $options[ $excluded_type ] );
			}
		}

		// Add Media Library (Attachments) option.
		$options['attachment'] = 'Media';

		return $options;
	}

	/**
	 * Get taxonomies for taxonomy control.
	 *
	 * @since 1.43.0
	 * @return array
	 */
	protected function get_taxonomies() {
		$taxonomies = get_taxonomies(
			array(
				'public'            => true,
				'show_in_nav_menus' => true,
			),
			'objects'
		);

		$options = array();

		foreach ( $taxonomies as $taxonomy ) {
			$options[ $taxonomy->name ] = $taxonomy->label;
		}

		// Exclude taxonomies that should not be searchable.
		$excluded_taxonomies = array(
			'nav_menu',           // Navigation menus.
			'link_category',      // Link categories.
			'post_format',        // Post formats.
			'elementor_library_type',    // Elementor library types.
			'elementor_library_category', // Elementor library categories.
		);

		foreach ( $excluded_taxonomies as $excluded_taxonomy ) {
			if ( isset( $options[ $excluded_taxonomy ] ) ) {
				unset( $options[ $excluded_taxonomy ] );
			}
		}

		return $options;
	}

	/**
	 * Load template file.
	 *
	 * Loads the main template file for the Advanced Search widget.
	 *
	 * @since 1.43.0
	 * @param array $settings Prepared settings array.
	 * @return void
	 */
	protected function load_template( $settings ) {
		$template_path = UAEL_MODULES_DIR . 'advanced-search/templates/template-search-form.php';

		if ( ! file_exists( $template_path ) ) {
			return;
		}

		include $template_path;
	}

	/**
	 * Prepare settings for template.
	 *
	 * @since 1.43.0
	 * @return array Prepared settings.
	 */
	protected function prepare_settings() {
		$settings = $this->get_settings_for_display();

		// Enforce 50-character limit on placeholder text.
		if ( ! empty( $settings['input_field_placeholder'] ) ) {
			$settings['input_field_placeholder'] = substr( $settings['input_field_placeholder'], 0, 50 );
		}

		// Map content sources to actual post types.
		$post_types = array();
		if ( ! empty( $settings['content_sources'] ) ) {
			$post_types = $this->filter_post_types( $settings['content_sources'] );
		}

		// Default to posts if no valid post types remain.
		if ( empty( $post_types ) ) {
			$post_types = array( 'post' );
		}

		$settings['filtered_post_types'] = $post_types;

		// Prepare trending keywords array.
		$settings['trending_keywords_array'] = $this->get_trending_keywords( $settings );

		// Pre-calculate display flags.
		$settings['show_left_icon']     = ( 'icon' === $settings['search_interface_style'] && 'yes' === $settings['display_search_icon'] && 'left' === $settings['search_icon_alignment'] );
		$settings['show_right_icon']    = ( 'icon' === $settings['search_interface_style'] && 'yes' === $settings['display_search_icon'] && 'right' === $settings['search_icon_alignment'] );
		$settings['show_action_button'] = ( 'icon_text' === $settings['search_interface_style'] );
		$settings['show_trending']      = ( 'yes' === $settings['enable_trending_searches'] && ! empty( $settings['trending_search_terms'] ) );
		$settings['show_results_count'] = ( 'yes' === $settings['display_results_counter'] );

		return $settings;
	}

	/**
	 * Get trending keywords as array.
	 *
	 * @since 1.43.0
	 * @param array $settings Widget settings.
	 * @return array Trending keywords.
	 */
	protected function get_trending_keywords( $settings ) {
		if ( empty( $settings['trending_search_terms'] ) ) {
			return array();
		}

		$keywords       = explode( ',', $settings['trending_search_terms'] );
		$keywords_array = array();

		foreach ( $keywords as $keyword ) {
			$keyword = trim( $keyword );
			if ( ! empty( $keyword ) ) {
				$keywords_array[] = $keyword;
			}
		}

		return $keywords_array;
	}

	/**
	 * Filter out excluded post types.
	 *
	 * @since 1.43.0
	 * @param array $post_types Post types to filter.
	 * @return array Filtered post types.
	 */
	protected function filter_post_types( $post_types ) {
		$excluded_types = array(
			'elementor_library',  // Elementor templates/library items.
			'wpforms',            // WP Forms.
			'e-floating-buttons', // Elementor floating buttons/elements.
			'elementor-hf',       // Header Footer Elementor post type.
			'hfe_header',         // Header Footer Elementor header.
			'hfe_footer',         // Header Footer Elementor footer.
			'hfe_section',        // Header Footer Elementor section.
		);

		$filtered = array();
		foreach ( $post_types as $post_type ) {
			if ( ! in_array( $post_type, $excluded_types, true ) ) {
				$filtered[] = $post_type;
			}
		}

		return $filtered;
	}

	/**
	 * Get search configuration data attributes.
	 *
	 * @since 1.43.0
	 * @param array $settings Widget settings.
	 * @return array Configuration array.
	 */
	protected function get_search_config( $settings ) {
		return array(
			'post_types'             => $settings['filtered_post_types'],
			'initial_results'        => $settings['initial_items_count'],
			'display_style'          => $settings['layout_presentation_mode'],
			'show_image'             => $settings['include_featured_images'],
			'show_pagination'        => $settings['enable_load_more_feature'],
			'results_per_page'       => $settings['items_per_load_batch'],
			'show_total_results'     => $settings['display_results_counter'],
			'total_results_text'     => $settings['results_counter_template'],
			// Enhanced search features.
			'enable_taxonomy_search' => $settings['enable_taxonomy_search'],
			'taxonomy_names'         => ! empty( $settings['taxonomies_to_search'] ) && is_array( $settings['taxonomies_to_search'] ) ? $settings['taxonomies_to_search'] : array(),
			'search_ordering'        => $settings['search_ordering'],
			// Automatically enable author and date filtering when advanced search features is on.
			'enable_author_filter'   => 'yes' === $settings['enable_advanced_search_features'] ? 'yes' : 'no',
			'enable_date_filter'     => 'yes' === $settings['enable_advanced_search_features'] ? 'yes' : 'no',
			'minimum_search_length'  => $settings['minimum_search_length'],
			'search_debounce_delay'  => $settings['search_debounce_delay'],
			'nonce'                  => wp_create_nonce( 'uael_advanced_search_nonce' ),
		);
	}

	/**
	 * Render Advanced Search widget output on the frontend.
	 *
	 * @since 1.43.0
	 * @access protected
	 */
	protected function render() {
		// Prepare settings.
		$settings = $this->prepare_settings();

		// Set render attributes.
		$this->add_render_attribute( 'search_container', 'class', 'uael-advanced-search-container' );
		$this->add_render_attribute(
			'search_container',
			'data-settings',
			wp_json_encode( $this->get_search_config( $settings ) )
		);

		// Load the template.
		$this->load_template( $settings );
	}
}
