<?php
/**
 * Woocommerce BNA Gateway
 *
 * @author 		BNA
 * @category 	'BNA Error Handling' Class
 * @version     1.0
 */


if ( !defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

/**
 * Error message output constants
 *
 * @var string
 */
define ('BNA_MSG_ERRORPARAMS', 1);
define ('BNA_MSG_ERRORPAYOR',  2);
define ('BNA_MSG_ERRORNONCE',  3);

define ('BNA_MSG_DELPAYMENT_ERRORID', 10);
define ('BNA_MSG_DELPAYMENT_ERROR', 11);
define ('BNA_MSG_DELPAYMENT_SUCCESS', 12);
define ('BNA_MSG_SUSPENDPAYMENT_SUCCESS', 13);
define ('BNA_MSG_SUSPENDPAYMENT_ERROR', 14);

define ('BNA_MSG_ADDPAYMENT_ERRPAYMENTTYPE', 201);
define ('BNA_MSG_ADDPAYMENT_ERROR', 202);
define ('BNA_MSG_ADDPAYMENT_SUCCESS', 203);

define ('BNA_MSG_UPDATE_ACCOUNT_ERROR', 21);
define ('BNA_MSG_UPDATE_ACCOUNT_SUCCESS', 22);

define ('BNA_MSG_ENDPOINT_ACCOUNT_ERRUSER', 101);



if ( ! class_exists( 'BNAJsonMsgAnswer' ) ) {

	class BNAJsonMsgAnswer {

        public function __construct() {}


        public static function send_json_answer( $errNumber )
		{
			$message = '';
			$status = 'false';

			switch ($errNumber) {
				case BNA_MSG_DELPAYMENT_ERRORID:
				case BNA_MSG_UPDATE_ACCOUNT_ERROR:
				case BNA_MSG_ERRORPARAMS:
				case BNA_MSG_ERRORPAYOR:
				case BNA_MSG_ERRORNONCE:
					$message =
						'<ul class="woocommerce-error">' .
							'<li>'.__('Error. Please contact your merchant about this issue.', 'wc-bna-gateway').'</li>' .
						'</ul>';
					break;
				case BNA_MSG_DELPAYMENT_ERROR:
					$message =
						'<ul class="woocommerce-error">' .
							'<li>'.__('Sorry, this payment method cannot be deleted. Please contact your merchant about this issue.', 'wc-bna-gateway').'</li>' .
						'</ul>';
					break;
				case BNA_MSG_DELPAYMENT_SUCCESS:
					$message =
						'<ul class="woocommerce-message">' .
							'<li>'.__('Payment method successfully deleted.', 'wc-bna-gateway').'</li>' .
						'</ul>';
					$status = 'true';
					break;				
				case BNA_MSG_SUSPENDPAYMENT_ERROR:
					$message =
						'<ul class="woocommerce-error">' .
							'<li>'.__('Sorry, this payment method cannot be suspended. Please contact your merchant about this issue.', 'wc-bna-gateway').'</li>' .
						'</ul>';
					break;
				case BNA_MSG_SUSPENDPAYMENT_SUCCESS:
					$message =
						'<ul class="woocommerce-message">' .
							'<li>'.__('Payment method successfully suspended.', 'wc-bna-gateway').'</li>' .
						'</ul>';
					$status = 'true';
					break;					
				case BNA_MSG_ENDPOINT_ACCOUNT_ERRUSER:
					$message = __('Error user e-mail in endpoint "account".', 'wc-bna-gateway');
					break;	
				case BNA_MSG_UPDATE_ACCOUNT_SUCCESS:
					$message =
						'<ul class="woocommerce-message">' .
							'<li>'.__('Account is successfully updated.', 'wc-bna-gateway').'</li>' .
						'</ul>';
					$status = 'true';
                    break;
                case BNA_MSG_ADDPAYMENT_SUCCESS:
                    $message =
                        '<ul class="woocommerce-message">' .
                            '<li>'.__('Payment method successfully added.', 'wc-bna-gateway').'</li>' .
                        '</ul>';
                    $status = 'true';
                    break;
                case BNA_MSG_ADDPAYMENT_ERROR:
                    $message =
                        '<ul class="woocommerce-error">' .
                            '<li>'.__('Sorry, this payment method cannot be added. Please contact your merchant about this issue.', 'wc-bna-gateway').'</li>' .
                        '</ul>';
                    break;                    
                case BNA_MSG_ADDPAYMENT_ERRPAYMENTTYPE:
                    $message = 
                        '<ul class="woocommerce-error">' .
                            '<li>'.__("Can't find BNA payment type. Please contact your merchant about this issue.", 'wc-bna-gateway').'</li>' .
                        '</ul>';
                    break;
				default:
					break;
			}
		
			echo ( json_encode( array('success'=> $status, 'message' => $message) ) );
		} 
    } // end of class
} 
