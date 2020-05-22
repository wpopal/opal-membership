<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit();
}

class Opalmembership_Form_Handler {

	public static function init() {
		add_action( 'init', [ __CLASS__, 'process_login' ] );
		add_action( 'init', [ __CLASS__, 'process_register' ] );
		// add_action( 'wp_loaded', [ __CLASS__, 'completed_payment' ], 20 );
		add_action( 'wp_loaded', [ __CLASS__, 'failed_payment' ], 20 );
	}

	/**
	 * Login processer
	 */
	public static function process_login() {
		$nonce_value = isset( $_POST['_wpnonce'] ) ? $_POST['_wpnonce'] : '';
		$nonce_value = isset( $_POST['opalmembership-login-nonce'] ) ? $_POST['opalmembership-login-nonce'] : $nonce_value;

		/* verify wp nonce */
		if ( ! isset( $_POST['login'] ) || ! wp_verify_nonce( $nonce_value, 'opalmembership-login' ) ) {
			return;
		}

		try {

			$credentials = [];
			$username    = isset( $_POST['username'] ) ? sanitize_user( $_POST['username'] ) : '';
			$password    = isset( $_POST['password'] ) ? sanitize_text_field( $_POST['password'] ) : '';

			/* sanitize, allow hook process like block somebody =)))) */
			$validation = apply_filters( 'opalmembership_validation_process_login_error', new WP_Error(), $username, $password );
			if ( $validation->get_error_code() ) {
				throw new Exception( '<strong>' . esc_html__( 'ERROR', 'opalmembership' ) . ':</strong> ' . $validation->get_error_message() );
			}

			/* validate username */
			if ( ! $username ) {
				throw new Exception( '<strong>' . esc_html__( 'ERROR', 'opalmembership' ) . ':</strong> ' . esc_html__( 'Username is required.', 'opalmembership' ) );
			} else {

				if ( is_email( $username ) ) {
					/* user object */
					$user = get_user_by( 'email', $username );
					if ( $user->user_login ) {
						$credentials['user_login'] = $user->user_login;
					} else {
						throw new Exception( '<strong>' . esc_html__( 'ERROR', 'opalmembership' ) . ':</strong> ' . esc_html__( 'A user could not be found with this email address.',
								'opalmembership' ) );
					}
				} else {
					$credentials['user_login'] = $username;
				}

			}

			/* validate password if it empty */
			if ( ! $password ) {
				throw new Exception( '<strong>' . esc_html__( 'ERROR', 'opalmembership' ) . ':</strong> ' . esc_html__( 'Password is required.', 'opalmembership' ) );
			}
			$credentials['user_password'] = $password;
			/* is rembemer me checkbox */
			$credentials['remember'] = isset( $_POST['remember'] );

			/* signon user */
			$user = wp_signon( $credentials, is_ssl() );
			if ( is_wp_error( $user ) ) {
				throw new Exception( $user->get_error_message() );
			} else {

				/* after signon successfully */
				do_action( 'opalmembership_after_signon_successfully', $user );

				$redirect = opalmembership_get_dashdoard_page_uri();
				if ( ! empty( $_POST['redirect'] ) ) {
					$redirect = sanitize_text_field( $_POST['redirect'] );
				} elseif ( wp_get_referer() ) {
					$redirect = wp_get_referer();
				}

				$redirect = apply_filters( 'opalmembership_signon_redirect_url', $redirect );
				if ( opalmembership_is_ajax_request() ) {
					wp_send_json( [ 'status' => true, 'redirect' => $redirect ] );
				} else {
					wp_safe_redirect( $redirect );
					exit();
				}
			}

		} catch ( Exception $e ) {
			opalmembership_add_notice( 'error', $e->getMessage() );
		}

		if ( opalmembership_is_ajax_request() ) {
			ob_start();
			opalmembership_print_notices();
			$message = ob_get_clean();
			wp_send_json( [
				'status'  => false,
				'message' => $message,
			] );
		}
	}

