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
 * Single property logic functions
 */


function opalmembership_set_package_grid_column(){
	return opalmembership_get_option( 'gridcols' , 4);
}

add_filter( 'opalmembership_package_grid_column', 'opalmembership_set_package_grid_column' );
/**
 * Single property logic functions
 */
function opalmembership_property_preview(){
	echo Opalmembership_Template_Loader::get_template_part( 'single-property/preview' );
}

/**
 *
 */
function opalmembership_property_content(){
	echo Opalmembership_Template_Loader::get_template_part( 'single-property/content' );
}

/**
 *
 */
function opalmembership_property_features(){
	echo Opalmembership_Template_Loader::get_template_part( 'single-property/features' );
}

/**
 *
 */
function opalmembership_property_amenities(){
	echo Opalmembership_Template_Loader::get_template_part( 'single-property/amenities' );
}

function opalmembership_property_tags(){
	return the_tags( '<footer class="entry-meta"><span class="tag-links">', '', '</span></footer>' );
}

function opalmembership_property_map(){
	echo Opalmembership_Template_Loader::get_template_part( 'single-property/map' );
}


function opalmembership_property_agent(){
	echo Opalmembership_Template_Loader::get_template_part( 'single-property/agent' );
}


function opalmembership_property_video(){
	echo Opalmembership_Template_Loader::get_template_part( 'single-property/video' );
}

function opalmembership_properties_same_agent(){
	echo Opalmembership_Template_Loader::get_template_part( 'single-property/sameagent' );
}

function opalmembership_property_price(){
	echo Opalmembership_Template_Loader::get_template_part( 'single-property/price' );
}

function opalmembership_property_location(){
	return Opalmembership_Template_Loader::get_template_part( 'single-property/location' );
}

/*
 * Hook single property template
 */

add_action( 'opalmembership_before_single_property_summary', 'opalmembership_property_price', 5 );

add_action( 'opalmembership_before_single_property_summary', 'opalmembership_property_location', 10 );

add_action( 'opalmembership_before_single_property_summary', 'opalmembership_property_preview', 15 );

add_action( 'opalmembership_single_property_summary', 'opalmembership_property_content', 5 );

add_action( 'opalmembership_single_property_summary', 'opalmembership_property_features', 10 );
add_action( 'opalmembership_single_property_summary', 'opalmembership_property_amenities', 15 );

add_action( 'opalmembership_single_property_summary', 'opalmembership_property_video', 20 );

add_action( 'opalmembership_single_property_summary', 'opalmembership_property_map', 25 );

add_action( 'opalmembership_single_property_summary', 'opalmembership_property_agent', 30 );
add_action( 'opalmembership_single_property_summary', 'opalmembership_property_tags', 35 );

// checkout page
function opalmembership_dashboard_checkout(){

	$purchase_info = opalmembership_get_purchase_session();

 	if( isset( $purchase_info['cart'] ) ){
		return Opalmembership_Template_Loader::get_template_part( 'checkout/shoppingcart', array( 'purchase_info' => $purchase_info ) );
	}
}


// checkout page
function opalmembership_checkout_shoppingcart(){

	$purchase_info = opalmembership_get_purchase_session();

 	if( isset( $purchase_info['cart'] ) ){
		echo Opalmembership_Template_Loader::get_template_part( 'checkout/shoppingcart', array( 'purchase_info' => $purchase_info ) );
	}
}

add_action( 'opalmembership_before_checkout_form', 'opalmembership_checkout_shoppingcart', 10 );
add_action( 'opalmembership_before_checkout_form', 'opalmembership_checkout_login', 10 );
if ( ! function_exists( 'opalmembership_checkout_login' ) ) {
	function opalmembership_checkout_login() {
		if ( ! is_user_logged_in() ) {
			echo Opalmembership_Template_Loader::get_template_part( 'checkout/login-form' );
		}
	}
}

/**
 *
 */
add_action( 'opalmembership_after_single_property_summary', 'opalmembership_properties_same_agent', 5 );

function opalmembership_agent_summary() {
	return Opalmembership_Template_Loader::get_template_part( 'single-agent/summary' );
}

function opalmembership_agent_properties() {
	return Opalmembership_Template_Loader::get_template_part( 'single-agent/properties' );
}

