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
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * @class   OpalMembership_Checkout
 *
 * @version 1.0
 */
class Opalmembership_Checkout {

	/**
	 * @var Integer $enable_signup
	 */
	public $enable_signup = 1;

	/**
	 * @var Integer $enable_guest_checkout
	 */
	public $enable_guest_checkout = 1;

	/**
	 * @var Array $billing_fields store billing address fields
	 *
	 * @access protected
	 */
	public $billing_fields = [];

	/**
	 * @var Array $post store post's data
	 *
	 * @access protected
	 */
	protected $post = [];

	public $actived_gateway;

	/**
	 * Constructor
	 */
	public function __construct() {
		add_action( 'opalmembership_checkout_billing', [ $this, 'form_billing' ] );
		add_action( 'opalmembership_checkout_payment_gateways', [ $this, 'show_payment_gateways' ] );

		if ( isset( $_POST ) ) {
			$this->post = $_POST;
		}
		global $current_user;
		if ( isset( $current_user ) ) {
			$this->billing_fields['email']      = $current_user->user_email;
			$this->billing_fields['first_name'] = $current_user->user_firstname;
			$this->billing_fields['last_name']  = $current_user->user_lastname;
		}

		/* enabled signup WP */
		$this->enable_signup = get_option( 'users_can_register' );
		/* Membership enabled guest checkout */
		$this->enable_guest_checkout = opalmembership_get_option( 'enable_guest_checkout' );
	}

	/**
	 * Get a instance of this
	 */
	public static function getInstance() {
		static $_instance;
		if ( ! $_instance ) {
			$_instance = new self();
		}

		return $_instance;
	}

	/**
	 * render checkout form
	 */
	public function checkout_form() {
		$purchase_info = opalmembership_get_purchase_session();
		$msg           = $this->is_nolimited_purchased( '' );

		if ( ! empty( $msg ) ) {
			opalmembership_add_notice( 'warning', $msg );
		}

		if ( ! empty( $purchase_info ) && isset( $purchase_info['cart'] ) ) {
			if ( $purchase_info['cart']['package_id'] ) {
				return Opalmembership_Template_Loader::get_template_part( 'checkout/checkout-form', [ 'checkout' => $this ] );
			}
		} else {
			return Opalmembership_Template_Loader::get_template_part( 'no-found', [ 'checkout' => $this, 'message' => esc_html__( 'You have no package', 'opalmebership' ) ] );
		}
	}

	protected function billing_fields() {
		$ss = OpalMembership()->session()->get( 'opalmembership_billing_address' );

		if ( $ss ) {
			$this->billing_fields = array_merge( $this->billing_fields, $ss );
		}

		$fields = OpalMembership()->address()->get_fields( $this->billing_fields );

		return $fields;
	}

	/**
	 * Render checkout form
	 */
	public function form_billing() {
		$fields = $this->billing_fields();
		echo Opalmembership_Template_Loader::get_template_part( 'checkout/billing-form', [ 'fields' => $fields, 'checkout' => $this ] );
	}

	/**
	 *
	 */
	public function get_billing_field_val( $key ) {
		if ( isset( $this->billing_fields[ $key ] ) ) {
			return $this->billing_fields[ $key ];
		}

		return;
	}

	/**
	 * display payment gateway form
	 */
	public function show_payment_gateways() {

		$ogateway = OpalMembership()->gateways();

		$default_gateway = OpalMembership()->session()->get( 'opalmembership_payment_method' );
		$default_gateway = $default_gateway ? $default_gateway : opalmembership_get_option( 'default_gateway' );

		$ogateway->set_gateway( $default_gateway );

		$gateways = $ogateway->get_list();

		echo Opalmembership_Template_Loader::get_template_part( 'gateways/gateways_form', [ 'checkout' => $this, 'gateways' => $gateways ] );
	}

	/**
	 * display payment gateway form
	 */
	public function preprocess_purchase() {

		if ( isset( $this->post['billing'] ) ) {
			OpalMembership()->session()->set( 'opalmembership_billing_address', $this->post['billing'] );
			$this->billing_fields = $this->post['billing'];
		}

		if ( isset( $this->post['payment_method'] ) ) {
			OpalMembership()->session()->set( 'opalmembership_payment_method', $this->post['payment_method'] );
			$this->actived_gateway = $this->post['payment_method'];
		}

		/**
		 * Validate billing fields
		 */
		$fields = $this->billing_fields();
		$check  = OpalMembership()->address()->validate( $fields, $this->billing_fields );

		/**
		 * Validate Payment Infos
		 */
		if ( isset( $this->post['payment_method'] ) ) {
			$this->actived_gateway = $this->post['payment_method'];
			$payment_info          = isset( $this->post['payment-info'] ) ? $this->post['payment-info'] : [];
			$payment               = OpalMembership_Payment_gateways::getInstance()->gateway( $this->actived_gateway );
			$purchase              = opalmembership_get_purchase_session();

			// Check if free package.
			if ( 0 == $purchase['cart']['price'] ) {
				$payment_valid = [];
			} else {
				$payment_valid = $payment->validate( $payment_info );
			}

			$check = array_merge( $check, $payment_valid );
		}

		OpalMembership()->session()->set( 'opalmembership_valid_checkout_info', empty( $check ) );

		$check = $this->is_nolimited_purchased( $check );

		$check = apply_filters( 'opalmembership_preprocess_purchase', $check );

		return $check;
	}

