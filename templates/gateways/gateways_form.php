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

?>

<div class="opalmembership-gateways opal-panel">
		<div class="opal-panel-heading">
			<h3><?php esc_html_e( 'Payment Gateways', 'opalmembership' ); ?></h3>
		</div>

		<div class="opal-panel-body">
			<?php if( isset($gateways) && count($gateways) ) : ?>

				<?php foreach( $gateways as  $key => $gateway ) : ?>

				 	<div class="opal-gateway-panel gateway-panel-<?php echo $key; ?>">
				 		<div class="opal-gateway-panel-heading">
				 			<label for="payment_method_<?php echo $key; ?>">
				 				<input type="radio" <?php checked( $gateway->iscurrent, true ); ?> value="<?php echo $key; ?>" name="payment_method" class="input-radio" id="payment_method_<?php echo $key; ?>">
				 			<?php echo esc_html( $gateway->title ); ?>
				 			</label>
				 		</div>
				 		<?php if( $gateway->description ) { ?>
				 		<div class="gateway-description<?php echo $gateway->iscurrent ? ' active' : '' ?>">
				 			<?php printf( '%s', $gateway->description ); ?>
				 		</div>
				 		<?php } ?>
				 		<?php if( $form = $gateway->form() ) { ?>
							<div class="gateway-form opal-gateway-<?php echo $key; ?><?php echo $gateway->iscurrent ? ' active' : '' ?>">
								<?php echo sprintf( '%s', $form ); ?>
							</div>
						<?php } ?>
					</div>

				<?php endforeach; ?>

			<?php else : ?>

				<div class="opal-payment-msg no-gateway">
					<?php esc_html_e( 'No Payment available', 'opalmembership' ); ?>
				</div>

			<?php endif; ?>
		</div>
</div>