function opalmembership_agent_contactform() {
	global $post;
	$args = array( 'post_id' => $post->ID );
	echo Opalmembership_Template_Loader::get_template_part( 'single-agent/form', $args );
}

add_action( 'opalmembership_single_agent_summary', 'opalmembership_agent_summary', 5 );
add_action( 'opalmembership_single_agent_summary', 'opalmembership_agent_properties', 10 );
add_action( 'opalmembership_after_single_agent_summary', 'opalmembership_agent_contactform', 15 );

 
add_action( 'the_post', 'opalmembership_package_setup' );
if ( ! function_exists( 'opalmembership_package_setup' ) ) {
	/**
	 * set update package global like post
	 */
	function opalmembership_package_setup() {
		global $post, $package;
		if ( ! $post || ! isset( $post->ID ) ) return;

		unset( $package );
		$package = opalmembership_package( $post->ID );
		return $package;
	}
}

function opalmembership_pagination($pages = '', $range = 2 ) {
    global $paged;

    if(empty($paged))$paged = 1;

    $prev = $paged - 1;
    $next = $paged + 1;
    $showitems = ( $range * 2 )+1;
    $range = 2; // change it to show more links

    if( $pages == '' ){
        global $wp_query;

        $pages = $wp_query->max_num_pages;
        if( !$pages ){
            $pages = 1;
        }
    }

    if( 1 != $pages ){

        echo '<div class="pagination-main">';
            echo '<ul class="pagination">';
                echo ( $paged > 2 && $paged > $range+1 && $showitems < $pages ) ? '<li><a aria-label="First" href="'.get_pagenum_link(1).'"><span aria-hidden="true"><i class="fa fa-angle-double-left"></i></span></a></li>' : '';
                echo ( $paged > 1 ) ? '<li><a aria-label="Previous" href="'.get_pagenum_link($prev).'"><span aria-hidden="true"><i class="fa fa-angle-left"></i></span></a></li>' : '<li class="disabled"><a aria-label="Previous"><span aria-hidden="true"><i class="fa fa-angle-left"></i></span></a></li>';
                for ( $i = 1; $i <= $pages; $i++ ) {
                    if ( 1 != $pages &&( !( $i >= $paged+$range+1 || $i <= $paged-$range-1 ) || $pages <= $showitems ) )
                    {
                        if ( $paged == $i ){
                            echo '<li class="active"><a href="'.get_pagenum_link($i).'">'.$i.' <span class="sr-only"></span></a></li>';
                        } else {
                            echo '<li><a href="'.get_pagenum_link($i).'">'.$i.'</a></li>';
                        }
                    }
                }
                echo ( $paged < $pages ) ? '<li><a aria-label="Next" href="'.get_pagenum_link($next).'"><span aria-hidden="true"><i class="fa fa-angle-right"></i></span></a></li>' : '';
                echo ( $paged < $pages-1 &&  $paged+$range-1 < $pages && $showitems < $pages ) ? '<li><a aria-label="Last" href="'.get_pagenum_link( $pages ).'"><span aria-hidden="true"><i class="fa fa-angle-double-right"></i></span></a></li>' : '';
            echo '</ul>';
        echo '</div>';

    }
}


add_action( 'template_redirect', 'opalmembership_redirect_dashboard' );
if ( ! function_exists( 'opalmembership_redirect_dashboard' ) ) {
	/**
	 * Redirect user to login page if not logged in
	 * Redirect user to dashboard page if user try access login page, resgiter page
	 */
	function opalmembership_redirect_dashboard() {
		global $post;
		if ( ! $post ) return;
		$ignoire_pages = array(
				opalmembership_get_option( 'dashboard_page' ),
				opalmembership_get_option( 'history_page' )
			);
		if ( ! is_user_logged_in() && in_array( $post->ID, $ignoire_pages ) ) {
			wp_redirect( opalmembership_get_login_page_uri() ); exit();
		}

		$ignoire_pages = array(
				opalmembership_get_option( 'register_page' ),
				opalmembership_get_option( 'login_page' )
			);
		if ( is_user_logged_in() && in_array( $post->ID, $ignoire_pages ) ) {
			wp_redirect( opalmembership_get_dashdoard_page_uri() ); exit();
		}

	}
}