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

function opalmembership_get_package_name( $post_id ) {
	$post = get_post( $post_id );
	wp_reset_query();

	return $post ? $post->post_title : esc_html__( 'Unknown Package', 'opalmembership' );
}

/**
 *
 */
function opalmembership_get_packages_list() {

	$query = Opalmembership_Query::get_packages();

	$output = [];

	$output[0] = esc_html__( 'All Packages', 'opalmembership' );

	foreach ( $query->posts as $post ) {
		$output[ $post->ID ] = $post->post_title . '( ' . $post->ID . ' )';
	}

	wp_reset_query();

	return $output;
}

function opalmembership_is_membership_valid( $user_id = null ) {
	return Opalmembership_User::is_membership_valid( $user_id );
}

/**
 *
 */
function opalmembership_package_expiry_labels() {
	return apply_filters( 'opalmembership_package_expiry_lables', [
		'day'   => esc_html__( 'Day', 'opalmembership' ),
		'week'  => esc_html__( 'Week', 'opalmembership' ),
		'month' => esc_html__( 'Month', 'opalmembership' ),
		'year'  => esc_html__( 'Year', 'opalmembership' ),
	] );
}

function opalmembership_package_get_expiry_label( $type = '' ) {
	$labels = opalmembership_package_expiry_labels();

	return isset( $labels[ $type ] ) ? $labels[ $type ] : '';
}

function opalmembership_package_get_expiration_date( $package_id, $date ) {
	$package = opalmembership_package( $package_id );

	return $package->get_expiration_date( $date );
}

/**
 * Get Instance Of OpalMembership_Payment Object
 */
function opalmembership_payment( $payment_id ) {
	return new OpalMembership_Payment( $payment_id );
}

function opalmembership_dashboard_page() {
	echo do_shortcode( '[opalmembership_user_current_package]' );
}

function opalmembership_is_limit_purchased() {
	$purchase     = opalmembership_get_purchase_session();
	$current_user = wp_get_current_user();

	if ( $purchase && $current_user->ID ) {
		$package_id = $purchase['cart']['package_id'];
		$limit      = (int) get_post_meta( $package_id, OPALMEMBERSHIP_PACKAGES_PREFIX . 'maximum_purchased', true );

		// check if enable
		if ( $limit > 0 ) {
			$purchased = Opalmembership_Query::get_user_purchased_package( $current_user->ID, $package_id );
			if ( $purchased >= $limit ) {
				return true;
			}
		}
	}

	return false;
}
