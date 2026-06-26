<?php
/**
 * Astra Addon Customizer
 *
 * @package Astra Addon
 * @since 1.6.0
 */

if ( ! class_exists( 'Astra_Addon_Beaver_Builder_Compatibility' ) ) {

	/**
	 * Astra Addon Page Builder Compatibility base class
	 *
	 * @since 1.6.0
	 */
	class Astra_Addon_Beaver_Builder_Compatibility extends Astra_Addon_Page_Builder_Compatibility {
		/**
		 * Instance
		 *
		 * @since 1.6.0
		 *
		 * @var object Class object.
		 */
		private static $instance;

		/**
		 * Initiator
		 *
		 * @since 1.6.0
		 *
		 * @return object initialized object of class.
		 */
		public static function get_instance() {
			if ( ! isset( self::$instance ) ) {
				self::$instance = new self();
			}
			return self::$instance;
		}

		/**
		 * Render content for post.
		 *
		 * @param int $post_id Post id.
		 *
		 * @since 1.6.0
		 */
		public function render_content( $post_id ) {

			if ( ! apply_filters( 'astra_addon_bb_render_content_by_id', false ) ) {
				if ( is_callable( 'FLBuilderShortcodes::insert_layout' ) ) {
					echo do_shortcode(
						FLBuilderShortcodes::insert_layout(
							array( // WPCS: XSS OK.
								'id' => $post_id,
							)
						)
					);
				}
			} else {
				FLBuilder::render_content_by_id(
					$post_id,
					'div',
					array()
				);
			}
		}

		/**
		 * Load styles and scripts for the BB layout.
		 *
		 * Skips enqueue for layout types that self-enqueue during render via
		 * FLBuilderShortcodes::insert_layout, to prevent FLBuilder from
		 * initialising its modules twice (e.g. breaking mobile submenu toggles).
		 *
		 * @since 1.6.0
		 *
		 * @param int $post_id Post id.
		 * @return void
		 */
		public function enqueue_scripts( $post_id ) {
			$layout = get_post_meta( $post_id, 'ast-advanced-hook-layout', true );

			/**
			 * Layout types that self-enqueue via BB's insert_layout/render_content.
			 * Add layout slugs here to prevent a double enqueue for that layout type.
			 *
			 * @since 4.13.2
			 *
			 * @param array<string> $layout_types Layout type slugs that self-enqueue.
			 * @param int           $post_id      Custom layout post ID.
			 */
			$self_enqueuing_layouts = apply_filters(
				'astra_addon_bb_self_enqueuing_layouts',
				array( 'header', 'footer', '404-page' ),
				$post_id
			);

			if ( in_array( $layout, $self_enqueuing_layouts, true ) ) {
				return;
			}

			if ( is_callable( 'FLBuilder::enqueue_layout_styles_scripts_by_id' ) ) {
				// Enqueue styles and scripts for this post.
				FLBuilder::enqueue_layout_styles_scripts_by_id( $post_id );
			}
		}

	}

}
