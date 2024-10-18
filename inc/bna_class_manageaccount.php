<?php
/**
 * Woocommerce BNA Gateway
 *
 * @author		BNA
 * @category 	'BNA Manage Accaount' Class
 * @version		1.0
 */

if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

require_once dirname( __FILE__ ). "/bna_class_jsonmessage.php";

define ( 'BNA_CONST_NONCE_NAME', 'bna_nonce' );

if ( ! class_exists( 'BNAAccountManager' ) ) {

	class BNAAccountManager {

		/**
		 * Custom endpoint name.
		 *
		 * @var string
		 */
		//public static $endpoint_account_management = 'bna-account-management';
		public static $endpoint_payment_methods = 'bna-payment-methods';
		public static $endpoint_transaction_info = 'bna-transaction-info';
		public static $endpoint_recurring_payments = 'bna-recurring-payments';
		public static $endpoint_add_credit_card = 'bna-add-credit-card';
		public static $endpoint_bank_account_info = 'bna-bank-account-info';
		public static $endpoint_e_transfer_info = 'bna-e-transfer-info';

		/**
		 * Plugin actions.
		 */
		public function __construct() {
			
			$this->plugin_name = plugin_basename( dirname( dirname( __FILE__ ) ) );
			$this->plugin_url = trailingslashit( plugin_dir_url( dirname( __FILE__ ) ) );

			register_activation_hook( $this->plugin_name, array( 'BNAAccountManager', 'activate' ) );

			// Actions used to insert a new endpoint in the WordPress.
			add_filter( 'woocommerce_get_query_vars', array( $this, 'get_query_vars' ), 0 );

			// Change the My Accout page title.
			add_filter( 'the_title', array( $this, 'endpoint_title' ) );

			// Insering your new tab/page into the My Account page.
			add_filter( 'woocommerce_account_menu_items', array( $this, 'new_menu_items' ) );
			//add_action( 'woocommerce_account_' . self::$endpoint_account_management .  '_endpoint', array( $this, 'endpoint_content_account_management' ) );
			add_action( 'woocommerce_account_' . self::$endpoint_payment_methods .  '_endpoint', array( $this, 'endpoint_content_payment_methods' ) );
			add_action( 'woocommerce_account_' . self::$endpoint_transaction_info .  '_endpoint', array( $this, 'endpoint_content_transaction_info' ) );
			add_action( 'woocommerce_account_' . self::$endpoint_recurring_payments .  '_endpoint', array( $this, 'endpoint_content_recurring_payments' ) );
			add_action( 'woocommerce_account_' . self::$endpoint_add_credit_card .  '_endpoint', array( $this, 'endpoint_content_add_credit_card' ) );
			add_action( 'woocommerce_account_' . self::$endpoint_bank_account_info .  '_endpoint', array( $this, 'endpoint_content_bank_account_info' ) );
			add_action( 'woocommerce_account_' . self::$endpoint_e_transfer_info .  '_endpoint', array( $this, 'endpoint_content_e_transfer_info' ) );

			add_action( 'wp_enqueue_scripts', array( &$this, 'site_load_styles') );
			
			add_action( 'wp_enqueue_scripts', function() {
				if ( is_cart() || is_checkout() || is_page( array( 'my-account' ) ) ) {			
					if ( ! wp_script_is( 'selectWoo' ) ) {
						wp_enqueue_script( 'bna-selectWoo-js', $this->plugin_url.'assets/lib/select/js/selectWoo.full.min.js', array('jquery'), WC_VERSION, true );
					}
					
					if ( ! wp_style_is( 'select2' ) ) {
						wp_enqueue_style( 'bna-select2-css', $this->plugin_url . 'assets/lib/select/css/select2.min.css', '', '4.0.13' );
					}
									
					if ( ! wp_style_is( 'woocommerce-layout' ) ) {				
						wp_enqueue_style( 'bna-woocommerce-layout', WP_PLUGIN_URL . '/woocommerce/assets/css/woocommerce-layout.css', '', WC_VERSION );
					}
					
					if ( ! wp_style_is( 'woocommerce-smallscreen' ) ) {				
						wp_enqueue_style( 'bna-woocommerce-smallscreen', WP_PLUGIN_URL . '/woocommerce/assets/css/woocommerce-smallscreen.css', '', WC_VERSION, "only screen and (max-width: 768px)" );
					}
					
					wp_enqueue_style( 'bna-style', $this->plugin_url . 'assets/css/bna-style.css', '', time() );
				}
				
				if ( is_wc_endpoint_url( 'edit-address' ) ) {
					wp_enqueue_style( 'bna-datepicker-css', $this->plugin_url . 'assets/lib/datepicker/css/datepicker.min.css' );
					wp_enqueue_script( 'bna-datepicker-js', $this->plugin_url . 'assets/lib/datepicker/js/datepicker.min.js', array('jquery'), '1.0.0', true );
					wp_enqueue_script( 'bna-script-js', $this->plugin_url.'assets/js/bna-script.js', array('jquery'), time(), true );
				}
				
			}, 99 );

			add_action( 'wp_ajax_create_payor', array( $this, 'ajax_create_payor' ) );
			add_action( 'wp_ajax_update_payor', array( $this, 'ajax_update_payor' ) );
			//add_action( 'wp_ajax_delete_payor', array(&$this, 'ajax_delete_payor' ) );
			add_action( 'wp_ajax_delete_payment', array( $this, 'ajax_delete_payment' ) );
			add_action( 'wp_ajax_add_payment', array( $this, 'ajax_add_payment' ) );
			add_action( 'wp_ajax_copy_billing_address_to_shipping', array( &$this, 'ajax_copy_billing_address_to_shipping' ) );

			add_action( 'profile_update', array( $this, 'check_user_profile_updated' ), 10, 2 );
		}

		/**
		* Loading the list of styles and scripts
		* @since		1.0.0
		*/
		public function site_load_styles()
		{			
			wp_register_style( 'bna-datepicker-css', $this->plugin_url . 'assets/lib/datepicker/css/datepicker.min.css' );
			wp_register_script( 'bna-datepicker-js', $this->plugin_url . 'assets/lib/datepicker/js/datepicker.min.js', array('jquery'), '1.0.0', true );
			wp_enqueue_script( 'bna-bank-names-js', $this->plugin_url . 'assets/js/bankNames.js', array(), '1.0.0', true );
			wp_register_script( 'wc-country-select', site_url() . '/wp-content/plugins/woocommerce/assets/js/frontend/country-select.min.js', array('jquery'), null, true );
			wp_register_script( 'bna-cc-form-validator', $this->plugin_url . 'assets/lib/cc-form-validator/cc-form-validator.js', '', time(), true );
			wp_register_script( 'bna-script', $this->plugin_url.'assets/js/bna-script.js', array('jquery'), time(), true );			

			wp_localize_script( 'bna-datepicker-js', 'bnaData',
				array(
					'url' 	=> admin_url('admin-ajax.php'),
					'nonce' => wp_create_nonce( BNA_CONST_NONCE_NAME ),
					'paymentMethodsEndpointUrl' => esc_url( wc_get_account_endpoint_url( 'bna-payment-methods' ) ),
					'btnSuspendSubscriptionQuestion' => __( 'Do you want to suspend the subscription', 'wc-bna-gateway' ),
					'btnDelSubscriptionQuestion' => __( 'Do you want to delete the subscription', 'wc-bna-gateway' )
				)	
			);
		}
			
		/**
		 * Executable code when activate a plugin
		 * @since 1.0.0
		 */
		public static function activate() 
		{
			global $wp_rewrite;
			
			add_rewrite_endpoint( self::$endpoint_payment_methods, EP_ROOT | EP_PAGES );
			add_rewrite_endpoint( self::$endpoint_transaction_info, EP_ROOT | EP_PAGES );
			add_rewrite_endpoint( self::$endpoint_recurring_payments, EP_ROOT | EP_PAGES );
			add_rewrite_endpoint( self::$endpoint_add_credit_card, EP_ROOT | EP_PAGES );
			add_rewrite_endpoint( self::$endpoint_bank_account_info, EP_ROOT | EP_PAGES );
			add_rewrite_endpoint( self::$endpoint_e_transfer_info, EP_ROOT | EP_PAGES );
			$wp_rewrite->flush_rules();
		}

		/**
		 * Add new query var.
		 *
		 * @param array $vars
		 * @return array
		 */
		public function get_query_vars( $vars ) {
			$vars[ self::$endpoint_payment_methods ] = self::$endpoint_payment_methods;
			$vars[ self::$endpoint_transaction_info ] = self::$endpoint_transaction_info;
			$vars[ self::$endpoint_recurring_payments ] = self::$endpoint_recurring_payments;
			$vars[ self::$endpoint_add_credit_card ] = self::$endpoint_add_credit_card;
			$vars[ self::$endpoint_bank_account_info ] = self::$endpoint_bank_account_info;
			$vars[ self::$endpoint_e_transfer_info ] = self::$endpoint_e_transfer_info;

			return $vars;
		}

		/**
		 * Set endpoint title.
		 *
		 * @param string $title
		 * @return string
		 */
		public function endpoint_title( $title ) {
			global $wp_query;
			
			if ( ! (is_main_query() && in_the_loop() && is_account_page()) || is_admin() ) return $title;
			
			if ( isset( $wp_query->query_vars[ self::$endpoint_payment_methods ] ) ) {
				$title = __( 'Payment methods', 'wc-bna-gateway' );
				remove_filter( 'the_title', array( $this, 'endpoint_title' ) );
			}

			if ( isset( $wp_query->query_vars[ self::$endpoint_transaction_info ] ) ) {
				$title = __( 'Transaction info', 'wc-bna-gateway' );
				remove_filter( 'the_title', array( $this, 'endpoint_title' ) );
			}

			if ( isset( $wp_query->query_vars[ self::$endpoint_recurring_payments ] ) ) {
				$title = __( 'Recurring payments', 'wc-bna-gateway' );
				remove_filter( 'the_title', array( $this, 'endpoint_title' ) );
			}
			
			if ( isset( $wp_query->query_vars[ self::$endpoint_add_credit_card ] ) ) {
				$title = __( 'Add credit card', 'wc-bna-gateway' );
				remove_filter( 'the_title', array( $this, 'endpoint_title' ) );
			}
			
			if ( isset( $wp_query->query_vars[ self::$endpoint_bank_account_info ] ) ) {
				$title = __( 'Add bank account info', 'wc-bna-gateway' );
				remove_filter( 'the_title', array( $this, 'endpoint_title' ) );
			}
			
			if ( isset( $wp_query->query_vars[ self::$endpoint_e_transfer_info ] ) ) {
				$title = __( 'Add e-transfer info', 'wc-bna-gateway' );
				remove_filter( 'the_title', array( $this, 'endpoint_title' ) );
			}

			return $title;
		}

		/**
		 * Insert the new endpoints into the My Account menu.
		 *
		 * @param array $items
		 * @return array
		 */
		public function new_menu_items( $items ) {
			// Remove the logout menu item.
			$logout = $items['customer-logout'];
			unset( $items['customer-logout'] );

			// Insert your custom endpoint.
			$items[ self::$endpoint_payment_methods ] = __( 'Payment methods', 'wc-bna-gateway' );
			$items[ self::$endpoint_transaction_info ] = __( 'Transaction info', 'wc-bna-gateway' );
			$items[ self::$endpoint_recurring_payments ] = __( 'Recurring payments', 'wc-bna-gateway' );
			// Insert back the logout item.
			$items['customer-logout'] = $logout;

			return $items;
		}

		/**
		 * Loading scripts on my-account page
		 */
		public static function loading_scripts() 
		{
			wp_enqueue_style( 'bna-datepicker-css' );
			wp_enqueue_script( 'bna-datepicker-js');
			wp_enqueue_script( 'wc-country-select' );
			wp_enqueue_script( 'bna-cc-form-validator' );
			wp_enqueue_script( 'bna-script');			
		}
		
		/**
		 * Checking the updated user profile
		 *
		 * @param int $user_id
		 * @param object $old_user_data
		 * @return json
		 */
		public function check_user_profile_updated( $user_id, $old_user_data ) 
		{
			global $wpdb;

			$user = get_userdata( $user_id );

			$args = WC_BNA_Gateway::get_merchant_params();
			if ( empty( $args) ) {
				BNAJsonMsgAnswer::send_json_answer( BNA_MSG_ERRORPARAMS );
				wp_die();
			}

			$api = new BNAExchanger( $args );
			
			$payorID = get_user_meta ( $user_id, 'payorID', true );
			if ( empty( $payorID ) ) {
				BNAJsonMsgAnswer::send_json_answer( BNA_MSG_ERRORPAYOR );
				wp_die();
			}

			$province = get_user_meta( $user_id, 'billing_state', true );
			$country  = get_user_meta( $user_id, 'billing_country', true );
			
			$data = array(
				"companyName" 	=> get_user_meta( $user_id, 'billing_company', true ),
				"firstName"		=> get_user_meta( $user_id, 'billing_first_name', true ),
				"lastName"		=> get_user_meta( $user_id, 'billing_last_name', true ),
				// here email should not updated
				//"email"	=> wp_get_current_user()->user_email,
				"phoneCode" =>  get_user_meta( $user_id, 'billing_phone_code', true ),
				"phoneNumber"	 => get_user_meta( $user_id, 'billing_phone', true ),
				"birthDate"		=> get_user_meta( $user_id, 'billing_birthday', true ),
				"address"		=> array(
					"streetNumber"	=> get_user_meta( $user_id, 'billing_street_number', true ),
					"apartment"		=> get_user_meta( $user_id, 'billing_apartment', true ),
					"streetName"	=> get_user_meta( $user_id, 'billing_street_name', true ),
					"postalCode"		=> get_user_meta( $user_id, 'billing_postcode', true ),
					"city"			=> get_user_meta( $user_id, 'billing_city', true ),
					"province"		=> WC()->countries->get_states( $country )[ $province ],
					"country"		=> WC()->countries->countries[ $country ]
				)
			);
			
			// search empty fields and delate
			$filtered_data = array();			
			foreach ( $data as $key => $value ) {
				if ( ! empty( $value ) ) {
					if ( $key == 'address' ) {
						$address = array();
						foreach ( $value as $k => $v ) {
							if ( ! empty( $v ) ) {
								$address[$k] = $v;
							}
						}
						$filtered_data['address'] = $address;
					} else {
						$filtered_data[$key] = $value;
					}
				}
			}

			$response = $api->query(
				$args['serverUrl'] . '/' . $args['protocol'] . '/customers/' . $payorID, 
				$filtered_data,
				'PATCH'
			);

			$response = json_decode( $response, true );
		
			empty( $response['id'] ) ? 
				BNAJsonMsgAnswer::send_json_answer( BNA_MSG_UPDATE_ACCOUNT_ERROR ) : 
				BNAJsonMsgAnswer::send_json_answer( BNA_MSG_UPDATE_ACCOUNT_SUCCESS );
		}

		/**
		 * Managing endpoint content payment methods
		 *
		 * @return view
		 */
		public function endpoint_content_payment_methods() {
			global $wpdb;

			self::loading_scripts();

			$payorID = get_user_meta (get_current_user_id(), 'payorID', true);
			$paymentMethods =  $wpdb->get_results("SELECT * FROM ".$wpdb->prefix.BNA_TABLE_SETTINGS);
			require_once dirname( __FILE__ ). "/../tpl/tpl_payment_methods.php";
		}

		/**
		 * Managing endpoint content transaction info
		 *
		 * @return view
		 */
		public function endpoint_content_transaction_info() {
			global $wpdb;

			self::loading_scripts();

			$orderIDs = [];
			
			$args = array( 'customer_id' => get_current_user_id(), 'nopaging' => true );
			$args = bna_my_account_orders( $args );
			
			$orders = wc_get_orders( $args );
			foreach( $orders as $order) array_push( $orderIDs, $order->get_id());

 			$transactions =  $wpdb->get_results(
				"SELECT * FROM " . $wpdb->prefix . BNA_TABLE_TRANSACTIONS
					." WHERE order_id IN (". implode( ',', $orderIDs ).") ORDER BY created_time DESC"
			);
			require_once dirname( __FILE__ ) . "/../tpl/tpl_transaction_info.php";
		}

		/**
		 * Managing endpoint content recurring payments
		 *
		 * @return view
		 */
		public function endpoint_content_recurring_payments() {
			global $wpdb;

			self::loading_scripts();

			$userID = get_current_user_id();
			
			 if ( isset( $_GET['bna-orders-filter'] ) && ! empty( $_GET['bna-orders-filter'] ) ) {
				$start_date = esc_attr( $_GET['bna-orders-filter'] );
        
				if ( $start_date === 'last-week' ) {
					$start_date = date( "Y-m-d", strtotime( "- 7 days" ) );
				} elseif ( $start_date === 'last-month' ) {
					$start_date = date( "Y-m-d", strtotime( "- 30 days" ) );
				} elseif ( $start_date === 'last-three-months' ) {
					$start_date = date( "Y-m-d", strtotime( "- 91 days" ) );
				} elseif ( $start_date === 'last-year' ) {
					$start_date = date( "Y-m-d", strtotime( "- 365 days" ) );
				}
				 
				$subscriptions =  $wpdb->get_results(
					"SELECT * FROM " . $wpdb->prefix . BNA_TABLE_RECURRING." WHERE user_id='{$userID}' AND DATE(created_time) > '{$start_date}' ORDER BY created_time DESC"
				);
			} else {
				$subscriptions =  $wpdb->get_results(
					"SELECT * FROM " . $wpdb->prefix . BNA_TABLE_RECURRING." WHERE user_id='{$userID}' ORDER BY created_time DESC"
				);
			}
			require_once dirname( __FILE__ ) . "/../tpl/tpl_subscription_info.php";
		}
		
		/**
		 * Managing endpoint content add credit card
		 *
		 * @return view
		 */
		public function endpoint_content_add_credit_card() {
			global $wpdb;

			self::loading_scripts();

			$payorID = get_user_meta( get_current_user_id(), 'payorID', true );
			require_once dirname( __FILE__ ). "/../tpl/tpl_add_credit_card.php";
		}
		
		/**
		 * Managing endpoint content bank account info
		 *
		 * @return view
		 */
		public function endpoint_content_bank_account_info() {
			global $wpdb;

			self::loading_scripts();

			$payorID = get_user_meta( get_current_user_id(), 'payorID', true );
			require_once dirname( __FILE__ ). "/../tpl/tpl_bank_account_info.php";
		}
		
		/**
		 * Managing endpoint content e-transfer info
		 *
		 * @return view
		 */
		public function endpoint_content_e_transfer_info() {
			global $wpdb;

			self::loading_scripts();

			$payorID = get_user_meta( get_current_user_id(), 'payorID', true );
			require_once dirname( __FILE__ ). "/../tpl/tpl_e_transfer_info.php";
		}

		/**
		 * Create payor on front
		 *
		 * @return json
		 */
		public function ajax_create_payor()
		{ 
			global $wpdb;
			
			if ( isset( $_POST['nonce'] ) ) {
				if ( ! wp_verify_nonce( $_POST['nonce'], BNA_CONST_NONCE_NAME ) ) {
					BNAJsonMsgAnswer::send_json_answer( BNA_MSG_ERRORNONCE );
					wp_die();
				}
								
				$form = array();
				$request = $_POST['fieldtext'];

				foreach ( $request as $rq )
					$form[$rq['name']] = $rq['value'];

				$args = WC_BNA_Gateway::get_merchant_params();	
				if ( empty( $args) ) {
					BNAJsonMsgAnswer::send_json_answer( BNA_MSG_ERRORPARAMS );
					wp_die();
				}

				$api = new BNAExchanger( $args );
				$user_id = get_current_user_id();					
				$payorID = get_user_meta( $user_id, 'payorID', true );
			
				if ( ! empty( $payorID) ) {
					BNAJsonMsgAnswer::send_json_answer( BNA_MSG_ERRORPAYOR );
					wp_die();
				}

				$province = isset( $form['billing_state'] ) ? sanitize_text_field( $form['billing_state'] ) : get_user_meta( $user_id, 'billing_state', true );
				$country  = isset( $form['billing_country'] ) ? sanitize_text_field( $form['billing_country'] ) : get_user_meta( $user_id, 'billing_country', true );

				$data = array(
					"companyName" => isset( $form['companyName'] ) 	? sanitize_text_field( $form['companyName'] ) : get_user_meta( $user_id, 'billing_company', true ),
					"firstName"		=> isset( $form['firstName'] ) ? sanitize_text_field( $form['firstName'] ) 	: get_user_meta( $user_id, 'billing_first_name', true ),
					"lastName"		=> isset( $form['lastName'] ) ? sanitize_text_field( $form['lastName'] ) 	: get_user_meta( $user_id, 'billing_last_name', true ),
					"email"	=> wp_get_current_user()->user_email,				
					"phoneCode" =>  isset( $form['phoneCode'] ) ? sanitize_text_field( $form['phoneCode'] ) : get_user_meta( $user_id, 'billing_phone_code', true ),
					"phoneNumber"	 => isset( $form['phone'] ) ? sanitize_text_field( $form['phone'] ) : get_user_meta( $user_id, 'billing_phone', true ),
					"birthDate"		=> isset( $form['billing_birthday'] ) ? date( "Y-m-d\TH:i", strtotime( sanitize_text_field( $form['billing_birthday'] ) ) ) : get_user_meta( $user_id, 'billing_birthday', true ),
					"address"		=> array(
						"streetNumber"	=> isset( $form['billing_street_number'] ) ? sanitize_text_field( $form['billing_street_number'] ) : get_user_meta( $user_id, 'billing_street_number', true ),
						"apartment"		=> isset( $form['billing_apartment'] ) ? sanitize_text_field( $form['billing_apartment'] ) : get_user_meta( $user_id, 'billing_apartment', true ),
						"streetName"	=> isset( $form['billing_street_name'] ) ? sanitize_text_field( $form['billing_street_name'] )	: get_user_meta( $user_id, 'billing_street_name', true ),
						"postalCode" 	=> isset( $form['billing_postcode'] ) ? sanitize_text_field( $form['billing_postcode'] ) : get_user_meta( $user_id, 'billing_postcode', true ),
						"city"			=> isset( $form['billing_city'] ) ? sanitize_text_field( $form['billing_city'] ) : get_user_meta( $user_id, 'billing_city', true ),
						"province"		=> WC()->countries->get_states( $country )[ $province ],
						"country"		=> WC()->countries->countries[ $country ]
					)
				);
				
				// search empty fields and delate
				$filtered_data = array();			
				foreach ( $data as $key => $value ) {
					if ( ! empty( $value ) ) {
						if ( $key == 'address' ) {
							$address = array();
							foreach ( $value as $k => $v ) {
								if ( ! empty( $v ) ) {
									$address[$k] = $v;
								}
							}
							$filtered_data['address'] = $address;
						} else {
							$filtered_data[$key] = $value;
						}
					}
				}

				$response = $api->query(
					$args['serverUrl'] . '/' . $args['protocol'] . '/customers',  
					$filtered_data,
					'POST'
				);
				
				$response = json_decode( $response, true );
			
				empty( $response['id'] ) ? 
					BNAJsonMsgAnswer::send_json_answer( BNA_MSG_UPDATE_ACCOUNT_ERROR ) : 
					BNAJsonMsgAnswer::send_json_answer( BNA_MSG_UPDATE_ACCOUNT_SUCCESS );
			}
		
			wp_die();
		}

		/**
		 * Update payor on front
		 *
		 * @return json
		 */
		public function ajax_update_payor () 
		{
			global $wpdb;
	
			if ( isset( $_POST['nonce'] )) {
				if ( ! wp_verify_nonce( $_POST['nonce'], BNA_CONST_NONCE_NAME ) ) {
					BNAJsonMsgAnswer::send_json_answer( BNA_MSG_ERRORNONCE );
					wp_die();
				}
								
				$form = array();
				$request = $_POST['fieldtext'];

				foreach ( $request as $rq )
					$form[$rq['name']] = $rq['value'];
	
				$args = WC_BNA_Gateway::get_merchant_params();
				if ( empty( $args ) ) {
					BNAJsonMsgAnswer::send_json_answer( BNA_MSG_ERRORPARAMS );
					wp_die();
				}

				$api = new BNAExchanger( $args );
				$user_id = get_current_user_id();
				
				$payorID = get_user_meta( $user_id, 'payorID', true );
				if ( empty( $payorID ) ) {
					BNAJsonMsgAnswer::send_json_answer( BNA_MSG_ERRORPAYOR );
					wp_die();
				}

				$province = isset( $form['billing_state'] ) ? sanitize_text_field( $form['billing_state'] ) : get_user_meta( $user_id, 'billing_state', true );
				$country  = isset( $form['billing_country'] ) ? sanitize_text_field( $form['billing_country'] ) : get_user_meta( $user_id, 'billing_country', true );
				
				$data = array(
					"companyName" 	=> isset( $form['billing_company'] ) ? sanitize_text_field( $form['billing_company'] ) : get_user_meta( $user_id, 'billing_company', true ),
					"firstName"		=> isset( $form['billing_first_name'] ) ? sanitize_text_field( $form['billing_first_name'] ) : get_user_meta( $user_id, 'billing_first_name', true ),
					"lastName"		=> isset( $form['billing_last_name'] ) ? sanitize_text_field( $form['billing_last_name'] ) : get_user_meta( $user_id, 'billing_last_name', true ),
					// here email should not updated
					//"email"	=> wp_get_current_user()->user_email,
					"phoneCode" =>  isset( $form['billing_phone_code'] ) ? sanitize_text_field( $form['billing_phone_code'] ) : get_user_meta( $user_id, 'billing_phone_code', true ),
					"phoneNumber"	 => isset( $form['billing_phone'] ) ? sanitize_text_field( $form['billing_phone'] ) : get_user_meta( $user_id, 'billing_phone', true ),
					"birthDate"		=> isset( $form['billing_birthday'] ) ? date( "Y-m-d\TH:i", strtotime( sanitize_text_field( $form['billing_birthday'] ) ) ) : get_user_meta( $user_id, 'billing_birthday', true ),
					"address"		=> array(
						"streetNumber"	=> isset( $form['billing_street_number'] ) ? sanitize_text_field( $form['billing_street_number'] ) : get_user_meta( $user_id, 'billing_street_number', true ),
						"apartment"		=> isset( $form['billing_apartment'] ) ? sanitize_text_field( $form['billing_apartment'] ) : get_user_meta( $user_id, 'billing_apartment', true ),
						"streetName"	=> isset( $form['billing_street_name'] ) ? sanitize_text_field( $form['billing_street_name'] )	: get_user_meta( $user_id, 'billing_street_name', true ),
						"postalCode"		=> isset( $form['billing_postcode'] ) ? sanitize_text_field( $form['billing_postcode'] ) : get_user_meta( $user_id, 'billing_postcode', true ),
						"city"			=> isset( $form['billing_city'] ) ? sanitize_text_field( $form['billing_city'] ) : get_user_meta( $user_id, 'billing_city', true ),
						"province"		=> WC()->countries->get_states( $country )[ $province ],
						"country"		=> WC()->countries->countries[ $country ]
					)
				);
				
				// search empty fields and delate
				$filtered_data = array();			
				foreach ( $data as $key => $value ) {
					if ( ! empty( $value ) ) {
						if ( $key == 'address' ) {
							$address = array();
							foreach ( $value as $k => $v ) {
								if ( ! empty( $v ) ) {
									$address[$k] = $v;
								}
							}
							$filtered_data['address'] = $address;
						} else {
							$filtered_data[$key] = $value;
						}
					}
				}

				$response = $api->query(
					$args['serverUrl'] . '/' . $args['protocol'] . '/customers/' . $payorID, 
					$filtered_data,
					'PATCH'
				);

				$response = json_decode( $response, true );
			
				empty( $response['id'] ) ? 
					BNAJsonMsgAnswer::send_json_answer( BNA_MSG_UPDATE_ACCOUNT_ERROR ) : 
					BNAJsonMsgAnswer::send_json_answer( BNA_MSG_UPDATE_ACCOUNT_SUCCESS );
			}
		
			wp_die();
		}
		
		/**
		 * Delete payor on front
		 *
		 * @return json
		 */
		public function ajax_delete_payor()
		{ 
			global $wpdb;
			
			if ( isset( $_POST['nonce'] )) {
				if ( !wp_verify_nonce( $_POST['nonce'], BNA_CONST_NONCE_NAME) ) {
					BNAJsonMsgAnswer::send_json_answer( BNA_MSG_ERRORNONCE );
					wp_die();
				}
								
				$args = WC_BNA_Gateway::get_merchant_params();	
				if ( empty( $args ) ) {
					BNAJsonMsgAnswer::send_json_answer( BNA_MSG_ERRORPARAMS );
					wp_die();
				}

				$api = new BNAExchanger( $args );
				$user_id = get_current_user_id();					
				$payorID = get_user_meta ( $user_id, 'payorID', true );
			
				if ( empty( $payorID) ) {
					BNAJsonMsgAnswer::send_json_answer( BNA_MSG_ERRORPAYOR );
					wp_die();
				}

				$response = $api->query(
					$args['serverUrl'] . '/' . $args['protocol'] . '/customers/' . $payorID,  
					'',
					'DELETE'
				);
				
				// remove fields from the database
				$response = json_decode( $response, true );
				
				if ( empty( $response ) ) {
					delete_user_meta( $user_id, 'payorID' );	
									
					$wpdb->query( "DELETE FROM ".$wpdb->prefix.BNA_TABLE_SETTINGS." WHERE payorId='$payorID'" );			
				}
				
				! empty( $response ) ? 
					BNAJsonMsgAnswer::send_json_answer( BNA_MSG_UPDATE_ACCOUNT_ERROR ) : 
					BNAJsonMsgAnswer::send_json_answer( BNA_MSG_UPDATE_ACCOUNT_SUCCESS );
			}
		
			wp_die();
		}

		/**
		 * Add payment method on front
		 *
		 * @return json
		 */
		public function ajax_add_payment() 
		{
			global $wpdb;

			if( isset( $_POST['nonce'] ) ) {
				if ( ! wp_verify_nonce( $_POST['nonce'], BNA_CONST_NONCE_NAME ) ) {
					BNAJsonMsgAnswer::send_json_answer( BNA_MSG_ERRORNONCE );
					wp_die();
				}

				$form = array();
				$request = $_POST['fieldtext'];

				foreach( $request as $rq )
					$form[$rq['name']] = $rq['value'];
									
				$args = WC_BNA_Gateway::get_merchant_params();
				if ( empty( $args ) ) {
					BNAJsonMsgAnswer::send_json_answer( BNA_MSG_ERRORPARAMS );
					wp_die();
				}

				$api = new BNAExchanger( $args);

				$payorID = get_user_meta( get_current_user_id(), 'payorID', true );
				if ( empty( $payorID) ) {
					BNAJsonMsgAnswer::send_json_answer( BNA_MSG_ERRORPAYOR );
					wp_die();
				}
				
				// add 'card' payment type
				if ( $form['payment_type'] === 'card' ) {
					$form['cc_expire_month'] = '';
					$form['cc_expire_year'] = '';
					if ( ! empty( $form['cc_expire'] ) ) {
						$cc_expire = explode( '/', $form['cc_expire'] );
						$form['cc_expire_month'] = $cc_expire[0];
						$form['cc_expire_year'] = $cc_expire[1];
					}
					$cardNumber = str_replace( ' ', '', $form['cc_number'] );

					$data = array (
						'cardNumber'	=> $cardNumber,
						'cardHolder'		=> $form['cc_holder'],
						'cardType'			=> 'credit',
						'cardIdNumber'	=> $form['cc_code'],
						'expiryMonth'	=> trim( $form['cc_expire_month'] ),
						'expiryYear'		=> trim( $form['cc_expire_year'] ),
					);

					$response = $api->query(
						$args['serverUrl'] . '/' . $args['protocol'] . '/customers/' . $payorID . '/card',  
						$data,
						'POST'
					);
				}
				
				// add 'e-transfer' payment type
				if ( $form['payment_type'] === 'e-transfer' ) {
					$data = array (
						'interacEmail' => $form['email'],
					);

					$response = $api->query(
						$args['serverUrl'] . '/' . $args['protocol'] . '/customers/' . $payorID . '/e-transfer',  
						$data,
						'POST'
					);
				}
				
				// add 'eft' payment type
				if ( $form['payment_type'] === 'eft' ) {
					$data = array (
						'bankNumber' => $form['bank_number'],
						'accountNumber' => $form['account_number'],
						'transitNumber' => $form['transit_number'],
					);
					
					$response = $api->query(
						$args['serverUrl'] . '/' . $args['protocol'] . '/customers/' . $payorID . '/eft',  
						$data,
						'POST'
					);
				}
				
				$response = json_decode( $response, true );
				
				empty( $response['id'] ) ? 
					BNAJsonMsgAnswer::send_json_answer( BNA_MSG_ADDPAYMENT_ERROR ) :
					BNAJsonMsgAnswer::send_json_answer( BNA_MSG_ADDPAYMENT_SUCCESS ) ;
						
			}
		
			wp_die();
		}

		/**
		 * Delete payment method on front
		 *
		 * @return json
		 */
		public function ajax_delete_payment() 
		{
			global $wpdb;

			if( isset( $_POST['nonce'] )) {
				if ( !wp_verify_nonce( $_POST['nonce'], BNA_CONST_NONCE_NAME) ) {
					BNAJsonMsgAnswer::send_json_answer(BNA_MSG_ERRORNONCE);
					wp_die();
				}
								
				$payment_id = $_POST['id'];
				if ( empty( $payment_id) ) {
					BNAJsonMsgAnswer::send_json_answer(BNA_MSG_DELPAYMENT_ERRORID);
					wp_die();
				}

				$args = WC_BNA_Gateway::get_merchant_params();
				if ( empty( $args) ) {
					BNAJsonMsgAnswer::send_json_answer(BNA_MSG_ERRORPARAMS);
					wp_die();
				}

				$api = new BNAExchanger( $args);

				$user_id = get_current_user_id();
				$payorID = get_user_meta ( $user_id, 'payorID', true );
				
				$paymentInfo =  $wpdb->get_row("SELECT * FROM ".$wpdb->prefix.BNA_TABLE_SETTINGS." WHERE id=$payment_id");

				if ( empty( $paymentInfo) || empty( $payorID) ) {
					BNAJsonMsgAnswer::send_json_answer(BNA_MSG_ERRORPAYOR);
					wp_die();
				}

				$end = '';
				if ( ! empty( $paymentInfo->paymentMethodId ) ) {
					$end = '/' . $paymentInfo->paymentMethodId;
				}
				  
				$response = $api->query(
					$args['serverUrl'] . '/' . $args['protocol'] . '/customers/' . $payorID . '/' . $paymentInfo->paymentType . $end,
					'',
					'DELETE'
				);

				empty( $response['success'] ) ? 
						BNAJsonMsgAnswer::send_json_answer(BNA_MSG_DELPAYMENT_SUCCESS) :
						BNAJsonMsgAnswer::send_json_answer(BNA_MSG_DELPAYMENT_ERROR);
			}
		
			wp_die();
		}
		
		/**
		 * Copy billing address to shipping
		 *
		 * @return json
		 */
		public function ajax_copy_billing_address_to_shipping() 
		{
			if ( isset( $_POST['nonce'] )) {
				if ( ! wp_verify_nonce( $_POST['nonce'], BNA_CONST_NONCE_NAME ) ) {
					BNAJsonMsgAnswer::send_json_answer( BNA_MSG_ERRORNONCE );
					wp_die();
				}
				
				$current_user_id = get_current_user_id();
				if ( empty( $current_user_id ) ) { wp_die(); }
				
				$updated = false;
				
				$shipping_first_name = get_user_meta( $current_user_id, 'shipping_first_name', true );
				if ( empty( $shipping_first_name ) ) {
					$billing_first_name = get_user_meta( $current_user_id, 'billing_first_name', true );
					if ( $billing_first_name ) {
						update_user_meta( $current_user_id, 'shipping_first_name', sanitize_text_field( $billing_first_name ) );
						$updated = true;
					}
				}
				
				$shipping_last_name = get_user_meta( $current_user_id, 'shipping_last_name', true );
				if ( empty( $shipping_last_name ) ) {
					$billing_last_name = get_user_meta( $current_user_id, 'billing_last_name', true );
					if ( $billing_last_name ) {
						update_user_meta( $current_user_id, 'shipping_last_name', sanitize_text_field( $billing_last_name ) );
						$updated = true;
					}
				}				
				
				$shipping_company = get_user_meta( $current_user_id, 'shipping_company', true );
				if ( empty( $shipping_company ) ) {
					$billing_company = get_user_meta( $current_user_id, 'billing_company', true );
					if ( $billing_company ) {
						update_user_meta( $current_user_id, 'shipping_company', sanitize_text_field( $billing_company ) );
						$updated = true;
					}
				}
				
				$shipping_address_1 = get_user_meta( $current_user_id, 'shipping_address_1', true );
				if ( empty( $shipping_address_1 ) ) {
					$billing_address_1 = get_user_meta( $current_user_id, 'billing_address_1', true );
					if ( $billing_address_1 ) {
						update_user_meta( $current_user_id, 'shipping_address_1', sanitize_text_field( $billing_address_1 ) );
						$updated = true;
					}
				}
				
				$shipping_address_2 = get_user_meta( $current_user_id, 'shipping_address_2', true );
				if ( empty( $shipping_address_2 ) ) {
					$billing_address_2 = get_user_meta( $current_user_id, 'billing_address_2', true );
					if ( $billing_address_2 ) {
						update_user_meta( $current_user_id, 'shipping_address_2', sanitize_text_field( $billing_address_2 ) );
						$updated = true;
					}
				}				
				
				$shipping_country = get_user_meta( $current_user_id, 'shipping_country', true );
				if ( empty( $shipping_country ) ) {
					$billing_country = get_user_meta( $current_user_id, 'billing_country', true );
					if ( $billing_country ) {
						update_user_meta( $current_user_id, 'shipping_country', sanitize_text_field( $billing_country ) );
						$updated = true;
					}
				}
				
				$shipping_state = get_user_meta( $current_user_id, 'shipping_state', true );
				if ( empty( $shipping_state ) ) {
					$billing_state = get_user_meta( $current_user_id, 'billing_state', true );
					if ( $billing_state ) {
						update_user_meta( $current_user_id, 'shipping_state', sanitize_text_field( $billing_state ) );
						$updated = true;
					}
				}
				
				$shipping_city = get_user_meta( $current_user_id, 'shipping_city', true );
				if ( empty( $shipping_city ) ) {
					$billing_city = get_user_meta( $current_user_id, 'billing_city', true );
					if ( $billing_city ) {
						update_user_meta( $current_user_id, 'shipping_city', sanitize_text_field( $billing_city ) );
						$updated = true;
					}
				}
				
				$shipping_postcode = get_user_meta( $current_user_id, 'shipping_postcode', true );
				if ( empty( $shipping_postcode ) ) {
					$billing_postcode = get_user_meta( $current_user_id, 'billing_postcode', true );
					if ( $billing_postcode ) {
						update_user_meta( $current_user_id, 'shipping_postcode', sanitize_text_field( $billing_postcode ) );
						$updated = true;
					}
				}
				
				if ( $updated === true ) {
					$message =
						'<ul class="woocommerce-message">' .
							'<li>'.__( 'Shipping fields updated.', 'wc-bna-gateway' ).'</li>' .
						'</ul>';
					$status = 'true';
					echo ( json_encode( array( 'success'=> $status, 'message' => $message ) ) );
				} else { echo json_encode( array( 'success'=> 'false' ) ); }
			}
			
			wp_die();
		}

		/**
		 * Endpoint for account
		 * @since		1.0.0
		 */
		public static function endpoint_account( $result )
		{
			global $wpdb, $BNAPluginManager;

			if ( empty( $result['id'] ) ) {
				BNAJsonMsgAnswer::send_json_answer( BNA_MSG_ERRORPAYOR );
				wp_die();
			}

			$user = get_user_by( 'email', $result['email'] );

			if ( empty( $user) ) {
				$config = $wpdb->get_row( "SELECT user_id FROM " . $wpdb->prefix . BNA_TABLE_SETTINGS. 
					" WHERE payorId='".$result['id']."'" );
				if ( empty( $config) ) {
					BNAJsonMsgAnswer::send_json_answer( BNA_MSG_ENDPOINT_ACCOUNT_ERRUSER );
					wp_die();
				}
				$user = get_user_by( 'ID', $config->user_id );
			}
		
			foreach ( $result as $rkey => $rval ) {
				$field = '';
				switch ( $rkey) {
					case 'id':						$field = 'payorID'; break;
					case 'companyName':	$field = 'billing_company'; break;
					case 'firstName':			$field = 'billing_first_name'; break;
					case 'lastName':			$field = 'billing_last_name'; break;
					case 'phoneCode':		$field = 'billing_phone_code'; break;
					case 'phoneNumber':	$field = 'billing_phone'; break;
					case 'birthDate':			$field = 'billing_birthday'; break;
					//case 'emailAddress':		$wpdb->query("UPDATE {$wpdb->users} SET user_email='{$rval}' WHERE ID={$user->ID}");
						break;
				}

				if ( ! empty( $field ) ) update_user_meta ( $user->ID, $field, $rval );
			}

			$country = $result['address']['country'];
			foreach ( WC()->countries->countries as $c_key => $c_val ) {
				if ( strripos( $c_val, $country) !== false ) { 
					$country = $c_key;
					update_user_meta ( $user->ID, 'billing_country', $country );
					break;
				}
			}

			$province = $result['address']['province'];
			foreach ( WC()->countries->get_states( $country ) as $p_key => $p_val ) {
				if ( strripos( $p_val, $province ) !== false ) {
					$province = $p_key;
					update_user_meta ( $user->ID, 'billing_state', $province );
					break;
				}
			}

			foreach ( $result['address'] as $rkey => $rval ) {
				$field = '';
				switch ( $rkey) {
					case 'streetNumber':	$field = 'billing_street_number'; break;
					case 'apartment':		$field = 'billing_apartment'; break;
					case 'streetName':	$field = 'billing_street_name'; break;
					case 'postalCode':		$field = 'billing_postcode'; break;
					case 'city':				$field = 'billing_city'; break;
				}

				if ( !empty( $field) ) update_user_meta ( $user->ID, $field, $rval );
			}

			update_user_meta ( $user->ID, 'billing_address_1', $result['address']['streetName'] );
			update_user_meta ( $user->ID, 'billing_address_2', 
				'street #'.$result['address']['streetNumber'] .
					( 
						empty( $result['address']['apartment'] ) ? 
						'' : 
						', apt. '.$result['address']['apartment'] 
					)
			);
			
			$payorID = get_user_meta( $user->ID, 'payorID', true );

			if ( ! empty( $payorID ) ) {
				if ( ! empty( $result['paymentMethods'] ) ) {
			
					$wpdb->query( "DELETE FROM ".$wpdb->prefix . BNA_TABLE_SETTINGS." WHERE payorId='$payorID'" );

					foreach ( $result['paymentMethods'] as $rkey => $rval ) {
						$paymentInfo = '';
						$paymentType = '';
						if ( ! empty( $rval['interacEmail'] ) ) {
							$paymentInfo = $rval['interacEmail'];
							$paymentType = 'e-transfer';
						} elseif ( ! empty( $rval['bankName'] ) ) {
							$paymentInfo = $rval['accountNumber'] . '/' . $rval['transitNumber'];
							$paymentType = 'eft';
						} elseif ( ! empty( $rval['cardType'] ) ) {
							$paymentInfo = $rval['cardNumber'];
							$paymentType = 'card';
						}

						if ( ! empty( $paymentInfo ) ) {
							$stmt = $wpdb->insert( 
								$wpdb->prefix . BNA_TABLE_SETTINGS,  
								array( 
									'user_id' 			=> 	$user->ID, 
									'payorId' 			=> 	$payorID,
									'paymentMethodId' => ! empty( $rval['id'] ) ? esc_html( $rval['id'] ) : '',
									'paymentType' 		=>  $paymentType,
									'paymentInfo' 		=>	esc_html( $paymentInfo ),
									'paymentsRecurrings'=>  0,
									'paymentDescription'=> json_encode( $rval )
								),
								array( 
									'%d','%s','%s','%s','%s','%s','%s'
								)
							);							
						}
					}
				} else {
					$wpdb->query( "DELETE FROM " . $wpdb->prefix . BNA_TABLE_SETTINGS . " WHERE payorId='$payorID'" );
				}
			}

			wp_die();
		}	
	}	//end of class
} //class_exists
