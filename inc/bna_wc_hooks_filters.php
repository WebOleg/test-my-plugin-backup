<?php
/**
 * Hooks and Filters
 *
 * WooCommerce Hooks and Filters for the plugin.
 *
 * @package WC-BNA-Gateway
 * @version 1.0
 */

defined( 'ABSPATH' ) || exit;

/**
 * Filter templates path to use in this plugin instead of the one in WooCommerce.
 *
 * @param string $template      Default template file path.
 * @param string $template_name Template file slug.
 * @param string $template_path Template file name.
 *
 * @return string The new Template file path.
 */
add_filter( 'woocommerce_locate_template', 'bna_wc_template', 10, 3 );

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
	} elseif ( 'form-edit-address.php' === basename( $template ) ) {
		$template = BNA_PLUGIN_DIR_PATH . 'woocommerce/templates/myaccount/form-edit-address.php';
	} elseif ( 'form-edit-account.php' === basename( $template ) ) {
		$template = BNA_PLUGIN_DIR_PATH . 'woocommerce/templates/myaccount/form-edit-account.php';
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
	} elseif ( 'form-pay.php' === basename( $template ) ) {
		$template = BNA_PLUGIN_DIR_PATH . 'woocommerce/templates/checkout/form-pay.php';
	}
	
	// order
	if ( 'order-details.php' === basename( $template ) ) {
		$template = BNA_PLUGIN_DIR_PATH . 'woocommerce/templates/order/order-details.php';
	}

	return $template;
}


/**
 * Changed column names on woocommerce/templates/myaccount/orders.php
 */ 
add_filter(
	'woocommerce_account_orders_columns',
	function( $columns ) {
		$columns['order-number']  = __( 'Order', 'wc-bna-gateway' );
		$columns['order-date']    = __( 'Order Placed', 'wc-bna-gateway' );
		$columns['order-status']  = __( 'Order Status', 'wc-bna-gateway' );
		$columns['order-total']   = __( 'Order Total', 'wc-bna-gateway' );
		$columns['order-actions'] = __( 'Manage', 'wc-bna-gateway' );
		
		return $columns;
	}
);

/**
 * Filter of orders on the 'my_account' page, '	Transaction info '.
 * 
 * @param  $array 
 * @return $array
 */
add_filter( 'woocommerce_my_account_my_orders_query', 'bna_my_account_orders', 10, 1 );

function bna_my_account_orders( $args ) {
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
		
		$args['date_created'] = '>=' . $start_date;
    }
 
	return $args;
}

/**
 * Change the entry title of the endpoints that appear in My Account Page
 * 
 * @param  $array 
 * @return $array
 */
add_filter( 'woocommerce_account_menu_items', 'bna_wc_menu_items', 99, 1 );

function bna_wc_menu_items( $items ) {
    $items['edit-address'] = __( 'Account info', 'wc-bna-gateway' );
    $items['edit-account'] = __( 'Security settings', 'wc-bna-gateway' );

    return $items;
}

/**
 * Adding custom styles
 * 
 * @return css styles
 */
add_action( 'wp_head', 'bna_variable_css' );

function bna_variable_css() {
	$bna_gateway_settings = get_option( 'woocommerce_bna_gateway_settings' );
	if ( ! empty( $bna_gateway_settings ) ) {
    ?>
        <style>
            :root :where(body) {
				--bna-font-color: <?php echo ! empty( $bna_gateway_settings['bna-font-color'] ) ? $bna_gateway_settings['bna-font-color'] : '#646464'; ?>;
				--bna-button-background-color: <?php echo ! empty( $bna_gateway_settings['bna-button-background-color'] ) ? $bna_gateway_settings['bna-button-background-color'] : '#00A0E3'; ?>;
				--bna-button-text-color: <?php echo ! empty( $bna_gateway_settings['bna-button-text-color'] ) ? $bna_gateway_settings['bna-button-text-color'] : '#FFF'; ?>;
				--bna-line-color: <?php echo ! empty( $bna_gateway_settings['bna-line-color'] ) ? $bna_gateway_settings['bna-line-color'] : '#CCC'; ?>;
				--bna-input-background-color: <?php echo ! empty( $bna_gateway_settings['bna-input-background-color'] ) ? $bna_gateway_settings['bna-input-background-color'] : '#FFF'; ?>;
				--bna-svg-first-color: <?php echo ! empty( $bna_gateway_settings['bna-svg-first-color'] ) ? $bna_gateway_settings['bna-svg-first-color'] : '#00A0E3'; ?>;
				--bna-svg-last-color: <?php echo ! empty( $bna_gateway_settings['bna-svg-last-color'] ) ? $bna_gateway_settings['bna-svg-last-color'] : '#B0CB1F'; ?>;
			}
        </style>
    <?php
	}
}


