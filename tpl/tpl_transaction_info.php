<?php
/**
 * Woocommerce BNA Gateway
 *
 * @author 	BNA
 * @category 	'BNA transaction' Template
 * @version     1.0
 */

if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly
?>

<section class="section my-account-orders">
	<div>
		<?php $bna_transaction_info_url = esc_url( wc_get_account_endpoint_url( 'bna-transaction-info' ) ); ?>
		<div class="woocommerce-orders-table__filters">
				<a href="<?php echo $bna_transaction_info_url . '?bna-orders-filter=last-week'; ?>" class="woocommerce-orders-table__filter <?php echo bna_add_class_active( 'last-week' ); ?>">
				<?php _e( 'Last Week', 'wc-bna-gateway' ); ?>
			</a>
			<a href="<?php echo $bna_transaction_info_url . '?bna-orders-filter=last-month'; ?>" class="woocommerce-orders-table__filter <?php echo bna_add_class_active( 'last-month' ); ?>">
				<?php _e( 'Last Month', 'wc-bna-gateway' ); ?>
			</a>
			<a href="<?php echo $bna_transaction_info_url . '?bna-orders-filter=last-three-months'; ?>" class="woocommerce-orders-table__filter <?php echo bna_add_class_active( 'last-three-months' ); ?>">
				<?php _e( 'Last 3 Months', 'wc-bna-gateway' ); ?>
			</a>
			<a href="<?php echo $bna_transaction_info_url . '?bna-orders-filter=last-year'; ?>" class="woocommerce-orders-table__filter <?php echo bna_add_class_active( 'last-year' ); ?>">
				<?php _e( 'Last Year', 'wc-bna-gateway' ); ?>
			</a>
		</div>
	</div>
	
	<table class="woocommerce-orders-table woocommerce-MyAccount-orders shop_table shop_table_responsive my_account_orders account-orders-table">
		<thead>
			<tr>
				<th class="woocommerce-orders-table__header"><span class="nobr"><?php _e( 'Order', 'wc-bna-gateway' ); ?></span></th>
				<th class="woocommerce-orders-table__header"><span class="nobr"><?php _e( 'Transaction', 'wc-bna-gateway' ); ?></span></th>
				<th class="woocommerce-orders-table__header"><span class="nobr"><?php _e( 'Type', 'wc-bna-gateway' ); ?></span></th>
				<th class="woocommerce-orders-table__header"><span class="nobr"><?php _e( 'Status', 'wc-bna-gateway' ); ?></span></th>
				<th class="woocommerce-orders-table__header"><span class="nobr"><?php _e( 'Description', 'wc-bna-gateway' ); ?></span></th>
				<th class="woocommerce-orders-table__header"><span class="nobr"><?php _e( 'Created', 'wc-bna-gateway' ); ?></span></th>
			</tr>
		</thead>
		<tbody>
			<?php
			foreach ( $transactions as $t_val ) {
				$desc = json_decode( $t_val->transactionDescription );

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
					<td class="woocommerce-orders-table__cell " data-title="<?php _e( 'Order', 'wc-bna-gateway' ); ?>">
						<a class="bna-orders-order-link" href="/my-account/view-order/<?php echo $t_val->order_id; ?>/">
							<?php echo '#' . $t_val->order_id; ?>
						</a>
					</td>
					<td class="woocommerce-orders-table__cell " data-title="<?php _e( 'Transaction', 'wc-bna-gateway' ); ?>">
						<?php echo $t_val->transactionToken;?>					
					</td>
					<td class="woocommerce-orders-table__cell " data-title="<?php _e( 'Type', 'wc-bna-gateway' ); ?>">
						<div style="display: flex;">
							<div class="img-transaction-type">
								<img src="<?php echo $this->plugin_url.'assets/img/' . $imageName; ?>" alt="<?php echo $desc->paymentMethod;?>" style="height: 25px;">
							</div>
						</div>					
					</td>
					<td class="woocommerce-orders-table__cell " data-title="<?php _e( 'Status', 'wc-bna-gateway' ); ?>">
						<?php echo $t_val->transactionStatus; ?>				
					</td>
					<td class="woocommerce-orders-table__cell " data-title="<?php _e( 'Description', 'wc-bna-gateway' ); ?>">
						<details><summary><?php _e( 'more...', 'wc-bna-gateway' ); ?></summary>
							<p><?php _e( 'Currency:', 'wc-bna-gateway' ); ?> <?php echo $desc->currency;?></p>
							<?php
								if ( isset( $desc->total ) )
									echo "<p>" . __( 'Total amount:', 'wc-bna-gateway' ) . " {$desc->total}</p>";

								if ( isset( $desc->subtotal ) )
									echo "<p>" . __( 'Subtotal:', 'wc-bna-gateway' ) . " {$desc->subtotal}</p>";
									
								if ( isset( $desc->amount ) )
									echo "<p>" . __( 'Amount:', 'wc-bna-gateway' ) . " {$desc->amount}</p>";
							?>
							<p><?php _e( 'BNA fee:', 'wc-bna-gateway' ); ?> <?php echo $desc->fee;?></p> 
							<?php
								switch ( strtolower( $desc->paymentMethod ) ) {
									case 'card':
										echo "<p>" . __( 'Card #:', 'wc-bna-gateway' ) . " {$desc->paymentDetails->cardNumber}</p>";
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
						<?php echo date( 'Y-m-d H:i:s', strtotime( $desc->transactionTime ) ); ?>
					</td>
				</tr>            
        <?php
		}
        ?>
		</tbody>
	</table>
</section>
