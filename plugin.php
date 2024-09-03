<?php
/**
 * Plugin Name: WooCommerce BNA Payment Gateway
 * Description: Official BNA payment system plug-in for Woocommerce
 * Author: BNA
 * Author URI: https://bnasmartpayment.com/
 * Version: 1.0.0
 * Text Domain: wc-bna-gateway
 * Domain Path: /i18n/languages/
 *
 * @package   WC-BNA-Gateway
 * @author    BNA
 * @category  Admin
 * @copyright Copyright (c) 2021 
 *
 */
 
defined( 'ABSPATH' ) or exit;

function my_log( $str ) {
	//$str = unserialize($str);
	if ( is_array($str) ) {
		//$str = implode(',', $str);
		$str = json_encode($str);
		//$str = var_dump($str);
	}
	$log_str = "Logged On: " . date("m/d/Y H:i:s") . "\n" . $str . "\n-------------\n";
	$loghandle = fopen(dirname(__FILE__) . "/../my_logs.txt", "a+");
        fwrite($loghandle, $log_str);
        fclose($loghandle);
}

// Plugin PATH:
if ( ! defined( 'BNA_PLUGIN_DIR_PATH' ) ) {
	define( 'BNA_PLUGIN_DIR_PATH', plugin_dir_path( __FILE__ ) );
}

// Plugin Url:
if ( ! defined( 'BNA_PLUGIN_DIR_URL' ) ) {
	define( 'BNA_PLUGIN_DIR_URL', plugin_dir_url( __FILE__ ) );
}

require_once dirname(__FILE__). "/inc/bna_class_exchanger.php";
require_once dirname(__FILE__). "/inc/bna_class_cctools.php";
require_once dirname(__FILE__). "/inc/bna_class_wcgate.php";
require_once dirname(__FILE__). "/inc/bna_class_manageaccount.php";
require_once dirname(__FILE__). "/inc/bna_class_subscriptions.php";
require_once dirname(__FILE__). "/inc/bna_class_jsonmessage.php";

// Make sure WooCommerce is active
if ( ! in_array( 'woocommerce/woocommerce.php', apply_filters( 'active_plugins', get_option( 'active_plugins' ) ) ) ) {
	return;
}

// Basic params in constants
define ('BNA_TABLE_TRANSACTIONS', 'bna_transactions');
define ('BNA_TABLE_SETTINGS', 'bna_settings');
define ('BNA_TABLE_RECURRING', 'bna_recurring');

define ('BNA_PAYMENT_TYPE_ETRANFER', -1);
define ('BNA_PAYMENT_TYPE_CREDITCARD', 1);
define ('BNA_PAYMENT_TYPE_DIRECTDEBIT', 2);
define ('BNA_PAYMENT_TYPE_DIRECTCREDIT', 4);

define ('BNA_SUBSCRIPTION_SETTING_REPEAT', 'monthly');
define ('BNA_SUBSCRIPTION_SETTING_STARTDATE', 0);//date('Y-m-d'));
define ('BNA_SUBSCRIPTION_SETTING_NUMPAYMENT', 0);


