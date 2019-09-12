<?php
/**
 * Give Session
 *
 * This is a wrapper class for WP_Session / PHP $_SESSION and handles the storage of Give sessions
 *
 * @package     Give
 * @subpackage  Classes/Session
 * @copyright   Copyright (c) 2015, WordImpress
 * @license     http://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since       1.0
 */

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Give_Session Class
 *
 * @since 1.0
 */
class Opalmembership_Coupon {

	public $total ;

	public  function check_existed( $code = '' ){
		$args = array(
			'post_type'	=> 'membership_coupons',
			's'			=> $code
		);

		$data =  new WP_Query( $args );

		if( $data->found_posts ) {
			return $data->post;
		}
		return false;
	}

	/* get coupon by package_id and coupon code */
	public  function get_coupon( $package_id = '', $code = '' ){

		$output = array( 'status' => false, 'message' => '', 'info' => array() );
		$coupon_data = $this->check_existed( $code );

		if ( ! $coupon_data ) {
			$output['message'] = esc_html__( 'The coupon is not valid, please try with other', 'opalmembership' );
		} else {

			if( $coupon_data->post_title != $code ){
				$output['message'] = esc_html__( 'The coupon is not valid, please try with other', 'opalmembership' );
				return $output;
			}

			$keys = array( 'applyfor', 'usage_limit', 'expired_date', 'start_date', 'value', 'type' );

			$coupon = array();
			$coupon['code'] = trim( $coupon_data->post_title );
			$coupon['coupon_id'] = $coupon_data->ID;
			foreach ( $keys as $key ) {
				$coupon[$key] = get_post_meta( $coupon_data->ID , OPALMEMBERSHIP_COUPON_PREFIX . $key, true );
			}

			if( $coupon['expired_date'] && current_time( 'timestamp' ) > strtotime( $coupon['expired_date'] ) ) {
		 	 	$output['message'] = esc_html__( 'This coupon has expired', 'opalmembership' );
		 	 	return $output;
		 	}

		 	if( $coupon['start_date'] && current_time( 'timestamp' ) < $coupon['start_date'] ) {
		 	 	$output['message'] = esc_html__( 'This coupon is not started', 'opalmembership' );
		 	 	return $output;
		 	}

		 	if( $coupon['applyfor'] != 0  && $coupon['applyfor'] != $package_id ) {
		 	 	$output['message'] = esc_html__( 'This coupon is not applied for this package', 'opalmembership' );
		 	 	return $output;
		 	}

		 	$sessions = opalmembership_get_purchase_session();
		 	$cart = ! empty( $sessions['cart'] ) ? $sessions['cart'] : array();
		 	$total = isset( $cart['total'] ) ? floatval( $cart['total'] ) : 0;
		 	$discount = 0;
		 	if( $coupon['type'] == 'percenatage' ) {
				$discount += ($total) * $coupon['value'] / 100;
			} else {
				$discount +=  $coupon['value'];
			}
		 	if ( ! empty( $cart ) && $total < $discount ) {
				$output['message'] = esc_html__( 'This coupon is not applied for this package', 'opalmembership' );
				return $output;
		 	}

		 	$output['status']  = true;
		 	$output['message'] = esc_html__( 'Applied this coupon successfully', 'opalmembership' );
		 	$output['info'] = $coupon;

		 	$this->add_discount( $coupon );
		}

		return $output;
	}

	public function add_discount( $coupon ){
		$cart = opalmembership_get_purchase_session();

		$cart['coupons'][$coupon['code']] = $coupon;

		opalmembership_set_purchase_session( $cart );

		return true ;
	}

	public function get_total_discount(){
		return $this->total;
	}

	public function get_total( $total ){

		$cart = opalmembership_get_purchase_session();

		$discount = 0 ;
		if( isset($cart['coupons']) ){
			foreach( $cart['coupons'] as $coupon ) {
				if( $coupon['type'] == 'percenatage' ) {
					$discount += ($total) * $coupon['value'] / 100;
				} else {
					$discount +=  $coupon['value'];
				}
			}
		}

	 	$this->total = $discount;

		return $total - $discount;

	}
}