	/**
	 * Register processer
	 */
	public static function process_register() {
		$nonce_value = isset( $_POST['_wpnonce'] ) ? $_POST['_wpnonce'] : '';
		$nonce_value = isset( $_POST['opalmembership-register-nonce'] ) ? $_POST['opalmembership-register-nonce'] : $nonce_value;

		/* verify wp nonce */
		if ( ! isset( $_POST['register'] ) || ! wp_verify_nonce( $nonce_value, 'opalmembership-register' ) ) {
			return;
		}

		try {

			$credentials = [];
			$username    = isset( $_POST['username'] ) ? sanitize_user( $_POST['username'] ) : '';
			$email       = isset( $_POST['email'] ) ? sanitize_email( $_POST['email'] ) : '';
			$password    = isset( $_POST['password'] ) ? sanitize_text_field( $_POST['password'] ) : '';
			$password1   = isset( $_POST['password1'] ) ? sanitize_text_field( $_POST['password1'] ) : '';

			/* sanitize, allow hook process like block somebody =)))) */
			$validation = apply_filters( 'opalmembership_validation_process_register_error', new WP_Error(), $username, $email );
			/* sanitize */
			if ( $validation->get_error_code() ) {
				throw new Exception( '<strong>' . esc_html__( 'ERROR', 'opalmembership' ) . ':</strong> ' . $validation->get_error_message() );
			}

			/* validate username */
			if ( ! $username ) {
				throw new Exception( '<strong>' . esc_html__( 'ERROR', 'opalmembership' ) . ':</strong> ' . esc_html__( 'Username is required.', 'opalmembership' ) );
			} else {
				$credentials['user_login'] = $username;
			}

			/* validate email */
			if ( ! $email ) {
				throw new Exception( '<strong>' . esc_html__( 'ERROR', 'opalmembership' ) . ':</strong> ' . esc_html__( 'Email is required.', 'opalmembership' ) );
			} else {
				$credentials['user_email'] = $email;
			}

			/* validate password */
			if ( ! $password ) {
				throw new Exception( '<strong>' . esc_html__( 'ERROR', 'opalmembership' ) . ':</strong> ' . esc_html__( 'Password is required.', 'opalmembership' ) );
			}
			if ( $password !== $password1 ) {
				throw new Exception( '<strong>' . esc_html__( 'ERROR', 'opalmembership' ) . ':</strong> ' . esc_html__( 'Re-Password is not match.', 'opalmembership' ) );
			}
			$credentials['user_pass'] = $password;

			/* create new user */
			$user_id = opalmembership_create_user( $credentials );

			if ( is_wp_error( $user_id ) ) {
				throw new Exception( '<strong>' . esc_html__( 'ERROR', 'opalmembership' ) . ':</strong> ' . $user_id->get_error_message() );
			} else {

				/* after register successfully */
				do_action( 'opalmembership_after_register_successfully', $user_id );

				$redirect = home_url();
				if ( opalmembership_get_option( 'login_user' ) ) {
					wp_set_auth_cookie( $user_id );
					$redirect = opalmembership_get_dashdoard_page_uri();
				} elseif ( ! empty( $_POST['redirect'] ) ) {
					$redirect = sanitize_text_field( $_POST['redirect'] );
				} elseif ( wp_get_referer() ) {
					$redirect = wp_get_referer();
				}

				$redirect = apply_filters( 'opalmembership_register_redirect_url', $redirect );

				/* is ajax request */
				if ( opalmembership_is_ajax_request() ) {
					wp_send_json( [ 'status' => true, 'redirect' => $redirect ] );
				} else {
					wp_safe_redirect( $redirect );
					exit();
				}
			}

		} catch ( Exception $e ) {
			opalmembership_add_notice( 'error', $e->getMessage() );
		}

		/* is ajax request */
		if ( opalmembership_is_ajax_request() ) {
			ob_start();
			opalmembership_print_notices();
			$message = ob_get_clean();
			wp_send_json( [
				'status'  => false,
				'message' => $message,
			] );
		}
	}

	/**
	 * Cancel a payment.
	 */
	public static function failed_payment() {
		if (
			isset( $_GET['payment_id'] ) &&
			isset( $_GET['status'] ) &&
			( $_GET['status'] === 'failed' ) &&
			( isset( $_GET['_wpnonce'] ) && wp_verify_nonce( wp_unslash( $_GET['_wpnonce'] ),
					'opalmemebership-payment-failed' ) ) // phpcs:ignore WordPress.Security.ValidatedSanitizedInput.InputNotSanitized
		) {
			nocache_headers();
			$payment_id = absint( $_GET['payment_id'] );
			$payment = new Opalmembership_Payment( $payment_id );

			if ( current_user_can( 'opalmembership_cancel_payment', $payment_id ) && ! $payment->has_status( 'completed' ) ) {
				$payment->update_status( 'failed', esc_html__( 'Failed payment.', 'opalmembership' ) );
			}
		}
	}

	/**
	 * Cancel a payment.
	 */
	public static function completed_payment() {
		if (
			isset( $_GET['payment_id'] ) &&
			isset( $_GET['status'] ) &&
			( $_GET['status'] === 'completed' ) &&
			( isset( $_GET['_wpnonce'] ) && wp_verify_nonce( wp_unslash( $_GET['_wpnonce'] ),
					'opalmemebership-payment-completed' ) ) // phpcs:ignore WordPress.Security.ValidatedSanitizedInput.InputNotSanitized
		) {
			nocache_headers();

			$payment_id = absint( $_GET['payment_id'] );
			$payment = new Opalmembership_Payment( $payment_id );

			if ( current_user_can( 'opalmembership_pay_for_payment', $payment_id ) && ! $payment->has_status( 'completed' ) ) {
				$payment->update_status( 'completed', esc_html__( 'Completed payment.', 'opalmembership' ) );
			}

			OpalMembership()->clear_payment_session();
			OpalMembership()->clear_cart_session();
		}
	}
}

Opalmembership_Form_Handler::init();
