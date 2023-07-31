<?php
/**
 * The file containing the main plugin class.
 */

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

require_once 'util/helpers.php';

require_once plugin_dir_path( dirname( __FILE__ ) ) . '/includes/class-super-light-woocommerce-hours-settings.php';

/**
 * The class responsible for defining all actions that occur in the admin area.
 */
require_once plugin_dir_path( dirname( __FILE__ ) ) . '/admin/class-super-light-woocommerce-hours-admin.php';

if ( ! class_exists( 'Super_Light_Woocommerce_Hours' ) ) {

	class Super_Light_Woocommerce_Hours {

		public function __construct() {
			// Register the function that's invoked when the plugin is activated.
			register_activation_hook( __FILE__, 'slwh_set_up_plugin' );

			$settings = new Super_Light_Woocommerce_Hours_Settings();
			$admin    = new Super_Light_Woocommerce_Hours_Admin();
			add_action( 'wp', array( $this, 'remove_add_to_cart_buttons_conditionally' ) );
			add_action( 'plugins_loaded', array( $this, 'super_light_woocommerce_hours_load_textdomain' ) );
		}

		/**
		 * Sets up the database option where scheduling data is stored.
		 *
		 * @return void
		 */
		function slwh_set_up_plugin() {
			// Create or upgrade the database.
		}

		/**
		 * Declares the plugin text domain and languages directory.
		 *
		 * @return void
		 */
		public function super_light_woocommerce_hours_load_textdomain() {
			$path = dirname( plugin_basename( __DIR__ ), 1 ) . '/languages/';
			load_plugin_textdomain( 'super-light-woocommerce-hours', false, $path );
		}

		public function remove_add_to_cart_buttons_conditionally() {
			$settings  = new Super_Light_Woocommerce_Hours_Settings();
			$condition = $settings->get_slwh_condition();
			$status    = $condition['status'];
			if ( boolval( $status ) === false && is_woocommerce_activated() ) {
				ray( 'Woocommerce is active' )->purple();
				remove_action( 'woocommerce_single_product_summary', 'woocommerce_template_single_add_to_cart', 30 );
				remove_action( 'woocommerce_simple_add_to_cart', 'woocommerce_simple_add_to_cart', 30 );
				remove_action( 'woocommerce_grouped_add_to_cart', 'woocommerce_grouped_add_to_cart', 30 );
				remove_action( 'woocommerce_variable_add_to_cart', 'woocommerce_variable_add_to_cart', 30 );
				remove_action( 'woocommerce_external_add_to_cart', 'woocommerce_external_add_to_cart', 30 );
				// remove_action( 'woocommerce_single_variation', 'woocommerce_single_variation', 10 );
				remove_action( 'woocommerce_single_variation', 'woocommerce_single_variation_add_to_cart_button', 20 );

				// Remove add to cart button from loop (archive page).
				remove_action( 'woocommerce_after_shop_loop_item', 'woocommerce_template_loop_add_to_cart', 10 );
			}
		}

	}
}
