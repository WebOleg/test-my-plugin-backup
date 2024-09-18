<?php
/**
 * Woocommerce BNA Gateway
 *
 * @author 	 BNA
 * @category 'BNA My-account managing' Template
 * @version    1.0
 */


if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

$current_user_id = get_current_user_id();
?>

<section class="section my-account-orders">
        <?php
		if ( empty( $payorID ) ) {
			echo '<label for="option-tabs">' . __( 'Create Customer:', 'wc-bna-gateway' ) . '</label>';
		} else {
			echo '<label for="option-tabs">' . __( 'Update Customer:', 'wc-bna-gateway' ) . '</label>';
		}
		?>         
		<form class="<?php if ( empty( $payorID ) ) { echo 'form_create_payor'; } else { echo 'form_update_payor'; } ?>"> 
			<div class="payor-tab">
				<div class="form-row form-row-wide">
					<label>First name <span class="required">*</span></label>
					<input type="text" name="firstName" autocomplete="off" maxlength="100" placeholder="FIRST NAME" 
						value="<?= get_user_meta( $current_user_id, 'billing_first_name', true ); ?>" require>
				</div>
				<div class="form-row form-row-wide">
					<label>Last name <span class="required">*</span></label>
					<input type="text" name="lastName" autocomplete="off" maxlength="100" placeholder="LAST NAME" 
						value="<?= get_user_meta( $current_user_id, 'billing_last_name', true ); ?>">
				</div>
				<div class="form-row form-row-wide">
					<label>Company name </label>
					<input type="text" name="companyName" autocomplete="off" maxlength="100" placeholder="COMPANY NAME" 
						value="<?= get_user_meta( $current_user_id, 'billing_company', true ); ?>">
				</div>
				<div class="form-row form-row-wide">
					<label>E-mail </label>
					<input type="text" name="email" autocomplete="off" maxlength="100" placeholder="E-mail" 
						value="<?=wp_get_current_user()->user_email;?>" readonly>
				</div>
				<div class="form-row form-row-wide">
					<label>Phone code <span class="required">*</span></label>
					<input type="text" name="phoneCode" autocomplete="off" maxlength="4" placeholder="+1" 
						 value="<?= get_user_meta( $current_user_id,   'billing_phone_code', true ); ?>">
				</div>
				<div class="form-row form-row-wide">
					<label>Phone number <span class="required">*</span></label>
					<input type="text" name="phone" autocomplete="off" maxlength="11" placeholder="1437XXXXXXX" 
						onkeyup="return input_test(this);" value="<?= get_user_meta( $current_user_id,   'billing_phone', true ); ?>">
				</div>
				<div class="form-row form-row-wide">
					<label>Birthday </label>
					<input type="text" class="datepicker-here" id="birthday" name="birthday" autocomplete="off" maxlength="15" 
						placeholder="XX.XX.XXXX" value="<?= date('d.m.Y', strtotime(get_user_meta( $current_user_id, 'billing_birthday', true ))); ?>">
				</div>

				<?php
					$checkout = WC()->checkout;
					foreach ( $checkout->get_checkout_fields( 'billing' ) as $key => $field ) {
						if( !in_array($key, [
								'billing_email', 
								'billing_address_1', 
								'billing_address_2', 
								'billing_company',
								'billing_first_name', 
								'billing_last_name', 
								'billing_phone'
								
						])) {
							woocommerce_form_field( $key, $field, $checkout->get_value( $key ) );
						}
					}
				?>
			</div>
			<?php
			if ( empty( $payorID ) ) {
				?>
				<div class="form-row form-row-wide">
					<button type="submit" class="button alt btn-margin wp-element-button" id="create_payor" 
						name="create_payor"><?php _e( 'Create', 'wc-bna-gateway' ); ?></button>
				</div>
				<?php
			} else {
				?>
				<div class="form-row form-row-wide">
					<button type="submit" class="button alt btn-margin  wp-element-button" id="update_payor" 
						name="update_payor"><?php _e( 'Update', 'wc-bna-gateway' ); ?></button>
					</div>
				</form>
				<?php
			}
			?>                    
		</form>             
    <div class="loading"></div>
</section>

<script>
    window.onload = function() 
    {
        (function($){
            $("#billing_country").val('<?= get_user_meta( $current_user_id, 'billing_country', true ); ?>').change();
            $("#billing_state").val('<?= get_user_meta( $current_user_id, 'billing_state', true ); ?>').change();
        })(jQuery);
    }

    let input_birth = document.querySelector('#birthday');
    if (typeof input_birth !== 'undefined') input_birth.removeAttribute('autocomplete');
</script>
