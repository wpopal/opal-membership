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

class Opalmembership_PostType_Coupon{

	/**
	 *
	 */
	public static function init(){
		
		add_action( 'init', array( __CLASS__, 'definition' ) );
		add_filter( 'cmb2_meta_boxes', array( __CLASS__, 'metaboxes' ) );

		add_filter( 'enter_title_here', array( __CLASS__, 'change_enter_title_here' ), 1,3  );

		define( 'OPALMEMBERSHIP_COUPON_PREFIX', 'opalmembership_coupon_' );
	}

	/**
	 *
	 */
	public static function definition(){

		$labels = array(
			'name'                  => esc_html__( 'Opal Coupons', 'opalmembership' ),
			'singular_name'         => esc_html__( 'Membership', 'opalmembership' ),
			'add_new'               => esc_html__( 'Add New Coupon', 'opalmembership' ),
			'add_new_item'          => esc_html__( 'Add New Coupon', 'opalmembership' ),
			'edit_item'             => esc_html__( 'Edit Coupon', 'opalmembership' ),
			'new_item'              => esc_html__( 'New Coupon', 'opalmembership' ),
			'all_items'             => esc_html__( 'All Coupons', 'opalmembership' ),
			'view_item'             => esc_html__( 'View Coupon', 'opalmembership' ),
			'search_items'          => esc_html__( 'Search Coupon', 'opalmembership' ),
			'not_found'             => esc_html__( 'No Coupons found', 'opalmembership' ),
			'not_found_in_trash'    => esc_html__( 'No Coupons found in Trash', 'opalmembership' ),
			'parent_item_colon'     => '',
			'menu_name'             => esc_html__( 'Opal Coupons', 'opalmembership' ),
		);

		$labels = apply_filters( 'opalmembership_postype_coupons_labels' , $labels );

		register_post_type( 'membership_coupons',
			array(
				'labels'            => $labels,
				'supports'            => array( 'title'  ),
				'public'              => false,
				'show_ui'             => true,
				'map_meta_cap'        => true,
				'publicly_queryable'  => false,
				'exclude_from_search' => true,

				'show_in_menu'	  => 'opalmembership',
				'has_archive'       => true,
				'menu_position'     => 90,
				'categories'        => array(),
				'menu_icon'         => 'dashicons-admin-home',
				'hierarchical'        => false,
				'show_in_nav_menus'   => false,
				'rewrite'             => false,
				'query_var'           => false,
			)
		);
	}
	public static function change_enter_title_here( $title ){
	     $screen = get_current_screen();

	     if  ( 'membership_coupons' == $screen->post_type ) {
	          $title = esc_html__( 'Enter your coupon code' );
	     }

	     return $title;
	}


	/**
	 *
	 */
	public static function metaboxes_fields(){


		$prefix = OPALMEMBERSHIP_COUPON_PREFIX;

		$packages = opalmembership_get_packages_list();


		$fields =  array(
		  // COLOR
			array(
				'name' 			=> esc_html__( 'Discount Type', 'opalmembership' ),
				'id'   			=> "{$prefix}type",
				'type' 			=> 'select',
				'options' 		=> array('percenatage' => esc_html__('Percenatage','opalmembership'), 'fixed' => esc_html__('Fixed Amount','opalmembership') ),
				'description' => esc_html__('Select discount type','opalmembership')
			),

			array(
				'name' 			=> esc_html__( 'Discount Value', 'opalmembership' ),
				'id'   			=> "{$prefix}value",
				'type' 			=> 'text',
				'description' 	=> esc_html__('Enter discount value','opalmembership'),
				'default'		=> 0,
				'attributes'	=> array(
						'type' 		=> 'number',
						'pattern' 	=> '\d*',
						'min'		=> 0
					)
			),

			array(
				'name' 			=> esc_html__( 'Start Date', 'opalmembership' ),
				'id'   			=> "{$prefix}start_date",
				'type' 			=> 'text_date',
				'date_format'	=> get_option( 'date_format' ),
				'description' 	=> esc_html__('Enter Start Date','opalmembership')
			),

			array(
				'name' 			=> esc_html__( 'Expired Date', 'opalmembership' ),
				'id'   			=> "{$prefix}expired_date",
				'type' 			=> 'text_date',
				'date_format'	=> get_option( 'date_format' ),
				'description' 	=> esc_html__('Enter End Date','opalmembership')
			),

			array(
				'name' 			=> esc_html__( 'Usage Limit', 'opalmembership' ),
				'id'   			=> "{$prefix}usage_limit",
				'type' 			=> 'text',
				'attributes' 	=> array(
					'type' 		=> 'number',
					'pattern' 	=> '\d*',
					'min'		=> 0
				),
				'description' => esc_html__('Enter usage limit time','opalmembership')
			),

			array(
				'name' => esc_html__( 'Discount For', 'opalmembership' ),
				'id'   => "{$prefix}applyfor",
				'type' => 'select',
				'options' => $packages,
				'description' => esc_html__('Select Package use this coupon.','opalmembership')
			),

		);

		return apply_filters( 'opalmembership_postype_agent_metaboxes_fields' , $fields );
	}
	/**
	 *
	 */
	public static function metaboxes( $metaboxes ){
		   // 1st meta box
	    $metaboxes[] = array(
	        // Meta box id, UNIQUE per meta box. Optional since 4.1.5
	        'id'         => 'standard-coupons',
	        // Meta box title - Will appear at the drag and drop handle bar. Required.
	        'title'      => esc_html__( 'Coupon Information', 'opalmembership' ),
	        // Post types, accept custom post types as well - DEFAULT is 'post'. Can be array (multiple post types) or string (1 post type). Optional.
	        'object_types' => array( 'membership_coupons' ),
	        // Where the meta box appear: normal (default), advanced, side. Optional.
	        'context'    => 'normal',
	        // Order of meta box: high (default), low. Optional.
	        'priority'   => 'low',
	        // Auto save: true, false (default). Optional.
	        'autosave'   => true,
	        // List of meta fields
	        'fields'     =>  self::metaboxes_fields()
	    );

	    return $metaboxes;
	}
}

Opalmembership_PostType_Coupon::init();
