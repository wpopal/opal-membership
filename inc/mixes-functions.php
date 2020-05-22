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
/**
 * Email settings
 */
function opalmembership_default_expired_email_body() {
	return trim( preg_replace( '/\t+/', '', '
                    Hi {user_name},
                    <br>
                    Your membership is expired at {site_name}:<br>
                    <br>
                    {package_membership}<br>
                    <br>
                    <em>This message was sent by {site_link} on {current_time}.</em>' ) );
}

function opalmembership_default_newpayment_email_body() {
	return trim( preg_replace( '/\t+/', '', "Thanks {user_name},<br>
                            <br>
                            Your payment request is <strong>waiting to be confirmed</strong>.<br>
                            <br>
                            Give us a few moments to make sure that we've got space for you. You will receive another email from us soon. If this request was made outside of our normal working hours, we may not be able to confirm it until we're open again.<br>
                            <br>
                            <strong>Your request details:</strong><br>
                        
                            {membership_page}<br>

                            <br>
                            &nbsp;<br>
                            <br>
                            <em>This message was sent by {site_link} on {current_time}.</em>" ) );
}

function opalmembership_default_confirmed_email_body() {
	return trim( preg_replace( '/\t+/', '', 'Hi {user_name},<br>
                            <br>
                            Your payment status has been <strong>confirmed</strong>. Your package is actived now. You should go and check it out.<br>
                            <br>
                            <strong>Here is detail:</strong><br>
                            {package_membership} <br>
                            <br>
                            Thanks you so much!
                            <em>This message was sent by {site_link} on {current_time}.</em>' ) );
}

function opalmembership_default_cancelled_email_body() {
	return trim( preg_replace( '/\t+/', '', "Hi {user_name},<br>
                            <br>
                            Sorry, we could not accommodate your booking request. We're full or not open at the time you requested:<br>
                            <br>
                            {package_membership}<br>
                            <br>
                            &nbsp;<br>
                            <br>
                            <em>This message was sent by {site_link} on {current_time}.</em>" ) );
}

function opalmembership_default_refunded_email_body() {
	return trim( preg_replace( '/\t+/', '', "Hi {user_name},<br>
                            <br>
                            Your refund request has been received:<br>
                            <br>
                            {package_membership}<br>
                            <br>
                            &nbsp;<br>
                            <br>
                            <em>This message was sent by {site_link} on {current_time}.</em>" ) );
}

// /// /
function opalmembership_process_post_checkout() {
	if ( isset( $_GET['action'] ) && ! empty( $_POST['billing'] ) && $_GET['action'] == 'proccess_checkout' ) {
		OpalMembership()->checkout()->process_checkout();
	}
}

add_action( 'init', 'opalmembership_process_post_checkout' );

/* paypal callback verify */
if ( ! function_exists( 'opalmembership_inits' ) ) {
	function opalmembership_inits() {

		if ( isset( $_GET['opalmembership-listener'] ) && $_GET['opalmembership-listener'] == 'IPN' ) {
			$log = new Opalmembership_Logger();
			$log->add( 'paypal', 'Start  to write33333' );

		}

		if ( isset( $_GET['pmgw-callback'] ) && ! empty( $_GET['pmgw-callback'] ) ) {
			$log = new Opalmembership_Logger();
			$log->add( 'paypal', 'Start  to write' );

			$file = apply_filters( strtolower( $_GET['pmgw-callback'] ) . '_file', 'class-' . str_replace( '_', '-', strtolower( $_GET['pmgw-callback'] ) ) );

			if ( file_exists( OPALMEMBERSHIP_PLUGIN_DIR . 'inc/gateways/response/' . $file . '.php' ) ) {
				require_once( OPALMEMBERSHIP_PLUGIN_DIR . 'inc/gateways/response/' . $file . '.php' );
			}
		}

	}

	add_action( 'init', 'opalmembership_inits' );
}

function opalmembership_options( $key, $default = '' ) {

	global $opalmembership_options;
	$default = '';
	$value   = ! empty( $opalmembership_options[ $key ] ) && isset( $opalmembership_options[ $key ] ) ? $opalmembership_options[ $key ] : $default;
	$value   = apply_filters( 'opalmembership_option_', $value, $key, $default );

	return apply_filters( 'opalmembership_option_' . $key, $value, $key, $default );
}

/**
 * batch including all files in a path.
 *
 * @param String $path : PATH_DIR/*.php or PATH_DIR with $ifiles not empty
 */
function opalmembership_includes( $path, $ifiles = [] ) {

	if ( ! empty( $ifiles ) ) {
		foreach ( $ifiles as $key => $file ) {
			$file = $path . '/' . $file;
			if ( is_file( $file ) ) {
				require( $file );
			}
		}
	} else {
		$files = glob( $path );
		foreach ( $files as $key => $file ) {
			if ( is_file( $file ) ) {
				require( $file );
			}
		}
	}
}

function opalmembership_insert_payment_note( $payment_id, $note ) {

	do_action( 'opalmembership_pre_insert_payment_note', $payment_id, $note );
	$data = wp_filter_comment( [
		'comment_post_ID'      => $payment_id,
		'comment_content'      => $note,
		'user_id'              => is_admin() ? get_current_user_id() : 0,
		'comment_date'         => current_time( 'mysql' ),
		'comment_date_gmt'     => current_time( 'mysql', 1 ),
		'comment_approved'     => 1,
		'comment_parent'       => 0,
		'comment_author'       => '',
		'comment_author_IP'    => '',
		'comment_author_url'   => '',
		'comment_author_email' => '',
		'comment_type'         => 'payment_notes',

	] );

	return wp_insert_comment( $data );
}

/**
 * Get a log file path
 *
 * @param string $handle name
 * @return string the log file path
 * @since 2.2
 */
if ( ! is_dir( ABSPATH . 'opal-logs/' ) && true ) {
	@mkdir( ABSPATH . 'opal-logs/', 0777 );
}
function opalmembership_get_log_file_path( $handle ) {
	return trailingslashit( ABSPATH . 'opal-logs/' ) . $handle . sanitize_file_name( ( $handle ) ) . '.log';
}

function opalmembership_get_success_page_uri() {
	global $opalmembership_options;

	$success_page = isset( $opalmembership_options['success_page'] ) ? get_permalink( absint( $opalmembership_options['success_page'] ) ) : get_bloginfo( 'url' );

	return apply_filters( 'opalmembership_get_success_page_uri', $success_page );
}

function opalmembership_get_membership_page_uri() {

	global $opalmembership_options;

	$membership_page = isset( $opalmembership_options['membership_page'] ) ? get_permalink( absint( $opalmembership_options['membership_page'] ) ) : get_bloginfo( 'url' );

	return apply_filters( 'opalmembership_get_membership_page_uri', $membership_page );

}

function opalmembership_get_failed_transaction_uri( $args = [] ) {
	global $opalmembership_options;

	$uri = isset( $opalmembership_options['failure_page'] ) ? get_permalink( absint( $opalmembership_options['failure_page'] ) ) : get_bloginfo( 'url' );

	if ( ! empty( $args ) ) {
		// Check for backward compatibility
		if ( is_string( $args ) ) {
			$args = str_replace( '?', '', $args );
		}

		$args = wp_parse_args( $args );

		$uri = add_query_arg( $args, $uri );
	}

	$scheme = defined( 'FORCE_SSL_ADMIN' ) && FORCE_SSL_ADMIN ? 'https' : 'admin';

	$ajax_url = admin_url( 'admin-ajax.php', $scheme );

	if ( ( ! preg_match( '/^https/', $uri ) && preg_match( '/^https/', $ajax_url ) ) ) {
		$uri = preg_replace( '/^http:/', 'https:', $uri );
	}

	return apply_filters( 'opalmembership_get_failed_transaction_uri', $uri );
}

function opalmembership_get_checkout_page_uri( $args = [] ) {
	global $opalmembership_options;

	$uri = isset( $opalmembership_options['checkout_page'] ) ? get_permalink( absint( $opalmembership_options['checkout_page'] ) ) : get_bloginfo( 'url' );

	if ( ! empty( $args ) ) {
		// Check for backward compatibility
		if ( is_string( $args ) ) {
			$args = str_replace( '?', '', $args );
		}

		$args = wp_parse_args( $args );

		$uri = add_query_arg( $args, $uri );
	}

	$scheme = defined( 'FORCE_SSL_ADMIN' ) && FORCE_SSL_ADMIN ? 'https' : 'admin';

	$ajax_url = admin_url( 'admin-ajax.php', $scheme );

	if ( ( ! preg_match( '/^https/', $uri ) && preg_match( '/^https/', $ajax_url ) ) ) {
		$uri = preg_replace( '/^http:/', 'https:', $uri );
	}

	return apply_filters( 'opalmembership_get_checkout_page_uri', $uri );
}

function opalmembership_get_register_page_uri() {
	global $opalmembership_options;

	$register_page = isset( $opalmembership_options['register_page'] ) ? get_permalink( absint( $opalmembership_options['register_page'] ) ) : get_bloginfo( 'url' );

	return apply_filters( 'opalmembership_get_register_page_uri', $register_page );
}

function opalmembership_get_login_page_uri() {
	global $opalmembership_options;

	$login_page = isset( $opalmembership_options['login_page'] ) ? get_permalink( absint( $opalmembership_options['login_page'] ) ) : get_bloginfo( 'url' );

	return apply_filters( 'opalmembership_get_login_page_uri', $login_page );
}

function opalmembership_get_current_url( $args = [] ) {
	global $wp;
	if ( isset( $_GET['tab'] ) && $_GET['tab'] ) {
		$args['tab'] = sanitize_text_field( $_GET['tab'] );
	}
	$current_url = home_url( add_query_arg( $args, $wp->request ) );

	return $current_url;
}

function opalmembership_get_payment_history_detail_page_uri( $id ) {
	return opalmembership_get_payment_history_page_uri( [ 'payment_id' => $id ] );
}

function opalmembership_get_payment_history_page_uri( $args = [] ) {
	global $opalmembership_options;

	$uri = isset( $opalmembership_options['history_page'] ) ? get_permalink( absint( $opalmembership_options['history_page'] ) ) : get_bloginfo( 'url' );

	if ( ! empty( $args ) ) {
		// Check for backward compatibility
		if ( is_string( $args ) ) {
			$args = str_replace( '?', '', $args );
		}

		$args = wp_parse_args( $args );

		$uri = add_query_arg( $args, $uri );
	}

	$scheme = defined( 'FORCE_SSL_ADMIN' ) && FORCE_SSL_ADMIN ? 'https' : 'admin';

	$ajax_url = admin_url( 'admin-ajax.php', $scheme );

	if ( ( ! preg_match( '/^https/', $uri ) && preg_match( '/^https/', $ajax_url ) ) ) {
		$uri = preg_replace( '/^http:/', 'https:', $uri );
	}

	return apply_filters( 'opalmembership_get_payment_history_page_uri', $uri );
}

function opalmembership_get_dashdoard_page_uri() {
	global $opalmembership_options;

	$dashboard = isset( $opalmembership_options['dashboard_page'] ) ? get_permalink( absint( $opalmembership_options['dashboard_page'] ) ) : get_bloginfo( 'url' );

	return apply_filters( 'opalmembership_get_dashdoard_page_uri', $dashboard );
}

function opalmembership_get_purchase_session() {
	return OpalMembership()->session->get( 'opalmembership_purchase' );
}

function opalmembership_set_purchase_session( $purchase_data = [] ) {
	OpalMembership()->session()->set( 'opalmembership_waiting_payment', null );
	OpalMembership()->session->set( 'opalmembership_purchase', $purchase_data );
}

/**
 *
 */
function opalmembership_get_payment_gateways() {
	$gateways = OpalMembership()->gateways()->get_getways();

	return apply_filters( 'opalmembership_payment_gateways', $gateways );
}

function opalmembership__log_file_path( $handle = '' ) {
	return trailingslashit( ABSPATH . 'opal-logs/' ) . $handle . sanitize_file_name( ( $handle ) ) . '.log';
}

/**
 * Returns a list of all enabled gateways.
 *
 * @return array $gateway_list All the available gateways
 * @since 1.0
 */
function opalmembership_get_enabled_payment_gateways() {
	global $opalmembership_options;

	$gateways = opalmembership_get_payment_gateways();
	$enabled  = isset( $opalmembership_options['gateways'] ) ? $opalmembership_options['gateways'] : false;

	$gateway_list = [];

	foreach ( $gateways as $key => $gateway ) {
		if ( isset( $enabled[ $key ] ) && $enabled[ $key ] == 1 ) {
			$gateway_list[ $key ] = $gateway;
		}
	}

	return apply_filters( 'opalmembership_enabled_payment_gateways', $gateway_list );
}

function opalmembership_get_gateways_by_key( $key ) {
	$gateways = opalmembership_get_enabled_payment_gateways();

	return isset( $gateways[ $key ] ) ? $gateways[ $key ]['admin_label'] : esc_html__( 'Undefined Payment', 'opalmembership' );
}

/**
 * Get Currencies
 *
 * @return array $currencies A list of the available currencies
 * @since 1.0
 */
function opalmembership_get_currencies() {
	$currencies = [
		'USD'  => esc_html__( 'US Dollars (&#36;)', 'opalmembership' ),
		'EUR'  => esc_html__( 'Euros (&euro;)', 'opalmembership' ),
		'GBP'  => esc_html__( 'Pounds Sterling (&pound;)', 'opalmembership' ),
		'AUD'  => esc_html__( 'Australian Dollars (&#36;)', 'opalmembership' ),
		'BRL'  => esc_html__( 'Brazilian Real (R&#36;)', 'opalmembership' ),
		'CAD'  => esc_html__( 'Canadian Dollars (&#36;)', 'opalmembership' ),
		'CZK'  => esc_html__( 'Czech Koruna', 'opalmembership' ),
		'DKK'  => esc_html__( 'Danish Krone', 'opalmembership' ),
		'HKD'  => esc_html__( 'Hong Kong Dollar (&#36;)', 'opalmembership' ),
		'HUF'  => esc_html__( 'Hungarian Forint', 'opalmembership' ),
		'ILS'  => esc_html__( 'Israeli Shekel (&#8362;)', 'opalmembership' ),
		'JPY'  => esc_html__( 'Japanese Yen (&yen;)', 'opalmembership' ),
		'MYR'  => esc_html__( 'Malaysian Ringgits', 'opalmembership' ),
		'MXN'  => esc_html__( 'Mexican Peso (&#36;)', 'opalmembership' ),
		'NZD'  => esc_html__( 'New Zealand Dollar (&#36;)', 'opalmembership' ),
		'NOK'  => esc_html__( 'Norwegian Krone (Kr.)', 'opalmembership' ),
		'PHP'  => esc_html__( 'Philippine Pesos', 'opalmembership' ),
		'PLN'  => esc_html__( 'Polish Zloty', 'opalmembership' ),
		'SGD'  => esc_html__( 'Singapore Dollar (&#36;)', 'opalmembership' ),
		'SEK'  => esc_html__( 'Swedish Krona', 'opalmembership' ),
		'CHF'  => esc_html__( 'Swiss Franc', 'opalmembership' ),
		'TWD'  => esc_html__( 'Taiwan New Dollars', 'opalmembership' ),
		'THB'  => esc_html__( 'Thai Baht (&#3647;)', 'opalmembership' ),
		'INR'  => esc_html__( 'Indian Rupee (&#8377;)', 'opalmembership' ),
		'TRY'  => esc_html__( 'Turkish Lira (&#8378;)', 'opalmembership' ),
		'RIAL' => esc_html__( 'Iranian Rial (&#65020;)', 'opalmembership' ),
		'RUB'  => esc_html__( 'Russian Rubles', 'opalmembership' ),
	];

	return apply_filters( 'opalmembership_currencies', $currencies );
}

/**
 * Get Currencies
 *
 * @return array $currencies A list of the available currencies
 * @since 1.0
 */
function opalmembership_get_user_ip() {

	$REMOTE_ADDR = $_SERVER['REMOTE_ADDR'];
	if ( ! empty( $_SERVER['X_FORWARDED_FOR'] ) ) {
		$X_FORWARDED_FOR = explode( ',', $_SERVER['X_FORWARDED_FOR'] );
		if ( ! empty( $X_FORWARDED_FOR ) ) {
			$REMOTE_ADDR = trim( $X_FORWARDED_FOR[0] );
		}
	} /*
    * Some php environments will use the $_SERVER['HTTP_X_FORWARDED_FOR']
    * variable to capture visitor address information.
    */
	elseif ( ! empty( $_SERVER['HTTP_X_FORWARDED_FOR'] ) ) {
		$HTTP_X_FORWARDED_FOR = explode( ',', $_SERVER['HTTP_X_FORWARDED_FOR'] );
		if ( ! empty( $HTTP_X_FORWARDED_FOR ) ) {
			$REMOTE_ADDR = trim( $HTTP_X_FORWARDED_FOR[0] );
		}
	}

	return preg_replace( '/[^0-9a-f:\., ]/si', '', $REMOTE_ADDR );
}

function opalmembership_date_diff( $time, $time2 = null, $precision = 2, $separator = ' /' ) {

	if ( empty( $time2 ) ) {
		$time2 = time();
	}
	$seconds_in = [

		''        => 86400,
		'hours'   => 3600,
		'minutes' => 60,
		'seconds' => 1,
	];
	$time_diff  = $time2 - $time;
	$diff       = [];
	foreach ( $seconds_in as $key => $seconds ) {
		$diff[ $key ] = floor( $time_diff / $seconds );
		$time_diff    -= $diff[ $key ] * $seconds;
	}
	$return = [];
	foreach ( $diff as $key => $count ) {
		if ( $count > 0 ) {
			$precision--;
			$return[] = $count . ' ' . $key . ( $count == 1 ? '' : '' );
		}
		if ( $precision == 0 ) {
			break;
		}
	}


	return trim( implode( $separator, $return ) );
}


function opalmembership_timezone_id() {
	// if site timezone string exists, return it
	if ( $timezone = get_option( 'timezone_string' ) ) {
		return $timezone;
	}

	// get UTC offset, if it isn't set return UTC
	if ( ! ( $utc_offset = 3600 * get_option( 'gmt_offset', 0 ) ) ) {
		return 'UTC';
	}

	// attempt to guess the timezone string from the UTC offset
	$timezone = timezone_name_from_abbr( '', $utc_offset );

	// last try, guess timezone string manually
	if ( $timezone === false ) {

		$is_dst = date( 'I' );

		foreach ( timezone_abbreviations_list() as $abbr ) {
			foreach ( $abbr as $city ) {
				if ( $city['dst'] == $is_dst && $city['offset'] == $utc_offset ) {
					return $city['timezone_id'];
				}
			}
		}
	}

	// fallback
	return 'UTC';
}

function opalmembership_currency_symbol( $currency = '' ) {
	if ( ! $currency ) {
		$currency = opalmembership_get_currency();
	}

	switch ( $currency ) {
		case 'AED' :
			$currency_symbol = 'د.إ';
			break;
		case 'BDT':
			$currency_symbol = '&#2547;&nbsp;';
			break;
		case 'BRL' :
			$currency_symbol = '&#82;&#36;';
			break;
		case 'BGN' :
			$currency_symbol = '&#1083;&#1074;.';
			break;
		case 'AUD' :
		case 'CAD' :
		case 'CLP' :
		case 'COP' :
		case 'MXN' :
		case 'NZD' :
		case 'HKD' :
		case 'SGD' :
		case 'USD' :
			$currency_symbol = '&#36;';
			break;
		case 'EUR' :
			$currency_symbol = '&euro;';
			break;
		case 'CNY' :
		case 'RMB' :
		case 'JPY' :
			$currency_symbol = '&yen;';
			break;
		case 'RUB' :
			$currency_symbol = '&#1088;&#1091;&#1073;.';
			break;
		case 'KRW' :
			$currency_symbol = '&#8361;';
			break;
		case 'PYG' :
			$currency_symbol = '&#8370;';
			break;
		case 'TRY' :
			$currency_symbol = '&#8378;';
			break;
		case 'NOK' :
			$currency_symbol = '&#107;&#114;';
			break;
		case 'ZAR' :
			$currency_symbol = '&#82;';
			break;
		case 'CZK' :
			$currency_symbol = '&#75;&#269;';
			break;
		case 'MYR' :
			$currency_symbol = '&#82;&#77;';
			break;
		case 'DKK' :
			$currency_symbol = 'kr.';
			break;
		case 'HUF' :
			$currency_symbol = '&#70;&#116;';
			break;
		case 'IDR' :
			$currency_symbol = 'Rp';
			break;
		case 'INR' :
			$currency_symbol = 'Rs.';
			break;
		case 'NPR' :
			$currency_symbol = 'Rs.';
			break;
		case 'ISK' :
			$currency_symbol = 'Kr.';
			break;
		case 'ILS' :
			$currency_symbol = '&#8362;';
			break;
		case 'PHP' :
			$currency_symbol = '&#8369;';
			break;
		case 'PLN' :
			$currency_symbol = '&#122;&#322;';
			break;
		case 'SEK' :
			$currency_symbol = '&#107;&#114;';
			break;
		case 'CHF' :
			$currency_symbol = '&#67;&#72;&#70;';
			break;
		case 'TWD' :
			$currency_symbol = '&#78;&#84;&#36;';
			break;
		case 'THB' :
			$currency_symbol = '&#3647;';
			break;
		case 'GBP' :
			$currency_symbol = '&pound;';
			break;
		case 'RON' :
			$currency_symbol = 'lei';
			break;
		case 'VND' :
			$currency_symbol = '&#8363;';
			break;
		case 'NGN' :
			$currency_symbol = '&#8358;';
			break;
		case 'HRK' :
			$currency_symbol = 'Kn';
			break;
		case 'EGP' :
			$currency_symbol = 'EGP';
			break;
		case 'DOP' :
			$currency_symbol = 'RD&#36;';
			break;
		case 'KIP' :
			$currency_symbol = '&#8365;';
			break;
		default    :
			$currency_symbol = '';
			break;
	}

	return apply_filters( 'opalmembership_currency_symbol', $currency_symbol, $currency );
}


/**
 * Return the thousand separator for prices
 *
 * @return string
 * @since  2.3
 */
function opalmembership_get_price_thousand_separator() {
	$separator = stripslashes( opalmembership_options( 'thousands_separator' ) );

	return $separator;
}

/**
 * Return the decimal separator for prices
 *
 * @return string
 * @since  2.3
 */
function opalmembership_get_price_decimal_separator() {
	$separator = stripslashes( opalmembership_options( 'decimal_separator' ) );

	return $separator ? $separator : '.';
}

/**
 * Return the number of decimals after the decimal point.
 *
 * @return int
 * @since  2.3
 */
function opalmembership_get_price_decimals() {
	return absint( opalmembership_options( 'price_num_decimals', 2 ) );
}

/**
 *
 */
function opalmembership_price( $price, $args = [] ) {

	$negative = $price < 0;

	if ( $negative ) {
		$price = substr( $price, 1 );
	}


	extract( apply_filters( 'opalmembership_price_args', wp_parse_args( $args, [
		'ex_tax_label'       => false,
		'decimal_separator'  => opalmembership_get_price_decimal_separator(),
		'thousand_separator' => opalmembership_get_price_thousand_separator(),
		'decimals'           => opalmembership_get_price_decimals(),

	] ) ) );

	$negative = $price < 0;
	$price    = apply_filters( 'opalmembership_raw_price', floatval( $negative ? $price * -1 : $price ) );
	$price    = apply_filters( 'opalmembership_formatted_price', number_format( $price, $decimals, $decimal_separator, $thousand_separator ), $price, $decimals, $decimal_separator,
		$thousand_separator );

	return $price;
}

/**
 *
 */
function opalmembership_price_format( $price, $args = [] ) {

	$price = opalmembership_price( $price, $args );
	$price = sprintf( opalmembership_price_format_position(), opalmembership_currency_symbol(), $price );

	return apply_filters( 'opalmembership_price_format', $price );
}

/**
 * Get the price format depending on the currency position
 *
 * @return string
 */
function opalmembership_price_format_position() {
	global $opalmembership_options;
	$currency_pos = $opalmembership_options['currency_position'];

	switch ( $currency_pos ) {
		case 'before' :
			$format = '%1$s%2$s';
			break;
		case 'after' :
			$format = '%2$s%1$s';
			break;
		case 'left_space' :
			$format = '%1$s&nbsp;%2$s';
			break;
		case 'right_space' :
			$format = '%2$s&nbsp;%1$s';
			break;
	}

	return apply_filters( 'opalmembership_price_format_position', $format, $currency_pos );
}

/**
 * Outputs a checkout/address form field.
 *
 * @access      public
 * @param mixed  $key
 * @param mixed  $args
 * @param string $value (default: null)
 * @return void
 * @subpackage  Forms
 * @todo        This function needs to be broken up in smaller pieces
 */
function opalmembership_form_field( $key, $args, $value = null ) {
	$defaults             = [
		'type'              => 'text',
		'label'             => '',
		'description'       => '',
		'placeholder'       => '',
		'maxlength'         => false,
		'required'          => false,
		'id'                => str_replace( "[", "_", str_replace( "]", "", $key ) ),
		'class'             => [],
		'label_class'       => [],
		'input_class'       => [],
		'return'            => false,
		'options'           => [],
		'custom_attributes' => [],
		'validate'          => [],
		'default'           => '',
	];
	$input_required_class = '';

	$args = wp_parse_args( $args, $defaults );

	if ( ( ! empty( $args['clear'] ) ) ) {
		$after = '<div class="clear"></div>';
	} else {
		$after = '';
	}

	$required = '';
	if ( $args['required'] ) {
		$args['class'][] = 'validate-required';
		$required        = ' <abbr class="required" title="' . esc_attr( 'required', 'opalmembership' ) . '">*</abbr>';
	}

	$args['maxlength'] = ( $args['maxlength'] ) ? 'maxlength="' . absint( $args['maxlength'] ) . '"' : '';

	if ( is_string( $args['label_class'] ) ) {
		$args['label_class'] = [ $args['label_class'] ];
	}

	if ( is_null( $value ) ) {
		$value = $args['default'];
	}

	// Custom attribute handling
	$custom_attributes = [];

	if ( ! empty( $args['custom_attributes'] ) && is_array( $args['custom_attributes'] ) ) {
		foreach ( $args['custom_attributes'] as $attribute => $attribute_value ) {
			$custom_attributes[] = esc_attr( $attribute ) . '="' . esc_attr( $attribute_value ) . '"';
		}
	}

	if ( ! empty( $args['validate'] ) ) {
		foreach ( $args['validate'] as $validate ) {
			$args['class'][] = 'validate-' . $validate;
		}
	}

	switch ( $args['type'] ) {
		case "country" :

			$countries = opalmembership_get_country_list();


			// $countries = $key == 'shipping_country' ? WC()->countries->get_shipping_countries() : WC()->countries->get_allowed_countries();
			if ( sizeof( $countries ) == 1 ) {
				$field = '<p class="form-row ' . esc_attr( implode( ' ', $args['class'] ) ) . '" id="' . esc_attr( $args['id'] ) . '_field">';
				if ( $args['label'] ) {
					$field .= '<label class="' . esc_attr( implode( ' ', $args['label_class'] ) ) . '">' . $args['label'] . '</label>';
				}

				$field .= '<strong>' . current( array_values( $countries ) ) . '</strong>';

				$field .= '<input type="hidden" name="' . esc_attr( $key ) . '" id="' . esc_attr( $args['id'] ) . '" value="' . current( array_keys( $countries ) ) . '" ' . implode( ' ',
						$custom_attributes ) . ' class="country_to_state" />';

				if ( $args['description'] ) {
					$field .= '<span class="description">' . esc_attr( $args['description'] ) . '</span>';
				}

				$field .= '</p>' . $after;

			} else {

				$field = '<p class="form-row ' . esc_attr( implode( ' ', $args['class'] ) ) . '" id="' . esc_attr( $args['id'] ) . '_field">'
				         . '<label for="' . esc_attr( $args['id'] ) . '" class="' . esc_attr( implode( ' ', $args['label_class'] ) ) . '">' . $args['label'] . $required . '</label>'
				         . '<select name="' . esc_attr( $key ) . '" id="' . esc_attr( $args['id'] ) . '" class="country_to_state country_select ' . $input_required_class . '" ' . implode( ' ',
						$custom_attributes ) . '>'
				         . '<option value="">' . esc_html__( 'Select a country&hellip;', 'opalmembership' ) . '</option>';
				foreach ( $countries as $ckey => $cvalue ) {
					$field .= '<option value="' . esc_attr( $ckey ) . '" ' . selected( $value, $ckey, false ) . '>' . esc_html__( $cvalue, 'opalmembership' ) . '</option>';
				}

				$field .= '</select>';

				$field .= '<noscript><input type="submit" name="opal_cf_checkout_update_totals" value="' . esc_html__( 'Update country', 'opalmembership' ) . '" /></noscript>';

				if ( $args['description'] ) {
					$field .= '<span class="description">' . esc_attr( $args['description'] ) . '</span>';
				}

				$field .= '</p>' . $after;

			}

			break;
		case "state" :

			$current_cc = '';
			$states     = opalmembership_get_states( $current_cc );

			if ( is_array( $states ) && empty( $states ) ) {

				$field = '<p class="form-row ' . esc_attr( implode( ' ', $args['class'] ) ) . '" id="' . esc_attr( $args['id'] ) . '_field" >';

				if ( $args['label'] ) {
					$field .= '<label for="' . esc_attr( $args['id'] ) . '" class="' . esc_attr( implode( ' ', $args['label_class'] ) ) . '">' . $args['label'] . $required . '</label>';
				}
				$field .= '<input type="hidden" class="hidden" name="' . esc_attr( $key ) . '" id="' . esc_attr( $args['id'] ) . '" value="" ' . implode( ' ',
						$custom_attributes ) . ' placeholder="' . esc_attr( $args['placeholder'] ) . '" />';

				if ( $args['description'] ) {
					$field .= '<span class="description">' . esc_attr( $args['description'] ) . '</span>';
				}

				$field .= '</p>' . $after;
				/// d( $field );die;

			} elseif ( is_array( $states ) ) {

				$field = '<p class="form-row ' . esc_attr( implode( ' ', $args['class'] ) ) . '" id="' . esc_attr( $args['id'] ) . '_field">';

				if ( $args['label'] ) {
					$field .= '<label for="' . esc_attr( $args['id'] ) . '" class="' . esc_attr( implode( ' ', $args['label_class'] ) ) . '">' . $args['label'] . $required . '</label>';
				}
				$field .= '<select name="' . esc_attr( $key ) . '" id="' . esc_attr( $args['id'] ) . '" class="state_select" ' . implode( ' ',
						$custom_attributes ) . ' placeholder="' . esc_attr( $args['placeholder'] ) . '">
                <option value="">' . esc_html__( 'Select a state&hellip;', 'opalmembership' ) . '</option>';

				foreach ( $states as $ckey => $cvalue ) {
					if ( $cvalue ) {
						$field .= '<option value="' . esc_attr( $ckey ) . '" ' . selected( $value, $ckey, false ) . '>' . esc_html( $cvalue ) . '</option>';
					}
				}

				$field .= '</select>';

				if ( $args['description'] ) {
					$field .= '<span class="description">' . esc_attr( $args['description'] ) . '</span>';
				}

				$field .= '</p>' . $after;

			} else {

				$field = '<p class="form-row ' . esc_attr( implode( ' ', $args['class'] ) ) . '" id="' . esc_attr( $args['id'] ) . '_field">';

				if ( $args['label'] ) {
					$field .= '<label for="' . esc_attr( $args['id'] ) . '" class="' . esc_attr( implode( ' ', $args['label_class'] ) ) . '">' . $args['label'] . $required . '</label>';
				}
				$field .= '<input type="text" class="input-text ' . esc_attr( implode( ' ',
						$args['input_class'] ) ) . '" value="' . esc_attr( $value ) . '"  placeholder="' . esc_attr( $args['placeholder'] ) . '" name="' . esc_attr( $key ) . '" id="' . esc_attr( $args['id'] ) . '" ' . implode( ' ',
						$custom_attributes ) . ' />';

				if ( $args['description'] ) {
					$field .= '<span class="description">' . esc_attr( $args['description'] ) . '</span>';
				}

				$field .= '</p>' . $after;

			}

			break;
		case "textarea" :

			$field = '<p class="form-row ' . esc_attr( implode( ' ', $args['class'] ) ) . '" id="' . esc_attr( $args['id'] ) . '_field">';

			if ( $args['label'] ) {
				$field .= '<label for="' . esc_attr( $args['id'] ) . '" class="' . esc_attr( implode( ' ', $args['label_class'] ) ) . '">' . $args['label'] . $required . '</label>';
			}

			$field .= '<textarea name="' . esc_attr( $key ) . '" class="input-text ' . esc_attr( implode( ' ',
					$args['input_class'] ) ) . '" id="' . esc_attr( $args['id'] ) . '" placeholder="' . esc_attr( $args['placeholder'] ) . '"' . ( empty( $args['custom_attributes']['rows'] ) ? ' rows="2"' : '' ) . ( empty( $args['custom_attributes']['cols'] ) ? ' cols="5"' : '' ) . implode( ' ',
					$custom_attributes ) . '>' . esc_textarea( $value ) . '</textarea>';

			if ( $args['description'] ) {
				$field .= '<span class="description">' . esc_attr( $args['description'] ) . '</span>';
			}

			$field .= '</p>' . $after;

			break;
		case "checkbox" :

			$field = '<p class="form-row ' . esc_attr( implode( ' ', $args['class'] ) ) . '" id="' . esc_attr( $args['id'] ) . '_field">
                <input type="' . esc_attr( $args['type'] ) . '" class="input-checkbox" name="' . esc_attr( $key ) . '" id="' . esc_attr( $args['id'] ) . '" value="1" ' . checked( $value, 1, false ) . ' />
                <label for="' . esc_attr( $args['id'] ) . '" class="checkbox ' . implode( ' ', $args['label_class'] ) . '" ' . implode( ' ',
					$custom_attributes ) . '>' . $args['label'] . $required . '</label>';

			if ( $args['description'] ) {
				$field .= '<span class="description">' . esc_attr( $args['description'] ) . '</span>';
			}

			$field .= '</p>' . $after;

			break;
		case "password" :

			$field = '<p class="form-row ' . esc_attr( implode( ' ', $args['class'] ) ) . '" id="' . esc_attr( $args['id'] ) . '_field">';

			if ( $args['label'] ) {
				$field .= '<label for="' . esc_attr( $args['id'] ) . '" class="' . esc_attr( implode( ' ', $args['label_class'] ) ) . '">' . $args['label'] . $required . '</label>';
			}

			$field .= '<input type="password" class="input-text ' . esc_attr( implode( ' ',
					$args['input_class'] ) ) . '" name="' . esc_attr( $key ) . '" id="' . esc_attr( $args['id'] ) . '" placeholder="' . esc_attr( $args['placeholder'] ) . '" value="' . esc_attr( $value ) . '" ' . implode( ' ',
					$custom_attributes ) . ' />';

			if ( $args['description'] ) {
				$field .= '<span class="description">' . esc_attr( $args['description'] ) . '</span>';
			}

			$field .= '</p>' . $after;

			break;
		case "email" :
		case "text" :

			$field = '<p class="form-row ' . esc_attr( implode( ' ', $args['class'] ) ) . '" id="' . esc_attr( $args['id'] ) . '_field">';

			if ( $args['label'] ) {
				$field .= '<label for="' . esc_attr( $args['id'] ) . '" class="' . esc_attr( implode( ' ', $args['label_class'] ) ) . '">' . $args['label'] . $required . '</label>';
			}

			$field .= '<input type="' . esc_attr( $args['type'] ) . '" class="input-text ' . esc_attr( implode( ' ',
					$args['input_class'] ) ) . '" name="' . esc_attr( $key ) . '" id="' . esc_attr( $args['id'] ) . '" placeholder="' . esc_attr( $args['placeholder'] ) . '" ' . $args['maxlength'] . ' value="' . esc_attr( $value ) . '" ' . implode( ' ',
					$custom_attributes ) . ' />';

			if ( $args['description'] ) {
				$field .= '<span class="description">' . esc_attr( $args['description'] ) . '</span>';
			}

			$field .= '</p>' . $after;

			break;
		case "select" :

			$options = '';

			if ( ! empty( $args['options'] ) ) {
				foreach ( $args['options'] as $option_key => $option_text ) {
					$options .= '<option value="' . esc_attr( $option_key ) . '" ' . selected( $value, $option_key, false ) . '>' . esc_attr( $option_text ) . '</option>';
				}
			}

			$field = '<p class="form-row ' . esc_attr( implode( ' ', $args['class'] ) ) . '" id="' . esc_attr( $args['id'] ) . '_field">';

			if ( $args['label'] ) {
				$field .= '<label for="' . esc_attr( $args['id'] ) . '" class="' . esc_attr( implode( ' ', $args['label_class'] ) ) . '">' . $args['label'] . $required . '</label>';
			}

			$field .= '<select name="' . esc_attr( $key ) . '" id="' . esc_attr( $args['id'] ) . '" class="select" ' . implode( ' ', $custom_attributes ) . '>
                    ' . $options . '
                </select>';

			if ( $args['description'] ) {
				$field .= '<span class="description">' . esc_attr( $args['description'] ) . '</span>';
			}

			$field .= '</p>' . $after;

			break;
		case "radio" :

			$field = '<p class="form-row ' . esc_attr( implode( ' ', $args['class'] ) ) . '" id="' . esc_attr( $args['id'] ) . '_field">';

			if ( $args['label'] ) {
				$field .= '<label for="' . esc_attr( current( array_keys( $args['options'] ) ) ) . '" class="' . esc_attr( implode( ' ',
						$args['label_class'] ) ) . '">' . $args['label'] . $required . '</label>';
			}

			if ( ! empty( $args['options'] ) ) {
				foreach ( $args['options'] as $option_key => $option_text ) {
					$field .= '<input type="radio" class="input-radio" value="' . esc_attr( $option_key ) . '" name="' . esc_attr( $key ) . '" id="' . esc_attr( $args['id'] ) . '_' . esc_attr( $option_key ) . '"' . checked( $value,
							$option_key, false ) . ' />';
					$field .= '<label for="' . esc_attr( $args['id'] ) . '_' . esc_attr( $option_key ) . '" class="radio ' . implode( ' ', $args['label_class'] ) . '">' . $option_text . '</label>';
				}
			}

			$field .= '</p>' . $after;

			break;
		default :

			$field = apply_filters( 'opalmembership_form_field_' . $args['type'], '', $key, $args, $value );

			break;
	}

	if ( $args['return'] ) {
		return $field;
	} else {
		echo $field;
	}
}

/**
 * Gets the payment note HTML
 *
 * @param object      /int $note The comment object or ID
 * @param int $payment_id The payment ID the note is connected to
 *
 * @return string
 * @since 1.0
 *
 */
function opalmembership_get_payment_note_html( $note, $payment_id = 0 ) {

	if ( is_numeric( $note ) ) {
		$note = get_comment( $note );
	}

	if ( ! empty( $note->user_id ) ) {
		$user = get_userdata( $note->user_id );
		$user = $user->display_name;
	} else {
		$user = esc_html__( 'System', 'opalmembership' );
	}

	$date_format = get_option( 'date_format' ) . ', ' . get_option( 'time_format' );

	$delete_note_url = wp_nonce_url( add_query_arg( [
		'opalmembership-action' => 'delete_payment_note',
		'note_id'               => $note->comment_ID,
		'payment_id'            => $payment_id,
	] ), 'opalmembership_delete_payment_note_' . $note->comment_ID );

	$note_html = '<div class="opalmembership-payment-note" id="opalmembership-payment-note-' . $note->comment_ID . '">';
	$note_html .= '<p>';
	$note_html .= '<strong>' . $user . '</strong>&nbsp;&ndash;&nbsp;<span style="color:#aaa;font-style:italic;">' . date_i18n( $date_format, strtotime( $note->comment_date ) ) . '</span><br/>';
	$note_html .= $note->comment_content;
	$note_html .= '&nbsp;&ndash;&nbsp;<a href="' . esc_url( $delete_note_url ) . '" class="opalmembership-delete-payment-note" data-note-id="' . absint( $note->comment_ID ) . '" data-payment-id="' . absint( $payment_id ) . '" title="' . esc_html__( 'Delete this payment note',
			'opalmembership' ) . '">' . esc_html__( 'Delete', 'opalmembership' ) . '</a>';
	$note_html .= '</p>';
	$note_html .= '</div>';

	return $note_html;

}

/**
 * Retrieves all available statuses for payments.
 *
 * @return array $payment_status All the available payment statuses
 * @since 1.0
 */
function opalmembership_get_payment_statuses() {

	$payment_statuses = [
		'opal-pending'    => esc_html__( 'Pending', 'opalmembership' ),
		'opal-processing' => esc_html__( 'Processing', 'opalmembership' ),
		'opal-on-hold'    => esc_html__( 'On Hold', 'opalmembership' ),
		'opal-failed'     => esc_html__( 'Failed', 'opalmembership' ),
		'opal-cancelled'  => esc_html__( 'Cancelled', 'opalmembership' ),
		'opal-completed'  => esc_html__( 'Completed', 'opalmembership' ),
		'opal-refunded'   => esc_html__( 'Refunded', 'opalmembership' ),
	];

	return apply_filters( "opalmembership_get_payment_statuses", $payment_statuses );
}

function opalmembership_get_payment_status_name( $status ) {

	$statuses = opalmembership_get_payment_statuses();

	return isset( $statuses[ $status ] ) ? $statuses[ $status ] : "";
}

function opalmembership_get_currency() {
	return opalmembership_options( 'currency', 'USD' );
}

function opalmembership_get_payment( $id ) {
	return new OpalMembership_Payment( $id );
}

function opalmembership_sanitize_tooltip( $var ) {
	return htmlspecialchars( wp_kses( html_entity_decode( $var ), [
		'br'     => [],
		'em'     => [],
		'strong' => [],
		'small'  => [],
		'span'   => [],
		'ul'     => [],
		'li'     => [],
		'ol'     => [],
		'p'      => [],
	] ) );
}

/**
 *
 */
function opalmembership_package( $id = null ) {
	global $package;

	$package = new Opalmembership_Package( $id );

	return $package;
}

if ( ! function_exists( 'opalmembership_create_user' ) ) {
	/**
	 * create new wp user
	 */
	function opalmembership_create_user( $credentials = [] ) {
		$cred = wp_parse_args( $credentials, [
			'user_login' => '',
			'user_email' => '',
			'user_pass'  => '',
			'first_name' => '',
			'last_name'  => '',
		] );

		/* sanitize user email */
		$user_email = sanitize_email( $cred['user_email'] );
		if ( email_exists( $user_email ) ) {
			return new WP_Error( 'email-exists', esc_html__( 'An account is already registered with your email address. Please login.', 'opalmembership' ) );
		}

		$username = sanitize_user( $cred['user_login'] );
		if ( ! $username || ! validate_username( $username ) ) {
			return new WP_Error( 'username-invalid', esc_html__( 'Please enter a valid account username.', 'opalmembership' ) );
		}
		/* if username exists */
		if ( username_exists( $username ) ) {
			return new WP_Error( 'username-exists', esc_html__( 'Username is already exists.', 'opalmembership' ) );
		}

		/* password empty */
		if ( ! $cred['user_pass'] ) {
			return new WP_Error( 'password-empty', esc_html__( 'Password is requried.', 'opalmembership' ) );
		} else {
			$password = $cred['user_pass'];
		}

		$user_data = apply_filters( 'opalmembership_create_user_data', [
			'user_login' => $username,
			'user_pass'  => $password,
			'user_email' => $user_email,
		] );

		/* insert new wp user */
		$user_id = wp_insert_user( $user_data );
		if ( is_wp_error( $user_id ) ) {
			return new WP_Error( 'user-create-failed', $user_id->get_error_message() );
		}

		/* allow hook like insert user meta. create new post type agent in opalmembership */
		do_action( 'opalmembership_create_new_user_successfully', $user_id, $user_data, $cred );

		return $user_id;
	}
}

if ( ! function_exists( 'opalmembership_add_notice' ) ) {
	function opalmembership_add_notice( $type = 'error', $message = '' ) {
		if ( ! $type || ! $message ) {
			return;
		}
		$notices = OpalMembership()->session->get( 'notices', [] );
		if ( ! isset( $notices[ $type ] ) ) {
			$notices[ $type ] = [];
		}
		$notices[ $type ][] = $message;
		OpalMembership()->session->set( 'notices', $notices );
	}
}

if ( ! function_exists( 'opalmembership_print_notices' ) ) {

	/**
	 * print all notices
	 */
	function opalmembership_print_notices() {
		$notices = OpalMembership()->session->get( 'notices', [] );

		if ( empty( $notices ) ) {
			return;
		}
		ob_start();
		foreach ( $notices as $type => $messages ) {
			echo Opalmembership_Template_Loader::get_template_part( 'notices/' . $type, [ 'messages' => $messages ] );
		}
		OpalMembership()->session->set( 'notices', [] );
		echo ob_get_clean();
	}
}

if ( ! function_exists( 'opalmembership_print_notice' ) ) {

	/**
	 * print single notice
	 */
	function opalmembership_print_notice( $type = 'warning', $message = '' ) {
		if ( $message ) {
			echo Opalmembership_Template_Loader::get_template_part( 'notices/warning', [ 'messages' => [ $message ] ] );
		}
	}
}

if ( ! function_exists( 'opalmembership_print_login_form' ) ) {
	function opalmembership_print_login_form( $args = [] ) {
		$args = wp_parse_args( $args, [
			'message'    => esc_html__( 'If you have registed with us before, please enter your details in the boxes below. If you are a new agent, please proceed to the Billing section.',
				'opalmembership' ),
			'redirect'   => '',
			'hide_title' => true,
		] );

		ob_start();
		echo Opalmembership_Shortcodes::login_form( $args );
		echo ob_get_clean();
	}
}
if ( ! function_exists( 'opalmembership_print_register_form' ) ) {
	function opalmembership_print_register_form( $args = [] ) {
		$args = wp_parse_args( $args, [
			'message'    => '',
			'redirect'   => '',
			'hide_title' => true,
		] );

		ob_start();
		echo Opalmembership_Shortcodes::register_form( $args );
		echo ob_get_clean();
	}
}
if ( ! function_exists( 'opalmembership_is_ajax_request' ) ) {
	function opalmembership_is_ajax_request() {
		return defined( 'DOING_AJAX' ) && DOING_AJAX;
	}
}
