<?php
/**
 * Woocommerce BNA Gateway
 *
 * @author 	BNA
 * @category 	'Payment checkout fileds' Template 
 * @version     1.0
 */

if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly
?>

<fieldset id="wc-<?= esc_attr( $this->id ); ?>-cc-form" class="wc-credit-card-form wc-payment-form" >
	<div>
		<div class="bna-payment-methods">
			
			<!-- Card -->
			<div class="bna-payment-method__item">
				<div class="bna-checkout-radio" data-payment-type="card"></div>
				<?php _e( 'Credit Card', 'wc-bna-gateway' ); ?>
				<div class="bna-checkout-images">
					<img src="<?php echo BNA_PLUGIN_DIR_URL . 'img/VISA_Logo.png'; ?>" alt="<?php _e( 'VISA_Logo', 'wc-bna-gateway' ); ?>" />
					<img src="<?php echo BNA_PLUGIN_DIR_URL . 'img/Mastercard_Logo.png'; ?>" alt="<?php _e( 'Mastercard_Logo', 'wc-bna-gateway' ); ?>" />
					<img src="<?php echo BNA_PLUGIN_DIR_URL . 'img/AMEX_Logo.png'; ?>" alt="<?php _e( 'AMEX_Logo', 'wc-bna-gateway' ); ?>" />
				</div>
			</div>
			<div class="bna-payment-method__content">
				<div class="bna-payment-method__content-title"><?php _e( 'Select Credit Card you want to pay', 'wc-bna-gateway' ); ?></div>
				<select class="bna-checkout-select-card" id="paymentMethodCC" name="paymentMethodCC" aria-placeholder="<?php _e( 'Please choose...', 'wc-bna-gateway' ); ?>">			
					<?php
						if ( is_array($paymentMethods) ) {
							foreach ($paymentMethods as $pm_val) {
								if ( $pm_val->paymentType === 'card' ) {
									echo 	"<option value=\"".$pm_val->paymentMethodId."\">" .
												$pm_val->paymentType . ' : ' . $pm_val->paymentInfo .
											"</option>";
								}
							}
						}
					?>
					<option value="new-card"><?php _e( 'Add New Card', 'wc-bna-gateway' ); ?></option>
				</select>
				
				<div class="tpl-payment-method-cards">
					<div class="tpl-text-required">* <?php _e( 'Required fields', 'wc-bna-gateway' ); ?></div>
					
					<div class="tpl-input-wrapper">
						<div class="tpl-input-label"><?php _e( 'Cardholder Name', 'wc-bna-gateway' ); ?> <span class="required">*</span><br>
						<span class="tpl-font-italic"><?php _e( '(the exact name as it appears on the front of your credit card)', 'wc-bna-gateway' ); ?></span></div>
						<input class="tpl-input" type="text" name="cc_holder" autocomplete="off" maxlength="100" placeholder="FIRSTNAME LASTNAME" >
					</div>
					
					<div class="tpl-two-inputs-wrapper">
						<div class="tpl-input-wrapper">
							<div class="tpl-input-label"><?php _e( 'Card Number', 'wc-bna-gateway' ); ?> <span class="required">*</span></div>
							<input class="tpl-input" type="text" name="cc_number" autocomplete="off" maxlength="18" placeholder="0000000000000000"
								onkeyup="return input_test(this);">
						</div>
						
						<div class="tpl-input-wrapper">
							<div class="tpl-input-label"><?php _e( 'Expiry Date', 'wc-bna-gateway' ); ?> <span class="required">*</span></div>
							<input class="tpl-input" type="text" name="cc_expire" 
								autocomplete="off" placeholder="MM/YY" onkeyup="return input_test(this);" maxlength="5">
						</div>
					</div>
						
					<div class="tpl-three-inputs-wrapper">
						<div class="tpl-input-wrapper">
							<div class="tpl-input-label"><?php _e( 'CVC', 'wc-bna-gateway' ); ?> <span class="required">*</span></div>
							<input  class="tpl-input" type="text" name="cc_code" autocomplete="off" placeholder="CVC" maxlength="3" 
								onkeyup="return input_test(this);">
						</div>
						<div class="tpl-CVC-text-wrapper">
							<div class="tpl-CVC-text">
								<?php _e( 'CVC (CVV, CCV, SVC or CSC) is a card security verification code. Three or four digits printed, not embossed, on the back of the card. ', 'wc-bna-gateway' ); ?>
							</div>
						</div>
						<div class="tpl-CVC-img-wrapper">
							<img class="tpl-CVC-img" src="<?php echo BNA_PLUGIN_DIR_URL . 'img/Credit_Card_SVC.png'; ?>" />
						</div>
					</div>
					
					<label class="bna-checkbox-container">
						<input type="checkbox" name="save-credit-card">
						<span class="checkmark"></span>
						<?php _e( 'Save Credit Card', 'wc-bna-gateway' ); ?>
					</label>
				</div>			
			</div>
					
			<!-- EFT -->
			<div class="bna-payment-method__item">
				<div class="bna-checkout-radio"  data-payment-type="eft"></div>
				<?php _e( 'Direct Payment  from your Bank Account', 'wc-bna-gateway' ); ?>
				<div class="bna-checkout-images">
					<img src="<?php echo BNA_PLUGIN_DIR_URL . 'img/pm_dc 3.png'; ?>" alt="<?php _e( 'Direct Payment  from your Bank Account', 'wc-bna-gateway' ); ?>" />
				</div>
			</div>
			<div class="bna-payment-method__content">
				<div class="bna-payment-method__content-title"><?php _e( 'Saved payment methods', 'wc-bna-gateway' ); ?></div>
				<select class="bna-checkout-select-card" id="paymentMethodDD" name="paymentMethodDD" aria-placeholder="<?php _e( 'Please choose...', 'wc-bna-gateway' ); ?>">					
					<?php
						if ( is_array($paymentMethods) ) {
							foreach ($paymentMethods as $pm_val) {
								if ( $pm_val->paymentType === 'eft' ) {
									echo 	"<option value=\"".$pm_val->paymentMethodId."\">" .
												$pm_val->paymentInfo . ' : ' . $data->institutionNumber .
											"</option>";
								}
							}
						}
					?>
					<option value="new-method"><?php _e( 'Add New Method', 'wc-bna-gateway' ); ?></option>
				</select>
				
				<div class="tpl-payment-method-eft">
					<div class="tpl-text-required">* <?php _e( 'Required fields', 'wc-bna-gateway' ); ?></div>
					
					<div class="tpl-input-wrapper">
						<div class="tpl-input-label"><?php _e( 'Bank Name', 'wc-bna-gateway' ); ?> <span class="required">*</span></div>
						<select id="bank_name" name="bank_name" class="input-text"></select>					
					</div>
					
					<div class="tpl-input-wrapper">
						<div class="tpl-input-label"><?php _e( 'Institution Number', 'wc-bna-gateway' ); ?> <span class="required">*</span></div>
						<input class="tpl-input" placeholder="000" type="text" id="institutionNumber" name="institutionNumber" value="" maxlength="3" 
									onkeyup="return digitValid(this);" autocomplete="off" >
					</div>
					
					<div class="tpl-two-inputs-wrapper">
						<div class="tpl-input-wrapper">
							<div class="tpl-input-label"><?php _e( 'Account Number', 'wc-bna-gateway' ); ?> <span class="required">*</span></div>
							<input class="tpl-input" placeholder="000000000000" type="text" id="accountNumber" name="accountNumber" value="" maxlength="12" 
								onkeyup="return digitValid(this);" autocomplete="off" >
						</div>
						
						<div class="tpl-input-wrapper">
							<div class="tpl-input-label"><?php _e( 'Transit Number', 'wc-bna-gateway' ); ?> <span class="required">*</span></div>
							<input class="tpl-input" placeholder="00000" type="text" id="transitNumber" name="transitNumber" value="" maxlength="5" 
								onkeyup="return digitValid(this);" autocomplete="off" >
						</div>
					</div>
					
					<label class="bna-checkbox-container">
						<input type="checkbox" name="save-credit-card">
						<span class="checkmark"></span>
						<?php _e( 'Save EFT Method', 'wc-bna-gateway' ); ?>
					</label>
				</div>
				
			</div>
			
			<!-- e-Transfer -->
			<div class="bna-payment-method__item">
				<div class="bna-checkout-radio" data-payment-type="e-transfer"></div>
				<?php _e( 'Interac e-Transfer', 'wc-bna-gateway' ); ?>
				<div class="bna-checkout-images">
					<img src="<?php echo BNA_PLUGIN_DIR_URL . 'img/pm_interac_etransfer 3.png'; ?>" alt="<?php _e( 'Interac e-Transfer', 'wc-bna-gateway' ); ?>" />
				</div>
			</div>			
			<div class="bna-payment-method__content">		
				<div class="tpl-input-wrapper">
					<div class="tpl-input-label"><?php _e( 'Interac Email', 'wc-bna-gateway' ); ?> <span class="required">*</span></div>
					<input class="tpl-input" type="text" placeholder="login@domain" name="email_transfer" value="<?php echo wp_get_current_user()->user_email; ?>" maxlength="100" readonly>
				</div>
				<div class="pm-et-block"></div>		
			</div>	
			
			<input type="hidden" id="payment_type" name="payment-type" value="">
			
			<?php include "tpl_checkout_subscription_fields.php"; ?>	
		</div>
	</div>
	<script type="module">
		(function($) {	
			$('#paymentMethodCC').select2();
			$('#ssRepeat').select2();
			$('#paymentMethodDD').select2();
			$('#bank_name').select2();
			
			$('#setting_first_payment_date').datepicker({
				dateFormat: 'yyyy-mm-dd',
				autoClose: true,
			});
			
			// select payment method (cc, i-transfer, eft, google pay, apple pay)
			$('.bna-payment-methods .bna-checkout-radio').click(function(){
				$(this).parent().parent().find('.bna-checkout-radio.selected').removeClass('selected');	
				if ( $('.bna-payment-method__content').is(':visible') ) { $('.bna-payment-method__content').css('display', 'none'); }
				$(this).addClass('selected');
				$(this).parent().next().css('display', 'block');
				$(this).parent().next().find('select.bna-checkout-select-card').css({'visibility': 'visible', 'opacity': '1'});
				
				$('#payment_type').val( $(this).data('payment-type') );
			});
			
			// select card or add new
			$('#paymentMethodCC').on("select2:select", function(e) {
			  let data = e.params.data;
			  if (data.id === 'new-card') {
					$('.bna-payment-method__content .tpl-payment-method-cards').addClass('tpl-active');
				} else {
					$('.bna-payment-method__content .tpl-payment-method-cards').removeClass('tpl-active');
				}
			});
			
			// select eft method or add new
			$('#paymentMethodDD').on("select2:select", function(e) {
			  let data = e.params.data;
			  if (data.id === 'new-method') {
					$('.bna-payment-method__content .tpl-payment-method-eft').addClass('tpl-active');
				} else {
					$('.bna-payment-method__content .tpl-payment-method-eft').removeClass('tpl-active');
				}
			});
			
			// select bank
			$('#bank_name').on("select2:select", function(e) {
				$('#institutionNumber').val( $(this).val() );
			});
			
			// select 'checkbox' recurring
			$('.bna-checkbox-container input[name="create_subscription"]').on('change', function(event) {
				if (event.currentTarget.checked) {
					$(this).parent().next().addClass('tpl-active');
				} else {
					$(this).parent().next().removeClass('tpl-active');
				}
			});
			
			// select recurring period
			$('#ssRepeat').on("select2:select", function(e) {
				$('#recurring').val( $(this).val() );
			});
		})(jQuery);	
	</script>

