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
<h3 class="woo-myaccount-text-bigger bna-subtitle"><?php _e( 'Manage your eTransfer Information', 'wc-bna-gateway' ); ?></h3>

<div style="<?php echo empty( $payorID ) ? 'display:block;' : 'display:none;'; ?>" >
	<p>
		<div class="woocommerce-error">
			<?php _e( 'Sorry. Please create a customer account first.', 'wc-bna-gateway' ); ?>
		</div>
	</p>
</div>

<form class="form_save_payment"  style="<?php echo empty( $payorID ) ? 'display:none;' : 'display:block;'; ?>" >
	<div class="bna-payment-method-cards">
		<div class="bna-text-required">* <?php _e( 'Required fields', 'wc-bna-gateway' ); ?></div>
		
		<div class="bna-input-wrapper">
			<div class="bna-input-label"><?php _e( 'New E-Mail', 'wc-bna-gateway' ); ?> <span class="required">*</span></div>
			<input class="bna-input" type="email" name="email" value="<?php echo wp_get_current_user()->user_email;?>" maxlength="100" 
				placeholder="login@domain" readonly>
		</div>
		
		<div  class="bna-button-wrapper bna-mt-60">
			<button id="save_payment" class="bna-button"><?php _e( 'Add Payment Method', 'wc-bna-gateway' ); ?></button>
		</div>
		<input type="hidden" name="payment_type" value="e-transfer">
	</div>
</form>
<div class="loading"></div>
