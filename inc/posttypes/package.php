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

class Opalmembership_PostType_Packages {

	/**
	 *
	 */
	public static function init() {
		add_action( 'init', [ __CLASS__, 'definition' ] );
		add_filter( 'cmb2_meta_boxes', [ __CLASS__, 'metaboxes' ] );
		add_filter( 'opalmembership_postype_membership_metaboxes_fields', [ __CLASS__, 'add_button_text_settings' ], 99, 2 );

		if ( ! defined( 'OPALMEMBERSHIP_PACKAGES_PREFIX' ) ) {
			define( 'OPALMEMBERSHIP_PACKAGES_PREFIX', 'opalmembership_pgk_' );
		}
	}

	/**
	 *
	 */
	public static function definition() {

		$labels = [
			'name'               => esc_html__( 'Opal Packages', 'opalmembership' ),
			'singular_name'      => esc_html__( 'Package', 'opalmembership' ),
			'add_new'            => esc_html__( 'Add New Package', 'opalmembership' ),
			'add_new_item'       => esc_html__( 'Add New Package', 'opalmembership' ),
			'edit_item'          => esc_html__( 'Edit Package', 'opalmembership' ),
			'new_item'           => esc_html__( 'New Package', 'opalmembership' ),
			'all_items'          => esc_html__( 'All Packages', 'opalmembership' ),
			'view_item'          => esc_html__( 'View Packages', 'opalmembership' ),
			'search_items'       => esc_html__( 'Search Package', 'opalmembership' ),
			'not_found'          => esc_html__( 'No Packages found', 'opalmembership' ),
			'not_found_in_trash' => esc_html__( 'No Packages found in Trash', 'opalmembership' ),
			'parent_item_colon'  => '',
			'menu_name'          => esc_html__( 'Opal Packages', 'opalmembership' ),
		];

		$labels = apply_filters( 'opalmembership_postype_packages_labels', $labels );

		register_post_type( 'membership_packages',
			[
				'labels'        => $labels,
				'supports'      => [ 'title', 'editor', 'thumbnail' ],
				'public'        => true,
				'show_in_menu'  => 'opalmembership',
				'has_archive'   => true,
				'rewrite'       => [ 'slug' => esc_html__( 'membership-packages', 'opalmembership' ) ],
				'menu_position' => 51,
				'categories'    => [],
				'menu_icon'     => 'dashicons-admin-home',

			]
		);
	}

	/**
	 *
	 */
	public static function metaboxes_fields() {
		$prefix = OPALMEMBERSHIP_PACKAGES_PREFIX;

		$fields = [
			[
				'name'        => esc_html__( 'Maximum Purchased', 'opalmembership' ),
				'id'          => $prefix . 'maximum_purchased',
				'type'        => 'text',
				'default'     => -1,
				'description' => esc_html__( 'Set Maximum purchased for each user, default limited -1', 'opalmembership' ),
			],

			[
				'name'    => esc_html__( 'Highlighted', 'opalmembership' ),
				'id'      => $prefix . 'hightlighted',
				'type'    => 'radio_inline',
				'options' => [
					0 => esc_html__( 'No', 'opalmembership' ),
					1 => esc_html__( 'Yes', 'opalmembership' ),
				],
			],
			[
				'name'        => esc_html__( 'Price', 'opalmembership' ),
				'id'          => "{$prefix}price",
				'type'        => 'text',
				'description' => sprintf( esc_html__( 'Enter Price Package (%s)', 'opalmembership' ), opalmembership_currency_symbol() ),
			],

			[
				'name'        => esc_html__( 'Saleprice', 'opalmembership' ),
				'id'          => "{$prefix}saleprice",
				'type'        => 'text',
				'description' => sprintf( esc_html__( 'Enter Sale Price (%s)', 'opalmembership' ), opalmembership_currency_symbol() ),
			],

			[
				'name'        => esc_html__( 'Recurring', 'opalmembership' ),
				'id'          => "{$prefix}recurring",
				'type'        => 'checkbox',
				'description' => esc_html__( 'Do you want enable recurring?', 'opalmembership' ),
			],

			[
				'name'        => esc_html__( 'Enable Expired Date ', 'opalmembership' ),
				'id'          => "{$prefix}enable_expired",
				'type'        => 'checkbox',
				'description' => esc_html__( 'Do you want enable expired date?', 'opalmembership' ),
			],

			[
				'name'        => esc_html__( 'Expired Date In', 'opalmembership' ),
				'id'          => "{$prefix}duration",
				'type'        => 'text',
				'attributes'  => [
					'type'    => 'number',
					'pattern' => '\d*',
					'min'     => 0,
				],
				'std'         => '1',
				'description' => esc_html__( 'Enter expired number. Example 1, 2, 3', 'opalmembership' ),
			],

			[
				'name'        => esc_html__( 'Expired Date Type', 'opalmembership' ),
				'id'          => "{$prefix}duration_unit",
				'type'        => 'select',
				'options'     => opalmembership_package_expiry_labels(),
				'description' => esc_html__( 'Enter expired date type. Example Day(s), Week(s), Month(s), Year(s)', 'opalmembership' ),
			],
		];

		return apply_filters( 'opalmembership_postype_membership_metaboxes_fields', $fields, $prefix );
	}

	public static function add_button_text_settings( $fields, $prefix ) {
		$fields[] = [
			'name'    => esc_html__( 'Button Text', 'opalmembership' ),
			'id'      => "{$prefix}button_text",
			'type'    => 'text',
			'default' => esc_html__( 'Buy Now', 'opalmembership' ),
		];

		return $fields;
	}

	/**
	 *
	 */
	public static function metaboxes( $metaboxes ) {
		// 1st meta box
		$metaboxes[] = [
			// Meta box id, UNIQUE per meta box. Optional since 4.1.5
			'id'           => 'opalmembership-standard',
			// Meta box title - Will appear at the drag and drop handle bar. Required.
			'title'        => esc_html__( 'Package Information', 'opalmembership' ),
			// Post types, accept custom post types as well - DEFAULT is 'post'. Can be array (multiple post types) or string (1 post type). Optional.
			'object_types' => [ 'membership_packages' ],
			// Where the meta box appear: normal (default), advanced, side. Optional.
			'context'      => 'normal',
			// Order of meta box: high (default), low. Optional.
			'priority'     => 'low',
			// Auto save: true, false (default). Optional.
			'autosave'     => true,
			// List of meta fields
			'show_names'   => true,
			'fields'       => self::metaboxes_fields(),
		];

		//  echo '<Pre>'.print_r( $metaboxes ,1 ); die ;
		return $metaboxes;
	}
}

Opalmembership_PostType_Packages::init();
