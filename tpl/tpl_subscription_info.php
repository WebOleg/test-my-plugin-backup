<?php
/**
 * Woocommerce BNA Gateway
 *
 * @author 	BNA
 * @category 	'BNA subscription' Template
 * @version     1.0
 */

if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly
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
				<th class="woocommerce-orders-table__header"><span class="nobr"><?php _e( 'Start', 'wc-bna-gateway' ); ?></span></th>
				<th class="woocommerce-orders-table__header"><span class="nobr"><?php _e( 'Number of payments', 'wc-bna-gateway' ); ?></span></th>
				<th class="woocommerce-orders-table__header"><span class="nobr"><?php _e( 'Desc.', 'wc-bna-gateway' ); ?></span></th>
				<th class="woocommerce-orders-table__header"><span class="nobr"><?php _e( 'Created', 'wc-bna-gateway' ); ?></span></th>
				<th class="woocommerce-orders-table__header"><span class="nobr"><?php _e( 'Manage', 'wc-bna-gateway' ); ?></span></th>
			</tr>
		</thead>
		<tbody>
			<?php                  
			foreach ( $subscriptions as $s_val ) {
				$desc = json_decode( $s_val->recurringDescription );

				$imageName = '';
				switch ( strtolower( $desc->paymentMethod ) ) {
					case 'card':
						if ( strtolower( $desc->paymentDetails->cardBrand ) === 'visa' ) {
							$imageName = 'visa.svg';
						} elseif ( strtolower( $desc->paymentDetails->cardBrand ) === 'mastercard' ) {
							$imageName = 'masterCard.svg';
						} elseif ( strtolower( $desc->paymentDetails->cardBrand ) === 'amex' ) {
							$imageName = 'americanExpress.svg';
						}
						break;
					case 'eft':
						$imageName = 'directCredit.svg';
						break;
					case 'e-transfer':
						$imageName = 'eTransfer.svg';
						break;
				}
				if ( empty( $imageName ) ) continue;
				?>
				<tr class="woocommerce-orders-table__row">
					<td class="woocommerce-orders-table__cell " data-title="<?php _e( 'Base Order', 'wc-bna-gateway' ); ?>">
						<a class="bna-orders-order-link" href="/my-account/view-order/<?php echo $desc->invoiceInfo->invoiceId; ?>/">
							<?php echo '#' . $desc->invoiceInfo->invoiceId; ?>
						</a>
					</td>
					<td class="woocommerce-orders-table__cell " data-title="<?php _e( 'Recurring', 'wc-bna-gateway' ); ?>">
						<?php echo $s_val->recurring; ?>
					</td>
					<td class="woocommerce-orders-table__cell " data-title="<?php _e( 'Start', 'wc-bna-gateway' ); ?>">
						<?php echo date( 'Y-m-d H:i:s', strtotime( $s_val->startDate ) ); ?>
					</td>
					<td class="woocommerce-orders-table__cell " data-title="<?php _e( 'Number of payments', 'wc-bna-gateway' ); ?>">
						<?php echo $s_val->numberOfPayments; ?>
					</td>
					<td class="woocommerce-orders-table__cell " data-title="<?php _e( 'Desc.', 'wc-bna-gateway' ); ?>">
						<details><summary><?php _e( 'more...', 'wc-bna-gateway' ); ?></summary>
							<p><?php _e( 'ID:', 'wc-bna-gateway' ); ?> <?php echo $s_val->recurringId; ?></p>
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
								switch ( strtolower( $desc->paymentMethod ) ) {
									case 'card':
										echo "<p>" . ucfirst( $desc->paymentDetails->cardBrand ) . ':  ' . " {$desc->paymentDetails->cardNumber}</p>";
										break;
									case 'eft':
										echo "<p>" . __( 'Account #:', 'wc-bna-gateway' ) . " {$desc->paymentDetails->accountNumber}</p>";
										echo "<p>" . __( 'Transit #:', 'wc-bna-gateway' ) . " {$desc->paymentDetails->transitNumber}</p>";
										echo "<p>" . __( 'Institution #:', 'wc-bna-gateway' ) . " {$desc->paymentDetails->bankNumber}</p>";
										break;
									case 'e-transfer':
										echo "<p>" . __( 'Email:', 'wc-bna-gateway' ) . " {$desc->paymentDetails->emailAddress}</p>";
										break;
								} 
							?>
						</details>
					</td>
					<td class="woocommerce-orders-table__cell " data-title="<?php _e( 'Created', 'wc-bna-gateway' ); ?>">
						<?php echo date( 'Y-m-d H:i:s', strtotime( $s_val->created_time ) );?>
					</td>
					<td class="woocommerce-orders-table__cell " data-title="<?php _e( 'Manage', 'wc-bna-gateway' ); ?>">
						<button type="button" class="btn-del-subscription" data-id="<?php echo $s_val->id; ?>" data-order-id="<?php echo $desc->invoiceInfo->invoiceId; ?>">
							<img  class="bna-delete-img" src="<?php echo $this->plugin_url . 'assets/img/trash-solid.svg'; ?>" >
						</button>
					</td>
				</tr>
			<?php
            }                           
            ?>	
		</tbody>
	</table>
	
</section>

<div id="jquery-ui-dialog" title="A dialog">
            <p>You can move this dialog box, or close it with the 'X' sign at the top-right.</p>
        </div>
