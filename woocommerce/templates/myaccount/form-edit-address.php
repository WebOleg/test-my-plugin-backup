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

	<form class="<?php if ( empty( $payorID ) ) { echo 'form_create_payor'; } else { echo 'form_update_payor'; } ?>" >

		<h3><?php echo apply_filters( 'woocommerce_my_account_edit_address_title', $page_title, $load_address ); ?></h3><?php // @codingStandardsIgnoreLine ?>

		<div class="woocommerce-address-fields">
			<?php do_action( "woocommerce_before_edit_address_form_{$load_address}" ); ?>

			<div class="woocommerce-address-fields__field-wrapper">
				<?php
				foreach ( $address as $key => $field ) {
					// show billing_birthday
					if ( $key === 'billing_company' ) {
						$birthday = date( 'd.m.Y', strtotime( get_user_meta( $current_user_id, 'billing_birthday', true ) ) );
						if ( empty( $birthday ) ) { $birthday = ''; }
						woocommerce_form_field( 'billing_birthday', array(
							'type' => 'text',
							'id' => 'billing_birthday',
							'label' => __( 'Birthday', 'wc-bna-gateway' ),
							'placeholder' => __( 'XX.XX.XXXX', 'wc-bna-gateway' ),
							'required' => false,
							'maxlength' => 15,
							'class' => array( 'form-row', 'form-row-wide' ),
							'input_class' => array( 'input-text' ),
						), $birthday );
					}
					
					// show billing_phone_code
					if ( $key === 'billing_phone' ) {
						$billing_phone_code = get_user_meta( $current_user_id, 'billing_phone_code', true );
						if ( empty( $billing_phone_code ) ) { $billing_phone_code = ''; }
						woocommerce_form_field( 'billing_phone_code', array(
							'type' => 'text',
							'id' => 'billing_phone_code',
							'label' => __( 'Country Phone Code', 'wc-bna-gateway' ),
							'placeholder' => __( '+1', 'wc-bna-gateway' ),
							'required' => true,
							'maxlength' => 6,
							'class' => array( 'form-row', 'form-row-wide' ),
							'input_class' => array( 'input-text' ),
						), $billing_phone_code );
					}
					
					woocommerce_form_field( $key, $field, wc_get_post_data_by_key( $key, $field['value'] ) );
				}
				?>
			</div>

			<?php do_action( "woocommerce_after_edit_address_form_{$load_address}" ); ?>

			
			<?php
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
				</form>
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
	})(jQuery);	
</script>
