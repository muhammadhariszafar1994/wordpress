<?php
/**
 * Field Template: Date Picker
 *
 * @since 1.4.0
 *
 * @var array $args Field arguments.
 * @var string $name Field name.
 * @var mixed $value Field value.
 */

defined( 'ABSPATH' ) || die();
?>

<input type="text"
       name="<?php echo esc_attr( $name ); ?>"
       class="fieldhelpers-field-datepicker-preview"
       value="<?php echo esc_attr( $value ); ?>"
	   <?php RBM_FH_Field::input_atts( $args ); ?>
       data-fieldhelpers-field-datepicker
       data-defaultDate="<?php echo esc_attr( $args['default'] ); ?>"
/>