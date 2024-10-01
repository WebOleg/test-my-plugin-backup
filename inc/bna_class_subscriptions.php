<?php
/**
 * Woocommerce BNA Gateway
 *
 * @author 		BNA
 * @category 	'BNASubscription' Class
 * @version     	1.0
 */

if ( !defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

require_once dirname(__FILE__). "/bna_class_jsonmessage.php";

/**
 * BNA plugin subscription class
 *
 * @class 		BNASubscriptions
 * @new			BNASubscriptions
 * @version		1.0.0
 * @package		WooCommerce/Classes/Payment
 */
if (!class_exists('BNASubscriptions')) {

	class BNASubscriptions {
	 
		public function __construct ()
		{		
			$this->plugin_name = plugin_basename(dirname(dirname(__FILE__)));
			$this->plugin_url = trailingslashit(plugin_dir_url(dirname(__FILE__)));

            add_action('wp_ajax_delete_subscription', array(&$this, 'ajax_delete_subscription'));
        }

		/**
		 * Endpoint for subscription processing
		 * @since		1.0.0
		 * @param json string $result
		 */
        public static function endpoint_subscriptions( $result )
		{
			global $wpdb, $woocommerce;
	
			if ( ! isset( $result['id'] ) ) exit();

			$base_order = wc_get_order( $result['invoiceInfo']['invoiceId'] );

            $subscription =  $wpdb->get_results(
                "SELECT * FROM ".$wpdb->prefix.BNA_TABLE_RECURRING." WHERE recurringId='{$result['id']}'" // subscriptionId
            );

            if ( empty( $subscription ) || count( $subscription ) < 1) {
				unset( $result['customerInfo'] );
				unset( $result['paymentMethods'] );
				
				$wpdb->insert( 
                    $wpdb->prefix . BNA_TABLE_RECURRING,  
                    array( 
                        'user_id'				=> $base_order->get_user_id(),
                        'order_id'		        => $result['invoiceInfo']['invoiceId'],
                        'recurringId'		    => $result['id'],
                        'recurring'		        => $result['recurrence'],
                        'status'		        	=> $result['status'],
                        'startDate'		        => $result['startPaymentDate'],
                        'nextChargeDate'	=> $result['remainingPayments'],
                        'expire'		        	=> empty( $result['expire'] ) ? "" : $result['expire'],
                        'numberOfPayments'   => isset( $result['remainingPayments'] ) ? $result['remainingPayments'] : -1,
                        'recurringDescription' => json_encode( $result ),
                    ),
                    array( 
                        '%d','%s','%s','%s','%s','%s','%s','%s','%s','%s'
                    )
                );
            } else {
				$json = json_encode( $result );
                $wpdb->query("UPDATE ".$wpdb->prefix.BNA_TABLE_RECURRING
                    ." SET "
                        ."status='{$result['status']}', "
                        .( isset($result['startPaymentDate']) 
                            ? "startDate='{$result['startPaymentDate']}', "
                            : ""
                        )
                        ."recurring='{$result['recurrence']}', "
                        ."numberOfPayments='{$result['remainingPayments']}', "
                        ."recurringDescription='{$json}' "
                    ." WHERE recurringId='{$result['id']}'"
                );
            }
		}

		/**
		 * Creating an order in manual mode
		 * @since		1.0.0
		 * @param int original_order_id
		 */
		public static function create_subscription_order($original_order_id) 
		{
			global $wpdb, $woocommerce;
		
			$original_order = new WC_Order( $original_order_id );
			$currentUser = $original_order->get_user_id(); 

			//1 Create Order
			//$order = new WC_Order($order_id);		
			$order = wc_create_order();
			$order_id = $order->get_id();							
			$order->set_parent_id($original_order_id);
			$order->set_customer_id( $currentUser );

			if ( is_wp_error( $order_id ) ) {
				error_log("Unable to create order:" . $order_id->get_error_message());
				wp_die();
			} else {
						
				$order_data =  array(
					//'post_type'     => 'shop_order',
					'ID'                =>  $order_id,
					'post_title'    => sprintf( __( 'Recurring Order &ndash; %s', 'woocommerce' ), strftime( _x( '%b %d, %Y @ %I:%M %p', 'Order date parsed by strftime', 'woocommerce' ) ) ),
					'post_status'   => 'publish',
					'ping_status'   => 'closed',
					'post_excerpt'  => 'Recurring Order based on original order ' . $original_order_id,
					'post_author'   => $original_order->get_user_id(),
					'post_password' => uniqid( 'order_' )   // Protects the post just in case
				);			
				$order_id = wp_insert_post( $order_data, true );				
				
				//2 Update Order Header		
				update_post_meta( $order_id, '_order_shipping',         get_post_meta($original_order_id, '_order_shipping', true) );
				update_post_meta( $order_id, '_order_discount',         get_post_meta($original_order_id, '_order_discount', true) );
				update_post_meta( $order_id, '_cart_discount',          get_post_meta($original_order_id, '_cart_discount', true) );
				update_post_meta( $order_id, '_order_tax',              get_post_meta($original_order_id, '_order_tax', true) );
				update_post_meta( $order_id, '_order_shipping_tax',     get_post_meta($original_order_id, '_order_shipping_tax', true) );
				update_post_meta( $order_id, '_order_total',            get_post_meta($original_order_id, '_order_total', true) );
		
				update_post_meta( $order_id, '_order_key',              'wc_' . apply_filters('woocommerce_generate_order_key', uniqid('order_') ) );
				update_post_meta( $order_id, '_customer_user',          get_post_meta($original_order_id, '_customer_user', true) );
				update_post_meta( $order_id, '_order_currency',         get_post_meta($original_order_id, '_order_currency', true) );
				update_post_meta( $order_id, '_prices_include_tax',     get_post_meta($original_order_id, '_prices_include_tax', true) );
				update_post_meta( $order_id, '_customer_ip_address',    get_post_meta($original_order_id, '_customer_ip_address', true) );
				update_post_meta( $order_id, '_customer_user_agent',    get_post_meta($original_order_id, '_customer_user_agent', true) );
		
				//3 Add Billing Fields
				update_post_meta( $order_id, '_billing_city',           get_post_meta($original_order_id, '_billing_city', true));
				update_post_meta( $order_id, '_billing_state',          get_post_meta($original_order_id, '_billing_state', true));
				update_post_meta( $order_id, '_billing_postcode',       get_post_meta($original_order_id, '_billing_postcode', true));
				update_post_meta( $order_id, '_billing_email',          get_post_meta($original_order_id, '_billing_email', true));
				update_post_meta( $order_id, '_billing_phone',          get_post_meta($original_order_id, '_billing_phone', true));
				update_post_meta( $order_id, '_billing_address_1',      get_post_meta($original_order_id, '_billing_address_1', true));
				update_post_meta( $order_id, '_billing_address_2',      get_post_meta($original_order_id, '_billing_address_2', true));
				update_post_meta( $order_id, '_billing_country',        get_post_meta($original_order_id, '_billing_country', true));
				update_post_meta( $order_id, '_billing_first_name',     get_post_meta($original_order_id, '_billing_first_name', true));
				update_post_meta( $order_id, '_billing_last_name',      get_post_meta($original_order_id, '_billing_last_name', true));
				update_post_meta( $order_id, '_billing_company',        get_post_meta($original_order_id, '_billing_company', true));
		
				//4 Add Shipping Fields
				update_post_meta( $order_id, '_shipping_country',       get_post_meta($original_order_id, '_shipping_country', true));
				update_post_meta( $order_id, '_shipping_first_name',    get_post_meta($original_order_id, '_shipping_first_name', true));
				update_post_meta( $order_id, '_shipping_last_name',     get_post_meta($original_order_id, '_shipping_last_name', true));
				update_post_meta( $order_id, '_shipping_company',       get_post_meta($original_order_id, '_shipping_company', true));
				update_post_meta( $order_id, '_shipping_address_1',     get_post_meta($original_order_id, '_shipping_address_1', true));
				update_post_meta( $order_id, '_shipping_address_2',     get_post_meta($original_order_id, '_shipping_address_2', true));
				update_post_meta( $order_id, '_shipping_city',          get_post_meta($original_order_id, '_shipping_city', true));
				update_post_meta( $order_id, '_shipping_state',         get_post_meta($original_order_id, '_shipping_state', true));
				update_post_meta( $order_id, '_shipping_postcode',      get_post_meta($original_order_id, '_shipping_postcode', true));
		
				//5 Add Line Items		
				foreach($original_order->get_items() as $originalOrderItem){
		
					$itemName 	= $originalOrderItem['name'];
					$qty 		= $originalOrderItem['qty'];
					$lineTotal 	= $originalOrderItem['line_total'];
					$lineTax 	= $originalOrderItem['line_tax'];
					$productID 	= $originalOrderItem['product_id'];
		
					$item_id = wc_add_order_item( $order_id, array(
						'order_item_name'       => $itemName,
						'order_item_type'       => 'line_item'
					) );
		
					wc_add_order_item_meta( $item_id, '_qty', $qty );
					//wc_add_order_item_meta( $item_id, '_tax_class', $_product->get_tax_class() );
					wc_add_order_item_meta( $item_id, '_product_id', $productID );
					//wc_add_order_item_meta( $item_id, '_variation_id', $values['variation_id'] );
					wc_add_order_item_meta( $item_id, '_line_subtotal', wc_format_decimal( $lineTotal ) );
					wc_add_order_item_meta( $item_id, '_line_total', wc_format_decimal( $lineTotal ) );
					wc_add_order_item_meta( $item_id, '_line_tax', wc_format_decimal( '0' ) );
					wc_add_order_item_meta( $item_id, '_line_subtotal_tax', wc_format_decimal( '0' ) );
		
				}

				//6 Copy shipping items and shipping item meta from original order
				$original_order_shipping_items = $original_order->get_items('shipping');
				foreach ( $original_order_shipping_items as $original_order_shipping_item ) {
		
					$item_id = wc_add_order_item( $order_id, array(
						'order_item_name'       => $original_order_shipping_item['name'],
						'order_item_type'       => 'shipping'
					) );
		
					if ( $item_id ) {
						wc_add_order_item_meta( $item_id, 'method_id', $original_order_shipping_item['method_id'] );
						wc_add_order_item_meta( $item_id, 'cost', wc_format_decimal( $original_order_shipping_item['cost'] ) );
					}
		
				}
		
				// Store coupons
				$original_order_coupons = $original_order->get_items('coupon');
				foreach ( $original_order_coupons as $original_order_coupon ) {
					$item_id = wc_add_order_item( $order_id, array(
						'order_item_name'       => $original_order_coupon['name'],
						'order_item_type'       => 'coupon'
					) );
					// Add line item meta
					if ( $item_id ) {
						wc_add_order_item_meta( $item_id, 'discount_amount', $original_order_coupon['discount_amount'] );
					}
				}
		
				//Payment Info
				update_post_meta( $order_id, '_payment_method',         get_post_meta($original_order_id, '_payment_method', true) );
				update_post_meta( $order_id, '_payment_method_title',   get_post_meta($original_order_id, '_payment_method_title', true) );
				$order->payment_complete();
		
				//6 Set Order Status to processing to trigger initial emails to end user and vendor
				$order->update_status('processing');
				$order->add_order_note("This recurring order by BNA payment.");
			}

			return $order_id;
		}

		/**
		 * Deleting a subscription in my-account
		 * @since		1.0.0
		 */
        public function ajax_delete_subscription() 
		{
			global $wpdb;

			$paymentTypeMethod	= 'account';
			$paymentMethod 		= 'delete-recurring';
			
			if( isset($_POST['nonce'])) {
				if ( !wp_verify_nonce( $_POST['nonce'], BNA_CONST_NONCE_NAME) ) {
					BNAJsonMsgAnswer::send_json_answer(BNA_MSG_ERRORNONCE);
					wp_die();
				}
								
				$subscription_id = $_POST['id'];
				if ( empty($subscription_id) ) {
					BNAJsonMsgAnswer::send_json_answer(BNA_MSG_DELPAYMENT_ERRORID);
					wp_die();
				}

				$args = WC_BNA_Gateway::get_merchant_params();
				if ( empty($args) ) {
					BNAJsonMsgAnswer::send_json_answer(BNA_MSG_ERRORPARAMS);
					wp_die();
				}

				$api = new BNAExchanger($args);

				$user_id = get_current_user_id();
				$payorID = get_user_meta ($user_id, 'payorID', true);
				
				$reccuringInfo =  $wpdb->get_row("SELECT * FROM ".$wpdb->prefix.BNA_TABLE_RECURRING." WHERE id={$subscription_id}");

				if ( empty($reccuringInfo) || empty($payorID) ) {
					BNAJsonMsgAnswer::send_json_answer(BNA_MSG_ERRORPAYOR);
					wp_die();
				}

				$data = (object) [
					"payorId" => $payorID,
					"recurringId" => $reccuringInfo->recurringId
				];


				$response = $api->query(
					$args['serverUrl'].'/'.$args['protocol'].'/'.$paymentTypeMethod.'/'.$paymentMethod,  
					$data,
					'DELETE'
				);
	
				empty($response['success']) ? 
						BNAJsonMsgAnswer::send_json_answer(BNA_MSG_DELPAYMENT_ERROR) : 
						BNAJsonMsgAnswer::send_json_answer(BNA_MSG_DELPAYMENT_SUCCESS);
			}
		
			wp_die();
		}
    }//end of class
}// end of class_exists
