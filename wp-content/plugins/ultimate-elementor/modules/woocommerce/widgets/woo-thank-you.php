<?php
/**
 * UAEL WooCommerce Thank You Page.
 *
 * @package UAEL
 */

namespace UltimateElementor\Modules\Woocommerce\Widgets;

use Elementor\Controls_Manager;
use Elementor\Group_Control_Background;
use Elementor\Group_Control_Border;
use Elementor\Group_Control_Typography;
use Elementor\Group_Control_Box_Shadow;
use Elementor\Core\Kits\Documents\Tabs\Global_Typography;
use Elementor\Core\Kits\Documents\Tabs\Global_Colors;
use UltimateElementor\Base\Common_Widget;
use UltimateElementor\Classes\UAEL_Helper;

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

/**
 * Class Woo_Thank_You.
 */
class Woo_Thank_You extends Common_Widget {

	/**
	 * Constructor.
	 *
	 * @param array $data Widget data. Default is an empty array.
	 * @param array $args Widget arguments. Default is null.
	 * @since 1.42.0
	 * @access public
	 */
	public function __construct( $data = array(), $args = null ) {
		parent::__construct( $data, $args );

		// Only add template override on frontend thank you pages.
		if ( ! is_admin() && function_exists( 'is_wc_endpoint_url' ) && is_wc_endpoint_url( 'order-received' ) ) {
			$this->maybe_override_thankyou_template();
		}
	}

	/**
	 * Check if widget is present on page and override WooCommerce template.
	 *
	 * @since 1.42.0
	 * @access private
	 */
	private function maybe_override_thankyou_template() {
		// Get the actual widget name from the widget itself.
		$widget_key = $this->get_name();
		$page_id    = get_the_ID();
		
		// Check if this widget exists on current page.
		$widgets = get_post_meta( $page_id, '_elementor_controls_usage', true );

		if ( isset( $widgets[ $widget_key ] ) ) {
			add_filter( 'wc_get_template', array( $this, 'override_thankyou_template' ), 10, 2 );
		} else {
			// Let's also check if page is built with Elementor.
			if ( did_action( 'elementor/loaded' ) && \Elementor\Plugin::$instance->db->is_built_with_elementor( $page_id ) ) {
				// Get all widgets on page.
				$document = \Elementor\Plugin::$instance->documents->get( $page_id );
				if ( $document ) {
					$data         = $document->get_elements_data();
					$widget_found = $this->widget_exists_in_data( $data, $widget_key );
					
					if ( $widget_found ) {
						
						add_filter( 'wc_get_template', array( $this, 'override_thankyou_template' ), 10, 2 );
					}
				}
			}
		}
	}

	/**
	 * Check if widget is present in Elementor data.
	 *
	 * @param array  $data The Elementor data to search through.
	 * @param string $widget_key The widget key to search for.
	 * @return bool Whether the widget exists in the data.
	 * @since 1.42.0
	 * @access private
	 */
	public function widget_exists_in_data( $data, $widget_key ) {

		if ( empty( $data ) || ! is_array( $data ) ) {
			return false;
		}

		foreach ( $data as $element ) {

			// 1. If this element is a widget, compare its widgetType.
			if ( isset( $element['elType'] ) && 'widget' === $element['elType'] ) {
				if ( isset( $element['widgetType'] ) && $element['widgetType'] === $widget_key ) {
					return true;
				}
			}

			// 2. CHILDREN: Elementor stores nested elements inside `elements`
			if ( isset( $element['elements'] ) && ! empty( $element['elements'] ) ) {
				if ( $this->widget_exists_in_data( $element['elements'], $widget_key ) ) {
					return true;
				}
			}
		}
		return false;
	}


	/**
	 * Override WooCommerce thank you template.
	 *
	 * @param string $template Template path.
	 * @param string $template_name Template name.
	 * @return string Modified template path.
	 * @since 1.42.0
	 * @access public
	 */
	public function override_thankyou_template( $template, $template_name ) {
		if ( 'checkout/thankyou.php' === $template_name ) {
			// Return path to our blank template.
			return __DIR__ . '/../templates/woo-thank-you-blank.php';
		}
		return $template;
	}

	/**
	 * Retrieve Widget name.
	 *
	 * @return string Widget name.
	 * @since 1.42.0
	 * @access public
	 */
	public function get_name() {
		return parent::get_widget_slug( 'Woo_Thank_You' );
	}

	/**
	 * Retrieve Widget title.
	 *
	 * @return string Widget title.
	 * @since 1.42.0
	 * @access public
	 */
	public function get_title() {
		return parent::get_widget_title( 'Woo_Thank_You' );
	}

	/**
	 * Retrieve Widget icon.
	 *
	 * @return string Widget icon.
	 * @since 1.42.0
	 * @access public
	 */
	public function get_icon() {
		return parent::get_widget_icon( 'Woo_Thank_You' );
	}

	/**
	 * Retrieve Widget Keywords.
	 *
	 * @return array Widget keywords.
	 * @since 1.42.0
	 * @access public
	 */
	public function get_keywords() {
		return parent::get_widget_keywords( 'Woo_Thank_You' );
	}

	/**
	 * Get Script Depends.
	 *
	 * @return array scripts.
	 * @since 1.42.0
	 * @access public
	 */
	public function get_script_depends() {
		return array( 'uael-woocommerce' );
	}

	/**
	 * Get Style Depends.
	 *
	 * @return array styles.
	 * @since 1.42.0
	 * @access public
	 */
	public function get_style_depends() {
		return array( 'uael-woo-thank-you' );
	}

	/**
	 * Register controls.
	 *
	 * @since 1.42.0
	 * @access protected
	 */
	protected function register_controls() {
		// Content Tab Controls.
		$this->register_general_controls();

		// Style Tab Controls.
		$this->register_style_controls();
		$this->register_helpful_information();
	}

