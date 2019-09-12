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

class Opalmembership_Protector {

	public function __construct(){
		if( !is_admin() ){
			add_action( 'int' , array( $this, 'check')  );
		}

		if( is_admin() ){
			$this->onRenderMetaboxes();
		}
	}

	public static function getInstance(){
		static $_instance;
		if( !$_instance ){
			$_instance = new Opalmembership_Protector();
		}
		return $_instance;
	}

	public function checkin(){

	}

	public function onRenderMetaboxes(){

	}
}

Opalmembership_Protector::getInstance();
?>