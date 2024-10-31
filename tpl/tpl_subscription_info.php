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
$icon_eye = '<svg fill="var(--bna-button-background-color)" version="1.1" width="32px" height="32px" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 512 512" >
<g><path d="M256,122.5C116.9,122.5,9.9,245.1,9.9,245.1L0,256l9.9,10.9c0,0,97.6,111.3,227.3,121.5c6.2,0.8,12.4,1,18.8,1
		c6.4,0,12.6-0.3,18.8-1c129.8-10.2,227.3-121.5,227.3-121.5L512,256l-9.9-10.9C502.1,245.1,395.1,122.5,256,122.5z M256,155.9
		c36.8,0,70.6,10,100.1,23.5c10.6,17.6,16.7,37.9,16.7,60c0,60.3-45.2,109.8-103.8,116.3h-1c-4,0.2-8,0.5-12,0.5
		c-4.4,0-8.7-0.3-13-0.5c-58.5-6.5-103.8-56-103.8-116.3c0-21.8,5.9-42,16.2-59.4h-0.5C184.6,166.2,218.9,155.9,256,155.9z
		 M256,189.3c-27.6,0-50.1,22.4-50.1,50.1s22.4,50.1,50.1,50.1s50.1-22.4,50.1-50.1S283.6,189.3,256,189.3z M110,204.9
		c-2.6,11.2-4.2,22.5-4.2,34.4c0,29.3,8.3,56.6,22.9,79.8c-42-24.3-71.2-53.3-80.8-63.1C56,247.8,78.3,226,110,204.9z M402,204.9
		c31.7,21.1,54,42.9,62,51.1c-9.6,9.8-38.8,38.8-80.8,63.1c14.6-23.1,22.9-50.5,22.9-79.8C406.2,227.4,404.6,216,402,204.9z"/>
