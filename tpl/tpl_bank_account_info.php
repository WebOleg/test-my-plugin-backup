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
				<span class="bna-font-italic"><?php _e( '(the full name of the person or business associated with the bank account.)', 'wc-bna-gateway' ); ?></span>
			</div>
			<select id="bank_name" name="bank_name" class="input-text"></select>
		</div>
		
		<div class="bna-two-inputs-wrapper">
			<div class="bna-input-wrapper">
				<div class="bna-input-label"><?php _e( 'Bank Number', 'wc-bna-gateway' ); ?> <span class="required">*</span></div>
				<input class="bna-input" type="text" id="bank_number" name="bank_number" 
					autocomplete="off" placeholder="" onkeyup="return input_test(this);" maxlength="3" >
			</div>
			
			<div class="bna-input-wrapper">
				<div class="bna-input-label"><?php _e( 'Account Number', 'wc-bna-gateway' ); ?> <span class="required">*</span></div>
				<input class="bna-input" type="text" name="account_number" 
					autocomplete="off" placeholder="" onkeyup="return input_test(this);" maxlength="8" >
			</div>
		</div>
			
		<div class="bna-two-inputs-wrapper">
			<div class="bna-input-wrapper">
				<div class="bna-input-label"><?php _e( 'Transit Number', 'wc-bna-gateway' ); ?> <span class="required">*</span></div>
				<input  class="bna-input" type="text" name="transit_number" 
					autocomplete="off" placeholder=""  onkeyup="return input_test(this);" maxlength="5" >
			</div>
			<div class="bna-number-img-wrapper">
				<img class="bna-CVC-img" src="<?php echo BNA_PLUGIN_DIR_URL . 'assets/img/Cheque_1.png'; ?>" />
			</div>			
		</div>
		
		<div  class="bna-button-wrapper bna-button-save-changes-two">
			<button id="save_payment" class="bna-button"><?php _e( 'Add Payment Method', 'wc-bna-gateway' ); ?></button>
		</div>
		<input type="hidden" name="payment_type" value="eft">
	</div>
</form>
<div class="loading"></div>

<script type="module">
	(function($) {	
		$('#bank_name').selectWoo();
		
		// select bank
		$('#bank_name').on("select2:select", function(e) {
			$('#bank_number').val( $(this).val() );
			$('#bank_number').removeClass('invalid');
		});
		// and select bank by id cod
		$('#bank_number').on('keyup', function() {
			let bankNumberVal = $('#bank_number').val();
			$('#bank_name option').each(function(i) {
				if ( parseInt( $(this).val() ) === parseInt(bankNumberVal) ) {
					$('#bank_number').removeClass('invalid');
					$('#bank_name').selectWoo().val( $(this).val() ).trigger('change');
					return false;
				} else { $('#bank_number').addClass('invalid'); }				
			});
		});
		// validation fields
		$('form.form_save_payment input[name="bank_number"]').on('blur keyup', function() {
			if ( $(this).val().length >= 1 ) {
				//$(this).removeClass('invalid');
			} else {
				$(this).addClass('invalid');
			}
		});
		$('form.form_save_payment input[name="account_number"]').on('blur keyup', function() {
			if ( $(this).val().length >= 7 ) {
				$(this).removeClass('invalid');
			} else {
				$(this).addClass('invalid');
			}
		});
		$('form.form_save_payment input[name="transit_number"]').on('blur keyup', function() {
			if ( $(this).val().length >= 3 ) {
				$(this).removeClass('invalid');
			} else {
				$(this).addClass('invalid');
			}
		});
	})(jQuery);
	
	let select_bankName = document.querySelector('#bank_name');
	let interval = setInterval(() => {
		if ( typeof window.bankName !== 'undefined' ) {
			let arBankName = (Object.entries(window.bankName)).sort(function(a,b){     
				if(a[1] > b[1]) return 1;
				if(a[1] < b[1]) return -1;
				return 0;
			});
			let options = '';
			let i = 0;
			for (i in arBankName) {
				options += '<option value="'+ arBankName[i][0] +'">' + arBankName[i][1] + '</option>';
			}
			select_bankName.innerHTML += options;
			clearInterval(interval);
		}
	}, 500);
</script>
