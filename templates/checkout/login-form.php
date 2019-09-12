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
if ( ! defined( 'ABSPATH' ) ) exit;

$message = apply_filters( 'opalmembership_checkout_warning_login_before', esc_html__( 'You must be logged in to process checkout.', 'opalmembership' ) );
$message .= ' ' . sprintf( '<a href="#" class="opalmembership-toggle-login"><strong>%s</strong></a>', esc_html__( 'Login', 'opalmembership' ) );
opalmembership_print_notice( 'warning', $message );

?>

<div class="opalmembership-login-form-toggle opalmembership-hidden in">

	<?php
		/**
		 * print login form section
		 */
		opalmembership_print_login_form( array( 'hide_title' => true ) );
	?>

</div>
