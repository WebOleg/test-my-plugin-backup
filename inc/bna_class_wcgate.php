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

			$this->plugin_name = plugin_basename( dirname( dirname( __FILE__ ) ) );
			$this->plugin_url = trailingslashit( plugin_dir_url( dirname( __FILE__ ) ) );

			add_action( 'woocommerce_thankyou_' . $this->id, array( $this, 'thankyou_page' ) );
			add_action( 'woocommerce_thankyou', array( $this, 'thankyou_page' ));

			// Customer Emails
			add_action( 'woocommerce_email_before_order_table', array( $this, 'email_instructions' ), 10, 3 );

			add_action( 'woocommerce_update_options_payment_gateways_' . $this->id, array( $this, 'process_admin_options' ) );
			add_action( 'wp_enqueue_scripts', array( $this, 'site_load_styles' ) );
			
			add_action( 'woocommerce_email_actions', array( $this, 'send_refund_email' ) );
			
			if ( is_admin() && isset( $_GET['section'] ) && $_GET['section'] === 'bna_gateway' ) {
				if ( ! in_array( get_woocommerce_currency(), BNA_CARD_ALLOWED_CURRENCY ) ) {
					add_action( 'admin_notices', array( $this, 'bna_admin_notice' ) );
				}
			}
		}

		/**
		* Loading the list of styles and scripts
		* @since		1.0.0
		*/
		public function site_load_styles()
		{
			$fees = get_option( 'wc_bna_gateway_fees' );
			wp_register_script( 'bna-cc-form-validator', $this->plugin_url . 'assets/lib/cc-form-validator/cc-form-validator.js', '', time(), true );
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
		 * Notice
		 */
		public function bna_admin_notice() {
			echo
			'<div class="notice notice-warning is-dismissible">
			<p><strong>' . __( 'Important!', 'wc-bna-gateway' ) . '</strong></p>
			<p>' . __( 'Your current currency is:', 'wc-bna-gateway' ) . ' <strong>' . get_woocommerce_currency() . '</strong>.</p>
			<p>' . __( 'Allowed currency for this plugin only:', 'wc-bna-gateway' ) . ' <strong>' . implode( ", ", BNA_CARD_ALLOWED_CURRENCY ) . '</strong>.</p>
			</div>'; 
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
		public static function add_payment_fee( $order, $percent, $flat ) 
		{
			global $woocommerce;

			$amount = $woocommerce->cart->cart_contents_total + $woocommerce->cart->tax_total + $order->get_total_shipping();
			$surcharge = $amount * $percent / 100 + $flat;
			$hst = $surcharge * 13 / 100; //HST Ontario
			$surcharge = round( round( $surcharge, 2 ) + round( $hst, 2 ), 2 );

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

			$woo_currency = get_woocommerce_currency();
			
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
				'iframe_id' => array(
					'title'       => 'iFrame ID',
					'type'        => 'text',
					'description' => 'Enter the iFrame ID from BNA Smart Payment',
					'default'     => '',
				),
				'access_key' => array(
					'title'       => 'Access Key',
					'type'        => 'text',
					'description' => 'API access key (login)',
					'default'     => '',
				),
				'secret_key' => array(
					'title'       => 'Secret Key',
					'type'        => 'password',
					'description' => 'API secret key (password)',
					'default'     => '',
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
				
				'bna-payment-methods' => array(
					'title' => __( 'Payment methods', 'wc-bna-gateway' ),
					'type'  => 'title',
					'description'  => __( 'Here you can change payment methods.', 'wc-bna-gateway' ),
					'id' => 'bna-payment-methods'
				),
				'bna-payment-method-card' => array(
					'title'   => __( 'Enable/Disable', 'wc-bna-gateway' ),
					'type'    => 'checkbox',
					'label'   => __( "Enable payment method 'Card'", 'wc-bna-gateway' ),
					'description' => __( 'This payment method is available when paying in US and Canadian dollars', 'wc-bna-gateway' ),
					'disabled'    => ( in_array( $woo_currency, BNA_CARD_ALLOWED_CURRENCY ) ) ? false : true,
					'default' => 'false'
				),
				'bna-payment-method-eft' => array(
					'title'   => __( 'Enable/Disable', 'wc-bna-gateway' ),
					'type'    => 'checkbox',
					'label'   => __( "Enable payment method 'Bank Transfer'", 'wc-bna-gateway' ),
					'description' => __( 'This payment method is available when paying in Canadian dollars', 'wc-bna-gateway' ),
					'disabled'    => ( in_array( $woo_currency, BNA_EFT_ALLOWED_CURRENCY ) ) ? false : true,
					'default' => 'false'
				),
				'bna-payment-method-e-transfer' => array(
					'title'   => __( 'Enable/Disable', 'wc-bna-gateway' ),
					'type'    => 'checkbox',
					'label'   => __( "Enable payment method 'E-transfer'", 'wc-bna-gateway' ),
					'description' => __( 'This payment method is available when paying in Canadian dollars', 'wc-bna-gateway' ),
					'disabled'    => ( in_array( $woo_currency, BNA_E_TRANSFER_ALLOWED_CURRENCY ) ) ? false : true,
					'default' => 'false'
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
				'bna-button-background-color' => array(
					'title'       => __( 'Button background (and link) color', 'wc-bna-gateway' ),
					'type'      => 'color',
					'default' => '#00A0E3'
				),
				'bna-button-text-color' => array(
					'title'       => __( 'Button text color', 'wc-bna-gateway' ),
					'type'      => 'color',
					'default' => '#FFF'
				),
				'bna-input-background-color' => array(
					'title'       => __( 'Input background color', 'wc-bna-gateway' ),
					'type'      => 'color',
					'default' => '#FFF'
				),
				'bna-line-color' => array(
					'title'       => __( 'Line color', 'wc-bna-gateway' ),
					'type'      => 'color',
					'default' => '#CCC'
				),				
				'bna-svg-first-color' => array(
					'title'       => __( 'SVG first color', 'wc-bna-gateway' ),
					'type'      => 'color',
					'default' => '#00A0E3'
				),
				'bna-svg-last-color' => array(
					'title'       => __( 'SVG last color', 'wc-bna-gateway' ),
					'type'      => 'color',
					'default' => '#B0CB1F'
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
		public function payment_fields()
		{
		    echo '<div id="bna-iframe-container"></div>';
		    ?>
		    <script>
		        jQuery(function($) {
		            function isBillingFormValid() {
		                const requiredFields = [
		                    '#billing_first_name',
		                    '#billing_last_name',
		                    '#billing_email',
		                    '#billing_phone',
		                    '#billing_city',
		                    '#billing_state',
		                    '#billing_postcode',
		                    '#billing_country'
		                ];

		                for (let i = 0; i < requiredFields.length; i++) {
		                    const field = $(requiredFields[i]);
		                    if (!field.length || !field.val().trim()) {
		                        return false;
		                    }
		                }
		                return true;
		            }

		            function loadBnaIframe() {
		                if (!isBillingFormValid()) {
		                    $('#bna-iframe-container').html('<div style="color:red;">❗ Please complete all required billing details.</div>');
		                    return;
		                }

		                let data = {
		                    action: 'load_bna_iframe',
		                    nonce: '<?php echo wp_create_nonce("bna_iframe_nonce"); ?>',
		                    customer: $('form.checkout').serialize()
		                };

		                $.post('<?php echo admin_url("admin-ajax.php"); ?>', data, function (response) {
		                    $('#bna-iframe-container').html(response);
		                });
		            }

		            $('form.checkout').on('change blur', 'input, select', function () {
		                if ($('input[name="payment_method"]:checked').val() === 'bna_gateway') {
		                    loadBnaIframe();
		                }
		            });

		            $('form.checkout').on('change', 'input[name="payment_method"]', function () {
		                if ($(this).val() === 'bna_gateway') {
		                    loadBnaIframe();
		                } else {
		                    $('#bna-iframe-container').html('');
		                }
		            });

		            // Initial check if selected
		            if ($('input[name="payment_method"]:checked').val() === 'bna_gateway') {
		                loadBnaIframe();
		            }
		        });
		    </script>
		    <?php
		}

		public function is_available() {
			return true;
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
			foreach( $order->get_items() as $id => $item ){
				$prod = array();
				$_woo_product = $item->get_product();

				$sku = $_woo_product->get_sku();
				$prod['sku']= ! empty( $sku ) ? $sku : strval( $item->get_product_id() ); // if 'sku' not exist add 'product_id'
				$prod['quantity'] = $item->get_quantity(); 						
				$prod['price'] = wc_get_price_including_tax( $_woo_product ); 		
				$prod['amount'] = $prod['price'] * $prod['quantity'];				
				$prod['description'] = $item->get_name(); 					

				$items[] = $prod; 
			}
		
			$api = new BNAExchanger($args);

			if ( empty( $_POST['payment-type'] ) ) {
				throw new Exception( __( 'Can\'t find BNA payment type', 'wc-bna-gateway' ) );
			}
			
			// customer info
			$customerInfo = array(
				'email'				=> $_POST['billing_email'],
				'firstName'		=> $_POST['billing_first_name'],
				'lastName'		=> $_POST['billing_last_name'],
				'phoneCode'		=> $_POST['billing_phone_code'],
				'phoneNumber'	=> $_POST['billing_phone'],
				'address' => array(
					'streetNumber'	=> $_POST['billing_street_number'],
					'streetName'		=> $_POST['billing_street_name'],
					'city'					=> $_POST['billing_city'],
					'province'			=> WC()->countries->get_states( $_POST['billing_country'] )[$_POST[ 'billing_state' ]],  //$_POST[ 'billing_country' ] .'-'. $_POST[ 'billing_state' ],
					'country'			=> WC()->countries->countries[$_POST['billing_country']], //$_POST[ 'billing_country' ], 
					'postalCode'		=> $_POST['billing_postcode'],
				),
			);
			if ( ! empty( $_POST['billing_company'] ) ) { $customerInfo['companyName'] = $_POST['billing_company']; }
			if ( ! empty( $_POST['billing_birthday'] ) ) { $customerInfo['birthDate'] = $_POST['billing_birthday']; }
			if ( ! empty( $_POST['billing_apartment'] ) ) { $customerInfo['address']['apartment'] = $_POST['billing_apartment']; }
			
			// data
			$data = array( 
				'transactionTime' => date('Y-m-d\TH:i:sO'),
				'items'				=> $items,
				'applyFee'		=> $args['applyFee'] == 'yes' ? true : false,
				'subtotal'			=> $order->get_total(),
					//($woocommerce->cart->cart_contents_total + $woocommerce->cart->tax_total + $order->get_total_shipping()),
				'currency'			=> get_woocommerce_currency(),
				'metadata'		=> array(
					'invoiceId' => $order_id,				
				),					
			);

			$payorID = get_user_meta( get_current_user_id(), 'payorID', true );			
			// if payor not exist - create payor
			if ( empty( $payorID ) && is_user_logged_in() ) {
				$customers_response = $api->query(
					$args['serverUrl'] . '/' . $args['protocol'] . '/customers',  
					$customerInfo,
					'POST'
				);
				$customers_response = json_decode( $customers_response, true );
				if ( ! empty( $customers_response['id'] ) ) {
					add_user_meta( get_current_user_id(), 'payorID', $customers_response['id'] );
				}
			}
			
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
					if ( isset( $_POST['paymentMethodCC'] ) ) {
						if ( $_POST['paymentMethodCC'] === 'new-card' ) {
							
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
						} else {
							$data['paymentDetails'] = array( "id" => $_POST['paymentMethodCC'] );
						}
					} else {
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
					if ( isset( $_POST['paymentMethodDD'] ) ) {
						if ( $_POST[ 'paymentMethodDD' ] === 'new-method' ) {
							$params = array (
								"bankNumber"		=> $_POST['bank_name'] !== 'other' ? $_POST['bank_name'] : $_POST['bank_number'],
								"accountNumber"	=> $_POST['account_number'],
								"transitNumber"	=> $_POST['transit_number']
							);
							foreach ( $params as $p_key => $p_val )
									$data['paymentDetails'][$p_key] = $p_val;
						} else {
							$data['paymentDetails'] = array( "id" => $_POST['paymentMethodDD'] );
						}
					} else {
							$params = array (
								"bankNumber"		=> $_POST['bank_name'] !== 'other' ? $_POST['bank_name'] : $_POST['bank_number'],
								"accountNumber"	=> $_POST['account_number'],
								"transitNumber"	=> $_POST['transit_number']
							);
							foreach ( $params as $p_key => $p_val )
									$data['paymentDetails'][$p_key] = $p_val;
					}
					self::add_payment_fee( $order, $fees->directDebitPercentageFee, $fees->directDebitFlatFee );
					break;
				case 'e-transfer':
					$paymentTypeMethod = 'e-transfer';
					//$params = array (
						//"interacEmail" => $_POST['email_transfer'],
					//);
					//foreach ( $params as $p_key => $p_val )
						//$data['paymentDetails'][$p_key] = $p_val;

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
				$data_subscription['subtotal'] = $order->get_total();
					//( $woocommerce->cart->cart_contents_total + $woocommerce->cart->tax_total + $order->get_total_shipping() );
				$data_subscription['currency'] = get_woocommerce_currency();
				$data_subscription['metadata']	= array(
					'invoiceId' => $order_id,				
				);
			}

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
				
				// Set subscription order details to post meta
				if ( isset( $_POST['create_subscription'] ) ) {
					$bna_subscription_order_info = array();
					$bna_subscription_order_info['recurrence'] = $response['recurrence'];
					$bna_subscription_order_info['startPaymentDate'] = $response['startPaymentDate'];
					$bna_subscription_order_info['nextPaymentDate'] = $response['nextPaymentDate'];
					$bna_subscription_order_info['lastPaymentDate'] = $response['lastPaymentDate'];
					$bna_subscription_order_info['remainingPayments'] = $response['remainingPayments'];
					$order->add_meta_data( 'bna_subscription_order_info', $bna_subscription_order_info );
				}
				
				// save payment if 'save-credit-card' exists				
				if ( ! empty( $_POST['save-credit-card'] ) && ( ! isset( $_POST['paymentMethodCC'] ) || $_POST['paymentMethodCC'] === 'new-card' ) ) {
					$response_save_cc = $api->query(
						$args['serverUrl'] . '/' . $args['protocol'] . '/customers/' . $payorID . '/card',  
						$data['paymentDetails'], 
						'POST'
					);				
				}
				// save payment if 'save-eft' exists				
				if ( ! empty( $_POST['save-eft'] ) && ( ! isset( $_POST['paymentMethodDD'] ) || $_POST[ 'paymentMethodDD' ] === 'new-method' ) ) {
					$response_save_eft = $api->query(
						$args['serverUrl'] . '/' . $args['protocol'] . '/customers/' . $payorID . '/eft',  
						$data['paymentDetails'], 
						'POST'
					);		
				}			
					
				// Set title for the payment method
				if (  ! empty( $response['paymentMethod'] ) ) {
					switch ( $response['paymentMethod'] ) {
						case 'CARD':
							$paymentDetails = __( 'Card #:', 'wc-bna-gateway' ) . esc_html( $response['paymentDetails']['cardNumber'] );
							$order->update_status( 'completed', __( 'Completed.', 'wc-bna-gateway' ) );
							break;
						case 'EFT':
							$paymentDetails = __( 'Account #:', 'wc-bna-gateway' ) . esc_html( $response['paymentDetails']['accountNumber'] ) . '<br>';
							$paymentDetails .= __( 'Transit #:', 'wc-bna-gateway' ) . esc_html( $response['paymentDetails']['transitNumber'] ) . '<br>';
							$paymentDetails .= __( 'Institution #:', 'wc-bna-gateway' ) . esc_html( $response['paymentDetails']['bankNumber'] );
							$order->update_status( 'pending', __( 'Pending.', 'wc-bna-gateway' ) );
							break;
						case 'E-TRANSFER':
							$paymentDetails = __( 'Email: ', 'wc-bna-gateway' ) . wp_get_current_user()->user_email;
							$order->update_status( 'pending', __( 'Pending.', 'wc-bna-gateway' ) );
							break;
					} 					
					$order->set_payment_method_title( $paymentDetails );
				}	
				$order->save();
				
				
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
					return false;
				}
			} 

			$args = WC_BNA_Gateway::get_merchant_params();
			if ( empty( $args ) ) {
				BNAJsonMsgAnswer::send_json_answer( BNA_MSG_ERRORPARAMS );
				return false;
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
			
			$check_transaction_id =  $wpdb->get_results(
                "SELECT * FROM " . $wpdb->prefix . BNA_TABLE_TRANSACTIONS . " WHERE transactionToken='{$result['id']}'"
            );
            
            if ( ! empty( $check_transaction_id[0]->order_id ) ) {
				$invoice_id = $check_transaction_id[0]->order_id;
			} elseif ( isset( $result['metadata']['invoiceId'] ) ) {
				$invoice_id = $result['metadata']['invoiceId'];
			} else {
				exit();
			}
               
            $order = wc_get_order( $invoice_id );
			$new_order = null;
			
			switch ( $result['status'] ) {
				case 'APPROVED':
					if ( isset( $result['subscriptionId'] ) && $order->get_status() === 'completed'  ) {					
						$new_order_id = $BNASubscriptions::create_subscription_order ( $order->get_id() );		
						$new_order = wc_get_order( $new_order_id );		
						$new_order->update_status( 'completed', __( 'Order completed.', 'wc-bna-gateway' ) );
						$new_order->payment_complete();
						wc_reduce_stock_levels( $new_order->get_id() );
					} else if ( $result['action'] === 'REFUND' ) {
						$amount = floatval( $result['amount'] ) - floatval( $result['fee'] );
						if ( $order->get_remaining_refund_amount() >= $amount ) {
							$refund = wc_create_refund(
								array(
									'amount' => $amount,
									'reason' => esc_html( $result['transactionComment'] ),
									'order_id' => $order->get_id(),
									'refund_payment' => true
								)
							);
							if ( is_wp_error( $refund ) ) {
								error_log( $refund->get_error_message() );
							} else {
								//$order->set_status( 'wc-refunded', __( 'Order Cancelled And Completely Refunded', 'wc-bna-gateway' ) );
							}
						} else {
							error_log( __( 'Refund requested exceeds remaining order balance of ' . $order->get_total(), 'wc-bna-gateway' ) );
						}											
					} else if ( $result['action'] === 'SALE' ) {
						$order->update_status( 'completed', __( 'Order completed.', 'wc-bna-gateway' ) );
						$order->payment_complete();
						wc_reduce_stock_levels( $order->get_id() );
						$woocommerce->cart->empty_cart();
					} else if ( $result['action'] === 'VOID' ) {
						$order->update_status( 'cancelled', __( 'Order void.', 'wc-bna-gateway' ) );
					}					
					break;				
				case 'CANCELED':
					$order->update_status( 'cancelled', __( 'Order void.', 'wc-bna-gateway' ) );
					break;
				case 'ERROR':
				case 'EXPIRED':
				case 'DECLINED':
					if ( $result['action'] === 'VOID' ) {
						$order->update_status( 'cancelled', __( 'Order void.', 'wc-bna-gateway' ) );
					} else if ( ! isset( $result['subscriptionId'] ) && $order->get_status() !== 'completed' ) {
						$order->update_status( 'on-hold', __( 'Waiting for payment.', 'wc-bna-gateway' ) );
					} else {
						$order->update_status( 'cancelled', __( 'Order void.', 'wc-bna-gateway' ) );
					}
					break;				
				case 'REFUNDED':
				case 'RETURNED':
				case 'CHARGEBACK':
					$amount = floatval( $result['amount'] ) - floatval( $result['fee'] );
					if ( $order->get_remaining_refund_amount() >= $amount ) {
						$refund = wc_create_refund(
							array(
								'amount' => $amount,
								'reason' => esc_html( $result['transactionComment'] ),
								'order_id' => $order->get_id(),
								'refund_payment' => true
							)
						);
						if ( is_wp_error( $refund ) ) {
							error_log( $refund->get_error_message() );
						} else {
							//$order->update_status( 'refunded', __( 'Order Cancelled And Completely Refunded', 'wc-bna-gateway' ) );
						}
					} else {
						error_log( __( 'Refund requested exceeds remaining order balance of ' . $order->get_total(), 'wc-bna-gateway' ) );
					}     
					break;
				case 'BATCHED':
				case 'PENDING':
				case 'OVERPAID':
				case 'UNDERPAID':
				default:
					$order->update_status( 'pending', __( 'Pending.', 'wc-bna-gateway' ) );
			}
			
			// not recording
			if ( $result['action'] === 'VOID' && $result['status'] === 'DECLINED' ) {
				exit();
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
			
			if ( empty( $check_transaction_id ) || count( $check_transaction_id ) < 1 ) {	
				$wpdb->insert( 
					$wpdb->prefix . BNA_TABLE_TRANSACTIONS,  
					array( 
						'order_id'				=>  empty( $new_order ) ? esc_html( $order->get_id() ) : esc_html( $new_order->get_id() ),
						'transactionToken'		=> esc_html( $result['id'] ),
						'referenceNumber'		=> esc_html( $result['referenceUUID'] ),
						'transactionStatus'		=> esc_html( $result['status'] ),
						'transactionDescription'=> json_encode( $result )
					),
					array( 
						'%d','%s','%s','%s','%s'
					)
				);
			} else {
				$transactionDescription = json_encode( $result );
				$result_status = esc_html( $result['status'] );
				$result_id = esc_html( $result['id'] );
				
				$wpdb->query("UPDATE " . $wpdb->prefix . BNA_TABLE_TRANSACTIONS
					." SET "
						."transactionStatus='{$result_status}', "
						."transactionDescription='{$transactionDescription}' "
					." WHERE transactionToken='{$result_id}'"
				);
			}
					
		}
			
  	}	//end of class
	
} //class_exists

add_action('wp_ajax_load_bna_iframe', 'load_bna_iframe_callback');
add_action('wp_ajax_nopriv_load_bna_iframe', 'load_bna_iframe_callback');

function load_bna_iframe_callback() {
    check_ajax_referer('bna_iframe_nonce', 'nonce');

    $settings = get_option('woocommerce_bna_gateway_settings');
    $access_key  = $settings['access_key'] ?? '';
    $secret_key  = $settings['secret_key'] ?? '';
    $iframe_id   = $settings['iframe_id'] ?? '';
    $environment = $settings['environment'] ?? 'https://dev-api-service.bnasmartpayment.com';

    $base_url = match ($environment) {
        'https://production-api-service.bnasmartpayment.com' => 'https://api.bnasmartpayment.com',
        'https://dev-api-service.bnasmartpayment.com' => 'https://stage-api-service.bnasmartpayment.com',
        default => 'https://stage-api-service.bnasmartpayment.com',
    };

    $cart = WC()->cart;
    $items = [];
    $subtotal = 0;

    if ($cart && !empty($cart->get_cart())) {
        foreach ($cart->get_cart() as $cart_item) {
            $product = $cart_item['data'];
            $price   = wc_get_price_including_tax($product);
            $quantity = $cart_item['quantity'];
            $amount = $price * $quantity;

            $items[] = [
                'description' => $product->get_name(),
                'sku'         => $product->get_sku() ?: (string) $product->get_id(),
                'price'       => round($price, 2),
                'quantity'    => $quantity,
                'amount'      => round($amount, 2),
            ];

            $subtotal += $amount;
        }
    }

    $payload = [
        'iframeId' => $iframe_id,
        'customerInfo' => [
            'type' => 'Personal',
            'email' => 'business@best-store.com',
            'firstName' => 'Angelica',
            'lastName' => 'Sloan',
            'phoneCode' => '+1',
            'phoneNumber' => '0989602398',
            'birthDate' => '1994-12-15',
            'address' => [
                'streetName'   => 'Ackroyd Road',
                'streetNumber' => '7788',
                'city'         => 'Richmond',
                'province'     => 'British Columbia',
                'country'      => 'Canada',
                'postalCode'   => 'V6X 2C9',
            ],
        ],
        'items' => $items,
        'subtotal' => round($subtotal, 2),
    ];

    $response = wp_remote_post("{$base_url}/v1/checkout", [
        'headers' => [
            'Authorization' => 'Basic ' . base64_encode("{$access_key}:{$secret_key}"),
            'Content-Type'  => 'application/json',
        ],
        'body' => json_encode($payload),
    ]);

    if (!is_wp_error($response)) {
        $body = json_decode(wp_remote_retrieve_body($response), true);
        if (!empty($body['token'])) {
            echo '<iframe src="' . esc_url("{$base_url}/v1/checkout/{$body['token']}") . '" width="100%" height="600" style="border:none;margin-top:20px;"></iframe>';
        } else {
            echo '<div style="color:red;">❗ iFrame token not returned by API.</div>';
        }
    } else {
        echo '<div style="color:red;">❗ Failed to connect to API.</div>';
    }

    wp_die();
}
