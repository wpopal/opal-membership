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

class Opalmembership_Admin_Pages {

	/**
	 *
	 */
	public function __construct(){
 		add_action( 'admin_menu', array( $this, 'pageMenus' ) , 10 );

 		add_action( 'opalmembership_before_render_setting_fields', array( $this, 'load_payment_fields' ), 10, 2 );
	}

	/**
	 *
	 */
	public function pageMenus( $data='', $a = '' ) {

		$icon = OPALMEMBERSHIP_PLUGIN_URL.'assets/icon.png';
		add_menu_page( esc_html__( 'Memberships', 'opalmembership' ), esc_html__( 'Memberships', 'opalmembership' ), 'edit_posts', 'opalmembership', null, $icon , 55 );
		//Users
		$users_page = add_submenu_page( 'opalmembership', esc_html__( 'Users', 'opalmembership' ), esc_html__( 'Member Users', 'opalmembership' ), 'manage_options', 'opalmembership-users', array( $this, 'customers_page') );
		add_submenu_page('opalmembership', esc_html__( 'News categories', 'opalmembership' ), esc_html__( 'Categories', 'opalmembership' ), 'edit_posts', 'edit-tags.php?taxonomy=membership_category&post_type=membership_packages',null );

	 
		//Settings
	 	$settings_page = add_submenu_page( 'opalmembership', esc_html__( 'Settings', 'opalmembership' ), esc_html__( 'Settings', 'opalmembership' ), 'manage_options', 'opalmembership-settings', array(
			new Opalmembership_Plugin_Settings(),
			'admin_page_display'
		) );
		add_action( "load-$users_page", array( $this, 'page_options' ) );

	}

	public function customers_page() {
		if ( ! class_exists( 'WP_List_Table' ) ) {
			require_once ABSPATH . 'wp-admin/includes/class-wp-list-table.php';
		}
		require_once OPALMEMBERSHIP_PLUGIN_DIR . '/inc/admin/class-opalmembership-member-table.php';
		$member_table = new Opalmembership_Member_Table();
		$member_table->prepare_items();
		?>
		<div class="wrap">
			<form method="POST">
				<?php $member_table->display(); ?>
			</form>
		</div>
		<?php
	}

	/**
	 * Add creen options
	 */
	public function page_options() {
		$option = 'per_page';
		$args = array(
				'label' => esc_html__( 'Number of users per page:', 'opalmembership' ),
				'default' => 10,
				'option' => 'users_per_page'
		    );
		add_screen_option( $option, $args );
	}

	/**
	 * load all gateways allow hook setting
	 */
	public function load_payment_fields( $actived, $key ) {
		if ( $actived === 'gateways' ) {
			OpalMembership()->gateways()->get_getways();
		}
	}

}

/**
 *
 */
new Opalmembership_Admin_Pages();
