<?php
/**
 * The file containing the main plugin class.
 */

namespace MwaleMe\Super_Light_Store_Hours;

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

require_once __DIR__ . '/util/helpers.php';

require_once __DIR__ . '/class-super-light-store-hours-settings.php';

/**
 * The class responsible for defining all actions that occur in the admin area.
 */
require_once realpath( __DIR__ . '/../admin/class-super-light-store-hours-admin.php' );

if ( ! class_exists( 'Super_Light_Store_Hours' ) ) {

	class Super_Light_Store_Hours {

		/**
		 * An array of allowed HTML elements and attributes.
		 *
		 * @var array
		 */
		public $allowed_html = array(
			'input'    => array(
				'name'        => array(),
				'id'          => array(),
				'type'        => array(),
				'placeholder' => array(),
				'value'       => array(),
			),
			'select'   => array(
				'name'     => array(),
				'id'       => array(),
				'disabled' => array(),
			),
			'option'   => array(
				'value'    => array(),
				'selected' => array(),

			),
			'textarea' => array(
				'name'        => array(),
				'id'          => array(),
				'placeholder' => array(),
			),
			'span'     => array(
				'class' => array(),
				'style' => array(),
			),
			'p'        => array(
				'class' => array(),
				'style' => array(),
			),
			'br'       => array(),
			'em'       => array(),
			'strong'   => array(),
			'fieldset' => array(),
			'hr'       => array(),
			'code'     => array(),
			'div'      => array(
				'class' => array(),
				'style' => array(),
			),

		);

		private static $store_closed_message = '<div class="disabled_store_notice" style="margin: 15px auto; padding: 10px; background-color: #f4f498;">Store temporarily closed. Please check back later.</div>';

		private $settings;

		public function __construct() {
			// Register the function that's invoked when the plugin is activated.
			register_activation_hook( __FILE__, 'slsh_set_up_plugin' );

			$this->settings = new Super_Light_Store_Hours_Settings();
			$admin          = new Super_Light_Store_Hours_Admin();
			add_action( 'setup_theme', array( $this, 'remove_add_to_cart_buttons_conditionally' ), 10 );
			add_action( 'plugins_loaded', array( $this, 'super_light_store_hours_load_textdomain' ) );
			add_action( 'woocommerce_proceed_to_checkout', array( $this, 'print_disabled_store_notice' ) );
			add_filter( 'woocommerce_order_button_html', array( $this, 'remove_place_order_button' ) );
		}

		/**
		 * Sets up the database option where scheduling data is stored.
		 *
		 * @return void
		 */
		public function slsh_set_up_plugin() {
			// Create or upgrade the database.
		}

		/**
		 * Declares the plugin text domain and languages directory.
		 *
		 * @return void
		 */
		public function super_light_store_hours_load_textdomain() {
			$path = dirname( plugin_basename( __DIR__ ), 1 ) . '/languages/';
			load_plugin_textdomain( 'super-light-store-hours', false, $path );
		}

		public function remove_add_to_cart_buttons_conditionally() {
			$condition = $this->settings->get_slsh_condition();
			$status    = $condition['status'];
			if ( boolval( $status ) === false && is_woocommerce_activated() ) {
				// Line below removes summary AND 'add to cart' button.
				// remove_action( 'woocommerce_single_product_summary', 'woocommerce_template_single_add_to_cart', 30 );
				remove_action( 'woocommerce_simple_add_to_cart', 'woocommerce_simple_add_to_cart', 30 );
				add_action( 'woocommerce_simple_add_to_cart', array( $this, 'print_disabled_store_notice' ) );
				remove_action( 'woocommerce_grouped_add_to_cart', 'woocommerce_grouped_add_to_cart', 30 );
				add_action( 'woocommerce_grouped_add_to_cart', array( $this, 'print_disabled_store_notice' ) );
				remove_action( 'woocommerce_variable_add_to_cart', 'woocommerce_variable_add_to_cart', 30 );
				add_action( 'woocommerce_variable_add_to_cart', array( $this, 'print_disabled_store_notice' ) );
				remove_action( 'woocommerce_external_add_to_cart', 'woocommerce_external_add_to_cart', 30 );
				add_action( 'woocommerce_external_add_to_cart', array( $this, 'print_disabled_store_notice' ) );
				remove_action( 'woocommerce_single_variation', 'woocommerce_single_variation_add_to_cart_button', 20 );
				add_action( 'woocommerce_single_variation', array( $this, 'print_disabled_store_notice' ) );
				// Remove add to cart button from loop (archive page).
				remove_action( 'woocommerce_after_shop_loop_item', 'woocommerce_template_loop_add_to_cart', 10 );
				// Remove 'proceed to checkout' button.
				remove_action( 'woocommerce_proceed_to_checkout', 'woocommerce_button_proceed_to_checkout', 20 );
			}
		}

		public function return_disabled_store_notice() {
			return $this->produce_disabled_store_notice( false );
		}
		public function print_disabled_store_notice() {
			$this->produce_disabled_store_notice();
		}
		public function remove_place_order_button( $button_html ) {
			$button_html = $this->return_disabled_store_notice() ?? $button_html;
			return $button_html;
		}
		private function produce_disabled_store_notice( $print = true ) {
			$condition = $this->settings->get_slsh_condition();
			$status    = $condition['status'];
			if ( boolval( $status ) === false ) {
				if ( true === $print ) {
					echo wp_kses( self::$store_closed_message, $this->allowed_html );
				} else {
					return self::$store_closed_message;
				}
			}
		}
	}
}
