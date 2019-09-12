<?php 
/**
 * Widget Functions
 *
 * Widget related functions and widget registration.
 *
 * @author 		WPOpal
 * @category 	Core
 * @package 	Opalmembership/Functions
 * @version     1.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

// Include widget classes.

include_once( 'widgets/current-package.php' );
include_once( 'widgets/quick-purchase.php' );

/**
 * Register Widgets.
 *
 * @since  1.0
 */
function opalmembership_register_widgets() {
	register_widget( 'Opalmembership_Current_Package' );
	register_widget( 'Opalmembership_Quick_Purchase' );
	

}
add_action( 'widgets_init', 'opalmembership_register_widgets' );