/**
* Validate checkout process
*/
add_action( 'woocommerce_checkout_process', 'bna_checkout_process_validation' );

function bna_checkout_process_validation() {
	if ( ! $_POST['billing_phone_code'] ) wc_add_notice( __( '<strong>Country Phone Code</strong> is a Required Field.', 'wc-bna-gateway' ) , 'error' );
	if ( ! $_POST['payment-type'] ) wc_add_notice( __( "You needs to chose payment type: 'Credit Card', 'Bank Transfer' or 'E-Transfer'.", 'wc-bna-gateway' ) , 'error' );

	if ( ! empty( $_POST['payment-type'] ) && $_POST['payment-type'] === 'card' ) {
		if ( ! isset( $_POST['paymentMethodCC'] ) || $_POST['paymentMethodCC'] === 'new-card' ) {
			if ( ! $_POST['cc_holder'] ) wc_add_notice( __( '<strong>Cardholder Name</strong> is a Required Field.', 'wc-bna-gateway' ) , 'error' );
			if ( ! $_POST['cc_number'] ) wc_add_notice( __( '<strong>Card Number</strong> is a Required Field.', 'wc-bna-gateway' ) , 'error' );
			if ( ! $_POST['cc_expire'] ) wc_add_notice( __( '<strong>Expiry Date</strong> is a Required Field.', 'wc-bna-gateway' ) , 'error' );
			if ( ! $_POST['cc_code'] ) wc_add_notice( __( '<strong>CVC</strong> is a Required Field.', 'wc-bna-gateway' ) , 'error' );
		} 
	} elseif ( ! empty( $_POST['payment-type'] ) && $_POST['payment-type'] === 'eft' ) {
		if ( ! isset( $_POST['paymentMethodDD'] ) || $_POST['paymentMethodDD'] === 'new-method' ) {
			if ( ! $_POST['bank_number'] ) wc_add_notice( __( '<strong>Institution Number</strong> is a Required Field.', 'wc-bna-gateway' ) , 'error' );
			if ( ! $_POST['account_number'] ) wc_add_notice( __( '<strong>Account Number</strong> is a Required Field.', 'wc-bna-gateway' ) , 'error' );
			if ( ! $_POST['transit_number'] ) wc_add_notice( __( '<strong>Transit Number</strong> is a Required Field.', 'wc-bna-gateway' ) , 'error' );
		} 
	} elseif ( ! empty( $_POST['payment-type'] ) && $_POST['payment-type'] === 'e-transfer' ) {
		if ( ! $_POST['email_transfer'] ) wc_add_notice( __( '<strong>Interac Email</strong> is a Required Field.', 'wc-bna-gateway' ) , 'error' );
	}
	
	if ( isset( $_POST['i-agree'] ) && $_POST['i-agree'] !== 'i-agree-on' ) wc_add_notice( __( '<strong>Recurring Payment Agreement</strong> is a Required Checkbox.', 'wc-bna-gateway' ) , 'error' );
	
}

/**
* Removing symbols from phone number in the profile.php file
* 
* @param  $int
*/
add_action( 'profile_update', 'bna_save_details', 10, 1 );
add_action( 'user_register', 'bna_save_details', 10, 1 );

function bna_save_details( $user_id ) {
	if ( isset( $_POST['billing_phone'] ) ) {
		$billing_phone = str_replace( '+', '', $_POST['billing_phone'] );	
		update_user_meta( $user_id, 'billing_phone', sanitize_text_field( $billing_phone ) );
	}
}

/**
 * Added the 'Country Phone Code' field to profile.php
 * 
 * @param  $array
 * @return $array
 */
add_filter( 'woocommerce_customer_meta_fields', 'bna_customer_meta_fields', 10, 1 );

function bna_customer_meta_fields( $array ) {
	$new_billing_fields = array();
	foreach ( $array['billing']['fields'] as $key => $value ) {
		if ( $key === 'billing_phone' ) {
			$new_billing_fields['billing_phone_code'] = array(
				'label'       => __( 'Country Phone Code', 'woocommerce' ),
				'description' => '',
			);
		}
		$new_billing_fields[$key] = $value;
	}
	$array['billing']['fields'] = $new_billing_fields;
	return $array;
}

/**
 * Added additional fields to form-edit-address.php
 * 
 * @param  $array
 * @param  $string
 * @return $array
 */
add_filter( 'woocommerce_address_to_edit', 'bna_address_to_edit', 10, 2 );

