<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
} 

get_header(); ?>

	<section id="primary" class="content-area">
		<main id="main" class="site-main content" role="main">
			<?php if ( have_posts() ) : ?>
				<?php while ( have_posts() ) : the_post(); ?>
                    <?php echo Opalmembership_Template_Loader::get_template_part( 'content-single-package' ); ?>
				<?php endwhile; ?>

				<?php the_posts_pagination( array(
					'prev_text'          => esc_html__( 'Previous page', 'opalmembership' ),
					'next_text'          => esc_html__( 'Next page', 'opalmembership' ),
					'before_page_number' => '<span class="meta-nav screen-reader-text">' . esc_html__( 'Page', 'opalmembership' ) . ' </span>',
				) ); ?>
			<?php else : ?>

				<?php get_template_part( 'content', 'none' ); ?>

			<?php endif; ?>

		</main><!-- .site-main -->
	</section><!-- .content-area -->

<?php get_footer(); ?>
