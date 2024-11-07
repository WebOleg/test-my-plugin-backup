<?php
/**
 * Woocommerce BNA Gateway
 *
 * @author 	BNA
 * @category 	'Payment checkout fileds' Template 
 * @version     1.0
 */

if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

$has_payment_method = false;
$bna_gateway_settings = get_option( 'woocommerce_bna_gateway_settings' );
$woo_currency = get_woocommerce_currency();

$is_card_exists = false;
$is_eft_exists = false;
if ( is_array( $paymentMethods ) ) {
	foreach ( $paymentMethods as $pm_val ) {
		if ( $pm_val->paymentType === 'card' ) { $is_card_exists = true; }
		if ( $pm_val->paymentType === 'eft' ) { $is_eft_exists = true; }
	}
}
?>

<script>
	var globalTotal = "<?php echo  WC()->cart->total; ?>";
	var curSymbol = "<?php echo get_woocommerce_currency_symbol(); ?>";
	var isFeeEnabled = false;
	
	function input_test(input) { 
		input.value = input.value.replace(/[^\d,]/g, "");
	};
	
	function roundAccurately (number, decimalPlaces) {
  		return Number(`${Math.round(`${number}e${decimalPlaces}`)}e-${decimalPlaces}`);
	}
</script>

<?php
if ( $bna_gateway_settings['applyFee'] === 'yes' ) {
	?>
	<script>
	isFeeEnabled = true;	
		
	function addFees() 
	{
		let feeTab = document.querySelector('.fee-total');
		let feeSum, feeMult;
		let allFees = window.bna_fee;

		if (!feeTab) {
			let tRef = document.querySelector('.shop_table').getElementsByTagName('tfoot')[0];
			feeTab = tRef.insertRow(1);
			feeTab.className = 'fee-total';
		}

		if ( jQuery('#payment_type').val() === 'e-transfer' ) {
			feeSum = parseFloat(allFees.etransferFlatFee);
			feeMult = parseFloat(allFees.etransferPercentageFee);
		} else if ( jQuery('#payment_type').val() === 'card' ) {
			feeSum = parseFloat(allFees.creditCardFlatFee);
			feeMult = parseFloat(allFees.creditCardPercentageFee);
		} else if ( jQuery('#payment_type').val() === 'eft' ) {
			feeSum = parseFloat(allFees.directDebitFlatFee);
			feeMult = parseFloat(allFees.directDebitPercentageFee);
		} else {
			feeSum = 0;
			feeMult = 0;
		}
		let allFeeSum = parseFloat(globalTotal*feeMult/100) + feeSum;
		allFeeSum = roundAccurately(allFeeSum + parseFloat(allFeeSum*13/100), 2);

		feeTab.innerHTML = 
			'<th>BNA Fee (Includes HST)	</th>'
			+ '<td><strong><span class="woocommerce-Price-amount amount"><bdi>'
			+ allFeeSum.toFixed(2).replace('.', ',')
			+ '<span class="woocommerce-Price-currencySymbol">'
			+ curSymbol
			+ '</span></bdi></span></strong></td>';
		
		let totalTab = document.querySelector('.bna-order-review .order-total');
		if (totalTab !== null) {
			totalTab.innerHTML = 	
				'<th>Total</th>'
				+ '<td><strong><span class="woocommerce-Price-amount amount"><bdi>'
				+ parseFloat(parseFloat(globalTotal) + allFeeSum).toFixed(2).replace('.', ',')
				+ '<span class="woocommerce-Price-currencySymbol">'
				+ curSymbol
				+ '</span></bdi></span></strong></td>';			
		}
	} 

	function removeFees() 
	{
		let child = document.querySelector('.fee-total');
		if (child) child.parentNode.removeChild(child);

		let totalTab = document.querySelector('.order-total');
		totalTab.innerHTML = 	
			'<th>Total</th>'
			+ '<td><strong><span class="woocommerce-Price-amount amount"><bdi>'
			+ parseFloat(globalTotal).toFixed(2).replace('.', ',')
			+ '<span class="woocommerce-Price-currencySymbol">'
			+ curSymbol
			+ '</span></bdi></span></strong></td>';
	}
	</script>
	<?php
}
?>

