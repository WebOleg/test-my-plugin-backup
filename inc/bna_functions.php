<?php
/**
 * Custom Functions
 *
 * Functions for the plugin.
 *
 * @package WC-BNA-Gateway
 * @version 1.0
 */

defined( 'ABSPATH' ) || exit;

/**
 * Return a css class for the active link.
 * 
 * @param $string Parameter from the GET request
 * @return $string
 */
function bna_add_class_active( $param ) {
	if ( isset( $_GET['bna-orders-filter'] ) && ! empty( $_GET['bna-orders-filter'] ) ) {
		if ( $_GET['bna-orders-filter'] === $param ) return ' filter-active';
	}
}
