<?php
/**
 * Helper functions
 *
 * This file contains helper functions used throughout the plugin.
 *
 * @link https://github.com/Mwalek/super-light-store-hours
 *
 * @package    WordPress
 * @subpackage Plugins
 * @since      1.0.0
 */

namespace MwaleMe\Super_Light_Store_Hours;

/**
 * Checks if WooCommerce is activated
 *
 * @return boolean
 */
function is_woocommerce_activated() {
	if ( class_exists( 'woocommerce' ) ) {
		return true;
	} else {
		return false; }
}
