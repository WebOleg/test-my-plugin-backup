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

<div class="tpl-payment-method" style="<?php echo empty( $payorID ) ? 'display:none;' : 'display:block;'; ?>" >
	<div  class="tpl-few-buttons-wrapper">
		<a href="<?php echo wc_get_account_endpoint_url( 'bna-add-credit-card' ); ?>" class="tpl-button tpl-button-flex">
			<?php _e( 'Add Card', 'wc-bna-gateway' ); ?>
		</a>
		<a href="<?php echo wc_get_account_endpoint_url( 'bna-bank-account-info' ); ?>" class="tpl-button tpl-button-flex">
			<?php _e( 'Add EFT', 'wc-bna-gateway' ); ?>
		</a>
		<a href="<?php echo wc_get_account_endpoint_url( 'bna-e-transfer-info' ); ?>" class="tpl-button tpl-button-flex">
			<?php _e( 'Add e-Transfer', 'wc-bna-gateway' ); ?>
		</a>
	</div>
	<div class="tpl-desc"><?php _e( 'The following payment methods will be available on the checkout page.', 'wc-bna-gateway' ); ?></div>
	<table class="shop_table shop_table_responsive">
		<thead>
			<tr>
				<th><?php _e( 'Payment types', 'wc-bna-gateway' ); ?></th>
				<th><?php _e( 'Information', 'wc-bna-gateway' ); ?></th>
				<th><?php _e( 'Manage', 'wc-bna-gateway' ); ?></th>
			</tr>
		</thead>

		<tbody>
			<tr>
				<td>
					<img class="method-img" src="<?php echo BNA_PLUGIN_DIR_URL . 'img/directCredit.svg'; ?>" />
				</td>
				<td>
					E-mail@address
				</td>
				<td>
					<a class="method-delete" href="#">Delete</a>
				</td>
			</tr>
			<tr>
				<td>
					<img class="method-img" src="<?php echo BNA_PLUGIN_DIR_URL . 'img/directCredit.svg'; ?>" >
				</td>
				<td>
					E-mail@address
				</td>
				<td>
					<a class="method-delete" href="#">Delete</a>
				</td>
			</tr>
		</tbody>
	</table>
</div>

<script>
(function() {	
	function input_test(input) { 
		input.value = input.value.replace(/[^\d,]/g, "");
	};
})();	
</script>

