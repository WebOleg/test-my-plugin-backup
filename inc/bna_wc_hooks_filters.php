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
			if ( empty( $billing_phone_code ) ) { $billing_phone_code = ''; }
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
	
	//if( is_wc_endpoint_url( 'bna-e-transfer-info' ) ){
		//if ( ! empty( $bna_gateway_settings['bna-payment-method-e-transfer'] ) && $bna_gateway_settings['bna-payment-method-e-transfer'] === 'yes' && in_array( $woo_currency, BNA_E_TRANSFER_ALLOWED_CURRENCY ) ) {
			//// Ok
		//} else {		
			//wp_safe_redirect( $bna_payment_methods );
			//exit;
		//}
	//}	
} );