	/**
	 * Register General Tab Controls.
	 *
	 * @since 1.42.0
	 * @access protected
	 */
	protected function register_general_controls() {

		// Order Confirmation Message Section.
		$this->start_controls_section(
			'confirmation_section',
			array(
				'label' => esc_html__( 'Thank You Card', 'uael' ),
				'tab'   => Controls_Manager::TAB_CONTENT,
			)
		);

		$this->add_control(
			'show_confirmation_message',
			array(
				'label'        => esc_html__( 'Show Card', 'uael' ),
				'type'         => Controls_Manager::SWITCHER,
				'label_on'     => esc_html__( 'Yes', 'uael' ),
				'label_off'    => esc_html__( 'No', 'uael' ),
				'return_value' => 'yes',
				'default'      => 'yes',
			)
		);

		$this->add_control(
			'confirmation_message',
			array(
				'label'       => esc_html__( 'Title', 'uael' ),
				'type'        => Controls_Manager::TEXT,
				'default'     => esc_html__( 'Thank you for your order!', 'uael' ),
				'placeholder' => esc_html__( 'Thank you for your order!', 'uael' ),
				'condition'   => array(
					'show_confirmation_message' => 'yes',
				),
			)
		);

		$this->add_control(
			'confirmation_html_tag',
			array(
				'label'     => esc_html__( 'HTML Tag', 'uael' ),
				'type'      => Controls_Manager::SELECT,
				'default'   => 'h2',
				'options'   => array(
					'h1'   => esc_html__( 'h1', 'uael' ),
					'h2'   => esc_html__( 'h2', 'uael' ),
					'h3'   => esc_html__( 'h3', 'uael' ),
					'h4'   => esc_html__( 'h4', 'uael' ),
					'h5'   => esc_html__( 'h5', 'uael' ),
					'h6'   => esc_html__( 'h6', 'uael' ),
					'p'    => esc_html__( 'p', 'uael' ),
					'div'  => esc_html__( 'div', 'uael' ),
					'span' => esc_html__( 'span', 'uael' ),
				),
				'condition' => array(
					'show_confirmation_message' => 'yes',
				),
			)
		);

		$this->add_control(
			'confirmation_subtext',
			array(
				'label'       => esc_html__( 'Description', 'uael' ),
				'type'        => Controls_Manager::TEXTAREA,
				'default'     => esc_html__( 'Your order has been received and is being processed. We\'ll send you a confirmation email shortly.', 'uael' ),
				'placeholder' => esc_html__( 'Enter subtext here...', 'uael' ),
				'condition'   => array(
					'show_confirmation_message' => 'yes',
				),
			)
		);

		$this->add_control(
			'confirmation_icon',
			array(
				'label'     => esc_html__( 'Icon', 'uael' ),
				'type'      => Controls_Manager::ICONS,
				'default'   => array(
					'value'   => 'fas fa-check-circle',
					'library' => 'fa-solid',
				),
				'condition' => array(
					'show_confirmation_message' => 'yes',
				),
			)
		);

		$this->add_responsive_control(
			'confirmation_icon_size',
			array(
				'label'      => esc_html__( 'Size', 'uael' ),
				'type'       => Controls_Manager::SLIDER,
				'size_units' => array( 'px', 'em', 'rem' ),
				'range'      => array(
					'px' => array(
						'min' => 1,
						'max' => 200,
					),
				),
				'default'    => array(
					'size' => 25,
					'unit' => 'px',
				),
				'selectors'  => array(
					'{{WRAPPER}} .uael-woo-thankyou-confirmation .uael-confirmation-icon' => 'font-size: {{SIZE}}{{UNIT}}; height: {{SIZE}}{{UNIT}}; width: {{SIZE}}{{UNIT}}; line-height: {{SIZE}}{{UNIT}};',
					'{{WRAPPER}} .uael-woo-thankyou-confirmation .uael-confirmation-icon i' => 'font-size: {{SIZE}}{{UNIT}};',
					'{{WRAPPER}} .uael-woo-thankyou-confirmation .uael-confirmation-icon svg' => 'width: {{SIZE}}{{UNIT}}; height: {{SIZE}}{{UNIT}};',
				),
				'condition'  => array(
					'show_confirmation_message' => 'yes',
					'confirmation_icon[value]!' => '',
				),
			)
		);

		$this->add_control(
			'confirmation_icon_rotate',
			array(
				'label'     => esc_html__( 'Rotate', 'uael' ),
				'type'      => Controls_Manager::SLIDER,
				'default'   => array(
					'size' => 0,
					'unit' => 'deg',
				),
				'range'     => array(
					'deg' => array(
						'min' => 0,
						'max' => 360,
					),
				),
				'selectors' => array(
					'{{WRAPPER}} .uael-woo-thankyou-confirmation .uael-confirmation-icon i, {{WRAPPER}} .uael-woo-thankyou-confirmation .uael-confirmation-icon svg' => 'transform: rotate({{SIZE}}{{UNIT}});',
				),
				'condition' => array(
					'show_confirmation_message' => 'yes',
					'confirmation_icon[value]!' => '',
				),
			)
		);

		$this->end_controls_section();

		// Order Information Section.
		$this->start_controls_section(
			'order_info_section',
			array(
				'label' => esc_html__( 'Order Details', 'uael' ),
				'tab'   => Controls_Manager::TAB_CONTENT,
			)
		);

		$this->add_control(
			'show_order_info',
			array(
				'label'        => esc_html__( 'Show Section', 'uael' ),
				'type'         => Controls_Manager::SWITCHER,
				'label_on'     => esc_html__( 'Yes', 'uael' ),
				'label_off'    => esc_html__( 'No', 'uael' ),
				'return_value' => 'yes',
				'default'      => 'yes',
			)
		);

		$this->add_control(
			'show_order_number',
			array(
				'label'        => esc_html__( 'Order Number', 'uael' ),
				'type'         => Controls_Manager::SWITCHER,
				'label_on'     => esc_html__( 'Yes', 'uael' ),
				'label_off'    => esc_html__( 'No', 'uael' ),
				'return_value' => 'yes',
				'default'      => 'yes',
				'condition'    => array(
					'show_order_info' => 'yes',
				),
			)
		);

		$this->add_control(
			'order_number_label',
			array(
				'label'     => esc_html__( 'Label', 'uael' ),
				'type'      => Controls_Manager::TEXT,
				'default'   => esc_html__( 'Order Number', 'uael' ),
				'condition' => array(
					'show_order_info'   => 'yes',
					'show_order_number' => 'yes',
				),
			)
		);

		$this->add_control(
			'show_order_date',
			array(
				'label'        => esc_html__( 'Order Date', 'uael' ),
				'type'         => Controls_Manager::SWITCHER,
				'label_on'     => esc_html__( 'Yes', 'uael' ),
				'label_off'    => esc_html__( 'No', 'uael' ),
				'return_value' => 'yes',
				'default'      => 'yes',
				'condition'    => array(
					'show_order_info' => 'yes',
				),
			)
		);

		$this->add_control(
			'order_date_label',
			array(
				'label'     => esc_html__( 'Label', 'uael' ),
				'type'      => Controls_Manager::TEXT,
				'default'   => esc_html__( 'Order Date', 'uael' ),
				'condition' => array(
					'show_order_info' => 'yes',
					'show_order_date' => 'yes',
				),
			)
		);

		$this->end_controls_section();

		// Order Body Section.
		$this->start_controls_section(
			'order_body_section',
			array(
				'label' => esc_html__( 'Order Summary', 'uael' ),
				'tab'   => Controls_Manager::TAB_CONTENT,
			)
		);

		$this->add_control(
			'show_order_body',
			array(
				'label'        => esc_html__( 'Show Section', 'uael' ),
				'type'         => Controls_Manager::SWITCHER,
				'label_on'     => esc_html__( 'Yes', 'uael' ),
				'label_off'    => esc_html__( 'No', 'uael' ),
				'return_value' => 'yes',
				'default'      => 'yes',
			)
		);

		$this->add_control(
			'show_products',
			array(
				'label'        => esc_html__( 'Show Products', 'uael' ),
				'type'         => Controls_Manager::SWITCHER,
				'label_on'     => esc_html__( 'Yes', 'uael' ),
				'label_off'    => esc_html__( 'No', 'uael' ),
				'return_value' => 'yes',
				'default'      => 'yes',
				'condition'    => array(
					'show_order_body' => 'yes',
				),
			)
		);

		$this->add_control(
			'show_order_summary',
			array(
				'label'        => esc_html__( 'Include summary info', 'uael' ),
				'type'         => Controls_Manager::SWITCHER,
				'label_on'     => esc_html__( 'Yes', 'uael' ),
				'label_off'    => esc_html__( 'No', 'uael' ),
				'return_value' => 'yes',
				'default'      => 'yes',
				'condition'    => array(
					'show_order_body' => 'yes',
				),
			)
		);

		$this->add_control(
			'show_product_quantity',
			array(
				'label'        => esc_html__( 'Show Quantity', 'uael' ),
				'type'         => Controls_Manager::SWITCHER,
				'label_on'     => esc_html__( 'Yes', 'uael' ),
				'label_off'    => esc_html__( 'No', 'uael' ),
				'return_value' => 'yes',
				'default'      => 'yes',
				'condition'    => array(
					'show_order_body' => 'yes',
					'show_products'   => 'yes',
				),
			)
		);

		$this->add_control(
			'show_sale_price',
			array(
				'label'        => esc_html__( 'Display Sale Price', 'uael' ),
				'type'         => Controls_Manager::SWITCHER,
				'label_on'     => esc_html__( 'Yes', 'uael' ),
				'label_off'    => esc_html__( 'No', 'uael' ),
				'return_value' => 'yes',
				'default'      => 'yes',
				'condition'    => array(
					'show_order_body' => 'yes',
					'show_products'   => 'yes',
				),
			)
		);

		$this->end_controls_section();

		// Addresses Section.
		$this->start_controls_section(
			'addresses_section',
			array(
				'label' => esc_html__( 'Addresses & Payment', 'uael' ),
				'tab'   => Controls_Manager::TAB_CONTENT,
			)
		);

		$this->add_control(
			'show_shipping_address',
			array(
				'label'        => esc_html__( 'Show Shipping Address', 'uael' ),
				'type'         => Controls_Manager::SWITCHER,
				'label_on'     => esc_html__( 'Yes', 'uael' ),
				'label_off'    => esc_html__( 'No', 'uael' ),
				'return_value' => 'yes',
				'default'      => 'yes',
			)
		);

		$this->add_control(
			'shipping_address_label',
			array(
				'label'     => esc_html__( 'Label', 'uael' ),
				'type'      => Controls_Manager::TEXT,
				'default'   => esc_html__( 'Shipping Address', 'uael' ),
				'condition' => array(
					'show_shipping_address' => 'yes',
				),
			)
		);

		$this->add_control(
			'show_billing_address',
			array(
				'label'        => esc_html__( 'Show Billing Address', 'uael' ),
				'type'         => Controls_Manager::SWITCHER,
				'label_on'     => esc_html__( 'Yes', 'uael' ),
				'label_off'    => esc_html__( 'No', 'uael' ),
				'return_value' => 'yes',
				'default'      => 'no',
			)
		);

		$this->add_control(
			'billing_address_label',
			array(
				'label'     => esc_html__( 'Label', 'uael' ),
				'type'      => Controls_Manager::TEXT,
				'default'   => esc_html__( 'Billing Address', 'uael' ),
				'condition' => array(
					'show_billing_address' => 'yes',
				),
			)
		);

		$this->add_control(
			'show_payment_method',
			array(
				'label'        => esc_html__( 'Show Payment Method', 'uael' ),
				'type'         => Controls_Manager::SWITCHER,
				'label_on'     => esc_html__( 'Yes', 'uael' ),
				'label_off'    => esc_html__( 'No', 'uael' ),
				'return_value' => 'yes',
				'default'      => 'yes',
			)
		);

		$this->add_control(
			'payment_method_label',
			array(
				'label'     => esc_html__( 'Label', 'uael' ),
				'type'      => Controls_Manager::TEXT,
				'default'   => esc_html__( 'Payment Method', 'uael' ),
				'condition' => array(
					'show_payment_method' => 'yes',
				),
			)
		);
		$this->end_controls_section();
	}

