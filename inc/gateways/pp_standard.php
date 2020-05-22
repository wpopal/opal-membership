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

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

/**
 * @class   Opalmembership_Gateway_Pp_standard
 *
 * @version 1.0
 */
class Opalmembership_Gateway_Pp_standard extends Opalmembership_Abstract_Gateway {

	/**
	 * @var String $title
	 */
	public $title;

	/**
	 * @var String $title
	 */
	public $description;

	/**
	 * @var String $title
	 */
	public $icon;

	/**
	 * @var String $title
	 */
	public $testmode = false;

	/**
	 * @var String $title
	 */
	protected $log;

	/**
	 * @var String $paypal_uri
	 */
	protected $paypal_uri;

	/**
	 * @var
	 */
	protected $debug = true;

	/**
	 * Constructor
	 */
	public function __construct() {

		$this->title       = esc_html__( 'PayPal Standard', 'opalmembership' );
		$this->icon        = apply_filters( 'opalmembership_pp_standard_icon', '' );
		$this->description = esc_html__( 'Pay via PayPal; you can pay with your credit card if you don’t have a PayPal account.', 'opalmembership' );

		$this->testmode = opalmembership_options( 'test_mode' );

		$this->set_paypal_uris();

		add_filter( 'opalmembership_settings_gateways', [ $this, 'admin_fields' ] );
	}

	/**
	 * Set paypal uri base on enabling or not sandbox mode
	 */
	protected function set_paypal_uris( $ssl_check = false ) {
		if ( is_ssl() || ! $ssl_check ) {
			$protocal = 'https://';
		} else {
			$protocal = 'http://';
		}

		// Check the current payment mode
		if ( $this->testmode || $this->testmode == 'on' ) {
			$this->paypal_uri = $protocal . 'www.sandbox.paypal.com/cgi-bin/webscr';
		} else {
			$this->paypal_uri = $protocal . 'www.paypal.com/cgi-bin/webscr';
		}
	}

	/**
	 *
	 */
	public function admin_fields( $fields ) {

		$paypal_field = apply_filters( 'opalmembership_settings_gateway_pp_standard', [

			[
				'name'    => esc_html__( 'PayPal Standard', 'opalmembership' ),
				'desc'    => '<hr>',
				'type'    => 'opalmembership_title',
				'id'      => 'opalmembership_title_gateway_settings_2',
				'default' => '1',
			],
			[
				'name' => esc_html__( 'PayPal Email', 'opalmembership' ),
				'desc' => esc_html__( 'Enter your PayPal account\'s email', 'opalmembership' ),
				'id'   => 'paypal_email',
				'type' => 'text_email',
			],
			[
				'name' => esc_html__( 'PayPal Page Style', 'opalmembership' ),
				'desc' => esc_html__( 'Enter the name of the page style to use, or leave blank to use the default', 'opalmembership' ),
				'id'   => 'paypal_page_style',
				'type' => 'text',
			],
		] );

		return array_merge( $fields, $paypal_field );
	}

	/**
	 *
	 */
	protected function make_transquery( $payment_data, $ssl_check = false ) {
		global $opalmembership_options;

		// Only send to PayPal if the pending payment is created successfully
		$listener_url = add_query_arg( 'pmgw-callback', 'Opalmembership_Pp_standard_IPN', home_url( 'index.php' ) );

		// echo $listener_url;die;
		$payment = $payment_data->id;

		// Get the success url
		$return_url = add_query_arg( [
			'payment_confirmation' => 'paypal',
			'payment_id'           => $payment,
			'status'               => 'completed',
			'_wpnonce'             => wp_create_nonce( 'opalmemebership-payment-completed' ),
		], get_permalink( $opalmembership_options['success_page'] ) );

		// Get the failed url
		$cancel_return = add_query_arg( [
			'payment_confirmation' => 'paypal',
			'payment_id'           => $payment,
			'status'               => 'failed',
			'_wpnonce'             => wp_create_nonce( 'opalmemebership-payment-failed' ),
		], get_permalink( $opalmembership_options['success_page'] ) );

		$paypal_redirect = trailingslashit( apply_filters( 'opalmembership_pp_standard_uri', $this->paypal_uri ) ) . '?';

		$paypal_redirect = $this->testmode ? $paypal_redirect . 'test_ipn=1&' : $paypal_redirect;

		if ( empty( $payment_data->invoice_key ) ) {
			$payment_data->invoice_key = md5( "paypal_standard" . time() . $payment_data->email );
		}

		// Setup PayPal arguments
		$paypal_args = [
			'business'      => opalmembership_options( 'paypal_email', '' ),
			'email'         => $payment_data->email,
			'invoice'       => $payment_data->invoice_key,
			'no_shipping'   => '1',
			'shipping'      => '0',
			'no_note'       => '1',
			'currency_code' => opalmembership_get_currency(),
			'charset'       => get_bloginfo( 'charset' ),
			'custom'        => $payment,
			'rm'            => is_ssl() ? 2 : 1,
			'return'        => $return_url,
			'cancel_return' => $cancel_return,
			'notify_url'    => $listener_url,
			'page_style'    => opalmembership_options( 'paypal_page_style' ),
			'cbt'           => get_bloginfo( 'name' ),
			'bn'            => 'OpalMembership_Cart',
			'paymentaction' => 'sale',
		];

		$paypal_args['first_name'] = $payment_data->billing['first_name'];
		$paypal_args['last_name']  = $payment_data->billing['last_name'];
		$paypal_args['tax_cart']   = 0;

		$paypal_args['address_1'] = $payment_data->billing['address_1'];
		$paypal_args['address_2'] = $payment_data->billing['address_2'];
		$paypal_args['city']      = $payment_data->billing['city'];
		$paypal_args['country']   = $payment_data->billing['country'];

		$items_args  = $this->get_items( $payment_data );
		$paypal_args = array_merge( $paypal_args, $items_args );

		$paypal_extra_args = [
			'cmd'    => '_cart',
			'upload' => '1',
		];

		$paypal_args = array_merge( $paypal_extra_args, $paypal_args );

		$paypal_args     = apply_filters( 'opalmembership_pp_standard_redirect_args', $paypal_args, $payment_data );
		$paypal_redirect .= http_build_query( $paypal_args, '', '&' );
		$paypal_redirect = str_replace( '&amp;', '&', $paypal_redirect );

		OpalMembership()->session()->set( 'opalmembership_confirmed_payment_id', $payment );
		OpalMembership()->clear_payment_session();
		OpalMembership()->clear_cart_session();

		wp_redirect( $paypal_redirect );
		exit();
	}

	/**
	 *
	 */
	protected function get_items( $payment_data ) {

		$paypal_args = [];
		$items       = [ $payment_data->cart_detail ];
		$i           = 1;

		foreach ( $items as $item ) {

			$item['package_title'] .= ' - ' . opalmembership_price_format( $item['price'] );

			$paypal_args[ 'item_name_' . $i ] = stripslashes_deep( html_entity_decode( wp_strip_all_tags( $item['package_title'] ), ENT_COMPAT, 'UTF-8' ) );
			$paypal_args[ 'quantity_' . $i ]  = 1;
			$paypal_args[ 'amount_' . $i ]    = $item['price'];

			$i++;
		}

		return $paypal_args;
	}

	/**
	 *
	 */
	public function process( $payment_id, $posted ) {
		$payment = new Opalmembership_Payment( $payment_id );
		$payment->update_status( 'processing', esc_html__( 'Payment to be made upon delivery.', 'opalmembership' ) );
		$query_http = $this->make_transquery( $payment );

		return true;
	}
}
