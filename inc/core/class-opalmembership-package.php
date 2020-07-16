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

class Opalmembership_Package {

	public $id;

    public $name;

    public $description;

    public $status;

    public $duration;

    public $duration_unit;

	protected $active;
	/**
	 *
	 */
	protected $price;

	/**
	 *
	 */
	protected $saleprice;


	public function __construct( $_id = false, $_args = array()  ){

		if ( false === $_id && false ) {

			$defaults = array(
				'post_type'   => 'membership_packages',
				'post_status' => 'draft',
				'post_title'  => esc_html__( 'New Membership Form', 'opalmembership' )
			);

			$args = wp_parse_args( $_args, $defaults );

			$_id = wp_insert_post( $args, true );

		}

		$membership = WP_Post::get_instance( $_id );

		if ( ! is_object( $membership ) ) {
			return false;
		}

		if ( ! is_a( $membership, 'WP_Post' ) ) {
			return false;
		}

		if ( 'membership_packages' !== $membership->post_type ) {
			return false;
		}

		$this->id = $membership->ID;
		foreach ( $membership as $key => $value ) {
			$this->$key = $value;
		}

	}

	/**
	 * Magic __get function to dispatch a call to retrieve a private property
	 *
	 * @since 1.0
	 */
	public function __get( $key ) {

		if ( method_exists( $this, 'get_' . $key ) ) {
			return call_user_func( array( $this, 'get_' . $key ) );
		} else if ( metadata_exists( 'post', $this->get_ID(), OPALMEMBERSHIP_PACKAGES_PREFIX . $key ) ) {
			return get_post_meta( $this->get_ID(), OPALMEMBERSHIP_PACKAGES_PREFIX . $key, true );
		}

	}

	public function is_hightlighted(){
		return get_post_meta( $this->get_ID(), OPALMEMBERSHIP_PACKAGES_PREFIX . 'hightlighted', true );
	}
	/**
	 * Retrieve the ID
	 *
	 * @since 1.0
	 * @return int
	 */
	public function get_ID() {

		return $this->id;

	}

	/**
	 * Retrieve the variable prices
	 *
	 * @since 1.0
	 * @return array
	 */
	public function get_price(){
		if ( ! isset( $this->price) ) {

			$this->price = get_post_meta( $this->id, OPALMEMBERSHIP_PACKAGES_PREFIX.'price', true );

		}
		if ( ! isset( $this->saleprice) ) {

			$this->saleprice = get_post_meta( $this->id, OPALMEMBERSHIP_PACKAGES_PREFIX.'saleprice', true );

		}

		$this->price 	 = apply_filters( 'opalmembership_package_price', $this->price, $this->id );
		$this->saleprice = apply_filters( 'opalmembership_package_saleprice', $this->saleprice, $this->id );

		return $this->saleprice > 0 ? $this->saleprice : $this->price;
	}

	/**
	 * Retrieve the variable prices
	 *
	 * @since 1.0
	 * @return string
	 */
	public function get_price_html() {

		if ( ! isset( $this->price) ) {
			$this->price = get_post_meta( $this->id, OPALMEMBERSHIP_PACKAGES_PREFIX.'price', true );
		}

		if ( ! isset( $this->saleprice) ) {
			$this->saleprice = get_post_meta( $this->id, OPALMEMBERSHIP_PACKAGES_PREFIX.'saleprice', true );
		}

		$this->price 	 = apply_filters( 'opalmembership_package_price', $this->price, $this->id );
		$this->saleprice = apply_filters( 'opalmembership_package_saleprice', $this->saleprice, $this->id );

		if( $this->saleprice > 0 ){
			$html =  apply_filters( 'opalmembership_package_price_html', '<span class="plan-figure">'.opalmembership_price_format( $this->saleprice ).'<del><span class="membership-oldprice">'.opalmembership_price_format( $this->price ).'</del></span></span>' );
		} else {
			$html =  apply_filters( 'opalmembership_package_price_html', '<span class="plan-figure">'.opalmembership_price_format( $this->price ).'</span>' );
		}

		$html .= '/' . opalmembership_package_get_expiry_label( $this->duration_unit );
		return  $html;
	}

	/*
     * Check to see if subscription plan exists
     *
     */
    public function is_valid() {

       return $this->get_ID();

    }

    public function get_expiration_unit_time(){

    	if ( ! ( $duration = absint( $this->get_post_meta( 'duration' ) ) ) ) {
    		$duration = 1;
    	}

        $duration_unit = $this->get_post_meta( 'duration_unit' ); 

		switch ($duration_unit){

			case 'day':
				$seconds = 60*60*24;
				break;
			case 'week':
				$seconds = 60*60*24*7;
			break;
			case 'month':
				$seconds = 60*60*24*30;
				break;    
			case 'year':
				$seconds = 60*60*24*365;
				break;    
		}

		return $seconds * $duration;
    }

    /*
     * Method that returns the expiration date of the subscription plan
     *
     */
    public function get_expiration_date( $actived_time = false ) {
    	$expired_date  =  ($actived_time + $this->get_expiration_unit_time());
      ///   $expired_date  = date( 'Y-m-d H:i:s', $expired_date ); 
        return  $expired_date;
    }

    public function get_post_meta( $key ){
    	return  get_post_meta( $this->id, OPALMEMBERSHIP_PACKAGES_PREFIX. $key, true );
    }

}
