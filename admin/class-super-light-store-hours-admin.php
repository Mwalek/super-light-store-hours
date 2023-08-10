<?php
/**
 * The admin facing side of the plugin.
 */


// Exit if file is accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( ! class_exists( 'Super_Light_Store_Hours_Admin' ) ) {
	class Super_Light_Store_Hours_Admin {
		public function __construct() {
			add_action( 'admin_enqueue_scripts', array( $this, 'load_slwh_scripts' ) );
			add_filter( 'admin_body_class', array( $this, 'add_slwh_body_class' ) );
		}

		public function load_slwh_scripts() {
			$screen = get_current_screen();
			if ( 'settings_page_sl-woocommerce-hours' === $screen->base ) {
				wp_enqueue_style( 'slwh_admin_style', plugins_url( '../assets/css/slwh_admin_style.css', ( __FILE__ ) ), false, '1.0.0', 'all' );
			}
		}

		/**
		 * Adds one or more classes to the body tag in the dashboard.
		 *
		 * @link https://developer.wordpress.org/reference/hooks/admin_body_class/
		 * @param  String $classes Current body classes.
		 * @return String          Altered body classes.
		 */
		public function add_slwh_body_class( $classes ) {
			$screen = get_current_screen();
			if ( 'settings_page_sl-woocommerce-hours' === $screen->base ) {
				$classes .= 'slwh_admin_wrapper';
			}
			return $classes;
		}
	}
}