	/**
	 * Register Style Tab Controls.
	 *
	 * @since 1.42.0
	 * @access protected
	 */
	protected function register_style_controls() {

		// Confirmation Message Styles.
		$this->register_confirmation_message_styles();

		// Order Information Styles.
		$this->register_order_information_styles();

		// Products & Summary Styles.
		$this->register_products_summary_styles();

		// Button Styles.
		$this->register_buttons_style_controls();

		// Addresses Styles.
		$this->register_address_styles();
	}

	/**
	 * Register Confirmation Message Style Controls.
	 *
	 * @since 1.42.0
	 * @access protected
	 */
	protected function register_confirmation_message_styles() {

		$this->start_controls_section(
			'confirmation_style_section',
			array(
				'label'     => esc_html__( 'Thank You Card', 'uael' ),
				'tab'       => Controls_Manager::TAB_STYLE,
				'condition' => array(
					'show_confirmation_message' => 'yes',
				),
			)
		);

		$this->add_group_control(
			Group_Control_Background::get_type(),
			array(
				'name'     => 'confirmation_background',
				'label'    => esc_html__( 'Background', 'uael' ),
				'types'    => array( 'classic', 'gradient' ),
				'selector' => '{{WRAPPER}} .uael-woo-thankyou-confirmation',
			)
		);

		$this->add_group_control(
			Group_Control_Border::get_type(),
			array(
				'name'     => 'confirmation_border',
				'label'    => esc_html__( 'Border', 'uael' ),
				'selector' => '{{WRAPPER}} .uael-woo-thankyou-container .uael-woo-thankyou-confirmation',
			)
		);

		$this->add_responsive_control(
			'confirmation_border_radius',
			array(
				'label'      => esc_html__( 'Border Radius', 'uael' ),
				'type'       => Controls_Manager::DIMENSIONS,
				'size_units' => array( 'px', '%', 'em' ),
				'selectors'  => array(
					'{{WRAPPER}} .uael-woo-thankyou-container .uael-woo-thankyou-confirmation' => 'border-radius: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
				),
			)
		);

		$this->add_responsive_control(
			'confirmation_padding',
			array(
				'label'      => esc_html__( 'Padding', 'uael' ),
				'type'       => Controls_Manager::DIMENSIONS,
				'size_units' => array( 'px', '%', 'em' ),
				'default'    => array(
					'top'      => 20,
					'right'    => 20,
					'bottom'   => 20,
					'left'     => 20,
					'unit'     => 'px',
					'isLinked' => true,
				),
				'selectors'  => array(
					'{{WRAPPER}} .uael-woo-thankyou-container .uael-woo-thankyou-confirmation' => 'padding: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
				),
			)
		);

		// Alignment Control.
		$this->add_responsive_control(
			'confirmation_alignment',
			array(
				'label'     => esc_html__( 'Alignment', 'uael' ),
				'type'      => Controls_Manager::CHOOSE,
				'options'   => array(
					'flex-start' => array(
						'title' => esc_html__( 'Left', 'uael' ),
						'icon'  => 'eicon-text-align-left',
					),
					'center'     => array(
						'title' => esc_html__( 'Center', 'uael' ),
						'icon'  => 'eicon-text-align-center',
					),
					'flex-end'   => array(
						'title' => esc_html__( 'Right', 'uael' ),
						'icon'  => 'eicon-text-align-right',
					),
				),
				'default'   => 'center',
				'selectors' => array(
					'{{WRAPPER}} .uael-woo-thankyou-confirmation' => 'align-items: {{VALUE}} !important;',
				),
			)
		);

		// Icon Settings.
		$this->add_control(
			'confirmation_icon_heading',
			array(
				'label'     => esc_html__( 'Icon', 'uael' ),
				'type'      => Controls_Manager::HEADING,
				'separator' => 'before',
				'condition' => array(
					'confirmation_icon[value]!' => '',
				),
			)
		);


		$this->add_control(
			'confirmation_icon_color',
			array(
				'label'     => esc_html__( 'Color', 'uael' ),
				'type'      => Controls_Manager::COLOR,
				'default'   => '#4CAF50',
				'selectors' => array(
					'{{WRAPPER}} .uael-woo-thankyou-confirmation .uael-confirmation-icon' => 'color: {{VALUE}};',
					'{{WRAPPER}} .uael-woo-thankyou-confirmation .uael-confirmation-icon svg' => 'fill: {{VALUE}};',
				),
				'condition' => array(
					'confirmation_icon[value]!' => '',
				),
			)
		);

		$this->add_control(
			'confirmation_icon_hover_color',
			array(
				'label'     => esc_html__( 'Hover Color', 'uael' ),
				'type'      => Controls_Manager::COLOR,
				'selectors' => array(
					'{{WRAPPER}} .uael-woo-thankyou-confirmation .uael-confirmation-icon:hover' => 'color: {{VALUE}};',
					'{{WRAPPER}} .uael-woo-thankyou-confirmation .uael-confirmation-icon:hover svg' => 'fill: {{VALUE}};',
				),
				'condition' => array(
					'confirmation_icon[value]!' => '',
				),
			)
		);

		$this->add_responsive_control(
			'confirmation_icon_spacing',
			array(
				'label'      => esc_html__( 'Spacing', 'uael' ),
				'type'       => Controls_Manager::SLIDER,
				'size_units' => array( 'px', 'em', 'rem' ),
				'range'      => array(
					'px' => array(
						'min' => 0,
						'max' => 100,
					),
				),
				'default'    => array(
					'size' => 10,
					'unit' => 'px',
				),
				'selectors'  => array(
					'{{WRAPPER}} .uael-woo-thankyou-confirmation .uael-confirmation-icon' => 'margin-bottom: {{SIZE}}{{UNIT}};',
				),
				'condition'  => array(
					'confirmation_icon[value]!' => '',
				),
			)
		);

		// Icon Settings.
		$this->add_control(
			'confirmation_content_heading',
			array(
				'label'     => esc_html__( 'Content', 'uael' ),
				'type'      => Controls_Manager::HEADING,
				'separator' => 'before',
			)
		);

		$this->add_group_control(
			Group_Control_Typography::get_type(),
			array(
				'name'     => 'confirmation_message_typography',
				'label'    => esc_html__( 'Title Typography', 'uael' ),
				'global'   => array(
					'default' => Global_Typography::TYPOGRAPHY_PRIMARY,
				),
				'selector' => '{{WRAPPER}} .uael-woo-thankyou-confirmation .uael-confirmation-message',
			)
		);

		$this->add_control(
			'confirmation_message_color',
			array(
				'label'     => esc_html__( 'Title Color', 'uael' ),
				'type'      => Controls_Manager::COLOR,
				'global'    => array(
					'default' => Global_Colors::COLOR_PRIMARY,
				),
				'selectors' => array(
					'{{WRAPPER}} .uael-woo-thankyou-confirmation .uael-confirmation-message' => 'color: {{VALUE}};',
				),
			)
		);

		$this->add_responsive_control(
			'confirmation_title_bottom_padding',
			array(
				'label'      => esc_html__( 'Title Bottom Spacing', 'uael' ),
				'type'       => Controls_Manager::SLIDER,
				'size_units' => array( 'px', '%' ),
				'range'      => array(
					'px' => array(
						'min'  => 0,
						'max'  => 100,
						'step' => 1,
					),
					'%'  => array(
						'min' => 0,
						'max' => 100,
					),
				),
				'default'    => array(
					'unit' => 'px',
					'size' => 10,
				),
				'selectors'  => array(
					'{{WRAPPER}} .uael-woo-thankyou-confirmation .uael-confirmation-message' => 'margin-bottom: {{SIZE}}{{UNIT}};',
				),
			)
		);

		$this->add_group_control(
			Group_Control_Typography::get_type(),
			array(
				'name'     => 'confirmation_description_typography',
				'label'    => esc_html__( 'Description Typography', 'uael' ),
				'global'   => array(
					'default' => Global_Typography::TYPOGRAPHY_TEXT,
				),
				'selector' => '{{WRAPPER}} .uael-woo-thankyou-confirmation .uael-confirmation-description',
			)
		);

		$this->add_control(
			'confirmation_description_color',
			array(
				'label'     => esc_html__( 'Description Color', 'uael' ),
				'type'      => Controls_Manager::COLOR,
				'default'   => '#ffffff',
				'selectors' => array(
					'{{WRAPPER}} .uael-woo-thankyou-confirmation .uael-confirmation-description' => 'color: {{VALUE}};',
				),
			)
		);


		$this->end_controls_section();

		// General / Global Settings Section.
		$this->start_controls_section(
			'general_settings_section',
			array(
				'label' => esc_html__( 'Global Settings', 'uael' ),
				'tab'   => Controls_Manager::TAB_STYLE,
			)
		);


		// Global Title Typography.
		$this->add_group_control(
			Group_Control_Typography::get_type(),
			array(
				'name'     => 'global_title_typography',
				'label'    => esc_html__( 'Title Typography', 'uael' ),
				'global'   => array(
					'default' => Global_Typography::TYPOGRAPHY_PRIMARY,
				),
				'selector' => '{{WRAPPER}} .uael-products-title, {{WRAPPER}} .uael-address-title, {{WRAPPER}} .uael-summary-title',
			)
		);

		// Global Title Color.
		$this->add_control(
			'global_title_color',
			array(
				'label'     => esc_html__( 'Title Color', 'uael' ),
				'type'      => Controls_Manager::COLOR,
				'global'    => array(
					'default' => Global_Colors::COLOR_TEXT,
				),
				'selectors' => array(
					'{{WRAPPER}} .uael-products-title' => 'color: {{VALUE}};',
					'{{WRAPPER}} .uael-address-title'  => 'color: {{VALUE}};',
					'{{WRAPPER}} .uael-summary-title'  => 'color: {{VALUE}};',
				),
			)
		);

		// Global Title & Content Spacing (applies to Addresses, Products, and Summary sections).
		$this->add_responsive_control(
			'global_title_spacing',
			array(
				'label'      => esc_html__( 'Title Bottom Spacing', 'uael' ),
				'type'       => Controls_Manager::SLIDER,
				'size_units' => array( 'px', 'em', '%' ),
				'range'      => array(
					'px' => array(
						'min'  => 0,
						'max'  => 100,
						'step' => 1,
					),
					'em' => array(
						'min'  => 0,
						'max'  => 10,
						'step' => 0.1,
					),
					'%'  => array(
						'min'  => 0,
						'max'  => 100,
						'step' => 1,
					),
				),
				'default'    => array(
					'size' => 14,
					'unit' => 'px',
				),
				'selectors'  => array(
					'{{WRAPPER}} .uael-products-title' => 'margin-bottom: {{SIZE}}{{UNIT}};',
					'{{WRAPPER}} .uael-address-title'  => 'margin-bottom: {{SIZE}}{{UNIT}};',
					'{{WRAPPER}} .uael-summary-title'  => 'margin-bottom: {{SIZE}}{{UNIT}};',
				),
			)
		);

		// Space between Products ↔ Order Summary.
		$this->add_responsive_control(
			'cards_spacing',
			array(
				'label'      => esc_html__( 'Spacing Between Cards', 'uael' ),
				'type'       => Controls_Manager::SLIDER,
				'size_units' => array( 'px' ),
				'range'      => array(
					'px' => array(
						'min'  => 0,
						'max'  => 100,
						'step' => 1,
					),
				),
				'default'    => array(
					'size' => 24,
					'unit' => 'px',
				),
				'selectors'  => array(
					'{{WRAPPER}} .uael-woo-thankyou-addresses' => 'gap: {{SIZE}}{{UNIT}};',
					'{{WRAPPER}} .uael-woo-thankyou-order-info' => 'margin-top: {{SIZE}}{{UNIT}};',
					'{{WRAPPER}} .uael-woo-thankyou-main-section' => 'margin-top: {{SIZE}}{{UNIT}}; gap: {{SIZE}}{{UNIT}};',
					'{{WRAPPER}} .uael-woo-thankyou-actions' => 'margin-top: {{SIZE}}{{UNIT}};',
				),
			)
		);

		// Inner Card Padding (Global).
		$this->add_responsive_control(
			'inner_card_padding',
			array(
				'label'      => esc_html__( 'Card Padding', 'uael' ),
				'type'       => Controls_Manager::DIMENSIONS,
				'default'    => array(
					'top'      => 20,
					'right'    => 20,
					'bottom'   => 20,
					'left'     => 20,
					'unit'     => 'px',
					'isLinked' => true,
				),
				'size_units' => array( 'px', '%', 'em' ),
				'selectors'  => array(
					'{{WRAPPER}} .uael-woo-thankyou-products'  => 'padding: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
					'{{WRAPPER}} .uael-woo-thankyou-summary' => 'padding: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
					'{{WRAPPER}} .uael-woo-thankyou-confirmation'  => 'padding: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
					'{{WRAPPER}} .uael-woo-thankyou-order-info' => 'padding: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
					'{{WRAPPER}} .uael-woo-thankyou-billing-address'  => 'padding: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
					'{{WRAPPER}} .uael-woo-thankyou-shipping-address' => 'padding: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
					'{{WRAPPER}} .uael-woo-thankyou-payment-method' => 'padding: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
				),
			)
		);

		// Border & Shadow Styling.
		$this->add_control(
			'border_shadow_heading',
			array(
				'label'     => esc_html__( 'Border & Shadow', 'uael' ),
				'type'      => Controls_Manager::HEADING,
				'separator' => 'before',
			)
		);

		// Global Border Type.
		$this->add_group_control(
			Group_Control_Border::get_type(),
			array(
				'name'     => 'global_card_border',
				'label'    => esc_html__( 'Border', 'uael' ),
				'selector' => '{{WRAPPER}} .uael-woo-thankyou-products, {{WRAPPER}} .uael-woo-thankyou-summary, {{WRAPPER}} .uael-woo-thankyou-billing-address, {{WRAPPER}} .uael-woo-thankyou-shipping-address, {{WRAPPER}} .uael-woo-thankyou-payment-method, {{WRAPPER}} .uael-woo-thankyou-order-info, {{WRAPPER}} .uael-woo-thankyou-confirmation',
			)
		);

		// Global Border Radius.
		$this->add_responsive_control(
			'global_card_border_radius',
			array(
				'label'      => esc_html__( 'Border Radius', 'uael' ),
				'type'       => Controls_Manager::DIMENSIONS,
				'size_units' => array( 'px', '%', 'em' ),
				'selectors'  => array(
					'{{WRAPPER}} .uael-woo-thankyou-products, {{WRAPPER}} .uael-woo-thankyou-summary, {{WRAPPER}} .uael-woo-thankyou-billing-address, {{WRAPPER}} .uael-woo-thankyou-shipping-address, {{WRAPPER}} .uael-woo-thankyou-payment-method, {{WRAPPER}} .uael-woo-thankyou-order-info, {{WRAPPER}} .uael-woo-thankyou-confirmation' => 'border-radius: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
				),
			)
		);

		// Global Box Shadow.
		$this->add_group_control(
			Group_Control_Box_Shadow::get_type(),
			array(
				'name'     => 'global_card_box_shadow',
				'label'    => esc_html__( 'Box Shadow', 'uael' ),
				'selector' => '{{WRAPPER}} .uael-woo-thankyou-products, {{WRAPPER}} .uael-woo-thankyou-summary, {{WRAPPER}} .uael-woo-thankyou-billing-address, {{WRAPPER}} .uael-woo-thankyou-shipping-address, {{WRAPPER}} .uael-woo-thankyou-payment-method, {{WRAPPER}} .uael-woo-thankyou-order-info, {{WRAPPER}} .uael-woo-thankyou-confirmation',
			)
		);

		// Enable Animations Toggle.
		$this->add_control(
			'enable_animations',
			array(
				'label'        => esc_html__( 'Enable Animations', 'uael' ),
				'type'         => Controls_Manager::SWITCHER,
				'label_on'     => esc_html__( 'Yes', 'uael' ),
				'label_off'    => esc_html__( 'No', 'uael' ),
				'return_value' => 'yes',
				'default'      => 'yes',
			)
		);

		// Load Animation Heading.
		$this->add_control(
			'load_animation_heading',
			array(
				'label'     => esc_html__( 'Load Animation', 'uael' ),
				'type'      => Controls_Manager::HEADING,
				'separator' => 'before',
				'condition' => array(
					'enable_animations' => 'yes',
				),
			)
		);

		// Global Animation Type.
		$this->add_control(
			'global_animation_type',
			array(
				'label'     => esc_html__( 'Animation Type', 'uael' ),
				'type'      => Controls_Manager::SELECT,
				'options'   => array(
					'none'         => esc_html__( 'None', 'uael' ),
					'fadeInUp'     => esc_html__( 'Fade In Up', 'uael' ),
					'slideInUp'    => esc_html__( 'Slide In Up', 'uael' ),
					'slideInLeft'  => esc_html__( 'Slide In Left', 'uael' ),
					'slideInRight' => esc_html__( 'Slide In Right', 'uael' ),
					'zoomIn'       => esc_html__( 'Zoom In', 'uael' ),
					'bounceIn'     => esc_html__( 'Bounce In', 'uael' ),
					'rotateIn'     => esc_html__( 'Rotate In', 'uael' ),
					'flipInX'      => esc_html__( 'Flip In X', 'uael' ),
				),
				'default'   => 'fadeInUp',
				'condition' => array(
					'enable_animations' => 'yes',
				),
			)
		);

		// Global Animation Duration.
		$this->add_control(
			'global_animation_duration',
			array(
				'label'     => esc_html__( 'Animation Duration (ms)', 'uael' ),
				'type'      => Controls_Manager::SLIDER,
				'range'     => array(
					'ms' => array(
						'min'  => 300,
						'max'  => 3000,
						'step' => 100,
					),
				),
				'default'   => array(
					'size' => 900,
					'unit' => 'ms',
				),
				'condition' => array(
					'enable_animations'      => 'yes',
					'global_animation_type!' => 'none',
				),
			)
		);

		// Global Animation Delay.
		$this->add_control(
			'global_animation_delay',
			array(
				'label'     => esc_html__( 'Animation Delay (ms)', 'uael' ),
				'type'      => Controls_Manager::SLIDER,
				'range'     => array(
					'ms' => array(
						'min'  => 0,
						'max'  => 2000,
						'step' => 100,
					),
				),
				'default'   => array(
					'size' => 0,
					'unit' => 'ms',
				),
				'condition' => array(
					'enable_animations'      => 'yes',
					'global_animation_type!' => 'none',
				),
			)
		);

		// Hover Animation Heading.
		$this->add_control(
			'hover_animation_heading',
			array(
				'label'     => esc_html__( 'Hover Animation', 'uael' ),
				'type'      => Controls_Manager::HEADING,
				'separator' => 'before',
				'condition' => array(
					'enable_animations' => 'yes',
				),
			)
		);

		// Hover Animation Type.
		$this->add_control(
			'hover_animation_type',
			array(
				'label'     => esc_html__( 'Hover Animation Type', 'uael' ),
				'type'      => Controls_Manager::SELECT,
				'options'   => array(
					'none'        => esc_html__( 'None', 'uael' ),
					'lift'        => esc_html__( 'Lift', 'uael' ),
					'shadow'      => esc_html__( 'Shadow Glow', 'uael' ),
					'shadow-lift' => esc_html__( 'Shadow + Lift', 'uael' ),
				),
				'default'   => 'shadow-lift',
				'condition' => array(
					'enable_animations' => 'yes',
				),
			)
		);

		// Hover Animation Duration.
		$this->add_control(
			'hover_animation_duration',
			array(
				'label'     => esc_html__( 'Hover Animation Duration (ms)', 'uael' ),
				'type'      => Controls_Manager::SLIDER,
				'range'     => array(
					'ms' => array(
						'min'  => 100,
						'max'  => 1000,
						'step' => 50,
					),
				),
				'default'   => array(
					'size' => 300,
					'unit' => 'ms',
				),
				'condition' => array(
					'enable_animations'     => 'yes',
					'hover_animation_type!' => 'none',
				),
			)
		);

		// Hover Animation Delay.
		$this->add_control(
			'hover_animation_delay',
			array(
				'label'     => esc_html__( 'Hover Animation Delay (ms)', 'uael' ),
				'type'      => Controls_Manager::SLIDER,
				'range'     => array(
					'ms' => array(
						'min'  => 0,
						'max'  => 500,
						'step' => 50,
					),
				),
				'default'   => array(
					'size' => 0,
					'unit' => 'ms',
				),
				'condition' => array(
					'enable_animations'     => 'yes',
					'hover_animation_type!' => 'none',
				),
			)
		);

		$this->end_controls_section();
	}

