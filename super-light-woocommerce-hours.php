<?php
/**
 * Plugin Name: Woocommerce Store Hours
 * Description: Disable your store during fixed hours of the week or whenever you wish to pause orders.
 * Version: 0.0.1
 * Author: Mwale Kalenga
 * Author URI: https://mwale.me
 * License: GPLv3 or later
 */

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * The core plugin class that is used to define internationalization,
 * admin-specific hooks, and public-facing site hooks.
 */
require plugin_dir_path( __FILE__ ) . 'includes/class-super-light-woocommerce-hours.php';

/**
 * Begins execution of the plugin.
 *
 * @return void
 */
function run_super_light_woocommerce_hours() {
	$plugin = new Super_Light_Woocommerce_Hours();
}

run_super_light_woocommerce_hours();