</g></svg>';
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
				$status_color = 'bna-status-' . strtolower( esc_html( $s_val->status ) );
				$invoice_id = esc_html( $desc->metadata->invoiceId );
				?>
				<tr class="woocommerce-orders-table__row">
					<td class="woocommerce-orders-table__cell " data-title="<?php _e( 'Base Order', 'wc-bna-gateway' ); ?>">
						<a class="bna-orders-order-link" href="/my-account/view-order/<?php echo $invoice_id; ?>/">
							<?php echo '#' . $invoice_id; ?>
						</a>
					</td>
					<td class="woocommerce-orders-table__cell " data-title="<?php _e( 'Recurring', 'wc-bna-gateway' ); ?>">
						<?php echo esc_html( $s_val->recurring ); ?>
					</td>
					<td class="woocommerce-orders-table__cell <?php echo $status_color; ?>" data-title="<?php _e( 'Status', 'wc-bna-gateway' ); ?>">
						<?php echo esc_html( $s_val->status ); ?>
					</td>
					<td class="woocommerce-orders-table__cell " data-title="<?php _e( 'Start', 'wc-bna-gateway' ); ?>">
						<?php echo date( 'Y-m-d', strtotime( $s_val->startDate ) ); ?>
					</td>
					<td class="woocommerce-orders-table__cell " data-title="<?php _e( 'Number of payments', 'wc-bna-gateway' ); ?>">
						<?php echo esc_html( $s_val->numberOfPayments ); ?>
					</td>
					<td class="woocommerce-orders-table__cell " data-title="<?php _e( 'Desc.', 'wc-bna-gateway' ); ?>">
						<?php
						switch ( $desc->paymentMethod ) {
							case 'CARD':
								$paymentDetails = ucfirst( $desc->paymentDetails->cardBrand ) . ':  ' . " {$desc->paymentDetails->cardNumber}";
								break;
							case 'EFT':
								$paymentDetails = __( 'Account #:', 'wc-bna-gateway' ) . " {$desc->paymentDetails->accountNumber}<br>";
								$paymentDetails .= __( 'Transit #:', 'wc-bna-gateway' ) . " {$desc->paymentDetails->transitNumber}<br>";
								$paymentDetails .= __( 'Institution #:', 'wc-bna-gateway' ) . " {$desc->paymentDetails->bankNumber}";
								break;
							case 'E-TRANSFER':
								if ( isset( $desc->paymentDetails->emailAddress ) ) {
									$paymentDetails = __( 'Email:', 'wc-bna-gateway' ) . " {$desc->paymentDetails->emailAddress}";
								} else { $paymentDetails = ''; }
								break;
						} 
						?>
						<button type="button" class="btn-show-desc"
							data-order-id="<?php echo $invoice_id; ?>"
							data-order-question="<?php _e( 'Description of the order', 'wc-bna-gateway' ) ?>"
							data-id="<?php echo esc_html( $s_val->recurringId ); ?>" 
							data-created="<?php echo date( 'Y-m-d H:i:s', strtotime( $s_val->created_time ) ); ?>" 
							data-status="<?php echo esc_html( $s_val->status ); ?>" 
							data-currency="<?php echo esc_html( $desc->currency ); ?>" 
							data-total="<?php if ( isset( $desc->total ) ) { echo esc_html( $desc->total ); } ?>" 
							data-subtotal="<?php if ( isset( $desc->subtotal ) ) { echo esc_html( $desc->subtotal ); } ?>" 
							data-amount="<?php if ( isset( $desc->amount ) ) { echo esc_html( $desc->amount ); } ?>" 
							data-balance="<?php if ( isset( $desc->balance ) ) { echo esc_html( $desc->balance ); } ?>" 
							data-fee="<?php  if ( isset( $desc->fee ) ) { echo esc_html( $desc->fee ); }  ?>" 
							data-payment-method="<?php echo esc_html( $desc->paymentMethod ); ?>" 
							data-payment-details="<?php if ( isset( $paymentDetails ) ) { echo esc_html( $paymentDetails ); } ?>" >
							<?php echo $icon_eye; ?>
						</button>
					</td>
					<td class="woocommerce-orders-table__cell" data-title="<?php _e( 'Manage', 'wc-bna-gateway' ); ?>">
						<?php if ( $s_val->status === 'ACTIVE' ) { ?>
							<button type="button" class="btn-suspend-subscription" data-id="<?php echo esc_html( $s_val->id ); ?>" data-suspend="yes" 
								data-order-id="<?php echo $invoice_id; ?>"
								data-order-question="<?php _e( 'Do you want to suspend the subscription', 'wc-bna-gateway' ) ?>" >
								<?php echo $icon_pause; ?>
							</button>
						<?php } ?>
						<?php if ( $s_val->status === 'SUSPENDED' ) { ?>
							<button type="button" class="btn-suspend-subscription" data-id="<?php echo esc_html( $s_val->id ); ?>" data-suspend="no" 
								data-order-id="<?php echo $invoice_id; ?>"
								data-order-question="<?php _e( 'Do you want to run the subscription', 'wc-bna-gateway' ) ?>" >
								<?php echo $icon_play; ?>
							</button>
						<?php } ?>
						<?php if ( $s_val->status !== 'DELETED' ) { ?>
							<button type="button" class="btn-del-subscription" data-id="<?php echo esc_html( $s_val->id ); ?>" 
								data-order-id="<?php echo $invoice_id; ?>"
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

<div id="bna-desc-wrapper">	
	<div id="bna-desc-box">
		<h2 id="bna-desc-header"><?php _e( 'Description', 'wc-bna-gateway' ) ?></h2>
		<button type="button" id="bna-desc-cancel">+</button>
		<div id="bna-desc-body">
			<p id="bna-desc-id"><span class="bna-desc-p-name"><?php _e( 'ID:', 'wc-bna-gateway' ); ?></span><span class="bna-desc-p-value"></span></p>
			<p id="bna-desc-created"><span class="bna-desc-p-name"><?php _e( 'Created:', 'wc-bna-gateway' ); ?></span><span class="bna-desc-p-value"></span></p>
			<p id="bna-desc-status"><span class="bna-desc-p-name"><?php _e( 'Status:', 'wc-bna-gateway' ); ?></span><span class="bna-desc-p-value"></span></p>
			<p id="bna-desc-currency"><span class="bna-desc-p-name"><?php _e( 'Currency:', 'wc-bna-gateway' ); ?></span><span class="bna-desc-p-value"></span></p>
			<p id="bna-desc-total"><span class="bna-desc-p-name"><?php _e( 'Total amount:', 'wc-bna-gateway' ); ?></span><span class="bna-desc-p-value"></span></p>
			<p id="bna-desc-subtotal"><span class="bna-desc-p-name"><?php _e( 'Subtotal:', 'wc-bna-gateway' ); ?></span><span class="bna-desc-p-value"></span></p>
			<p id="bna-desc-amount"><span class="bna-desc-p-name"><?php _e( 'Amount:', 'wc-bna-gateway' ); ?></span><span class="bna-desc-p-value"></span></p>
			<p id="bna-desc-balance"><span class="bna-desc-p-name"><?php _e( 'Balance:', 'wc-bna-gateway' ); ?></span><span class="bna-desc-p-value"></span></p>
			<p id="bna-desc-fee"><span class="bna-desc-p-name"><?php _e( 'Fee:', 'wc-bna-gateway' ); ?></span><span class="bna-desc-p-value"></span></p>
			<p id="bna-desc-payment-method"><span class="bna-desc-p-name"><?php _e( 'Payment method:', 'wc-bna-gateway' ); ?></span><span class="bna-desc-p-value"></span></p>
			<p id="bna-desc-payment-details"><span class="bna-desc-p-name"><?php _e( 'Payment details:', 'wc-bna-gateway' ); ?></span><span class="bna-desc-p-value"></span></p>			
		</div>
	</div>
</div>
