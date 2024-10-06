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
		$columns['order-actions'] = '';
		
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
				--bna-button-color: <?php echo ! empty( $bna_gateway_settings['bna-button-color'] ) ? $bna_gateway_settings['bna-button-color'] : '#00A0E3'; ?>;
				--bna-button-text-color: <?php echo ! empty( $bna_gateway_settings['bna-button-text-color'] ) ? $bna_gateway_settings['bna-button-text-color'] : '#FFF'; ?>;
				--bna-line-color: <?php echo ! empty( $bna_gateway_settings['bna-line-color'] ) ? $bna_gateway_settings['bna-line-color'] : '#CCC'; ?>;
				--bna-background-color: <?php echo ! empty( $bna_gateway_settings['bna-background-color'] ) ? $bna_gateway_settings['bna-background-color'] : '#FFF'; ?>;
			}
        </style>
    <?php
	}
}


/**
* Validate checkout process
* 
* @return string
*/

add_action( 'woocommerce_checkout_process', 'bna_checkout_process_validation' );

function bna_checkout_process_validation() {

if ( ! $_POST['billing_phone_code'] ) wc_add_notice( __( '<strong>Country Phone Code</strong> is a Required Field.', 'wc-bna-gateway' ) , 'error' );
if ( ! $_POST['payment-type'] ) wc_add_notice( __( "You needs to chose payment type: 'Credit Card', 'Direct Payment' or 'e-Transfer'.", 'wc-bna-gateway' ) , 'error' );

}


