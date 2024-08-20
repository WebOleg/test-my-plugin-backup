<?php
/**
 * Woocommerce Paylinks Gateway
 *
 * @author 		ktscript
 * @category 	'Paylinks Manage Accaount' Class
 * @version     1.0
 */

if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

require_once dirname(__FILE__). "/pl_class_jsonmessage.php";

define ('PAYLINKS_CONST_NONCE_NAME', 'gwpl_nonce');

if (!class_exists('PaylinksAccountManager')) {

	class PaylinksAccountManager {

		/**
		 * Custom endpoint name.
		 *
		 * @var string
		 */
		public static $endpoint_account_management 	= 'pl-account-management';
		public static $endpoint_payment_methods 	= 'pl-payment-methods';
		public static $endpoint_transaction_info 	= 'pl-transaction-info';
		public static $endpoint_recurring_payments 	= 'pl-recurring-payments';

		/**
		 * Plugin actions.
		 */
		public function __construct() {

			$this->plugin_name = plugin_basename(dirname(dirname(__FILE__)));
			$this->plugin_url = trailingslashit(plugin_dir_url(dirname(__FILE__)));

			register_activation_hook( $this->plugin_name, array('PaylinksAccountManager', 'activate') );

			// Actions used to insert a new endpoint in the WordPress.
			add_filter( 'woocommerce_get_query_vars', array( $this, 'get_query_vars' ), 0 );

			// Change the My Accout page title.
			add_filter( 'the_title', array( $this, 'endpoint_title' ) );

			// Insering your new tab/page into the My Account page.
			add_filter( 'woocommerce_account_menu_items', array( $this, 'new_menu_items' ) );
			add_action( 'woocommerce_account_' . self::$endpoint_account_management .  '_endpoint', array( $this, 'endpoint_content_account_management' ) );
			add_action( 'woocommerce_account_' . self::$endpoint_payment_methods .  '_endpoint', array( $this, 'endpoint_content_payment_methods' ) );
			add_action( 'woocommerce_account_' . self::$endpoint_transaction_info .  '_endpoint', array( $this, 'endpoint_content_transaction_info' ) );
			add_action( 'woocommerce_account_' . self::$endpoint_recurring_payments .  '_endpoint', array( $this, 'endpoint_content_recurring_payments' ) );

			add_action( 'wp_enqueue_scripts', array(&$this, 'site_load_styles'));

			add_action('wp_ajax_create_payor', array(&$this, 'ajax_create_payor'));
			add_action('wp_ajax_update_payor', array(&$this, 'ajax_update_payor'));
			add_action('wp_ajax_delete_payment', array(&$this, 'ajax_delete_payment'));
			add_action('wp_ajax_add_payment', array(&$this, 'ajax_add_payment'));

			add_action( 'profile_update', array(&$this, 'check_user_profile_updated'), 10, 2 );
		}

		/**
		* Loading the list of styles and scripts
		* @since		1.0.0
		*/
		public function site_load_styles()
		{
			wp_register_style('wc_gwpl_css_1', $this->plugin_url . 'js/datepicker/css/datepicker.min.css' );
			wp_enqueue_script ( 'wc_gwpl_1', $this->plugin_url.'js/bankNames.js', array(), '1.0.0', true );
			wp_register_script( 'wc_gwpl_2', $this->plugin_url.'js/ajax_io.js', array('jquery'), '1.0.0', true );
			wp_register_script( 'wc_gwpl_3', $this->plugin_url.'js/datepicker/js/datepicker.min.js', array('jquery'), '1.0.0', true );
			wp_register_script( 'wc-country-select', site_url().'/wp-content/plugins/woocommerce/assets/js/frontend/country-select.min.js', array('jquery'), null, true );

			wp_localize_script( 'wc_gwpl_3', 'wc_gwpl',
				array(
					'url' 	=> admin_url('admin-ajax.php'),
					'nonce' => wp_create_nonce(PAYLINKS_CONST_NONCE_NAME)
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
			
			add_rewrite_endpoint( self::$endpoint_account_management, EP_ROOT | EP_PAGES );
			add_rewrite_endpoint( self::$endpoint_payment_methods, EP_ROOT | EP_PAGES );
			add_rewrite_endpoint( self::$endpoint_transaction_info, EP_ROOT | EP_PAGES );
			add_rewrite_endpoint( self::$endpoint_recurring_payments, EP_ROOT | EP_PAGES );
			$wp_rewrite->flush_rules();
		}

		/**
		 * Add new query var.
		 *
		 * @param array $vars
		 * @return array
		 */
		public function get_query_vars( $vars ) {
			$vars[ self::$endpoint_account_management ] = self::$endpoint_account_management;
			$vars[ self::$endpoint_payment_methods ] 	= self::$endpoint_payment_methods;
			$vars[ self::$endpoint_transaction_info ] 	= self::$endpoint_transaction_info;
			$vars[ self::$endpoint_recurring_payments ] = self::$endpoint_recurring_payments;

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
			
			if (  !(is_main_query() && in_the_loop() && is_account_page()) || is_admin() ) return $title;
			
			if ( isset( $wp_query->query_vars[ self::$endpoint_account_management ]) ) {
				$title = __( 'Account management', 'wc-gateway-paylinks' );
				remove_filter( 'the_title', array( $this, 'endpoint_title' ) );
			}

			if ( isset( $wp_query->query_vars[ self::$endpoint_payment_methods ]) ) {
				$title = __( 'Payment methods', 'wc-gateway-paylinks' );
				remove_filter( 'the_title', array( $this, 'endpoint_title' ) );
			}

			if ( isset( $wp_query->query_vars[ self::$endpoint_transaction_info ]) ) {
				$title = __( 'Transaction info', 'wc-gateway-paylinks' );
				remove_filter( 'the_title', array( $this, 'endpoint_title' ) );
			}

			if ( isset( $wp_query->query_vars[ self::$endpoint_recurring_payments ]) ) {
				$title = __( 'Recurring payments', 'wc-gateway-paylinks' );
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
			$items[ self::$endpoint_account_management ] = __( 'Account info', 'wc-gateway-paylinks' );
			$items[ self::$endpoint_payment_methods ] = __( 'Payment methods', 'wc-gateway-paylinks' );
			$items[ self::$endpoint_transaction_info ] = __( 'Transaction info', 'wc-gateway-paylinks' );
			$items[ self::$endpoint_recurring_payments ] = __( 'Recurring payments', 'wc-gateway-paylinks' );
			// Insert back the logout item.
			$items['customer-logout'] = $logout;

			return $items;
		}

		/**
		 * Loading scripts on my-account page
		 */
		public static function loading_scripts() 
		{
			wp_deregister_script('jquery');
			wp_enqueue_script( 'jquery', "https://code.jquery.com/jquery-3.6.0.min.js", array(), null, true);
			wp_enqueue_script( 'wc_gwpl_1');
			wp_enqueue_script( 'wc_gwpl_2');
			wp_enqueue_script( 'wc_gwpl_3');
			wp_enqueue_script( 'wc-country-select');
			wp_enqueue_style('wc_gwpl_css_1');
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

			if ( $user->user_email === $old_user_data->data->user_email ) {
				return $user_id;
			}

			$paymentTypeMethod	= 'account';
			$paymentMethod 		= 'update-payor';
			
			$args = WC_Paylinks_Gateway::get_merchant_params();
			if ( empty($args) ) {
				PaylinksJsonMsgAnswer::send_json_answer(PAYLINKS_MSG_ERRORPARAMS);
				wp_die();
			}

			$api = new PaylinksExchanger($args);
			
			$payorID = get_user_meta ($user_id, 'payorID', true);
			if ( empty($payorID) ) {
				PaylinksJsonMsgAnswer::send_json_answer(PAYLINKS_MSG_ERRORPAYOR);
				wp_die();
			}

			$data = (object) [
				"payorId" => $payorID,
				"payorInfo" => (object) [
					"companyName" 	=> get_user_meta( $user_id, 'billing_company', true ),
					"firstName"		=> get_user_meta( $user_id, 'billing_first_name', true ),
					"lastName"		=> get_user_meta( $user_id, 'billing_last_name', true ),
					"emailAddress"	=> $user->user_email,
					"phone"			=> get_user_meta( $user_id, 'billing_phone', true ),
					"birthday"		=> get_user_meta( $user_id, 'billing_birthday', true ),
					"address"		=> (object) [
						"streetNumber"	=> get_user_meta( $user_id, 'billing_street_number', true ),
						"apartment"		=> get_user_meta( $user_id, 'billing_apartment', true ),
						"streetName"	=> get_user_meta( $user_id, 'billing_street_name', true ),
						"postalZip"		=> get_user_meta( $user_id, 'billing_postcode', true ),
						"city"			=> get_user_meta( $user_id, 'billing_city', true ),
						"province"		=> get_user_meta( $user_id, 'billing_state', true ),
						"country"		=> get_user_meta( $user_id, 'billing_country', true ),
					]
				]
			];

			$response = $api->query(
				$args['serverUrl'].'/'.$args['protocol'].'/'.$paymentTypeMethod.'/'.$paymentMethod,  
				$data,
				'PUT'
			);

			empty($response['success']) ? 
				PaylinksJsonMsgAnswer::send_json_answer(PAYLINKS_MSG_UPDATE_ACCOUNT_ERROR) : 
				PaylinksJsonMsgAnswer::send_json_answer(PAYLINKS_MSG_UPDATE_ACCOUNT_SUCCESS);
		}

		/**
		 * Managing endpoint content accounts management 
		 *
		 * @return view
		 */
		public function endpoint_content_account_management() 
		{
			global $wpdb;

			self::loading_scripts();

			$payorID = get_user_meta (get_current_user_id(), 'payorID', true);
			$paymentMethods =  $wpdb->get_results("SELECT * FROM ".$wpdb->prefix.PAYLINKS_TABLE_SETTINGS);
			require_once dirname(__FILE__). "/../tpl/tpl_account_management.php";
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
			$paymentMethods =  $wpdb->get_results("SELECT * FROM ".$wpdb->prefix.PAYLINKS_TABLE_SETTINGS);
			require_once dirname(__FILE__). "/../tpl/tpl_payment_methods.php";
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
			$orders = wc_get_orders(['customer_id' => get_current_user_id(), 'nopaging' => true]);
			foreach($orders as $order) array_push($orderIDs, $order->get_id());

 			$transactions =  $wpdb->get_results(
				"SELECT * FROM ".$wpdb->prefix.PAYLINKS_TABLE_TRANSACTIONS
					." WHERE order_id IN (". implode(',', $orderIDs).") ORDER BY created_time DESC"
			);
			require_once dirname(__FILE__). "/../tpl/tpl_transaction_info.php";
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
			$subscriptions =  $wpdb->get_results(
				"SELECT * FROM ".$wpdb->prefix.PAYLINKS_TABLE_RECURRING." WHERE user_id='{$userID}' ORDER BY created_time DESC"
			);
			require_once dirname(__FILE__). "/../tpl/tpl_subscription_info.php";
		}

		/**
		 * Create payor on front
		 *
		 * @return json
		 */
		public function ajax_create_payor() 
		{
			global $wpdb;
			
			$paymentTypeMethod	= 'account';
			$paymentMethod 		= 'create-payor';
		
			if( isset($_POST['nonce'])) {
				if ( !wp_verify_nonce( $_POST['nonce'], PAYLINKS_CONST_NONCE_NAME) ) {
					PaylinksJsonMsgAnswer::send_json_answer(PAYLINKS_MSG_ERRORNONCE);
					wp_die();
				}
								
				$form = array();
				$request = $_POST['fieldtext'];

				foreach($request as $rq)
					$form[$rq['name']] = $rq['value'];

				$args = WC_Paylinks_Gateway::get_merchant_params();
				if ( empty($args) ) {
					PaylinksJsonMsgAnswer::send_json_answer(PAYLINKS_MSG_ERRORPARAMS);
					wp_die();
				}

				$api = new PaylinksExchanger($args);
				$user_id = get_current_user_id();
				
				$payorID = get_user_meta ($user_id, 'payorID', true);
				if ( empty($payorID) ) {
					PaylinksJsonMsgAnswer::send_json_answer(PAYLINKS_MSG_ERRORPAYOR);
					wp_die();
				}

				$province = isset($form['billing_state'])   ? sanitize_text_field($form['billing_state'])   : get_user_meta( $user_id, 'billing_state', true );
				$country  = isset($form['billing_country']) ? sanitize_text_field($form['billing_country']) : get_user_meta( $user_id, 'billing_country', true );

				$data = (object) [
					"payorId" => $payorID,
					"payorInfo" => (object) [
						"companyName" 	=> isset($form['companyName']) 	? sanitize_text_field($form['companyName']) : get_user_meta( $user_id, 'billing_company', true ),
						"firstName"		=> isset($form['firstName']) 	? sanitize_text_field($form['firstName']) 	: get_user_meta( $user_id, 'billing_first_name', true ),
						"lastName"		=> isset($form['lastName']) 	? sanitize_text_field($form['lastName']) 	: get_user_meta( $user_id, 'billing_last_name', true ),
						"emailAddress"	=> wp_get_current_user()->user_email,
						"phone"			=> isset($form['phone']) 		? sanitize_text_field($form['phone']) 		: get_user_meta( $user_id, 'billing_phone', true ),
						"birthday"		=> isset($form['birthday']) 	? date("Y-m-d\TH:i",strtotime(sanitize_text_field($form['birthday']))) : get_user_meta( $user_id, 'billing_birthday', true ),
						"address"		=> (object) [
							"streetNumber"	=> isset($form['billing_street_number']) ? sanitize_text_field($form['billing_street_number']) 	: get_user_meta( $user_id, 'billing_street_number', true ),
							"apartment"		=> isset($form['billing_apartment']) 	 ? sanitize_text_field($form['billing_apartment']) 		: get_user_meta( $user_id, 'billing_apartment', true ),
							"streetName"	=> isset($form['billing_street_name']) 	 ? sanitize_text_field($form['billing_street_name'])	: get_user_meta( $user_id, 'billing_street_name', true ),
							"postalZip"		=> isset($form['billing_postcode']) 	 ? sanitize_text_field($form['billing_postcode']) 		: get_user_meta( $user_id, 'billing_postcode', true ),
							"city"			=> isset($form['billing_city']) 		 ? sanitize_text_field($form['billing_city']) 			: get_user_meta( $user_id, 'billing_city', true ),
							"province"		=> WC()->countries->get_states( $country )[ $province ],
							"country"		=> WC()->countries->countries[ $country ]
						]
					]
				];

				$response = $api->query(
					$args['serverUrl'].'/'.$args['protocol'].'/'.$paymentTypeMethod.'/'.$paymentMethod,  
					$data,
					'PUT'
				);
	
				empty($response['success']) ? 
					PaylinksJsonMsgAnswer::send_json_answer(PAYLINKS_MSG_UPDATE_ACCOUNT_ERROR) : 
					PaylinksJsonMsgAnswer::send_json_answer(PAYLINKS_MSG_UPDATE_ACCOUNT_SUCCESS);
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
			
			$paymentTypeMethod	= 'account';
			$paymentMethod 		= 'update-payor';
		
			if( isset($_POST['nonce'])) {
				if ( !wp_verify_nonce( $_POST['nonce'], PAYLINKS_CONST_NONCE_NAME) ) {
					PaylinksJsonMsgAnswer::send_json_answer(PAYLINKS_MSG_ERRORNONCE);
					wp_die();
				}
								
				$form = array();
				$request = $_POST['fieldtext'];

				foreach($request as $rq)
					$form[$rq['name']] = $rq['value'];

				$args = WC_Paylinks_Gateway::get_merchant_params();
				if ( empty($args) ) {
					PaylinksJsonMsgAnswer::send_json_answer(PAYLINKS_MSG_ERRORPARAMS);
					wp_die();
				}

				$api = new PaylinksExchanger($args);
				$user_id = get_current_user_id();
				
				$payorID = get_user_meta ($user_id, 'payorID', true);
				if ( empty($payorID) ) {
					PaylinksJsonMsgAnswer::send_json_answer(PAYLINKS_MSG_ERRORPAYOR);
					wp_die();
				}

				$province = isset($form['billing_state'])   ? sanitize_text_field($form['billing_state'])   : get_user_meta( $user_id, 'billing_state', true );
				$country  = isset($form['billing_country']) ? sanitize_text_field($form['billing_country']) : get_user_meta( $user_id, 'billing_country', true );
				
				$data = (object) [
					"payorId" => $payorID,
					"payorInfo" => (object) [
						"companyName" 	=> isset($form['companyName']) 	? sanitize_text_field($form['companyName']) : get_user_meta( $user_id, 'billing_company', true ),
						"firstName"		=> isset($form['firstName']) 	? sanitize_text_field($form['firstName']) 	: get_user_meta( $user_id, 'billing_first_name', true ),
						"lastName"		=> isset($form['lastName']) 	? sanitize_text_field($form['lastName']) 	: get_user_meta( $user_id, 'billing_last_name', true ),
						"emailAddress"	=> wp_get_current_user()->user_email,
						"phone"			=> isset($form['phone']) 		? sanitize_text_field($form['phone']) 		: get_user_meta( $user_id, 'billing_phone', true ),
						"birthday"		=> isset($form['birthday']) 	? date("Y-m-d\TH:i",strtotime(sanitize_text_field($form['birthday']))) : get_user_meta( $user_id, 'billing_birthday', true ),
						"address"		=> (object) [
							"streetNumber"	=> isset($form['billing_street_number']) ? sanitize_text_field($form['billing_street_number']) 	: get_user_meta( $user_id, 'billing_street_number', true ),
							"apartment"		=> isset($form['billing_apartment']) 	 ? sanitize_text_field($form['billing_apartment']) 		: get_user_meta( $user_id, 'billing_apartment', true ),
							"streetName"	=> isset($form['billing_street_name']) 	 ? sanitize_text_field($form['billing_street_name'])	: get_user_meta( $user_id, 'billing_street_name', true ),
							"postalZip"		=> isset($form['billing_postcode']) 	 ? sanitize_text_field($form['billing_postcode']) 		: get_user_meta( $user_id, 'billing_postcode', true ),
							"city"			=> isset($form['billing_city']) 		 ? sanitize_text_field($form['billing_city']) 			: get_user_meta( $user_id, 'billing_city', true ),
							"province"		=> WC()->countries->get_states( $country )[ $province ],
							"country"		=> WC()->countries->countries[ $country ]
						]
					]
				];

				$response = $api->query(
					$args['serverUrl'].'/'.$args['protocol'].'/'.$paymentTypeMethod.'/'.$paymentMethod,  
					$data,
					'PUT'
				);
	
				empty($response['success']) ? 
					PaylinksJsonMsgAnswer::send_json_answer(PAYLINKS_MSG_UPDATE_ACCOUNT_ERROR) : 
					PaylinksJsonMsgAnswer::send_json_answer(PAYLINKS_MSG_UPDATE_ACCOUNT_SUCCESS);
			}
		
			wp_die();
		}

		/**
		 * Add payment method on front
		 *
		 * @return json
		 */
		public function ajax_add_payment () 
		{
			global $wpdb;

			$paymentTypeMethod 	= 'account';
			$paymentMethod 		= 'add-paymentmethod';

			if( isset($_POST['nonce'])) {
				if ( !wp_verify_nonce( $_POST['nonce'], PAYLINKS_CONST_NONCE_NAME) ) {
					PaylinksJsonMsgAnswer::send_json_answer(PAYLINKS_MSG_ERRORNONCE);
					wp_die();
				}

				$form = array();
				$request = $_POST['fieldtext'];

				foreach($request as $rq)
					$form[$rq['name']] = $rq['value'];
	
				$args = WC_Paylinks_Gateway::get_merchant_params();
				if ( empty($args) ) {
					PaylinksJsonMsgAnswer::send_json_answer(PAYLINKS_MSG_ERRORPARAMS);
					wp_die();
				}

				$api = new PaylinksExchanger($args);

				$payorID = get_user_meta (get_current_user_id(), 'payorID', true);
				if ( empty($payorID) ) {
					PaylinksJsonMsgAnswer::send_json_answer(PAYLINKS_MSG_ERRORPAYOR);
					wp_die();
				}

				$data = (object) [ 
					"payorId" => $payorID,
					"paymentMethod" => (object) [
						"currency"	=> get_woocommerce_currency()
					]
				];
				
				switch ($form['paymentType']) {
					case PAYLINKS_PAYMENT_TYPE_CREDITCARD: 
						$params = array (
							"paymentType"		=> check_cc($form['cc_number']),
							"cardNumber"		=> $form['cc_number'],
							"securityCode"		=> $form['cc_code'],
							"expiryMonth"		=> $form['cc_expire_month'],
							"expiryYear"		=> $form['cc_expire_year'],
							"cardHolder"		=> $form['cc_holder']
						);
						foreach ($params as $p_key => $p_val) 
							$data->{"paymentMethod"}->{$p_key} = $p_val;
						break;
					case PAYLINKS_PAYMENT_TYPE_DIRECTDEBIT:
						$params = array (
							"paymentType" 		=> "DIRECT-DEBIT",
							"institutionNumber"	=> $form['ddBankName'] !== 'other' ? $form['ddBankName'] : $form['ddInstitutionNumber'],
							"accountNumber"		=> $form['ddAccountNumber'],
							"transitNumber"		=> $form['ddTransitNumber']
						);
						foreach ($params as $p_key => $p_val)
							$data->paymentMethod->{$p_key} = $p_val;
						break;
					case PAYLINKS_PAYMENT_TYPE_DIRECTCREDIT:
						$params = array (
							"paymentType" 		=> "DIRECT-CREDIT",
							"institutionNumber"	=> $form['dcBankName'] !== 'other' ? $form['dcBankName'] : $form['dcInstitutionNumber'],
							"accountNumber"		=> $form['dcAccountNumber'],
							"transitNumber"		=> $form['dcTransitNumber']
						);
						foreach ($params as $p_key => $p_val)
							$data->paymentMethod->{$p_key} = $p_val;
						break;

					default:
						PaylinksJsonMsgAnswer::send_json_answer(PAYLINKS_MSG_ADDPAYMENT_ERRPAYMENTTYPE);
						wp_die();				
				}

				$response = $api->query(
					$args['serverUrl'].'/'.$args['protocol'].'/'.$paymentTypeMethod.'/'.$paymentMethod,  
					$data,
					'PUT'
				);
	
				empty($response['success']) ? 
					PaylinksJsonMsgAnswer::send_json_answer(PAYLINKS_MSG_ADDPAYMENT_ERROR) :
					PaylinksJsonMsgAnswer::send_json_answer(PAYLINKS_MSG_ADDPAYMENT_SUCCESS) ;
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

			$paymentTypeMethod	= 'account';
			$paymentMethod 		= 'delete-paymentmethod';
			
			if( isset($_POST['nonce'])) {
				if ( !wp_verify_nonce( $_POST['nonce'], PAYLINKS_CONST_NONCE_NAME) ) {
					PaylinksJsonMsgAnswer::send_json_answer(PAYLINKS_MSG_ERRORNONCE);
					wp_die();
				}
								
				$payment_id = $_POST['id'];
				if ( empty($payment_id) ) {
					PaylinksJsonMsgAnswer::send_json_answer(PAYLINKS_MSG_DELPAYMENT_ERRORID);
					wp_die();
				}

				$args = WC_Paylinks_Gateway::get_merchant_params();
				if ( empty($args) ) {
					PaylinksJsonMsgAnswer::send_json_answer(PAYLINKS_MSG_ERRORPARAMS);
					wp_die();
				}

				$api = new PaylinksExchanger($args);

				$user_id = get_current_user_id();
				$payorID = get_user_meta ($user_id, 'payorID', true);
				
				$paymentInfo =  $wpdb->get_row("SELECT * FROM ".$wpdb->prefix.PAYLINKS_TABLE_SETTINGS." WHERE id=$payment_id");

				if ( empty($paymentInfo) || empty($payorID) ) {
					PaylinksJsonMsgAnswer::send_json_answer(PAYLINKS_MSG_ERRORPAYOR);
					wp_die();
				}

				$data = (object) [
					"payorId" => $payorID,
					"paymentMethodId" => $paymentInfo->paymentMethodId
				];

				$response = $api->query(
					$args['serverUrl'].'/'.$args['protocol'].'/'.$paymentTypeMethod.'/'.$paymentMethod,  
					$data,
					'DELETE'
				);
	
				empty($response['success']) ? 
						PaylinksJsonMsgAnswer::send_json_answer(PAYLINKS_MSG_DELPAYMENT_ERROR) : 
						PaylinksJsonMsgAnswer::send_json_answer(PAYLINKS_MSG_DELPAYMENT_SUCCESS);
			}
		
			wp_die();
		}

		/**
		 * Endpoint for account
		 * @since		1.0.0
		 */
		public static function endpoint_account ($result)
		{
			global $wpdb, $PaylinksPluginManager;

			if ( empty($result['data']['payorId']) ) {
				PaylinksJsonMsgAnswer::send_json_answer(PAYLINKS_MSG_ERRORPAYOR);
				wp_die();
			}

			$user = get_user_by('email', $result['data']['emailAddress']);
			if ( empty($user) ) {
				$config = $wpdb->get_row("SELECT user_id FROM ".$wpdb->prefix.PAYLINKS_TABLE_SETTINGS. 
					" WHERE payorId='".$result['data']['payorId']."'");
				if ( empty($config) ) {
					PaylinksJsonMsgAnswer::send_json_answer(PAYLINKS_MSG_ENDPOINT_ACCOUNT_ERRUSER);
					wp_die();
				}
				$user = get_user_by('ID', $config->user_id );
			}
			
			foreach ($result['data'] as $rkey => $rval) {
				$field = '';
				switch ($rkey) {
					case 'payorId':		$field = 'payorID'; break;
					case 'companyName':	$field = 'billing_company'; break;
					case 'firstName':	$field = 'billing_first_name'; break;
					case 'lastName':	$field = 'billing_last_name'; break;
					case 'phone':		$field = 'billing_phone'; break;
					case 'birthday':	$field = 'billing_birthday'; break;
					case 'emailAddress':
						$wpdb->query("UPDATE {$wpdb->users} SET user_email='{$rval}' WHERE ID={$user->ID}");
						break;
				}

				if ( !empty($field) ) update_user_meta ( $user->ID, $field, $rval );
			}

			$country = $result['data']['address']['country'];
			foreach (WC()->countries->countries as $c_key => $c_val) {
				if ( strripos($c_val, $country) !== false ) { 
					$country = $c_key;
					update_user_meta ( $user->ID, 'billing_country', $country );
					break;
				}
			}

			$province = $result['data']['address']['province'];
			foreach (WC()->countries->get_states( $country ) as $p_key => $p_val) {
				if ( strripos($p_val, $province) !== false ) {
					$province = $p_key;
					update_user_meta ( $user->ID, 'billing_state', $province );
					break;
				}
			}

			foreach ($result['data']['address'] as $rkey => $rval) {
				$field = '';
				switch ($rkey) {
					case 'streetNumber':	$field = 'billing_street_number'; break;
					case 'apartment':		$field = 'billing_apartment'; break;
					case 'streetName':		$field = 'billing_street_name'; break;
					case 'postalZip':		$field = 'billing_postcode'; break;
					case 'city':			$field = 'billing_city'; break;
				}

				if ( !empty($field) ) update_user_meta ( $user->ID, $field, $rval );
			}

			update_user_meta ( $user->ID, 'billing_address_1', $result['data']['address']['streetName'] );
			update_user_meta ( $user->ID, 'billing_address_2', 
				'street #'.$result['data']['address']['streetNumber'] .
					( 
						empty($result['data']['address']['apartment']) ? 
						'' : 
						', apt. '.$result['data']['address']['apartment'] 
					)
			);
			
			$payorID = get_user_meta ($user->ID, 'payorID', true);

			if ( !empty($payorID) ) {
				
				$wpdb->query("DELETE FROM ".$wpdb->prefix.PAYLINKS_TABLE_SETTINGS." WHERE payorId='$payorID'");

				foreach ($result['data']['paymentTypes'] as $rkey => $rval) {
					$paymentInfo = '';
					switch ($rval['paymentType']) {
						case 'MASTERCARD':
						case 'VISA':
						case 'AMEX':
							$paymentInfo = $rval['cardNumber'];
							break;
						case 'DIRECT-DEBIT':
						case 'DIRECT-CREDIT':
							$paymentInfo = $rval['accountNumber'] . '/' . $rval['transitNumber'];
							break;
						case 'ETRANSFER':
							$paymentInfo = $rval['emailAddress'];
							break;		
					}

					if ( !empty($paymentInfo) ) {
						$wpdb->insert( 
							$wpdb->prefix.PAYLINKS_TABLE_SETTINGS,  
							array( 
								'user_id' 			=> 	$user->ID, 
								'payorId' 			=> 	$payorID,
								'paymentMethodId' 	=>  $rval['paymentMethodId'],
								'paymentType' 		=>  $rval['paymentType'],
								'paymentInfo' 		=> 	$paymentInfo,
								'paymentsRecurrings'=>  0,
								'paymentDescription'=> 	json_encode($rval)
							),
							array( 
								'%d','%s','%s','%s','%s','%s','%s'
							)
						);
					}
				}
			}	

			$wpdb->query("DELETE FROM ".$wpdb->prefix.PAYLINKS_TABLE_RECURRING." WHERE user_id='$user->ID'");

			if ( isset( $result['data']['subscriptions'] ) ) {

				foreach ($result['data']['subscriptions'] as $rkey => $rval) {
					$wpdb->insert( 
						$wpdb->prefix.PAYLINKS_TABLE_RECURRING,  
						array( 
							'user_id'				=> $user->ID,
							'order_id'		        => $rval['transactionInfo']['invoiceId'],
							'recurringId'		    => $rval['recurringId'],
							'recurring'		    	=> $rval['recurring'],
							'status'		        => $rval['status'],
							'startDate'		        => $rval['startDate'],
							'nextChargeDate'		=> $rval['nextChargeDate'],
							'expire'		        => empty( $rval['expire'] ) ? "" : $rval['expire'],
							'numberOfPayments'      => isset( $rval['numberOfPayments'] ) ? $rval['numberOfPayments'] : -1,
							'recurringDescription'  => json_encode((object)[ 'transactionInfo' => $rval['transactionInfo'], 
								'cartItems' => $rval['cartItems']])
						),
						array( 
							'%d','%s','%s','%s','%s','%s','%s','%s','%s','%s'
						)
					);
				}
			}	

			wp_die();
		}	
	}	//end of class
} //class_exists
