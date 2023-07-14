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
			<div class="wrap">
				<div class="main_content">
					<h2>Store Operating Hours</h2>
					<form action="options.php" method="post">
					<?php
					settings_fields( 'slwh_plugin_options' );
					do_settings_sections( 'slwh_example_plugin' );
					?>
						<input name="submit" class="button button-primary" type="submit" value="<?php esc_attr_e( 'Save' ); ?>" />
					</form>
				</div>
			</div>
			<?php
	}

	public function slwh_register_settings() {
		$default_options = array(
			'working_days'  => array( 'working_days' => array( 'Wednesday' ) ),
			'results_limit' => 'unknown',
			'status'        => 'today',
		);
		register_setting( 'slwh_plugin_options', 'slwh_plugin_options', array( 'default' => $default_options ), array( $this, 'slwh_plugin_options_validate' ) );
		add_settings_section( 'api_settings', 'Schedule Settings', array( $this, 'slwh_plugin_section_text' ), 'slwh_example_plugin' );

		add_settings_field( 'slwh_plugin_setting_api_key', 'Working Days', array( $this, 'slwh_plugin_setting_api_key' ), 'slwh_example_plugin', 'api_settings' );
		add_settings_field( 'slwh_plugin_setting_results_limit', 'Opening & Closing Time', array( $this, 'slwh_plugin_setting_results_limit' ), 'slwh_example_plugin', 'api_settings' );
		add_settings_field( 'slwh_plugin_setting_status', 'Enable/Disable Store', array( $this, 'slwh_plugin_setting_status' ), 'slwh_example_plugin', 'api_settings' );
	}

	public function slwh_plugin_options_validate( $input ) {
		$newinput['api_key'] = trim( $input['api_key'] );
		if ( ! preg_match( '/^[a-z0-9]{32}$/i', $newinput['api_key'] ) ) {
			$newinput['api_key'] = '';
		}

		return $newinput;
	}

	public function slwh_plugin_section_text() {
		echo '<p>Here you can set all the options regarding when you want to accept orders.</p>';
	}

	public function slwh_plugin_setting_api_key() {
		$options           = get_option( 'slwh_plugin_options', array() );
		$slwh_working_days = isset( $options['working_days'] )
		? (array) $options['working_days'] : array();
		ray( $options, $slwh_working_days );
		// $html = "
		?>
		<input type='checkbox' name='slwh_plugin_options[working_days][]' id='sunday' <?php checked( in_array( 'Sunday', $slwh_working_days ), 1 ); ?> value='Sunday'>
		<label for='sunday'> Sunday</label><br>
		<input type='checkbox' name='slwh_plugin_options[working_days][]' id='monday' <?php checked( in_array( 'Monday', $slwh_working_days ), 1 ); ?> value='Monday'>
		<label for='monday'> Monday</label><br>
		<input type='checkbox' name='slwh_plugin_options[working_days][]' id='tuesday' <?php checked( in_array( 'Tuesday', $slwh_working_days ), 1 ); ?> value='Tuesday'>
		<label for='tuesday'> Tuesday</label><br>
		<input type='checkbox' name='slwh_plugin_options[working_days][]' id='wednesday' <?php checked( in_array( 'Wednesday', $slwh_working_days ), 1 ); ?> value='Wednesday'>
		<label for='wednesday'> Wednesday</label><br>
		<input type='checkbox' name='slwh_plugin_options[working_days][]' id='thursday' <?php checked( in_array( 'Thursday', $slwh_working_days ), 1 ); ?> value='Thursday'>
		<label for='thursday'> Thursday</label><br>
		<input type='checkbox' name='slwh_plugin_options[working_days][]' id='friday' <?php checked( in_array( 'Friday', $slwh_working_days ), 1 ); ?> value='Friday'>
		<label for='friday'> Friday</label><br>
		<input type='checkbox' name='slwh_plugin_options[working_days][]' id='saturday' <?php checked( in_array( 'Saturday', $slwh_working_days ), 1 ); ?> value='Saturday'>
		<label for='saturday'> Saturday</label><br>
		<?php
		// ";
		// echo "<input id='slwh_plugin_setting_api_key' name='slwh_plugin_options[api_key]' type='text' value='" . esc_attr( $options['api_key'] ) . "' />";
		// echo $html;
	}

	public function slwh_plugin_setting_results_limit() {
		$default_options = array(
			'working_days'  => array( 'working_days' => array( 'Wednesday' ) ),
			'results_limit' => 'unknown',
			'status'        => 'today',
		);
		// update_option( 'slwh_plugin_options', $default_options );
		$options = get_option( 'slwh_plugin_options' );
		?>
		<input id='slwh_plugin_setting_results_limit' name='slwh_plugin_options[results_limit]' type='text' value=' <?php esc_attr( $options['results_limit'] ); ?>' />
		<span class="block_description">Use the format <strong>HH - HH</strong>, for example, <code>08 - 18</code>. Hours are only supported in the 24H format and minutes are not allowed.</span>

		<?php
	}

	public function slwh_plugin_setting_status() {
		$default_options = array(
			'working_days'  => array( 'working_days' => array( 'Wednesday' ) ),
			'results_limit' => 'unknown',
			'status'        => 'today',
		);
		$options         = get_option( 'slwh_plugin_options' );
		// $update          = update_option( 'slwh_plugin_options', $default_options );
		// $slwh_status = $options['status'];
		$checked = 1;
		$current = isset( $options['status'] )
		? $options['status'] : '0';
		ray( $options, $current )->purple();
		$value = $current ? 'true' : 'false';
		// The value to compare with (the value of the checkbox below).
		$current = 1;
		// True by default, just here to make things clear.
		$display = true;
		?>
		<label class="switch">
		<input type='checkbox' name='slwh_plugin_options[status]' id='status' <?php checked( $checked, $current, $display ); ?> value='1' >

			<span class="slider round"></span>
		</label>
		<br><span class="block_description">This option <strong>overrides</strong> other scheduling options.</span>
		<?php
	}

}
