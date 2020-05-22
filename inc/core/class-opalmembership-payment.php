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
 * @class   Opalmembership_Payment_Getways
 *
 * @version 1.0
 */
class OpalMembership_Payment {


	/**
	 *
	 */
	public $id;

	/**
	 *
	 */
	public $billing;

	/**
	 *
	 */
	protected $gateway;

	/**
	 *
	 */
	public $payment_date;

	/**
	 *
	 */
	protected $user;

	/**
	 *
	 */
	protected $note;

	/**
	 *
	 */
	public $email;

	/**
	 *
	 */
	public $data;

	/**
	 *
	 */
	protected $payment_total;

	/**
	 *
	 */
	public $package_id;

	public $invoice_key;

	public $address;

	public $address2;

	public $city;

	public $country;

	public $postcode;

	protected $meta;

	public $user_info;

	public $company;

	public $cart_detail;

	protected $status;

	protected $payment_currency;

	public $user_id;

	public $payment_method_title;

	/**
	 *
	 */
	public function __construct( $id = null ) {
		if ( $id ) {
			$this->load( $id );
		}
	}


	/**
	 *
	 */
	public function load( $payment ) {

		$this->data             = get_post( $payment );
		$this->id               = $this->data->ID;
		$this->email            = get_post_meta( $this->id, OPALMEMBERSHIP_PAYMENT_PREFIX . 'user_email', true );
		$this->package_id       = get_post_meta( $this->id, OPALMEMBERSHIP_PAYMENT_PREFIX . 'package_id', true );
		$this->payment_total    = get_post_meta( $this->id, OPALMEMBERSHIP_PAYMENT_PREFIX . 'total', true );
		$this->meta             = get_post_meta( $this->id, OPALMEMBERSHIP_PAYMENT_PREFIX . 'meta', true );
		$this->billing          = get_post_meta( $this->id, OPALMEMBERSHIP_PAYMENT_PREFIX . 'billing', true );
		$this->gateway          = get_post_meta( $this->id, OPALMEMBERSHIP_PAYMENT_PREFIX . 'gateway', true );
		$this->payment_currency = get_post_meta( $this->id, OPALMEMBERSHIP_PAYMENT_PREFIX . 'currency', true );
		$this->coupons          = get_post_meta( $this->id, OPALMEMBERSHIP_PAYMENT_PREFIX . 'coupons', true );
		$this->status           = $this->data->post_status;
		$this->payment_date     = $this->data->post_date;
		$this->user_id          = get_post_meta( $this->id, OPALMEMBERSHIP_PAYMENT_PREFIX . 'user_id', true );

		if ( isset( $gateways ) ) {
			$this->payment_method_title = isset( $gateways[ $this->gateway ] ) ? $gateways[ $this->gateway ]['checkout_label'] : esc_html__( 'Unknown payment' );
		}

		if ( isset( $this->meta['user_info'] ) ) {
			$this->user_info = $this->meta['user_info'];
		}
		if ( isset( $this->meta['cart_detail'] ) ) {
			$this->cart_detail = $this->meta['cart_detail'];
		}
		if ( ! empty( $this->billing ) ) {
			foreach ( $this->billing as $key => $value ) {
				$this->__set( $key, $value );
			}
		}
	}

	public function get_payment_number() {
		return '#' . $this->id;
	}

	public function get_formatted_payment_total() {
		return opalmembership_price_format( $this->get_payment_total() );
	}

	public function __set( $key, $value ) {

		$this->{$key} = $value;
	}

	public function get_backer_name() {
		return $this->data->post_title;
	}

	public function get_gateway() {
		return $this->gateway;
	}

	/**
	 *
	 */
	public function created() {
		return $this->data->post_date;
	}

	public function get_payment_total() {
		return $this->payment_total;
	}

	/**
	 * get list payments buy user id
	 */
	public function get_lists_byuser() {

	}

	public function get_user() {
		if ( $this->user_id ) {
			$user_info = get_userdata( $this->user_id );
		}

		if ( ! empty( $user_info ) ) {

			$username = '<a href="user-edit.php?user_id=' . absint( $user_info->ID ) . '">';

			if ( $user_info->first_name || $user_info->last_name ) {
				$username .= esc_html( ucfirst( $user_info->first_name ) . ' ' . ucfirst( $user_info->last_name ) );
			} else {
				$username .= esc_html( ucfirst( $user_info->display_name ) );
			}

			$username .= '</a>';

		} else {
			if ( isset( $this->billing_first_name ) && isset( $this->billing_last_name ) ) {
				$username = trim( $this->billing_first_name . ' ' . $this->billing_last_name );
			} else {
				$username = esc_html__( 'Guest', 'opalmembership' );
			}
		}

		return $username;
	}

