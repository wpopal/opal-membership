<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit();
}
$featured = isset( $atts['featured_item'] ) ? absint( $atts['featured_item'] ) : 0;
$i = 1;

if ( $query->have_posts() ) : ?>

	<div class="opalmembership-pricing-table">
		<?php while ( $query->have_posts() ) : $query->the_post(); ?>

			<?php global $package; ?>
			<?php $class = ( $featured === $i ) ? ' featured' : ''; ?>
			<div class="pricing-column<?php echo esc_attr( $class ); ?>">

				<div class="pricing-content">

					<?php do_action( 'opalmembership_before_shortcode_pricing' ); ?>

					<!-- pricing header -->
					<div class="pricing-header">
						<h2 class="pricing-title"><?php the_title() ?></h2>
						<h3 class="pricing-value">
							<span class="pricing-price">
								<?php printf( '%s', $package->get_price_html() ) ?>
							</span>
						</h3>
					</div>
					<!-- end pricing header -->

					<!-- pricing info -->
					<div class="pricing-info">
						<p class="text-center"><?php printf( esc_html__( 'Property Submit Limit: %s', 'opalmembership' ), absint( $package->property_number ) ) ?></p>
						<p class="text-center"><?php printf( esc_html__( 'Featured Property: %s', 'opalmembership' ), absint( $package->featured_property_number ) ) ?></p>
					</div>
					<!-- end pricing info -->

					<!-- pricing footer -->
					<div class="pricing-footer">
						<form class="opalesate_pricing_form">
							<?php wp_nonce_field( 'opalmembership_register_package', 'register-package' ); ?>
							<input type="hidden" name="action" value="opalmembership_register_agent" />
							<input type="hidden" name="package_id" value="<?php echo esc_attr( $package->id ); ?>" />
							<button type="submit" class="register_button membership-add-to-purchase" data-action="membership_add_to_purchase" data-id="<?php echo esc_attr( $package->id); ?>"><?php printf( '%s', $atts['button_text'] ) ?></button>
						</form>
					</div>
					<!-- end pricing footer -->

					<?php do_action( 'opalmembership_after_shortcode_pricing' ); ?>

				</div>

			</div>

		<?php $i++; endwhile; ?>
	</div>

<?php endif; ?>