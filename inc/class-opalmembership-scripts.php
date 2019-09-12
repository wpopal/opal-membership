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
class Opalmembership_Scripts{

	public static function init(){

		add_action( 'wp_enqueue_scripts', array( __CLASS__, 'loadScripts' ) );
		add_action( 'admin_enqueue_scripts', array( __CLASS__, 'loadAdminScripts' ) );

	}

	public static function loadAdminScripts(){
		wp_enqueue_script( "opalmembership-admin-scripts", OPALMEMBERSHIP_PLUGIN_URL . 'assets/admin-scripts.js', array( 'jquery' ), "1.0.0", true);
		wp_enqueue_style( "opalmembership-admin-styles", OPALMEMBERSHIP_PLUGIN_URL . 'assets/admin-styles.css',  null, "1.3", false );
	}

	public static function loadScripts(){

	 	$localize_ajax    = apply_filters( 'opalmembership_global_ajax_vars', array(
			'ajaxurl'     => esc_js( admin_url('admin-ajax.php') ),
			'checkout'	  => esc_js(opalmembership_get_checkout_page_uri())
		) );

	 	wp_enqueue_script( 'opalmembership-scripts', OPALMEMBERSHIP_PLUGIN_URL . 'assets/script.js' ); // , array( 'jquery' ), "1.0.0", true
	 	wp_localize_script( 'opalmembership-scripts', 'opalmembership_scripts', $localize_ajax );

		wp_enqueue_style( 'opalmembership-styles', OPALMEMBERSHIP_PLUGIN_URL . 'assets/style.css' );

		wp_enqueue_script( 'opalmembership-checkout-scripts', OPALMEMBERSHIP_PLUGIN_URL . 'assets/jquery.payment.min.js' );
	}
}

Opalmembership_Scripts::init();

