<?php
/**
 * Field: DateTime Picker
 *
 * @since 1.4.0
 *
 * @package RBMFieldHelpers
 */

defined( 'ABSPATH' ) || die();

/**
 * Class RBM_FH_Field_DateTimePicker
 *
 * @since 1.4.0
 */
class RBM_FH_Field_DateTimePicker extends RBM_FH_Field {

	/**
	 * Field defaults.
	 *
	 * @since 1.4.0
	 *
	 * @var array
	 */
	public $defaults = array(
		'default'             => '',
		'format'              => '',
		'datetimepicker_args' => array(
			'enableTime'       => true,
			'altInput'         => true,
			'dateFormat'       => 'Ymd H:i', // Saved format
			'altFormat'        => 'F j, Y h:i K', // Display format
		),
	);

	/**
	 * RBM_FH_Field_DateTimePicker constructor.
	 *
	 * @since 1.4.0
	 *
	 * @var string $name
	 * @var array $args
	 * @var mixed $value
	 */
	function __construct( $name, $args = array() ) {
		
		$date_format_php = get_option( 'date_format', 'F j, Y' );
		$time_format_php = get_option( 'time_format', 'g:i a' );

		// Cannot use function in property declaration
		$this->defaults['format'] = $date_format_php . ' ' . $time_format_php;
		
		// Ensure the Date/Time Format matches the stored format in WordPress
		$this->defaults['datetimepicker_args']['altFormat'] = RBM_FH_Field_DateTimePicker::php_date_to_flatpickr( $this->defaults['format'] );
		
		if ( ! isset( $args['datetimepicker_args'] ) ) {
			$args['datetimepicker_args'] = array();
		}

		// Default options
		$args['datetimepicker_args'] = wp_parse_args( $args['datetimepicker_args'], $this->defaults['datetimepicker_args'] );

		if ( ! isset( $args['default'] ) ) {

			// This is used when creating the field HTML
			$args['default'] = current_time( $args['datetimepicker_args']['dateFormat'] );

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

		// DateTimepicker args
		if ( $args['datetimepicker_args'] ) {

			add_filter( 'rbm_field_helpers_admin_data', function ( $data ) use ( $args, $name ) {

				$data["datetimepicker_args_$name"] = $args['datetimepicker_args'];

				return $data;
			} );
		}

		do_action( "{$args['prefix']}_fieldhelpers_do_field", 'datetimepicker', $args, $name, $value );
	}
	
	/**
	 * Converts a PHP Date/Time Format to what Flatpickr expects
	 * In most cases, it is identical. This function helps with some edge-cases.
	 * 
	 * Cleaned up variant of http://stackoverflow.com/a/16725290
	 * 
	 * @since 1.5.0
	 * 
	 * @param string $php_format PHP Date Format
	 * @return string jQuery UI Date Format
	 */
	public static function php_date_to_flatpickr( $php_format ) {
		
		$format_map = array(
			// Day
			'd' => 'd',
			'D' => 'D',
			'j' => 'j',
			'l' => 'l',
			'N' => '',
			'S' => '',
			'w' => 'w',
			'z' => 'o',
			// Week
			'W' => 'W',
			// Month
			'F' => 'F',
			'm' => 'm',
			'M' => 'M',
			'n' => 'n',
			't' => '',
			// Year
			'L' => '',
			'o' => '',
			'Y' => 'Y',
			'y' => 'y',
			// Time
			'a' => 'K',
			'A' => 'K',
			'B' => '',
			'g' => 'h',
			'G' => 'H',
			'h' => 'G',
			'H' => 'H',
			'i' => 'i',
			's' => 'S',
			'u' => ''
		);

		$flatpickr_format = '';
		$escaped = false;

		for ( $index = 0; $index < strlen( $php_format ); $index++ ) {

			$char = $php_format[$index];

			if ( $char === '\\' ) { // If Character is an Escaping Slash

				$index++;

				// If we haven't already escaped a character, output it alongside the next character
				if ( ! $escaped ) {

					$flatpickr_format .= '\'' . $php_format[ $index ];
					$escaped = true;

				}
				else  {

					// Ignore, we've already escaped it
					$flatpickr_format .= $php_format[ $index ];

				}

			}
			else {

				// Reset Escaped Status for next loop
				if ( $escaped ) {

					$flatpickr_format .= "'";
					$escaped = false;

				}

				// Make necessary replacements via our PHP->jQuery UI Format Map
				if ( isset( $format_map[ $char ] ) ) {
					$flatpickr_format .= $format_map[ $char ];
				}
				else {
					$flatpickr_format .= $char;
				}

			}

		}

		return $flatpickr_format;	
	}
	
}