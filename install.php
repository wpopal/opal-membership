<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit();
}

function opalmembership_install(){
	/* test */
	delete_option( 'opalmembership_settings' );
	global $opalmembership_options;
	if ( ! class_exists( 'Opalmembership_Plugin_Settings' ) ) {
		require_once OPALMEMBERSHIP_PLUGIN_DIR . 'inc/admin/register-settings.php';
	}

	$settings = new Opalmembership_Plugin_Settings();
	$default_options = $settings->opalmembership_settings();

	// Clear the permalinks
	flush_rewrite_rules( false );

	// Add Upgraded From Option
	$current_version = get_option( 'opalmembership_version' );
	if ( $current_version ) {
		update_option( 'opalmembership_version_upgraded_from', $current_version );
	}

	// Setup some default options
	$options = array();

	//Fresh Install? Setup Test Mode, Base Country (US), Test Gateway, Currency
	if ( empty( $current_version ) ) {

		$options['test_mode']          = 1;
		$options['currency']           = 'USD';
		$options['currency_position']  = 'before';
		$options['gridcols']              = '4';

		$options['gateways'] = array(
			'cod' 			=> 1,
			'pp_standard'   => 1,
			'stripe' 		=> 1
		);
		
		$options['default_gateway'] = 'cod';
	}

	// Checks if the Dashboard Page option exists AND that the page exists
	if ( ! get_post( opalmembership_get_option( 'dashboard_page' ) ) ) {

		// Purchase Confirmation (Dashboard) Page
		$dashboard_page = wp_insert_post(
			array(
				'post_title'     => esc_html__( 'Membership Dashboard', 'opalmembership' ),
				'post_content'   => esc_html__( '[opalmembership_dashboard]', 'opalmembership' ),
				'post_status'    => 'publish',
				'post_author'    => 1,
				'post_type'      => 'page',
				'comment_status' => 'closed'
			)
		);

		// Store our page IDs
		$options['dashboard_page'] = $dashboard_page;
	}


	// Checks if the Membership Page option exists AND that the page exists
	if ( ! get_post( opalmembership_get_option( 'membership_page' ) ) ) {

		// Purchase Confirmation (Dashboard) Page
		$membership_page = wp_insert_post(
			array(
				'post_title'     => esc_html__( 'Membership Packages Page', 'opalmembership' ),
				'post_content'   => esc_html__( '[opalmembership_packages]', 'opalmembership' ),
				'post_status'    => 'publish',
				'post_author'    => 1,
				'post_type'      => 'page',
				'comment_status' => 'closed'
			)
		);

		// Store our page IDs
		$options['membership_page'] = $membership_page;
	}


	// Checks if the Success Page option exists AND that the page exists
	if ( ! get_post( opalmembership_get_option( 'success_page' ) ) ) {

		// Purchase Confirmation (Success) Page
		$success_page = wp_insert_post(
			array(
				'post_title'     => esc_html__( 'Membership Payment Successfull', 'opalmembership' ),
				'post_content'   => esc_html__( '[opalmembership_receipt]', 'opalmembership' ),
				'post_status'    => 'publish',
				'post_author'    => 1,
				'post_type'      => 'page',
				'comment_status' => 'closed'
			)
		);

		// Store our page IDs
		$options['success_page'] = $success_page;
	}

 	// Checks if the Checkout Page option exists AND that the page exists
	if ( ! get_post( opalmembership_get_option( 'checkout_page' ) ) ) {

		// Purchase Confirmation (Checkout) Page
		$checkout_page = wp_insert_post(
			array(
				'post_title'     => esc_html__( 'Membership Checkout', 'opalmembership' ),
				'post_content'   => esc_html__( '[opalmembership_checkout]', 'opalmembership' ),
				'post_status'    => 'publish',
				'post_author'    => 1,
				'post_type'      => 'page',
				'comment_status' => 'closed'
			)
		);

		// Store our page IDs
		$options['checkout_page'] = $checkout_page;
	}

 	// Checks if the History Page option exists AND that the page exists
	if ( ! get_post( opalmembership_get_option( 'history_page' ) ) ) {

		// Purchase Confirmation (History) Page
		$history_page = wp_insert_post(
			array(
				'post_title'     => esc_html__( 'Membership Payment History', 'opalmembership' ),
				'post_content'   => esc_html__( '[opalmembership_history]', 'opalmembership' ),
				'post_status'    => 'publish',
				'post_author'    => 1,
				'post_type'      => 'page',
				'comment_status' => 'closed'
			)
		);

		// Store our page IDs
		$options['history_page'] = $history_page;
	}

	// Checks if the Failed Page option exists AND that the page exists
	// if ( ! get_post( opalmembership_get_option( 'failure_page' ) ) ) {
	//
	// 	// Purchase Confirmation (Failed) Page
	// 	$checkout_faile_page = wp_insert_post(
	// 		array(
	// 			'post_title'     => esc_html__( 'Membership Checkout Failed', 'opalmembership' ),
	// 			'post_content'   => esc_html__( '[opalmembership_checkout_failed]', 'opalmembership' ),
	// 			'post_status'    => 'publish',
	// 			'post_author'    => 1,
	// 			'post_type'      => 'page',
	// 			'comment_status' => 'closed'
	// 		)
	// 	);
	//
	// 	// Store our page IDs
	// 	$options['checkout_faile_page'] = $checkout_faile_page;
	// }

	// Checks if the Register Page option exists AND that the page exists
	if ( ! get_post( opalmembership_get_option( 'register_page' ) ) ) {

		// Purchase Confirmation (register) Page
		$register_page = wp_insert_post(
			array(
				'post_title'     => esc_html__( 'Opal Membership Register', 'opalmembership' ),
				'post_content'   => esc_html__( '[opalmembership_register_form]', 'opalmembership' ),
				'post_status'    => 'publish',
				'post_author'    => 1,
				'post_type'      => 'page',
				'comment_status' => 'closed'
			)
		);

		// Store our page IDs
		$options['register_page'] = $register_page;
	}

	// Checks if the Login Page option exists AND that the page exists
	if ( ! get_post( opalmembership_get_option( 'login_page' ) ) ) {

		// Purchase Confirmation (Login) Page
		$login_page = wp_insert_post(
			array(
				'post_title'     => esc_html__( 'Opal Membership Login', 'opalmembership' ),
				'post_content'   => esc_html__( '[opalmembership_login_form]', 'opalmembership' ),
				'post_status'    => 'publish',
				'post_author'    => 1,
				'post_type'      => 'page',
				'comment_status' => 'closed'
			)
		);

		// Store our page IDs
		$options['login_page'] = $login_page;
	}

	// Populate some default values
	$options = array_merge( $opalmembership_options, $options );
	foreach ( $default_options as $tab => $ops ) {
		if ( ! isset( $ops['fields'] ) ) continue;
		foreach ( $ops['fields'] as $field ) {
			if ( isset( $field['default'] ) && ! isset( $options[ $field['id'] ] ) ) {
				$options[ $field['id'] ] = $field['default'];
			}
		}
	}
	update_option( 'opalmembership_settings', $options );
	update_option( 'opalmembership_version', OPALMEMBERSHIP_VERSION );

	// Create Give roles
	$roles = new Opalmembership_Roles();
	$roles->add_roles();
	$roles->add_caps();

	// Add a temporary option to note that Give pages have been created
	set_transient( '_opalmembership_installed', $options, 30 );

	// Bail if activating from network, or bulk
	if ( is_network_admin() || isset( $_GET['activate-multi'] ) ) {
		return;
	}
	// Add the transient to redirect
	set_transient( '_opalmembership_activation_redirect', true, 30 );

}

