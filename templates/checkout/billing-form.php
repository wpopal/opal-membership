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
if ( !defined( 'ABSPATH' ) ) exit;
?>
<div class="billing-fields">
	<h3><?php esc_html_e( 'Address Details', 'opalmembership' ); ?></h3>
	<?php do_action( 'opal_cf_before_checkout_billing_form', $checkout ); ?>
	<?php foreach ( $fields as $key => $field ) : ?>
		<?php opalmembership_form_field( 'billing['.$key.']', $field,  $checkout->get_billing_field_val($key) ); ?>
	<?php endforeach; ?>
	<?php do_action('opal_cf_after_checkout_billing_form', $checkout ); ?>
</div>