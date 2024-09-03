<?php
/**
 * Woocommerce BNA Gateway
 *
 * @author 	BNA
 * @category 'Bank Account Info' Template
 * @version     1.0
 */

if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly
?>
<h3 class="woo-myaccount-text-bigger tpl-subtitle"><?php _e( 'Manage your eTransfer Information', 'wc-bna-gateway' ); ?></h3>

<div style="<?php echo empty( $payorID ) ? 'display:block;' : 'display:none;'; ?>" >
	<p>
		<div class="woocommerce-error">
			<?php _e( 'Sorry. Please create a customer account first.', 'wc-bna-gateway' ); ?>
		</div>
	</p>
</div>

<form class="form_save_payment"  style="<?php echo empty( $payorID ) ? 'display:none;' : 'display:block;'; ?>" >
	<div class="tpl-payment-method-cards">
		<div class="tpl-text-required">* <?php _e( 'Required fields', 'wc-bna-gateway' ); ?></div>
		
		<div class="tpl-input-wrapper">
			<div class="tpl-input-label"><?php _e( 'New E-Mail', 'wc-bna-gateway' ); ?> <span class="required">*</span></div>
			<input class="tpl-input" type="email" name="email" autocomplete="off" maxlength="100" placeholder="Email">
		</div>
		
		<div  class="tpl-button-wrapper tpl-mt-60">
			<button id="save_payment" class="tpl-button"><?php _e( 'Add Payment Method', 'wc-bna-gateway' ); ?></button>
		</div>
		<input type="hidden" name="payment_type" value="e-transfer">
	</div>
</form>
<div class="loading"></div>
