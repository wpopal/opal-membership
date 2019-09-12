<div class="membership-packages">
	<?php if ( $loop->have_posts() ) : ?>
		<div class="row">
		<?php
			$col = floor(12/$column);
			$i = 0;
			while (  $loop->have_posts() ) :  $loop->the_post(); ?>
			<div class="col-lg-<?php echo $col; ?> col-md-<?php echo $col; ?> col-sm-6 col-xs-12 <?php if($i++%$column==0): ?>first<?php endif; ?>">
            	<?php echo Opalmembership_Template_Loader::get_template_part( 'content-single-package' ); ?>
        	</div>
		<?php endwhile; ?>
		</div>
		<?php the_posts_pagination( array(
			'prev_text'          => esc_html__( 'Previous page', 'opalmembership' ),
			'next_text'          => esc_html__( 'Next page', 'opalmembership' ),
			'before_page_number' => '<span class="meta-nav screen-reader-text">' . esc_html__( 'Page', 'opalmembership' ) . ' </span>',
		) ); ?>
	<?php else : ?>

		<?php get_template_part( 'content', 'none' ); ?>

	<?php endif; ?>
 
</div>