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

class Opalmembership_PostType_Payment{

	/**
	 *
	 */
	public static function init(){
		add_action( 'init', array( __CLASS__, 'definition' ) );
		add_action( 'init', array( __CLASS__, 'register_post_type_statuses' ) );

		define( 'OPALMEMBERSHIP_PAYMENT_PREFIX', 'opalmembership_payment_' );
	}

	/**
	 *
	 */
	public static function definition(){

		$labels = array(
			'name'                  => esc_html__( 'Opal Payments Transactions', 'opalmembership' ),
			'singular_name'         => esc_html__( 'Payment ', 'opalmembership' ),
			'add_new'               => esc_html__( 'Add New Payment', 'opalmembership' ),
			'add_new_item'          => esc_html__( 'Add New Payment', 'opalmembership' ),
			'edit_item'             => esc_html__( 'Edit Payment', 'opalmembership' ),
			'new_item'              => esc_html__( 'New Payment', 'opalmembership' ),
			'all_items'             => esc_html__( 'All Payments', 'opalmembership' ),
			'view_item'             => esc_html__( 'View Payment', 'opalmembership' ),
			'search_items'          => esc_html__( 'Search Payment', 'opalmembership' ),
			'not_found'             => esc_html__( 'No Payments found', 'opalmembership' ),
			'not_found_in_trash'    => esc_html__( 'No Payments found in Trash', 'opalmembership' ),
			'parent_item_colon'     => '',
			'menu_name'             => esc_html__( 'Opal Payments', 'opalmembership' ),
		);

		$labels = apply_filters( 'opalmembership_postype_payments_labels' , $labels );

		register_post_type( 'membership_payments',
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
				'rewrite'           => array( 'slug' => 'membership-payment' ),
				'menu_position'     => 51,
				'categories'        => array(),
				'menu_icon'         => 'dashicons-admin-home',
				'hierarchical'        => false,
				'show_in_nav_menus'   => false,
				'rewrite'             => false,
				'query_var'           => false,


			)
		);
	}

	/**
	 *
	 */
	public static function metaboxes_fields(){


		$prefix = OPALMEMBERSHIP_PAYMENT_PREFIX;

		$fields =  array(
		  // COLOR
			array(
				'name' => esc_html__( 'email', 'opalmembership' ),
				'id'   => "{$prefix}email",
				'type' => 'text',
				'description' => esc_html__('Enter Job example CEO, CTO','opalmembership')
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
	        'id'         => 'opalmembership-payment',
	        // Meta box title - Will appear at the drag and drop handle bar. Required.
	        'title'      => esc_html__( 'Payment Information', 'opalmembership' ),
	        // Post types, accept custom post types as well - DEFAULT is 'post'. Can be array (multiple post types) or string (1 post type). Optional.
	        'object_types' => array( 'membership_payments' ),
	        // Where the meta box appear: normal (default), advanced, side. Optional.
	        'context'    => 'normal',
	        // Order of meta box: high (default), low. Optional.
	        'priority'   => 'low',
	        // Auto save: true, false (default). Optional.
	        'autosave'   => true,
	        'show_names'                => true,
	        // List of meta fields
	        'fields'     =>  self::metaboxes_fields()
	    );

	  //  echo '<pre>'.print_r( $metaboxes ,1 );die;
	    return $metaboxes;
	}


	public static function  register_post_type_statuses() {
		register_post_status( 'opal-pending', array(
			'label'                     => _x( 'Pending payment', 'Order status', 'opalmembership' ),
			'public'                    => true,
			'exclude_from_search'       => false,
			'show_in_admin_all_list'    => true,
			'show_in_admin_status_list' => true,
			'label_count'               => _n_noop( 'Pending payment <span class="count">(%s)</span>', 'Pending payment <span class="count">(%s)</span>', 'opalmembership' )
		) );


		register_post_status( 'opal-processing', array(
			'label'                     => _x( 'Processing', 'Order status', 'opalmembership' ),
			'public'                    => true,
			'exclude_from_search'       => false,
			'show_in_admin_all_list'    => true,
			'show_in_admin_status_list' => true,
			'label_count'               => _n_noop( 'Processing <span class="count">(%s)</span>', 'Processing <span class="count">(%s)</span>', 'opalmembership' )
		) );
		register_post_status( 'opal-on-hold', array(
			'label'                     => _x( 'On hold', 'Order status', 'opalmembership' ),
			'public'                    => true,
			'exclude_from_search'       => false,
			'show_in_admin_all_list'    => true,
			'show_in_admin_status_list' => true,
			'label_count'               => _n_noop( 'On hold <span class="count">(%s)</span>', 'On hold <span class="count">(%s)</span>', 'opalmembership' )
		) );
		register_post_status( 'opal-completed', array(
			'label'                     => _x( 'Completed', 'Order status', 'opalmembership' ),
			'public'                    => true,
			'exclude_from_search'       => false,
			'show_in_admin_all_list'    => true,
			'show_in_admin_status_list' => true,
			'label_count'               => _n_noop( 'Completed <span class="count">(%s)</span>', 'Completed <span class="count">(%s)</span>', 'opalmembership' )
		) );
		register_post_status( 'opal-cancelled', array(
			'label'                     => _x( 'Cancelled', 'Order status', 'opalmembership' ),
			'public'                    => true,
			'exclude_from_search'       => false,
			'show_in_admin_all_list'    => true,
			'show_in_admin_status_list' => true,
			'label_count'               => _n_noop( 'Cancelled <span class="count">(%s)</span>', 'Cancelled <span class="count">(%s)</span>', 'opalmembership' )
		) );
		register_post_status( 'opal-refunded', array(
			'label'                     => _x( 'Refunded', 'Order status', 'opalmembership' ),
			'public'                    => true,
			'exclude_from_search'       => false,
			'show_in_admin_all_list'    => true,
			'show_in_admin_status_list' => true,
			'label_count'               => _n_noop( 'Refunded <span class="count">(%s)</span>', 'Refunded <span class="count">(%s)</span>', 'opalmembership' )
		) );
		register_post_status( 'opal-failed', array(
			'label'                     => _x( 'Failed', 'Order status', 'opalmembership' ),
			'public'                    => true,
			'exclude_from_search'       => false,
			'show_in_admin_all_list'    => true,
			'show_in_admin_status_list' => true,
			'label_count'               => _n_noop( 'Failed <span class="count">(%s)</span>', 'Failed <span class="count">(%s)</span>', 'opalmembership' )
		) );


		// Discount Code Statuses
		register_post_status( 'active', array(
			'label'                     => _x( 'Active', 'Active discount code status', 'opalmembership' ),
			'public'                    => true,
			'exclude_from_search'       => false,
			'show_in_admin_all_list'    => true,
			'show_in_admin_status_list' => true,
			'label_count'               => _n_noop( 'Active <span class="count">(%s)</span>', 'Active <span class="count">(%s)</span>', 'opalmembership' )
		)  );
		register_post_status( 'inactive', array(
			'label'                     => _x( 'inactive', 'Inactive discount code status', 'opalmembership' ),
			'public'                    => true,
			'exclude_from_search'       => false,
			'show_in_admin_all_list'    => true,
			'show_in_admin_status_list' => true,
			'label_count'               => _n_noop( 'pending <span class="count">(%s)</span>', 'Inactive <span class="count">(%s)</span>', 'opalmembership' )
		)  );


	}

}

Opalmembership_PostType_Payment::init();