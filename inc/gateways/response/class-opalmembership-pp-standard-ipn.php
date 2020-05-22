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
 * @class Opalmembership_Gateway_Pp_standard
 *
 * @version 1.0
 */
class Opalmembership_Gateway_Pp_standard_INP {

	public $debug;

	public $receiver_email;
	/**
	 * Constructor
	 */
	public function __construct(){

		$this->log =  new Opalmembership_Logger();
		$this->testmode    =  opalmembership_options('test_mode');
		$this->debug  = true;

		$this->receiver_email =  opalmembership_options('paypal_email');
		$this->set_paypal_uris();

		add_action( 'opalmembership_legacy_paypal_ipn', array( $this, 'check_ipn_response' ) );
		add_action( 'opalmembership_valid_paypal_standard_ipn_request', array( $this, 'callback') );
		// $this->listen_for_paypal_ipn();

		add_action( 'init', array( $this, 'listen_for_paypal_ipn' ) );
	}

	/**
	 * Set paypal uri base on enabling or not sandbox mode
	 */
	protected function set_paypal_uris( $ssl_check=false ){
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
	 * Listens for a PayPal IPN requests and then sends to the processing function
	 *
	 * @since 1.0
	 * @return void
	 */
	public function listen_for_paypal_ipn() {
		// Regular PayPal IPN
		if ( isset( $_GET['pmgw-callback'] ) && $_GET['pmgw-callback'] == 'Opalmembership_Pp_standard_IPN' ) {
			do_action( 'opalmembership_legacy_paypal_ipn' ); exit;
		}
	}

	public function callback( $posted ) {
		 $posted = stripslashes_deep( $posted );

		// Custom holds post ID
		if ( ! empty( $posted['invoice'] ) && ! empty( $posted['custom'] ) ) {

			$payment = new Opalmembership_Payment( $posted['custom'] );

			// $payment->update_status( 'processing', esc_html__( 'Payment to be made upon delivery.', 'opalmembership' ) );

			if ( $this->debug ) {
				opalmembership_insert_payment_note( $payment->id, 'Found Payment #' . $payment->id );
			}

			// Lowercase returned variables
			$posted['payment_status'] 	= strtolower( $posted['payment_status'] );
			$posted['txn_type'] 		= strtolower( $posted['txn_type'] );

			if ( 1 == $posted['test_ipn'] && 'pending' == $posted['payment_status'] ) {
				$posted['payment_status'] = 'completed';
			}

			// We are here so lets check status and do actions
			switch ( $posted['payment_status'] ) {
				case 'completed' :
				case 'pending' :

					// Check order not already completed
					if ( $payment->has_status( 'completed' ) ) {
						if ( $this->debug ) {
							opalmembership_insert_payment_note( $payment->id,'paypal', 'Aborting, Order #' . $payment->id . ' is already complete.' );
						}
						exit;
					}

					// Check valid txn_type
					$accepted_types = array( 'cart', 'instant', 'express_checkout', 'web_accept', 'masspay', 'send_money' );

					if ( ! in_array( $posted['txn_type'], $accepted_types ) ) {
						if ( $this->debug ) {
							opalmembership_insert_payment_note( $payment->id,  'Aborting, Invalid type:' . $posted['txn_type'] );
						}
						exit;
					}

					// Validate currency
					if ( $payment->get_payment_currency() != $posted['mc_currency'] ) {
						if ( $this->debug ) {
							opalmembership_insert_payment_note( $payment->id,  'Payment error: Currencies do not match (sent "' . $payment->get_payment_currency() . '" | returned "' . $posted['mc_currency'] . '")' );
						}

						// Put this order on-hold for manual checking
						$payment->update_status( 'on-hold', sprintf( esc_html__( 'Validation error: PayPal currencies do not match (code %s).', 'opalmembership' ), $posted['mc_currency'] ) );
						exit;
					}

					// Validate amount
					if ( $payment->get_payment_total() != $posted['mc_gross'] ) {
						if ( $this->debug ) {
							opalmembership_insert_payment_note( $payment->id, 'Payment error: Amounts do not match (gross ' . $posted['mc_gross'] . ')' );
						}

						// Put this order on-hold for manual checking
						$payment->update_status( 'on-hold', sprintf( esc_html__( 'Validation error: PayPal amounts do not match (gross %s).', 'opalmembership' ), $posted['mc_gross'] ) );
						exit;
					}

					// Validate Email Address
					if ( strcasecmp( trim( $posted['receiver_email'] ), trim( $this->receiver_email ) ) != 0 ) {
						if ( $this->debug ) {
							opalmembership_insert_payment_note( $payment->id, "IPN Response is for another one: {$posted['receiver_email']} our email is {$this->receiver_email}" );
						}

						// Put this order on-hold for manual checking
						$payment->update_status( 'on-hold', sprintf( esc_html__( 'Validation error: PayPal IPN response from a different email address (%s).', 'opalmembership' ), $posted['receiver_email'] ) );

						exit;
					}

					$payment->add_note( esc_html__( 'IPN payment completed', 'opalmembership' ) );

					if ( $posted['payment_status'] == 'completed' ) {
						$payment->add_note( esc_html__( 'IPN payment completed', 'opalmembership' ) );
						$txn_id = ( ! empty( $posted['txn_id'] ) ) ? trim( $posted['txn_id'] ) : '';
						$payment->payment_complete( $txn_id );
						$payment->update_status( 'completed', sprintf( esc_html__( 'PayPal Transaction ID: %s', 'opalmembership' ), $txn_id ) );

					} else {
						$payment->update_status( 'on-hold', sprintf( esc_html__( 'Payment pending: %s', 'opalmembership' ), $posted['pending_reason'] ) );
					}

					if ( $this->debug ) {
						opalmembership_insert_payment_note( $payment->id, 'Payment complete.' );
					}

				break;
				case 'denied' :
				case 'expired' :
				case 'failed' :
				case 'voided' :
					// Order failed
					$payment->update_status( 'failed', sprintf( esc_html__( 'Payment %s via IPN.', 'opalmembership' ), strtolower( $posted['payment_status'] ) ) );
				break;
				case 'refunded' :

					// Only handle full refunds, not partial
					if ( $payment->get_total() == ( $posted['mc_gross'] * -1 ) ) {

						// Mark order as refunded
						$payment->update_status( 'refunded', sprintf( esc_html__( 'Payment %s via IPN.', 'opalmembership' ), strtolower( $posted['payment_status'] ) ) );

						/*
						$this->send_ipn_email_notification(
							sprintf( esc_html__( 'Payment for order %s refunded/reversed', 'opalmembership' ), $payment->get_order_number() ),
							sprintf( esc_html__( 'Order %s has been marked as refunded - PayPal reason code: %s', 'opalmembership' ), $payment->get_order_number(), $posted['reason_code'] )
						); */
					}

				break;
				case 'reversed' :

					// Mark order as refunded
					$payment->update_status( 'on-hold', sprintf( esc_html__( 'Payment %s via IPN.', 'opalmembership' ), strtolower( $posted['payment_status'] ) ) );

				/*	$this->send_ipn_email_notification(
						sprintf( esc_html__( 'Payment for order %s reversed', 'opalmembership' ), $payment->get_order_number() ),
						sprintf(esc_html__( 'Order %s has been marked on-hold due to a reversal - PayPal reason code: %s', 'opalmembership' ), $payment->get_order_number(), $posted['reason_code'] )
					);
				*/
				break;
				case 'canceled_reversal' :
				/*	$this->send_ipn_email_notification(
						sprintf( esc_html__( 'Reversal cancelled for order %s', 'opalmembership' ), $payment->get_order_number() ),
						sprintf( esc_html__( 'Order %s has had a reversal cancelled. Please check the status of payment and update the order status accordingly.', 'opalmembership' ), $payment->get_order_number() )
					); */
				break;
				default :
					// No action
				break;
			}

			exit;
		}
	}

	/**
	 *
	 */
	public function check_ipn_response(){

		@ob_clean();
		$ipn_response = ! empty( $_POST ) ? $_POST : false;

		if ( $ipn_response && $this->valid_ipn_response( $ipn_response ) ) {

			do_action( "opalmembership_valid_paypal_standard_ipn_request", $ipn_response );

		} else {
			$this->log->add( 'paypal', 'Error response 222: '. "PayPal IPN Request Failure" );
			wp_die( "PayPal IPN Request Failure", "PayPal IPN", array( 'response' => 200 ) );
		}
	}

	/**
	 *
	 */
	public function valid_ipn_response( $ipn_response ){
	//	if ( $this->debug ) {
			$this->log->add( 'paypal', 'Checking IPN response is valid via ' . $this->paypal_uri. '...' );
		//}

		// Get received values from post data
		$validate_ipn = array( 'cmd' => '_notify-validate' );
		$validate_ipn += stripslashes_deep( $ipn_response );

		// make params postback vars to paypal
		$params = array(
			'body' 			=> $validate_ipn,
			'method'      	=> 'POST',
			'timeout'     	=> 45,
			'redirection' 	=> 5,
			'httpversion' 	=> '1.1',
			'blocking'    	=> true,
			'headers'     	=> array(
				'host'         => 'www.paypal.com',
				'connection'   => 'close',
				'content-type' => 'application/x-www-form-urlencoded',
				'post'         => '/cgi-bin/webscr HTTP/1.1',

			),
			'sslverify'   => false,
		);

		if ( $this->debug ) {
			$this->log->add( 'paypal', 'IPN Request: ' . print_r( $params, true ) );
		}

		// auto post back to get a response
		$response = wp_safe_remote_post( $this->paypal_uri, $params );

		if ( $this->debug ) {
			$this->log->add( 'paypal', 'IPN Response: ' . print_r( $response, true ) );
		}

		// check to see if the request was valid
		if ( ! is_wp_error( $response ) && $response['response']['code'] >= 200 && $response['response']['code'] < 300 && strstr( $response['body'], 'VERIFIED' ) ) {
			if ( $this->debug ) {
				$this->log->add( 'paypal', 'Received valid response from PayPal' );
			}
			return true;
		}

		if ( $this->debug ) {
			$this->log->add( 'paypal', 'Received invalid response from PayPal' );
			if ( is_wp_error( $response ) ) {
				$this->log->add( 'paypal', 'Error response: ' . $response->get_error_message() );
			}
		}

		return false;

	}

}

new Opalmembership_Gateway_Pp_standard_INP();
