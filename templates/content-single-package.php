<?php
global $post;

$membership = new Opalmembership_Package( $post->ID );
$style      = '';
if ( has_post_thumbnail() ) {
	$style .= 'style="background-image:url(' . get_the_post_thumbnail_url() . ');"';
}

$button_text = $membership->get_post_meta( 'button_text' );
$button_text = $button_text ? $button_text : esc_html__( 'Buy Now', 'opalmembership' );
?>
<article id="post-<?php the_ID(); ?>" <?php post_class(); ?>>
    <div class="package-inner<?php if ( $membership->is_hightlighted() ): ?> package-hightlighted<?php endif; ?>">
        <div class="pricing pricing-v3">
            <div class="pricing-header" <?php echo wp_kses_post( $style ); ?>>
                <span class="plan-subtitle hide"><?php esc_html_e( 'Recommend', 'opalmembership' ); ?></span>
				<?php the_title( '<h4 class="plan-title">', '</h4>' ); ?>
                <div class="plan-price">
					<?php echo wp_kses_post( $membership->get_price_html() ); ?>
                    <p>
						<?php
						$duration_unit = $membership->get_post_meta( 'duration_unit' );
						$duration      = absint( $membership->get_post_meta( 'duration' ) );
						echo esc_html( $duration . ' ' . $duration_unit );
						?>
                    </p>
                </div>
            </div>
            <div class="pricing-body">
                <div class="plain-info">

					<?php
					do_action( 'opalmembership_content_single_before' );
					/* translators: %s: Name of current post */
					the_content( sprintf(
						esc_html__( 'Continue reading %s <span class="meta-nav">&rarr;</span>', 'prestabase' ),
						the_title( '<span class="screen-reader-text">', '</span>', false )
					) );

					wp_link_pages( [
						'before'      => '<div class="page-links"><span class="page-links-title">' . esc_html__( 'Pages:', 'prestabase' ) . '</span>',
						'after'       => '</div>',
						'link_before' => '<span>',
						'link_after'  => '</span>',
					] );
					do_action( 'opalmembership_content_single_after' );
					?>
                </div>
            </div>
            <div class="pricing-footer">
				<?php
				echo Opalmembership_Template_Loader::get_template_part( 'single-package-purchase-form', [
					'highlighted' => $membership->is_hightlighted(),
					'button_text' => $button_text,
				] );
				?>
            </div>
        </div>
    </div>
</article>
