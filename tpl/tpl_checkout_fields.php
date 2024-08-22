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

<fieldset id="wc-<?= esc_attr( $this->id ); ?>-cc-form" class="wc-credit-card-form wc-payment-form" style="background:transparent;">

	<?php do_action( 'woocommerce_credit_card_form_start', $this->id ); ?>
	<div>
		<label for="option-tabs"><h3>Choose payment type:</h3>
			<div class="tabs">
				
				<select class="checkout-tab input-text" name="paymentType" aria-placeholder="Please choose...">
					<option id="tab-btn-1" value="1" selected>Credit card</option>
					<option id="tab-btn-2" value="2">Direct Debit</option>
					<option id="tab-btn-3" value="-1">ETransfer</option>
				</select>

				<div class="tab active" id="content-1">
					<div class="form-row form-row-wide"><label>Saved payment methods</label>
						<select class="pm-cc input-text" name="paymentMethodCC" aria-placeholder="Please choose...">
							<option id="tab-btn-1" value="0" selected>New Card</option>
							<?php
								if ( is_array($paymentMethods) ) {
									foreach ($paymentMethods as $pm_val) {
										if ( in_array($pm_val->paymentType, ['MASTERCARD', 'VISA', 'AMEX']) ) {
											echo 	"<option value=\"".$pm_val->paymentMethodId."\">" .
														$pm_val->paymentInfo . ':' . $pm_val->paymentType .
													"</option>";
										}
									}
								}
							?>
						</select>
					</div>
					<div class="pm-cc-block">
						<div class="form-row form-row-wide">
							<label>Card Holder <span class="required">*</span></label>
							<input type="text" name="cc_holder" autocomplete="off" maxlength="100" placeholder="FIRSTNAME LASTNAME" value="">
						</div>
						<div class="form-row form-row-wide">
							<label>Card Number <span class="required">*</span></label>
							<input type="text" name="cc_number" autocomplete="off" maxlength="18" placeholder="0000000000000000"
								onkeyup="return digitValid(this);">
						</div>
						<div class="form-row form-row-first">
							<label>Expiry Date <span class="required">*</span></label>
							<input class="form-row form-row-small" type="text" name="cc_expire_month" autocomplete="off" placeholder="MM" 
								onkeyup="return digitValid(this);" maxlength="2">
							<span class="">/</span>
							<input class="form-row form-row-small" type="text" name="cc_expire_year" autocomplete="off" placeholder="YY" 
								onkeyup="return digitValid(this);" maxlength="2">
						</div>
						<div class="form-row form-row-last">
							<label>Card Code (CVC) <span class="required">*</span></label>
							<input type="password" name="cc_code" autocomplete="off" placeholder="CVC" maxlength="4" 
								value="" onkeyup="return digitValid(this);">
						</div>
					</div>
					<div class="clear"></div>
				</div>
				<div class="tab" id="content-2">
					<div class="form-row form-row-wide">
						<label>Saved payment methods</label>
						<select class="pm-dd input-text" name="paymentMethodDD" aria-placeholder="Please choose...">
							<option id="tab-btn-1" value="0" selected>New Debit</option>
							<?php
								if ( is_array($paymentMethods) ) {
									foreach ($paymentMethods as $pm_val) {
										$data = json_decode($pm_val->paymentDescription);
										if ( in_array($pm_val->paymentType, ['DIRECT-DEBIT']) ) {
											echo 	"<option id=\"tab-btn-1\" value=\"".$pm_val->paymentMethodId."\">" .
														$pm_val->paymentInfo . ':' . $data->institutionNumber .
													"</option>";
										}
									}
								}
							?>
						</select>
					</div>
					<div class="pm-dd-block">
						<div class="form-row form-row-wide">
							<label>Bank Name<span>*</span></label>
							<select id="bank_name" name="bank_name" class="input-text"></select>
							<div class="institutionNumber">
								<label>Institution Number <span>*</span></label>
								<input placeholder="000" type="text" id="institutionNumber" name="institutionNumber" value="" maxlength="3" 
									onkeyup="return digitValid(this);" autocomplete="off" >
							</div>
						</div>
						<div class="form-row form-row-wide">
							<label>Account Number <span>*</span></label>
							<input placeholder="000000000000" type="text" id="accountNumber" name="accountNumber" value="" maxlength="12" 
								onkeyup="return digitValid(this);" autocomplete="off" >
						</div>
						<div class="form-row form-row-wide">
							<label>Transit Number <span>*</span></label>
							<input placeholder="00000" type="text" id="transitNumber" name="transitNumber" value="" maxlength="5" 
								onkeyup="return digitValid(this);" autocomplete="off" >
						</div>
					</div>
					<div class="clear"></div>
				</div>
				<div class="tab" id="content-3">
					<div class="form-row form-row-wide">
						<label>E-mail<span>*</span></label>
						<input class="input-text" placeholder="login@domain" name="email_transfer" value="<?=wp_get_current_user()->user_email;?>" maxlength="100" readonly>
					</div>
					<div class="pm-et-block"></div>
				</div>
			</div>
		</label>
	</div>
	<div class="clear"></div>
	
	<div class="save-pm-div form-row form-row-wide">
		<label>
			<input class="save-pm-checkbox" type="checkbox" name="save_payment" id="save_payment" checked>
			Save this type of payment
		</label>
	</div>

	<div class="save-pm-div form-row form-row-wide">
		<label>
			<input type="hidden" id="recurring" name="recurring" value="<?= BNA_SUBSCRIPTION_SETTING_REPEAT; ?>">
			<input type="hidden" id="startDate" name="startDate" value="<?= BNA_SUBSCRIPTION_SETTING_STARTDATE;?>">
			<input type="hidden" id="numberOfPayments" name="numberOfPayments" value="<?= BNA_SUBSCRIPTION_SETTING_NUMPAYMENT; ?>">
			<input class="save-pm-checkbox" type="checkbox" name="create_subscription" checked>
			Create subscription (<a href="#" id="showHideSettings">show settings</a>)
		</label>
	</div>

	<div class="form-row form-row-wide">
		<div class="stabs">							
			<div class="tab">
				<h3>Subscription settings</h3>
				<div class="form-row form-row-wide">
					<label>Repeat <span class="required">*</span></label>
					<select class="ssRepeat" id="ssRepeat" name="ssRepeat" aria-placeholder="Please choose...">
						<?php
							$selected = BNA_SUBSCRIPTION_SETTING_REPEAT;
							$duration = ['day', 'week', 'two weeks', 'month', 'three month', 'six month', 'year'];
							$durOptions = ['daily', 'weekly', 'bi-weekly', 'monthly', 'every-3-months', 'every-6-months' , 'yearly'];

							foreach ($duration as $d_key => $d_val) {
								$attr = $durOptions[$d_key] == $selected ? 'selected' : '';
								echo "<option value='{$durOptions[$d_key]}' {$attr}>EVERY ".strtoupper($d_val)."</option>";
							}
						?>
					</select>
				</div>
				<div class="form-row form-row-wide">
					<label>FIRST PAYMENT</label>
					<div id="ck-button"><label><input type="radio" id="btn_immediately" name="setting_first_payment" class="radio-button" value="immediately" checked><span>IMMEDIATELY</span></label></div>
					<div id="ck-button"><label><input type="radio" id="btn_firstPayment" name="setting_first_payment" class="radio-button" value="set-date"><span>FIRST PAYMENT</span></label></div>
					<input type="text" class="datepicker-here" data-position="right top" id="setting_first_payment_date" name="setting_first_payment_date" 
						autocomplete="off" maxlength="15" placeholder="Select date" data-date-format="yyyy-mm-dd "  disabled>
				</div>
				<div class="form-row form-row-wide">
					<label>BILLING DURATION</label>
					<div id="ck-button"><label><input type="radio" id="btn_noLimit" name="setting_billing_duration" class="radio-button" value="immediately" checked><span>NO LIMIT</span></label></div>
					<div id="ck-button"><label><input type="radio" id="btn_numPayment" name="setting_billing_duration" class="radio-button" value="set-date"><span># OF PAYMENTS</span></label></div>
					<div class="quantity_inner">   
						<input type='text' value='1' name='setting_number_of_payment' id="setting_number_of_payment" disabled/>
						<input type='button' value='-' class='qtyminus' id='qtyminus' disabled/>
						<input type='button' value='+' class='qtyplus' id='qtyplus' disabled/> 
					</div>
				</div>
			</div>
		</div>
	</div>
	<div class="clear"></div>

	
	<div class="cards-field-form">
		<svg version="1.1" viewBox="0 0 130 25" xmlns="http://www.w3.org/2000/svg" xmlns:inkscape="http://www.inkscape.org/namespaces/inkscape" xmlns:sodipodi="http://sodipodi.sourceforge.net/DTD/sodipodi-0.dtd">
			<sodipodi:namedview id="base" bordercolor="#666666" borderopacity="1.0" inkscape:current-layer="layer1" />
			<g transform="translate(-60.579 -177.99)" inkscape:groupmode="layer" inkscape:label="Layer 1">
				<path d="m60.954 177.99h20.275v20.301h-20.65v-20.301zm19.525 0.75001h-19.15v18.801h19.15z" fill="#5c5c5c" inkscape:connector-curvature="0"/>
				<g fill-rule="evenodd">
					<path d="m67.535 179.63 4.1136 5.6283 3.5569 11.403h4.2141v-17.031l-3.5661 2.585-3.5306-2.585z" fill="#5c5c5c" inkscape:connector-curvature="0"/>
					<path d="m64.535 196.66 1.1787-6.3447 5.6594-4.1024 3.2588 10.447-1.7747-1e-4 -3.5483-2.598-3.5483 2.598z" fill="#b0cc1f" inkscape:connector-curvature="0"/>
					<path d="m62.388 179.63v2.271l2.598 3.5483-2.598 3.5483v7.6635h1.5768l1.2524-6.7409 5.9656-4.3244-4.361-5.9658z" fill="#00a1e3" inkscape:connector-curvature="0"/>
				</g>
				<path d="m92.699 192.68-5.5553-4.6827 5.5553-4.6743 1.2859 1.3029-4.0889 3.3546 4.0889 3.3967z" fill="#5c5c5c" inkscape:connector-curvature="0"/>
				<path d="m104.27 188.15c0 0.76831-0.10982 1.4352-0.32643 1.9952-0.21667 0.56-0.51788 1.0272-0.90618 1.3987-0.3883 0.36851-0.84419 0.64438-1.3732 0.82451-0.52628 0.17727-1.1004 0.26748-1.7224 0.26748-0.20832 0-0.41931-0.0141-0.62763-0.0424-0.2081-0.0281-0.3967-0.0618-0.56839-0.10679v3.2728h-2.0122v-11.651h1.7393l0.05052 1.2383c0.1717-0.214 0.349-0.41092 0.53181-0.58551 0.18299-0.1772 0.3828-0.32639 0.59679-0.45021 0.21661-0.12379 0.44729-0.21661 0.69218-0.2843 0.24772-0.0645 0.52631-0.0985 0.83291-0.0985 0.49541 0 0.93441 0.0985 1.32 0.29552 0.38541 0.19999 0.70922 0.4813 0.96802 0.84709 0.2618 0.3659 0.46158 0.80769 0.59958 1.3255 0.1378 0.5206 0.20532 1.1059 0.20532 1.7533zm-2.1106 0.0816c0-0.46151-0.0337-0.8527-0.0985-1.1791-0.0676-0.32371-0.16027-0.59101-0.28427-0.79933-0.12383-0.20828-0.27563-0.36018-0.45311-0.45589-0.18009-0.0985-0.38551-0.1463-0.61619-0.1463-0.34051 0-0.66432 0.1379-0.9766 0.4137-0.3096 0.27281-0.63881 0.64732-0.98492 1.12v3.6613c0.16041 0.0591 0.35729 0.10971 0.5909 0.14919 0.23361 0.0366 0.47001 0.0562 0.71212 0.0562 0.31799 0 0.6078-0.0648 0.8696-0.1971 0.26158-0.13219 0.48397-0.31789 0.66678-0.55989 0.18592-0.24201 0.32639-0.5376 0.42499-0.8865 0.0986-0.349 0.14923-0.74009 0.14923-1.1763zm9.1095 4.2551-0.0477-1.0976c-0.17742 0.1886-0.36044 0.36012-0.55171 0.51499-0.19421 0.15762-0.4052 0.2927-0.63592 0.40812-0.23089 0.11529-0.4813 0.20539-0.7542 0.27009-0.27298 0.0619-0.57129 0.0928-0.895 0.0928-0.42767 0-0.80479-0.0619-1.1285-0.18842-0.32639-0.12668-0.59659-0.3012-0.81608-0.52909-0.2195-0.22522-0.38841-0.4953-0.50102-0.81619-0.1125-0.31792-0.1687-0.66982-0.1687-1.0553 0-0.39402 0.0844-0.75971 0.25319-1.0946 0.16612-0.33503 0.42214-0.6248 0.76553-0.86691 0.34339-0.24201 0.77389-0.43061 1.2862-0.56851 0.51492-0.13779 1.1171-0.20531 1.8094-0.20531h1.0975v-0.50378c0-0.21392-0.031-0.40812-0.09-0.57982-0.0619-0.17448-0.15759-0.32068-0.29271-0.44178-0.13511-0.12101-0.30959-0.21372-0.52338-0.28142-0.21671-0.0647-0.4785-0.0984-0.79371-0.0984-0.49241 0-0.9821 0.0562-1.4633 0.1688-0.48122 0.11257-0.94573 0.27009-1.3959 0.47558v-1.6098c0.3997-0.15748 0.8641-0.28991 1.3875-0.39391 0.5262-0.10417 1.0722-0.15769 1.6378-0.15769 0.62201 0 1.1539 0.0591 1.5985 0.17741 0.44729 0.11811 0.81319 0.29549 1.1003 0.53189 0.2897 0.23639 0.5038 0.53181 0.64159 0.88642 0.13519 0.35458 0.2055 0.76817 0.2055 1.2411v5.7213zm-0.28702-3.7008h-1.2297c-0.3405 0-0.6277 0.0311-0.86402 0.0985-0.2364 0.0647-0.4306 0.1548-0.5768 0.2703-0.14929 0.1154-0.25911 0.2504-0.32649 0.39959-0.0677 0.15191-0.10432 0.31228-0.10432 0.48401 0 0.34051 0.10982 0.59937 0.33211 0.7795 0.2195 0.17738 0.5178 0.26737 0.89771 0.26737 0.27869 0 0.57129-0.10139 0.87239-0.30409 0.30409-0.20528 0.63619-0.4952 0.9991-0.8751zm9.3573 2.7861c-0.29002 0.72608-0.59383 1.3622-0.90911 1.908-0.31792 0.54599-0.6641 1.0019-1.0441 1.3677-0.37991 0.3659-0.8019 0.6417-1.2663 0.82462-0.4644 0.18577-0.98488 0.27576-1.5619 0.27576-0.1379 0-0.28702-6e-3 -0.45032-0.0197-0.1603-0.014-0.32639-0.0338-0.49798-0.062v-1.7054c0.0676 0.0113 0.14058 0.0226 0.22789 0.0367 0.0845 0.0141 0.1746 0.0253 0.26741 0.0337 0.0928 8e-3 0.1886 0.0141 0.2842 0.0198 0.0957 6e-3 0.18581 8e-3 0.2673 8e-3 0.23078 0 0.4446-0.045 0.64438-0.13223 0.1971-0.0872 0.38001-0.2111 0.54892-0.3659 0.16598-0.15748 0.318-0.34328 0.4559-0.56278 0.13779-0.21671 0.2588-0.45311 0.3629-0.71191l-3.3235-8.3779h2.243l1.7392 4.7503 0.52049 1.5337 0.51219-1.4689 1.7561-4.8151h2.1585zm6.9511-9.1685h-2.3668v-1.5676h4.4296v10.083h2.4144v1.5675h-7.131v-1.5675h2.6538zm11.51-0.58551c0 0.18009-0.0339 0.35179-0.0986 0.51231-0.0646 0.1574-0.15759 0.2983-0.2757 0.41641-0.11828 0.11829-0.25608 0.211-0.41649 0.27859-0.1604 0.0704-0.3321 0.10411-0.52059 0.10411-0.18581 0-0.36033-0.0337-0.52352-0.10411-0.16041-0.0676-0.3012-0.1603-0.41938-0.27859-0.11822-0.11811-0.21093-0.25901-0.2757-0.41641-0.0676-0.16052-0.0984-0.33222-0.0984-0.51231 0-0.17999 0.0308-0.35168 0.0984-0.51209 0.0648-0.15769 0.15748-0.29831 0.2757-0.41942 0.11818-0.12107 0.25897-0.21657 0.41938-0.28427 0.16319-0.0703 0.33771-0.104 0.52352-0.104 0.18849 0 0.36019 0.0337 0.52059 0.104 0.16041 0.0677 0.29821 0.1632 0.41649 0.28427 0.11811 0.12111 0.21111 0.26173 0.2757 0.41942 0.0647 0.16041 0.0986 0.3321 0.0986 0.51209zm-2.2261 3.8582h-2.3668v-1.5674h4.4297v6.8104h2.4145v1.5675h-7.1311v-1.5675h2.6537zm11.578 6.8105v-5.4568c0-0.92018-0.34039-1.3789-1.0244-1.3789-0.3404 0-0.66421 0.1379-0.97642 0.4137-0.3096 0.27281-0.63899 0.64732-0.98499 1.12v5.302h-2.0121v-8.3779h1.7391l0.0506 1.2383c0.1717-0.214 0.349-0.41092 0.53188-0.58551 0.18292-0.1772 0.3828-0.32639 0.59673-0.45021 0.21667-0.12379 0.44739-0.21661 0.69229-0.2843 0.24751-0.0645 0.52609-0.0985 0.8329-0.0985 0.43071 0 0.80219 0.0704 1.1229 0.21121 0.31799 0.1379 0.58529 0.33771 0.79918 0.59369 0.21403 0.25322 0.37433 0.56282 0.48133 0.92311 0.10678 0.36019 0.1603 0.7626 0.1603 1.2072v5.6229zm9.4726 0-3.0926-4.1875v4.1875h-2.0096v-11.651h2.0096v6.8188l2.9267-3.5459h2.5158l-3.3967 3.8188 3.6781 4.5591zm11.006-2.4653c0 0.47836-0.104 0.88638-0.31499 1.2242-0.21389 0.33771-0.49259 0.61341-0.84148 0.82451-0.3489 0.21121-0.74581 0.3659-1.1875 0.46161-0.44189 0.0955-0.895 0.14341-1.3565 0.14341-0.6164 0-1.1706-0.0282-1.666-0.0843-0.4953-0.0591-0.96248-0.14362-1.4015-0.2534v-1.8123c0.5178 0.21399 1.0328 0.36868 1.5451 0.46439 0.51498 0.0957 0.99889 0.1464 1.4548 0.1464 0.5291 0 0.92301-0.0845 1.1848-0.2534 0.26169-0.16601 0.39109-0.38541 0.39109-0.6556 0-0.12682-0.0281-0.24211-0.0816-0.34621-0.0563-0.10421-0.15748-0.2055-0.30949-0.3011-0.15202-0.0958-0.3658-0.1941-0.63881-0.29549-0.2758-0.10121-0.63599-0.21671-1.0807-0.34339-0.41088-0.11543-0.77389-0.24483-1.0833-0.39123-0.31249-0.14619-0.56858-0.31789-0.77117-0.51488-0.20271-0.19699-0.35751-0.42781-0.45883-0.6895-0.10118-0.25891-0.1519-0.56561-0.1519-0.9174 0-0.3405 0.076-0.66421 0.23082-0.9652 0.1548-0.30399 0.38259-0.5685 0.6895-0.7965 0.30378-0.2279 0.68379-0.40799 1.1398-0.54042 0.45861-0.13219 0.9905-0.197 1.6012-0.197 0.5261 0 0.99342 0.0282 1.4015 0.0816 0.4052 0.0564 0.76538 0.11539 1.0806 0.18292v1.6408c-0.4784-0.1549-0.92869-0.26462-1.348-0.32649-0.4221-0.0618-0.83859-0.0956-1.2495-0.0956-0.4136 0-0.74299 0.0758-0.9935 0.22218-0.2505 0.1493-0.37701 0.35461-0.37701 0.6192 0 0.12672 0.0252 0.23922 0.076 0.33761 0.0478 0.0986 0.14641 0.19709 0.2926 0.29001 0.1464 0.0927 0.3489 0.19128 0.61069 0.2926 0.25891 0.10139 0.60501 0.2111 1.0327 0.33221 0.48422 0.13779 0.89231 0.28698 1.2215 0.44178 0.32928 0.15741 0.59379 0.33489 0.79629 0.53181 0.19981 0.19689 0.34339 0.425 0.4307 0.6755 0.09 0.25319 0.13208 0.54311 0.13208 0.86671zm4.1146-6.7033 5.5579 4.6743-5.5579 4.6827-1.2777-1.3028 4.0806-3.363-4.0806-3.3883z" fill="#5c5c5c" inkscape:connector-curvature="0"/>
			</g>
		</svg>
	</div>
	
	<div class="clear"></div>

	<?php do_action( 'woocommerce_credit_card_form_end', $this->id ); ?>
