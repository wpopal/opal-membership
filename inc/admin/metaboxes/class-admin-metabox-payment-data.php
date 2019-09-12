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
class Opalmembership_Metabox_Payment_Data {

	/**
	 * Output the metabox
	 */
	public static function output( $post ) {  // die("dd");
		global $post, $thepostid, $thepayment;

		if ( ! is_int( $thepostid ) ) {
			$thepostid = $post->ID;
		}

		if ( ! is_object( $thepayment ) ) {
			$thepayment = new Opalmembership_Payment( $thepostid );
		}

		include( 'views/html-payment-data.php' );
	}

	/**
	 * Save meta box data
	 */ 
	public static function save( $post_id, $post ) {
		
		
		 
	}
}
