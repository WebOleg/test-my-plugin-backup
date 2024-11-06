<?php
/**
 * Woocommerce BNA Gateway
 *
 * @author 	BNA
 * @category 	'BNA transaction' Template
 * @version     1.0
 */

if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly
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
				<th class="woocommerce-orders-table__header"><span class="nobr"><?php _e( 'Action', 'wc-bna-gateway' ); ?></span></th>
			</tr>
		</thead>
		<tbody>
			<?php
			foreach ( $transactions as $t_val ) {
				$desc = json_decode( $t_val->transactionDescription );
				$status_color = 'bna-status-' . strtolower( esc_html( $t_val->transactionStatus ) );
				$order_id = esc_html( $t_val->order_id );

				$imageName = '';
				switch ( $desc->paymentMethod ) {
					case 'CARD':
						if ( $desc->paymentDetails->cardBrand === 'visa' ) {
							$imageName = 'visaCard.svg';
						} elseif ( $desc->paymentDetails->cardBrand === 'mastercard' ) {
							$imageName = 'masterCard.svg';
						} elseif ( $desc->paymentDetails->cardBrand === 'amex' ) {
							$imageName = 'americanExpress.svg';
						} elseif ( $desc->paymentDetails->cardBrand === 'discover' ) {
							$imageName = 'discoverCard.svg';
						} else {
							$imageName = 'visaCard.svg';
						}
						break;
					case 'EFT':
						$imageName = 'eft.svg';
						break;
					case 'E-TRANSFER':
						$imageName = 'etransfer.svg';
						break;
				}
				if ( empty( $imageName ) ) continue;
                ?>
				<tr class="woocommerce-orders-table__row">
					<td class="woocommerce-orders-table__cell " data-title="<?php _e( 'Order', 'wc-bna-gateway' ); ?>">
						<a class="bna-orders-order-link" href="/my-account/view-order/<?php echo $order_id; ?>/">
							<?php echo '#' . esc_html( $order_id ); ?>
						</a>
					</td>
					<td class="woocommerce-orders-table__cell " data-title="<?php _e( 'Transaction', 'wc-bna-gateway' ); ?>">
						<?php echo esc_html( $t_val->transactionToken );?>					
					</td>
					<td class="woocommerce-orders-table__cell " data-title="<?php _e( 'Type', 'wc-bna-gateway' ); ?>">					
						<div class="img-transaction-type">
							<img src="<?php echo $this->plugin_url.'assets/img/' . $imageName; ?>" alt="<?php echo esc_html( $desc->paymentMethod ); ?>">
						</div>									
					</td>
					<td class="woocommerce-orders-table__cell <?php echo $status_color; ?>" data-title="<?php _e( 'Status', 'wc-bna-gateway' ); ?>">
						<?php echo esc_html( $t_val->transactionStatus ); ?>				
					</td>
					<td class="woocommerce-orders-table__cell " data-title="<?php _e( 'Description', 'wc-bna-gateway' ); ?>">
						<?php
						switch ( $desc->paymentMethod ) {
							case 'CARD':
								$paymentDetails = ucfirst( esc_html( $desc->paymentDetails->cardBrand ) ) . ':  ' .  esc_html( $desc->paymentDetails->cardNumber );
								break;
							case 'EFT':
								$paymentDetails = __( 'Account #:', 'wc-bna-gateway' ) . esc_html( $desc->paymentDetails->accountNumber ) . '<br>';
								$paymentDetails .= __( 'Transit #:', 'wc-bna-gateway' ) . esc_html( $desc->paymentDetails->transitNumber ) . '<br>';
								$paymentDetails .= __( 'Institution #:', 'wc-bna-gateway' ) . esc_html( $desc->paymentDetails->bankNumber );
								break;
								break;
							case 'E-TRANSFER':
								if ( isset( $desc->paymentDetails->emailAddress ) ) {
									$paymentDetails = __( 'Email:', 'wc-bna-gateway' ) . esc_html( $desc->paymentDetails->emailAddress );
								} else { $paymentDetails = ''; }
								break;
						} 
						?>
						<button type="button" class="btn-show-desc"
							data-order-id="<?php echo $order_id; ?>"
							data-order-question="<?php _e( 'Description of the order', 'wc-bna-gateway' ) ?>"
							data-created="<?php echo date( 'Y-m-d H:i:s', strtotime( $desc->transactionTime ) ); ?>" 
							data-currency="<?php echo esc_html( $desc->currency ); ?>" 
							data-total="<?php if ( ! empty( $desc->total ) ) { echo number_format( $desc->total, 2 ); } ?>" 
							data-subtotal="<?php if ( ! empty( $desc->subtotal ) ) { echo number_format( $desc->subtotal, 2 ); } ?>" 
							data-amount="<?php if ( ! empty( $desc->amount ) ) { echo number_format( $desc->amount, 2 ); } ?>" 
							data-balance="<?php if ( ! empty( $desc->balance ) ) { echo number_format( $desc->balance, 2 ); } ?>" 
							data-fee="<?php  if ( ! empty( $desc->fee ) ) { echo number_format( $desc->fee, 2 ); }  ?>" 
							data-payment-method="<?php echo esc_html( $desc->paymentMethod ); ?>" 
							data-payment-details="<?php if ( isset( $paymentDetails ) ) { echo esc_html( $paymentDetails ); } ?>" >
							<?php echo $icon_eye; ?>
						</button>
					</td>
					<td class="woocommerce-orders-table__cell " data-title="<?php _e( 'Action', 'wc-bna-gateway' ); ?>">
						<?php echo esc_html( $desc->action ); ?>
					</td>
				</tr>            
        <?php
		}
        ?>
		</tbody>
	</table>
</section>

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
			<p id="bna-desc-created"><span class="bna-desc-p-name"><?php _e( 'Created:', 'wc-bna-gateway' ); ?></span><span class="bna-desc-p-value"></span></p>
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
