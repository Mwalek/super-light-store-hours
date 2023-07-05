<?php
/**
 * Extends the main plugin class.
 */

// if ( ! defined( 'ABSPATH' ) ) {
// exit;
// }

// require_once 'class-super-light-woocommerce-hours-settings.php';


class Super_Light_Woocommerce_Hours_Settings {
	public function __construct() {
		add_action( 'admin_menu', array( $this, 'slwh_add_settings_page' ) );
		add_action( 'admin_init', array( $this, 'slwh_register_settings' ) );

	}

	public function slwh_add_settings_page() {
		add_options_page( 'Woocommerce Store Hours', 'Store Hours', 'manage_options', 'sl-woocommerce-hours', array( $this, 'slwh_render_plugin_settings_page' ) );
	}

	public function slwh_render_plugin_settings_page() {
		?>
			<h2>Store Operating Hours</h2>
			<form action="options.php" method="post">
			<?php
			settings_fields( 'slwh_plugin_options' );
			do_settings_sections( 'slwh_example_plugin' );
			?>
				<input name="submit" class="button button-primary" type="submit" value="<?php esc_attr_e( 'Save' ); ?>" />
			</form>
			<?php
	}

	public function slwh_register_settings() {
		$default_options = array(
			'api_key'       => '123abc',
			'results_limit' => 'unknown',
			'start_date'    => 'today',
		);
		register_setting( 'slwh_plugin_options', 'slwh_plugin_options', array( 'default' => $default_options ), array( $this, 'slwh_plugin_options_validate' ) );
		add_settings_section( 'api_settings', 'API Settings', array( $this, 'slwh_plugin_section_text' ), 'slwh_example_plugin' );

		add_settings_field( 'slwh_plugin_setting_api_key', 'API Key', array( $this, 'slwh_plugin_setting_api_key' ), 'slwh_example_plugin', 'api_settings' );
		add_settings_field( 'slwh_plugin_setting_results_limit', 'Results Limit', array( $this, 'slwh_plugin_setting_results_limit' ), 'slwh_example_plugin', 'api_settings' );
		add_settings_field( 'slwh_plugin_setting_start_date', 'Start Date', array( $this, 'slwh_plugin_setting_start_date' ), 'slwh_example_plugin', 'api_settings' );
	}

	public function slwh_plugin_options_validate( $input ) {
		$newinput['api_key'] = trim( $input['api_key'] );
		if ( ! preg_match( '/^[a-z0-9]{32}$/i', $newinput['api_key'] ) ) {
			$newinput['api_key'] = '';
		}

		return $newinput;
	}

	public function slwh_plugin_section_text() {
		echo '<p>Here you can set all the options for using the API</p>';
	}

	public function slwh_plugin_setting_api_key() {
		$options = get_option( 'slwh_plugin_options' );
		ray( $options );
		echo "<input id='slwh_plugin_setting_api_key' name='slwh_plugin_options[api_key]' type='text' value='" . esc_attr( $options['api_key'] ) . "' />";
	}

	public function slwh_plugin_setting_results_limit() {
		$options = get_option( 'slwh_plugin_options' );
		echo "<input id='slwh_plugin_setting_results_limit' name='slwh_plugin_options[results_limit]' type='text' value='" . esc_attr( $options['results_limit'] ) . "' />";
	}

	public function slwh_plugin_setting_start_date() {
		$options = get_option( 'slwh_plugin_options' );
		echo "<input id='slwh_plugin_setting_start_date' name='slwh_plugin_options[start_date]' type='text' value='" . esc_attr( $options['start_date'] ) . "' />";
	}

}
