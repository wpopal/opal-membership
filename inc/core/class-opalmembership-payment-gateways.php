<?php
/**
 * $Desc$
 *
 * @version    $Id$
 * @package    $package$
 * @author     Opal  Team <info@wpopal.com >
 * @copyright  Copyright (C) 2014 wpopal.com. All Rights Reserved.
 * @license    GNU/GPL v2 or later http://www.gnu.org/licenses/gpl-2.0.html
 *
 * @website  http://www.wpopal.com
 * @support  http://www.wpopal.com/support/forum.html
 */

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * @class   Opalmembership_Payment_Getways
 *
 * @version 1.0
 */
class Opalmembership_Payment_gateways {

	protected $gateways = [];

	protected $gateway;

	public function __construct() {

	}

	/**
	 * Get a Instance of this
	 */
	public static function getInstance() {
		static $_instance;
		if ( ! $_instance ) {
			$_instance = new self();
		}

		return $_instance;
	}

	/**
	 * Assign default payment gateway
	 */
	public function set_gateway( $gateway ) {
		$this->gateway = $gateway;
	}

	/**
	 *
	 */
	public function is_enable() {

	}

	/**
	 *
	 */
	public function get_getways() {

		$files = glob( OPALMEMBERSHIP_PLUGIN_DIR . 'inc/gateways/*.php' );

		$output = [];

		foreach ( $files as $file ) {

			$gateway = str_replace( ".php", "", basename( $file ) );

			require_once( $file );

			$class = "Opalmembership_Gateway_" . ucfirst( $gateway );

			if ( class_exists( $class ) ) {
				$object = new $class ();

				$output[ $gateway ] = [
					'admin_label'    => $object->title,
					'checkout_label' => $object->title,
					'supports'       => '',
				];
			}


		}

		return $output;
	}

	/**
	 * Get list payment gateways were enabled by admin
	 *
	 * @return Object collection of payment Opalmembership_Payment_gateways.
	 */
	public function get_list() {

		global $opalmembership_options;

		if ( isset( $opalmembership_options['gateways'] ) && ! empty( $opalmembership_options['gateways'] ) ) {
			$gateways = (array) $opalmembership_options['gateways'];
			if ( count( $this->gateways ) <= 0 ) {
				foreach ( $gateways as $gateway => $enable ) {
					if ( $enable ) {
						if ( file_exists( OPALMEMBERSHIP_PLUGIN_DIR . 'inc/gateways/' . $gateway . '.php' ) ) {
							require_once( OPALMEMBERSHIP_PLUGIN_DIR . 'inc/gateways/' . $gateway . '.php' );
							$class                      = "Opalmembership_Gateway_" . ucfirst( $gateway );
							$this->gateways[ $gateway ] = new $class ();
							if ( $this->gateway == $gateway ) {
								$this->gateways[ $gateway ]->iscurrent = true;
							}
						}
					}
				}
			}
		}

		return $this->gateways;
	}

	/**
	 *
	 */
	public function gateway( $gateway ) {
		$this->get_list();

		return isset( $this->gateways[ $gateway ] ) ? $this->gateways[ $gateway ] : null;
	}
}
