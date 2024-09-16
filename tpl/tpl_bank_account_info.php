<?php
/**
 * Woocommerce BNA Gateway
 *
 * @author 	BNA
 * @category 'E-Transfer Info' Template
 * @version     1.0
 */

if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly
?>
<h3 class="woo-myaccount-text-bigger bna-subtitle"><?php _e( 'Manage your Bank Accounts Information', 'wc-bna-gateway' ); ?></h3>

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
			<div class="bna-input-label">
				<?php _e( 'Name on Bank Account', 'wc-bna-gateway' ); ?><br>
				<span class="bna-font-italic"><?php _e( '(the full name of the person or business associated with the bank account.)', 'wc-bna-gateway' ); ?></span></div>
			<input class="bna-input" type="text" name="eft_holder" autocomplete="off" maxlength="100" placeholder=""
		</div>
		
		<div class="bna-two-inputs-wrapper">
			<div class="bna-input-wrapper">
				<div class="bna-input-label"><?php _e( 'Bank Number', 'wc-bna-gateway' ); ?> <span class="required">*</span></div>
				<input class="bna-input" type="text" name="bank_number" autocomplete="off" maxlength="18" placeholder=""
					onkeyup="return input_test(this);" >
			</div>
			
			<div class="bna-input-wrapper">
				<div class="bna-input-label"><?php _e( 'Account Number', 'wc-bna-gateway' ); ?> <span class="required">*</span></div>
				<input class="bna-input" type="text" name="account_number" 
					autocomplete="off" placeholder="" onkeyup="return input_test(this);" maxlength="18" >
			</div>
		</div>
			
		<div class="bna-two-inputs-wrapper">
			<div class="bna-input-wrapper">
				<div class="bna-input-label"><?php _e( 'Transit Number', 'wc-bna-gateway' ); ?> <span class="required">*</span></div>
				<input  class="bna-input" type="text" name="transit_number" autocomplete="off" placeholder="" maxlength="18" 
					onkeyup="return input_test(this);" >
			</div>
			<div class="bna-number-img-wrapper">
				<img class="bna-CVC-img" src="<?php echo BNA_PLUGIN_DIR_URL . 'assets/img/Cheque_1.png'; ?>" />
			</div>
			<div  class="bna-button-wrapper bna-button-save-changes">
				<button id="save_payment" class="bna-button"><?php _e( 'Add Payment Method', 'wc-bna-gateway' ); ?></button>
			</div>
			<input type="hidden" name="payment_type" value="eft">
		</div>
	</div>
</form>
<div class="loading"></div>
