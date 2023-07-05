<?php
/**
 * The file containing the main plugin class.
 */

// Exit if accessed directly.
// if ( ! defined( 'ABSPATH' ) ) {
// exit;
// }

require_once 'class-super-light-woocommerce-hours-settings.php';

if ( ! class_exists( 'Super_Light_Woocommerce_Hours' ) ) {

	class Super_Light_Woocommerce_Hours {

		public function __construct() {
			// Register the function that's invoked when the plugin is activated.
			register_activation_hook( __FILE__, 'slwh_set_up_plugin' );

			$settings = new Super_Light_Woocommerce_Hours_Settings();
		}

		/**
		 * Sets up the database option where scheduling data is stored.
		 *
		 * @return void
		 */
		function slwh_set_up_plugin() {
			// Create or upgrade the database.
		}

	}
}
