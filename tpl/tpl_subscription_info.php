<?php
/**
 * Woocommerce BNA Gateway
 *
 * @author 	BNA
 * @category 	'BNA subscription' Template
 * @version     1.0
 */

if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly
?>

<section class="section my-account-orders">
	<div>
		<div class="woocommerce-orders-table__filters">
			<div class="woocommerce-orders-table__filter"><?php _e( 'Last Week', 'wc-bna-gateway' ); ?></div>
			<div class="woocommerce-orders-table__filter"><?php _e( 'Last Month', 'wc-bna-gateway' ); ?></div>
			<div class="woocommerce-orders-table__filter filter-active"><?php _e( 'Last 3 Months', 'wc-bna-gateway' ); ?></div>
			<div class="woocommerce-orders-table__filter"><?php _e( 'Last Year', 'wc-bna-gateway' ); ?></div>
		</div>
	</div>
    <div>
        <div class="payor-tab">
            <div class="payorTab">
                <table style="table-layout: auto;">
                    <thead class="ant-table-thead">
                        <tr>
                            <th class="ant-table-cell">Manage</th>
                            <th class="ant-table-cell">Base Order</th>
                            <th class="ant-table-cell">ID</th>
                            <th class="ant-table-cell">Recurring</th>
                            <th class="ant-table-cell">Status</th>
                            <th class="ant-table-cell">Payment #</th>
                            <th class="ant-table-cell">PaymentType</th>                            
                            <th class="ant-table-cell">Start</th>
                            <th class="ant-table-cell">Next</th>
                            <th class="ant-table-cell">Expire</th>
                            <th class="ant-table-cell">Desc.</th>
                            <th class="ant-table-cell">Created</th>
                        </tr>
                    </thead>
                    <tbody class="ant-table-tbody">
                        <?php
                            foreach ($subscriptions as $s_val) {
                                $desc = json_decode($s_val->recurringDescription);

                                $imageName = '';
                                switch ($desc->transactionInfo->transactionType) {
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
                                    case 'ETRANSFER':
                                        $imageName = 'eTransfer.svg';
                                        break;
                                } 

                                if ( empty($imageName) ) continue;
                        ?>
                                <tr class="ant-table-row ant-table-row-level-0">
                                    <td class="ant-table-cell">
                                        <button type="button" class="btn-del-subscription" data-id="<?=$s_val->id;?>">
                                            <div>
                                                <img src="<?=$this->plugin_url.'/img/trash-solid.svg';?>" >
                                            </div>
                                        </button>
                                    </td>
                                    <td class="ant-table-cell">
                                        <a href="/my-account/view-order/<?=$desc->transactionInfo->invoiceId;?>/">
                                            <?=$desc->transactionInfo->invoiceId;?>
                                        </a>
                                    </td>
                                    <td class="ant-table-cell"><?=$s_val->recurringId;?></td>
                                    <td class="ant-table-cell"><?=$s_val->recurring;?></td>
                                    <td class="ant-table-cell"><?=$s_val->status;?></td>
                                    <td class="ant-table-cell"><?=$s_val->numberOfPayments < 0 ? 'NO LIMIT' : $s_val->numberOfPayments;?></td>
                                    <td class="ant-table-cell">
                                        <div style="display: flex;">
                                            <div style="margin: 0px 4px;">
                                                <img src="<?=$this->plugin_url.'img/'.$imageName; ?>" alt="<?=$desc->transactionInfo->transactionType;?>" style="height: 25px;">
                                            </div>
                                        </div>
                                    </td>
                                    <td class="ant-table-cell"><?=date('Y-m-d H:i:s', strtotime($s_val->startDate));?></td>
                                    <td class="ant-table-cell"><?=date('Y-m-d H:i:s', strtotime($s_val->nextChargeDate));?></td>
                                    <td class="ant-table-cell"><?=$s_val->expire == 'NEVER' ? $s_val->expire : date('Y-m-d H:i:s', strtotime($s_val->expire));?></td>
                                    <td class="ant-table-cell">
                                        <details><summary>more...</summary>
                                            <p>Currency: <?=$desc->transactionInfo->currency;?></p>
                                            <?php

                                                if (isset($desc->transactionInfo->totalAmount))
                                                    echo "<p>Total amount: {$desc->transactionInfo->totalAmount}</p>";

                                                if (isset($desc->transactionInfo->subtotal))
                                                    echo "<p>Subtotal: {$desc->transactionInfo->subtotal}</p>";
                                                    
                                                if (isset($desc->transactionInfo->refundedAmount))
                                                    echo "<p>Refunded: {$desc->transactionInfo->refundedAmount}</p>";

                                            ?>
                                            <p>BNA fee: <?=$desc->transactionInfo->paylinksFee;?></p>
                                            <p>BNA Hst fee: <?=$desc->transactionInfo->paylinksHstFee;?></p>
                                            <?php
                                                switch ($desc->transactionInfo->transactionType) {
                                                    case 'VISA':
                                                    case 'MASTERCARD':
                                                    case 'AMEX':
                                                        echo "<p>Card #: {$desc->transactionInfo->cardNumber}</p>";
                                                        break;
                                                    case 'DIRECT-DEBIT':
                                                    case 'DIRECT-CREDIT':
                                                        echo "<p>Account #: {$desc->transactionInfo->accountNumber}</p>";
                                                        echo "<p>Transit #: {$desc->transactionInfo->transitNumber}</p>";
                                                        echo "<p>Institution #: {$desc->transactionInfo->institutionNumber}</p>";
                                                        break;
                                                    case 'ETRANSFER':
                                                        echo "<p>Email: ". (empty($desc->transactionInfo->emailAddress) ? wp_get_current_user()->user_email : $desc->transactionInfo->emailAddress) ."</p>";
                                                        break;
                                                } 
                                            ?>
                                        </details>
                                    </td>
                                    <td class="ant-table-cell">
                                        <?=date('Y-m-d H:i:s', strtotime($s_val->created_time));?>
                                    </td>
                                </tr>
                        <?php
                            }
                            
                        ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</section>

<script>
</script>


<style scoped="scoped">
	table {
		width: 100%;
	}
    details summary::-webkit-details-marker {
        display: none
    }
    details > summary {
        list-style: none;
        font-weight:bold;
        cursor: pointer;
    }
    details summary:before {
        content: '\f0fe';
        font-family: "Font Awesome 5 free";
        margin-right: 7px;
    }
    details[open] summary:before {
        content: '\f146';
    }


    tr td:last-child{
        width:1%;
        white-space:nowrap;
    }
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
    .btn-del-subscription {
        border-radius: 5px;
    }
    .btn-del-subscription div {
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
