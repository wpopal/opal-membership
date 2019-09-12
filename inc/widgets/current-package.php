<?php

class Opalmembership_Current_Package extends WP_Widget {

    /**
     * Register widget
    **/
    public function __construct() {

        parent::__construct(
            'opalmembership_current_package', // Base ID
            esc_html__( 'OpalMembership: Current Package', 'opalmembership' ), // Name
            array( 'description' => esc_html__( 'Display summary of current package', 'opalmembership' ), ) // Args
        );

    }


    /**
     * Front-end display of widget
    **/
    public function widget( $args, $instance ) {

        if( !is_user_logged_in()  ){
            return ;
        }

        global $before_widget, $after_widget, $before_title, $after_title, $post;
        extract( $args );



        $title = apply_filters('widget_title', $instance['title'] );
        $items_num = $instance['items_num'];

        echo ( $before_widget  );


            if ( $title ) echo ( $before_title ) . $title . ( $after_title );
            ?>
            <div class="widget-body">
            <?php

            global $current_user;

            wp_get_current_user();
            $user_id = $current_user->ID;

            $package_activation = get_user_meta( $user_id, OPALMEMBERSHIP_USER_PREFIX_.'package_activation', true );
            $package_expired = get_user_meta( $user_id, OPALMEMBERSHIP_USER_PREFIX_.'package_expired', true );
            $package_id = (int)get_user_meta( $user_id, OPALMEMBERSHIP_USER_PREFIX_.'package_id', true );
            $payment_id = (int)get_user_meta( $user_id, OPALMEMBERSHIP_USER_PREFIX_.'payment_id', true );
            
            if( $package_id ):
                $package = opalmembership_package( $package_id );
                $payment =  opalmembership_payment( $payment_id );
                $package_expired  = date( 'Y-m-d H:i:s', strtotime($package_expired) ); 
                $package_activation = date_i18n( get_option( 'date_format' ) . ' ' . get_option( 'time_format' ), strtotime( $package_activation  ) );
                $package_expired = date_i18n( get_option( 'date_format' ) . ' ' . get_option( 'time_format' ), strtotime( $package_expired  ) );

                ?>
                <div class="membership-content">
                    <ul>
                        <li><span><?php esc_html_e( 'Membership', 'opalmembership' );?></span>: <?php echo esc_html( $package->post_title ); ?> </li>
                        <li><span><?php esc_html_e( 'Activion Date', 'opalmembership' );?></span>: <?php echo esc_html( $package_activation ); ?></li>
                        <li><span><?php esc_html_e( 'Expired On', 'opalmembership' );?></span>: <span class="text-primary"><?php echo esc_html( $package_expired ); ?></span></li>
                        <?php do_action( 'opalmembership_current_package_summary_after', $package_id, $payment_id ); ?>
                    </ul>
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
            'title' => 'Current Package',
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