register_activation_hook( OPALMEMBERSHIP_PLUGIN_FILE, 'opalmembership_install' );

/**
 * Install user roles on sub-sites of a network
 *
 * Roles do not get created when Give is network activation so we need to create them during admin_init
 *
 * @since 1.0
 * @return void
 */
function opalmembership_install_roles_on_network() {

	global $wp_roles;

	if ( ! is_object( $wp_roles ) ) {
		return;
	}

	if ( ! array_key_exists( 'opalmembership_manager', $wp_roles->roles ) ) {


		$roles = new Opalmembership_Roles;
		$roles->add_roles();
		$roles->add_caps();

	}else {
		// remove_role( 'opalmembership_manager' );
		// remove_role( 'opalmembership_manager' );
		// $roles = new Opalmembership_Roles;
		// $roles->remove_caps();
	}
}

add_action( 'admin_init', 'opalmembership_install_roles_on_network' );


/**
 * Network Activated New Site Setup
 *
 * @description: When a new site is created when Give is network activated this function runs the appropriate install function to set up the site for Give.
 *
 * @since      1.3.5
 *
 * @param $blog_id
 * @param $user_id
 * @param $domain
 * @param $path
 * @param $site_id
 * @param $meta
 */
function opalmembership_on_create_blog( $blog_id, $user_id, $domain, $path, $site_id, $meta ) {
	if ( is_plugin_active_for_network( OPALMEMBERSHIP_PLUGIN_BASENAME ) ) {
		switch_to_blog( $blog_id );
		opalmembership_install();
		restore_current_blog();
	}
}

add_action( 'wpmu_new_blog', 'opalmembership_on_create_blog', 10, 6 );
?>
