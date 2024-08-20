<?php
/**
 * Woocommerce Paylinks Gateway
 *
 * @author 	ktscript
 * @category 	'Paylinks My-account managing' Template
 * @version     1.0
 */


if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

$current_user_id = get_current_user_id();
?>

<section class="section my-account-orders">
    <div class="col-xs-12">
        <label for="option-tabs">Manage payment methods:</label>
        <div class="tabs-main" id="option-tabs">
            <input type="radio" name="tab-btn" id="tab-btn-0" value="" <?= empty($payorID) ? 'checked' : 'disabled';?> >
            <label for="tab-btn-0" style="<?= empty($payorID) ? 'display:inline-block;' : 'display:none;';?>" >Create payor</label>
            <input type="radio" name="tab-btn" id="tab-btn-1" value="" <?= empty($payorID) ? 'disabled' : 'checked';?> >
            <label for="tab-btn-1" style="<?= empty($payorID) ? 'display:none;' : 'display:inline-block;';?>" >Update payor</label>
            <input type="radio" name="tab-btn" id="tab-btn-2" value="" <?= empty($payorID) ? 'disabled' : '';?> >
            <label for="tab-btn-2" style="<?= empty($payorID) ? 'display:none;' : 'display:inline-block;';?>" >Update address</label>

            <div id="content-0">
                <div class="payorTab">s
                    <form class="form_create_payor"> 
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
                        <div class="form-row form-row-wide">
                            <button type="submit" class="button alt btn-margin" id="create_payor" 
                                name="create_payor"><?=__('Create', 'wc-gateway-paylinks'); ?></button>
                        </div>                    
                    </form>
                </div>
            </div>           
            <div id="content-1">
                <div class="payorTab">
                    <form class="form_update_payor">
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
                                <label>Phone number <span class="required">*</span></label>
                                <input type="text" name="phone" autocomplete="off" maxlength="11" placeholder="1437XXXXXXX" 
                                    onkeyup="return input_test(this);" value="<?= get_user_meta( $current_user_id,   'billing_phone', true ); ?>">
                            </div>
                            <div class="form-row form-row-wide">
                                <label>Birthday </label>
                                <input type="text" class="datepicker-here" id="birthday" name="birthday" autocomplete="off" maxlength="15" 
                                    placeholder="XX.XX.XXXX" value="<?= date('d.m.Y', strtotime(get_user_meta( $current_user_id, 'billing_birthday', true ))); ?>">
                            </div>
                        </div>
                        <div class="form-row form-row-wide">
                            <button type="submit" class="button alt btn-margin" id="update_payor" 
                                name="update_payor"><?=__('Update', 'wc-gateway-paylinks'); ?></button>
                        </div>
                    </form>
                </div>
            </div>
            <div id="content-2">
                <div class="payorTab">  
                    <form class="form_update_address">
                        <div class="payor-tab">
                            <div class="woocommerce-billing-fields__field-wrapper">
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
                                            /*
                                            if ( isset($field['type']) && in_array($field['type'],  ['select', 'country', 'state']) ) {
                                                $field['default'] = get_user_meta( $current_user_id, $key, true );
                                                woocommerce_form_field( 
                                                    $key, 
                                                    $field, 
                                                    $field['type'] == 'state' ? $checkout->get_value( $key ) : $field['default'] 
                                                );
                                            } else {
                                                woocommerce_form_field( $key, $field, $checkout->get_value( $key ) );
                                            }
                                            */
                                            woocommerce_form_field( $key, $field, $checkout->get_value( $key ) );
                                        }
                                    }
                                ?>
                            </div>  
                        </div>
                        <div class="form-row form-row-wide">
                            <button type="submit" class="button alt btn-margin" id="update_address" name="update_address"><?=__('Update', 'wc-gateway-paylinks'); ?></button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
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

    function input_test(input) 
	{ 
		input.value = input.value.replace(/[^\d,]/g, "");
	};

    let input_birth = document.querySelector('#birthday');
    if (typeof input_birth !== 'undefined') input_birth.removeAttribute('autocomplete');
</script>

<style scoped="scoped">
    .tabs-main {
        font-size: 0;
        display: block;
    }
    .tabs-main>input[type="radio"] {
        display: none;
    }
    .tabs-main>div, 
    .content-tab,
    .institutionNumber,
    .payor-tab {
        display: none;
        border: 1px solid #e0e0e0;
        padding: 10px 15px;
        font-size: 16px;
    }
    .payor-tab,
    #tab-btn-0:checked~#content-0,
    #tab-btn-1:checked~#content-1,
    #tab-btn-2:checked~#content-2,
    #tab-btn-3:checked~#content-3,
    #tab-opt-1:checked~#content-child-1,
    #tab-opt-2:checked~#content-child-2,
    .active {  
        display: block;
    }
    .tabs-main>label {
        display: inline-block;
        text-align: center;
        vertical-align: middle;
        user-select: none;
        background-color: #f5f5f5;
        border: 1px solid #e0e0e0;
        padding: 2px 8px;
        font-size: 16px;
        line-height: 1.5;
        transition: color 0.15s ease-in-out, background-color 0.15s ease-in-out;
        cursor: pointer;
        position: relative;
        top: 1px;
    }
    .tabs-main>label:not(:first-of-type) {
        border-left: none;
    }
    .tabs-main>input[type="radio"]:checked+label {
        background-color: #fff;
        border-bottom: 1px solid #fff;
    }
    .payorTab {
        overflow-x: auto;
    }
    #content-1 label,
    #content-2 label,
    #content-3 label {
        font-size: 13px;
    }
    .form-row-small {
        width: 50% !important;
    }
    .btn-margin {
        margin: 20px auto;
    }
    .btn-del-payment {
        border-radius: 5px;
    }
    .btn-del-payment div {
        width:20px;
        height:20px;
    }
    .deactive {
        -webkit-user-select: none;
        -moz-user-select: none;
        -ms-user-select: none;
        user-select: none;  
        pointer-events: none;
    }
    .deactive input{
        cursor: not-allowed !important;
    }
    .loading {
        display:none;
        position:absolute; 
        z-index:3000; 
        background:url(<?=$this->plugin_url;?>img/loading.gif) center, no-repeat;
        background-size: cover;
        top: 45%;
        left: 40%;
        width: 150px;
        height: 150px;
        font-size: 24px;
        text-align: center;
    }
</style>