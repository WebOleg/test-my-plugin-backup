<?php
/**
 * Woocommerce BNA Gateway
 *
 * @author 	BNA
 * @category 	'BNA subscription' Template
 * @version     1.0
 */

if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

$icon_pause = '<svg viewBox="64 64 896 896" width="22px" height="22px" fill="var(--bna-button-background-color)" aria-hidden="true"><path d="M512 64C264.6 64 64 264.6 64 512s200.6 448 448 448 448-200.6 448-448S759.4 64 512 64zm0 820c-205.4 0-372-166.6-372-372s166.6-372 372-372 372 166.6 372 372-166.6 372-372 372zm-88-532h-48c-4.4 0-8 3.6-8 8v304c0 4.4 3.6 8 8 8h48c4.4 0 8-3.6 8-8V360c0-4.4-3.6-8-8-8zm224 0h-48c-4.4 0-8 3.6-8 8v304c0 4.4 3.6 8 8 8h48c4.4 0 8-3.6 8-8V360c0-4.4-3.6-8-8-8z"></path></svg>';
$icon_play = '<svg viewBox="64 64 896 896" width="22px" height="22px" fill="var(--bna-button-background-color)" aria-hidden="true"><path d="M512 64C264.6 64 64 264.6 64 512s200.6 448 448 448 448-200.6 448-448S759.4 64 512 64zm0 820c-205.4 0-372-166.6-372-372s166.6-372 372-372 372 166.6 372 372-166.6 372-372 372z"></path><path d="M719.4 499.1l-296.1-215A15.9 15.9 0 00398 297v430c0 13.1 14.8 20.5 25.3 12.9l296.1-215a15.9 15.9 0 000-25.8zm-257.6 134V390.9L628.5 512 461.8 633.1z"></path></svg>';
$icon_trash = '<svg role="img" xmlns="http://www.w3.org/2000/svg" height="22px" viewBox="0 0 512 512" width="22px"><path fill="red" d="M432 32H312l-9.4-18.7A24 24 0 0 0 281.1 0H166.8a23.72 23.72 0 0 0-21.4 13.3L136 32H16A16 16 0 0 0 0 48v32a16 16 0 0 0 16 16h416a16 16 0 0 0 16-16V48a16 16 0 0 0-16-16zM53.2 467a48 48 0 0 0 47.9 45h245.8a48 48 0 0 0 47.9-45L416 128H32z"></path></svg>';
$icon_play_deleted = '<svg viewBox="64 64 896 896" width="22px" height="22px" fill="#cccccc" aria-hidden="true"><path d="M512 64C264.6 64 64 264.6 64 512s200.6 448 448 448 448-200.6 448-448S759.4 64 512 64zm0 820c-205.4 0-372-166.6-372-372s166.6-372 372-372 372 166.6 372 372-166.6 372-372 372z"></path><path d="M719.4 499.1l-296.1-215A15.9 15.9 0 00398 297v430c0 13.1 14.8 20.5 25.3 12.9l296.1-215a15.9 15.9 0 000-25.8zm-257.6 134V390.9L628.5 512 461.8 633.1z"></path></svg>';
$icon_trash_deleted = '<svg role="img" xmlns="http://www.w3.org/2000/svg" height="22px" viewBox="0 0 512 512" width="22px"><path fill="#cccccc" d="M432 32H312l-9.4-18.7A24 24 0 0 0 281.1 0H166.8a23.72 23.72 0 0 0-21.4 13.3L136 32H16A16 16 0 0 0 0 48v32a16 16 0 0 0 16 16h416a16 16 0 0 0 16-16V48a16 16 0 0 0-16-16zM53.2 467a48 48 0 0 0 47.9 45h245.8a48 48 0 0 0 47.9-45L416 128H32z"></path></svg>';
?>

