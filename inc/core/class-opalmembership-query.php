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

class Opalmembership_Query {

	public static function get_packages( $per_page = 10, $paged = -1 ) {
		$args = [
			'post_type'      => 'membership_packages',
			'posts_per_page' => $per_page,
			'paged'          => $paged,
			'order'          => 'ASC',
			'orderby'        => 'meta_value_num',
			'meta_key'       => OPALMEMBERSHIP_PACKAGES_PREFIX . 'price',
		];

		$query = new WP_Query( $args );

		wp_reset_postdata();

		return $query;
	}

	public static function get_user_purchased_package( $user_id, $package_id ) {
		$args = [
			'post_type'      => 'membership_payments',
			'posts_per_page' => -1,
			'paged'          => 99,
			'post_status'    => 'opal-completed',
			'meta_query'     => [
				[
					'key'   => OPALMEMBERSHIP_PAYMENT_PREFIX . 'user_id',
					'value' => $user_id,
				],
				[
					'key'   => OPALMEMBERSHIP_PAYMENT_PREFIX . 'package_id',
					'value' => $package_id,
				],
			],
		];

		$query = new WP_Query( $args );

		wp_reset_postdata();

		return $query->found_posts;
	}

	/**
	 * get List Payment By User
	 */
	public static function get_payments_by_user( $user_id, $per_page = 10, $paged = -1 ) {
		$args = [
			'post_type'      => 'membership_payments',
			'posts_per_page' => $per_page,
			'paged'          => $paged,
			'meta_query'     => [
				[
					'key'   => OPALMEMBERSHIP_PAYMENT_PREFIX . 'user_id',
					'value' => $user_id,

				],
			],
		];

		$query = new WP_Query( $args );

		wp_reset_postdata();

		return $query;
	}
}