	public function insert( $payment_data = [] ) {

		if ( empty( $payment_data ) ) {
			return false;
		}

		date_default_timezone_set( opalmembership_timezone_id() );

		if ( isset( $payment_data['user_info']['first_name'] ) || isset( $payment_data['user_info']['last_name'] ) ) {
			$payment_title = $payment_data['user_info']['first_name'] . ' ' . $payment_data['user_info']['last_name'];
		} else {
			$payment_title = $payment_data['user_email'];
		}

		$args = apply_filters( 'opalmembership_insert_payment_args', [
			'post_title'    => $payment_title,
			'post_status'   => "opal-" . ( isset( $payment_data['status'] ) ? $payment_data['status'] : 'pending' ),
			'post_type'     => 'membership_payments',
			'post_parent'   => isset( $payment_data['parent'] ) ? $payment_data['parent'] : null,
			'post_date'     => isset( $payment_data['post_date'] ) ? $payment_data['post_date'] : null,
			'post_date_gmt' => isset( $payment_data['post_date'] ) ? $payment_data['post_date'] : null,
		], $payment_data );

		$payment = wp_insert_post( $args );

		if ( $payment ) {

			$cart_tax = [];

			$payment_meta = [
				'currency'    => $payment_data['currency'],
				'user_info'   => $payment_data['user_info'],
				'cart_detail' => $payment_data['cart_detail'],
				'tax'         => $cart_tax,
			];

			$mode    = 'live';
			$gateway = ! empty( $payment_data['gateway'] ) ? $payment_data['gateway'] : '';

			if ( empty( $payment_data['price'] ) ) {
				$payment_data['price'] = '0.00';
			}

			// Record the payment details
			update_post_meta( $payment, OPALMEMBERSHIP_PAYMENT_PREFIX . 'meta', apply_filters( 'opalmembership_payment_meta', $payment_meta, $payment_data ) );
			update_post_meta( $payment, OPALMEMBERSHIP_PAYMENT_PREFIX . 'user_id', $payment_data['user_info']['id'] );
			update_post_meta( $payment, OPALMEMBERSHIP_PAYMENT_PREFIX . 'package_id', $payment_data['package_id'] );
			update_post_meta( $payment, OPALMEMBERSHIP_PAYMENT_PREFIX . 'billing', $payment_data['billing_info'] );
			update_post_meta( $payment, OPALMEMBERSHIP_PAYMENT_PREFIX . 'customer_id', $payment_data['user_info']['id'] );
			update_post_meta( $payment, OPALMEMBERSHIP_PAYMENT_PREFIX . 'user_email', $payment_data['user_email'] );
			update_post_meta( $payment, OPALMEMBERSHIP_PAYMENT_PREFIX . 'user_ip', opalmembership_get_user_ip() );
			update_post_meta( $payment, OPALMEMBERSHIP_PAYMENT_PREFIX . 'purchase_key', $payment_data['purchase_key'] );
			update_post_meta( $payment, OPALMEMBERSHIP_PAYMENT_PREFIX . 'total', $payment_data['price'] );
			update_post_meta( $payment, OPALMEMBERSHIP_PAYMENT_PREFIX . 'mode', $mode );
			update_post_meta( $payment, OPALMEMBERSHIP_PAYMENT_PREFIX . 'gateway', $gateway );
			update_post_meta( $payment, OPALMEMBERSHIP_PAYMENT_PREFIX . 'currency', $payment_data['currency'] );
			update_post_meta( $payment, OPALMEMBERSHIP_PAYMENT_PREFIX . 'coupons', $payment_data['coupons'] );

			do_action( 'opalmembership_insert_payment', $payment, $payment_data );

			return $payment; // Return the ID
		}

		// Return false if no payment was inserted
		return false;
	}

	public function set_ID() {

	}

	/**
	 * get list payments buy user id
	 */
	public function refresh( $id ) {

	}

	public function get_payment_currency() {
		return apply_filters( OPALMEMBERSHIP_PAYMENT_PREFIX . 'currency', $this->payment_currency, $this );
	}


	public function update_transaction_id( $tid ) {
		update_post_meta( $this->id, OPALMEMBERSHIP_PAYMENT_PREFIX . 'transaction_id', $tid );
	}

	/**
	 * get list payments buy user id
	 */
	public function get_ID() {
		return $this->id;
	}

	/**
	 * get list payments buy user id
	 */
	public function get_metas() {

	}

	public function user_id() {
		return $this->get_meta( 'user_id' );
	}

	public function get_user_id() {
		return $this->get_meta( 'user_id' );
	}

	public function get_meta( $key ) {
		$key = OPALMEMBERSHIP_PAYMENT_PREFIX . $key;

		return apply_filters( 'opalmembership_payment_meta' . $key, $this->data->__get( $key ) );
	}

	public static function get_meta_value( $payment_id, $key ) {
		$key = OPALMEMBERSHIP_PAYMENT_PREFIX . $key;

		return get_post_meta( $payment_id, $key, true );
	}

	public function meta( $key ) {
		return $this->get_meta( $key );
	}

