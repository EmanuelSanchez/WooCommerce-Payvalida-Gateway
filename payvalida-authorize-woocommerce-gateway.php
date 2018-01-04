<?php
/*
Plugin Name: WooCommerce Payvalida Gateway
Plugin URI: https://es.wordpress.org/plugins/woo-payvalida-gateway/
Description: Pague con tarjeta de crédito utilizando Payvalida (Solo Colombia)
Version: 1.0
Author: Valida SAS
Author URI: https://payvalida.com/
*/


add_action( 'plugins_loaded', 'wc_payvalida_init', 0 );

function wc_payvalida_init() {
    //if condition use to do nothin while WooCommerce is not installed
	if ( ! class_exists( 'WC_Payment_Gateway' ) ) return;
	include_once( 'payvalida-authorize-woocommerce.php' );
	// class add it too WooCommerce
	add_filter( 'woocommerce_payment_gateways', 'add_payvalida_gateway' );
	function add_payvalida_gateway( $methods ) {
		$methods[] = 'wc_PayValida';
		return $methods;
	}
}
// Add custom action links
add_filter( 'plugin_action_links_' . plugin_basename( __FILE__ ), 'wc_payvalida_action_links' );
function wc_payvalida_action_links( $links ) {
	$plugin_links = array(
		'<a href="' . admin_url( 'admin.php?page=wc-settings&tab=checkout' ) . '">' . __( 'Settings', 'wc_payvalida' ) . '</a>',
	);
	return array_merge( $plugin_links, $links );
}