	/**
	 * Register Order Information Style Controls.
	 *
	 * @since 1.42.0
	 * @access protected
	 */
	protected function register_order_information_styles() {

		$this->start_controls_section(
			'order_info_style_section',
			array(
				'label'     => esc_html__( 'Order Details', 'uael' ),
				'tab'       => Controls_Manager::TAB_STYLE,
				'condition' => array(
					'show_order_info' => 'yes',
				),
			)
		);

		// Card Background (Always visible, not conditional).
		$this->add_group_control(
			Group_Control_Background::get_type(),
			array(
				'name'     => 'order_info_background',
				'label'    => esc_html__( 'Background', 'uael' ),
				'types'    => array( 'classic', 'gradient' ),
				'selector' => '{{WRAPPER}} .uael-woo-thankyou-order-info',
			)
		);

		// Override Global Settings Toggle.
		$this->add_control(
			'override_order_info_styles',
			array(
				'label'        => esc_html__( 'Override Global Settings', 'uael' ),
				'type'         => Controls_Manager::SWITCHER,
				'label_on'     => esc_html__( 'Yes', 'uael' ),
				'label_off'    => esc_html__( 'No', 'uael' ),
				'return_value' => 'yes',
				'default'      => 'no',
			)
		);

		$this->add_group_control(
			Group_Control_Border::get_type(),
			array(
				'name'      => 'order_info_border',
				'label'     => esc_html__( 'Border', 'uael' ),
				'selector'  => '{{WRAPPER}} .uael-woo-thankyou-container .uael-woo-thankyou-order-info',
				'condition' => array(
					'override_order_info_styles' => 'yes',
				),
			)
		);

		$this->add_responsive_control(
			'order_info_border_radius',
			array(
				'label'      => esc_html__( 'Border Radius', 'uael' ),
				'type'       => Controls_Manager::DIMENSIONS,
				'size_units' => array( 'px', '%', 'em' ),
				'selectors'  => array(
					'{{WRAPPER}} .uael-woo-thankyou-container .uael-woo-thankyou-order-info' => 'border-radius: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}} !important;',
				),
				'condition'  => array(
					'override_order_info_styles' => 'yes',
				),
			)
		);

		$this->add_responsive_control(
			'order_info_padding',
			array(
				'label'      => esc_html__( 'Padding', 'uael' ),
				'type'       => Controls_Manager::DIMENSIONS,
				'size_units' => array( 'px', '%', 'em' ),
				'selectors'  => array(
					'{{WRAPPER}} .uael-woo-thankyou-container .uael-woo-thankyou-order-info' => 'padding: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}} !important;',
				),
				'condition'  => array(
					'override_order_info_styles' => 'yes',
				),
			)
		);

		// Labels Typography.
		$this->add_control(
			'order_info_labels_heading',
			array(
				'label'     => esc_html__( 'Labels', 'uael' ),
				'type'      => Controls_Manager::HEADING,
				'separator' => 'before',
			)
		);

		$this->add_group_control(
			Group_Control_Typography::get_type(),
			array(
				'name'     => 'order_info_labels_typography',
				'label'    => esc_html__( 'Typography', 'uael' ),
				'global'   => array(
					'default' => Global_Typography::TYPOGRAPHY_TEXT,
				),
				'selector' => '{{WRAPPER}} .uael-woo-thankyou-order-info .uael-order-info-label',
			)
		);

		$this->add_control(
			'order_info_labels_color',
			array(
				'label'     => esc_html__( 'Color', 'uael' ),
				'type'      => Controls_Manager::COLOR,
				'global'    => array(
					'default' => Global_Colors::COLOR_TEXT,
				),
				'selectors' => array(
					'{{WRAPPER}} .uael-woo-thankyou-order-info .uael-order-info-label' => 'color: {{VALUE}};',
				),
			)
		);

		// Details Typography.
		$this->add_control(
			'order_info_details_heading',
			array(
				'label'     => esc_html__( 'Details', 'uael' ),
				'type'      => Controls_Manager::HEADING,
				'separator' => 'before',
			)
		);

		$this->add_group_control(
			Group_Control_Typography::get_type(),
			array(
				'name'     => 'order_info_details_typography',
				'label'    => esc_html__( 'Typography', 'uael' ),
				'global'   => array(
					'default' => Global_Typography::TYPOGRAPHY_TEXT,
				),
				'selector' => '{{WRAPPER}} .uael-woo-thankyou-order-info .uael-order-info-value',
			)
		);

		$this->add_control(
			'order_info_details_color',
			array(
				'label'     => esc_html__( 'Color', 'uael' ),
				'type'      => Controls_Manager::COLOR,
				'global'    => array(
					'default' => Global_Colors::COLOR_TEXT,
				),
				'selectors' => array(
					'{{WRAPPER}} .uael-woo-thankyou-order-info .uael-order-info-value' => 'color: {{VALUE}};',
				),
			)
		);

		$this->end_controls_section();
	}

