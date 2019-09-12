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

$cart  = $purchase_info['cart'];
$get_checkout_url = opalmembership_get_checkout_page_uri( array('action' => 'proccess_checkout') ); ?>

<div class="opalmembership-box">
	<div class="panel-body">
		<div class="opalmembership-shoppingcart">
			<table class="table table-responsive">
				<caption> <?php esc_html_e( 'Shopping Cart', 'opalmembership' ); ?></caption>
				<thead>
					<tr>
						<th><?php esc_html_e( 'Package', 'opalmembership' ); ?></th>
						<th><?php esc_html_e( 'Price', 'opalmembership' ); ?></th>
					</tr>
				</thead>
				<tbody>
					<tr>
						<td><?php echo $cart['package_title'];?></td>
						<td><?php echo opalmembership_price_format( $cart['price'] );?></td>
					</tr>
				</tbody>
			</table>
			<div class="row">
				<div class="col-lg-6">
					<form class="form-inline" action="" id="opalmembership-coupon-form" action="<?php echo esc_url( $get_checkout_url ); ?>" enctype="multipart/form-data">

						  <div class="form-group">
						    <label for="exampleInputEmail2"><?php esc_html_e( 'Have any coupon?', 'opalmembership' ); ?></label>
						    <input type="input" class="form-control" name="coupon_code" id="opalmembership-coupon-code-input" placeholder="<?php esc_html_e( 'Enter your coupon code', 'opalmembership' ); ?>">
						  </div>

						  <button type="submit" class="btn button-primary btn-primary"><?php esc_html_e( 'Apply', 'opalmembership' ); ?></button>
					</form>
				</div>
				<div class="col-lg-6">
					<table class="table table-responsive">
						<tbody>
							<?php if( isset( $purchase_info['coupons'] ) ) : ?>
								<?php foreach( $purchase_info['coupons'] as $coupon ) : ?>
									<tr>
										<td><?php esc_html_e( 'Coupon', 'opalmembership' ); ?> <span>( <?php echo $coupon['code']; ?> )</span></td>
										<td><?php if( $coupon['type'] == 'percenatage' ){ ?>
											-<?php echo opalmembership_price_format( ( $cart['price']* $coupon['value'] ) / 100 );  ?>
										<?php }else {  ?>
										-<?php echo opalmembership_price_format(  $coupon['value'] );  ?>
										<?php } ?>
										<a href="#" class="opalmembership-remove-coupon" data-code="<?php echo $coupon['code']; ?>"> <i class="fa fa-times text-danger"></i> </a>
										</td>
									</tr>
								<?php endforeach; ?>
							<?php endif; ?>
							<tr>
								<td><?php esc_html_e( 'Total', 'opalmembership' ); ?></td>
								<td><?php echo opalmembership_price_format( $cart['total'] );?></td>
							</tr>
						</tbody>
					</table>
				</div>
			</div>
		</div>
	</div>
</div>
