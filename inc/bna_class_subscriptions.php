<?php
/**
 * Woocommerce BNA Gateway
 *
 * @author 		BNA
 * @category 	'BNASubscription' Class
 * @version     	1.0
 */

if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

require_once dirname( __FILE__ ) . "/bna_class_jsonmessage.php";

/**
 * BNA plugin subscription class
 *
 * @class 		BNASubscriptions
 * @new			BNASubscriptions
 * @version		1.0.0
 * @package		WooCommerce/Classes/Payment
 */
if ( ! class_exists( 'BNASubscriptions' ) ) {

	class BNASubscriptions {
	 
		public function __construct ()
		{		
			$this->plugin_name = plugin_basename( dirname (dirname( __FILE__ ) ) );
			$this->plugin_url = trailingslashit( plugin_dir_url( dirname( __FILE__ ) ) );

            add_action( 'wp_ajax_delete_subscription', array( $this, 'ajax_delete_subscription' ) );
            add_action( 'wp_ajax_suspend_subscription', array( $this, 'ajax_suspend_subscription' ) );
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
			
			if ( isset( $result['metadata']['invoiceId'] ) ) { $invoice_id = $result['metadata']['invoiceId']; }
			
			if ( empty( $invoice_id ) ) exit();
			
			$base_order = wc_get_order( $invoice_id );		

            $subscription =  $wpdb->get_results(
                "SELECT * FROM " . $wpdb->prefix . BNA_TABLE_RECURRING . " WHERE recurringId='{$result['id']}'"
            );
            
            unset( $result['customerInfo'] );
			unset( $result['paymentMethods'] );

            if ( empty( $subscription ) || count( $subscription ) < 1 ) {
				
				$wpdb->insert( 
                    $wpdb->prefix . BNA_TABLE_RECURRING,  
                    array( 
                        'user_id'				=> esc_html( $base_order->get_user_id() ),
                        'order_id'		        => esc_html( $invoice_id ),
                        'recurringId'		    => esc_html( $result['id'] ),
                        'recurring'		        => esc_html( $result['recurrence'] ),
                        'status'		        	=> esc_html( $result['status'] ),
                        'startDate'		        => esc_html( $result['startPaymentDate'] ),
                        'nextChargeDate'	=> esc_html( $result['remainingPayments'] ),
                        'expire'		        	=> empty( $result['expire'] ) ? "" : esc_html( $result['expire'] ),
                        'numberOfPayments'   => isset( $result['remainingPayments'] ) ? esc_html( $result['remainingPayments'] ) : -1,
                        'recurringDescription' => json_encode( $result ),
                    ),
                    array( 
                        '%d','%s','%s','%s','%s','%s','%s','%s','%s','%s'
                    )
                );
            } else {
				$json = json_encode( $result );
				$result_status = esc_html( $result['status'] );
				$result_startPaymentDate = isset( $result['startPaymentDate'] ) ? esc_html( $result['startPaymentDate'] ) : '';
				$result_recurrence = esc_html( $result['recurrence'] );
				$result_remainingPayments = esc_html( $result['remainingPayments'] );
				$result_id = esc_html( $result['id'] );
				
                $wpdb->query("UPDATE " . $wpdb->prefix . BNA_TABLE_RECURRING
                    ." SET "
                        ."status='{$result_status}', "
                        .( isset( $result['startPaymentDate'] ) 
                            ? "startDate='{$result_startPaymentDate}', "
                            : ""
                        )
                        ."recurring='{$result_recurrence}', "
                        ."numberOfPayments='{$result_remainingPayments}', "
                        ."recurringDescription='{$json}' "
                    ." WHERE recurringId='{$result_id}'"
                );
            }
		}

		/**
		 * Creating an order in manual mode
		 * @since		1.0.0
		 * @param int original_order_id
		 */
		public static function create_subscription_order( $original_order_id ) 
		{
			global $wpdb, $woocommerce;
		
			$original_order = new WC_Order( $original_order_id );
			$original_data = $original_order->get_data();

			// Create Order	
			$order = wc_create_order();
			$order_id = $order->get_id();							
			$order->set_parent_id( $original_order_id );
						
			$order->set_currency( $original_data['currency'] );
			$order->set_prices_include_tax( $original_data['prices_include_tax'] );
			$order->set_discount_total( $original_data['discount_total'] );
			$order->set_discount_tax( $original_data['discount_tax'] );
			$order->set_shipping_total( $original_data['shipping_total'] );
			$order->set_shipping_tax( $original_data['shipping_tax'] );
			$order->set_cart_tax( $original_data['cart_tax'] );
			$order->set_total( $original_data['total'] );
			//$order->set_total_tax( $original_data['total_tax'] );
			$order->set_customer_id( $original_data['customer_id'] );
			
			$address_billing = array(
				'first_name' => $original_data['billing']['first_name'],
				'last_name'  => $original_data['billing']['last_name'],
				'company'    => $original_data['billing']['company'],
				'email'      => $original_data['billing']['email'],
				'phone'      => $original_data['billing']['phone'],
				'address_1'  => $original_data['billing']['address_1'],
				'address_2'  => $original_data['billing']['address_2'],
				'city'       => $original_data['billing']['city'],
				'state'      => $original_data['billing']['state'],
				'postcode'   => $original_data['billing']['postcode'],
				'country'    => $original_data['billing']['country']
			);
			$order->set_address( $address_billing, 'billing' );
			
			$address_shipping = array(
				'first_name' => $original_data['shipping']['first_name'],
				'last_name'  => $original_data['shipping']['last_name'],
				'company'    => $original_data['shipping']['company'],
				'phone'      => $original_data['shipping']['phone'],
				'address_1'  => $original_data['shipping']['address_1'],
				'address_2'  => $original_data['shipping']['address_2'],
				'city'       => $original_data['shipping']['city'],
				'state'      => $original_data['shipping']['state'],
				'postcode'   => $original_data['shipping']['postcode'],
				'country'    => $original_data['shipping']['country']
			);
			$order->set_address( $address_shipping, 'shipping' );
			
			$order->set_payment_method( $original_data['payment_method'] );
			$order->set_payment_method_title( $original_data['payment_method_title'] );

			if ( is_wp_error( $order_id ) ) {
				error_log( "Unable to create order:" . $order_id->get_error_message() );
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
				
				// Add Line Items		
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

				// Copy shipping items and shipping item meta from original order
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
				$order->payment_complete();
		
				//6 Set Order Status to processing to trigger initial emails to end user and vendor
				$order->update_status('processing');
				$order->add_order_note( __( "This recurring order by BNA payment.", 'wc-bna-gateway' ) );
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

			if ( isset( $_POST['nonce'] ) ) {
				if ( ! wp_verify_nonce( $_POST['nonce'], BNA_CONST_NONCE_NAME ) ) {
					BNAJsonMsgAnswer::send_json_answer( BNA_MSG_ERRORNONCE );
					wp_die();
				}
								
				$subscription_id = $_POST['id'];
				if ( empty( $subscription_id ) ) {
					BNAJsonMsgAnswer::send_json_answer( BNA_MSG_DELPAYMENT_ERRORID  );
					wp_die();
				}

				$args = WC_BNA_Gateway::get_merchant_params();
				if ( empty( $args ) ) {
					BNAJsonMsgAnswer::send_json_answer( BNA_MSG_ERRORPARAMS );
					wp_die();
				}

				$api = new BNAExchanger($args);

				$user_id = get_current_user_id();
				$payorID = get_user_meta ( $user_id, 'payorID', true );
				
				$reccuringInfo =  $wpdb->get_row( "SELECT * FROM " . $wpdb->prefix . BNA_TABLE_RECURRING . " WHERE id={$subscription_id}" );

				if ( empty( $reccuringInfo ) || empty( $payorID ) ) {
					BNAJsonMsgAnswer::send_json_answer( BNA_MSG_ERRORPAYOR );
					wp_die();
				}
				
				$response = $api->query(
					$args['serverUrl'] . '/' . $args['protocol'] . '/subscription/' . $reccuringInfo->recurringId,  
					'',
					'DELETE'
				);

				empty( $response ) ?
						BNAJsonMsgAnswer::send_json_answer( BNA_MSG_DELPAYMENT_SUCCESS ) :
						BNAJsonMsgAnswer::send_json_answer( BNA_MSG_DELPAYMENT_ERROR ) ;
			}
		
			wp_die();
		}
		
		/**
		 * Suspend a subscription in my-account
		 * @since		1.0.0
		 */
        public function ajax_suspend_subscription() 
		{
			global $wpdb;

			if ( isset( $_POST['nonce'] ) ) {
				if ( ! wp_verify_nonce( $_POST['nonce'], BNA_CONST_NONCE_NAME ) ) {
					BNAJsonMsgAnswer::send_json_answer( BNA_MSG_ERRORNONCE );
					wp_die();
				}
								
				$subscription_id = $_POST['id'];
				
				if ( empty( $subscription_id ) ) {
					BNAJsonMsgAnswer::send_json_answer( BNA_MSG_DELPAYMENT_ERRORID  );
					wp_die();
				}

				$args = WC_BNA_Gateway::get_merchant_params();
				if ( empty( $args ) ) {
					BNAJsonMsgAnswer::send_json_answer( BNA_MSG_ERRORPARAMS );
					wp_die();
				}

				$api = new BNAExchanger($args);

				$user_id = get_current_user_id();
				$payorID = get_user_meta ( $user_id, 'payorID', true );
				
				$reccuringInfo =  $wpdb->get_row( "SELECT * FROM " . $wpdb->prefix . BNA_TABLE_RECURRING . " WHERE id={$subscription_id}" );

				if ( empty( $reccuringInfo ) || empty( $payorID ) ) {
					BNAJsonMsgAnswer::send_json_answer( BNA_MSG_ERRORPAYOR );
					wp_die();
				}
				
				if ( $_POST['suspend'] === 'yes' ) {
					$suspend = true;
				} elseif ( $_POST['suspend'] === 'no' ) {
					$suspend = false;
				}
				$data = array(
					'suspend' => $suspend
				);
			
				$response = $api->query(
					$args['serverUrl'] . '/' . $args['protocol'] . '/subscription/' . $reccuringInfo->recurringId . '/suspend',  
					$data,
					'PATCH'
				);
				
				$response = json_decode( $response, true );

				! empty( $response['id'] ) ?
					BNAJsonMsgAnswer::send_json_answer( BNA_MSG_SUSPENDPAYMENT_SUCCESS ) :
					BNAJsonMsgAnswer::send_json_answer( BNA_MSG_SUSPENDPAYMENT_ERROR );
			}
		
			wp_die();
		}
		
    }//end of class
}// end of class_exists