	/**
	 * Register Products & Summary Style Controls.
	 *
	 * @since 1.42.0
	 * @access protected
	 */
	protected function register_products_summary_styles() {

		// Products Section..
		$this->start_controls_section(
			'products_style_section',
			array(
				'label' => esc_html__( 'Products', 'uael' ),
				'tab'   => Controls_Manager::TAB_STYLE,
			)
		);

		// Products Card Background.
		$this->add_group_control(
			Group_Control_Background::get_type(),
			array(
				'name'     => 'products_background',
				'label'    => esc_html__( 'Background', 'uael' ),
				'types'    => array( 'classic', 'gradient' ),
				'selector' => '{{WRAPPER}} .uael-woo-thankyou-products',
			)
		);

		// Products Title Typography.
		$this->add_group_control(
			Group_Control_Typography::get_type(),
			array(
				'name'     => 'products_title_typography',
				'label'    => esc_html__( 'Title Typography', 'uael' ),
				'global'   => array(
					'default' => Global_Typography::TYPOGRAPHY_PRIMARY,
				),
				'selector' => '{{WRAPPER}} .uael-products-title',
			)
		);

		$this->add_control(
			'products_title_color',
			array(
				'label'     => esc_html__( 'Title Color', 'uael' ),
				'type'      => Controls_Manager::COLOR,
				'selectors' => array(
					'{{WRAPPER}} .uael-products-title' => 'color: {{VALUE}} !important;',
				),
			)
		);


		// Product Name Typography.
		$this->add_group_control(
			Group_Control_Typography::get_type(),
			array(
				'name'     => 'product_name_typography',
				'label'    => esc_html__( 'Product Name Typography', 'uael' ),
				'global'   => array(
					'default' => Global_Typography::TYPOGRAPHY_PRIMARY,
				),
				'selector' => '{{WRAPPER}} .uael-product-name',
			)
		);

		$this->add_control(
			'product_name_color',
			array(
				'label'     => esc_html__( 'Product Name Color', 'uael' ),
				'type'      => Controls_Manager::COLOR,
				'global'    => array(
					'default' => Global_Colors::COLOR_TEXT,
				),
				'selectors' => array(
					'{{WRAPPER}} .uael-product-name' => 'color: {{VALUE}};',
				),
			)
		);

		// Product Price. Typography.
		$this->add_group_control(
			Group_Control_Typography::get_type(),
			array(
				'name'     => 'product_price_typography',
				'label'    => esc_html__( 'Price Typography', 'uael' ),
				'global'   => array(
					'default' => Global_Typography::TYPOGRAPHY_TEXT,
				),
				'selector' => '{{WRAPPER}} .uael-product-price',
			)
		);

		$this->add_control(
			'product_price_color',
			array(
				'label'     => esc_html__( 'Price Color', 'uael' ),
				'type'      => Controls_Manager::COLOR,
				'global'    => array(
					'default' => Global_Colors::COLOR_TEXT,
				),
				'selectors' => array(
					'{{WRAPPER}} .uael-product-price' => 'color: {{VALUE}};',
				),
			)
		);

		// Product Quantity Typography.
		$this->add_group_control(
			Group_Control_Typography::get_type(),
			array(
				'name'     => 'product_quantity_typography',
				'label'    => esc_html__( 'Quantity Typography', 'uael' ),
				'global'   => array(
					'default' => Global_Typography::TYPOGRAPHY_TEXT,
				),
				'selector' => '{{WRAPPER}} .uael-product-qty',
			)
		);

		$this->add_control(
			'product_quantity_color',
			array(
				'label'     => esc_html__( 'Quantity Color', 'uael' ),
				'type'      => Controls_Manager::COLOR,
				'global'    => array(
					'default' => Global_Colors::COLOR_TEXT,
				),
				'selectors' => array(
					'{{WRAPPER}} .uael-product-qty' => 'color: {{VALUE}};',
				),
			)
		);

		$this->end_controls_section();

		// Order Summary Section..
		$this->start_controls_section(
			'order_summary_style_section',
			array(
				'label' => esc_html__( 'Order Summary', 'uael' ),
				'tab'   => Controls_Manager::TAB_STYLE,
			)
		);

		// Summary Card Background.
		$this->add_group_control(
			Group_Control_Background::get_type(),
			array(
				'name'     => 'summary_background',
				'label'    => esc_html__( 'Background', 'uael' ),
				'types'    => array( 'classic', 'gradient' ),
				'selector' => '{{WRAPPER}} .uael-woo-thankyou-summary',
			)
		);

		// Summary Title Typography.
		$this->add_group_control(
			Group_Control_Typography::get_type(),
			array(
				'name'     => 'summary_title_typography',
				'label'    => esc_html__( 'Title Typography', 'uael' ),
				'global'   => array(
					'default' => Global_Typography::TYPOGRAPHY_PRIMARY,
				),
				'selector' => '{{WRAPPER}} .uael-summary-title',
			)
		);

		$this->add_control(
			'summary_title_color',
			array(
				'label'     => esc_html__( 'Title Color', 'uael' ),
				'type'      => Controls_Manager::COLOR,
				'selectors' => array(
					'{{WRAPPER}} .uael-summary-title' => 'color: {{VALUE}} !important;',
				),
			)
		);

		// Summary Row Typography.
		$this->add_group_control(
			Group_Control_Typography::get_type(),
			array(
				'name'     => 'summary_row_typography',
				'label'    => esc_html__( 'Row Typography', 'uael' ),
				'global'   => array(
					'default' => Global_Typography::TYPOGRAPHY_TEXT,
				),
				'selector' => '{{WRAPPER}} .uael-summary-label, {{WRAPPER}} .uael-summary-value',
			)
		);

		$this->add_control(
			'summary_label_color',
			array(
				'label'     => esc_html__( 'Label Color', 'uael' ),
				'type'      => Controls_Manager::COLOR,
				'global'    => array(
					'default' => Global_Colors::COLOR_TEXT,
				),
				'selectors' => array(
					'{{WRAPPER}} .uael-summary-label' => 'color: {{VALUE}};',
				),
			)
		);

		$this->add_control(
			'summary_value_color',
			array(
				'label'     => esc_html__( 'Value Color', 'uael' ),
				'type'      => Controls_Manager::COLOR,
				'global'    => array(
					'default' => Global_Colors::COLOR_TEXT,
				),
				'selectors' => array(
					'{{WRAPPER}} .uael-summary-value' => 'color: {{VALUE}};',
				),
			)
		);

		$this->end_controls_section();
	}

