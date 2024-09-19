<?php
/**
 * Woocommerce BNA Gateway
 *
 * @author 	BNA
 * @category 'Add Credit Card' Template
 * @version     1.0
 */

if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly
?>
<h3 class="woo-myaccount-text-bigger bna-subtitle"><?php _e( 'Manage your Credit Cards', 'wc-bna-gateway' ); ?></h3>

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
			<div class="bna-input-label"><?php _e( 'Cardholder Name', 'wc-bna-gateway' ); ?> <span class="required">*</span><br>
			<span class="bna-font-italic"><?php _e( '(the exact name as it appears on the front of your credit card)', 'wc-bna-gateway' ); ?></span></div>
			<input class="bna-input" type="text" name="cc_holder" autocomplete="off" maxlength="100" placeholder="FIRSTNAME LASTNAME" >
		</div>
		
		<div class="bna-two-inputs-wrapper">
			<div class="bna-input-wrapper">
				<div class="bna-input-label"><?php _e( 'Card Number', 'wc-bna-gateway' ); ?> <span class="required">*</span></div>
				<input class="bna-input bna-check-cc-number" type="text" name="cc_number" autocomplete="off" maxlength="19" placeholder="0000 0000 0000 0000" >
			</div>
			
			<div class="bna-input-wrapper">
				<div class="bna-input-label"><?php _e( 'Expiry Date', 'wc-bna-gateway' ); ?> <span class="required">*</span></div>
				<input class="bna-input bna-check-cc-expire" type="text" name="cc_expire" 
					autocomplete="off" placeholder="MM/YY" maxlength="7">
			</div>
		</div>
			
		<div class="bna-three-inputs-wrapper">
			<div class="bna-input-wrapper">
				<div class="bna-input-label"><?php _e( 'CVC', 'wc-bna-gateway' ); ?> <span class="required">*</span></div>
				<input  class="bna-input bna-check-cc-cvc" type="text" name="cc_code" autocomplete="off" placeholder="CVC" maxlength="3" >
			</div>
			<div class="bna-CVC-text-wrapper">
				<div class="bna-CVC-text">
					<?php _e( 'CVC (CVV, CCV, SVC or CSC) is a card security verification code. Three or four digits printed, not embossed, on the back of the card. ', 'wc-bna-gateway' ); ?>
				</div>
			</div>
			<div class="bna-CVC-img-wrapper">
				<img class="bna-CVC-img" src="<?php echo BNA_PLUGIN_DIR_URL . 'assets/img/Credit_Card_SVC.png'; ?>" />
			</div>
			<div  class="bna-button-wrapper bna-button-save-changes">
				<button id="save_payment" class="bna-button"><?php _e( 'Add Payment Method', 'wc-bna-gateway' ); ?></button>
			</div>
			<input type="hidden" name="payment_type" value="card">
		</div>
	</div>
</form>
<div class="loading"></div>
