<?php
/**
 * Woocommerce BNA Gateway
 *
 * @author 	BNA
 * @category 	'BNA Payment Method' Template
 * @version     1.0
 */

if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly
?>

<section class="section my-account-orders">
    <div class="col-xs-12">
        <label for="option-tabs">Manage payment methods:</label>
        <div class="tabs-main" id="option-tabs">
            <input type="radio" name="tab-btn" id="tab-btn-1" value="" <?= empty($payorID) ? 'disabled' : '';?> checked>
            <label for="tab-btn-1">Saved methods</label>
            <input type="radio" name="tab-btn" id="tab-btn-2" value="" <?= empty($payorID) ? 'disabled' : '';?> >
            <label for="tab-btn-2">Add method</label>

            <div id="content-1">
                <div class="payor-tab">
                    <div class="isoPayorTable" <?= empty($payorID) ? 'style="display:none;"' : '' ?> >
                        <table style="table-layout: auto;">
                            <colgroup>
                                <col style="width: 30%; min-width: 30%;">
                                <col style="width: 60%; min-width: 60%;">
                                <col style="width: 5%; min-width: 5%;">
                                <col style="width: 10%; min-width: 10%;">
                            </colgroup>
                            <thead class="ant-table-thead">
                                <tr>
                                    <th class="ant-table-cell">Payment types</th>
                                    <th class="ant-table-cell">Information</th>
                                    <!--<th class="ant-table-cell ant-table-column-has-sorters">Recurrings</th>-->
                                    <th class="ant-table-cell">Manage</th>
                                </tr>
                            </thead>
                            <tbody class="ant-table-tbody">
                                <?php
                                    foreach ($paymentMethods as $p_method) {
                                        $imageName = '';
                                        switch ($p_method->paymentType) {
                                            case 'VISA':
                                                $imageName = 'visa.svg';
                                                break;
                                            case 'MASTERCARD':
                                                $imageName = 'masterCard.svg';
                                                break;
                                            case 'AMEX':
                                                $imageName = 'americanExpress.svg';
                                                break;
                                            case 'DIRECT-DEBIT':
                                                $imageName = 'directDebit.svg';
                                                break;
                                            case 'DIRECT-CREDIT':
                                                $imageName = 'directCredit.svg';
                                                break;
                                        } 

                                        if ( empty($imageName) ) continue;

                                        ?>
                                        <tr class="ant-table-row ant-table-row-level-0">
                                            <td class="ant-table-cell">
                                                <div style="display: flex;">
                                                    <div style="margin: 0px 4px;">
                                                        <img src="<?=$this->plugin_url.'img/'.$imageName; ?>" alt="<?=$p_method->paymentType;?>" style="height: 25px;">
                                                    </div>
                                                </div>
                                            </td>
                                            <td class="ant-table-cell">
                                                <?php
                                                    $data = json_decode($p_method->paymentDescription);
                                                    if ( in_array($p_method->paymentType, ['DIRECT-DEBIT', 'DIRECT-CREDIT']) ) {
                                                        echo $data->accountNumber.'/'.$data->transitNumber.'<br>'.
                                                             'institution: #'.$data->institutionNumber;  
                                                    } else {
                                                        echo $data->cardNumber.'<br>'.
                                                             'expiry: '.$data->expiryMonth.'/'.$data->expiryYear;
                                                    }
                                                ?>
                                            </td>
                                            <!--<td class="ant-table-cell"><?=$p_method->paymentsRecurrings > 0 ? 'YES' : 'NO';?></td>-->
                                            <td class="ant-table-cell">
                                                <button type="button" class="btn-del-payment" data-id="<?=$p_method->id;?>">
                                                    <div>
                                                        <img src="<?=$this->plugin_url.'/img/trash-solid.svg';?>" >
                                                    </div>
                                                </button>
                                            </td>
                                        </tr>
                                        <?php
                                    }
                                ?>
                            </tbody>
                        </table>
                    </div>
                    <div class="isoPayorTable" style="<?= empty($payorID) ? 'display:block;' : 'display:none;'; ?>" >
                        <p>
                            <div class="woocommerce-error">
                                <?=__('Sorry. Please create a payer account first.', 'wc-bna-gateway');?>
                            </div>
                        </p>
                    </div>
                </div>
            </div>
            <div id="content-2">
                <form class="form_save_payment">
                    <div class="payor-tab">
                        <div class="form-row form-row-wide">
                            <label>Choose payment method:</label>
                            <select class="stab input-text" name="paymentType" aria-placeholder="Please choose...">
                                <option id="tab-opt-1" value="<?=BNA_PAYMENT_TYPE_CREDITCARD;?>" selected>Credit card</option>
                                <option id="tab-opt-2" value="<?=BNA_PAYMENT_TYPE_DIRECTDEBIT;?>">Direct Debit</option>
                                <option id="tab-opt-3" value="<?=BNA_PAYMENT_TYPE_DIRECTCREDIT;?>">Direct Credit</option>
                            </select>
                        </div>

                        <div class="content-tab active" id="content-child-1" >
                            <div class="pm-cc-block">
                                <div class="form-row form-row-wide">
                                    <label>Card Holder <span class="required">*</span></label>
                                    <input type="text" name="cc_holder" autocomplete="off" maxlength="100" placeholder="FIRSTNAME LASTNAME" value="">
                                </div>
                                <div class="form-row form-row-wide">
                                    <label>Card Number <span class="required">*</span></label>
                                    <input type="text" name="cc_number" autocomplete="off" maxlength="18" placeholder="0000000000000000"
                                        onkeyup="return input_test(this);" value="">
                                </div>
                                <div class="form-row form-row-wide">
                                    <div class="form-row form-row-first">
                                        <label>Expiry Date <span class="required">*</span></label>
                                        <input class="form-row form-row-small" style="float:left" type="text" id="cc_expire_month" name="cc_expire_month" 
                                            autocomplete="off" placeholder="MM" onkeyup="return input_test(this);" maxlength="2" value="">
                                        <input class="form-row form-row-small" style="float:right" type="text" id="cc_expire_year" name="cc_expire_year" autocomplete="off" placeholder="YY" 
                                            onkeyup="return input_test(this);" maxlength="2" value="">
                                        
                                    </div>
                                    <div class="form-row form-row-last">
                                        <label>Card Code (CVC) <span class="required">*</span></label>
                                        <input type="password" name="cc_code" autocomplete="off" placeholder="CVC" maxlength="3" 
                                            onkeyup="return input_test(this);" value="">
                                    </div>
                                    <div class="clear"></div>
                                </div>
                            </div>
                        </div>
                        <div class="content-tab" id="content-child-2">
                            <div class="pm-dd-block">
                                <div class="form-row form-row-wide">
                                    <label>Bank Name<span>*</span></label>
                                    <select id="ddBankName" name="ddBankName" class="input-text"></select>
                                    <div class="ddInstitutionNumber">
                                        <label>Institution Number <span>*</span></label>
                                        <input placeholder="000" type="text" id="ddInstitutionNumber" name="ddInstitutionNumber" value="" maxlength="3" 
                                            onkeyup="return input_test(this);" autocomplete="off" >
                                    </div>
                                </div>
                                <div class="form-row form-row-wide">
                                    <label>Account Number <span>*</span></label>
                                    <input placeholder="000000000000" type="text" id="ddAccountNumber" name="ddAccountNumber" value="" maxlength="12" 
                                        onkeyup="return input_test(this);" autocomplete="off" >
                                </div>
                                <div class="form-row form-row-wide">
                                    <label>Transit Number <span>*</span></label>
                                    <input placeholder="00000" type="text" id="ddTransitNumber" name="ddTransitNumber" value="" maxlength="5" 
                                        onkeyup="return input_test(this);" autocomplete="off" >
                                </div>
                            </div>
                            <div class="clear"></div>
                        </div> 
                        <div class="content-tab" id="content-child-3">
                            <div class="pm-dc-block">
                                <div class="form-row form-row-wide">
                                    <label>Bank Name<span>*</span></label>
                                    <select id="dcBankName" name="dcBankName" class="input-text"></select>
                                    <div class="dcInstitutionNumber">
                                        <label>Institution Number <span>*</span></label>
                                        <input placeholder="000" type="text" id="dcInstitutionNumber" name="dcInstitutionNumber" value="" maxlength="3" 
                                            onkeyup="return input_test(this);" autocomplete="off" >
                                    </div>
                                </div>
                                <div class="form-row form-row-wide">
                                    <label>Account Number <span>*</span></label>
                                    <input placeholder="000000000000" type="text" id="dcAccountNumber" name="dcAccountNumber" value="" maxlength="12" 
                                        onkeyup="return input_test(this);" autocomplete="off" >
                                </div>
                                <div class="form-row form-row-wide">
                                    <label>Transit Number <span>*</span></label>
                                    <input placeholder="00000" type="text" id="dcTransitNumber" name="dcTransitNumber" value="" maxlength="5" 
                                        onkeyup="return input_test(this);" autocomplete="off" >
                                </div>
                            </div>
                            <div class="clear"></div>
                        </div> 
                        <div class="form-row form-row-wide">
                            <button type="submit" class="button alt btn-add-payment" id="save_payment" name="save_payment"><?=__('Save', 'wc-bna-gateway'); ?></button>
                        </div>
                    </div>
                </form>
            </div>
        </div>
      </div>
      <div class="loading"></div>
