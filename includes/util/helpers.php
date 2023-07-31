<?php
/**
 * Helper functions
 *
 * This file contains helper functions used throughout the plugin.
 *
 * @link https://github.com/Mwalek/super-light-woocommerce-hours
 *
 * @package    WordPress
 * @subpackage Plugins
 * @since      1.0.0
 */

/**
 * Check if WooCommerce is activated
 */
if ( ! function_exists( 'is_woocommerce_activated' ) ) {
	function is_woocommerce_activated() {
		if ( class_exists( 'woocommerce' ) ) {
			return true;
		} else {
			return false; }
	}
}
