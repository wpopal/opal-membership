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

class Opalmembership_Admin_Posttype {

	/**
	 *
	 */
	public function __construct(){
		add_filter( 'manage_membership_payments_posts_columns', array( $this, 'membership_payments_columns' ) );
		add_action( 'manage_membership_payments_posts_custom_column', array( $this, 'membership_payments_columns_render' ), 2 );
	}


	/**
	 * Define custom columns for payments
	 * @param  array $existing_columns
	 * @return array
	 */
	public function membership_payments_columns( $existing_columns ) {


		$columns                     = array();
		$columns['cb']               = $existing_columns['cb'];
		$columns['payment_status']     = '<span class="status_head tips" data-tip="' . esc_attr( 'Status', 'opalmembership' ) . '">' . esc_attr( 'Status', 'opalmembership' ) . '</span>';
		$columns['payment_title']      = esc_html__( 'Payment Transaction', 'opalmembership' );

		$columns['membership_package'] = esc_html__( 'Membership Package', 'opalmembership' );

		$columns['payment_date']       = esc_html__( 'Date', 'opalmembership' );
		$columns['payment_total']      = esc_html__( 'Total', 'opalmembership' );
		$columns['payment_actions']    = esc_html__( 'Actions', 'opalmembership' );

		return $columns;
	}

	/**
	 * Output custom columns for coupons
	 * @param  string $column
	 */
	public function membership_payments_columns_render( $column ) {

		global $post, $opalmembership, $the_payment;

		if ( empty( $the_payment ) || $the_payment->id != $post->ID ) {
			$the_payment = opalmembership_get_payment( $post->ID );
		}

		switch ( $column ) {
			case 'payment_status' :

				printf( '<mark class="%s tips" data-tip="%s">%s</mark>', sanitize_title( $the_payment->get_status() ), opalmembership_get_payment_status_name( $the_payment->get_status() ), opalmembership_get_payment_status_name( $the_payment->get_status() ) );

			break;
			case 'payment_date' :

				if ( '0000-00-00 00:00:00' == $post->post_date ) {
					$t_time = $h_time = esc_html__( 'Unpublished', 'opalmembership' );
				} else {
					$t_time = get_the_time( esc_html__( 'Y/m/d g:i:s A', 'opalmembership' ), $post );
					$h_time = get_the_time( esc_html__( 'Y/m/d', 'opalmembership' ), $post );
				}

				echo '<abbr title="' . esc_attr( $t_time ) . '">' . esc_html( apply_filters( 'post_date_column_time', $h_time, $post ) ) . '</abbr>';

			break;

			case 'membership_package' :

				$paymentedpackages = $the_payment->get_cartitems();

				foreach( $paymentedpackages as $package ){
				?>
				<a target="_blank" href="<?php echo get_edit_post_link( $package['package_id'] ); ?>"> <?php echo $package['package_title']; ?> </a>
				<?php
				}

			break;

			case 'payment_total' :
				echo $the_payment->get_formatted_payment_total();

				if ( $the_payment->payment_method_title ) {
					echo '<small class="meta">' . esc_html__( 'Via', 'opalmembership' ) . ' ' . esc_html( $the_payment->payment_method_title ) . '</small>';
				}
			break;
			case 'payment_title' :

				$customer_tip = array();


				if ( $the_payment->user_id ) {
					$user_info = get_userdata( $the_payment->user_id );
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
					if ( isset( $the_payment->billing_first_name ) && isset( $the_payment->billing_last_name ) ) {
						$username = trim( $the_payment->billing_first_name . ' ' . $the_payment->billing_last_name );
					} else {
						$username = esc_html__( 'Guest', 'opalmembership' );
					}
				}

				printf( _x( '%s by %s', 'Order number by X', 'opalmembership' ), '<a href="' . admin_url( 'post.php?post=' . absint( $post->ID ) . '&action=edit' ) . '" class="row-title"><strong>' . esc_attr( $the_payment->get_payment_number() ) . '</strong></a>', $username );

				if ( isset($the_payment->billing['email']) ) {
					echo '<br />(<small class="meta email"><a href="' . esc_url( 'mailto:' . $the_payment->billing['email'] ) . '">' . esc_html( $the_payment->billing['email'] ) . '</a></small>)';
				}

				echo '<button type="button" class="toggle-row"><span class="screen-reader-text">' . esc_html__( 'Show more details', 'opalmembership' ) . '</span></button>';

			break;
			case 'payment_actions' :

				?><p>
					<?php
						do_action( 'opalmembership_admin_payment_actions_start', $the_payment );

						$actions = array();


						$actions['view'] = array(
							'url'       => admin_url( 'post.php?post=' . $post->ID . '&action=edit' ),
							'name'      => esc_html__( 'View', 'opalmembership' ),
							'action'    => "view"
						);

						$actions = apply_filters( 'opalmembership_admin_payment_actions', $actions, $the_payment );

						foreach ( $actions as $action ) {
							printf( '<a class="button tips %s" href="%s" data-tip="%s">%s</a>', esc_attr( $action['action'] ), esc_url( $action['url'] ), esc_attr( $action['name'] ), esc_attr( $action['name'] ) );
						}

						do_action( 'opalmembership_admin_payment_actions_end', $the_payment );
					?>
				</p><?php

			break;
		}
	}

}

/**
 *
 */
new Opalmembership_Admin_Posttype();