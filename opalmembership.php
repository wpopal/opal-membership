<?php
/**
 * Plugin Name: Opal Membership
 * Plugin URI: http://www.wpopal.com/plugins/membership
 * Description: There are plenty of WordPress Membership Plugins but not all of them have the specific features that you may need for creating your membership site.
 * Version: 1.2.3
 * Author: WPOPAL
 * Author URI: http://www.wpopal.com
 * Requires at least: 4.6
 * Tested up to: 5.3.2
 *
 * Text Domain: opalmembership
 * Domain Path: /languages/
 *
 * @package  Opal Membership
 * @category Plugins
 * @author   WPOPAL
 */
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * @class   OpalMembership
 *
 * @version 1.0
 */
if ( ! class_exists( 'OpalMembership' ) ) {

	final class OpalMembership {

		/**
		 * @var Opalmembership The one true Opalmembership
		 * @since 1.0
		 */
		private static $instance;

		/**
		 * Opalmembership Roles Object
		 *
		 * @var object
		 * @since 1.0
		 */
		public $roles;

		/**
		 * Opalmembership Settings Object
		 *
		 * @var object
		 * @since 1.0
		 */
		public $opalmembership_settings;

		/**
		 * Opalmembership Session Object
		 *
		 * This holds donation data for user's session
		 *
		 * @var object
		 * @since 1.0
		 */
		public $session;

		/**
		 * Opalmembership HTML Element Helper Object
		 *
		 * @var object
		 * @since 1.0
		 */
		public $html;

		/**
		 * Opalmembership Emails Object
		 *
		 * @var object
		 * @since 1.0
		 */
		public $emails;

		/**
		 * Opalmembership Email Template Tags Object
		 *
		 * @var object
		 * @since 1.0
		 */
		public $email_tags;

		/**
		 * Opalmembership Customers DB Object
		 *
		 * @var object
		 * @since 1.0
		 */
		public $customers;

		/**
		 * Opalmembership API Object
		 *
		 * @var object
		 * @since 1.1
		 */
		public $api;

		/**
		 *
		 */
		public function __construct() {
			register_activation_hook( __FILE__, [ $this, 'install' ] );
			register_deactivation_hook( __FILE__, [ $this, 'uninstall' ] );
		}

		/**
		 * Main Opalmembership Instance
		 *
		 * Insures that only one instance of Opalmembership exists in memory at any one
		 * time. Also prevents needing to define globals all over the place.
		 *
		 * @return    Opalmembership
		 * @uses      Opalmembership::setup_constants() Setup the constants needed
		 * @uses      Opalmembership::includes() Include the required files
		 * @uses      Opalmembership::load_textdomain() load the language files
		 * @see       Opalmembership()
		 * @since     1.0
		 * @static
		 * @staticvar array $instance
		 */
		public static function getInstance() {
			if ( ! isset( self::$instance ) && ! ( self::$instance instanceof Opalmembership ) ) {

				self::$instance = new OpalMembership;
				self::$instance->setup_constants();

				add_action( 'plugins_loaded', [ self::$instance, 'load_textdomain' ] );

				self::$instance->includes();

				self::$instance->session = new Opalmembership_Session();

				//self::$instance->html               = new Opalmembership_HTML_Elements();

				//self::$instance->emails              = Opalmembership_Emails::instance();

				//self::$instance->email_tags         = new Opalmembership_Email_Template_Tags();
				// Install needed components on plugin activation


			}


			return self::$instance;
		}

		/**
		 * Throw error on object clone
		 *
		 * The whole idea of the singleton design pattern is that there is a single
		 * object, therefore we don't want the object to be cloned.
		 *
		 * @return void
		 * @since  1.0
		 * @access protected
		 */
		public function __clone() {
			// Cloning instances of the class is forbidden
			_doing_it_wrong( __FUNCTION__, esc_html__( 'Cheatin&#8217; huh?', 'opalmembership' ), '1.2.3' );
		}

		/**
		 *
		 */
		public function setup_constants() {
			// Plugin version
			if ( ! defined( 'OPALMEMBERSHIP_VERSION' ) ) {
				define( 'OPALMEMBERSHIP_VERSION', '1.2.3' );
			}

			// Plugin Folder Path
			if ( ! defined( 'OPALMEMBERSHIP_PLUGIN_DIR' ) ) {
				define( 'OPALMEMBERSHIP_PLUGIN_DIR', plugin_dir_path( __FILE__ ) );
			}

			// Plugin Folder URL
			if ( ! defined( 'OPALMEMBERSHIP_PLUGIN_URL' ) ) {
				define( 'OPALMEMBERSHIP_PLUGIN_URL', plugin_dir_url( __FILE__ ) );
			}

			// Plugin Root File
			if ( ! defined( 'OPALMEMBERSHIP_PLUGIN_FILE' ) ) {
				define( 'OPALMEMBERSHIP_PLUGIN_FILE', __FILE__ );
			}

			/* Plugin basename */
			if ( ! defined( 'OPALMEMBERSHIP_PLUGIN_BASENAME' ) ) {
				define( 'OPALMEMBERSHIP_PLUGIN_BASENAME', basename( OPALMEMBERSHIP_PLUGIN_DIR ) . '/' . basename( OPALMEMBERSHIP_PLUGIN_FILE ) );
			}
		}

		public function setup_cmb2_url() {
			return OPALMEMBERSHIP_PLUGIN_DIR . 'inc/vendors/cmb2/libraries';
		}

		public function includes() {

			global $opalmembership_options;

			if ( file_exists( WP_PLUGIN_DIR . '/cmb2/init.php' ) ) {
				require_once WP_PLUGIN_DIR . '/cmb2/init.php';
				add_filter( 'cmb2_meta_box_url', [ $this, 'setup_cmb2_url' ] );
			} elseif ( file_exists( OPALMEMBERSHIP_PLUGIN_DIR . 'inc/vendors/cmb2/init.php' ) ) {
				require_once OPALMEMBERSHIP_PLUGIN_DIR . 'inc/vendors/cmb2/init.php';
			}


			require_once OPALMEMBERSHIP_PLUGIN_DIR . 'inc/mixes-functions.php';

			// include classes entity  ///
			opalmembership_includes( OPALMEMBERSHIP_PLUGIN_DIR . 'inc/core/*.php' );

			// require_once OPALMEMBERSHIP_PLUGIN_DIR . 'inc/class-opalmembership-roles.php';

			require_once OPALMEMBERSHIP_PLUGIN_DIR . 'inc/class-opalmembership-scripts.php';

			//	require_once OPALMEMBERSHIP_PLUGIN_DIR . 'inc/class-opalmembership-session.php';
			require_once OPALMEMBERSHIP_PLUGIN_DIR . 'inc/admin/register-settings.php';

			$opalmembership_options = opalmembership_get_settings();


			require_once OPALMEMBERSHIP_PLUGIN_DIR . 'inc/class-template-loader.php';


			opalmembership_includes( OPALMEMBERSHIP_PLUGIN_DIR . 'inc/posttypes/*.php' );
			opalmembership_includes( OPALMEMBERSHIP_PLUGIN_DIR . 'inc/taxonomies/*.php' );

			require_once OPALMEMBERSHIP_PLUGIN_DIR . 'inc/template-functions.php';

			require_once OPALMEMBERSHIP_PLUGIN_DIR . 'inc/class-opalmembership-checkout.php';
			require_once OPALMEMBERSHIP_PLUGIN_DIR . 'inc/class-opalmembership-shortcodes.php';

			if ( is_admin() ) {
				require_once OPALMEMBERSHIP_PLUGIN_DIR . 'inc/admin/class-admin-pages.php';
				require_once OPALMEMBERSHIP_PLUGIN_DIR . 'inc/admin/class-admin-metaboxes.php';
				require_once OPALMEMBERSHIP_PLUGIN_DIR . 'inc/admin/class-admin-posttype.php';
			}

			require_once OPALMEMBERSHIP_PLUGIN_DIR . 'inc/class-opalmembership-ajax.php';
			require_once OPALMEMBERSHIP_PLUGIN_DIR . 'inc/country-functions.php';
			require_once OPALMEMBERSHIP_PLUGIN_DIR . 'inc/class-opalmembership-user.php';
			require_once OPALMEMBERSHIP_PLUGIN_DIR . 'inc/functions.php';
			require_once OPALMEMBERSHIP_PLUGIN_DIR . 'inc/class-opalmembership-form-handler.php';
			require_once OPALMEMBERSHIP_PLUGIN_DIR . 'inc/class-opalmembership-cronjob.php';
			require_once OPALMEMBERSHIP_PLUGIN_DIR . 'inc/class-opalmembership-email.php';

			require_once OPALMEMBERSHIP_PLUGIN_DIR . 'inc/widget-functions.php';

			require_once OPALMEMBERSHIP_PLUGIN_DIR . 'install.php';

		}

		public function install() {

			$this->cron_job();
		}

		public function uninstall() {
			$this->clear_cron_job();
		}

		/**
		 *
		 */
		public function load_textdomain() {
			// Set filter for Opalmembership's languages directory
			$lang_dir = dirname( plugin_basename( OPALMEMBERSHIP_PLUGIN_FILE ) ) . '/languages/';
			$lang_dir = apply_filters( 'opalmembership_languages_directory', $lang_dir );

			// Traditional WordPress plugin locale filter
			$locale = apply_filters( 'plugin_locale', get_locale(), 'opalmembership' );
			$mofile = sprintf( '%1$s-%2$s.mo', 'opalmembership', $locale );

			// Setup paths to current locale file
			$mofile_local  = $lang_dir . $mofile;
			$mofile_global = WP_LANG_DIR . '/opalmembership/' . $mofile;

			if ( file_exists( $mofile_global ) ) {
				// Look in global /wp-content/languages/opalmembership folder
				load_textdomain( 'opalmembership', $mofile_global );
			} elseif ( file_exists( $mofile_local ) ) {
				// Look in local /wp-content/plugins/opalmembership/languages/ folder
				load_textdomain( 'opalmembership', $mofile_local );
			} else {
				// Load the default language files
				load_plugin_textdomain( 'opalmembership', false, $lang_dir );
			}
		}

		/*
	     * Function that schedules a hook to be executed daily (cron job)
	     *
	     */
		public function cron_job() {
			// Schedule event for checking subscription status
			wp_schedule_event( time(), 'daily', 'opalmembership_check_subscription_status' );
		}

		/*
		 * Function that cleans the scheduler on plugin deactivation:
		 *
		 */
		public function clear_cron_job() {
			wp_clear_scheduled_hook( 'opalmembership_check_subscription_status' );
		}

		/**
		 * get Address Model Object
		 *
		 * @return A Instance of Opalmembership_Address
		 */
		public function address() {
			return Opalmembership_Address::getInstance();
		}

		/**
		 * get Payment Model Object
		 *
		 * @return A Instance of Opalmembership_Payment_Gateways
		 */
		public function gateways() {
			return Opalmembership_Payment_gateways::getInstance();
		}


		public function checkout() {
			return Opalmembership_Checkout::getInstance();
		}


		public function session() {
			return $this->session;
		}


		public function clear_cart_session() {
			$sessions = [
				'opalmembership_purchase',
			];

			foreach ( $sessions as $session ) {
				$this->session->set( $session, null );
			}
		}

		/**
		 * clear payment session
		 */
		public function clear_payment_session() {
			$sessions = [
				'opalmembership_billing_address',
				'opalmembership_payment_method',
				'opalmembership_waiting_payment',
				'opalmembership_valid_checkout_info',
			];

			foreach ( $sessions as $session ) {
				$this->session->set( $session, null );
			}
		}


	}
}

if ( ! function_exists( 'OpalMembership' ) ) {

	function OpalMembership() {
		return OpalMembership::getInstance();
	}

	OpalMembership();

}
