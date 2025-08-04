<?php
/**
 * Field: Time Picker
 *
 * @since 1.4.0
 *
 * @package RBMFieldHelpers
 */

defined( 'ABSPATH' ) || die();

/**
 * Class RBM_FH_Field_TimePicker
 *
 * @since 1.4.0
 */
class RBM_FH_Field_TimePicker extends RBM_FH_Field {

	/**
	 * Field defaults.
	 *
	 * @since 1.4.0
	 *
	 * @var array
	 */
	public $defaults = array(
		'default'         => '',
		'format'          => '',
		'timepicker_args' => array(
			'enableTime'       => true,
			'noCalendar'       => true,
			'altInput'         => true,
			'dateFormat'       => 'H:i', // Saved format
			'altFormat'        => 'h:i K', // Display format
		),
	);

	/**
	 * RBM_FH_Field_TimePicker constructor.
	 *
	 * @since 1.4.0
	 *
	 * @var string $name
	 * @var array $args
	 */
	function __construct( $name, $args = array() ) {

		// Cannot use function in property declaration
		$this->defaults['format'] = get_option( 'time_format', 'g:i a' );
		
		// Ensure the Time Format matches the stored format in WordPress
		$this->defaults['timepicker_args']['altFormat'] = RBM_FH_Field_DateTimePicker::php_date_to_flatpickr( $this->defaults['format'] );
		
		if ( ! isset( $args['timepicker_args'] ) ) {
			$args['timepicker_args'] = array();
		}

		// Default options
		$args['timepicker_args'] = wp_parse_args( $args['timepicker_args'], $this->defaults['timepicker_args'] );

		if ( ! isset( $args['default'] ) ) {

			// This is used when creating the field HTML
			$args['default'] = current_time( $args['timepicker_args']['dateFormat'] );

		}

		parent::__construct( $name, $args );
	}

	/**
	 * Outputs the field.
	 *
	 * @since 1.4.0
	 *
	 * @param string $name Name of the field.
	 * @param mixed $value Value of the field.
	 * @param array $args Field arguments.
	 */
	public static function field( $name, $value, $args = array() ) {

		// Timepicker args
		if ( $args['timepicker_args'] ) {
			add_filter( 'rbm_field_helpers_admin_data', function ( $data ) use ( $args, $name ) {

				$data["timepicker_args_$name"] = $args['timepicker_args'];

				return $data;
			} );
		}

		do_action( "{$args['prefix']}_fieldhelpers_do_field", 'timepicker', $args, $name, $value );
	}
}