	/**
	 * Register Button Style Controls.
	 *
	 * @since 1.42.0
	 * @access protected
	 */
	protected function register_buttons_style_controls() {

		$this->start_controls_section(
			'buttons_style_section',
			array(
				'label' => esc_html__( 'Buttons', 'uael' ),
				'tab'   => Controls_Manager::TAB_STYLE,
			)
		);

		// Typography Group Control.
		$this->add_group_control(
			Group_Control_Typography::get_type(),
			array(
				'name'     => 'buttons_typography',
				'label'    => esc_html__( 'Typography', 'uael' ),
				'global'   => array(
					'default' => Global_Typography::TYPOGRAPHY_ACCENT,
				),
				'selector' => '{{WRAPPER}} .uael-action-button',
			)
		);

		// Border.
		$this->add_group_control(
			Group_Control_Border::get_type(),
			array(
				'name'     => 'buttons_border',
				'label'    => esc_html__( 'Border', 'uael' ),
				'selector' => '{{WRAPPER}} .uael-action-button',
			)
		);

		// Border Radius.
		$this->add_responsive_control(
			'buttons_border_radius',
			array(
				'label'      => esc_html__( 'Border Radius', 'uael' ),
				'type'       => Controls_Manager::DIMENSIONS,
				'size_units' => array( 'px', '%', 'em' ),
				'default'    => array(
					'top'      => 12,
					'right'    => 12,
					'bottom'   => 12,
					'left'     => 12,
					'unit'     => 'px',
					'isLinked' => true,
				),
				'selectors'  => array(
					'{{WRAPPER}} .uael-action-button' => 'border-radius: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
				),
			)
		);

		// Normal State Tab.
		$this->start_controls_tabs( 'buttons_style_tabs' );

		$this->start_controls_tab(
			'buttons_normal_tab',
			array(
				'label' => esc_html__( 'Normal', 'uael' ),
			)
		);

		// Text Color.
		$this->add_control(
			'buttons_text_color',
			array(
				'label'     => esc_html__( 'Text Color', 'uael' ),
				'type'      => Controls_Manager::COLOR,
				'global'    => array(
					'default' => Global_Colors::COLOR_TEXT,
				),
				'selectors' => array(
					'{{WRAPPER}} .uael-action-button' => 'color: {{VALUE}};',
				),
			)
		);

		// Background Color.
		$this->add_control(
			'buttons_background_color',
			array(
				'label'     => esc_html__( 'Background Color', 'uael' ),
				'type'      => Controls_Manager::COLOR,
				'global'    => array(
					'default' => Global_Colors::COLOR_ACCENT,
				),
				'selectors' => array(
					'{{WRAPPER}} .uael-action-button' => 'background: {{VALUE}} !important;',
				),
			)
		);

		// Box Shadow.
		$this->add_group_control(
			Group_Control_Box_Shadow::get_type(),
			array(
				'name'     => 'buttons_box_shadow',
				'label'    => esc_html__( 'Box Shadow', 'uael' ),
				'selector' => '{{WRAPPER}} .uael-action-button',
			)
		);

		
		$this->end_controls_tab();

		// Hover State Tab.
		$this->start_controls_tab(
			'buttons_hover_tab',
			array(
				'label' => esc_html__( 'Hover', 'uael' ),
			)
		);

		// Hover Text Color.
		$this->add_control(
			'buttons_hover_text_color',
			array(
				'label'     => esc_html__( 'Text Color', 'uael' ),
				'type'      => Controls_Manager::COLOR,
				'selectors' => array(
					'{{WRAPPER}} .uael-action-button:hover' => 'color: {{VALUE}};',
				),
			)
		);

		// Hover Background Color.
		$this->add_control(
			'buttons_hover_background_color',
			array(
				'label'     => esc_html__( 'Background Color', 'uael' ),
				'type'      => Controls_Manager::COLOR,
				'selectors' => array(
					'{{WRAPPER}} .uael-action-button:hover' => 'background: {{VALUE}} !important;',
				),
			)
		);

		// Hover Border Color.
		$this->add_control(
			'buttons_hover_border_color',
			array(
				'label'     => esc_html__( 'Border Color', 'uael' ),
				'type'      => Controls_Manager::COLOR,
				'selectors' => array(
					'{{WRAPPER}} .uael-action-button:hover' => 'border-color: {{VALUE}};',
				),
			)
		);

		// Hover Box Shadow.
		$this->add_group_control(
			Group_Control_Box_Shadow::get_type(),
			array(
				'name'     => 'buttons_hover_box_shadow',
				'label'    => esc_html__( 'Box Shadow', 'uael' ),
				'selector' => '{{WRAPPER}} .uael-action-button:hover',
			)
		);

		$this->end_controls_tab();

		$this->end_controls_tabs();


		// Padding.
		$this->add_responsive_control(
			'buttons_padding',
			array(
				'label'      => esc_html__( 'Padding', 'uael' ),
				'type'       => Controls_Manager::DIMENSIONS,
				'size_units' => array( 'px', '%', 'em' ),
				'selectors'  => array(
					'{{WRAPPER}} .uael-action-button' => 'padding: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
				),
			)
		);

		// Buttons Spacing.
		$this->add_responsive_control(
			'buttons_spacing',
			array(
				'label'      => esc_html__( 'Spacing Between Buttons', 'uael' ),
				'type'       => Controls_Manager::SLIDER,
				'size_units' => array( 'px' ),
				'range'      => array(
					'px' => array(
						'min'  => 0,
						'max'  => 100,
						'step' => 1,
					),
				),
				'default'    => array(
					'size' => 12,
					'unit' => 'px',
				),
				'selectors'  => array(
					'{{WRAPPER}} .uael-woo-thankyou-actions' => 'gap: {{SIZE}}{{UNIT}};',
				),
			)
		);

		$this->end_controls_section();
	}

