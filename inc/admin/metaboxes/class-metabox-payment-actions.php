<?php
/**
 * Order Actions
 *
 * Functions for displaying the order actions meta box.
 *
 * @author      WooThemes
 * @category    Admin
 * @package     WooCommerce/Admin/Meta Boxes
 * @version     2.1.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

/**
 * WC_Meta_Box_Order_Actions Class
 */
class Opalmembership_Metabox_Payment_Actions {

	/**
	 * Output the metabox
	 */
	public static function output( $post ) {
		global $thepayment, $post, $thepostid;

		// This is used by some callbacks attached to hooks such as opalmembership_order_actions which rely on the global to determine if actions should be displayed for certain orders.
		if ( ! is_int( $thepostid ) ) {
			$thepostid = $post->ID;
		}

		if ( ! is_object( $thepayment ) ) {
			$thepayment = new Opalmembership_Payment( $thepostid );
		}

		$payment_type_object = get_post_type_object( $post->post_type );

		// echo '<pre>'.print_r( $thepayment->get_status(), 1 );die ;

		?>
		<ul class="order_actions submitbox">

			<?php do_action( 'opalmembership_order_actions_start', $post->ID ); ?>

			<li class="wide" id="actions">
					<div class="opalmembership-admin-box-inside">
						<p>
							<span class="label"><?php esc_html_e( 'Status:', 'opalmembership' ); ?></span>&nbsp;
							<select name="opalmembership-payment-status" class="medium-text">
								<?php foreach ( opalmembership_get_payment_statuses() as $key => $status ) : ?>
									<option value="<?php echo esc_attr( $key ); ?>" <?php selected( $thepayment->get_status(), $key ); ?>><?php echo esc_html( $status ); ?></option>
								<?php endforeach; ?>
							</select>
							<span class="opalmembership-donation-status status-<?php echo sanitize_title( $thepayment->get_status() ); ?>"><span class="opalmembership-payment-status-icon"></span></span>
						</p>
					</div>
			</li>

			<li class="wide">
				<div id="delete-action"><?php

					if ( current_user_can( 'delete_post', $post->ID ) ) {

						if ( ! EMPTY_TRASH_DAYS ) {
							$delete_text = esc_html__( 'Delete Permanently', 'opalmembership' );
						} else {
							$delete_text = esc_html__( 'Move to Trash', 'opalmembership' );
						}
						?><a class="submitdelete deletion" href="<?php echo esc_url( get_delete_post_link( $post->ID ) ); ?>"><?php echo $delete_text; ?></a><?php
					}
				?></div>

				<input type="submit" class="button save_order button-primary tips" name="save" value="<?php printf( esc_html__( 'Save %s', 'opalmembership' ), $payment_type_object->labels->singular_name ); ?>" data-tip="<?php printf( esc_html__( 'Save/update the %s', 'opalmembership' ), $payment_type_object->labels->singular_name ); ?>" />
			</li>

			<?php do_action( 'opalmembership_order_actions_end', $post->ID ); ?>

		</ul>
		<?php
	}

	/**
	 * Save meta box data
	 */
	public static function save( $post_id, $post ) {
		if(  $post->post_type == 'membership_payments' && $post_id ){
			Opalmembership_Payment::update_payment_status( $post_id, sanitize_text_field( $_POST['opalmembership-payment-status'] ) );
		}
 

	}

	/**
	 * Set the correct message ID
	 *
	 * @param $location
	 *
	 * @since  2.3.0
	 *
	 * @static
	 *
	 * @return string
	 */
	public static function set_email_sent_message( $location ) {
		return add_query_arg( 'message', 11, $location );
	}

}
