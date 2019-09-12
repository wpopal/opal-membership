<?php

class Opalmembership_Quick_Purchase extends WP_Widget {

    /**
     * Register widget
    **/
    public function __construct() {

        parent::__construct(
            'opalmembership_quick_purchase', // Base ID
            esc_html__( 'OpalMembership: Quick Purchase', 'opalmembership' ), // Name
            array( 'description' => esc_html__( 'Display all packages and allow quick purchase one', 'opalmembership' ), ) // Args
        );

    }


    /**
     * Front-end display of widget
    **/
    public function widget( $args, $instance ) {

        global $before_widget, $after_widget, $before_title, $after_title, $post;
        extract( $args );

        $title = apply_filters('widget_title', $instance['title'] );
        $items_num = $instance['items_num'];

        echo ( $before_widget  );


            if ( $title ) echo ( $before_title ) . $title . ( $after_title );
            ?>
            <div class="widget-body">
                <?php
                $loop = Opalmembership_Query::get_packages();

                if(  $loop->have_posts() ): ?>
                    <div class="membership-quick-purchase">

                        <div class="dropdown dropdown-menu-select">
                          <button class="btn btn-default btn-block dropdown-toggle" type="button" data-toggle="dropdown" aria-haspopup="true" aria-expanded="true">
                           <span class="text-label"><?php esc_html_e( 'Select A Package', 'opalmembership' ) ?></span>
                            <span class="caret"></span>
                          </button>
                          <ul class="dropdown-menu" aria-labelledby="dropdownMenu1">
                            <?php while( $loop->have_posts() ): $loop->the_post(); ?>
                            <li><a data-package-id="<?php echo get_the_ID(); ?>" data-value="action"><?php the_title(); ?></a></li>
                            <?php endwhile; ?>
                          </ul>
                        </div>
                        <p>
                          <a target="_blank" href="<?php echo opalmembership_get_membership_page_uri();?>"><?php esc_html_e( 'View Packages Detail', 'opalmembership' );?></a>
                        </p>
                        <div class="membership-quick-action">
                            <button class="membership-add-to-purchase btn btn-danger btn-3d" data-action="membership_add_to_purchase" data-id=""><?php esc_html_e( 'By Now', 'opalmembership' );?></button>
                        </div>
                    </div>
                    <?php else : ?>
                    <div class="alert alert-warning">
                        <p><?php esc_html_e( 'You have not purchased any package now.', 'opalmembership' ); ?></p>
                        <p><a href="<?php echo opalmembership_get_membership_page_uri();?>"><?php esc_html_e( 'Click to this link to see plans', 'opalmembership' );?></a></p>
                    </div>
                <?php endif; ?>
            </div>
        <?php
        echo ( $after_widget );
    }


    /**
     * Sanitize widget form values as they are saved
    **/
    public function update( $new_instance, $old_instance ) {

        $instance = array();

        /* Strip tags to remove HTML. For text inputs and textarea. */
        $instance['title'] = strip_tags( $new_instance['title'] );
        $instance['items_num'] = strip_tags( $new_instance['items_num'] );

        return $instance;

    }

    /**
     * Back-end widget form
    **/
    public function form( $instance ) {

        /* Default widget settings. */
        $defaults = array(
            'title' => 'Upgrade membership',
        );
        $instance = wp_parse_args( (array) $instance, $defaults );

    ?>
        <p>
            <label for="<?php echo esc_attr( $this->get_field_id( 'title' ) ); ?>"><?php esc_html_e('Title:', 'opalmembership'); ?></label>
            <input type="text" id="<?php echo esc_attr( $this->get_field_id( 'title' ) ); ?>" name="<?php echo esc_attr( $this->get_field_name( 'title' ) ); ?>" value="<?php echo esc_attr( $instance['title'] ); ?>" class="widefat" />
        </p>

    <?php
    }
}