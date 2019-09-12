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
class Opalmembership_Emails {

	 
	/* protected $instance instead. singleton */
	protected static $instance = null;

	
	/* email constructor */
	public static function init() {
 		
	 		// send email when payment is completed 
	// 	add_action(  'opalmembership_update_payment_status_to_pending', );
	 	add_action(  'opalmembership_update_payment_status_to_processing', array( __CLASS__ , 'new_purchased_email') , 10 ,1 );
	  	add_action(  'opalmembership_update_payment_status_to_completed' , array( __CLASS__ , 'completed_email'), 10, 1 );
	 	add_action(  'opalmembership_update_payment_status_to_cancelled' , array( __CLASS__ , 'cancelled_email'), 10, 1  );
	 	add_action(  'opalmembership_update_payment_status_to_refunded'  , array( __CLASS__ , 'refunded_email'), 10, 1 );
	 	//	add_action(  'opalmembership_update_payment_status_to_failed' );
 		// send email if payment is cancelled or returned 

 		//send email when package is expired
 		add_action( 'opalmembership_process_user_package_expired', array( __CLASS__ , 'send_expired_email'), 10, 1 );
	}

	public static function send_expired_email( $user ){
	//	return ;
		$user_id = $user->ID; 
		$package_id = (int)get_user_meta( $user_id, OPALMEMBERSHIP_USER_PREFIX_.'package_id', true );
		$payment_id =  (int)get_user_meta( $user_id, OPALMEMBERSHIP_USER_PREFIX_.'payment_id', true );

		if( $package_id && $payment_id ){
			$args = self::get_email_args_by_payment( $payment_id );

			$subject = opalmembership_get_option( 'expired_email_subject' );
			$body 	 = opalmembership_get_option( 'expired_email_body' );
	 	 	if( $body && $subject ){
				// repleace all custom tags
				$subject = self::replace_shortcode( $args, $subject );
				$body 	 = self::replace_shortcode( $args, $body );
				// send mail for customer
				self::send( $args['user_mail'], $subject, $body );	
				// send mmail for admin store
				self::send( opalmembership_get_option('from_email'), $subject, $body );	
				update_user_meta( $user_id, OPALMEMBERSHIP_USER_PREFIX_.'send_expired_email', 1 );
			}	
		}
		
	}
	/**
	 * get data of newrequest email
	 *
	 * @var $args  array: booking_id , $body 
	 * @return text: message
	 */
	public static function replace_shortcode( $args, $body ) {

		$tags =  array(
			'user_name' 	=> "",
			'user_mail' 	=> "",
			'purchased_date' => "",
			'package_membership' => "",
			'site_name' => '',
			'site_link'	=> '',
			'payment_number' => '',
			'payment_id' => '',
			'membership_page' => ''
		);
		$tags = array_merge( $tags, $args );

		extract( $tags );

		$tags 	 = array( "{user_mail}",
						  "{user_name}",
						  "{purchased_date}",
						  "{site_name}",
						  "{site_link}",
						  "{current_time}",
						  '{package_membership}',
						  '{membership_page}' , 
						  '{payment_id}'
		);

		$values  = array(   $user_mail, 
							$user_name ,
							$purchased_date ,
							get_bloginfo( 'name' ) ,
							get_home_url(), 
							date("F j, Y, g:i a"),
							$package_membership,
							$membership_page, 
							$payment_id

		);

		$message = str_replace($tags, $values, $body);

		return $message;
	}

	/**
	 * Send email
	 */
	public static function send( $emailto, $subject, $body ){

		$from_name 	= opalmembership_get_option('from_name'  , get_bloginfo( 'name' ) );
		$from_email = opalmembership_get_option('from_email' , get_bloginfo( 'admin_email' ) );
		
		$headers 	= sprintf( "From: %s <%s>\r\n Content-type: text/html", $from_name, $from_email );

		wp_mail( @$emailto, @$subject, @$body, $headers );

	}

	/**
	 *
	 */
	public static function get_email_args_by_payment( $payment_id ){
		
		$payment 	   = new Opalmembership_Payment( $payment_id );
		$package       = new Opalmembership_Package( $payment ->package_id );
		$duration_unit = $package->get_post_meta( 'duration_unit' ); 
		$duration 	   = absint( $package->get_post_meta( 'duration' ) );

		$package_info  = esc_html__('Package:'  ,'opalmembership') . ' ' . $package->post_title .'<br>';
		$package_info .= esc_html__('Price:' ,'opalmembership') . ' ' .  $package->get_price_html() .'<br>';
		$package_info .= esc_html__('Duration:' ,'opalmembership') . ' ' .  $duration  .' '. $duration_unit .'<br>';

		$package_info  = apply_filters( 'opalmembership_email_package_info', $package_info, $package );
		$user    	   = get_userdata( $payment->user_id ); 

		$args = array(
			'user_mail' 		 => $payment->email,
			'package_membership' => $package_info,
			'membership_page'	 => opalmembership_get_payment_history_detail_page_uri( $payment ->package_id ),
			'user_name'			 => $user->display_name,
			'purchased_date'	 => $payment->payment_date,
			'payment_number'	 => $payment->get_payment_number(),
			'payment_id'	 	 => $payment->get_payment_number(),
		); 

		return $args; 
	}

	/**
	 *
	 */
	public static function completed_email( $payment_id ){

		$args = self::get_email_args_by_payment( $payment_id );

		$subject = opalmembership_get_option( 'confirmed_email_subject' );
		$body 	 = opalmembership_get_option( 'confirmed_email_body' );

		// repleace all custom tags
		$subject = self::replace_shortcode( $args, $subject );
		$body 	 = self::replace_shortcode( $args, $body );

		self::send( $args['user_mail'], $subject, $body );
	}

	/**
	 *
	 */
	public static function new_purchased_email( $payment_id ){

		$args = self::get_email_args_by_payment( $payment_id );

		$subject = opalmembership_get_option( 'newpayment_email_subject' );
		$body 	 = opalmembership_get_option( 'newpayment_email_body' );
 
		// repleace all custom tags
		$subject = self::replace_shortcode( $args, $subject );
		$body 	 = self::replace_shortcode( $args, $body );


		// send mail for customer
		self::send( $args['user_mail'], $subject, $body );	
				// send mmail for admin store
		self::send( opalmembership_get_option('from_email'), $subject, $body );		
	}

	/**
	 *
	 */
	public static function cancelled_email( $payment_id ){

		$args = self::get_email_args_by_payment( $payment_id );

		$subject = opalmembership_get_option( 'cancelled_email_subject' );
		$body 	 = opalmembership_get_option( 'cancelled_email_body' );
 
		// repleace all custom tags
		$subject = self::replace_shortcode( $args, $subject );
		$body 	 = self::replace_shortcode( $args, $body );

		self::send( $args['user_mail'], $subject, $body );
	}
	
	/**
	 * refunded email
	 */
	public static function refunded_email( $payment_id ){

		$args = self::get_email_args_by_payment( $payment_id );

		$subject = opalmembership_get_option( 'refunded_email_subject' );
		$body 	 = opalmembership_get_option( 'refunded_email_body' );
 
		// repleace all custom tags
		$subject = self::replace_shortcode( $args, $subject );
		$body 	 = self::replace_shortcode( $args, $body );

		self::send( $args['user_mail'], $subject, $body );
	}
}

Opalmembership_Emails::init();