function bna_address_to_edit( $address, $load_address ) {
	$new_address = array();
	foreach ( $address as $key => $value ) {
		
		if ( $key === 'billing_address_1' || $key === 'billing_address_2' ) {
			continue;
		}
		
		if ( $key === 'billing_company' ) {
			$birthday = date( 'd.m.Y', strtotime( get_user_meta( get_current_user_id(), 'billing_birthday', true ) ) );
			if ( empty( $birthday ) ) { $birthday = ''; }
			$new_address['billing_birthday'] = array(
				'label' => __( 'Birthday', 'wc-bna-gateway' ),
				'placeholder' => __( 'XX.XX.XXXX', 'wc-bna-gateway' ),
				'required' => false,
				'maxlength' => 15,
				'class' => array( 'form-row', 'form-row-wide' ),
				'input_class' => array( 'input-text' ),
				'value' => $birthday,
			);
		}
		
		if ( $key === 'billing_phone' ) {
			$billing_phone_code = get_user_meta( get_current_user_id(), 'billing_phone_code', true );
			if ( empty( $billing_phone_code ) ) { $billing_phone_code = '+1'; }
			$new_address['billing_phone_code'] = array(
				'label' => __( 'Country Phone Code', 'wc-bna-gateway' ),
				'placeholder' => __( '+1', 'wc-bna-gateway' ),
				'required' => true,
				'maxlength' => 6,
				'class' => array( 'form-row', 'form-row-wide' ),
				'input_class' => array( 'input-text' ),
				'value' => $billing_phone_code,
			);
		}
		
		if ( $key === 'billing_postcode' ) {
			$billing_street_name = get_user_meta( get_current_user_id(), 'billing_street_name', true );
			if ( empty( $billing_street_name ) ) { $billing_street_name = ''; }
			$new_address['billing_street_name'] = array(
				'type' => 'text', 
				'label' => __('Street name', 'wc-bna-gateway'),
				'required' => true,
				'class' => array('form-row-wide'),
				'value' => $billing_street_name
			);
			
			$billing_street_number = get_user_meta( get_current_user_id(), 'billing_street_number', true );
			if ( empty( $billing_street_number ) ) { $billing_street_number = ''; }
			$new_address['billing_street_number'] = array(
				'type' => 'text', 
				'label' => __('Street number', 'wc-bna-gateway'),
				'required' => true,
				'class' => array('form-row-wide'),
				'value' => $billing_street_number
			);
			
			$billing_apartment = get_user_meta( get_current_user_id(), 'billing_apartment', true );
			if ( empty( $billing_apartment ) ) { $billing_apartment = ''; }
			$new_address['billing_apartment'] = array(
				'type' => 'text', 
				'label' => __('Apartment', 'wc-bna-gateway'),
				'required' => false,
				'class' => array('form-row-wide'),
				'value' => $billing_apartment
			);
		}
		
		$new_address[$key] = $value;
	}
	
	//$new_address['billing_email']['priority'] = 32;
	//$new_address['billing_phone_code']['priority'] = 34;
	//$new_address['billing_phone']['priority'] = 36;
	//$new_address['billing_apartment']['priority'] = 42;
	//$new_address['billing_street_number']['priority'] = 44;
	//$new_address['billing_street_name']['priority'] = 46;		
	//$new_address['billing_city']['priority'] = 72;
	//$new_address['billing_state']['priority'] = 74;
	//$new_address['billing_country']['priority'] = 76;
	//$new_address['billing_postcode']['priority'] = 88;
	
	return $new_address;
}

/**
 * Template redirects if payment method not allowed
 * 
 * @return redirect
 */
add_action( 'template_redirect', function() {
	$bna_gateway_settings = get_option( 'woocommerce_bna_gateway_settings' );
	$woo_currency = get_woocommerce_currency();
	$bna_payment_methods = esc_url( wc_get_endpoint_url( 'bna-payment-methods' ) );
	
	if( is_wc_endpoint_url( 'bna-add-credit-card' ) ){
		if ( ! empty( $bna_gateway_settings['bna-payment-method-card'] ) && $bna_gateway_settings['bna-payment-method-card'] === 'yes' && in_array( $woo_currency, BNA_CARD_ALLOWED_CURRENCY ) ) {
			// Ok
		} else {		
			wp_safe_redirect( $bna_payment_methods );
			exit;
		}
	}
	
	if( is_wc_endpoint_url( 'bna-bank-account-info' ) ){
		if ( ! empty( $bna_gateway_settings['bna-payment-method-eft'] ) && $bna_gateway_settings['bna-payment-method-eft'] === 'yes' && in_array( $woo_currency, BNA_EFT_ALLOWED_CURRENCY ) ) {
			// Ok
		} else {		
			wp_safe_redirect( $bna_payment_methods );
			exit;
		}
	}
} );