<fieldset id="wc-<?php echo esc_attr( $this->id ); ?>-cc-form" class="wc-credit-card-form wc-payment-form" >
	<div>
		<div class="bna-payment-methods">
			
			<!-- Card -->
			<?php if ( ! empty( $bna_gateway_settings['bna-payment-method-card'] ) && $bna_gateway_settings['bna-payment-method-card'] === 'yes' && in_array( $woo_currency, BNA_CARD_ALLOWED_CURRENCY ) ) { ?>
			<?php $has_payment_method = true; ?>
				<div class="bna-payment-method__item">
					<div class="bna-checkout-radio" data-payment-type="card"></div>
					<?php _e( 'Credit Card', 'wc-bna-gateway' ); ?>
					<div class="bna-checkout-images">
						<img src="<?php echo BNA_PLUGIN_DIR_URL . 'assets/img/visaCard.svg'; ?>" alt="<?php _e( 'VISA', 'wc-bna-gateway' ); ?>" />
						<img src="<?php echo BNA_PLUGIN_DIR_URL . 'assets/img/masterCard.svg'; ?>" alt="<?php _e( 'Mastercard', 'wc-bna-gateway' ); ?>" />
						<img src="<?php echo BNA_PLUGIN_DIR_URL . 'assets/img/americanExpress.svg'; ?>" alt="<?php _e( 'AMEX', 'wc-bna-gateway' ); ?>" />
					</div>
				</div>
				<div class="bna-payment-method__content">
					<?php if ( is_user_logged_in() && $is_card_exists ) { ?>
						<div class="bna-payment-method__content-title"><?php _e( 'Select Credit Card you want to pay', 'wc-bna-gateway' ); ?></div>
						<select class="bna-checkout-select-card" id="paymentMethodCC" name="paymentMethodCC" aria-placeholder="<?php _e( 'Please choose...', 'wc-bna-gateway' ); ?>">			
							<?php
								if ( is_array( $paymentMethods ) ) {
									foreach ( $paymentMethods as $pm_val ) {
										$pm_desc = json_decode( $pm_val->paymentDescription );
										if ( $pm_val->paymentType === 'card' ) {
											echo 	"<option value=\"".$pm_val->paymentMethodId."\">" .
														$pm_desc->cardBrand . ': ' . $pm_val->paymentInfo .
													"</option>";
										}
									}
								}
							?>
							<option value="new-card"><?php _e( 'New Card', 'wc-bna-gateway' ); ?></option>
						</select>
					<?php } ?>
					
					<div class="bna-payment-method-cards">
						<div class="bna-text-required">* <?php _e( 'Required fields', 'wc-bna-gateway' ); ?></div>
						
						<div class="bna-input-wrapper">
							<div class="bna-input-label"><?php _e( 'Cardholder Name', 'wc-bna-gateway' ); ?> <span class="required">*</span><br>
							<span class="bna-font-italic"><?php _e( '(the exact name as it appears on the front of your credit card)', 'wc-bna-gateway' ); ?></span></div>
							<input class="bna-input" type="text" name="cc_holder" autocomplete="off" maxlength="100" placeholder="FIRSTNAME LASTNAME" >
						</div>
						
						<div class="bna-two-inputs-wrapper">
							<div class="bna-input-wrapper bna-pos-relative">
								<div class="bna-input-label"><?php _e( 'Card Number', 'wc-bna-gateway' ); ?> <span class="required">*</span></div>
								<input class="bna-input" id="credit-card-number" type="text" name="cc_number" autocomplete="off" 
									autocorrect="off" autocapitalize="none" spellcheck="false"
									maxlength="19" placeholder="0000 0000 0000 0000" >
								<div class="bna-card-number-img"></div>
							</div>
							
							<div class="bna-input-wrapper">
								<div class="bna-input-label"><?php _e( 'Expiry Date', 'wc-bna-gateway' ); ?> <span class="required">*</span></div>
								<input class="bna-input" id="expiration" type="text" name="cc_expire" autocomplete="off"
									autocorrect="off" autocapitalize="none" spellcheck="false"
									 placeholder="MM/YY" maxlength="7">
							</div>
						</div>
							
						<div class="bna-three-inputs-wrapper">
							<div class="bna-input-wrapper">
								<div class="bna-input-label"><?php _e( 'CVC', 'wc-bna-gateway' ); ?> <span class="required">*</span></div>
								<input  class="bna-input" id="cvv" type="text" name="cc_code" autocomplete="off" 
									autocorrect="off" autocapitalize="none" spellcheck="false"
									placeholder="CVC" maxlength="4" >
							</div>
							<div class="bna-CVC-text-wrapper">
								<div class="bna-CVC-text">
									<?php _e( 'CVC (CVV, CCV, SVC or CSC) is a card security verification code. Three or four digits printed, not embossed, on the back of the card. ', 'wc-bna-gateway' ); ?>
								</div>
							</div>
							<div class="bna-CVC-img-wrapper">
								<img class="bna-CVC-img" src="<?php echo BNA_PLUGIN_DIR_URL . 'assets/img/Credit_Card_SVC.png'; ?>" />
							</div>
						</div>
						
						<?php if ( is_user_logged_in() ) { ?>
							<label class="bna-checkbox-container">
								<input type="checkbox" name="save-credit-card">
								<span class="checkmark"></span>
								<?php _e( 'Save this payment method for your next purchases.', 'wc-bna-gateway' ); ?>
							</label>
						<?php } ?>
					</div>			
				</div>
			<?php } ?>
					
			<!-- EFT -->
			<?php if ( ! empty( $bna_gateway_settings['bna-payment-method-eft'] ) && $bna_gateway_settings['bna-payment-method-eft'] === 'yes' && in_array( $woo_currency, BNA_EFT_ALLOWED_CURRENCY ) ) { ?>
			<?php $has_payment_method = true; ?>
				<div class="bna-payment-method__item">
					<div class="bna-checkout-radio"  data-payment-type="eft"></div>
					<?php _e( 'Bank Transfer', 'wc-bna-gateway' ); ?>
					<div class="bna-checkout-images">
						<img src="<?php echo BNA_PLUGIN_DIR_URL . 'assets/img/eft.svg'; ?>" alt="<?php _e( 'Bank Transfer', 'wc-bna-gateway' ); ?>" />
					</div>
				</div>
				<div class="bna-payment-method__content">
					<?php if ( is_user_logged_in() && $is_eft_exists ) { ?>
						<div class="bna-payment-method__content-title"><?php _e( 'Saved payment methods', 'wc-bna-gateway' ); ?></div>
						<select class="bna-checkout-select-card" id="paymentMethodDD" name="paymentMethodDD" aria-placeholder="<?php _e( 'Please choose...', 'wc-bna-gateway' ); ?>">					
							<?php
								if ( is_array( $paymentMethods ) ) {
									foreach ( $paymentMethods as $pm_val ) {
										$pm_desc = json_decode( $pm_val->paymentDescription );
										if ( $pm_val->paymentType === 'eft' ) {
											echo "<option value=\"".$pm_val->paymentMethodId."\">" .
														$pm_val->paymentInfo . ' : ' . $pm_desc->bankName .
													"</option>";
										}
									}
								}
							?>
							<option value="new-method"><?php _e( 'New Method', 'wc-bna-gateway' ); ?></option>
						</select>
					<?php } ?>
					
					<div class="bna-payment-method-eft">
						<div class="bna-text-required">* <?php _e( 'Required fields', 'wc-bna-gateway' ); ?></div>
						
						<div class="bna-input-wrapper">
							<div class="bna-input-label"><?php _e( 'Bank Name', 'wc-bna-gateway' ); ?> <span class="required">*</span></div>
							<select id="bank_name" name="bank_name" class="input-text"></select>					
						</div>
						
						<div class="bna-input-wrapper">
							<div class="bna-input-label"><?php _e( 'Institution Number', 'wc-bna-gateway' ); ?> <span class="required">*</span></div>
							<input class="bna-input" type="text" id="bank_number" name="bank_number" 
								autocomplete="off" placeholder="" onkeyup="return input_test(this);" maxlength="3" >
						</div>
						
						<div class="bna-two-inputs-wrapper">
							<div class="bna-input-wrapper">
								<div class="bna-input-label"><?php _e( 'Account Number', 'wc-bna-gateway' ); ?> <span class="required">*</span></div>
								<input class="bna-input" type="text" name="account_number" 
									autocomplete="off" placeholder="" onkeyup="return input_test(this);" maxlength="8" >
							</div>
							
							<div class="bna-input-wrapper">
								<div class="bna-input-label"><?php _e( 'Transit Number', 'wc-bna-gateway' ); ?> <span class="required">*</span></div>
								<input  class="bna-input" type="text" name="transit_number" 
									autocomplete="off" placeholder=""  onkeyup="return input_test(this);" maxlength="5" >
							</div>
						</div>
						
						<?php if ( is_user_logged_in() ) { ?>
							<label class="bna-checkbox-container">
								<input type="checkbox" name="save-eft">
								<span class="checkmark"></span>
								<?php _e( 'Save this payment method for your next purchases.', 'wc-bna-gateway' ); ?>
							</label>
						<?php } ?>
					</div>
					
				</div>
			<?php } ?>
			
			<!-- e-Transfer -->
			<?php if ( ! empty( $bna_gateway_settings['bna-payment-method-e-transfer'] ) && $bna_gateway_settings['bna-payment-method-e-transfer'] === 'yes' && in_array( $woo_currency, BNA_E_TRANSFER_ALLOWED_CURRENCY ) ) { ?>
			<?php $has_payment_method = true; ?>
				<div class="bna-payment-method__item">
					<div class="bna-checkout-radio" data-payment-type="e-transfer"></div>
					<?php _e( 'E-Transfer', 'wc-bna-gateway' ); ?>
					<div class="bna-checkout-images">
						<img src="<?php echo BNA_PLUGIN_DIR_URL . 'assets/img/etransfer.svg'; ?>" alt="<?php _e( 'E-Transfer', 'wc-bna-gateway' ); ?>" />
					</div>
				</div>			
				<div class="bna-payment-method__content">		
					<div class="bna-input-wrapper">
						<div class="bna-input-label"><?php _e( 'Interac Email', 'wc-bna-gateway' ); ?> <span class="required">*</span></div>
						<input class="bna-input" type="text" placeholder="login@domain" name="email_transfer" value="<?php echo wp_get_current_user()->user_email; ?>" maxlength="100" <?php if ( is_user_logged_in() ) echo 'readonly'; ?>>
					</div>
					<div class="pm-et-block"></div>		
				</div>
			<?php } ?>	
			
			<input type="hidden" id="payment_type" name="payment-type" value="">
			
			<?php
			if ( $has_payment_method === true ) {
				if ( is_user_logged_in() ) { include "tpl_checkout_subscription_fields.php"; }
			}	
			?>
			
			<label class="bna-checkbox-container">
				<input type="checkbox" name="i-agree" checked required>
				<span id="checkmark_privacy_policy" class="checkmark"></span>
				<?php _e( 'I have read and agree to the terms presented in the ', 'wc-bna-gateway' ); ?>
				<a class="woocommerce-privacy-policy-link" href="<?php echo ! empty( $bna_gateway_settings['bna-recurring-pp-link'] ) ? esc_url( $bna_gateway_settings['bna-recurring-pp-link'] ) : '#'; ?>">
					<?php _e( 'Recurring Payment Agreement.', 'wc-bna-gateway' ); ?>
				</a>
			</label>
			
			<!--<label class="bna-checkbox-container">
				<input type="checkbox" name="save_customer">
				<span class="checkmark" id="checkmark_save_customer"></span>
				<?php //_e( 'Save customer?', 'wc-bna-gateway' ); ?>
			</label>-->
			
		</div>
	</div>

