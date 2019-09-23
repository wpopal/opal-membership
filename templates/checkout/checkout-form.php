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

opalmembership_print_notices();
$purchase = opalmembership_get_purchase_session();

do_action( 'opalmembership_before_checkout_form', $checkout );

// If checkout registration is disabled and not logged in, the user cannot checkout
if ( ! $checkout->enable_signup && ! $checkout->enable_guest_checkout && ! is_user_logged_in() ) {
	echo apply_filters( 'opalmembership_checkout_must_be_logged_in_message', esc_html__( 'You must be logged in to checkout.', 'opalmembership' ) );

	return;
}

$get_checkout_url = opalmembership_get_checkout_page_uri( [ 'action' => 'proccess_checkout' ] ); ?>


<div class="opalmembership-box">
    <div class="panel-body">
        <!-- CHECKOUT FORM -->
        <form id="opalmembership-checkout-form" name="checkout" method="post" class="checkout" action="<?php echo esc_url( $get_checkout_url ); ?>" enctype="multipart/form-data">

			<?php do_action( 'opalmembership_checkout_form_top' ); ?>

            <div class="row">
                <div class="col-lg-6">
					<?php if ( sizeof( $checkout->billing_fields ) > 0 ) : ?>
                        <div class="opalmembership-billing-fields">

							<?php do_action( 'opalmembership_checkout_billing', $checkout ); ?>

                        </div>
					<?php endif; ?>
                </div>
				<?php if ( isset( $purchase['cart']['price'] ) && $purchase['cart']['price'] > 0 ) : ?>
                    <div class="col-lg-6">

						<?php
						/**
						 * @load payment gateways
						 */
						do_action( 'opalmembership_checkout_payment_gateways' );
						?>
                    </div>
                <?php else : ?>
                    <input type="hidden" name="payment_method" value="cod">
				<?php endif; ?>

                <div class="col-lg-12">
                    <input type="submit" class="button-submit btn btn-primary" id="btn-opalmembership-checkout-button" name="opalmembership-button-purchase" value="<?php esc_html_e( 'Complete Now', 'opalmembership' ); ?>"/>
                </div>
            </div>

			<?php do_action( 'opalmembership_checkout_form_bottom' ); ?>
        </form>
    </div>
</div>


<?php do_action( 'opalmembership_after_checkout_form', $checkout ); ?>