</section>

<script>
(function() {	
	function input_test(input) 
	{ 
		input.value = input.value.replace(/[^\d,]/g, "");
	};
    let select_stab = document.querySelector('.stab');
	select_stab.addEventListener('click', event => {
		event.preventDefault();
		let tabs = document.querySelector('#content-2');
		tabs.querySelector('.active').classList.remove('active');
		tabs.querySelectorAll('.content-tab')[select_stab.options.selectedIndex].classList.add('active');
	}, true);

    let select_ddBankName = document.querySelector('#ddBankName');
    let select_dcBankName = document.querySelector('#dcBankName');
    setTimeout(() => {
        let arBankName = (Object.entries(window.bankName)).sort(function(a,b){     
            if(a[1] > b[1]) return 1;
            if(a[1] < b[1]) return -1;
            return 0;
        });
        let options;
        for (i in arBankName) {
            options = '<option value="'+ arBankName[i][0] +'">' + arBankName[i][1] + '</option>';
            select_ddBankName.innerHTML += options;
            select_dcBankName.innerHTML += options;
        }
        options = '<option value="other">&lt;&lt; Other &gt;&gt;</option>'
        select_ddBankName.innerHTML += options;
        select_dcBankName.innerHTML += options;
    }, 500);

    select_ddBankName.addEventListener('click', event => {
		event.preventDefault();
        
        let iNum = document.querySelector('.ddInstitutionNumber');
        iNum.classList.remove('active');
        if (select_ddBankName.value == 'other') {
            iNum.classList.add('active');
        } 
    });

    select_dcBankName.addEventListener('click', event => {
		event.preventDefault();
        
        let iNum = document.querySelector('.dcInstitutionNumber');
        iNum.classList.remove('active');
        if (select_dcBankName.value == 'other') {
            iNum.classList.add('active');
        } 
    });
    let input_tab = document.querySelector('#tab-btn-1');
	input_tab.addEventListener('click', event => {
		window.location.reload();
	}, true);
})();	
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
    .payor-tab,
    .ddInstitutionNumber,
    .dcInstitutionNumber {
        display: none;
        border: 1px solid #e0e0e0;
        padding: 10px 15px;
        font-size: 16px;
    }
    .payor-tab,
    #tab-btn-1:checked~#content-1,
    #tab-btn-2:checked~#content-2,
    #tab-btn-3:checked~#content-3,
    #tab-opt-1:checked~#content-child-1,
    #tab-opt-2:checked~#content-child-2,
    #tab-opt-3:checked~#content-child-3,
    .active {  
        display: block !important;
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
    .btn-add-payment {
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
