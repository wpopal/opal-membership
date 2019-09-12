<?php
/**
 * $Desc$
 *
 * @version    $Id$
 * @package    opalmembership
 * @author     Opal  Team <info@wpopal.com >
 * @copyright  Copyright (C) 2016 wpopal.com. All Rights Reserved.
 * @license    GNU/GPL v2 or later http://www.gnu.org/licenses/gpl-2.0.html
 *
 * @website  http://www.wpopal.com
 * @support  http://www.wpopal.com/support/forum.html
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

class Opalmembership_Ajax {

	/**
	 * Default templates
	 *
	 * @access public
	 * @param $template
	 * @return string
	 * @throws Exception
	 */
	public static function init(){

		$actions = array(
			'membership_add_to_purchase',
			'membership_preprocess_purchase',
			'membership_add_payment_note',
			'delete_order_note',
			'membership_apply_coupon',
			'membership_remove_coupon'
		);

		foreach( $actions as $action ){
			add_action( 'wp_ajax_'.trim($action), array( __CLASS__, trim($action) ) );
			add_action( 'wp_ajax_nopriv_'.trim($action), array( __CLASS__, trim($action) ) );
		}

	}

	/**
	 * Default templates
	 *
	 * @access public
	 * @param $template
	 * @return string
	 * @throws Exception
	 */
	public static function membership_add_to_purchase(){

		$session = opalmembership_get_purchase_session();

		if( empty( $session ) ){
			$session = array( 'cart' => array() , 'payment' => array(), 'info' => array(), 'coupons' => array()  );
		}

		if( ! isset($_POST['packageID']) ){
			self::output_json_message( false, esc_html__( 'The package was not existed, please try to select other', 'opalmembership' ) );
		}

		$package = new Opalmembership_Package( absint($_POST['packageID']) );

		if( is_object($package) && isset($package->ID) && $package->ID ){

			$price =  $package->get_price();

			$cart = array(
				'package_id' 	=> $package->ID,
				'package_title' => $package->post_title,
				'price'			=> $price,
				'total'			=> $price,
			);

			$session['cart'] = $cart;
			$session['coupons'] = array();

			opalmembership_set_purchase_session( $session );

			self::output_json_message( true, esc_html__( 'The package was added successfull, please wait for minute to switch payment process', 'opalmembership' ) );
		}

		self::output_json_message( false, esc_html__( 'The package was not existed, please try to select other', 'opalmembership' ) );
	}

	/**
	 *
	 */
	public static function membership_preprocess_purchase(){

		$data = OpalMembership()->checkout()->preprocess_purchase();

		if ( empty( $data ) ) {
			self::output_json_message( true, null, array( 'fields' => $data ) );
		}

		self::output_json_message( false, esc_html__('Could not save to cart, please try again.', 'opalmembership'), array( 'fields' => $data ) );
	}

	/**
	 * output stream information as json data with message and result or return the json object data
	 *
	 * @param $result boolean true or false
	 * @param $message  String or HTML
	 */
	public static function output_json_message( $result = false, $message = '', $args = array(), $return = false ){
		$out = new stdClass();
		$out->result = $result;
		$out->message = $message;

		if( $args ){
			foreach( $args as $key => $arg ){
				$out->$key = $arg;
			}
		}
		if( $return ){
			return json_encode( $out );
		} else {
			echo json_encode( $out ); die;
		}
	}

	public static function membership_add_payment_note(){

		if ( ! isset($_POST['post_id']) ) {
			return false;
		}

		$note_id = opalmembership_insert_payment_note( absint( $_POST['post_id'] ), 
													   sanitize_text_field( $_POST['note'] ) );

		echo opalmembership_get_payment_note_html( $note_id, $payment_id );

		do_action( 'opalmembership_insert_payment_note', $note_id, $payment_id, $note );

		exit;
	}

	public static function membership_apply_coupon(){

		if( isset($_POST['coupon_code']) && !empty($_POST['coupon_code']) ){

			$cart = new Opalmembership_Cart();

			$output  = $cart->add_coupon( sanitize_text_field( $_POST['coupon_code'] ) );

			self::output_json_message( $output['status'], $output['message'] );
		}

		self::output_json_message( false, esc_html__('Please enter your coupon to get a discount', 'opalmembership') );
		exit();
	}

	public static function membership_remove_coupon(){

		if( isset($_POST['coupon_code']) ){


			$cart = new Opalmembership_Cart();

			$output  = $cart->remove_coupon( sanitize_text_field( $_POST['coupon_code'] ) );
		}

		self::output_json_message( true, esc_html__('Please enter your coupon to get a discount', 'opalmembership') );
		exit();
	}

}

Opalmembership_Ajax::init();