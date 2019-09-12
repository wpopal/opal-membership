<?php 
 
	$orderedpackages = $thepayment->get_cartitems(); 
 	$coupons = $thepayment->get_coupons();

?>
<table style="width:100%;text-align:left;">
	<thead>
	<tr>
		<th><?php esc_html_e( 'Package', 'opalmembership' ); ?></th>
		<th><?php esc_html_e( 'Package Name', 'opalmembership' ); ?></th>
		<th><?php esc_html_e( 'Total', 'opalmembership' ); ?></th>
	</tr>
	</thead>
	<tbody>
		<?php foreach( $orderedpackages as $package ): ?>
		<tr>
			<td>
				 <?php echo '#'.$package['package_id']; ?>
			</td>
			<td>
				<a target="_blank" href="<?php echo get_edit_post_link( $package['package_id'] ); ?>"> <?php echo $package['package_title']; ?> </a>
			</td>
			<td><?php echo opalmembership_price_format( $package['price'] ); ?></td>
		</tr>
		<?php endforeach; ?>
	</tbody>
</table>
<table style="width:50%;text-align:left; float:right">

	<?php if( !empty($coupons) ) { ?>
	<?php foreach(  $coupons as $coupon ) { ?>
		<tr>
			<td><?php esc_html_e( 'Coupon', 'opalmembership' ); ?> <span>( <?php echo $coupon['code']; ?> )</span></td>
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
		<td><?php esc_html_e( 'Total', 'opalmembership' ); ?></td> 
		<td><?php echo opalmembership_price_format( $package['total'] );?></td> 
	</tr>	
</table>
<div class="clear clearfix"></div>
