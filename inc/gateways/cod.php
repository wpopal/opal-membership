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

/**
 * @class   Opal_CF_Gateway_Cod
 *
 * @version 1.0
 */
class Opalmembership_Gateway_Cod extends Opalmembership_Abstract_Gateway {

	/**
	 * @var string $title payment tittle
	 */
	public $title;

	/**
	 * @var string $description is payment description
	 */
	public $description;

	/**
	 * $var string $icon
	 */
	public $icon;

	/**
	 * Constructor
	 */
	public function __construct() {
		$this->title       = esc_html__( 'Cash On Delivery', 'opalmembership' );
		$this->icon        = apply_filters( 'opalmembership_cod_icon', '' );
		$this->description = esc_html__( 'Have your customers pay with cash (or by other means) upon delivery', 'opalmembership' );
		add_filter( 'opalmembership_settings_gateways', [ $this, 'admin_fields' ] );
	}

	/**
	 * Get admin setting fields
	 */
	public function admin_fields( $fields ) {
		$code_field = apply_filters( 'opalmembership_settings_gateway_cod', [] );

		return array_merge( $fields, $code_field );
	}

	/**
	 * Process payment and automatic set payment status, order status
	 */
	public function process( $payment_id, $posted ) {

		$payment = new OpalMembership_Payment( $payment_id );
		$payment->update_status( 'processing', esc_html__( 'Payment to be made upon delivery.', 'opalmembership' ) );

		return true;
	}
}
