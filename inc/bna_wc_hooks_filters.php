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


/**
 * Changed column names on woocommerce/templates/myaccount/orders.php
 */ 
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
