<?php
/**
 * My Account navigation
 *
 * This template can be overridden by copying it to yourtheme/woocommerce/myaccount/navigation.php.
 *
 * HOWEVER, on occasion WooCommerce will need to update template files and you
 * (the theme developer) will need to copy the new files to your theme to
 * maintain compatibility. We try to do this as little as possible, but it does
 * happen. When this occurs the version of the template file will be bumped and
 * the readme will list any important changes.
 *
 * @see     https://woocommerce.com/document/template-structure/
 * @package WooCommerce\Templates
 * @version 9.0.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

do_action( 'woocommerce_before_account_navigation' );
?>

<nav class="woocommerce-MyAccount-navigation" aria-label="<?php esc_html_e( 'Account pages', 'woocommerce' ); ?>">
	<div class="woo-myaccount-customer">
		<div class="woo-myaccount-customer-photo-wrapper">
			<img src="<?php echo BNA_PLUGIN_DIR_URL . 'img/woo-myaccount/pa_photo_2.png'; ?>" class="woo-myaccount-customer-photo">
		</div>
		<div class="woo-myaccount-customer-data">
			<div class="woo-myaccount-customer-data-name woo-myaccount-title"><?php echo get_user_meta( get_current_user_id(), 'billing_first_name', true ); ?></div>
			<div class="woo-myaccount-customer-data-email woo-myaccount-text-small"><?php echo wp_get_current_user()->user_email; ?></div>
			<div class="woo-myaccount-customer-data-logout woo-myaccount-text-small">
				<a href="<?php echo esc_url( wc_get_account_endpoint_url( 'customer-logout' ) ); ?>"><?php esc_html_e( 'Logout', 'wc-bna-gateway' ); ?></a>
			</div>
		</div>
	</div>
	<ul>
		<?php foreach ( wc_get_account_menu_items() as $endpoint => $label ) : ?>
			<li class="<?php echo wc_get_account_menu_item_classes( $endpoint ); ?>">
				<div class="woo-myaccount-nav-li-img-wrapper">
				<?php
					switch ( $endpoint ) {
						case "dashboard":
							echo '<img src="' . BNA_PLUGIN_DIR_URL . 'img/woo-myaccount/pa_dashboard_1.png" class="woo-myaccount-nav-li-img">';
							break;
						case "orders":
							echo '<img src="' . BNA_PLUGIN_DIR_URL . 'img/woo-myaccount/pa_account_info_1.png" class="woo-myaccount-nav-li-img">';
							break;
						case "edit-address":
							echo '<img src="' . BNA_PLUGIN_DIR_URL . 'img/woo-myaccount/pa_addresses_1.png" class="woo-myaccount-nav-li-img">';
							break;
						case "edit-account":
							echo '<img src="' . BNA_PLUGIN_DIR_URL . 'img/woo-myaccount/pa_profile_1.png" class="woo-myaccount-nav-li-img">';
							break;
						case "pl-account-management":
							echo '<img src="' . BNA_PLUGIN_DIR_URL . 'img/woo-myaccount/pa_account_info_1.png" class="woo-myaccount-nav-li-img">';
							break;
						case "pl-payment-methods":
							echo '<img src="' . BNA_PLUGIN_DIR_URL . 'img/woo-myaccount/pa_payment_methods_1.png" class="woo-myaccount-nav-li-img">';
							break;
						case "pl-transaction-info":
							echo '<img src="' . BNA_PLUGIN_DIR_URL . 'img/woo-myaccount/pa_transactions_1.png" class="woo-myaccount-nav-li-img">';
							break;
						case "pl-recurring-payments":
							echo '<img src="' . BNA_PLUGIN_DIR_URL . 'img/woo-myaccount/pa_recurring_payments_1.png" class="woo-myaccount-nav-li-img">';
							break;
						case "customer-logout":
							echo '<img src="' . BNA_PLUGIN_DIR_URL . 'img/woo-myaccount/pa_system-log-out_1.png" class="woo-myaccount-nav-li-img">';
							break;
						
						default:
							echo '<img src="' . BNA_PLUGIN_DIR_URL . 'img/woo-myaccount/pa_dashboard_1.png" class="woo-myaccount-nav-li-img">';
					}
				?>
				</div>
				<div class="woo-myaccount-nav-li-a-wrapper">
					<a href="<?php echo esc_url( wc_get_account_endpoint_url( $endpoint ) ); ?>"><?php echo esc_html( $label ); ?></a>
				</div>
			</li>
		<?php endforeach; ?>
	</ul>
</nav>

<?php do_action( 'woocommerce_after_account_navigation' ); ?>
