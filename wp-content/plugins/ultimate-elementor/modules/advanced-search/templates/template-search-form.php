<?php
/**
 * UAEL Advanced Search - Main Template.
 *
 * This template handles all the frontend output for the Advanced Search widget.
 *
 * Available variables:
 *
 * @var array $settings Widget settings with pre-calculated display flags.
 *
 * @package UAEL
 * @since 1.36.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

?>

<div <?php echo wp_kses_post( $this->get_render_attribute_string( 'search_container' ) ); ?>>

	<!-- Search Form -->
	<form class="uael-advanced-search-form" role="search" aria-label="<?php echo esc_attr__( 'Advanced Search', 'uael' ); ?>">
		<div class="uael-advanced-search-input-wrapper">

			<?php if ( $settings['show_left_icon'] ) : ?>
				<i class="fa fa-search uael-advanced-search-icon uael-icon-left" aria-hidden="true"></i>
			<?php endif; ?>

			<label for="uael-search-input-<?php echo esc_attr( $this->get_id() ); ?>" class="uael-advanced-search-screen-reader-text">
				<?php echo esc_html__( 'Search for:', 'uael' ); ?>
			</label>
			<input
				type="search"
				id="uael-search-input-<?php echo esc_attr( $this->get_id() ); ?>"
				class="uael-advanced-search-input"
				placeholder="<?php echo esc_attr( $settings['input_field_placeholder'] ); ?>"
				maxlength="50"
				autocomplete="off"
				aria-autocomplete="list"
				aria-controls="uael-search-results-<?php echo esc_attr( $this->get_id() ); ?>"
				aria-label="<?php echo esc_attr( $settings['input_field_placeholder'] ); ?>"
			>

			<?php if ( $settings['show_right_icon'] ) : ?>
				<i class="fa fa-search uael-advanced-search-icon uael-icon-right" aria-hidden="true"></i>
			<?php endif; ?>

		</div>

		<?php if ( $settings['show_action_button'] ) : ?>
			<button type="submit" class="uael-advanced-search-button elementor-button" aria-label="<?php echo esc_attr( $settings['action_button_label'] ); ?>">
				<span class="elementor-button-content-wrapper">
					<?php
					// Icon before text.
					if ( 'before' === $settings['action_button_icon_position'] ) {
						if ( \UltimateElementor\Classes\UAEL_Helper::is_elementor_updated() ) {
							if ( ! empty( $settings['action_button_icon'] ) || ! empty( $settings['new_action_button_icon'] ) ) {
								$migrated = isset( $settings['__fa4_migrated']['new_action_button_icon'] );
								$is_new   = ! isset( $settings['action_button_icon'] );
								?>
								<span class="elementor-button-icon elementor-align-icon-before" aria-hidden="true">
									<?php
									if ( $is_new || $migrated ) :
										\Elementor\Icons_Manager::render_icon( $settings['new_action_button_icon'], array( 'aria-hidden' => 'true' ) );
									elseif ( ! empty( $settings['action_button_icon'] ) ) :
										?>
										<i class="<?php echo esc_attr( $settings['action_button_icon'] ); ?>" aria-hidden="true"></i>
									<?php endif; ?>
								</span>
								<?php
							}
						} elseif ( ! empty( $settings['action_button_icon'] ) ) {
							?>
							<span class="elementor-button-icon elementor-align-icon-before" aria-hidden="true">
								<i class="<?php echo esc_attr( $settings['action_button_icon'] ); ?>" aria-hidden="true"></i>
							</span>
							<?php
						}
					}
					?>

					<span class="elementor-button-text"><?php echo esc_html( $settings['action_button_label'] ); ?></span>

					<?php
					// Icon after text.
					if ( 'after' === $settings['action_button_icon_position'] ) {
						if ( \UltimateElementor\Classes\UAEL_Helper::is_elementor_updated() ) {
							if ( ! empty( $settings['action_button_icon'] ) || ! empty( $settings['new_action_button_icon'] ) ) {
								$migrated = isset( $settings['__fa4_migrated']['new_action_button_icon'] );
								$is_new   = ! isset( $settings['action_button_icon'] );
								?>
								<span class="elementor-button-icon elementor-align-icon-after" aria-hidden="true">
									<?php
									if ( $is_new || $migrated ) :
										\Elementor\Icons_Manager::render_icon( $settings['new_action_button_icon'], array( 'aria-hidden' => 'true' ) );
									elseif ( ! empty( $settings['action_button_icon'] ) ) :
										?>
										<i class="<?php echo esc_attr( $settings['action_button_icon'] ); ?>" aria-hidden="true"></i>
									<?php endif; ?>
								</span>
								<?php
							}
						} elseif ( ! empty( $settings['action_button_icon'] ) ) {
							?>
							<span class="elementor-button-icon elementor-align-icon-after" aria-hidden="true">
								<i class="<?php echo esc_attr( $settings['action_button_icon'] ); ?>" aria-hidden="true"></i>
							</span>
							<?php
						}
					}
					?>
				</span>
			</button>
		<?php endif; ?>
	</form>

	<!-- Trending Searches -->
	<?php if ( $settings['show_trending'] && ! empty( $settings['trending_keywords_array'] ) ) : ?>
		<div class="uael-advanced-search-popular-keywords">
			<h4><?php echo esc_html( $settings['trending_searches_heading'] ); ?></h4>
			<div class="uael-popular-keywords-list">
				<?php foreach ( $settings['trending_keywords_array'] as $keyword ) : ?>
					<span class="uael-popular-keyword" data-keyword="<?php echo esc_attr( $keyword ); ?>">
						<?php echo esc_html( $keyword ); ?>
					</span>
				<?php endforeach; ?>
			</div>
		</div>
	<?php endif; ?>

	<!-- Search Results Container -->
	<div id="uael-search-results-<?php echo esc_attr( $this->get_id() ); ?>" class="uael-advanced-search-results" style="display: none;" role="region" aria-live="polite" aria-atomic="true">

		<?php if ( $settings['show_results_count'] ) : ?>
			<div class="uael-search-results-count" role="status" aria-live="polite"></div>
		<?php endif; ?>

		<div class="uael-search-results-content uael-results-<?php echo esc_attr( $settings['layout_presentation_mode'] ); ?>" role="list">
			<!-- Search results will be populated here via AJAX -->
		</div>

		<div class="uael-search-results-pagination">
			<button class="uael-load-more-button <?php echo ! empty( $settings['load_more_button_hover_animation'] ) && 'none' !== $settings['load_more_button_hover_animation'] ? 'uael-hover-' . esc_attr( $settings['load_more_button_hover_animation'] ) : ''; ?>" style="display: none;" aria-label="<?php echo esc_attr__( 'Load more results', 'uael' ); ?>">
				<?php echo esc_html( $settings['load_more_button_text'] ); ?>
			</button>
		</div>

		<div class="uael-search-no-results" style="display: none;" role="status">
			<p><?php echo esc_html( $settings['empty_results_message'] ); ?></p>
		</div>

	</div>

</div>
