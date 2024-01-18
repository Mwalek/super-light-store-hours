<?php
/**
 * Extends the main plugin class.
 *
 * @link https://github.com/Mwalek/super-light-store-hours
 *
 * @package    WordPress
 * @subpackage Plugins
 * @since      1.0.0
 */

namespace MwaleMe\Super_Light_Store_Hours;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Class Super_Light_Store_Hours_Settings.
 *
 * Handles the configuration and settings for the Super Light Store Hours plugin.
 */
class Super_Light_Store_Hours_Settings {
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

	);
	/**
	 * Default options for the plugin.
	 *
	 * @var array
	 */
	private $default_options = array(
		'working_days'         => array( 'Sunday', 'Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday' ),
		'opening_closing_time' => '00-24',
		'override_status'      => '0',
	);
	/**
	 * Super_Light_Store_Hours_Settings constructor.
	 *
	 * Initializes the Super_Light_Store_Hours_Settings class by setting up various WordPress actions and filters.
	 */
	public function __construct() {
		// Add settings page to admin menu.
		add_action( 'admin_menu', array( $this, 'slsh_add_settings_page' ) );

		// Register settings on admin initialization.
		add_action( 'admin_init', array( $this, 'slsh_register_settings' ) );

		// Register settings for REST API.
		add_action( 'rest_api_init', array( $this, 'slsh_register_settings' ) );

		// Initialize conditions on WordPress initialization.
		add_action( 'init', array( $this, 'get_slsh_condition' ) );

		// Register REST API route for fetching store state.
		add_action(
			'rest_api_init',
			function () {
				register_rest_route(
					'slsh/v1',
					'/state',
					array(
						'methods'             => 'GET',
						'callback'            => array( $this, 'get_slsh_settings' ),
						'permission_callback' => '__return_true',
					)
				);
			}
		);

		// Add settings page link to plugin action links.
		$dir = dirname( plugin_basename( __DIR__ ), 1 );
		add_filter( 'plugin_action_links_' . $dir . '/super-light-store-hours.php', array( $this, 'add_settings_page_link' ) );

		// Set capability for options page to manage WooCommerce capability.
		add_filter(
			'option_page_capability_sl-store-hours',
			function( $capability ) {
				return 'manage_woocommerce';
			}
		);
	}

	/**
	 * Adds the settings page to the admin menu.
	 */
	public function slsh_add_settings_page() {
		add_options_page( 'Custom Store Hours', 'Store Hours', 'manage_woocommerce', 'sl-store-hours', array( $this, 'slsh_render_plugin_settings_page' ) );
	}

	/**
	 * Renders the plugin settings page.
	 */
	public function slsh_render_plugin_settings_page() {
		?>
		<div class="wrap">
			<div class="main_content">
				<h2><?php esc_html_e( 'Store Operating Hours', 'super-light-store-hours' ); ?></h2>
				<form action="options.php" method="post">
					<?php
					settings_fields( 'sl-store-hours' );
					do_settings_sections( 'sl_store_hours' );
					?>
					<input name="submit" class="button button-primary" type="submit" value="<?php esc_attr_e( 'Save' ); ?>" />
				</form>
			</div>
		</div>
		<?php
	}

	/**
	 * Registers plugin settings.
	 */
	public function slsh_register_settings() {
		register_setting(
			'sl-store-hours',
			'slsh_plugin_options',
			array(
				'default'           => $this->default_options,
				'type'              => 'object',
				'sanitize_callback' => array(
					$this,
					'slsh_plugin_options_validate',
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
							'override_status'      => array(
								'type' => 'string',
							),
						),
					),
				),
			)
		);

		if ( function_exists( 'add_settings_section' ) ) {
			add_settings_section( 'schedule_settings', __( 'Schedule Settings', 'super-light-store-hours' ), array( $this, 'slsh_plugin_section_text' ), 'sl_store_hours' );

			add_settings_field( 'slsh_plugin_setting_working_days', __( 'Working Days', 'super-light-store-hours' ), array( $this, 'slsh_plugin_setting_working_days' ), 'sl_store_hours', 'schedule_settings' );
			add_settings_field( 'slsh_plugin_setting_opening_closing_time', __( 'Opening & Closing Time', 'super-light-store-hours' ), array( $this, 'slsh_plugin_setting_opening_closing_time' ), 'sl_store_hours', 'schedule_settings' );
			add_settings_field( 'slsh_plugin_setting_override_status', __( 'Enable Store', 'super-light-store-hours' ), array( $this, 'slsh_plugin_setting_override_status' ), 'sl_store_hours', 'schedule_settings' );
		}
	}

	/**
	 * Validates plugin options.
	 *
	 * @param array $input The input options.
	 *
	 * @return array The validated options.
	 */
	public function slsh_plugin_options_validate( $input ) {
		$current_options = get_option( 'slsh_plugin_options', $this->default_options );

		// Add 0 as the override_status value if it's not currently set.
		$input['override_status'] ??= '0';

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
						$input['opening_closing_time'] = $current_options['opening_closing_time'];
						add_settings_error( 'slsh_plugin_setting_opening_closing_time', 'slsh_plugin_options[opening_closing_time]', __( 'Error: The opening and closing time should not exceed 24 (hours).', 'super-light-store-hours' ) );
						break;
					}

					if ( $prev_match > $match ) {
						$input['opening_closing_time'] = $current_options['opening_closing_time'];
						add_settings_error( 'slsh_plugin_setting_opening_closing_time', 'slsh_plugin_options[opening_closing_time]', __( 'Error: The opening time should be earlier than the closing time.', 'super-light-store-hours' ) );
						break;
					}

					$prev_match = $match;
				}

				return $input;
			} else {
				add_settings_error( 'slsh_plugin_setting_opening_closing_time', 'slsh_plugin_options[opening_closing_time]', __( 'Error: Use the format HH - HH for the opening and closing time. For example, 08 - 18.', 'super-light-store-hours' ) );
				$input['opening_closing_time'] = $current_options['opening_closing_time'];
			}
		}

		return $input;
	}

	/**
	 * Renders the section text for plugin settings.
	 */
	public function slsh_plugin_section_text() {
		esc_html_e( 'Here you can set all the options regarding when you want to accept orders.', 'super-light-store-hours' );
	}

	/**
	 * Renders the working days setting.
	 */
	public function slsh_plugin_setting_working_days() {
		$options           = get_option( 'slsh_plugin_options', $this->default_options );
		$slsh_working_days = isset( $options['working_days'] ) ? (array) $options['working_days'] : array();
		?>
		<input type='checkbox' name='slsh_plugin_options[working_days][]' id='sunday' <?php checked( in_array( 'Sunday', $slsh_working_days, true ), 1 ); ?> value='Sunday'>
		<label for='sunday'><?php esc_html_e( 'Sunday', 'super-light-store-hours' ); ?></label><br>
		<input type='checkbox' name='slsh_plugin_options[working_days][]' id='monday' <?php checked( in_array( 'Monday', $slsh_working_days, true ), 1 ); ?> value='Monday'>
		<label for='monday'><?php esc_html_e( 'Monday', 'super-light-store-hours' ); ?></label><br>
		<input type='checkbox' name='slsh_plugin_options[working_days][]' id='tuesday' <?php checked( in_array( 'Tuesday', $slsh_working_days, true ), 1 ); ?> value='Tuesday'>
		<label for='tuesday'><?php esc_html_e( 'Tuesday', 'super-light-store-hours' ); ?></label><br>
		<input type='checkbox' name='slsh_plugin_options[working_days][]' id='wednesday' <?php checked( in_array( 'Wednesday', $slsh_working_days, true ), 1 ); ?> value='Wednesday'>
		<label for='wednesday'><?php esc_html_e( 'Wednesday', 'super-light-store-hours' ); ?></label><br>
		<input type='checkbox' name='slsh_plugin_options[working_days][]' id='thursday' <?php checked( in_array( 'Thursday', $slsh_working_days, true ), 1 ); ?> value='Thursday'>
		<label for='thursday'><?php esc_html_e( 'Thursday', 'super-light-store-hours' ); ?></label><br>
		<input type='checkbox' name='slsh_plugin_options[working_days][]' id='friday' <?php checked( in_array( 'Friday', $slsh_working_days, true ), 1 ); ?> value='Friday'>
		<label for='friday'><?php esc_html_e( 'Friday', 'super-light-store-hours' ); ?></label><br>
		<input type='checkbox' name='slsh_plugin_options[working_days][]' id='saturday' <?php checked( in_array( 'Saturday', $slsh_working_days, true ), 1 ); ?> value='Saturday'>
		<label for='saturday'><?php esc_html_e( 'Saturday', 'super-light-store-hours' ); ?></label><br>
		<?php
	}

	/**
	 * Display HTML input for setting opening and closing time.
	 *
	 * @return void
	 */
	public function slsh_plugin_setting_opening_closing_time() {
		$options = get_option( 'slsh_plugin_options', $this->default_options );
		?>
	<input id='slsh_plugin_setting_opening_closing_time' name='slsh_plugin_options[opening_closing_time]' type='text' value='<?php echo esc_attr( $options['opening_closing_time'] ); ?>' />
	<span class="block_description">
		<?php
		// Description for operating hours format.
		$operating_hrs_desc = __( 'Use the format <strong>HH - HH</strong>, for example, <code>08 - 18</code>. Hours are only supported in the 24H format and minutes are not allowed. Spaces are optional.', 'super-light-store-hours' );
		echo wp_kses( $operating_hrs_desc, $this->allowed_html );
		?>
		<!-- Use the format <strong>HH - HH</strong>, for example, <code>08 - 18</code>. Hours are only supported in the 24H format and minutes are not allowed. Spaces are optional.</span> -->
		<?php
	}

	/**
	 * Display HTML input for overriding store status.
	 *
	 * @return void
	 */
	public function slsh_plugin_setting_override_status() {
		$options = get_option( 'slsh_plugin_options', $this->default_options );
		// One of the values to compare.
		$checked = 1;
		// The other value to compare if not just true.
		$current = isset( $options['override_status'] )
		? $options['override_status'] : '0'; // Set current to false by default.
		// Whether to echo or just return the string.
		$display = true;
		?>
	<label class="switch">
	<input type='checkbox' name='slsh_plugin_options[override_status]' id='override_status' <?php checked( $checked, $current, $display ); ?> value='1' >
		<span class="slider round"></span>
	</label>
	<br><span class="block_description">
		<?php
		// Description for override status.
		esc_html_e( 'This option overrides other scheduling options.', 'super-light-store-hours' );
		?>
	</span>
		<?php
	}

	/**
	 * Get the store condition based on working days, current day, and time range.
	 *
	 * @return array Store condition status.
	 */
	public function get_slsh_condition() {
		$slsh_options           = get_option( 'slsh_plugin_options', $this->default_options );
		$slsh_options['status'] = null;

		// Return early if the override_status is 1, regardless of the date or time.
		if ( '1' === $slsh_options['override_status'] ) {
			$slsh_options['status'] = '1';
			return $slsh_options;
		}

		$current_datetime_obj = current_datetime();
		$day_of_week          = $current_datetime_obj->format( 'l' );

		// Check if no working day has been added.
		if ( is_null( $slsh_options['working_days'] ) ) {
			$slsh_options['status'] = '0';
			return $slsh_options;
		}

		// Check if current day of the week is on our list of working days.
		if ( ! in_array( $day_of_week, $slsh_options['working_days'], true ) ) {
			$slsh_options['status'] = '0';
			return $slsh_options;
		}

		$current_time    = strtotime( $current_datetime_obj->format( 'H:i:s' ) );
		$raw_time_range  = $slsh_options['opening_closing_time'];
		$operating_hours = explode( '-', $raw_time_range );

		if ( ! function_exists( __NAMESPACE__ . '\format_plain_hours' ) ) {
			/**
			 * Takes a plain number and returns a valid time string (e.g 14 => 14:00:00).
			 *
			 * @param string $hours A plain 2 digit string.
			 * @return string
			 */
			function format_plain_hours( $hours ) {
				$formatted_hours = $hours . ':00:00';
				return $formatted_hours;
			}
		}

		$opening_time = strtotime( format_plain_hours( $operating_hours[0] ) );
		$closing_time = strtotime( format_plain_hours( $operating_hours[1] ) );

		// Check if current time is between opening and closing time.
		if ( $opening_time < $current_time && $current_time < $closing_time ) {
			$slsh_options['status'] = '1';
			return $slsh_options;
		} else {
			$slsh_options['status'] = '0';
			return $slsh_options;
		}
	}

	/**
	 * Get the store settings by calling the condition function.
	 *
	 * @return array Store condition status.
	 */
	public function get_slsh_settings() {
		return $this->get_slsh_condition();
	}

	/**
	 * Add settings page link to the plugin action links.
	 *
	 * @param array $links Plugin action links.
	 * @return array Updated plugin action links.
	 */
	public function add_settings_page_link( array $links ) {
		$url           = get_admin_url() . 'options-general.php?page=sl-store-hours';
		$settings_link = '<a href="' . $url . '">' . __( 'Settings', 'super-light-store-hours' ) . '</a>';
		array_unshift( $links, $settings_link );
		return $links;
	}

}