</fieldset>

<script>
(function() {	

	let globalTotal = "<?= WC()->cart->total; ?>";
	let curSymbol = "<?= get_woocommerce_currency_symbol(); ?>";

	let paymentMethod = document.querySelector(".wc_payment_methods");
	paymentMethod.addEventListener('click', event => {
		let paylinks = document.getElementById('payment_method_paylinks_gateway');
		paylinks.checked ? addFees() : removeFees();
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

		switch (select_ctab.value) {
			case '-1':
				feeSum = parseFloat(allFees.etransferFlatFee);
				feeMult = parseFloat(allFees.etransferPercentageFee);
				break;			
			case '1':
				feeSum = parseFloat(allFees.creditCardFlatFee);
				feeMult = parseFloat(allFees.creditCardPercentageFee);
				break;
			case '2':
				feeSum = parseFloat(allFees.directDebitFlatFee);
				feeMult = parseFloat(allFees.directDebitPercentageFee);
				break;	
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

	let select_ctab = document.querySelector('.checkout-tab');
	select_ctab.addEventListener('change', event => {
		event.preventDefault();
		let tabs = document.querySelector('.tabs');
		tabs.querySelector('.active').classList.remove('active');
		tabs.querySelectorAll('.tab')[select_ctab.options.selectedIndex].classList.add('active');

		changeSelectbox (select_ctab.value > 0 ? 0 : select_ctab.value, '.pm-et-block');
	}, true);

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
	let select_pmcc = document.querySelector('.pm-cc');
	select_pmcc.addEventListener('click', event => {
		event.preventDefault();
		changeSelectbox(select_pmcc.value != 0 ? 1: 0, '.pm-cc-block');
	}, true);

	let select_pmdd = document.querySelector('.pm-dd');
	select_pmdd.addEventListener('click', event => {
		event.preventDefault();
		changeSelectbox(select_pmdd.value != 0 ? 1: 0, '.pm-dd-block');
	}, true);

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

	let a_settings = document.getElementById('showHideSettings');
	a_settings.addEventListener('click', event => {
		event.preventDefault();
		event.stopPropagation();
		let settingTab = document.querySelector('.stabs');
		settingTab.style.display = settingTab.style.display === 'none' || settingTab.style.display === '' ? 'block' : 'none';

		jQuery('.datepicker-here').datepicker({minDate: new Date()});
		
		var first_payment_date_1 = document.getElementById('datepickers-container');
		first_payment_date_1.addEventListener('click', event => {
			document.getElementById('startDate').value = first_payment_date.value;
		});
	});

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
		first_payment_date.classList.add('activate_input');
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

	let ssRepeat = document.getElementById('ssRepeat');
	ssRepeat.addEventListener('change', event => {
		document.getElementById('recurring').value = ssRepeat.value;
	});

	let billing_duration = document.getElementById('btn_noLimit');
	billing_duration.addEventListener('change', event => {
		document.getElementById('numberOfPayments').value = 0;
	});
	
	paymentMethod.click();

})();	
</script>

<style scoped="scoped">
	.form-row.form-row-small {
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
	}
</style>
