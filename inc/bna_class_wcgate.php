<?php
/**
 * Woocommerce BNA Smart Payment Gateway
 *
 * @author 		BNA
 * @category 	'Payment Gateway' Class 
 * @version     1.0
 */

if ( !defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

require_once dirname(__FILE__). "/bna_class_jsonmessage.php";

/**
 * Registering the BNA Gateway
 * @since 1.0.0
 * @param array $gateways all woo gateways
 * @return array $gateways + our custom
 */
function wc_bna_add_to_gateways( $gateways ) 
{
	$gateways[] = 'WC_BNA_Gateway';
	return $gateways;
}
add_filter( 'woocommerce_payment_gateways', 'wc_bna_add_to_gateways' );


/**
 * Adds plugin page links
 * 
 * @since 1.0.0
 * @param array $links all plugin links
 * @return array $links all plugin links + our custom links
 */
function wc_bna_gateway_plugin_links( $links ) 
{
	$plugin_links = array(
		'<a href="' . admin_url( 'admin.php?page=wc-settings&tab=checkout&section=bna_gateway' ) . '">' 
			. __( 'Configure', 'wc-bna-gateway' ) . '</a>'
	);

	return array_merge( $plugin_links, $links );
}
add_filter( 'plugin_action_links_' . plugin_basename( __FILE__ ), 'wc_bna_gateway_plugin_links' );


/**
 * BNA Payment Gateway (extend WC_Payment_Gateway)
 *
 * @class 		WC_BNA_Gateway
 * @extends		WC_Payment_Gateway
 * @version		1.0.0
 * @package		WooCommerce/Classes/Payment
 */
add_action( 'plugins_loaded', 'wc_bna_gateway_init', 10 );
add_filter( 'request', array( 'WC_BNA_Gateway' , 'get_request'));

function wc_bna_gateway_init() {

	class WC_BNA_Gateway extends WC_Payment_Gateway {

		public static $order_id;

		/**
		 * Constructor for the gateway.
		 */
		public function __construct() {
	  
			$intro			 		  = "BNA Smart Payment Gateway";
			$this->id                 = 'bna_gateway';
			$this->has_fields         = false;
			$this->method_title       = __( 'BNA', 'wc-bna-gateway' );
			$this->method_description = __( $intro, 'wc-bna-gateway' );
			$this->supports           = array( 'products', 'refunds' );
		  
			// Load the settings.
			$this->init_form_fields();
			$this->init_settings();

			self::get_fees_request();
		  
			// Define user set variables
			$this->title        = $this->get_option( 'title' );
			$this->description  = $this->get_option( 'description' );
			$this->instructions = $this->get_option( 'instructions', $this->description );

			$this->plugin_name = plugin_basename(dirname(dirname(__FILE__)));
			$this->plugin_url = trailingslashit(plugin_dir_url(dirname(__FILE__)));

			add_action( 'woocommerce_thankyou_' . $this->id, array( &$this, 'thankyou_page' ) );
			add_action( 'woocommerce_thankyou', array( &$this, 'thankyou_page' ));

			// Customer Emails
			add_action( 'woocommerce_email_before_order_table', array( &$this, 'email_instructions' ), 10, 3 );

			add_action( 'woocommerce_update_options_payment_gateways_' . $this->id, array( &$this, 'process_admin_options' ) );
			add_action( 'wp_enqueue_scripts', array( &$this, 'site_load_styles' ) );
			
			add_action( 'woocommerce_email_actions', array( &$this, 'send_refund_email' ) );
		}

		/**
		* Loading the list of styles and scripts
		* @since		1.0.0
		*/
		public function site_load_styles()
		{
			$fees = get_option( 'wc_bna_gateway_fees' );
			wp_register_script( 'bna-payment-js', $this->plugin_url .'assets/lib/payment/payment.js', '', '', true );
			wp_localize_script( 'jquery', 'bna_fee',
				array(
					"creditCardPercentageFee" => $fees['creditCardPercentage'],
					"creditCardFlatFee" => $fees['creditCardFlat'],
					"etransferPercentageFee" => $fees['eTransferPercentage'],
					"etransferFlatFee" => $fees['eTransferFlat'],
					"directDebitPercentageFee" => $fees['eftPercentage'],
					"directDebitFlatFee" => $fees['eftFlat'], 
				)	
			);
			wp_register_style( 'bna-datepicker-css', $this->plugin_url . 'assets/lib/datepicker/css/datepicker.min.css' );
			wp_register_script( 'bna-datepicker-js', $this->plugin_url.'assets/lib/datepicker/js/datepicker.min.js', array('jquery'), '1.0.0', true );													
		}

		/**
		* Send refund email
		* @since		1.0.0
		* @return email actions list
		*/
		public function send_refund_email()
		{
			$email_actions[] = 'woocommerce_order_status_refunded';
			return $email_actions;
		}

		/**
		* Recalculation of taxes in manual mode
		* @since		1.0.0
		* @param object $order 
		* @param float $percent
		* @param float $flat
		* @return $surcharge
		*/
		public static function add_payment_fee($order, $percent, $flat) 
		{
			global $woocommerce;

			$amount = $woocommerce->cart->cart_contents_total + $woocommerce->cart->tax_total + $order->get_total_shipping();
			$surcharge = $amount * $percent / 100 + $flat;
			$hst = $surcharge * 13 / 100; //HST Ontario
			$surcharge = round( round($surcharge, 2) + round($hst, 2), 2 );

			$args = WC_BNA_Gateway::get_merchant_params();
			if ( empty( $args ) ) {
				wc_add_notice(  'Error configuring payment parameters.', 'error' );
				return false;
			}

			if ( ($percent > 0 || $flat > 0) && $args['applyFee'] == 'yes' ) {

				$country_code = $order->get_shipping_country();

				// Set the array for tax calculations
				$calculate_tax_for = array(
					'country' => $country_code, 
					'state' => '', 
					'postcode' => '', 
					'city' => ''
				);

				$item_fee = new WC_Order_Item_Fee();

				$item_fee->set_name( __('BNA fee', 'wc-bna-gateway') ); 
				$item_fee->set_amount( $surcharge ); 
				$item_fee->set_tax_class( '' ); 
				$item_fee->set_tax_status( 'none' ); 
				$item_fee->set_total( $surcharge ); 
				
				// Calculating Fee taxes
				$item_fee->calculate_taxes( $calculate_tax_for );
				
				// Add Fee item to the order
				$order->add_item( $item_fee );
				$order->calculate_totals();
				$order->save();
            }

			return $surcharge;
        }

		/**
		* Initial Gate Settings
		* @since		1.0.0
		*/
		public function init_form_fields() {
	  
			$this->form_fields = array( 
		  
				'enabled' => array(
					'title'   => __( 'Enable/Disable', 'wc-bna-gateway' ),
					'type'    => 'checkbox',
					'label'   => __( 'Enable BNA Payment', 'wc-bna-gateway' ),
					'default' => 'yes'
				),
				'title' => array(
					'title'       => __( 'Title', 'wc-bna-gateway' ),
					'type'        => 'text',
					'description' => __( 'Name of the payment gateway on the checkout page', 'wc-bna-gateway'),
					'default'     => __( 'BNA Payment', 'wc-bna-gateway' ),
					'desc_tip'    => true,
				),
				'description' => array(
					'title'       => __( 'Description', 'wc-bna-gateway' ),
					'type'        => 'textarea',
					'description' => __( 'Description of the payment method. You can write whatever you think is necessary to describe the payment service', 'wc-bna-gateway' ),
					'default'     => 'Online payments by card visa, amex, mastercard, etc., through the payment service',
					'desc_tip'    => true,
				),
				'instructions' => array(
					'title'       => __( 'Instructions for the customer', 'wc-bna-gateway' ),
					'type'        => 'textarea',
					'description' => __( 'Instructions for the customer when creating an order. The notification appears both on the thankyou_page and in the body of the email', 'wc-bna-gateway' ),
					'default'     => '',
					'desc_tip'    => true,
				),
				'applyFee' => array(
					'title'   => __( 'Enable/Disable', 'wc-bna-gateway' ),
					'type'    => 'checkbox',
					'label'   => __( 'Apply BNA Payment Fee', 'wc-bna-gateway' ),
					'default' => 'false'
				),
				'environment' => array(
					'title'   => __( 'Environment', 'wc-bna-gateway' ),
					'type'    => 'select',
					'description'  => __( 'Check stage or production', 'wc-bna-gateway' ),
					'options' => array( 'https://dev-api-service.bnasmartpayment.com' => 'Stage', 'https://production-api-service.bnasmartpayment.com' => 'Production' ),
					'default' => 'stage'
				),
				'login' => array(
					'title'       => __( 'Login', 'wc-bna-gateway' ),
					'type'        => 'text',
				),
				'secretKey' => array(
					'title'       => __( 'Secret key', 'wc-bna-gateway' ),
					'type'        => 'password'
				),
				
				'bna-colors-title' =>array(
					'title' => __( 'Colors options', 'wc-bna-gateway' ),
					'type'  => 'title',
					'description'  => __( 'Here you can change the default colors.', 'wc-bna-gateway' ),
				),
				'bna-font-color' => array(
					'title'       => __( 'Font color', 'wc-bna-gateway' ),
					'type'      => 'color',
					'default' => '#646464'
				),
				'bna-button-color' => array(
					'title'       => __( 'Button color', 'wc-bna-gateway' ),
					'type'      => 'color',
					'default' => '#00A0E3'
				),
				'bna-line-color' => array(
					'title'       => __( 'Line color', 'wc-bna-gateway' ),
					'type'      => 'color',
					'default' => '#CCC'
				),
				'bna-background-color' => array(
					'title'       => __( 'Background color', 'wc-bna-gateway' ),
					'type'      => 'color',
					'default' => '#FFF'
				),
				'bna-privacy-policy-title' =>array(
					'title' => __( 'Privacy policy.', 'wc-bna-gateway' ),
					'type'  => 'title',
					'description'  => __( 'Here you can add privacy policy links.', 'wc-bna-gateway' ),
				),
				'bna-recurring-pp-link' => array(
					'title'       => __( 'Recurring privacy policy link', 'wc-bna-gateway' ),
					'type'      => 'url',
					'description'	=> __( 'Recurring Payment Agreement.', 'wc-bna-gateway' ),
				),
			);
		}
	
		/**
		 * Add instructions to email.
		 * @since		1.0.0
		 * @param WC_Order $order
		 * @param bool $sent_to_admin
		 * @param bool $plain_text
		 */
		public function email_instructions( $order, $sent_to_admin, $plain_text = false ) {
		
			if ( $this->instructions && ! $sent_to_admin && $this->id === $order->payment_method && $order->has_status( 'on-hold' ) ) {
				echo wpautop( wptexturize( $this->instructions ) ) . PHP_EOL;
			}
		}


		/**
		 * Output the field with information about the payment
		 * @since		1.0.0
		 * @param WC_Order $order
		 * @param bool $plain_text
		 */
		public function payment_fields() {

			global $wpdb;

			wp_enqueue_script( 'bna-payment-js' );
			wp_enqueue_style( 'bna-datepicker-css' );
			wp_enqueue_script( 'bna-datepicker-js' );

			if ( $this->description ) {
				echo wpautop( wp_kses_post( $this->description ) );
			}
			
			$paymentMethods = null;
			$payorID = get_user_meta( get_current_user_id(), 'payorID', true );
			if ( !empty($payorID) ) {
				$paymentMethods = $wpdb->get_results(
					"SELECT * FROM " . $wpdb->prefix.BNA_TABLE_SETTINGS." WHERE payorId='$payorID'"
				);
			}

			ob_start();
			
			include_once  dirname(__FILE__) . '/../tpl/tpl_checkout_fields.php';

			$answer = ob_get_contents();
			ob_end_clean();

			echo $answer;
		}

		/**
		 * Get gateway settings
		 * @since		1.0.0
		 */
		public static function get_merchant_params()
		{
			global $wpdb;

			$params = $wpdb->get_var( $wpdb->prepare( "SELECT option_value FROM $wpdb->options WHERE option_name=%s", 
			'woocommerce_bna_gateway_settings' ) );
	
			if ( ! $params ) return null;

			preg_match_all( '`"([^"]*)"`', $params, $params );

			return array(
				'serverUrl' 	=> WC_BNA_Gateway::get_next_arrval( $params[1], 'environment' ),
				'protocol' 		=> 'v1',
				'applyFee'		=> WC_BNA_Gateway::get_next_arrval( $params[1], 'applyFee' ),
				'secretKey'	=> WC_BNA_Gateway::get_next_arrval( $params[1], 'secretKey' ),
				'login'			=> WC_BNA_Gateway::get_next_arrval( $params[1], 'login' )
			);
		}

		/**
		 * Search method in a serialized array
		 * @since		1.0.0
 		 * @param array $array
 		 * @param int $key
		 * @return string element
		 */
		public static function get_next_arrval( $array, $key ) {
			$fbreak = 0;
			foreach ( $array as $arr ) {
				
				if ( $fbreak ) break;
				if ( $arr == $key ) {
					$fbreak ++; 
				}
			}
			
			return $fbreak ? $arr : null;
		 }
				
		/**
		 * Method of payment process
		 * @since		1.0.0
		 * @param int $order_id
		 * @return array
		 */
		public function process_payment( $order_id ) {
 
			global $wpdb;
			global $woocommerce;


			static::$order_id = $order_id;
			$order = wc_get_order( $order_id );

			$args = WC_BNA_Gateway::get_merchant_params();
			if ( empty( $args ) ) {
				wc_add_notice( 'Error configuring payment parameters.', 'error' );
				return false;
			}

			$fees = get_option( 'wc_bna_gateway_fees' );
			
			// products
			$items = [];
			foreach ( WC()->cart->get_cart() as $cart_item_key => $cart_item ) { 
				$item = array();
				$_woo_product = wc_get_product( $cart_item['product_id'] );

				$sku = $_woo_product->get_sku;
				$item['sku']= ! empty( $sku ) ? $sku : strval( $cart_item['product_id'] ); // if 'sku' not exist add 'product_id'
				$item['quantity'] = $cart_item['quantity']; 						
				$item['price'] = wc_get_price_including_tax( $_woo_product ); 		
				$item['amount'] = $item['price'] * $item['quantity'];				
				$item['description'] = $_woo_product->get_title(); 					

				$items[] = $item; 
			}

			$api = new BNAExchanger($args);

			if ( empty( $_POST['payment-type'] ) ) {
				throw new Exception( "Can't find BNA payment type" );
			}
			
			// customer info
			$customerInfo = array(
				'email'				=> $_POST['billing_email'],
				'firstName'		=> $_POST['billing_first_name'],
				'lastName'		=> $_POST['billing_last_name'],
				'phoneCode'		=> $_POST['billing_phone_code'],
				'phoneNumber'	=> $_POST['billing_phone'], // "phone"
				'address' => array(
					'streetNumber'	=> $_POST['billing_street_number'],
					'streetName'		=> $_POST['billing_street_name'],
					'city'					=> $_POST['billing_city'],
					'province'			=> WC()->countries->get_states( $_POST['billing_country'] )[$_POST[ 'billing_state' ]],  //$_POST[ 'billing_country' ] .'-'. $_POST[ 'billing_state' ],
					'country'			=> WC()->countries->countries[$_POST['billing_country']], //$_POST[ 'billing_country' ], 
					'postalCode'		=> $_POST['billing_postcode'],
				),
			);
			if ( ! empty( $_POST['billing_apartment'] ) ) { $customerInfo['address']['apartment'] = $_POST['billing_apartment']; }
			
			// data
			$data_subscription = array();
			$data = array( 
				'transactionTime' => date('Y-m-d\TH:i:sO'),
				'items'				=> $items,
				'applyFee'		=> $args['applyFee'] == 'yes' ? true : false,
				'subtotal'			=> 
					($woocommerce->cart->cart_contents_total + $woocommerce->cart->tax_total + $order->get_total_shipping()),
				'currency'			=> get_woocommerce_currency(),
				'metadata'		=> array(
					'invoiceId' => $order_id,				
				),					
			);

			$payorID = get_user_meta( get_current_user_id(), 'payorID', true );
			// if payor not exist - add customer info
			if ( ! empty( $payorID ) ) {
				$data['customerId'] = $payorID;
			} else {
				$data['customerInfo'] = $customerInfo;
			}
			
			// verify credit card
			if ( ! empty( $_POST['payment-type'] ) && $_POST['payment-type'] === 'card' ) {
				if ( $_POST['paymentMethodCC'] === 'new-card' ) {
					if ( ! empty( $_POST['cc_expire'] ) ) {
						$cc_expire = explode( '/', $_POST['cc_expire'] );
					}
					$cardNumber = str_replace( ' ', '', $_POST['cc_number'] );
					
					$data_verify = array(
						'transactionTime'	=> date('Y-m-d\TH:i:sO'),
						'customerId'			=> $payorID,
						'currency'				=> get_woocommerce_currency(),
						'paymentDetails'	=> array(
							'cardNumber'	=> $cardNumber,
							'cardHolder'		=> $_POST['cc_holder'],
							'cardType'			=> 'credit',
							'cardIdNumber'	=> $_POST['cc_code'],
							'expiryMonth'	=> trim( $cc_expire[0] ),
							'expiryYear'		=> trim( $cc_expire[1] ),
						)
					);
				
					$response_verify = $api->query(
						$args['serverUrl'] . '/' . $args['protocol'] . '/transaction/card/verify',
						$data_verify,
						'POST'
					);
				
					$response_verify = json_decode( $response_verify, true );
				
					if ( empty( $response_verify['id'] ) ) {
						wc_add_notice( 'Error in the credit card parameters.', 'error' );
						return false;
					}
				}
			}

			$paymentTypeMethod = '';
			// paymentType
			switch ( $_POST['payment-type'] ) { 
				case 'card':
					$paymentTypeMethod = 'card';
					if ( ! empty( $_POST['paymentMethodCC'] ) ) {
						if ( $_POST['paymentMethodCC'] === 'new-card' ) {
							//if ( ! empty( $_POST['paymentMethodCC'] ) && ! empty( $_POST['cc_expire'] ) ) {
								$cc_expire = explode( '/', $_POST['cc_expire'] );
							//}
							$cardNumber = str_replace( ' ', '', $_POST['cc_number'] );
							
							$params = array (
								'cardNumber'	=> $cardNumber,
								'cardHolder'		=> $_POST['cc_holder'],
								'cardType'			=> 'credit',
								'cardIdNumber'	=> $_POST['cc_code'],
								'expiryMonth'	=> trim( $cc_expire[0] ),
								'expiryYear'		=> trim( $cc_expire[1] ),
							);
							foreach ( $params as $p_key => $p_val )
								$data['paymentDetails'][$p_key] = $p_val;
						} else {
							$data['paymentDetails'] = array( "id" => $_POST['paymentMethodCC'] );
						}
					} elseif ( ! is_user_logged_in() && ! empty( $_POST['cc_holder'] ) && ! empty( $_POST['cc_number'] ) && ! empty( $_POST['cc_expire'] ) && ! empty( $_POST['cc_code'] ) ) {
						$cc_expire = explode( '/', $_POST['cc_expire'] );
						$cardNumber = str_replace( ' ', '', $_POST['cc_number'] );
							
						$params = array (
							'cardNumber'	=> $cardNumber,
							'cardHolder'		=> $_POST['cc_holder'],
							'cardType'			=> 'credit',
							'cardIdNumber'	=> $_POST['cc_code'],
							'expiryMonth'	=> trim( $cc_expire[0] ),
							'expiryYear'		=> trim( $cc_expire[1] ),
						);
						foreach ( $params as $p_key => $p_val )
							$data['paymentDetails'][$p_key] = $p_val;
					}
					self::add_payment_fee( $order, $fees->creditCardPercentageFee, $fees->creditCardFlatFee );
					break;
				case 'eft':
					$paymentTypeMethod = 'eft';
					if ( ! empty( $_POST[ 'paymentMethodDD' ] ) ) {
						if ( $_POST[ 'paymentMethodDD' ] === 'new-method' ) {
							$params = array (
								"bankNumber"		=> $_POST['bank_name'] !== 'other' ? $_POST['bank_name'] : $_POST['institutionNumber'],
								"accountNumber"	=> $_POST['accountNumber'],
								"transitNumber"	=> $_POST['transitNumber']
							);
							foreach ( $params as $p_key => $p_val )
									$data['paymentDetails'][$p_key] = $p_val;
						} else {
							$data['paymentDetails'] = array( "id" => $_POST['paymentMethodDD'] );
						}
					} elseif ( ! is_user_logged_in() && ! empty( $_POST['bank_name'] ) && ! empty( $_POST['accountNumber'] ) && ! empty( $_POST['transitNumber'] ) ) {
							$params = array (
								"bankNumber"		=> $_POST['bank_name'] !== 'other' ? $_POST['bank_name'] : $_POST['institutionNumber'],
								"accountNumber"	=> $_POST['accountNumber'],
								"transitNumber"	=> $_POST['transitNumber']
							);
							foreach ( $params as $p_key => $p_val )
									$data['paymentDetails'][$p_key] = $p_val;
					}
					self::add_payment_fee( $order, $fees->directDebitPercentageFee, $fees->directDebitFlatFee );
					break;
				case 'e-transfer':
					$paymentTypeMethod = 'e-transfer';
					$params = array (
						"interacEmail" => $_POST['email_transfer'],
					);
					foreach ( $params as $p_key => $p_val )
						$data['paymentDetails'][$p_key] = $p_val;

					self::add_payment_fee( $order, $fees->etransferPercentageFee, $fees->etransferFlatFee );
					break;
			}

			$paymentMethod = '';
			$is_subscription = isset( $_POST['create_subscription'] );
			// subscription
			if ( isset( $_POST['create_subscription'] ) ) { 
				$data_subscription = array();
				
				$data_subscription['paymentDetails'] = $data['paymentDetails'];
				
				$data_subscription['recurrence'] = $_POST['recurring'];
				if ( ! empty( $_POST['startDate'] ) && $_POST['startDate'] !== '0' ) {
					$data_subscription['startPaymentDate'] = date( 'Y-m-d\TH:i:sO', strtotime( $_POST['startDate'] ) );
				}
				if ( ! empty( $_POST['numberOfPayments'] ) && $_POST['numberOfPayments'] !== '0' ) {
					$data_subscription['remainingPayments'] = $_POST['numberOfPayments'];
				}
				$data_subscription['customerId'] = $payorID;
				$data_subscription['items'] = $items;
				$data_subscription['action'] = 'SALE';
				$data_subscription['paymentMethod'] = $paymentTypeMethod;
				$data_subscription['applyFee'] = $args['applyFee'] == 'yes' ? true : false;
				$data_subscription['subtotal'] = 
					( $woocommerce->cart->cart_contents_total + $woocommerce->cart->tax_total + $order->get_total_shipping() );
				$data_subscription['currency'] = get_woocommerce_currency();
				$data_subscription['invoiceInfo'] = array(
					'invoiceId' => strval( $order_id ),				
				);
			}

			//if ( is_user_logged_in() ) {
				//if ( empty( $_POST['save_payment'] ) ) {
					//if ( empty($payorID) ) {
						//$paymentMethod = $is_subscription 
							//? 'recurring-save-payor-payment'	
							//: 'one-time-save-payor-payment';
					//} else {
						//if ( empty($data->paymentMethodId) ) {
							//$paymentMethod = $is_subscription 
								//? 'recurring-existing-payor'
								//: 'one-time-existing-payor';
						//} else {
							//$paymentMethod = $is_subscription 
								//? 'recurring-existing-payor-existing-payment'
								//: 'one-time-existing-payor-existing-payment';
						//}
					//}
				//} else {
					//if ( empty($payorID) ) {
						//$paymentMethod = $is_subscription 
							//? 'recurring-save-payor-save-payment'
							//: "one-time-save-payor-save-payment";
					//} else {
						//$paymentMethod = $is_subscription 
							//? 'recurring-existing-payor-save-payment'
							//: 'one-time-existing-payor-save-payment';
					//}
				//}
			//} else {
				//$paymentMethod =  $is_subscription  
					//? 'recurring-payment'
					//: 'one-time-payment';
			//}

			if ( isset( $_POST['create_subscription'] ) ) {
				$response = $api->query(
					$args['serverUrl'] . '/' . $args['protocol'] . '/subscription',
					$data_subscription,
					'POST'
				);
			} else {
				$response = $api->query(
					$args['serverUrl'] . '/' . $args['protocol'] . '/transaction/' . $paymentTypeMethod . '/sale',
					$data,
					'POST'
				);							
			}
		
			$response = json_decode( $response, true );

			if ( ! empty( $response['id'] ) ) {
				
				// save payment if 'save-credit-card' exists				
				if ( ! empty( $_POST['save-credit-card'] ) && $_POST['paymentMethodCC'] === 'new-card' ) {
					sleep(3);
					$response_save_cc = $api->query(
						$args['serverUrl'] . '/' . $args['protocol'] . '/customers/' . $payorID . '/card',  
						$data['paymentDetails'], 
						'POST'
					);
				}
				// save payment if 'save-eft' exists				
				if ( ! empty( $_POST['save-eft'] ) && $_POST[ 'paymentMethodDD' ] === 'new-method' ) {
					sleep(3);
					$response_save_eft = $api->query(
						$args['serverUrl'] . '/' . $args['protocol'] . '/customers/' . $payorID . '/eft',  
						$data['paymentDetails'], 
						'POST'
					);
				}			
					
				$status = $order->get_status();
				if ( ! in_array( $status, ['pending', 'completed', 'cancelled', 'processing'] ) ) {
					$order->update_status( 'pending', __( 'Pending.', 'wc-bna-gateway' ) );
				}
				sleep(5);
				return array(
					'result' 	=> 'success',
					'redirect'  => $this->get_return_url( $order )
				);
			} else {
				//$order->update_status( 'on-hold', __( 'Pending.', 'wc-bna-gateway' ) );
			}
			
			throw new Exception(
				__( "Communication error with the payment system. Try again later.", 'wc-bna-gateway' )
			);
		}

		/**
		* Enabling refund mode
		* @since		1.0.0
		* @return email actions list
		*/
		public function process_refund( $order_id, $amount = null, $reason = '' ) {
			return true;
		}


		/**
		 * Page output if the order is successfully completed
		 * @since		1.0.0
		 */
		public function thankyou_page() {

			global $wp, $woocommerce;

			$order_id  = absint( $wp->query_vars['order-received'] );
			$order = wc_get_order( $order_id );

			$status = $order->get_status();

			if ( in_array( $status, ['pending', 'completed', 'processing'] ) ) {
				if ( $this->instructions ) {
					echo wpautop( wptexturize( $this->instructions));
				}
			} elseif ( $status !== 'cancelled' ) {
				$order->update_status( 'on-hold', __( 'Waiting for payment', 'wc-bna-gateway' ) );
			}
		}

		/**
		 * Function of receiving data (webhook) from the payment server 
		 * @since		1.0.0
		 * @param array $query
		 */
		public static function get_request( $query ) {

			$request = urldecode( $_SERVER['REQUEST_URI'] );

			if ( stristr( $request, '/bnasmartpayment/' ) ) {
				global $wpdb, $woocommerce, $BNAAccountManager, $BNASubscriptions;

				$endpoint = explode( '/', trim( $request, '/' ) );
				$endpoint = array_pop( $endpoint );

				$data = file_get_contents( "php://input" );
			
				if ( ! empty( $data) ) {

					$args = WC_BNA_Gateway::get_merchant_params();
					if ( empty($args) ) {
						BNAJsonMsgAnswer::send_json_answer(BNA_MSG_ERRORPARAMS);
						wp_die();
					}

					$result = json_decode( $data, true );
my_log($result);
					switch ( $endpoint ) {
						case 'transactions':
							self::endpoint_transactions( $result );
							break;
						case 'subscriptions':
							$BNASubscriptions::endpoint_subscriptions( $result );
							break;
						case 'account':
							$BNAAccountManager::endpoint_account( $result );
							break;
					}
				}

				exit();
			}

			return $query;
		} 

		/**
		 * Updating tax parameter values
		 * @since		1.0.0
		 */
		public static function get_fees_request()
		{
			$updatetime = get_option( 'wc_bna_gateway_fees_updatetime' );
			if ( ! empty( $updatetime ) ) {
				if ( date( 'Y-m-d', strtotime( $updatetime) ) === date( 'Y-m-d' ) ) {
					return null;
				}
			} 

			$args = WC_BNA_Gateway::get_merchant_params();
			if ( empty( $args ) ) {
				BNAJsonMsgAnswer::send_json_answer( BNA_MSG_ERRORPARAMS );
				wp_die();
			}

			$api = new BNAExchanger( $args );
			
			$response = $api->query(
				$args['serverUrl'] . '/' . $args['protocol'] . '/account', [], 'GET'
			);

			$response = json_decode( $response, true );

			if ( ! empty( $response['fees'] ) ) {
				update_option( 'wc_bna_gateway_fees', $response['fees'] );
				update_option( 'wc_bna_gateway_fees_updatetime', date( 'Y-m-d' ) );
			}
		}

		/**
		 * Endpoint for transaction processing
		 * @since		1.0.0
		 * @param json string $result
		 */
		public static function endpoint_transactions( $result )
		{
			global $wpdb, $woocommerce, $BNASubscriptions;
			
			$invoice_id = '';
			
			if ( isset( $result['metadata']['invoiceId'] ) ) { $invoice_id = $result['metadata']['invoiceId']; }
			
			if ( empty( $invoice_id ) && isset( $result['invoiceInfo']['invoiceId'] ) ) { $invoice_id = $result['invoiceInfo']['invoiceId']; }
	
			if ( empty( $invoice_id ) ) exit();

			$check_transaction_id =  $wpdb->get_results(
                "SELECT * FROM " . $wpdb->prefix . BNA_TABLE_TRANSACTIONS ." WHERE referenceNumber='{$result['referenceUUID']}'"
            );

            if ( ! empty( $check_transaction_id ) || count( $check_transaction_id ) >= 1)  exit();
		
			$order = wc_get_order( $invoice_id );
			$new_order = null;

			switch ( strtolower( $result['status'] ) ) {
				case 'approved':
					if ( isset( $result['subscriptionId'] ) && $order->get_status() === 'completed'  ) {					
						$new_order_id = $BNASubscriptions::create_subscription_order ( $order->get_id() );		
						$new_order = wc_get_order( $new_order_id );		
						$new_order->update_status( 'completed', __('Order completed.', 'wc-bna-gateway') );
						$new_order->payment_complete();
						wc_reduce_stock_levels ($new_order->get_id());
					} else if ( strtolower( $result['action'] ) === 'sale' ) {
						$order->update_status( 'completed', __('Order completed.', 'wc-bna-gateway') );
						$order->payment_complete();
						wc_reduce_stock_levels( $order->get_id() );
						$woocommerce->cart->empty_cart();
					} 
					
					break;
				case 'declined':
					if ( ! isset( $result['subscriptionId'] ) && $order->get_status() !== 'completed' ) {
						$order->update_status( 'on-hold', __('Waiting for payment.', 'wc-bna-gateway') );
					}
					break;				
				case 'refunded':
				case 'returned':
				case 'chargedback':
					$amount = floatval( $result['transactionInfo']['refundedAmount'] ) - 
						floatval( $result['transactionInfo']['paylinksFee'] );
					if ( $order->get_remaining_refund_amount() >= $amount ) {
						$refund = wc_create_refund(
							array(
								'amount' => $amount,
								'reason' => __("Order Cancelled", 'wc-bna-gateway'),
								'order_id' => $order->get_id(),
								'refund_payment' => true
							)
						);
						if ( is_wp_error($refund) ) {
							error_log($refund->get_error_message());
						} else {
							$order->update_status('refunded', __('Order Cancelled And Completely Refunded', 'wc-bna-gateway'));
						}
					} else {
						error_log(__('Refund requested exceeds remaining order balance of ' . $order->get_total(), 'wc-bna-gateway'));
					}     
					break;
				case 'batched':
				case 'pending':
				default:
					$order->update_status( 'pending', __( 'Pending.', 'wc-bna-gateway' ) );
			}

			$payorId = get_user_meta( $order->get_user_id(), 'payorID', true );
			$newPayorId = isset( $result['customerId'] ) ?
				$result['customerId'] : null;
			
			if ( ! empty( $newPayorId ) && empty( $payorId ) ) {
				add_user_meta( $order->get_user_id(), 'payorID', $newPayorId );
				$payorId = $newPayorId;
			}
			
			unset( $result['customerInfo'] );
			unset( $result['paymentMethods'] );
			
			$wpdb->insert( 
				$wpdb->prefix . BNA_TABLE_TRANSACTIONS,  
				array( 
					'order_id'				=> empty( $new_order ) ? $order->get_id() : $new_order->get_id(),
					'transactionToken'		=> $result['id'],
					'referenceNumber'		=> $result['referenceUUID'],
					'transactionStatus'		=> $result['status'],
					'transactionDescription'=> json_encode( $result )
				),
				array( 
					'%d','%s','%s','%s','%s'
				)
			);
		}	
  	}	//end of class
} //class_exists
