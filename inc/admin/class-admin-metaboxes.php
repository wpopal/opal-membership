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

class Opalmembership_Admin_Metaboxes {

	/**
	 *
	 */
	public function __construct(){

		$this->included();

		add_action( 'add_meta_boxes', array( $this, 'remove_meta_boxes' ), 10 );
		add_action( 'add_meta_boxes', array( $this, 'add_meta_boxes' ), 30 );
		add_action( 'save_post', array( $this, 'save_meta_boxes' ), 1, 2 );

		add_action( 'opalmembership_payments_save_meta', 'Opalmembership_Metabox_Payment_Data::save', 10, 2 );
		add_action( 'opalmembership_payments_save_meta', 'Opalmembership_Metabox_Payment_Items::save', 10, 2 );
		// add_action( 'opalmembership_payments_save_meta', 'Opalmembership_Metabox_Payment_Data::save', 10, 2 );

		add_action( 'opalmembership_payments_save_meta','Opalmembership_Metabox_Payment_Actions::save', 13, 2  );

		/**
		 * Protect box setting
		 */
		// add_action( 'add_meta_boxes', array( $this, 'add_protect_metabox' ), 30 );

	}

	/**
	 *
	 */
	public function included(){
		opalmembership_includes( OPALMEMBERSHIP_PLUGIN_DIR . 'inc/admin/metaboxes/*.php' );
	}

	public function add_protect_metabox(){

		$post_types = get_post_types();

		$payment_type_object = get_post_type_object( 'membership_payments' );

        foreach ($post_types as $post_type => $type) {
			add_meta_box( 'opalmembership-protect-data',
							 sprintf( esc_html__( '%s Content Protect', 'opalmembership' ),
							 $payment_type_object->labels->singular_name ),
						  'Opalmembership_Metabox_Protect::output', $type, 'normal', 'low' );
		}

		// do_action( 'opalmembership-add-meta-box-protect' );
		// return true;
	}

	/**
	 *
	 */
	public function add_meta_boxes( $a ='' ){
		// Orders

	 	global $post;

 		if( get_post_type() == 'membership_payments' ){

			$payment_type_object = get_post_type_object( 'membership_payments' );

			$type = get_post_type() ;

			add_meta_box( 'opalmembership-payment-data', sprintf( esc_html__( '%s Data', 'opalmembership' ), $payment_type_object->labels->singular_name ), 'Opalmembership_Metabox_Payment_Data::output', $type, 'normal', 'high' );
			add_meta_box( 'opalmembership-payment-items', sprintf( esc_html__( '%s Items', 'opalmembership' ), $payment_type_object->labels->singular_name ), 'Opalmembership_Metabox_Payment_Items::output', $type, 'normal', 'high' );

			add_meta_box( 'opalmembership-payment-notes', sprintf( esc_html__( '%s Notes', 'opalmembership' ), $payment_type_object->labels->singular_name ), 'Opalmembership_Metabox_Payment_Notes::output', $type, 'side', 'default' );

			add_meta_box( 'opalmembership-payment-actions', sprintf( esc_html__( '%s Actions', 'opalmembership' ), $payment_type_object->labels->singular_name ), 'Opalmembership_Metabox_Payment_Actions::output', $type, 'side', 'high' );

			remove_meta_box( 'submitdiv', $type, 'side' );

		}
	}

	/**
	 * Remove bloat
	 */
	public function remove_meta_boxes() {


	}


	public function save_meta_boxes( $post_id , $post ){
		// $post_id and $post are required
		if ( empty( $post_id ) || empty( $post )   ) {
			return;
		}

		// Dont' save meta boxes for revisions or autosaves
		if ( defined( 'DOING_AUTOSAVE' ) || is_int( wp_is_post_revision( $post ) ) || is_int( wp_is_post_autosave( $post ) ) ) {
			return;
		}

		// Check the nonce
		if ( empty( $_POST['opalmembership_payment_data_nonce'] ) || ! isset( $_POST['opalmembership_payment_data_nonce'] )  ) {
			return;
		}

		// Check the post being saved == the $post_id to prevent triggering this call for other save_post events
		if ( empty( $_POST['post_ID'] ) || $_POST['post_ID'] != $post_id ) {
			return;
		}

		// Check user has permission to edit
		if ( ! current_user_can( 'edit_post', $post_id ) ) {
			return;
		}

		// Check the post type
		if (  $post->post_type == 'membership_payments' ) {
			do_action( 'opalmembership_payments_save_meta', $post_id, $post );
		}
	}
}

/**
 *
 */
new Opalmembership_Admin_Metaboxes();