</fieldset>

<script>
(function() {	

	let globalTotal = "<?= WC()->cart->total; ?>";
	let curSymbol = "<?= get_woocommerce_currency_symbol(); ?>";

	let paymentMethod = document.querySelector(".wc_payment_methods");
	paymentMethod.addEventListener('click', event => {
		//let paylinks = document.getElementById('payment_method_paylinks_gateway');
		//paylinks.checked ? addFees() : removeFees();
		return false;
	});

	function changeSelectbox (value, selector)
	{
		if (value != 0) {
			document.querySelector(selector).style.display = 'none';
			document.querySelector('.save-pm-div').style.display = 'none';
			document.querySelector('.save-pm-checkbox').checked = false;
		} else {
			document.querySelector(selector).style.display = 'block';
			document.querySelector('.save-pm-div').style.display = 'block';
			document.querySelector('.save-pm-checkbox').checked = true;
			document.querySelectorAll('.woocommerce-billing-fields__field-wrapper input, .woocommerce-billing-fields__field-wrapper select').
				forEach((function(x){ x.removeAttribute('disabled');}))
		}

		if (value > 0) {
			document.querySelectorAll('.woocommerce-billing-fields__field-wrapper input, .woocommerce-billing-fields__field-wrapper select').
				forEach((function(x){ x.setAttribute('disabled', true);}));
		}


		addFees();

		return 0;
	}
	function digitValid(input) 
	{ 
		input.value = input.value.replace(/[^\d,]/g, "");
	};

	function roundAccurately (number, decimalPlaces) {
  		return Number(`${Math.round(`${number}e${decimalPlaces}`)}e-${decimalPlaces}`);
	}

	function addFees() 
	{
		let feeTab = document.querySelector('.fee-total');
		let feeSum, feeMult;
		let allFees = window.wc_gwpl_fee;

		if (!feeTab) {
			let tRef = document.querySelector('.shop_table').getElementsByTagName('tfoot')[0];
			feeTab = tRef.insertRow(1);
			feeTab.className = 'fee-total';
		}

		//switch (select_ctab.value) {
			//case '-1':
				feeSum = parseFloat(allFees.etransferFlatFee);
				feeMult = parseFloat(allFees.etransferPercentageFee);
				//break;			
			//case '1':
				//feeSum = parseFloat(allFees.creditCardFlatFee);
				//feeMult = parseFloat(allFees.creditCardPercentageFee);
				//break;
			//case '2':
				//feeSum = parseFloat(allFees.directDebitFlatFee);
				//feeMult = parseFloat(allFees.directDebitPercentageFee);
				//break;	
		//}
		let allFeeSum = parseFloat(globalTotal*feeMult/100) + feeSum;
		allFeeSum = roundAccurately(allFeeSum + parseFloat(allFeeSum*13/100), 2);

		feeTab.innerHTML = 
			'<th>BNA Fee (Includes HST)	</th>'
			+ '<td><strong><span class="woocommerce-Price-amount amount"><bdi>'
			+ allFeeSum.toFixed(2).replace('.', ',')
			+ '<span class="woocommerce-Price-currencySymbol">'
			+ curSymbol
			+ '</span></bdi></span></strong></td>';
		
		let totalTab = document.querySelector('.order-total');
		totalTab.innerHTML = 	
			'<th>Total</th>'
			+ '<td><strong><span class="woocommerce-Price-amount amount"><bdi>'
			+ parseFloat(parseFloat(globalTotal) + allFeeSum).toFixed(2).replace('.', ',')
			+ '<span class="woocommerce-Price-currencySymbol">'
			+ curSymbol
			+ '</span></bdi></span></strong></td>';
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

	//let select_ctab = document.querySelector('.checkout-tab');
	//select_ctab.addEventListener('change', event => {
		//event.preventDefault();
		//let tabs = document.querySelector('.tabs');
		//tabs.querySelector('.active').classList.remove('active');
		//tabs.querySelectorAll('.tab')[select_ctab.options.selectedIndex].classList.add('active');

		//changeSelectbox (select_ctab.value > 0 ? 0 : select_ctab.value, '.pm-et-block');
	//}, true);

	let select_bankName = document.querySelector('#bank_name');
	let interval = setInterval(() => {
		if ( typeof window.bankName !== 'undefined' ) {
			let arBankName = (Object.entries(window.bankName)).sort(function(a,b){     
				if(a[1] > b[1]) return 1;
				if(a[1] < b[1]) return -1;
				return 0;
			});
			let options = '';
			for (i in arBankName) {
				options += '<option value="'+ arBankName[i][0] +'">' + arBankName[i][1] + '</option>';
			}
			options += '<option value="other">&lt;&lt; Other &gt;&gt;</option>'
			select_bankName.innerHTML += options;
			clearInterval(interval);
		}
	}, 500);

	select_bankName.addEventListener('click', event => {
		event.preventDefault();
        
        let iNum = document.querySelector('.institutionNumber');
        iNum.classList.remove('active');
        if (select_bankName.value == 'other') {
            iNum.classList.add('active');
        } 
    });
	//let select_pmcc = document.querySelector('.pm-cc');
	//select_pmcc.addEventListener('click', event => {
		//event.preventDefault();
		//changeSelectbox(select_pmcc.value != 0 ? 1: 0, '.pm-cc-block');
	//}, true);

	//let select_pmdd = document.querySelector('.pm-dd');
	//select_pmdd.addEventListener('click', event => {
		//event.preventDefault();
		//changeSelectbox(select_pmdd.value != 0 ? 1: 0, '.pm-dd-block');
	//}, true);

	let input_phone = document.querySelector('#billing_phone');
	input_phone.addEventListener('keyup', event => {
		event.preventDefault();
		input_phone.value = input_phone.value.replace(/[^\d,]/g, "");
	}, true);
	
	let input_submit = document.querySelector('.checkout');
	input_submit.addEventListener('submit', event => {
		event.preventDefault();
		document.querySelector('#billing_address_1').value = document.querySelector('#billing_street_name').value;
		document.querySelector('#billing_address_2').value = 'street #' + document.querySelector('#billing_street_number').value + 
			(document.querySelector('#billing_apartment').value.length ? ', apt. ' + document.querySelector('#billing_apartment').value : '');
		document.querySelectorAll('.woocommerce-billing-fields__field-wrapper input, .woocommerce-billing-fields__field-wrapper select').
			forEach((function(x){ x.removeAttribute('disabled');}))
	}, true);

	//let a_settings = document.getElementById('showHideSettings');
	//a_settings.addEventListener('click', event => {
		//event.preventDefault();
		//event.stopPropagation();
		//let settingTab = document.querySelector('.stabs');
		//settingTab.style.display = settingTab.style.display === 'none' || settingTab.style.display === '' ? 'block' : 'none';

		//$('.datepicker-here').datepicker({minDate: new Date()});
		
		//var first_payment_date_1 = document.getElementById('datepickers-container');
		//first_payment_date_1.addEventListener('click', event => {
			//document.getElementById('startDate').value = first_payment_date.value;
		//});
	//});

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
	first_payment_date.addEventListener('change', event => {
		document.getElementById('startDate').value = first_payment_date.value;
	});

	let btn_immediately = document.getElementById('btn_immediately');
	btn_immediately.addEventListener('click', event => {
		first_payment_date.disabled=true;
		first_payment_date.classList.remove('activate_input');
		//first_payment_date.value = new Date().toISOString().slice(0, 10);
		document.getElementById('startDate').value = "<?=BNA_SUBSCRIPTION_SETTING_STARTDATE;?>";
	});

	let btn_firstPayment = document.getElementById('btn_firstPayment');
	btn_firstPayment.addEventListener('click', event => {
		first_payment_date.disabled=false;
		//first_payment_date.classList.add('activate_input');
		first_payment_date.value = new Date().toISOString().slice(0, 10);	
		document.getElementById('startDate').value = first_payment_date.value.toString();
	});

	var number_of_payment = document.getElementById('setting_number_of_payment');
	number_of_payment.addEventListener('change', event => {
		digitValid (event.target);
		document.getElementById('numberOfPayments').value = number_of_payment.value;
	});

	let btn_noLimit = document.getElementById('btn_noLimit');
	btn_noLimit.addEventListener('click', event => {
		number_of_payment.disabled=true;
		number_of_payment.classList.remove('activate_input');
		document.getElementById('qtyminus').disabled=true;
		document.getElementById('qtyplus').disabled=true;
		document.getElementById('numberOfPayments').value = "<?=BNA_SUBSCRIPTION_SETTING_NUMPAYMENT;?>";
	});
	
	let btn_numPayment = document.getElementById('btn_numPayment');
	btn_numPayment.addEventListener('click', event => {
		number_of_payment.disabled=false;
		number_of_payment.classList.add('activate_input');
		document.getElementById('qtyminus').disabled=false;
		document.getElementById('qtyplus').disabled=false;		
	});

	//let ssRepeat = document.getElementById('ssRepeat');
	//ssRepeat.addEventListener('change', event => {
		//document.getElementById('recurring').value = ssRepeat.value;
		//console.log(document.getElementById('recurring').value);
		//console.log(ssRepeat.value);
	//});

	let billing_duration = document.getElementById('btn_noLimit');
	billing_duration.addEventListener('change', event => {
		document.getElementById('numberOfPayments').value = 0;
	});
	
	paymentMethod.click();

})();	
</script>

<style scoped="scoped">
	/*.form-row.form-row-small {
		width: 40%;
	}
	.save-pm-div{
		text-align: left;
		margin-top: 15px;
	}
	#billing_address_1_field,
	#billing_address_2_field {
		display: none !important;
	}
	.institutionNumber {
		display: none;
	}
	.active {
		display: block;
	}
	.radio-button input:hover + span {
		border-right-color: #40a9ff;
		color:#fff;
	}
	#ck-button,
	.ssRepeat {
		margin:4px;
		background-color: unset;
		border-radius:4px;
		border:1px solid #D0D0D0;
		overflow:auto;
		float:left;
	}
	#ck-button:hover {
		margin:4px;
		border-radius:4px;
		border:1px solid #40a9ff;
		overflow:auto;
		float:left;
		color:#40a9ff;
	}
	#ck-button label {
		float:left;
	}
	#ck-button label span {
		text-align:center;
		padding:3px 0px;
		display:block;
	}
	#ck-button label input {
		position:absolute;
		left:-900vh;
	}
	#ck-button input + span {
		padding: 5px 10px;
		background-color:unset;
		color:unset;
	}
	#ck-button input:checked + span {
		background-color:#40a9ff;
		color:#fff;
	}
	.stabs >.tab > div > label {
		line-height: 1;
    	margin-top: 5px;
	}
	.stabs {
		display: none;
	}
	.ssRepeat {
		border: 1px solid #43454b !important;
		font-size: 14px !important;
		background-color: #fff !important;
		padding: 3px 3px;
	} 
	#setting_number_of_payment {
		width: 75%;
	}
	input.qtyplus , input.qtyminus { 
		padding: 8px 0px;
		width: 25px;
		background-color: #40a9ff;
		color: #fff;
	}
	.activate_input {
		background-color: #fff !important;
	}*/
</style>
