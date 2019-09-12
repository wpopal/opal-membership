<div class="payment-detail">

	<?php if( $payment ): ?>
	<?php
		$fields = OpalMembership()->address()->get_fields( );
		$packages = $payment->get_cartitems();
	 	$coupons = $payment->get_coupons();

	 // 	echo '<pre>'.print_r($packages, 1 );die;
	 	
	?>
		
			<h2><?php esc_html_e( 'Invoice ID' ); ?>: <?php echo $payment->get_payment_number(); ?></h2>
			<hr>
			<div class="opal-row">
				<div class="membership-payment-info col-sm-6">
						<div class="opalmembership-box">
							<h5 class="box-heading"><?php esc_html_e( 'Information', 'opalmembership' ); ?></h5>
							 <ul class="list-group">
							 	 <li class="list-group-item">
									<span class="text-label"><?php esc_html_e( 'Date Purchase', 'opalmembership' ); ?>:</span><span> <?php echo $payment->created(); ?> </span>
								</li>
								 
								 <li class="list-group-item">
									<span class="text-label"><?php esc_html_e( 'Gateway', 'opalmembership' ); ?>:</span>
									<span class="text-primary"> <?php echo opalmembership_get_gateways_by_key( $payment->get_gateway() ); ?> </span>
								</li>

								<li class="list-group-item">
									<span class="text-label"><?php esc_html_e( 'Status', 'opalmembership' ); ?>:</span>
									<span class="text-primary"> <?php echo opalmembership_get_payment_status_name($payment->get_status() ); ?>  </span>
								</li>
							

								 <li class="list-group-item">
									<span><?php esc_html_e( 'Key', 'opalmembership' ); ?>:</span><span><?php echo $payment->get_meta( 'purchase_key' ); ?></span>
								</li>
								<?php if( $payment->get_meta( 'transaction_id' ) ) {  ?>
								 <li class="list-group-item">
									<span><?php esc_html_e( 'Transaction ID', 'opalmembership' ); ?>:</span><span><?php echo $payment->get_meta( 'transaction_id' ); ?></span>
								</li>
								<?php } ?>
								 <li class="list-group-item">
									<span><?php esc_html_e( 'IP Client', 'opalmembership' ); ?></span><span><?php echo $payment->get_meta( 'user_ip' ); ?></span>
								</li>
							</ul>
						</div> 		
					</div>

					<div class="membership-payment-address col-sm-6">
						<div class="opalmembership-box">
							<h5 class="box-heading"><?php esc_html_e( 'Billing Address', 'opalmembership' ); ?></h5>
							 <ul class="list-group">
								 <?php foreach ( $fields as $key => $field ) : ?>
								 	<?php if( isset($payment->billing[$key]) && isset($field['label']) && $payment->billing[$key] ): ?> 
									 <li class="list-group-item">
									 	<span class="text-label"><?php echo $field['label']; ?> : </span>
									 	<span> <?php echo $payment->billing[$key]; ?> </span>
									 	</li>
									<?php endif; ?>
								<?php endforeach; ?>
							</ul>
						</div> 	
					</div>
		 			
			</div>	
		

		<div class="opalmembership-box">
			<div class="box-body">
				<h5><?php esc_html_e( 'Packages' ); ?></h5>
				<div class="table-invoice-payment">
				  <table  class="table table-bordered">
				   		<thead> 
				   			<tr> 
				   				<th><?php esc_html_e( 'Package Name' ,'opalmembership'); ?></th> 
				   				<th><?php esc_html_e( 'Total' ,'opalmembership'); ?></th> 
				   			</tr> 
				   		
				   		</thead>
				   		<tbody>
				   			<?php foreach( $packages as $package ): ?>
				   			<tr> 
				  				<td><?php echo opalmembership_get_package_name($package['package_id']); ?></td> 

				  				<td><?php echo opalmembership_price_format( $package['price'] ); ?></td> 
				  			 
				  			</tr> 
				  			<?php endforeach; ?>
				 

							<?php if( !empty($coupons) ) { ?>
							<?php foreach(  $coupons as $coupon ) { ?>
								<tr>
									<td class="text-right"><strong><?php esc_html_e( 'Coupon' ); ?> <span>( <?php echo $coupon['code']; ?> )</span></strong></td>
									<td><?php if( $coupon['type'] == 'percenatage' ){ ?>
										-<?php  echo opalmembership_price_format(  ($package['price']/$coupon['value']) * 10 );  ?>
									<?php }else {  ?>
									<?php  echo opalmembership_price_format(  $coupon['value'] );  ?>
									<?php } ?>
									</td>
								</tr>
								<?php } ?>
							<?php } ?>


							<tr>
								<td class="text-right"><strong><?php esc_html_e( 'Total', 'opalmembership' ); ?></strong></td>
								<td><?php echo opalmembership_price_format( $payment->get_payment_total() );?></td>
							</tr>
				 		</tbody>
					</table>

				</div>   		
			</div>	
		</div>	


		<?php if( $payment->get_status()  != 'opal-completed' ): ?>
		<div class="opalmembership-box">
			<?php esc_html_e( 'The order is not completed, please make a payment' ); ?>
			<a class="btn btn-primary" href="#"><?php esc_html_e( 'Pay Now' )?></a>
		</div>
		<?php endif; ?>
	<?php else : ?>
	<?php esc_html_e( 'Could not find any payment', 'opalmembership' ); ?>	
	<?php endif; ?>
	<hr>
	<div>
		<a href="<?php echo esc_url( opalmembership_get_payment_history_page_uri() ) ; ?>" class="btn btn-primary"><?php esc_html_e( 'Back To List', 'opalmembership' ); ?></a>
	</div>	
</div>	