	/**
	 * Register Helpful Information Section.
	 *
	 * @since 1.42.0
	 * @access protected
	 */
	protected function register_helpful_information() {

		$link = 'https://docs.ultimate-elementor.com/woo-thank-you-page/?utm_source=uael-editor&utm_medium=editor&utm_campaign=woo-thank-you';

		if ( parent::is_internal_links() ) {
			$this->start_controls_section(
				'helpful_info_section',
				array(
					'label' => esc_html__( 'Helpful Information', 'uael' ),
					'tab'   => Controls_Manager::TAB_CONTENT,
				)
			);

			$this->add_control(
				'help_doc_1',
				array(
					'type'            => Controls_Manager::RAW_HTML,
					'raw'             => sprintf(
						'<a href="%s" target="_blank" rel="noopener">%s</a>',
						esc_url( $link ),
						esc_html__( 'Getting started article »', 'uael' )
					),
					'content_classes' => 'uael-editor-doc',
				)
			);

			$this->end_controls_section();
		}
	}

	/**
	 * Register Address & Payment Style Controls.
	 *
	 * @since 1.42.0
	 * @access protected
	 */
	protected function register_address_styles() {

		// Addresses & Payment Section (Unified).
		$this->start_controls_section(
			'addresses_payment_style_section',
			array(
				'label' => esc_html__( 'Addresses & Payment', 'uael' ),
				'tab'   => Controls_Manager::TAB_STYLE,
			)
		);

		// Card Styling (applies to all address and payment boxes).
		$this->add_control(
			'addresses_card_heading',
			array(
				'label'     => esc_html__( 'Card Style', 'uael' ),
				'type'      => Controls_Manager::HEADING,
				'separator' => 'before',
			)
		);

		// Global Card Background.
		$this->add_group_control(
			Group_Control_Background::get_type(),
			array(
				'name'     => 'addresses_card_background',
				'label'    => esc_html__( 'Background', 'uael' ),
				'types'    => array( 'classic', 'gradient' ),
				'selector' => '{{WRAPPER}} .uael-woo-thankyou-shipping-address, {{WRAPPER}} .uael-woo-thankyou-billing-address, {{WRAPPER}} .uael-woo-thankyou-payment-method',
			)
		);

		// Override Global Settings Toggle.
		$this->add_control(
			'override_addresses_styles',
			array(
				'label'        => esc_html__( 'Override Global Settings', 'uael' ),
				'type'         => Controls_Manager::SWITCHER,
				'label_on'     => esc_html__( 'Yes', 'uael' ),
				'label_off'    => esc_html__( 'No', 'uael' ),
				'return_value' => 'yes',
				'default'      => 'no',
			)
		);

		// Global Card Border.
		$this->add_group_control(
			Group_Control_Border::get_type(),
			array(
				'name'      => 'addresses_card_border',
				'label'     => esc_html__( 'Border', 'uael' ),
				'selector'  => '{{WRAPPER}} .uael-woo-thankyou-shipping-address, {{WRAPPER}} .uael-woo-thankyou-billing-address, {{WRAPPER}} .uael-woo-thankyou-payment-method',
				'condition' => array(
					'override_addresses_styles' => 'yes',
				),
			)
		);

		// Global Card Border. Radius.
		$this->add_responsive_control(
			'addresses_card_border_radius',
			array(
				'label'      => esc_html__( 'Border Radius', 'uael' ),
				'type'       => Controls_Manager::DIMENSIONS,
				'size_units' => array( 'px', '%', 'em' ),
				'selectors'  => array(
					'{{WRAPPER}} .uael-woo-thankyou-shipping-address, {{WRAPPER}} .uael-woo-thankyou-billing-address, {{WRAPPER}} .uael-woo-thankyou-payment-method' => 'border-radius: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
				),
				'condition'  => array(
					'override_addresses_styles' => 'yes',
				),
			)
		);

		// Global Card Padding.
		$this->add_responsive_control(
			'addresses_card_padding',
			array(
				'label'      => esc_html__( 'Padding', 'uael' ),
				'type'       => Controls_Manager::DIMENSIONS,
				'size_units' => array( 'px', '%', 'em' ),
				'default'    => array(
					'top'      => 24,
					'right'    => 24,
					'bottom'   => 24,
					'left'     => 24,
					'unit'     => 'px',
					'isLinked' => true,
				),
				'selectors'  => array(
					'{{WRAPPER}} .uael-woo-thankyou-shipping-address, {{WRAPPER}} .uael-woo-thankyou-billing-address, {{WRAPPER}} .uael-woo-thankyou-payment-method' => 'padding: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
				),
				'condition'  => array(
					'override_addresses_styles' => 'yes',
				),
			)
		);

		// Global Card Box Shadow.
		$this->add_group_control(
			Group_Control_Box_Shadow::get_type(),
			array(
				'name'      => 'addresses_card_box_shadow',
				'label'     => esc_html__( 'Box Shadow', 'uael' ),
				'selector'  => '{{WRAPPER}} .uael-woo-thankyou-shipping-address, {{WRAPPER}} .uael-woo-thankyou-billing-address, {{WRAPPER}} .uael-woo-thankyou-payment-method',
				'condition' => array(
					'override_addresses_styles' => 'yes',
				),
			)
		);

		// Title Styling.
		$this->add_control(
			'addresses_title_heading',
			array(
				'label'     => esc_html__( 'Title', 'uael' ),
				'type'      => Controls_Manager::HEADING,
				'separator' => 'before',
			)
		);

		// Global Title Typography.
		$this->add_group_control(
			Group_Control_Typography::get_type(),
			array(
				'name'     => 'addresses_title_typography',
				'label'    => esc_html__( 'Typography', 'uael' ),
				'global'   => array(
					'default' => Global_Typography::TYPOGRAPHY_PRIMARY,
				),
				'selector' => '{{WRAPPER}} .uael-address-title',
			)
		);

		// Address Title Color.
		$this->add_control(
			'addresses_title_color',
			array(
				'label'     => esc_html__( 'Color', 'uael' ),
				'type'      => Controls_Manager::COLOR,
				'selectors' => array(
					'{{WRAPPER}} .uael-address-title' => 'color: {{VALUE}} !important;',
				),
			)
		);

		// Content Styling.
		$this->add_control(
			'addresses_content_heading',
			array(
				'label'     => esc_html__( 'Content', 'uael' ),
				'type'      => Controls_Manager::HEADING,
				'separator' => 'before',
			)
		);

		// Global Content Typography.
		$this->add_group_control(
			Group_Control_Typography::get_type(),
			array(
				'name'     => 'addresses_content_typography',
				'label'    => esc_html__( 'Typography', 'uael' ),
				'global'   => array(
					'default' => Global_Typography::TYPOGRAPHY_TEXT,
				),
				'selector' => '{{WRAPPER}} .uael-address-content',
			)
		);

		// Global Content Color.
		$this->add_control(
			'addresses_content_color',
			array(
				'label'     => esc_html__( 'Color', 'uael' ),
				'type'      => Controls_Manager::COLOR,
				'global'    => array(
					'default' => Global_Colors::COLOR_TEXT,
				),
				'selectors' => array(
					'{{WRAPPER}} .uael-address-content' => 'color: {{VALUE}};',
				),
			)
		);

		$this->end_controls_section();
	}