	/**
	 * get list payments buy user id
	 */
	public function update_status( $status, $note = '' ) {
		// return wp_update_post( array( 'ID' => $this->id, 'post_status' => "opal-".$status ) );
		$this->update_payment_status( $this->id, "opal-" . $status );
		if ( $note ) {
			opalmembership_insert_payment_note( $this->id, $note );
		}
	}

	public function payment_complete( $txt_id ) {
		return $this->update_transaction_id( $txt_id );
	}

	public function has_status( $status ) {
		return 'opal-' . $status == $this->get_status();
	}

	public function get_status() {
		return $this->status;
	}

	public function get_status_name() {
		return opalmembership_get_payment_status_name( $this->status );
	}

	public function get_cartitems() {
		return [ $this->cart_detail ];
	}

	/**
	 * Get the parsed notes for a customer as an array
	 *
	 * @param integer $length The number of notes to get
	 * @param integer $paged  What note to start at
	 *
	 * @return array           The notes requsted
	 * @since  1.0
	 *
	 */
	public function get_notes( $length = 20, $paged = 1 ) {

		$length = is_numeric( $length ) ? $length : 20;
		$offset = is_numeric( $paged ) && $paged != 1 ? ( ( absint( $paged ) - 1 ) * $length ) : 0;

		$all_notes = $this->get_raw_notes();
		// $notes_array = array_reverse( array_filter( explode( "\n\n", $all_notes ) ) );

		// $desired_notes = array_slice( $notes_array, $offset, $length );
		$desired_notes = array_slice( $all_notes, $offset, $length );

		return $desired_notes;

	}

	/**
	 * Get the total number of notes we have after parsing
	 *
	 * @return int The number of notes for the customer
	 * @since  1.0
	 */
	public function get_notes_count() {

		// $all_notes   = $this->get_raw_notes();
		// $notes_array = array_reverse( array_filter( explode( "\n\n", $all_notes ) ) );

		// return count( $notes_array );

		return count( $this->get_raw_notes() );
	}

	/**
	 * Add a note for the customer
	 *
	 * @param string $note The note to add
	 *
	 * @return string|boolean The new note if added succesfully, false otherwise
	 * @since  1.0
	 *
	 */
	public function add_note( $note = '' ) {

		$note = trim( $note );
		if ( empty( $note ) ) {
			return false;
		}

		$notes = $this->get_raw_notes();

		if ( empty( $notes ) ) {
			$notes = '';
		}

		$note_string = date_i18n( 'F j, Y H:i:s', current_time( 'timestamp' ) ) . ' - ' . $note;

		$new_note = apply_filters( 'opalmembership_customer_add_note_string', $note_string );
		// $notes .= "\n\n" . $new_note;

		do_action( 'opalmembership_customer_pre_add_note', $new_note, $this->id );

		// $updated = $this->update( array( 'notes' => $notes ) );
		$updated = opalmembership_insert_payment_note( $this->id, $note );
		// $updated = wp_insert_comment( array(
		// 		'comment_post_ID' 	=> $this->id,
		// 		'comment_content'	=> $note
		// 	) );

		if ( $updated ) {
			$this->notes = $this->get_notes();
		}

		do_action( 'opalmembership_customer_post_add_note', $this->notes, $new_note, $this->id );

		// Return the formatted note, so we can test, as well as update any displays
		return $new_note;

	}

	/**
	 * Get the notes column for the customer
	 *
	 * @return string The Notes for the customer, non-parsed
	 * @since  1.0
	 */
	private function get_raw_notes() {

		// $all_notes = $this->db->get_column( 'notes', $this->id );
		$all_notes = get_comments( [ 'post_id' => $this->id ] );

		return $all_notes;

	}

	public function get_coupons() {
		return $this->coupons;
	}

	/**
	 *
	 */
	public static function update_payment_status( $payment_id, $new_status = 'opal-publish' ) {

		if ( empty( $payment_id ) ) {
			return;
		}

		$payment = get_post( $payment_id );

		if ( is_wp_error( $payment ) || ! is_object( $payment ) ) {
			return;
		}

		$old_status = $payment->post_status;

		if ( $old_status === $new_status ) {
			return; // Don't permit status changes that aren't changes
		}

		$do_change = apply_filters( 'opalmembership_should_update_payment_status', true, $payment_id, $new_status, $old_status );

		if ( $do_change ) {

			do_action( 'opalmembership_before_payment_status_change', $payment_id, $new_status, $old_status );

			$update_fields = [
				'ID'          => $payment_id,
				'post_status' => $new_status,
				'edit_date'   => current_time( 'mysql' ),
			];
			wp_update_post( apply_filters( 'opalmembership_update_payment_status_fields', $update_fields ) );

			$old_action = substr( $old_status, strlen( 'opal-' ) );
			$new_action = substr( $new_status, strlen( 'opal-' ) );
			do_action( 'opalmembership_update_payment_status', $payment_id, $new_status, $old_status );

			do_action( 'opalmembership_update_payment_status_' . $old_action . '_to_' . $new_action, $payment_id );
			do_action( 'opalmembership_update_payment_status_to_' . $new_action, $payment_id );
		}
	}
}
