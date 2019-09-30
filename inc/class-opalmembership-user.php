<?php

/**
 * @Class Opalmembership_User
 *
 * @since 1.0
 */
class Opalmembership_User {

	/**
	 *
	 */
	public static function init() {
		define( 'OPALMEMBERSHIP_USER_PREFIX_', 'opalmb_' );
		add_action( 'opalmembership_update_payment_status', [ __CLASS__, 'set_membership_active' ], 1, 99 );
		add_filter( 'opalmembership_dashboard_tabs', [ __CLASS__, 'opalmembership_add_dashboard_tabs' ] );
		add_action( 'opalmembership_dashboard_container_before', [ __CLASS__, 'show_membership_warning' ], 9 );
	}

	/**
	 *
	 */
	public static function get_current_membership( $user_id = null ) {

		return (int) get_user_meta( $user_id, OPALMEMBERSHIP_USER_PREFIX_ . 'package_id', true );
	}

	public static function get_count_purchase_packages() {

	}

	/**
	 *
	 */
	public static function set_membership_active( $payment_id, $new_status, $old_status ) {

		if ( $new_status == 'opal-completed' ) {

			$package_id = Opalmembership_Payment::get_meta_value( $payment_id, 'package_id' );
			if ( $package_id ) {
				$user_id = Opalmembership_Payment::get_meta_value( $payment_id, 'user_id' );

				$time = time();
				$date = $time;

				$expired_time = opalmembership_package_get_expiration_date( $package_id, $date );


				update_user_meta( $user_id, OPALMEMBERSHIP_USER_PREFIX_ . 'payment_id', $payment_id );
				update_user_meta( $user_id, OPALMEMBERSHIP_USER_PREFIX_ . 'package_activation', $date );
				update_user_meta( $user_id, OPALMEMBERSHIP_USER_PREFIX_ . 'package_expired', $expired_time );
				update_user_meta( $user_id, OPALMEMBERSHIP_USER_PREFIX_ . 'package_id', $package_id );
				update_user_meta( $user_id, OPALMEMBERSHIP_USER_PREFIX_ . 'send_expired_email', 0 );

				do_action( 'opalmembership_after_update_user_membership', $package_id, $user_id, $payment_id );
			}
		}
	}

	/**
	 *
	 */
	public static function is_membership_valid( $user_id = null ) {
		if ( ! defined( 'OPALMEMBERSHIP_USER_PREFIX_' ) ) {
			return false;
		}

		if ( ! $user_id ) {
			$user    = wp_get_current_user();
			$user_id = $user->ID;
		}

		$package_id = get_user_meta( $user_id, OPALMEMBERSHIP_USER_PREFIX_ . 'package_id', true );

		if ( ! $package_id ) {
			return false;
		}

		if ( '-1' !== $package_id ) {
			if ( ! get_post( $package_id ) ) {
				return false;
			}

			$payment_id = (int) get_user_meta( $user_id, OPALMEMBERSHIP_USER_PREFIX_ . 'payment_id', true );

			/* payment is not completed */
			if ( ! $payment_id || get_post_status( $payment_id ) !== 'opal-completed' ) {
				return false;
			}
		}

		$package_expired = get_user_meta( $user_id, OPALMEMBERSHIP_USER_PREFIX_ . 'package_expired', true );

		if ( ! $package_expired || ( $package_expired ) <= time() ) {
			return false;
		}

		return true;
	}

	/**
	 *
	 */
	public static function show_membership_warning() {
		echo Opalmembership_Template_Loader::get_template_part( 'account/membership-warning' );
	}

	/**
	 *
	 */
	public static function opalmembership_add_dashboard_tabs( $tabs ) {
		if ( empty( $tabs ) ) {
			$tabs = [];
		}

		$tabs['dashboard_page'] = [
			'callback' => 'opalmembership_dashboard_page',
			'title'    => esc_html__( 'Dashboard', 'opalmembership' ),
		];

		return $tabs;
	}
}

Opalmembership_User::init();
