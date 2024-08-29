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
<h3 class="woo-myaccount-text-bigger tpl-subtitle"><?php _e( 'Manage your Bank Accounts Information', 'wc-bna-gateway' ); ?></h3>

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
			<div class="tpl-input-label">
				<?php _e( 'Name on Bank Account', 'wc-bna-gateway' ); ?> <span class="required">*</span><br>
				<span class="tpl-font-italic"><?php _e( '(the full name of the person or business associated with the bank account.)', 'wc-bna-gateway' ); ?></span></div>
			<input class="tpl-input" type="text" name="eft_holder" autocomplete="off" maxlength="100" placeholder="FIRSTNAME LASTNAME" value="">
		</div>
		
		<div class="tpl-two-inputs-wrapper">
			<div class="tpl-input-wrapper">
				<div class="tpl-input-label"><?php _e( 'Branch/Transit Number *', 'wc-bna-gateway' ); ?> <span class="required">*</span></div>
				<input class="tpl-input" type="text" name="eft_branch" autocomplete="off" maxlength="18" placeholder=""
					onkeyup="return input_test(this);" value="">
			</div>
			
			<div class="tpl-input-wrapper">
				<div class="tpl-input-label"><?php _e( 'Financial Institution Number *', 'wc-bna-gateway' ); ?> <span class="required">*</span></div>
				<input class="tpl-input" type="text" id="cc_expire_month" name="eft_institution" 
					autocomplete="off" placeholder="" onkeyup="return input_test(this);" maxlength="18" value="">
			</div>
		</div>
			
		<div class="tpl-two-inputs-wrapper">
			<div class="tpl-input-wrapper">
				<div class="tpl-input-label"><?php _e( 'Account Number', 'wc-bna-gateway' ); ?> <span class="required">*</span></div>
				<input  class="tpl-input" type="password" name="eft_account" autocomplete="off" placeholder="" maxlength="3" 
					onkeyup="return input_test(this);" value="">
			</div>
			<div class="tpl-number-img-wrapper">
				<img class="tpl-CVC-img" src="<?php echo BNA_PLUGIN_DIR_URL . 'img/Cheque_1.png'; ?>" />
			</div>
			<div  class="tpl-button-wrapper tpl-button-save-changes">
				<button id="save_payment" class="tpl-button"><?php _e( 'Save Changes', 'wc-bna-gateway' ); ?></button>
			</div>
		</div>
	</div>
</form>
<div class="loading"></div>
