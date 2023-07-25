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
		add_action( 'rest_api_init', array( $this, 'slwh_register_settings' ) );
		add_action( 'init', array( $this, 'get_slwh_status' ) );
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
					settings_fields( 'sl-woocommerce-hours' );
					do_settings_sections( 'sl_woocommerce_hours' );
					?>
						<input name="submit" class="button button-primary" type="submit" value="<?php esc_attr_e( 'Save' ); ?>" />
					</form>
				</div>
			</div>
			<?php
	}

	public function slwh_register_settings() {
		$default_options = array(
			'working_days'         => array(),
			'opening_closing_time' => '',
			'status'               => '0',
		);
		register_setting(
			'sl-woocommerce-hours',
			'slwh_plugin_options',
			array(
				'default'           => $default_options,
				'type'              => 'object',
				'sanitize_callback' => array(
					$this,
					'slwh_plugin_options_validate',
				),
				'show_in_rest'      => array(
					'schema' => array(
						'type'       => 'object',
						'properties' => array(
							'working_days'         => array(
								'type' => 'array',
							),
							'opening_closing_time' => array(
								'type' => 'string',
							),
							'status'               => array(
								'type' => 'string',
							),
						),
					),
				),
			)
		);

		if ( function_exists( 'add_settings_section' ) ) {

			add_settings_section( 'schedule_settings', 'Schedule Settings', array( $this, 'slwh_plugin_section_text' ), 'sl_woocommerce_hours' );

			add_settings_field( 'slwh_plugin_setting_working_days', 'Working Days', array( $this, 'slwh_plugin_setting_working_days' ), 'sl_woocommerce_hours', 'schedule_settings' );
			add_settings_field( 'slwh_plugin_setting_opening_closing_time', 'Opening & Closing Time', array( $this, 'slwh_plugin_setting_opening_closing_time' ), 'sl_woocommerce_hours', 'schedule_settings' );
			add_settings_field( 'slwh_plugin_setting_status', 'Enable/Disable Store', array( $this, 'slwh_plugin_setting_status' ), 'sl_woocommerce_hours', 'schedule_settings' );

		}
	}

	public function slwh_plugin_options_validate( $input ) {
		$current_options = get_option( 'slwh_plugin_options' );
		// Add 0 as the status value if it's not currently set.
		$input['status'] ??= '0';
		if ( isset( $input['opening_closing_time'] ) ) {
			$input['opening_closing_time'] = trim( preg_replace( '/\s+/', '', $input['opening_closing_time'] ) );
			if ( preg_match( '/^(\d{2}(?=-\d{2}))-((?<=\d{2}-)\d{2})/i', $input['opening_closing_time'], $matches ) ) {
				array_shift( $matches );
				$prev_match = 0;
				foreach ( $matches as $match ) {

					/*
					Make sure that
					[1] provided values do not exceed 24 (hours).
					[2] The first (prev) match is less than the current one.
					*/
					if ( 24 < $match ) {
						// $input['opening_closing_time'] = '';
						$input['opening_closing_time'] = $current_options['opening_closing_time'];
						add_settings_error( 'slwh_plugin_setting_opening_closing_time', 'slwh_plugin_options[opening_closing_time]', __( 'Error: The opening and closing time should not exceed 24 (hours).', 'super-light-woocommerce-hours' ) );
						break;
					}
					if ( $prev_match > $match ) {
						// $input['opening_closing_time'] = '';
						$input['opening_closing_time'] = $current_options['opening_closing_time'];
						add_settings_error( 'slwh_plugin_setting_opening_closing_time', 'slwh_plugin_options[opening_closing_time]', __( 'Error: The opening time should be earlier than the closing time.', 'super-light-woocommerce-hours' ) );
						break;
					}
					$prev_match = $match;
				}
				// ray( $input )->orange();
				return $input;
			} else {
				// $input['opening_closing_time'] = '';
				add_settings_error( 'slwh_plugin_setting_opening_closing_time', 'slwh_plugin_options[opening_closing_time]', __( 'Error: Use the format HH - HH for the opening and closing time. For example, 08 - 18.', 'super-light-woocommerce-hours' ) );
				$input['opening_closing_time'] = $current_options['opening_closing_time'];
			}
		}

		return $input;

	}

	public function slwh_plugin_section_text() {
		echo '<p>Here you can set all the options regarding when you want to accept orders.</p>';
	}

	public function slwh_plugin_setting_working_days() {
		$options           = get_option( 'slwh_plugin_options', array() );
		$slwh_working_days = isset( $options['working_days'] )
		? (array) $options['working_days'] : array();
		ray( $options );
		?>
		<input type='checkbox' name='slwh_plugin_options[working_days][]' id='sunday' <?php checked( in_array( 'Sunday', $slwh_working_days, true ), 1 ); ?> value='Sunday'>
		<label for='sunday'> Sunday</label><br>
		<input type='checkbox' name='slwh_plugin_options[working_days][]' id='monday' <?php checked( in_array( 'Monday', $slwh_working_days, true ), 1 ); ?> value='Monday'>
		<label for='monday'> Monday</label><br>
		<input type='checkbox' name='slwh_plugin_options[working_days][]' id='tuesday' <?php checked( in_array( 'Tuesday', $slwh_working_days, true ), 1 ); ?> value='Tuesday'>
		<label for='tuesday'> Tuesday</label><br>
		<input type='checkbox' name='slwh_plugin_options[working_days][]' id='wednesday' <?php checked( in_array( 'Wednesday', $slwh_working_days, true ), 1 ); ?> value='Wednesday'>
		<label for='wednesday'> Wednesday</label><br>
		<input type='checkbox' name='slwh_plugin_options[working_days][]' id='thursday' <?php checked( in_array( 'Thursday', $slwh_working_days, true ), 1 ); ?> value='Thursday'>
		<label for='thursday'> Thursday</label><br>
		<input type='checkbox' name='slwh_plugin_options[working_days][]' id='friday' <?php checked( in_array( 'Friday', $slwh_working_days, true ), 1 ); ?> value='Friday'>
		<label for='friday'> Friday</label><br>
		<input type='checkbox' name='slwh_plugin_options[working_days][]' id='saturday' <?php checked( in_array( 'Saturday', $slwh_working_days, true ), 1 ); ?> value='Saturday'>
		<label for='saturday'> Saturday</label><br>
		<?php
	}

	public function slwh_plugin_setting_opening_closing_time() {
		$default_options = array(
			'working_days'         => array(),
			'opening_closing_time' => '',
			'status'               => '0',
		);
		$options         = get_option( 'slwh_plugin_options' );
		?>
		<input id='slwh_plugin_setting_opening_closing_time' name='slwh_plugin_options[opening_closing_time]' type='text' value='<?php echo esc_attr( $options['opening_closing_time'] ); ?>' />
		<span class="block_description">Use the format <strong>HH - HH</strong>, for example, <code>08 - 18</code>. Hours are only supported in the 24H format and minutes are not allowed. Spaces are optional.</span>

		<?php
	}

	public function slwh_plugin_setting_status() {
		$default_options = array(
			'working_days'         => array(),
			'opening_closing_time' => '',
			'status'               => '0',
		);
		// delete_option( 'slwh_plugin_options' );
		$options = get_option( 'slwh_plugin_options' );
		// One of the values to compare.
		$checked = 1;
		// The other value to compare if not just true.
		$current = isset( $options['status'] )
		? $options['status'] : '0'; // Set current to false by default.
		// Whether to echo or just return the string.
		$display = true;
		?>
		<label class="switch">
		<input type='checkbox' name='slwh_plugin_options[status]' id='status' <?php checked( $checked, $current, $display ); ?> value='1' >

			<span class="slider round"></span>
		</label>
		<br><span class="block_description">This option <strong>overrides</strong> other scheduling options.</span>
		<?php
	}

	public function get_slwh_status() {
		$slwh_options = get_option( 'slwh_plugin_options' );

		// Return early if the override status is 1, regardless of the date or time.
		if ( '1' === $slwh_options['status'] ) {
			ray( 1 );
			return '1';
		}
		$current_datetime_obj = current_datetime();
		$day_of_week          = $current_datetime_obj->format( 'l' );

		// Check if current day of the week is on our list of working days.
		if ( ! in_array( $day_of_week, $slwh_options['working_days'] ) ) {
			ray( 0 );
			return '0';
		}

		$current_time    = strtotime( $current_datetime_obj->format( 'H:i:s' ) );
		$raw_time_range  = $slwh_options['opening_closing_time'];
		$operating_hours = explode( '-', $raw_time_range );

		function format_plain_hours( $hours ) {
			$formatted_hours = $hours . ':00:00';
			return $formatted_hours;
		}

		$opening_time = strtotime( format_plain_hours( $operating_hours[0] ) );
		$closing_time = strtotime( format_plain_hours( $operating_hours[1] ) );

		// Check if current time is between opening and closing time.
		if ( $opening_time < $current_time && $current_time < $closing_time ) {
			ray( 1 );
			return '1';
		} else {
			ray( 0 );
			return '0';
		}
	}

}