</fieldset>

<?php
if ( $bna_gateway_settings['applyFee'] === 'yes' ) {
	?>
	<script>
	(function() {
		let paymentMethod = document.querySelector(".wc_payment_methods");
		paymentMethod.addEventListener('click', event => {
			let bnaPayment = document.getElementById('payment_method_bna_gateway');
			bnaPayment.checked ? addFees() : removeFees();
			return false;
		});
		
		paymentMethod.click();
	})();	
	</script>
	<?php
}
?>

<script>
(function() {
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

	if ( jQuery('#billing_phone').length > 0 ) {
		let input_phone = document.querySelector('#billing_phone');
		input_phone.addEventListener('keyup', event => {
			event.preventDefault();
			input_phone.value = input_phone.value.replace(/[^\d,]/g, "");
		}, true);
	}
	
	if ( jQuery('#checkout').length > 0 ) {
		let input_submit = document.querySelector('.checkout');
		input_submit.addEventListener('submit', event => {
			event.preventDefault();
			document.querySelector('#billing_address_1').value = document.querySelector('#billing_street_name').value;
			document.querySelector('#billing_address_2').value = 'street #' + document.querySelector('#billing_street_number').value + 
				(document.querySelector('#billing_apartment').value.length ? ', apt. ' + document.querySelector('#billing_apartment').value : '');
			document.querySelectorAll('.woocommerce-billing-fields__field-wrapper input, .woocommerce-billing-fields__field-wrapper select').
				forEach((function(x){ x.removeAttribute('disabled');}))
		}, true);
	}

	let btn_qplus = document.getElementById('qtyminus');
	btn_qplus.addEventListener('click', event => {
		let currentValue = document.getElementById("setting_number_of_payment");
		currentValue.value = currentValue.value > 1 ? parseInt(currentValue.value) - 1 : 1;
		document.getElementById('numberOfPayments').value = number_of_payment.value;	
	});

	let btn_qmin = document.getElementById('qtyplus');
	btn_qmin.addEventListener('click', event => {
		let currentValue = document.getElementById("setting_number_of_payment");
		currentValue.value = parseInt(currentValue.value) + 1;
		document.getElementById('numberOfPayments').value = number_of_payment.value;
	});

	var first_payment_date = document.getElementById('setting_first_payment_date');
	let btn_immediately = document.getElementById('btn_immediately');
	btn_immediately.addEventListener('click', event => {
		first_payment_date.disabled=true;
		first_payment_date.classList.remove('activate_input');
		//first_payment_date.value = new Date().toISOString().slice(0, 10);
		document.getElementById('startDate').value = "<?php echo BNA_SUBSCRIPTION_SETTING_STARTDATE;?>";
	});

	var tomorrow1 = new Date();
	tomorrow1.setDate(tomorrow1.getDate() + 1);
	let btn_firstPayment = document.getElementById('btn_firstPayment');
	btn_firstPayment.addEventListener('click', event => {
		first_payment_date.disabled=false;
		//first_payment_date.classList.add('activate_input');
		first_payment_date.value = tomorrow1.toISOString().slice(0, 10);	
		document.getElementById('startDate').value = first_payment_date.value.toString();
	});

	var number_of_payment = document.getElementById('setting_number_of_payment');
	number_of_payment.addEventListener('change', event => {
		input_test(event.target);
		document.getElementById('numberOfPayments').value = number_of_payment.value;
	});

	let btn_noLimit = document.getElementById('btn_noLimit');
	btn_noLimit.addEventListener('click', event => {
		number_of_payment.disabled=true;
		number_of_payment.classList.remove('activate_input');
		document.getElementById('qtyminus').disabled=true;
		document.getElementById('qtyplus').disabled=true;
		document.getElementById('numberOfPayments').value = "<?php echo BNA_SUBSCRIPTION_SETTING_NUMPAYMENT;?>";
	});
	
	let btn_numPayment = document.getElementById('btn_numPayment');
	btn_numPayment.addEventListener('click', event => {
		number_of_payment.disabled=false;
		number_of_payment.classList.add('activate_input');
		document.getElementById('qtyminus').disabled=false;
		document.getElementById('qtyplus').disabled=false;
		document.getElementById('numberOfPayments').value = number_of_payment.value;
	});

	let billing_duration = document.getElementById('btn_noLimit');
	billing_duration.addEventListener('change', event => {
		document.getElementById('numberOfPayments').value = 0;
	});
	
	//paymentMethod.click();

})();	
</script>

