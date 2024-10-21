<?php
/**
 * Edit address form
 *
 * This template can be overridden by copying it to yourtheme/woocommerce/myaccount/form-edit-address.php.
 *
 * HOWEVER, on occasion WooCommerce will need to update template files and you
 * (the theme developer) will need to copy the new files to your theme to
 * maintain compatibility. We try to do this as little as possible, but it does
 * happen. When this occurs the version of the template file will be bumped and
 * the readme will list any important changes.
 *
 * @see https://woocommerce.com/document/template-structure/
 * @package WooCommerce\Templates
 * @version 7.0.1
 */

defined( 'ABSPATH' ) || exit;

$page_title = ( 'billing' === $load_address ) ? esc_html__( 'Billing address', 'woocommerce' ) : esc_html__( 'Shipping address', 'woocommerce' );

do_action( 'woocommerce_before_edit_account_address_form' ); ?>

<?php if ( ! $load_address ) : ?>
	<?php wc_get_template( 'myaccount/my-address.php' ); ?>
<?php else : ?>

	<?php
	$current_user_id = get_current_user_id();
	$payorID = get_user_meta( $current_user_id, 'payorID', true );
	?>
	
	<?php if ( 'billing' === $load_address ) { ?>
		<form class="<?php if ( empty( $payorID ) ) { echo 'form_create_payor'; } else { echo 'form_update_payor'; } ?>" >
	<?php } else { ?>
		<form method="post">
	<?php } ?>
	
		<h3><?php echo apply_filters( 'woocommerce_my_account_edit_address_title', $page_title, $load_address ); ?></h3><?php // @codingStandardsIgnoreLine ?>
		
		<?php
		if ( 'shipping' === $load_address ) {
			if ( get_user_meta( $current_user_id, 'shipping_first_name', true ) &&
				get_user_meta( $current_user_id, 'shipping_last_name', true ) &&
				get_user_meta( $current_user_id, 'shipping_company', true ) &&
				get_user_meta( $current_user_id, 'shipping_address_1', true ) &&
				get_user_meta( $current_user_id, 'shipping_address_2', true ) &&
				get_user_meta( $current_user_id, 'shipping_country', true ) &&
				get_user_meta( $current_user_id, 'shipping_state', true ) &&
				get_user_meta( $current_user_id, 'shipping_city', true ) &&
				get_user_meta( $current_user_id, 'shipping_postcode', true ) ) {
					$display = 'none';
				} else { $display = 'block'; }
			?>
			<div class="bna-address-copy" style="display: <?php echo $display; ?>">
				<button id="bna-address-copy_button" class="bna-address-copy_button"><?php _e( 'Copy', 'wc-bna-gateway' ); ?></button>
				<span  class="bna-address-copy_span"><?php _e( 'Copy shipping address from billing address', 'wc-bna-gateway' ); ?></span>
			</div>
		<?php } ?>
		
		<div class="woocommerce-address-fields">
			<?php do_action( "woocommerce_before_edit_address_form_{$load_address}" ); ?>

			<div class="woocommerce-address-fields__field-wrapper">
				<?php
				foreach ( $address as $key => $field ) {
					woocommerce_form_field( $key, $field, wc_get_post_data_by_key( $key, $field['value'] ) );
				}
				?>
			</div>

			<?php do_action( "woocommerce_after_edit_address_form_{$load_address}" ); ?>

			
			<?php
			if ( 'billing' === $load_address ) {
				if ( empty( $payorID ) ) {
					?>
					<div class="form-row form-row-wide">
						<button type="submit" class="button alt btn-margin wp-element-button" id="create_payor" 
							name="create_payor"><?php _e( 'Save Address', 'wc-bna-gateway' ); ?></button>
					</div>
					<?php
				} else {
					?>
					<div class="form-row form-row-wide">
						<button type="submit" class="button alt btn-margin  wp-element-button" id="update_payor" 
							name="update_payor"><?php _e( 'Save Address', 'wc-bna-gateway' ); ?></button>
					</div>
					<?php
				}
			} else {
				?>
				<p>
					<button type="submit" class="button<?php echo esc_attr( wc_wp_theme_get_element_class_name( 'button' ) ? ' ' . wc_wp_theme_get_element_class_name( 'button' ) : '' ); ?> wp-element-button" name="save_address" value="<?php esc_attr_e( 'Save address', 'woocommerce' ); ?>"><?php esc_html_e( 'Save address', 'woocommerce' ); ?></button>
					<?php wp_nonce_field( 'woocommerce-edit_address', 'woocommerce-edit-address-nonce' ); ?>
					<input type="hidden" name="action" value="edit_address" />
				</p>
				<?php
			}
			?>
		</div>

	</form>

<?php endif; ?>

<?php do_action( 'woocommerce_after_edit_account_address_form' ); ?>

<script type="module">
	(function($) {		
		$('#billing_birthday').datepicker({
			dateFormat: 'dd.mm.yyyy',
			autoClose: true,
		});	
		
		if ( $('#billing_phone').length > 0 ) {
			let input_phone = document.querySelector('#billing_phone');
			input_phone.addEventListener('keyup', event => {
				event.preventDefault();
				input_phone.value = input_phone.value.replace(/[^\d,]/g, "");
			}, true);
		}	
	})(jQuery);	
</script>
