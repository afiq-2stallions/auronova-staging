<?php
/**
 * UAEL WooCommerce Thank You Page - Main Template
 *
 * This template can be overridden by copying it to yourtheme/uael/woocommerce/woo-thank-you-template.php
 *
 * Variables available:
 *
 * @var array    $settings Widget settings
 * @var WC_Order $order    WooCommerce order object
 * @var object   $widget   Widget instance
 *
 * @package UAEL
 * @since 1.42.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}
?>

<div class="uael-woo-thankyou-container">
	<?php
	// Render global animations CSS.
	$widget->render_global_animations_css( $settings );

	// Allow for payment method specific actions.
	do_action( 'woocommerce_thankyou_' . $order->get_payment_method(), $order->get_id() );
	?>

	<?php if ( 'yes' === $settings['show_confirmation_message'] ) : ?>
		<?php
		$html_tag = ! empty( $settings['confirmation_html_tag'] ) ? $settings['confirmation_html_tag'] : 'h2';
		$message  = ! empty( $settings['confirmation_message'] ) ? $settings['confirmation_message'] : esc_html__( 'Thank you for your order!', 'uael' );
		?>
		<div class="uael-woo-thankyou-confirmation">

			<?php if ( ! empty( $settings['confirmation_icon']['value'] ) ) : ?>
				<span class="uael-confirmation-icon">
					<?php \Elementor\Icons_Manager::render_icon( $settings['confirmation_icon'], array( 'aria-hidden' => 'true' ) ); ?>
				</span>
			<?php endif; ?>

			<<?php echo esc_attr( $html_tag ); ?> class="uael-confirmation-message">
				<?php echo esc_html( $message ); ?>
			</<?php echo esc_attr( $html_tag ); ?>>

			<?php if ( ! empty( $settings['confirmation_subtext'] ) ) : ?>
				<p class="uael-confirmation-description"><?php echo esc_html( $settings['confirmation_subtext'] ); ?></p>
			<?php endif; ?>

		</div>
	<?php endif; ?>

	<?php if ( 'yes' === $settings['show_order_info'] ) : ?>
		<div class="uael-woo-thankyou-order-info">

			<?php if ( 'yes' === $settings['show_order_number'] ) : ?>
				<?php $label = ! empty( $settings['order_number_label'] ) ? $settings['order_number_label'] : esc_html__( 'Order Number', 'uael' ); ?>
				<div class="uael-order-info-item">
					<span class="uael-order-info-label"><?php echo esc_html( $label ); ?></span>
					<span class="uael-order-info-value">#<?php echo esc_html( $order->get_order_number() ); ?></span>
				</div>
			<?php endif; ?>

			<?php if ( 'yes' === $settings['show_order_date'] ) : ?>
				<?php $label = ! empty( $settings['order_date_label'] ) ? $settings['order_date_label'] : esc_html__( 'Order Date', 'uael' ); ?>
				<div class="uael-order-info-item">
					<span class="uael-order-info-label"><?php echo esc_html( $label ); ?></span>
					<span class="uael-order-info-value"><?php echo esc_html( wc_format_datetime( $order->get_date_created() ) ); ?></span>
				</div>
			<?php endif; ?>

			<div class="uael-order-info-item">
				<span class="uael-order-info-label"><?php esc_html_e( 'Status', 'uael' ); ?></span>
				<span class="uael-order-info-value uael-order-status">
					<span style="display: inline-block; width: 8px; height: 8px; background-color: #4CAF50; border-radius: 50%; margin-right: 4px;"></span>
					<?php echo esc_html( wc_get_order_status_name( $order->get_status() ) ); ?>
				</span>
			</div>

		</div>
	<?php endif; ?>

	<div class="uael-woo-thankyou-main-section<?php echo ( 'yes' !== $settings['show_products'] ) ? ' uael-no-products' : ''; ?>">

		<?php if ( 'yes' === $settings['show_order_body'] ) : ?>
			<div class="uael-woo-thankyou-products-summary">

				<!-- Products Table -->
				<?php if ( 'yes' === $settings['show_products'] ) { ?>
					<div class="uael-woo-thankyou-products">
						<h3 class="uael-products-title"><?php esc_html_e( 'Products', 'uael' ); ?></h3>
						<div class="uael-products-table">

							<?php foreach ( $order->get_items() as $item_id => $item ) : ?>
								<?php
								$product = $item->get_product();
								if ( ! $product ) {
									continue;
								}

								$image_id  = $product->get_image_id();
								$image_url = $image_id ? wp_get_attachment_image_url( $image_id, 'thumbnail' ) : wc_placeholder_img_src();
								?>

								<div class="uael-product-row">

									<div class="uael-product-image">
										<img src="<?php echo esc_url( $image_url ); ?>" alt="<?php echo esc_attr( $product->get_name() ); ?>" />
									</div>

									<div class="uael-product-details">
										<h4 class="uael-product-name"><?php echo esc_html( $product->get_name() ); ?></h4>

										<?php if ( 'yes' === $settings['show_product_quantity'] ) : ?>
											<p class="uael-product-qty"><?php esc_html_e( 'Qty:', 'uael' ); ?> <?php echo intval( $item->get_quantity() ); ?></p>
										<?php endif; ?>
									</div>

									<div class="uael-product-price">
										<?php
										if ( 'yes' === $settings['show_sale_price'] ) {
											// Show full price HTML (includes sale price if available).
											echo wp_kses_post( $product->get_price_html() );
										} else {
											// Show only regular price.
											$regular_price = $product->get_regular_price();
											if ( $regular_price ) {
												echo wp_kses_post( wc_price( $regular_price ) );
											} else {
												echo wp_kses_post( $product->get_price_html() );
											}
										}
										?>
									</div>

								</div>

							<?php endforeach; ?>

						</div>
					</div>
				<?php } ?>

				<!-- Order Summary -->
				<div class="uael-woo-thankyou-summary-wrapper">

					<?php if ( 'yes' === $settings['show_order_summary'] ) : ?>
						<div class="uael-woo-thankyou-summary">
							<h3 class="uael-summary-title"><?php esc_html_e( 'Order Summary', 'uael' ); ?></h3>

							<div class="uael-summary-row">
								<span class="uael-summary-label"><?php esc_html_e( 'Subtotal', 'uael' ); ?></span>
								<span class="uael-summary-value"><?php echo wp_kses_post( wc_price( $order->get_subtotal() ) ); ?></span>
							</div>

							<?php if ( $order->get_shipping_total() > 0 ) : ?>
								<div class="uael-summary-row">
									<span class="uael-summary-label"><?php esc_html_e( 'Shipping', 'uael' ); ?></span>
									<span class="uael-summary-value"><?php echo wp_kses_post( wc_price( $order->get_shipping_total() ) ); ?></span>
								</div>
							<?php endif; ?>

							<?php if ( $order->get_total_tax() > 0 ) : ?>
								<div class="uael-summary-row">
									<span class="uael-summary-label"><?php esc_html_e( 'Tax', 'uael' ); ?></span>
									<span class="uael-summary-value"><?php echo wp_kses_post( wc_price( $order->get_total_tax() ) ); ?></span>
								</div>
							<?php endif; ?>

							<div class="uael-summary-row uael-summary-total">
								<span class="uael-summary-label"><?php esc_html_e( 'Total', 'uael' ); ?></span>
								<span class="uael-summary-value"><?php echo wp_kses_post( $order->get_formatted_order_total() ); ?></span>
							</div>

						</div>
					<?php endif; ?>

					<!-- Action Buttons -->
					<div class="uael-woo-thankyou-actions">

						<a href="<?php echo esc_url( $order->get_view_order_url() ); ?>" class="uael-action-button uael-button-primary">
							<?php esc_html_e( 'View Order', 'uael' ); ?>
						</a>

						<a href="<?php echo esc_url( wc_get_page_permalink( 'shop' ) ); ?>" class="uael-action-button uael-button-secondary">
							<?php esc_html_e( 'Continue Shopping', 'uael' ); ?>
						</a>

					</div>

				</div>

			</div>
		<?php endif; ?>

		<!-- Addresses Section -->
		<?php if ( 'yes' === $settings['show_shipping_address'] || 'yes' === $settings['show_billing_address'] ) : ?>
			<div class="uael-woo-thankyou-addresses">

				<?php if ( 'yes' === $settings['show_shipping_address'] ) : ?>
					<?php
					$label   = $settings['shipping_address_label'];
					$address = $order->get_formatted_shipping_address();
					?>
					<div class="uael-woo-thankyou-shipping-address">
						<h4 class="uael-address-title"><?php echo esc_html( $label ); ?></h4>
						<?php if ( $address ) : ?>
							<div class="uael-address-content"><?php echo wp_kses_post( $address ); ?></div>
						<?php else : ?>
							<div class="uael-address-content"><?php esc_html_e( 'N/A', 'uael' ); ?></div>
						<?php endif; ?>
					</div>
				<?php endif; ?>

				<?php if ( 'yes' === $settings['show_billing_address'] ) : ?>
					<?php
					$label   = $settings['billing_address_label'];
					$address = $order->get_formatted_billing_address();
					?>
					<div class="uael-woo-thankyou-billing-address">
						<h4 class="uael-address-title"><?php echo esc_html( $label ); ?></h4>
						<?php if ( $address ) : ?>
							<div class="uael-address-content"><?php echo wp_kses_post( $address ); ?></div>
						<?php else : ?>
							<div class="uael-address-content"><?php esc_html_e( 'N/A', 'uael' ); ?></div>
						<?php endif; ?>
					</div>
				<?php endif; ?>
				<?php if ( 'yes' === $settings['show_payment_method'] ) : ?>
					<?php
						$label          = $settings['payment_method_label']; 
						$payment_method = $order->get_payment_method_title(); 
					?>
					<div class="uael-woo-thankyou-payment-method">
						<h4 class="uael-address-title"><?php echo esc_html( $label ); ?></h4>
						<?php if ( $payment_method ) : ?>
							<div class="uael-address-content"><?php echo esc_html( $payment_method ); ?></div>
						<?php else : ?>
							<div class="uael-address-content"><?php esc_html_e( 'N/A', 'uael' ); ?></div>
						<?php endif; ?>
					</div>
				<?php endif; ?>
			</div>
		<?php endif; ?>

	</div>

</div>