<section class="section my-account-orders">
	<div>
		<?php $bna_recurring_payments_url = esc_url( wc_get_account_endpoint_url( 'bna-recurring-payments' ) ); ?>
		<div class="woocommerce-orders-table__filters">
				<a href="<?php echo $bna_recurring_payments_url . '?bna-orders-filter=last-week'; ?>" class="woocommerce-orders-table__filter <?php echo bna_add_class_active( 'last-week' ); ?>">
				<?php _e( 'Last Week', 'wc-bna-gateway' ); ?>
			</a>
			<a href="<?php echo $bna_recurring_payments_url . '?bna-orders-filter=last-month'; ?>" class="woocommerce-orders-table__filter <?php echo bna_add_class_active( 'last-month' ); ?>">
				<?php _e( 'Last Month', 'wc-bna-gateway' ); ?>
			</a>
			<a href="<?php echo $bna_recurring_payments_url . '?bna-orders-filter=last-three-months'; ?>" class="woocommerce-orders-table__filter <?php echo bna_add_class_active( 'last-three-months' ); ?>">
				<?php _e( 'Last 3 Months', 'wc-bna-gateway' ); ?>
			</a>
			<a href="<?php echo $bna_recurring_payments_url . '?bna-orders-filter=last-year'; ?>" class="woocommerce-orders-table__filter <?php echo bna_add_class_active( 'last-year' ); ?>">
				<?php _e( 'Last Year', 'wc-bna-gateway' ); ?>
			</a>
		</div>
	</div>
	
	<table class="woocommerce-orders-table woocommerce-MyAccount-orders shop_table shop_table_responsive my_account_orders account-orders-table">
		<thead>
			<tr>		
				<th class="woocommerce-orders-table__header"><span class="nobr"><?php _e( 'Base Order', 'wc-bna-gateway' ); ?></span></th>
				<th class="woocommerce-orders-table__header"><span class="nobr"><?php _e( 'Recurring', 'wc-bna-gateway' ); ?></span></th>
				<th class="woocommerce-orders-table__header"><span class="nobr"><?php _e( 'Status', 'wc-bna-gateway' ); ?></span></th>
				<th class="woocommerce-orders-table__header"><span class="nobr"><?php _e( 'Start', 'wc-bna-gateway' ); ?></span></th>
				<th class="woocommerce-orders-table__header"><span class="nobr"><?php _e( 'Number of payments', 'wc-bna-gateway' ); ?></span></th>
				<th class="woocommerce-orders-table__header"><span class="nobr"><?php _e( 'Desc.', 'wc-bna-gateway' ); ?></span></th>
				<th class="woocommerce-orders-table__header"><span class="nobr"><?php _e( 'Manage', 'wc-bna-gateway' ); ?></span></th>
			</tr>
		</thead>
		<tbody>
			<?php                  
			foreach ( $subscriptions as $s_val ) {
				$desc = json_decode( $s_val->recurringDescription );
				$status_color = 'bna-status-' . strtolower( $s_val->status );
				?>
				<tr class="woocommerce-orders-table__row">
					<td class="woocommerce-orders-table__cell " data-title="<?php _e( 'Base Order', 'wc-bna-gateway' ); ?>">
						<a class="bna-orders-order-link" href="/my-account/view-order/<?php echo $desc->metadata->invoiceId; ?>/">
							<?php echo '#' . $desc->metadata->invoiceId; ?>
						</a>
					</td>
					<td class="woocommerce-orders-table__cell " data-title="<?php _e( 'Recurring', 'wc-bna-gateway' ); ?>">
						<?php echo $s_val->recurring; ?>
					</td>
					<td class="woocommerce-orders-table__cell <?php echo $status_color; ?>" data-title="<?php _e( 'Status', 'wc-bna-gateway' ); ?>">
						<?php echo $s_val->status; ?>
					</td>
					<td class="woocommerce-orders-table__cell " data-title="<?php _e( 'Start', 'wc-bna-gateway' ); ?>">
						<?php echo date( 'Y-m-d', strtotime( $s_val->startDate ) ); ?>
					</td>
					<td class="woocommerce-orders-table__cell " data-title="<?php _e( 'Number of payments', 'wc-bna-gateway' ); ?>">
						<?php echo $s_val->numberOfPayments; ?>
					</td>
					<td class="woocommerce-orders-table__cell " data-title="<?php _e( 'Desc.', 'wc-bna-gateway' ); ?>">
						<h6 class="bna-details-title"></h6>
						<div class="bna-details">
							<p><?php _e( 'ID:', 'wc-bna-gateway' ); ?> <?php echo $s_val->recurringId; ?></p>
							<p><?php _e( 'Created:', 'wc-bna-gateway' ); ?> <?php echo date( 'Y-m-d H:i:s', strtotime( $s_val->created_time ) ); ?></p>
							<p><?php _e( 'Status:', 'wc-bna-gateway' ); ?> <?php echo $s_val->status; ?></p>
							<p><?php _e( 'Currency:', 'wc-bna-gateway' ); ?> <?php echo $desc->currency; ?></p>
							<?php
								if ( isset( $desc->total ) )
									echo "<p>" . __( 'Total amount:', 'wc-bna-gateway' ) . " {$desc->total}</p>";

								if ( isset( $desc->subtotal ) )
									echo "<p>" . __( 'Subtotal:', 'wc-bna-gateway' ) . " {$desc->subtotal}</p>";
									
								if ( isset( $desc->amount ) )
									echo "<p>" . __( 'Refunded:', 'wc-bna-gateway' ) . " {$desc->amount}</p>";
							?>
							<p><?php _e( 'BNA fee:', 'wc-bna-gateway' ); ?> <?php echo ! empty( $desc->fee ) ? $desc->fee : 'No'; ?></p>                                          
							<p><?php _e( 'Payment Method:', 'wc-bna-gateway' ); ?> <?php echo $desc->paymentMethod; ?></p>
							<?php
								switch ( $desc->paymentMethod ) {
									case 'CARD':
										echo "<p>" . ucfirst( $desc->paymentDetails->cardBrand ) . ':  ' . " {$desc->paymentDetails->cardNumber}</p>";
										break;
									case 'EFT':
										echo "<p>" . __( 'Account #:', 'wc-bna-gateway' ) . " {$desc->paymentDetails->accountNumber}</p>";
										echo "<p>" . __( 'Transit #:', 'wc-bna-gateway' ) . " {$desc->paymentDetails->transitNumber}</p>";
										echo "<p>" . __( 'Institution #:', 'wc-bna-gateway' ) . " {$desc->paymentDetails->bankNumber}</p>";
										break;
									case 'E-TRANSFER':
										echo "<p>" . __( 'Email:', 'wc-bna-gateway' ) . " {$desc->paymentDetails->emailAddress}</p>";
										break;
								} 
							?>
						</div>
					</td>
					<td class="woocommerce-orders-table__cell" data-title="<?php _e( 'Manage', 'wc-bna-gateway' ); ?>">
						<?php if ( $s_val->status === 'NEW' || $s_val->status === 'ACTIVE' ) { ?>
							<button type="button" class="btn-suspend-subscription" data-id="<?php echo $s_val->id; ?>" data-suspend="yes" 
								data-order-id="<?php echo $desc->metadata->invoiceId; ?>"
								data-order-question="<?php _e( 'Do you want to suspend the subscription', 'wc-bna-gateway' ) ?>" >
								<?php echo $icon_pause; ?>
							</button>
						<?php } ?>
						<?php if ( $s_val->status === 'SUSPENDED' ) { ?>
							<button type="button" class="btn-suspend-subscription" data-id="<?php echo $s_val->id; ?>" data-suspend="no" 
								data-order-id="<?php echo $desc->metadata->invoiceId; ?>"
								data-order-question="<?php _e( 'Do you want to run the subscription', 'wc-bna-gateway' ) ?>" >
								<?php echo $icon_play; ?>
							</button>
						<?php } ?>
						<?php if ( $s_val->status !== 'DELETED' ) { ?>
							<button type="button" class="btn-del-subscription" data-id="<?php echo $s_val->id; ?>" 
								data-order-id="<?php echo $desc->metadata->invoiceId; ?>"
								data-order-question="<?php _e( 'Do you want to delete the subscription', 'wc-bna-gateway' ) ?>" >
								<?php echo $icon_trash; ?>
							</button>
						<?php } ?>
						<?php if ( $s_val->status === 'DELETED' ) { ?>
							<button type="button" class="btn-subscription-deleted" >
								<?php echo $icon_play_deleted; ?>
							</button>
							<button type="button" class="btn-subscription-deleted" >
								<?php echo $icon_trash_deleted; ?>
							</button>
						<?php } ?>
					</td>
				</tr>
			<?php
            }                           
            ?>	
		</tbody>
	</table>
	