<script type="module">
	(function($) {	
		$('#paymentMethodCC').selectWoo();	
		$('#ssRepeat').selectWoo();
		$('#paymentMethodDD').selectWoo();
		$('#bank_name').selectWoo();
		$('#paymentMethodCC, #ssRepeat, #paymentMethodDD').on("select2:open", function(e) {
		  $('.select2-search__field').css('display', 'none');
		});
		
		// validation before send order
		$('#place_order').on('click', function(event) {
			$('.bna-input:visible:enabled, input.input-text:not(#billing_company, #billing_apartment)').each(function(i) {
				if ( $(this).val() == 0 ) {
					$(this).addClass('invalid');
				} else {
					$(this).removeClass('invalid');
				}
			});
		});
		$('input.input-text').on('blur keyup', function() {
			if ( $(this).val().length >= 3 ) {
				$(this).removeClass('invalid');
			}
		});
		
		//var minDate = $('#setting_first_payment_date').datepicker('getDate'); // new Date(),
		var tomorrow2 = new Date();
		tomorrow2.setDate(tomorrow2.getDate() + 1);
		$('#setting_first_payment_date').datepicker({
			dateFormat: 'yyyy-mm-dd',
			autoClose: true,
			minDate: tomorrow2,
			startDate: tomorrow2,
			setDate: tomorrow2,
			onSelect: function(dateText) {
				$('#startDate').val(dateText);
			}
		});
		
		// select payment method (cc, i-transfer, eft, google pay, apple pay)
		$('.bna-payment-methods .bna-checkout-radio').click(function(){
			$(this).parent().parent().find('.bna-checkout-radio.selected').removeClass('selected');	
			if ( $('.bna-payment-method__content').is(':visible') ) { $('.bna-payment-method__content').css('display', 'none'); }
			$(this).addClass('selected');
			$(this).parent().next().css('display', 'block');
			$(this).parent().next().find('select.bna-checkout-select-card').css({'visibility': 'visible', 'opacity': '1'});
			
			$('#payment_type').val( $(this).data('payment-type') );
			
			// open if only 'new-card'
			if ( $('#paymentMethodCC').is(':visible') ) {
				if ( $('#paymentMethodCC').val() === 'new-card' ) {
					$('.bna-payment-method__content .bna-payment-method-cards').addClass('bna-active');
				}
			}
			
			// show if the user is not logged
			if ( $('#paymentMethodCC').length === 0 ) {
				$('.bna-payment-method__content .bna-payment-method-cards').addClass('bna-active');
			}
			
			// open if only 'new-method'
			if ( $('#paymentMethodDD').is(':visible') ) {
				if ( $('#paymentMethodDD').val() === 'new-method' ) {
					$('.bna-payment-method__content .bna-payment-method-eft').addClass('bna-active');
				}
			}
			
			// show if the user is not logged
			if ( $('#paymentMethodDD').length === 0 ) {
				$('.bna-payment-method__content .bna-payment-method-eft').addClass('bna-active');
			}
			
			// remove class invalid
			$('.bna-input:hidden').each(function(i) {
				$(this).removeClass('invalid');
			});
			
			if (isFeeEnabled) { addFees(); }
			
			let position = jQuery(this).parent().offset();	
			var fixed_offset = 70;	
			jQuery('html,body').stop().animate({ scrollTop: position.top - fixed_offset }, 1000);
		});
		
		// select card or add new			
		$('#paymentMethodCC').on("select2:select", function(e) {			
			let data = e.params.data;
			if (data.id === 'new-card') {
				$('.bna-payment-method__content .bna-payment-method-cards').addClass('bna-active');
			} else {
				$('.bna-payment-method__content .bna-payment-method-cards').removeClass('bna-active');
			}
		});
		$('#paymentMethodCC').on("select2:opening", function(e) {
			$('.select2-container--open .select2-dropdown .select2-search--dropdown').css('display', 'none');
		});	
			
		
		// select eft method or add new
		$('#paymentMethodDD').on("select2:select", function(e) {
		  let data = e.params.data;
		  if (data.id === 'new-method') {
				$('.bna-payment-method__content .bna-payment-method-eft').addClass('bna-active');
			} else {
				$('.bna-payment-method__content .bna-payment-method-eft').removeClass('bna-active');
			}
		});
		
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
		
		// select 'checkbox' recurring
		$('.bna-checkbox-container input[name="create_subscription"]').on('change', function(event) {
			if (event.currentTarget.checked) {
				$(this).parent().next().addClass('bna-active');
				$('#checkmark_privacy_policy').css('left', '0px');
			} else {
				$(this).parent().next().removeClass('bna-active');
				$('#checkmark_privacy_policy').css('left', '20px');
			}
		});
		
		// select recurring period
		$('#ssRepeat').on("select2:select", function(e) {
			$('#recurring').val( $(this).val() );
		});
			
		// validation fields cc
		$('input[name="cc_holder"]').on('blur keyup', function() {
			if ( $(this).val().length >= 3 ) {
				$(this).removeClass('invalid');
			} else {
				$(this).addClass('invalid');
			}
		});
			
		const credit=document.getElementById('credit-card-number');
		if ( credit !== null ) { make_credit_card_input(credit); }
		const cvv=document.getElementById('cvv');
		if ( cvv !== null ) { make_cvv_input(cvv); }	
		const expiration=document.getElementById('expiration');
		if ( expiration !== null ) { make_expiration_input(expiration); }
		
		$('input[name="cc_number"]').on('blur keyup', function() {
			let cc = $(this).val();
			cc = cc.replace(/[^0-9]+/g,'');
			let typeArray=checkType(cc);
			
			if (typeArray.length==1) {			
				if (typeArray[0].type !== undefined && typeArray[0].type) {
					if (typeArray[0].type != $(this).next().data('img')) {
						let cardName = 'credit_card';
						if (typeArray[0].type == 'visa') {
							cardName = 'visaCard';
						} else if (typeArray[0].type == 'master-card') {
							cardName = 'masterCard';
						} else if (typeArray[0].type == 'american-express') {
							cardName = 'americanExpress';
						} else if (typeArray[0].type == 'discover') {
							cardName = 'discoverCard';
						}
										
						$(this).next().html('<img data-img="' + typeArray[0].type + '" src="<?php echo BNA_PLUGIN_DIR_URL ?>assets/img/' + cardName + '.svg">');
					}
				} else { $(this).next().html(''); }
			} else { $(this).next().html(''); }
		});
		
		// validation fields eft
		$('input[name="bank_number"]').on('blur keyup', function() {
			if ( $(this).val().length >= 1 ) {
				//$(this).removeClass('invalid');
			} else {
				$(this).addClass('invalid');
			}
		});
		$('input[name="account_number"]').on('blur keyup', function() {
			if ( $(this).val().length >= 3 ) {
				$(this).removeClass('invalid');
			} else {
				$(this).addClass('invalid');
			}
		});
		$('input[name="transit_number"]').on('blur keyup', function() {
			if ( $(this).val().length >= 3 ) {
				$(this).removeClass('invalid');
			} else {
				$(this).addClass('invalid');
			}
		});
		
	})(jQuery);	
</script>