	private function is_nolimited_purchased( $check ) {
		$purchase     = opalmembership_get_purchase_session();
		$current_user = wp_get_current_user();

		if ( $purchase && $current_user->ID ) {
			$package_id = $purchase['cart']['package_id'];
			$limit      = (int) get_post_meta( $package_id, OPALMEMBERSHIP_PACKAGES_PREFIX . 'maximum_purchased', true );

			if ( $limit > 0 ) {
				$purchased = Opalmembership_Query::get_user_purchased_package( $current_user->ID, $package_id );
				if ( $purchased >= $limit ) {
					return esc_html__( 'You have too limited to purchase this package, please try to purchase other.', 'opalmembership' );
				}
			}
		}

		return $check;
	}

	/**
	 *
	 */
	public function process_checkout() {
		$checked  = $this->preprocess_purchase();
		$purchase = opalmembership_get_purchase_session();

		if ( empty( $checked ) && ! empty( $purchase ) && isset( $this->post['payment_method'] ) ) {
			$current_user = wp_get_current_user();
			$payment      = new OpalMembership_Payment();

			$purchase['actived_gateway'] = $this->post['payment_method'];

			$user = [
				'id'         => isset( $current_user->ID ) && $current_user->ID ? $current_user->ID : 0,
				'first_name' => sanitize_text_field( $this->billing_fields['first_name'] ),
				'last_name'  => sanitize_text_field( $this->billing_fields['last_name'] ),
				'email'      => sanitize_text_field( $this->billing_fields['email'] ),
			];

			$data = [
				'price'         => opalmembership_price( $purchase['cart']['total'] ),
				'date'          => time(),
				'user_email'    => sanitize_text_field( $this->billing_fields['email'] ),
				'purchase_key'  => rand(),
				'currency'      => opalmembership_get_currency(),
				'package_title' => $purchase['cart']['package_title'],
				'package_id'    => $purchase['cart']['package_id'],
				'billing_info'  => $this->billing_fields,
				'user_info'     => $user,
				'status'        => 'pending',
				'cart_detail'   => $purchase['cart'],
				'gateway'       => $purchase['actived_gateway'],
				'coupons'       => $purchase['coupons'],
				'discount'      => opalmembership_price( isset( $purchase['cart']['discount'] ) ? $purchase['cart']['discount'] : 0 ),
			];

			// create pending order
			$paymentID = OpalMembership()->session()->get( 'opalmembership_waiting_payment' );
			if ( ! $paymentID ) {
				$paymentID = $payment->insert( $data );
				OpalMembership()->session()->set( 'opalmembership_waiting_payment', $paymentID );
			}

			/// if cart total is price < 0
			if ( $paymentID && $data['price'] <= 0 ) {
				$this->set_free_purchased( $paymentID );

				OpalMembership()->clear_payment_session();
				OpalMembership()->clear_cart_session();

				OpalMembership()->session()->set( 'opalmembership_confirmed_payment_id', $paymentID );
				wp_redirect( opalmembership_get_success_page_uri() );
				exit;
			}

			if ( ! empty( $paymentID ) ) {

				$payment = OpalMembership_Payment_gateways::getInstance()->gateway( $this->actived_gateway );

				if ( $payment ) {

					$done = $payment->process( $paymentID, $this->post );

					OpalMembership()->clear_payment_session();
					OpalMembership()->clear_cart_session();

					if ( $done ) {
						OpalMembership()->session()->set( 'opalmembership_confirmed_payment_id', $paymentID );
						wp_redirect( opalmembership_get_success_page_uri() );
						exit;
					}
				}
			}
		}
		wp_redirect( opalmembership_get_checkout_page_uri() );
		die( esc_html__( 'The payment is not complete', 'opalmembership' ) );
	}

	private function set_free_purchased( $payment_id ) {
		$payment        = new OpalMembership_Payment( $payment_id );
		$default_status = apply_filters( 'opalmembership_free_purchased_default_status', 'completed' );
		$payment->update_status( $default_status, esc_html__( 'Payment to be made upon delivery.', 'opalmembership' ) );
	}

	/**
	 *
	 */
	public function thankyou() {
		$id = OpalMembership()->session()->get( 'opalmembership_confirmed_payment_id' );
		if ( ! $id && ! empty( $_REQUEST['payment_id'] ) ) {
			$id = absint( $_REQUEST['payment_id'] );
		}
		$payment = null;

		if ( absint( $id ) ) {
			$payment = new OpalMembership_Payment( $id );
		}

		echo Opalmembership_Template_Loader::get_template_part( 'checkout/thankyou', [ 'checkout' => $this, 'payment' => $payment ] );

		OpalMembership()->clear_payment_session();
		OpalMembership()->clear_cart_session();
	}

	/**
	 * Checkout failed transaction
	 */
	public function checkout_failed() {

	}
}
