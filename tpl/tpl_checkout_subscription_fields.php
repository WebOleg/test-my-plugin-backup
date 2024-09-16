<?php

?>

<label class="bna-checkbox-container">
	<input type="checkbox" name="create_subscription">
	<span class="checkmark" id="checkmark_is_recurring"></span>
	<?php _e( 'Is this payment Recurring Payment?', 'wc-bna-gateway' ); ?>
</label>

<!-- Recurring -->
<div class="bna-recurring-cards">
	<input type="hidden" id="recurring" name="recurring" value="<?php echo BNA_SUBSCRIPTION_SETTING_REPEAT; ?>">
	<input type="hidden" id="startDate" name="startDate" value="<?php echo BNA_SUBSCRIPTION_SETTING_STARTDATE;?>">
	<input type="hidden" id="numberOfPayments" name="numberOfPayments" value="<?php echo BNA_SUBSCRIPTION_SETTING_NUMPAYMENT; ?>">
	
	<div class="bna-text-required">* <?php _e( 'Required fields', 'wc-bna-gateway' ); ?></div>
	
	<div class="bna-input-wrapper">					
		<div class="bna-input-label bna-mb-10"><?php _e( 'Select Frequency (“Monthly” by default)', 'wc-bna-gateway' ); ?> <span class="required">*</span></div>
		<select class="ssRepeat" id="ssRepeat" name="ssRepeat" aria-placeholder="<?php _e( 'Please choose...', 'wc-bna-gateway' ); ?>">
			<?php
				$selected = BNA_SUBSCRIPTION_SETTING_REPEAT;
				$duration = ['day', 'week', 'two weeks', 'month', 'three month', 'six month', 'year'];
				$durOptions = ['daily', 'weekly', 'biweekly', 'monthly', 'quarterly', 'biannual' , 'annual'];

				foreach ($duration as $d_key => $d_val) {
					$attr = $durOptions[$d_key] == $selected ? 'selected' : '';
					echo '<option value=' . $durOptions[$d_key] . ' ' . $attr . '>' . __( 'EVERY', 'wc-bna-gateway' ) . ' ' . strtoupper( $d_val ) .' </option>';
				}
			?>
		</select>
	</div>
	
	<div class="bna-three-inputs-wrapper">
		<div class="bna-input-label"><?php _e( 'My First Payment Starts', 'wc-bna-gateway' ); ?></div>
		<div class="bna-input-wrapper">						
			<input class="bna-input bna-radio-button" type="radio" id="btn_immediately" name="setting_first_payment" value="immediately" checked>
			<span><?php _e( 'Immediately', 'wc-bna-gateway' ); ?></span>
		</div>
		
		<div class="bna-input-wrapper">						
			<input class="bna-input bna-radio-button" type="radio" id="btn_firstPayment" name="setting_first_payment"  value="set-date">
			<span><?php _e( 'Custom Date', 'wc-bna-gateway' ); ?></span>
		</div>
		
		<div class="bna-input-wrapper">						
			<input class="bna-input" type="text" id="setting_first_payment_date" name="setting_first_payment_date"  
				placeholder="<?php _e( 'Select date', 'wc-bna-gateway' ); ?>" disabled readonly>
		</div>
	</div>
	
	<div class="bna-three-inputs-wrapper">
		<div class="bna-input-label"><?php _e( 'Number of Payments', 'wc-bna-gateway' ); ?></div>
		<div class="bna-input-wrapper">						
			<input class="bna-input bna-radio-button" type="radio" id="btn_noLimit" name="setting_billing_duration" value="immediately" checked>
			<span><?php _e( 'NO LIMIT', 'wc-bna-gateway' ); ?></span>
		</div>
		
		<div class="bna-input-wrapper">						
			<input class="bna-input bna-radio-button" type="radio" id="btn_numPayment" name="setting_billing_duration"  value="set-date">
			<span><?php _e( 'CUSTOM NUMBER', 'wc-bna-gateway' ); ?></span>
		</div>
		
		<div class="bna-input-wrapper">						
			<input value="1" class="bna-input" type="text" name="setting_number_of_payment" id="setting_number_of_payment" disabled>
			<input type="button" value="-" class="qtyminus" id="qtyminus" disabled>
			<input type="button" value="+" class="qtyplus" id="qtyplus" disabled> 
		</div>
	</div>

	<label class="bna-checkbox-container">
		<input type="checkbox" name="i-agree">
		<span class="checkmark"></span>
		<?php _e( 'I have read and agree to the terms presented in the ', 'wc-bna-gateway' ); ?>
		<a href="#"><?php _e( 'Recurring Payment Agreement.', 'wc-bna-gateway' ); ?></a>
	</label>
</div>
