<?php
/**
 * Woocommerce BNA Gateway
 *
 * @author 	BNA
 * @category 	'BNA transaction' Template
 * @version     1.0
 */

if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly
?>

<section class="section my-account-orders">
    <div class="col-xs-12">
        <div class="payor-tab">
            <div class="payorTab">
                <table style="table-layout: auto;">
                    <thead class="ant-table-thead">
                        <tr>
                            <th class="ant-table-cell">Order</th>
                            <th class="ant-table-cell">Transaction #</th>
                            <th class="ant-table-cell">Reference #</th>
                            <th class="ant-table-cell">Type</th>
                            <th class="ant-table-cell">Status</th>
                            <th class="ant-table-cell">Description</th>
                            <th class="ant-table-cell">Created</th>
                        </tr>
                    </thead>
                    <tbody class="ant-table-tbody">
                        <?php
                            foreach ($transactions as $t_val) {
                                $desc = json_decode($t_val->transactionDescription);

                                $imageName = '';
                                switch ($desc->transactionType) {
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
                                        <a href="/my-account/view-order/<?=/*$desc->transactionInfo->invoiceId;*/$t_val->order_id;?>/">
                                            <?=/*$desc->transactionInfo->invoiceId;*/$t_val->order_id;?>
                                        </a>
                                    </td>
                                    <td class="ant-table-cell"><?=$desc->transactionInfo->transactionToken;?></td>
                                    <td class="ant-table-cell"><?=$desc->transactionInfo->referenceNumber;?></td>
                                    <td class="ant-table-cell">
                                        <div style="display: flex;">
                                            <div style="margin: 0px 4px;">
                                                <img src="<?=$this->plugin_url.'img/'.$imageName; ?>" alt="<?=$desc->transactionType;?>" style="height: 25px;">
                                            </div>
                                        </div>
                                    </td>
                                    <td class="ant-table-cell"><?=$desc->transactionStatus;?></td>
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
                                                switch ($desc->transactionType) {
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
                                                        echo "<p>Email: {$desc->transactionInfo->emailAddress}</p>";
                                                        break;
                                                } 
                                            ?>
                                        </details>
                                    </td>
                                    <td class="ant-table-cell">
                                        <?=date('Y-m-d H:i:s', strtotime($desc->transactionInfo->merchantTimestamp));?>
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
