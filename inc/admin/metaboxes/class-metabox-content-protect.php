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
 * WC_Meta_Box_Order_Notes Class
 */
class Opalmembership_Metabox_Protect {

	/**
	 * Output the metabox
	 */
	public static function output( $post ) {
		global $post;

		$args = array(
			'post_id'   => $post->ID,
			'orderby'   => 'comment_ID',
			'order'     => 'DESC',
			'approve'   => 'approve',
			'type'      => 'payment_notes'
		);


		$notes = get_comments( $args );


		echo '<ul class="order_notes">';

		if ( $notes ) {

			foreach( $notes as $note ) {

				$note_classes = get_comment_meta( $note->comment_ID, 'is_customer_note', true ) ? array( 'customer-note', 'note' ) : array( 'note' );

				?>
			 	<?php echo opalmembership_get_payment_note_html( $note , $note->ID ); ?>
				<?php
			}

		} else {
			echo '<li>' . esc_html__( 'There are no notes yet.', 'opalmembership' ) . '</li>';
		}

		echo '</ul>';
		?>
		<div class="add_note">
			<h4><?php esc_html_e( 'Add note', 'opalmembership' ); ?> <img class="help_tip" data-tip='<?php esc_attr_e( 'Add a note for your reference, or add a customer note (the user will be notified).', 'opalmembership' ); ?>' src="<?php echo ''; ?>/assets/images/help.png" height="16" width="16" /></h4>
			<p>
				<textarea type="text" name="order_note" id="add_order_note" class="input-text" cols="20" rows="5"></textarea>
			</p>
			<p>
				<?php wp_nonce_field( 'opalmembership-add-customer-note', 'opalmembership_add_customer_note_nonce', true, true ); ?>

				<select name="order_note_type" id="order_note_type">
					<option value=""><?php esc_html_e( 'Private note', 'opalmembership' ); ?></option>
					<option value="customer"><?php esc_html_e( 'Note to customer', 'opalmembership' ); ?></option>
				</select>
				<a href="#" class="add_note button" data-post-id="<?php echo $post->ID; ?>"><?php esc_html_e( 'Add', 'opalmembership' ); ?></a>
			</p>
		</div>
		<?php
	}
}
