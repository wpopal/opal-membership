<?php
/**
 * $Desc$
 *
 * @version    $Id$
 * @package    $package$
 * @author     Opal  Team <info@wpopal.com >
 * @copyright  Copyright (C) 2014 wpopal.com. All Rights Reserved.
 * @license    GNU/GPL v2 or later http://www.gnu.org/licenses/gpl-2.0.html
 *
 * @website  http://www.wpopal.com
 * @support  http://www.wpopal.com/support/forum.html
 */

// Exit if accessed directly
if ( !defined( 'ABSPATH' ) ) exit;

/**
 * @class OpalMembership_Checkout
 *
 * @version 1.0
 */
class Opalmembership_CronJob{

	/**
	 * Constructor
	 *
	 * @version 1.0
	 */
	public function __construct(){
		add_action( 'opalmembership_check_subscription_status', array($this, 'check_membership_expired') );
	}

	/**
	 * Check all user is expired
	 */
	public function check_membership_expired(){
		$args = array();

		$args['meta_query'] = array('relation' => 'AND');
		 
        array_push($args['meta_query'], array(
            'key'     => OPALMEMBERSHIP_USER_PREFIX_.'payment_id',
            'value'   =>  0,
            'compare' => '>=',
            'type' => 'NUMERIC'
        ));

       array_push($args['meta_query'], array(
            'key'     => OPALMEMBERSHIP_USER_PREFIX_.'send_expired_email',
            'value'   =>  0,
            'compare' => '<=',
            'type' => 'NUMERIC'
        ));

        array_push($args['meta_query'], array(
            'key'     => OPALMEMBERSHIP_USER_PREFIX_.'package_expired',
            'value'   =>  time(),
            'compare' => '<=',
            'type' => 'NUMERIC'
        ));
		$user_query = new WP_User_Query( $args );

		if ( ! empty( $user_query->results ) ) {
	 		foreach ( $user_query->results as $user ) {
				do_action( 'opalmembership_process_user_package_expired', $user );
			}
	 	}	
	 	wp_reset_postdata(); 
	}
}

new Opalmembership_CronJob();