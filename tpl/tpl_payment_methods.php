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
		<a href="<?php echo wc_get_account_endpoint_url( 'bna-add-credit-card' ); ?>" class="bna-button bna-button-flex">
			<?php _e( 'Add Card', 'wc-bna-gateway' ); ?>
		</a>
		<a href="<?php echo wc_get_account_endpoint_url( 'bna-bank-account-info' ); ?>" class="bna-button bna-button-flex">
			<?php _e( 'Add EFT', 'wc-bna-gateway' ); ?>
		</a>
		<a href="<?php echo wc_get_account_endpoint_url( 'bna-e-transfer-info' ); ?>" class="bna-button bna-button-flex">
			<?php _e( 'Add e-Transfer', 'wc-bna-gateway' ); ?>
		</a>
	</div>
	<div class="bna-desc"><?php _e( 'The following payment methods will be available on the checkout page.', 'wc-bna-gateway' ); ?></div>
	<table class="shop_table shop_table_responsive">
		<thead>
			<tr>
				<th><?php _e( 'Payment types', 'wc-bna-gateway' ); ?></th>
				<th><?php _e( 'Information', 'wc-bna-gateway' ); ?></th>
				<th><?php _e( 'Manage', 'wc-bna-gateway' ); ?></th>
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
								$imageName = 'visa.svg';
							} elseif ( $data->cardBrand === 'MASTERCARD' ) {
								$imageName = 'masterCard.svg';
							} elseif ( $data->cardBrand === 'AMEX' ) {
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
					<tr>
						<td>
							<img class="method-img" src="<?php echo BNA_PLUGIN_DIR_URL . 'assets/img/' . $imageName; ?>" alt="<?php echo $p_method->paymentType; ?>" />
						</td>
						<td>
							<?php							
								switch ( $p_method->paymentType ) {
									case 'card':
										echo $data->cardNumber . '<br>' . __( 'Expiry: ', 'wc-bna-gateway' ) . $data->expiryMonth . '/' . $data->expiryYear;
										break;
									case 'eft':
										echo $data->accountNumber . '/' . $data->transitNumber . '<br>' . __( 'Institution: ', 'wc-bna-gateway' ) . $data->bankName; 
										break;
									case 'e-transfer':
										echo __( 'Email: ', 'wc-bna-gateway' ) . $data->interacEmail;
										break;
								}							
							?>
						</td>
						<td>
							<a class="method-delete btn-del-payment" data-id="<?php echo $p_method->id; ?>" href="#"><?php _e( 'Delete', 'wc-bna-gateway' ); ?></a>
						</td>
					</tr>
					<?php
				}
			?>					
		</tbody>
	</table>
</div>
