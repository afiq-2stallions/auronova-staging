<?php
/**
 * UAEL Display Conditions ACF feature.
 *
 * @package UAEL
 */

namespace UltimateElementor\Modules\DisplayConditions\Conditions;

use Elementor\Controls_Manager;
use UltimateElementor\Classes\UAEL_Helper;

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

/**
 * Class Acf_Text
 *
 * @package UltimateElementor\Modules\DisplayConditions\Conditions
 */
class Acf_Text extends Condition {
	/**
	 * Get Name
	 *
	 * Get the name of the module
	 *
	 * @since  1.35.1
	 * @return string
	 */
	public function get_key_name() {
		return 'acf_text';
	}

	/**
	 * ID for ACF field.
	 *
	 * @since 1.35.1
	 * @return string|void
	 */
	public function get_acf_field_name() {
		return 'acf_text_key';
	}

	/**
	 * ID for ACF value field.
	 *
	 * @since 1.35.1
	 * @return string|void
	 */
	public function get_acf_field_value() {
		return 'acf_text_value';
	}
	/**
	 * Get Condition Title
	 *
	 * @since 1.35.1
	 * @return string|void
	 */
	public function get_title() {
		return __( 'ACF Field', 'uael' );
	}

	/**
	 * Get Name Control
	 *
	 * Get the settings for the name control
	 *
	 * @param array $condition Condition.
	 * @since  1.35.1
	 * @return array
	 */
	public function get_acf_field( $condition ) {

		return wp_parse_args(
			array(
				$this->get_acf_field_name(),
				'type'          => 'uael-control-query',
				'description'   => __( 'Search ACF fields ( Types: textual, select, date, boolean, post, taxonomy ) by name.', 'uael' ),
				'placeholder'   => __( 'Search Fields', 'uael' ),
				'post_type'     => '',
				'options'       => array(),
				'query_type'    => 'acf',
				'label_block'   => true,
				'multiple'      => false,
				'query_options' => array(
					'show_type'       => false,
					'show_field_type' => true,
					'field_type'      => array(
						'textual',
						'select',
						'date',
						'boolean',
						'post',
						'taxonomy',
					),
				),
				'condition'     => $condition,
			)
		);
	}

	/**
	 * Get Value Control.
	 * Get the settings for the value control.
	 *
	 * @param array $condition Condition.
	 *
	 * @since  1.35.1
	 * @return array
	 */
	public function get_repeater_control( array $condition ) {
		return array(
			$this->get_acf_field_value(),
			'type'        => Controls_Manager::TEXT,
			'default'     => '',
			'placeholder' => __( 'Value', 'uael' ),
			'label_block' => true,
			'condition'   => $condition,
		);
	}


	/**
	 * Compare Condition value.
	 *
	 * @param array  $settings Extension settings.
	 * @param string $operator Relationship operator.
	 * @param string $key The ACF field key to check.
	 * @param mixed  $value The value to check the key against.
	 *
	 * @return bool|string
	 * @access public
	 * @since 1.35.1
	 */
	public function acf_compare_value( $settings, $operator, $key, $value ) {

		$show = false;

		// Ensure the ACF function exists before calling it.
		if ( function_exists( 'get_field_object' ) ) {
			$field_object = get_field_object( $key );

			// Handle string value for correct comparison boolean (true_false) acf field.
			if ( is_array( $field_object ) && isset( $field_object['type'] ) && ( 'true_false' === $field_object['type'] ) ) {

				if ( 1 == $field_object['default_value'] ) {

					if ( ( true == $field_object['value'] && 'true' == $value ) || ( false == $field_object['value'] && 'false' == $value ) ) {
						$value       = true;
						$field_value = true;
					} else {
						$field_value = get_field( $key );
					}
				} elseif ( 0 == $field_object['default_value'] ) {

					if ( is_array( $field_object ) && isset( $field_object['type'] ) && ( 'true_false' === $field_object['type'] ) 
						&& ( filter_var( $field_object['value'], FILTER_VALIDATE_BOOLEAN, FILTER_NULL_ON_FAILURE ) === 
						filter_var( $value, FILTER_VALIDATE_BOOLEAN, FILTER_NULL_ON_FAILURE ) ) ) {
						$value       = true;
						$field_value = true;

					} else {
						$value       = false;
						$field_value = get_field( $key );
					}
				}           
			} else {
				$field_value = get_field( $key );
			}

			global $post;

			/** If ACF field is a checkbox */
			if ( isset( $field_object['type'] ) && 'checkbox' === $field_object['type'] ) {

				if ( is_array( $field_value ) && ! empty( $field_value ) ) {

					if ( isset( $field_object['return_format'] ) && 'array' === $field_object['return_format'] ) {

						$formatted_values = array();
						
						// Loop through each choice and compare 'value' and 'field_value'.
						foreach ( $field_value as $choice ) {
							if ( isset( $choice['value'] ) && isset( $choice['label'] ) ) {
								// Find the matching label for the value.
								foreach ( $field_object['choices'] as $field_key => $field_label ) {
									if ( $choice['value'] === $field_key ) {
										// Format as 'value : label' and add to formatted_values array.
										$formatted_values[] = $choice['value'] . ' : ' . $field_label;
									}
								}
							}
						}
						
						$field_value = implode( ', ', $formatted_values ); // Convert formatted values array to a single string.

					} else {
						
						$field_value = implode( ', ', $field_value ); // Convert array to string for non-array return format.
					}
				} elseif ( isset( $field_object['default_value'] ) ) {
					$field_value = $field_object['default_value'];

					// If the default value is an array (checkbox field typically returns array), extract the value.
					if ( is_array( $field_value ) && isset( $field_value[0] ) ) {
						$field_value = $field_value[0]; // Take the first value from the array.
					}
				}
			}

			if ( is_archive() ) {
				$term = get_queried_object();

				if ( is_object( $term ) && get_class( $term ) === 'WP_Term' ) {
					$field_value = get_field( $key, $term );
				}
			}

			if ( $field_value ) {
				$field_settings = get_field_object( $key );

				switch ( $field_settings['type'] ) {
					default:
						$show = $value === $field_value;
						break;
				}
			}
		}

		return UAEL_Helper::display_conditions_compare( $show, true, $operator );
	}
}

