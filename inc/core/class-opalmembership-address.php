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
 * @Class Opalmembership_Address
 *
 * @version 1.0
 */
class Opalmembership_Address{


	/**
	 * Get a instance of this
	 */
	public static function getInstance(){
		static $_instance;
		if( !$_instance ){
			$_instance = new self();
		}
		return $_instance;
	}

	/**
	 *
	 */
	public function validate( $fields, $data ){
		$message = array();

		foreach( $fields as $key  => $value ){
			if( isset($value['required']) && $value['required'] ){
				if( empty($data[$key]) || !isset($data[$key]) ){
					$message[] = array( 'field' => $key, 'message' => $value['message'] );
				}else if( isset($value['sanitize_callback'] ) ){
				 	if( ! call_user_func( $value['sanitize_callback'], $data[$key] ) ) {
				 		$message[] = array( 'field' => $key, 'message' => $value['message'] );
				 	}
				}
			}
		}

		return $message;
	}

	public function _check_email( $value ){

		return is_email( $value )  ;

	}
	/**
	 *
	 */
	public function get_default_fields(){
		$fields = array(

			'email'         => array(
				'type'		=> 'email',
				'label'    => esc_html__( 'Email', 'opalmembership' ),
				'required' => true,
				'class'    => array( 'form-row-first' ),
				'message' => esc_html__('Please enter your email', 'opalmembership'),
				'sanitize_callback'=> array($this,'_check_email')
			),

			'first_name'         => array(
				'label'    => esc_html__( 'First Name', 'opalmembership' ),
				'required' => true,
				'class'    => array( 'form-row-first' ),
				'message' => esc_html__('Please enter your first name', 'opalmembership')
			),
			'last_name'          => array(
				'label'    => esc_html__( 'Last Name', 'opalmembership' ),
				'required' => true,
				'class'    => array( 'form-row-last' ),
				'clear'    => true,
				'message' => esc_html__('Please enter your last name', 'opalmembership')
			),
			'company'            => array(
				'label' => esc_html__( 'Company Name', 'opalmembership' ),
				'class' => array( 'form-row-wide' ),
				'message' => esc_html__('Please enter your company', 'opalmembership')
			),
			'address_1'          => array(
				'label'       => esc_html__( 'Address', 'opalmembership' ),
				'placeholder' => _x( 'Street address', 'placeholder', 'opalmembership' ),
				'required'    => true,
				'class'       => array( 'form-row-wide', 'address-field' ),
				'message' => esc_html__('Please enter your address 1', 'opalmembership')
			),
			'address_2'          => array(
				'label'       => esc_html__( 'Address 2', 'opalmembership' ),
				'placeholder' => _x( 'Apartment, suite, unit etc. (optional)', 'placeholder', 'opalmembership' ),
				'class'       => array( 'form-row-wide', 'address-field' ),
				'required'    => false,
				'message' => esc_html__('Please enter your address 2', 'opalmembership')
			),
			'city'               => array(
				'label'       => esc_html__( 'Town / City', 'opalmembership' ),
				'placeholder' => esc_html__( 'Town / City', 'opalmembership' ),
				'required'    => true,
				'class'       => array( 'form-row-wide', 'address-field' ),
				'message' => esc_html__('Please enter your Town/ City', 'opalmembership')
			),
			'state'              => array(
				'type'        => 'state',
				'label'       => esc_html__( 'State / County', 'opalmembership' ),
				'placeholder' => esc_html__( 'State / County', 'opalmembership' ),
				'required'    => false,
				'class'       => array( 'form-row-first', 'address-field' ),
				'validate'    => array( 'state' ),
				'message' => esc_html__('Please enter your State', 'opalmembership')
			),
			'postcode'           => array(
				'label'       => esc_html__( 'Postcode / Zip', 'opalmembership' ),
				'placeholder' => esc_html__( 'Postcode / Zip', 'opalmembership' ),
				'required'    => true,
				'class'       => array( 'form-row-last', 'address-field' ),
				'clear'       => true,
				'validate'    => array( 'postcode' ),
				'message' => esc_html__('Please enter your Postcode', 'opalmembership')
			),
			'country'            => array(
				'type'     => 'country',
				'label'    => esc_html__( 'Country', 'opalmembership' ),
				'required' => true,
				'class'    => array( 'form-row-wide', 'address-field', 'update_totals_on_change' ),
				'message' => esc_html__('Please enter your Country', 'opalmembership')
			),

		);

		return apply_filters( 'opalmembership_default_address_fields', $fields );
	}

	/**
	 *
	 */
	public function get_fields( $data = array() ) {
		$output = array();

		if( ! is_array( $data ) ){
			$data = array();
		}

		$default_country = 'US';


		$output = $this->get_default_fields();

		return $output;
	}
}

?>