/*
* BNA plugin management class
*/
if (!class_exists('BNAPluginManager')) {

	class BNAPluginManager {
	 
		public function __construct ()
		{		
			$this->plugin_name = plugin_basename(__FILE__);
			$this->plugin_url = trailingslashit(plugin_dir_url((__FILE__)));
			register_activation_hook( $this->plugin_name, array('BNAPluginManager', 'activate') );
			register_deactivation_hook( $this->plugin_name, array('BNAPluginManager', 'deactivate') );
			register_uninstall_hook( $this->plugin_name, array('BNAPluginManager', 'uninstall') );

			add_filter( 'woocommerce_checkout_fields' , array(&$this,'custom_override_checkout_fields') );

			if (is_admin()) {
				add_action( 'woocommerce_admin_order_data_after_order_details', array(&$this,'show_order_itemmeta'));
			}
			add_filter( 'woocommerce_states', array(&$this, 'custom_woocommerce_states') );
			add_filter( 'woocommerce_countries', array(&$this, 'custom_woocommerce_countries') );

			add_filter( 'woocommerce_locate_template', array( $this, 'bna_wc_template' ), 10, 3 );
		}

		/**
		 * Executable code when activate a plugin
		 * @since 1.0.0
		 */
		public static function activate() 
		{
			global $wpdb;
			
			require_once(ABSPATH . 'wp-admin/includes/upgrade.php');

			$link = mysqli_connect(DB_HOST, DB_USER, DB_PASSWORD);

			if ($link) {
				if ( version_compare(mysqli_get_server_info($link), '4.1.0', '>=') ) {
					if ( ! empty($wpdb->charset) )
						$charset_collate = "DEFAULT CHARACTER SET $wpdb->charset";
					if ( ! empty($wpdb->collate) )
						$charset_collate .= " COLLATE $wpdb->collate";
				}
				mysqli_close($link);

			} else {
				$charset_collate = "DEFAULT CHARACTER SET $wpdb->charset";
			}

			$sql_table = 
				'CREATE TABLE `'.$wpdb->prefix.BNA_TABLE_TRANSACTIONS.'` (
					`id` bigint(20) unsigned NOT NULL auto_increment,
					`order_id` varchar(100) NOT NULL,
					`transactionToken` varchar(100) NOT NULL,
					`referenceNumber` varchar(100) NOT NULL,
					`transactionDescription` varchar(2000) NOT NULL,
					`transactionStatus` varchar(100) NOT NULL,
					`created_time` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
					PRIMARY KEY (`id`)
					)' .$charset_collate.";";
			if ( $wpdb->get_var("show tables like '".$wpdb->prefix.BNA_TABLE_TRANSACTIONS."'") != $wpdb->prefix.BNA_TABLE_TRANSACTIONS ) {
				dbDelta($sql_table);
			}

			$sql_table = 
				'CREATE TABLE `'.$wpdb->prefix.BNA_TABLE_SETTINGS.'` (
					`id` bigint(20) unsigned NOT NULL auto_increment,
					`user_id` bigint(20) unsigned NOT NULL,
					`payorId`	varchar(100) NOT NULL,
					`paymentMethodId` varchar(100) NOT NULL,
					`paymentType` varchar(100) NOT NULL,
					`paymentInfo`	varchar(100) NOT NULL,
					`paymentsRecurrings` TINYINT NOT NULL DEFAULT "0",
					`paymentDescription` varchar(2000) NOT NULL,
					`created_time` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
					PRIMARY KEY (`id`)
					)' .$charset_collate.";";
			if ( $wpdb->get_var("show tables like '".$wpdb->prefix.BNA_TABLE_SETTINGS."'") != $wpdb->prefix.BNA_TABLE_SETTINGS ) {
				dbDelta($sql_table);
			}	
			
			$sql_table = 
			'CREATE TABLE `'.$wpdb->prefix.BNA_TABLE_RECURRING.'` (
				`id` bigint(20) unsigned NOT NULL auto_increment,
				`user_id` bigint(20) unsigned NOT NULL,
				`order_id` varchar(100) NOT NULL,
				`recurringId` varchar(100) NOT NULL,
				`recurring` varchar(30) NOT NULL,
				`status` varchar(20) NOT NULL,
				`startDate` varchar(50) NOT NULL,
				`nextChargeDate` varchar(50) NOT NULL,
				`expire` varchar(50) NOT NULL,
				`numberOfPayments` int(11) NOT NULL DEFAULT "0",
				`recurringDescription` varchar(2000) NOT NULL,
				`created_time` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
				PRIMARY KEY (`id`)
				)' .$charset_collate.";";
			if ( $wpdb->get_var("show tables like '".$wpdb->prefix.BNA_TABLE_RECURRING."'") != $wpdb->prefix.BNA_TABLE_RECURRING ) {
				dbDelta($sql_table);
			}
		}

		/**
		 * Executable code when deactivate a plugin
		 * @since 1.0.0
		 */
		public static function deactivate() 
		{
			return true;
		}

		/**
		* Executable code when uninstall a plugin
		* @since 1.0.0
		*/
		public static function uninstall() 
		{
			global $wpdb;
			$wpdb->query("DROP TABLE IF EXISTS ".$wpdb->prefix.BNA_TABLE_TRANSACTIONS);
			$wpdb->query("DROP TABLE IF EXISTS ".$wpdb->prefix.BNA_TABLE_SETTINGS);
		}

		/**
		* Uploading a list of states
		* @since 1.0.0
		* @param array $states
 		* @return array $states all states + our custom 
		*/
		public function custom_woocommerce_states( $states ) 
		{
			$iso_codes = file_get_contents($this->plugin_url.'js/iso-3166-2.json');

			if ( !empty( $iso_codes ) ) {
				$iso = json_decode($iso_codes, true);

				foreach($iso as $ikey => $ival) {
					$states[$ikey] = array();
					foreach($ival['divisions'] as $dkey => $dval) {
						$state = explode('-', $dkey);
						$state = array_pop($state);
						$states[$ikey][$state] = $dval;
					}
				}
			}

			return $states;
		}

		/**
		* Uploading a list of countries 
		* @since 1.0.0
		* @param array $countries
 		* @return array $countries all countries + our custom 
		*/
		public function custom_woocommerce_countries( $countries ) 
		{
			$iso_codes = file_get_contents($this->plugin_url.'js/iso-3166-2.json');

			if ( !empty( $iso_codes ) ) {
				$iso = json_decode($iso_codes, true);

				foreach($iso as $ikey => $ival) {
					$countries[$ikey] = $ival['name'];
				}
			}

			return $countries;
		}
 
		/**
		* Changing the order of the fields on the checkout page 
		* @since 1.0.0
		* @param array $fields
 		* @return array $fields all checkout fields + our custom 
		*/
		public function custom_override_checkout_fields( $fields ) 
		{
			$fields['billing']['billing_street_name'] = array(
				'type' => 'text', 
				'label' => __('Street name', 'wc-bna-gateway'),
				'required' => true,
				'class' => array('form-row-wide'),
			);
			$fields['billing']['billing_street_number'] = array(
				'type' => 'text', 
				'label' => __('Street number', 'wc-bna-gateway'),
				'required' => true,
				'class' => array('form-row-wide'),
			);
			$fields['billing']['billing_apartment'] = array(
				'type' => 'text', 
				'label' => __('Apartment', 'wc-bna-gateway'),
				'required' => false,
				'class' => array('form-row-wide'),
			);

			$fields['billing']['billing_address_1']['required'] = false;
			$fields['billing']['billing_address_2']['required'] = false;

			$order_fields = array(
				"billing_first_name", 
				"billing_last_name", 
				"billing_company", 
				"billing_country", 
				"billing_state", 
				'billing_city',
				"billing_postcode",
				"billing_street_name", 
				"billing_street_number", 
				"billing_apartment", 
				"billing_email", 
				"billing_phone",
				"billing_address_1",
				"billing_address_2",
			);
		
			$new = [];
			foreach($order_fields as $key => $o_field) {
				$new [$o_field] = $fields["billing"][$o_field];
			}
			$fields["billing"] = $new;

		
			return $fields;
		}

		/**
		* Displaying transaction information in the admin panel
		* @since 1.0.0
		* @param object $order
		*/		
		
		public function show_order_itemmeta( $order )
		{
			global $wpdb;

			echo '<p class="form-field form-field-wide">&nbsp;</p>';
			echo '<h3>'.__('Extra Details', 'wc-bna-gateway').'</h3>';

			$paymentInfo =  $wpdb->get_results("SELECT * FROM ".$wpdb->prefix.BNA_TABLE_TRANSACTIONS." WHERE order_id=".$order->get_id());
			foreach($paymentInfo as $pi_key => $pi_val) {
				$data = json_decode($pi_val->transactionDescription); 
				?>
					<p class="form-field form-field-wide"><strong><?=__( 'Transaction #', 'wc-bna-gateway' ).$pi_key;?>:</strong></p>
					<p class="form-field form-field-wide"><?=__( 'Transaction token', 'wc-bna-gateway' );?>: <?=$pi_val->transactionToken;?></p>
					<p class="form-field form-field-wide"><?=__( 'Reference number', 'wc-bna-gateway' );?>: <?=$pi_val->referenceNumber;?></p>
					<p class="form-field form-field-wide"><?=__( 'Type', 'wc-bna-gateway' );?>: <?=$data->transactionType;?></p>
					<p class="form-field form-field-wide"><?=__( 'Status', 'wc-bna-gateway' );?>: <?=$data->transactionStatus;?></p>
					<p class="form-field form-field-wide"><?=__( 'Created', 'wc-bna-gateway' );?>: <?=$pi_val->created_time;?></p>
					<p class="form-field form-field-wide">&nbsp;</p>
				<?php
			}
		}
		
		/**
		 * Filter templates path to use in this plugin instead of the one in WooCommerce.
		 *
		 * @param string $template      Default template file path.
		 * @param string $template_name Template file slug.
		 * @param string $template_path Template file name.
		 *
		 * @return string The new Template file path.
		 */
		function bna_wc_template( $template, $template_name, $template_path ) {
			// my-account
			if ( 'navigation.php' === basename( $template ) ) {
				$template = BNA_PLUGIN_DIR_PATH . 'woocommerce/templates/myaccount/navigation.php';
			} elseif ( 'dashboard.php' === basename( $template ) ) {
				$template = BNA_PLUGIN_DIR_PATH . 'woocommerce/templates/myaccount/dashboard.php';
			} elseif ( 'orders.php' === basename( $template ) ) {
				$template = BNA_PLUGIN_DIR_PATH . 'woocommerce/templates/myaccount/orders.php';
			} elseif ( 'my-address.php' === basename( $template ) ) {
				$template = BNA_PLUGIN_DIR_PATH . 'woocommerce/templates/myaccount/my-address.php';
			}
			
			// cart
			if ( 'cart.php' === basename( $template ) ) {
				$template = BNA_PLUGIN_DIR_PATH . 'woocommerce/templates/cart/cart.php';
			} elseif ( 'cart-totals.php' === basename( $template ) ) {
				$template = BNA_PLUGIN_DIR_PATH . 'woocommerce/templates/cart/cart-totals.php';
			}
			
			// checkout
			if ( 'form-billing.php' === basename( $template ) ) {
				$template = BNA_PLUGIN_DIR_PATH . 'woocommerce/templates/checkout/form-billing.php';
			} elseif ( 'form-checkout.php' === basename( $template ) ) {
				$template = BNA_PLUGIN_DIR_PATH . 'woocommerce/templates/checkout/form-checkout.php';
			} elseif ( 'form-shipping.php' === basename( $template ) ) {
				$template = BNA_PLUGIN_DIR_PATH . 'woocommerce/templates/checkout/form-shipping.php';
			} elseif ( 'payment.php' === basename( $template ) ) {
				$template = BNA_PLUGIN_DIR_PATH . 'woocommerce/templates/checkout/payment.php';
			} elseif ( 'payment-method.php' === basename( $template ) ) {
				$template = BNA_PLUGIN_DIR_PATH . 'woocommerce/templates/checkout/payment-method.php';
			} elseif ( 'review-order.php' === basename( $template ) ) {
				$template = BNA_PLUGIN_DIR_PATH . 'woocommerce/templates/checkout/review-order.php';
			} elseif ( 'thankyou.php' === basename( $template ) ) {
				$template = BNA_PLUGIN_DIR_PATH . 'woocommerce/templates/checkout/thankyou.php';
			}

			return $template;
		}

	}
}

global $BNAAccountManager, $BNAPluginManager, $BNAJsonMsgAnswer, $BNASubscriptions;
$BNAPluginManager  = new BNAPluginManager();
$BNAAccountManager = new BNAAccountManager();
$BNASubscriptions = new BNASubscriptions();

// Changed column names on woocommerce/templates/myaccount/orders.php
add_filter(
	'woocommerce_account_orders_columns',
	function( $columns ) {
		$columns['order-number']  = __( 'Order Number', 'wc-bna-gateway' );
		$columns['order-date']    = __( 'Order Placed', 'wc-bna-gateway' );
		$columns['order-status']  = __( 'Order Status', 'wc-bna-gateway' );
		$columns['order-total']   = __( 'Order Total', 'wc-bna-gateway' );
		$columns['order-actions'] = '';
		
		return $columns;
	}
);
