<?php
/**
 * My Account Dashboard
 *
 * Shows the first intro screen on the account dashboard.
 *
 * This template can be overridden by copying it to yourtheme/woocommerce/myaccount/dashboard.php.
 *
 * HOWEVER, on occasion WooCommerce will need to update template files and you
 * (the theme developer) will need to copy the new files to your theme to
 * maintain compatibility. We try to do this as little as possible, but it does
 * happen. When this occurs the version of the template file will be bumped and
 * the readme will list any important changes.
 *
 * @see     https://woocommerce.com/document/template-structure/
 * @package WooCommerce\Templates
 * @version 4.4.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

$allowed_html = array(
	'a' => array(
		'href' => array(),
	),
);
?>
<div class="woo-myaccount-dashboard-title"><?php esc_html_e( 'Welcome', 'wc-bna-gateway' ); ?></div>
<?php $user_name = esc_html( get_user_meta( get_current_user_id(), 'billing_first_name', true ) ); ?>
<p class="woo-myaccount-dashboard-subtitle woo-myaccount-text-medium"><?php esc_html_e( 'Hi ', 'wc-bna-gateway' ); ?><span><?php if ( ! empty( $user_name ) ) echo ', ' . $user_name; ?></span>!</p>

<p class="woo-myaccount-dashboard-text woo-myaccount-text-medium">
	<?php esc_html_e( 'Here You can check your last orders and payment transactions, setup or delete recurring payments, change your personal settings and preferences.', 'wc-bna-gateway' ); ?>
</p>


<div class="woo-myaccount-dashboard-content">
	<?php foreach ( wc_get_account_menu_items() as $endpoint => $label ) : ?>
		<?php if ( $endpoint === "dashboard" || $endpoint === "customer-logout" ) { continue; } ?>
		<div class="woo-myaccount-dashboard-item">	
			<div class="woo-myaccount-dashboard-item-img-wrapp">
				<?php
					switch ( $endpoint ) {
						case "orders":
							echo '<img src="' . BNA_PLUGIN_DIR_URL . 'img/woo-myaccount/pa_my_orders_2.png">';	
							break;
						case "edit-address":
							echo '<img src="' . BNA_PLUGIN_DIR_URL . 'img/woo-myaccount/pa_addresses_2.png">';	
							break;
						case "edit-account":
							echo '<img src="' . BNA_PLUGIN_DIR_URL . 'img/woo-myaccount/pa_profile_2.png">';	
							break;
						case "pl-account-management":
							echo '<img src="' . BNA_PLUGIN_DIR_URL . 'img/woo-myaccount/pa_account_info_2.png">';	
							break;
						case "pl-payment-methods":
							echo '<img src="' . BNA_PLUGIN_DIR_URL . 'img/woo-myaccount/pa_payment_methods_2.png">';	
							break;
						case "pl-transaction-info":
							echo '<img src="' . BNA_PLUGIN_DIR_URL . 'img/woo-myaccount/pa_transactions_2.png">';	
							break;
						case "pl-recurring-payments":
							echo '<img src="' . BNA_PLUGIN_DIR_URL . 'img/woo-myaccount/pa_recurring_payments_2.png">';	
							break;
						
						default:
							echo '<img src="' . BNA_PLUGIN_DIR_URL . 'img/woo-myaccount/pa_account_info_2.png">';	
					}
				?>
			</div>
			<div class="woo-myaccount-dashboard-item-link">			
				<a href="<?php echo esc_url( wc_get_account_endpoint_url( $endpoint ) ); ?>"><?php echo esc_html( $label ); ?></a>
			</div>
		</div>
	<?php endforeach; ?>
	
</div>

<?php
	/**
	 * My Account dashboard.
	 *
	 * @since 2.6.0
	 */
	do_action( 'woocommerce_account_dashboard' );

	/**
	 * Deprecated woocommerce_before_my_account action.
	 *
	 * @deprecated 2.6.0
	 */
	do_action( 'woocommerce_before_my_account' );

	/**
	 * Deprecated woocommerce_after_my_account action.
	 *
	 * @deprecated 2.6.0
	 */
	do_action( 'woocommerce_after_my_account' );

/* Omit closing PHP tag at the end of PHP files to avoid "headers already sent" issues. */