	/**
	 * Get Latest Order for Preview.
	 *
	 * @return WC_Order|false Latest order or false if none found.
	 * @since 1.42.0
	 * @access private
	 */
	private function get_preview_order() {
		if ( ! class_exists( 'WooCommerce' ) ) {
			return false;
		}

		global $wp;
		
		// Check if we're in edit mode.
		$is_edit_mode = apply_filters( 'uael_woo_thankyou_force_view', \Elementor\Plugin::$instance->editor->is_edit_mode() );
		
		// Edit mode - use latest order for preview.
		if ( $is_edit_mode ) {
			$orders = wc_get_orders(
				array(
					'limit'  => 1,
					'status' => 'any',
				)
			);
			return ! empty( $orders ) ? $orders[0] : false;
		}

		// Live mode - validate thank you page access with security.
		if ( isset( $wp->query_vars['order-received'] ) ) {
			$order_id = absint( $wp->query_vars['order-received'] );
			$order    = wc_get_order( $order_id );
			
			if ( ! $order ) {
				return false;
			}
			
			// Validate security key.
			// phpcs:ignore WordPress.Security.NonceVerification.Recommended, WordPress.Security.ValidatedSanitizedInput.InputNotSanitized -- Order key validation on thank you page is standard WooCommerce practice.
			$security_key = empty( $_GET['key'] ) ? '' : wc_clean( wp_unslash( $_GET['key'] ) );
			$is_key_valid = is_object( $order ) && $order->key_is_valid( $security_key );
			
			if ( $is_key_valid && 'trash' !== $order->get_status() ) {
				// Handle order refunds.
				if ( $order instanceof \Automattic\WooCommerce\Admin\Overrides\OrderRefund ) {
					$order = wc_get_order( $order->get_parent_id() );
				}
				return $order;
			}
		}

		// Return false if not properly accessed.
		return false;
	}

	/**
	 * Render global animations CSS.
	 *
	 * @param array $settings Widget settings.
	 * @since 1.42.0
	 * @access protected
	 */
	protected function render_global_animations_css( $settings ) {
		// Check if animations are enabled.
		$enable_animations = $settings['enable_animations'] ?? 'yes';

		// If animations are disabled, add CSS to disable all animations and hover effects.
		if ( 'yes' !== $enable_animations ) {
			$css  = '<style id="uael-global-animations">';
			$css .= '.uael-woo-thankyou-products, .uael-woo-thankyou-summary, .uael-woo-thankyou-billing-address, .uael-woo-thankyou-shipping-address, .uael-woo-thankyou-payment-method, .uael-woo-thankyou-confirmation, .uael-woo-thankyou-order-info { ';
			$css .= '-webkit-animation: none !important; animation: none !important; ';
			$css .= 'transition: none !important; ';
			$css .= '}';
			$css .= '.uael-woo-thankyou-products:hover, .uael-woo-thankyou-summary:hover, .uael-woo-thankyou-billing-address:hover, .uael-woo-thankyou-shipping-address:hover, .uael-woo-thankyou-payment-method:hover, .uael-woo-thankyou-confirmation:hover, .uael-woo-thankyou-order-info:hover { ';
			$css .= '-webkit-transform: none !important; transform: none !important; ';
			$css .= '-webkit-box-shadow: initial !important; box-shadow: initial !important; ';
			$css .= '}';
			$css .= '</style>';
			echo $css; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
			return;
		}

		// Get load animation settings.
		$animation_type     = $settings['global_animation_type'] ?? 'fadeInUp';
		$animation_duration = $settings['global_animation_duration']['size'] ?? 900;
		$animation_delay    = $settings['global_animation_delay']['size'] ?? 0;

		// Get hover animation settings.
		$hover_animation_type     = $settings['hover_animation_type'] ?? 'shadow-lift';
		$hover_animation_duration = $settings['hover_animation_duration']['size'] ?? 300;
		$hover_animation_delay    = $settings['hover_animation_delay']['size'] ?? 0;

		$css = '<style id="uael-global-animations">';

		// Load animation CSS.
		if ( 'none' !== $animation_type ) {
			$css .= '.uael-woo-thankyou-products, .uael-woo-thankyou-summary, .uael-woo-thankyou-billing-address, .uael-woo-thankyou-shipping-address, .uael-woo-thankyou-payment-method { ';
			$css .= '-webkit-animation: ' . esc_attr( $animation_type ) . ' ' . intval( $animation_duration ) . 'ms ease-out ' . intval( $animation_delay ) . 'ms both; ';
			$css .= 'animation: ' . esc_attr( $animation_type ) . ' ' . intval( $animation_duration ) . 'ms ease-out ' . intval( $animation_delay ) . 'ms both; ';
			$css .= '}';
		}

		// Hover animation CSS.
		if ( 'none' !== $hover_animation_type ) {
			$css .= '.uael-woo-thankyou-products, .uael-woo-thankyou-summary, .uael-woo-thankyou-billing-address, .uael-woo-thankyou-shipping-address, .uael-woo-thankyou-payment-method, .uael-woo-thankyou-confirmation, .uael-woo-thankyou-order-info { ';
			$css .= 'transition: ';

			switch ( $hover_animation_type ) {
				case 'lift':
					$css .= '-webkit-transform ' . intval( $hover_animation_duration ) . 'ms ease ' . intval( $hover_animation_delay ) . 'ms, transform ' . intval( $hover_animation_duration ) . 'ms ease ' . intval( $hover_animation_delay ) . 'ms; ';
					break;
				case 'shadow':
					$css .= '-webkit-box-shadow ' . intval( $hover_animation_duration ) . 'ms ease ' . intval( $hover_animation_delay ) . 'ms, box-shadow ' . intval( $hover_animation_duration ) . 'ms ease ' . intval( $hover_animation_delay ) . 'ms; ';
					break;
				case 'shadow-lift':
					$css .= '-webkit-box-shadow ' . intval( $hover_animation_duration ) . 'ms ease ' . intval( $hover_animation_delay ) . 'ms, box-shadow ' . intval( $hover_animation_duration ) . 'ms ease ' . intval( $hover_animation_delay ) . 'ms, -webkit-transform ' . intval( $hover_animation_duration ) . 'ms ease ' . intval( $hover_animation_delay ) . 'ms, transform ' . intval( $hover_animation_duration ) . 'ms ease ' . intval( $hover_animation_delay ) . 'ms; ';
					break;
			}

			$css .= '}';

			// Hover state styles.
			$css .= '.uael-woo-thankyou-products:hover, .uael-woo-thankyou-summary:hover, .uael-woo-thankyou-billing-address:hover, .uael-woo-thankyou-shipping-address:hover, .uael-woo-thankyou-payment-method:hover, .uael-woo-thankyou-confirmation:hover, .uael-woo-thankyou-order-info:hover { ';

			switch ( $hover_animation_type ) {
				case 'lift':
					$css .= '-webkit-transform: translateY(-2px); transform: translateY(-2px); ';
					break;
				case 'shadow':
					$css .= '-webkit-box-shadow: 0 8px 24px rgba(0, 0, 0, 0.15); box-shadow: 0 8px 24px rgba(0, 0, 0, 0.15); ';
					break;
				case 'shadow-lift':
					$css .= '-webkit-box-shadow: 0 4px 12px rgba(0, 0, 0, 0.08); box-shadow: 0 4px 12px rgba(0, 0, 0, 0.08); ';
					$css .= '-webkit-transform: translateY(-1px); transform: translateY(-1px); ';
					break;
			}

			$css .= '}';
		}

		$css .= '</style>';

		echo $css; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
	}

	/**
	 * Render widget output on the frontend.
	 *
	 * @since 1.42.0
	 * @access protected
	 */
	protected function render() {

		// Check if we're in edit mode.
		$is_edit_mode = apply_filters( 'uael_woo_thankyou_force_view', \Elementor\Plugin::$instance->editor->is_edit_mode() );
		
		// Only show on proper thank you pages or in edit mode.
		if ( ! $is_edit_mode && ( ! function_exists( 'is_wc_endpoint_url' ) || ! is_wc_endpoint_url( 'order-received' ) ) ) {
			return; // Don't display on non-thank-you pages.
		}

		$settings = $this->get_settings_for_display();
		$order    = $this->get_preview_order();

		if ( ! $order ) {
			if ( $is_edit_mode ) {
				echo '<div class="uael-woo-thankyou-error">';
				esc_html_e( 'No orders found. Please place an order to view the widget.', 'uael' );
				echo '</div>';
			}
			return;
		}

		// Load the template with all rendering logic.
		$this->load_simple_template( $settings, $order );
	}

	/**
	 * Load simple template file for thank you page output.
	 *
	 * @param array    $settings Widget settings.
	 * @param WC_Order $order WooCommerce order object.
	 * @since 1.42.0
	 * @access private
	 */
	private function load_simple_template( $settings, $order ) {
		// Check for theme override first.
		$theme_template = locate_template(
			array(
				'uael/woocommerce/woo-thank-you-template.php',
				'uael/woo-thank-you-template.php',
				'woo-thank-you-template.php',
			)
		);

		if ( $theme_template ) {
			$template_file = $theme_template;
		} else {
			// Use plugin template.
			$template_file = UAEL_MODULES_DIR . 'woocommerce/templates/woo-thank-you-template.php';
		}

		// Make widget instance available to template.
		$widget = $this;

		// Include the template.
		if ( file_exists( $template_file ) ) {
			include $template_file;
		}
	}
}
