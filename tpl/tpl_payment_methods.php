<?php
/**
 * Woocommerce BNA Gateway
 *
 * @author 	BNA
 * @category 'BNA Payment Method' Template
 * @version     1.0
 */

if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly
?>
<div style="<?php echo empty( $payorID ) ? 'display:block;' : 'display:none;'; ?>" >
	<p>
		<div class="woocommerce-error">
			<?php _e( 'Sorry. Please create a customer account first.', 'wc-bna-gateway' ); ?>
		</div>
	</p>
</div>

<div class="bna-payment-method" style="<?php echo empty( $payorID ) ? 'display:none;' : 'display:block;'; ?>" >
	<div  class="bna-few-buttons-wrapper">
		<?php
			$bna_gateway_settings = get_option( 'woocommerce_bna_gateway_settings' );
			$woo_currency = get_woocommerce_currency();
		?>
		<?php if ( ! empty( $bna_gateway_settings['bna-payment-method-card'] ) && $bna_gateway_settings['bna-payment-method-card'] === 'yes' && in_array( $woo_currency, BNA_CARD_ALLOWED_CURRENCY ) ) { ?>
			<a href="<?php echo wc_get_account_endpoint_url( 'bna-add-credit-card' ); ?>" class="bna-button bna-button-flex">
				<?php _e( 'Add Credit Card', 'wc-bna-gateway' ); ?>
			</a>
		<?php } ?>
		<?php if ( ! empty( $bna_gateway_settings['bna-payment-method-eft'] ) && $bna_gateway_settings['bna-payment-method-eft'] === 'yes' && in_array( $woo_currency, BNA_EFT_ALLOWED_CURRENCY ) ) { ?>
			<a href="<?php echo wc_get_account_endpoint_url( 'bna-bank-account-info' ); ?>" class="bna-button bna-button-flex">
				<?php _e( 'Add Bank Transfer', 'wc-bna-gateway' ); ?>
			</a>
		<?php } ?>
	</div>
	<div class="bna-desc"><?php _e( 'The following payment methods will be available on the checkout page.', 'wc-bna-gateway' ); ?></div>
	<table class="woocommerce-orders-table woocommerce-MyAccount-orders shop_table shop_table_responsive my_account_orders account-orders-table">
		<thead>
			<tr>
				<th class="woocommerce-orders-table__header"><span class="nobr"><?php _e( 'Payment types', 'wc-bna-gateway' ); ?></span></th>
				<th class="woocommerce-orders-table__header"><span class="nobr"><?php _e( 'Information', 'wc-bna-gateway' ); ?></span></th>
				<th class="woocommerce-orders-table__header"><span class="nobr"><?php _e( 'Manage', 'wc-bna-gateway' ); ?></span></th>
			</tr>
		</thead>

		<tbody>
			<?php
				foreach ( $paymentMethods as $p_method ) {
					$data = json_decode( $p_method->paymentDescription );
					$imageName = '';
					switch ( $p_method->paymentType ) {
						case 'card':
							if ( $data->cardBrand === 'VISA' ) {
								$imageName = 'visaCard.svg';
							} elseif ( $data->cardBrand === 'MASTERCARD' ) {
								$imageName = 'masterCard.svg';
							} elseif ( $data->cardBrand === 'AMEX' ) {
								$imageName = 'americanExpress.svg';
							} elseif ( $data->cardBrand === 'DISCOVER' ) {
								$imageName = 'discoverCard.svg';
							} else {
								$imageName = 'visaCard.svg';
							}
							break;
						case 'eft':
							$imageName = 'eft.svg';
							break;
					}
					if ( empty( $imageName ) ) continue;
					?>
					<tr class="woocommerce-orders-table__row">
						<td class="woocommerce-orders-table__cell " data-title="<?php _e( 'Information', 'wc-bna-gateway' ); ?>">
							<img class="method-img" src="<?php echo BNA_PLUGIN_DIR_URL . 'assets/img/' . $imageName; ?>" alt="<?php echo esc_html( $p_method->paymentType ); ?>" />
						</td>
						<td class="woocommerce-orders-table__cell " data-title="<?php _e( 'Recurring', 'wc-bna-gateway' ); ?>">
							<?php
								$current_method = '';
								switch ( $p_method->paymentType ) {
									case 'card':
										$current_method = '<br>' . esc_html( $data->cardBrand ) . ': ' . esc_html( $data->cardNumber ) . '<br>' . __( 'Expiry: ', 'wc-bna-gateway' ) . esc_html( $data->expiryMonth ) . '/' . esc_html( $data->expiryYear );
										break;
									case 'eft':
										$current_method = '<br>' . esc_html( $data->accountNumber ) . '/' . esc_html( $data->transitNumber ) . '<br>' . __( 'Institution: ', 'wc-bna-gateway' ) . esc_html( $data->bankName ); 
										break;
								}
								echo $current_method;					
							?>
						</td>
						<td class="woocommerce-orders-table__cell " data-title="<?php _e( 'Manage', 'wc-bna-gateway' ); ?>">
							<button class="btn-del-payment" data-id="<?php echo esc_html( $p_method->id ); ?>" 
								data-current-method="<?php echo $current_method; ?>"
								data-order-question="<?php _e( 'Do you want to delete the current method ', 'wc-bna-gateway' ) ?>">
								<img class="bna-delete-img" src="<?php echo $this->plugin_url . 'assets/img/trash-solid.svg'; ?>" >
							</button>
						</td>
					</tr>
					<?php
				}
			?>					
		</tbody>
	</table>
</div>

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

