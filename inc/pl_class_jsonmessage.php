<?php
/**
 * Woocommerce Paylinks Gateway
 *
 * @author 		ktscript
 * @category 	'Paylinks Error Handling' Class
 * @version     1.0
 */


if ( !defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

/**
 * Error message output constants
 *
 * @var string
 */
define ('PAYLINKS_MSG_ERRORPARAMS', 1);
define ('PAYLINKS_MSG_ERRORPAYOR',  2);
define ('PAYLINKS_MSG_ERRORNONCE',  3);

define ('PAYLINKS_MSG_DELPAYMENT_ERRORID', 10);
define ('PAYLINKS_MSG_DELPAYMENT_ERROR', 11);
define ('PAYLINKS_MSG_DELPAYMENT_SUCCESS', 12);

define ('PAYLINKS_MSG_ADDPAYMENT_ERRPAYMENTTYPE', 201);
define ('PAYLINKS_MSG_ADDPAYMENT_ERROR', 202);
define ('PAYLINKS_MSG_ADDPAYMENT_SUCCESS', 203);

define ('PAYLINKS_MSG_UPDATE_ACCOUNT_ERROR', 21);
define ('PAYLINKS_MSG_UPDATE_ACCOUNT_SUCCESS', 22);

define ('PAYLINKS_MSG_ENDPOINT_ACCOUNT_ERRUSER', 101);



if (!class_exists('PaylinksJsonMsgAnswer')) {

	class PaylinksJsonMsgAnswer {

        public function __construct() {}


        public static function send_json_answer($errNumber)
		{
			$message = $status = '';
			$status = 'false';

			switch ($errNumber) {
				case PAYLINKS_MSG_DELPAYMENT_ERRORID:
				case PAYLINKS_MSG_UPDATE_ACCOUNT_ERROR:
				case PAYLINKS_MSG_ERRORPARAMS:
				case PAYLINKS_MSG_ERRORPAYOR:
					$message =
						'<ul class="woocommerce-error">' .
							'<li>'.__('Error. Please contact your merchant about this issue.', 'wc-gateway-paylinks').'</li>' .
						'</ul>';
					break;
				case PAYLINKS_MSG_DELPAYMENT_ERROR:
					$message =
						'<ul class="woocommerce-error">' .
							'<li>'.__('Sorry, this payment method cannot be deleted. Please contact your merchant about this issue.', 'wc-gateway-paylinks').'</li>' .
						'</ul>';
					break;
				case PAYLINKS_MSG_DELPAYMENT_SUCCESS:
					$message =
						'<ul class="woocommerce-message">' .
							'<li>'.__('Payment method successfully deleted.', 'wc-gateway-paylinks').'</li>' .
						'</ul>';
					$status = 'true';
					break;	
				case PAYLINKS_MSG_ENDPOINT_ACCOUNT_ERRUSER:
					$message = __('Error user e-mail in endpoint "account".', 'wc-gateway-paylinks');
					break;	
				case PAYLINKS_MSG_UPDATE_ACCOUNT_SUCCESS:
					$message =
						'<ul class="woocommerce-message">' .
							'<li>'.__('Account is successfully updated.', 'wc-gateway-paylinks').'</li>' .
						'</ul>';
					$status = 'true';
                    break;
                case PAYLINKS_MSG_ADDPAYMENT_SUCCESS:
                    $message =
                        '<ul class="woocommerce-message">' .
                            '<li>'.__('Payment method successfully added.', 'wc-gateway-paylinks').'</li>' .
                        '</ul>';
                    $status = 'true';
                    break;
                case PAYLINKS_MSG_ADDPAYMENT_ERROR:
                    $message =
                        '<ul class="woocommerce-error">' .
                            '<li>'.__('Sorry, this payment method cannot be added. Please contact your merchant about this issue.', 'wc-gateway-paylinks').'</li>' .
                        '</ul>';
                    break;                    
                case PAYLINKS_MSG_ADDPAYMENT_ERRPAYMENTTYPE:
                    $message = 
                        '<ul class="woocommerce-error">' .
                            '<li>'.__("Can't find Paylinks payment type. Please contact your merchant about this issue.", 'wc-gateway-paylinks').'</li>' .
                        '</ul>';
                    break;
				default:
					break;
			}
			
			echo ( json_encode( array('success'=> $status, 'message' => $message)) );
		} 
    } // end of class
} 