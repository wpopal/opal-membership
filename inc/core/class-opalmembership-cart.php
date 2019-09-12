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

class Opalmembership_Cart {

	protected $package ;

	public function __construct(){
		$this->load();
	}

	public function load(){

	 	if( ! $this->package ){
	 		$cart = opalmembership_get_purchase_session();
	 		$this->package = $cart['cart'];
	 	}

	}

	public function get_items(){

	}

	public function calculate_totals(){

	}

	public function reload_cart_with_discount( $coupon ){

		$this->package['total']		= $coupon->get_total( $this->package['price'] );
		$this->package['discount']	= $coupon->get_total_discount();

		$cart = opalmembership_get_purchase_session();
		$cart['cart']  = $this->package;
		opalmembership_set_purchase_session( $cart );

	}

	public function add_coupon( $coupon_code ) {

		$coupon = new Opalmembership_Coupon();
		$data = $coupon->get_coupon( $this->package['package_id'], $coupon_code );

		if( $data['status'] == true ){
			$this->reload_cart_with_discount( $coupon );
		}

		return ( $data );
	}

	public function remove_coupon( $code ){
		$cart = opalmembership_get_purchase_session();

		if( isset($cart['coupons'])  && $cart['coupons'][$code] ){
			unset( $cart['coupons'][$code] );
			opalmembership_set_purchase_session( $cart );
			$this->reload_cart_with_discount(  new Opalmembership_Coupon() );

		}
		return true;
	}

}