/**
 * Default checkout billing state
 * 
 * @return null
 */
add_filter( 'default_checkout_billing_state', '__return_null' );

/**
 * Show subscription order details
 * 
 * @return string
 */
add_action( 'woocommerce_after_order_details', function( $order ) {
	$info = $order->get_meta( 'bna_subscription_order_info' );
	if ( ! empty( $info ) ) {
		?>
		<header><h4><?php _e( 'Recurring Details', 'wc-bna-gateway' ); ?></h4></header>
		<table class="shop_table shop_table_responsive customer_details">
			<tbody>
				<?php if ( ! empty( $info['recurrence'] ) ) { ?>
					<tr>
						<th><?php _e( 'Recurrence:', 'wc-bna-gateway' ); ?></th>
						<td data-title="<?php _e( 'Recurrence', 'wc-bna-gateway' ); ?>"><?php echo $info['recurrence']; ?></td>
					</tr>
				<?php } ?>
				<?php if ( ! empty( $info['startPaymentDate'] ) ) { ?>
					<tr>
						<th><?php _e( 'Start payment date:', 'wc-bna-gateway' ); ?></th>
						<td data-title="<?php _e( 'Start payment date', 'wc-bna-gateway' ); ?>"><?php echo date( 'Y-m-d H:i:s', strtotime( $info['startPaymentDate'] ) ); ?></td>
					</tr>
				<?php } ?>
				<?php if ( ! empty( $info['nextPaymentDate'] ) ) { ?>
					<tr>
						<th><?php _e( 'Next payment date:', 'wc-bna-gateway' ); ?></th>
						<td data-title="<?php _e( 'Next payment date', 'wc-bna-gateway' ); ?>"><?php echo date( 'Y-m-d H:i:s', strtotime( $info['nextPaymentDate'] ) ); ?></td>
					</tr>
				<?php } ?>
				<?php if ( ! empty( $info['lastPaymentDate'] ) ) { ?>
					<tr>
						<th><?php _e( 'Last payment date:', 'wc-bna-gateway' ); ?></th>
						<td data-title="<?php _e( 'Last payment date', 'wc-bna-gateway' ); ?>"><?php echo date( 'Y-m-d H:i:s', strtotime( $info['lastPaymentDate'] ) ); ?></td>
					</tr>
				<?php } ?>
				<?php if ( ! empty( $info['remainingPayments'] ) ) { ?>
					<tr>
						<th><?php _e( 'Remaining payments:', 'wc-bna-gateway' ); ?></th>
						<td data-title="<?php _e( 'Remaining payments', 'wc-bna-gateway' ); ?>"><?php echo $info['remainingPayments']; ?></td>
					</tr>
				<?php } ?>
			</tbody>
		</table>
		<?php
	}
}, 10, 1 );

/**
 * Cancel transaction in the portal if the order canceled
 * 
 * @param int $order_id
 */
add_action( 'woocommerce_order_status_cancelled', function( $order_id ) {
	global $wpdb;
			
	$order = wc_get_order( $order_id );
	if ( ! $order || $order->get_status() !== 'pending' ) {
		return;
	}
	
	$check_transaction_id =  $wpdb->get_results(
		"SELECT * FROM " . $wpdb->prefix . BNA_TABLE_TRANSACTIONS . " WHERE order_id='{$order_id}'"
	);
	
	if ( ! empty( $check_transaction_id[0]->transactionToken ) ) {
		$transactionToken = $check_transaction_id[0]->transactionToken;	
		$desc = json_decode( $check_transaction_id[0]->transactionDescription );
		$paymentMethod = strtolower( $desc->paymentMethod );
		
		if ( $paymentMethod === 'eft' || $paymentMethod === 'e-transfer' ) {
			$args = WC_BNA_Gateway::get_merchant_params();
			if ( empty( $args ) ) {
				BNAJsonMsgAnswer::send_json_answer( BNA_MSG_ERRORPARAMS );
				return false;
			}

			$api = new BNAExchanger( $args );
			
			$data = array();
			$data['transactionTime'] = date('Y-m-d\TH:i:sO');
			$data['referenceUUID']	= $transactionToken;
			$data['transactionComment']	= __( 'Manually canceled the transaction from a woocommerce page', 'wc-bna-gateway' );
			$data['metadata']	= array(
				'invoiceId' => $order_id,				
			);
		
			$response = $api->query(
				$args['serverUrl'] . '/' . $args['protocol'] . '/transaction/' . $paymentMethod . '/cancel',  
				$data, 
				'POST'
			);
		}
	}
} );
