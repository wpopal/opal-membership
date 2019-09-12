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

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}


class Opalmembership_Abstract_Gateway{

	/**
	 * @var String $title
	 */
	public $title;

	/**
	 * @var String $title
	 */
	public $description;

	/**
	 * @var String $title
	 */
	public $icon;

	/**
	 * @var String $title
	 */
	public $testmode = false;

	/**
	 * @var String $title
	 */
	protected $log;

	/**
	 * @var String $paypal_uri
	 */
	protected $paypal_uri;

	/**
	 * @var Boolean $iscurrent
	 */
	public $iscurrent = false;

	/**
	 * @var array $field
	 */
	public $fields = array();

	/**
	 * @var Integer $debug mode
	 */
	protected $debug = false;

	/**
	 * Process payment and automatic set payment status, order status
	 */
	public function process( $payment_id, $posted ){}

	/**
	 *
	 */
	public function confirm(){}

	public function process_refund(){}

	public function can_refund_payment( $payment ) {
		return $payment && $payment->get_transaction_id();
	}

	/**
	 *
	 */
	public function setting_form(){}

	/**
	 * Get admin setting fields
	 */
	public function admin_fields( $fields ){
		return array();
	}

	public function set_debug( $debug ){
		$this->debug = $debug;
	}

	/**
	 * Render Payment Fields If needed
	 */
	public function form() { return false; }

	/**
	 * Validate payment info
	 * @return empty array if successfully
	 */
	public function validate(){  return array(); }
}