</section>

<div class="loading"></div>

<div id="confirm-wrapper">
	<div id="confirm-box">
		<h2 id="confirm-header"><?php _e( 'Are you sure?', 'wc-bna-gateway' ) ?></h2>
		<div id="confirm-buttons">
			<button id="confirm-ok"><?php _e( 'OK', 'wc-bna-gateway' ) ?></button>
			<button type="button" id="confirm-cancel"><?php _e( 'Cancel', 'wc-bna-gateway' ) ?></button>
		</div>
	</div>
</div>

<script>
jQuery("body").on('click', 'h6.bna-details-title:not(.open)', function(e){
	jQuery('div.bna-details').hide();
	jQuery('h6.bna-details-title.open').removeClass('open');
	
	jQuery(this).addClass('open');
	jQuery(this).next().show();
		
	var fixed_offset = 70;
	let position = jQuery(this).parent().offset();
	jQuery('html,body').stop().animate({ scrollTop: position.top - fixed_offset }, 1000);
	e.preventDefault();
});
	
jQuery("body").on('click', 'h6.bna-details-title.open', function(e){
	jQuery(this).removeClass('open');
	jQuery(this).next().hide();
	setTimeout(()=>{
		var fixed_offset = 70;
		let position = jQuery(this).parent().offset();	
		jQuery('html,body').stop().animate({ scrollTop: position.top - fixed_offset }, 1000);
	}, 100);	
	e.preventDefault();
});
</script>
