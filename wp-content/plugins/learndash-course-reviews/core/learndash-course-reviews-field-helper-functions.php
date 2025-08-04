<?php
/**
 * Holds some shorthand functions for RBM Field Helpers.
 *
 * TODO: Autoload this file.
 *
 * @since 1.0.0
 *
 * @package LearnDash\Course_Reviews
 */

if ( ! defined( 'ABSPATH' ) ) {
	die;
}

/**
 * Quick access to plugin field helpers.
 *
 * @since 1.0.0
 *
 * @return RBM_FieldHelpers
 */
function learndash_course_reviews_fieldhelpers(): RBM_FieldHelpers {
	return LEARNDASHCOURSEREVIEWS()->field_helpers;
}

/**
 * Initializes a field group for automatic saving.
 *
 * @since 1.0.0
 *
 * @param string $group Field Group Name.
 *
 * @return void
 */
function learndash_course_reviews_init_field_group( $group ): void {
	learndash_course_reviews_fieldhelpers()->fields->save->initialize_fields( $group );
}

/**
 * Gets a field helpers meta field value.
 *
 * @since 1.0.0
 *
 * @param string                                        $name    Field name.
 * @param int|false                                     $post_ID Optional post ID.
 * @param mixed                                         $default Default value if none is retrieved.
 * @param array{sanitization?: callable, single?: bool} $args    Additional Args.
 *
 * @return mixed Field value
 */
function learndash_course_reviews_get_field( string $name, $post_ID = false, $default = '', array $args = array() ) {
	$value = learndash_course_reviews_fieldhelpers()->fields->get_meta_field( $name, $post_ID, $args );
	return $value !== false ? $value : $default;
}

/**
 * Gets a field helpers option field value.
 *
 * @since 1.0.0
 *
 * @param string                         $name    Field name.
 * @param mixed                          $default Default value if none is retrieved.
 * @param array{sanitization?: callable} $args    Additional Args.
 *
 * @return mixed Field value
 */
function learndash_course_reviews_get_option_field( string $name, $default = '', array $args = array() ) {
	$value = learndash_course_reviews_fieldhelpers()->fields->get_option_field( $name, $args );
	return ( $value !== false && $value !== '' ) ? $value : $default;
}

/**
 * Outputs a text field.
 *
 * @since 1.0.0
 *
 * @param array{name: string, id?: string, group?: string, value?: mixed, prefix?: string, label?: string, default?: mixed, description?: string, description_placement?: string, description_tip_alignment?: string, wrapper_classes?: array<string>, no_init?: bool, sanitization?: callable, input_class?: string, input_atts?: array<string, string>, option_field?: bool, repeater?: bool, name_base?: string, description_tip?: bool, multi_field?: bool} $args Field Args.
 *
 * @return void
 */
function learndash_course_reviews_do_field_text( array $args ): void {
	learndash_course_reviews_fieldhelpers()->fields->do_field_text( $args['name'], $args );
}

/**
 * Outputs a password field.
 *
 * @since 1.0.0
 *
 * @param array{name: string, id?: string, group?: string, value?: mixed, prefix?: string, label?: string, default?: mixed, description?: string, description_placement?: string, description_tip_alignment?: string, wrapper_classes?: array<string>, no_init?: bool, sanitization?: callable, input_class?: string, input_atts?: array<string, string>, option_field?: bool, repeater?: bool, name_base?: string, description_tip?: bool, multi_field?: bool} $args Field Args.
 *
 * @return void
 */
function learndash_course_reviews_do_field_password( array $args ): void {
	learndash_course_reviews_fieldhelpers()->fields->do_field_password( $args['name'], $args );
}

/**
 * Outputs a textarea field.
 *
 * @since 1.0.0
 *
 * @param array{name: string, id?: string, group?: string, value?: mixed, prefix?: string, label?: string, default?: mixed, description?: string, description_placement?: string, description_tip_alignment?: string, wrapper_classes?: array<string>, no_init?: bool, sanitization?: callable, input_class?: string, input_atts?: array<string, string>, option_field?: bool, repeater?: bool, name_base?: string, description_tip?: bool, multi_field?: bool, rows?: int, wysiwyg: true, wysiwyg_options: array<string, mixed>} $args Field Args.
 *
 * @return void
 */
function learndash_course_reviews_do_field_textarea( array $args ): void {
	learndash_course_reviews_fieldhelpers()->fields->do_field_textarea( $args['name'], $args );
}

/**
 * Outputs a checkbox field.
 *
 * @since 1.0.0
 *
 * @param array{name: string, id?: string, group?: string, value?: mixed, prefix?: string, label?: string, default?: mixed, description?: string, description_placement?: string, description_tip_alignment?: string, wrapper_classes?: array<string>, no_init?: bool, sanitization?: callable, input_class?: string, input_atts?: array<string, string>, option_field?: bool, repeater?: bool, name_base?: string, description_tip?: bool, multi_field?: bool, no_options_text?: string, options?: array<string, string>} $args Field Args.
 *
 * @return void
 */
function learndash_course_reviews_do_field_checkbox( array $args ): void {
	learndash_course_reviews_fieldhelpers()->fields->do_field_checkbox( $args['name'], $args );
}

/**
 * Outputs a toggle field.
 *
 * @since 1.0.0
 *
 * @param array{name: string, id?: string, group?: string, value?: mixed, prefix?: string, label?: string, default?: mixed, description?: string, description_placement?: string, description_tip_alignment?: string, wrapper_classes?: array<string>, no_init?: bool, sanitization?: callable, input_class?: string, input_atts?: array<string, string>, option_field?: bool, repeater?: bool, name_base?: string, description_tip?: bool, multi_field?: bool, checked_value?: string, unchecked_value: string} $args Field Args.
 *
 * @return void
 */
function learndash_course_reviews_do_field_toggle( array $args ): void {
	learndash_course_reviews_fieldhelpers()->fields->do_field_toggle( $args['name'], $args );
}

/**
 * Outputs a radio field.
 *
 * @since 1.0.0
 *
 * @param array{name: string, id?: string, group?: string, value?: mixed, prefix?: string, label?: string, default?: mixed, description?: string, description_placement?: string, description_tip_alignment?: string, wrapper_classes?: array<string>, no_init?: bool, sanitization?: callable, input_class?: string, input_atts?: array<string, string>, option_field?: bool, repeater?: bool, name_base?: string, description_tip?: bool, multi_field?: bool, options?: array<string, string>} $args Field Args.
 *
 * @return void
 */
function learndash_course_reviews_do_field_radio( array $args ): void {
	learndash_course_reviews_fieldhelpers()->fields->do_field_radio( $args['name'], $args );
}

/**
 * Outputs a select field.
 *
 * @since 1.0.0
 *
 * @param array{name: string, id?: string, group?: string, value?: mixed, prefix?: string, label?: string, default?: mixed, description?: string, description_placement?: string, description_tip_alignment?: string, wrapper_classes?: array<string>, no_init?: bool, sanitization?: callable, input_class?: string, input_atts?: array<string, string>, option_field?: bool, repeater?: bool, name_base?: string, description_tip?: bool, multi_field?: bool, multiple?: bool, show_empty_select?: bool, opt_groups?: bool, options?: array{value: string, text: string}|array<string, array<array{value: string, text: string}>>} $args Field Args.
 *
 * @return void
 */
function learndash_course_reviews_do_field_select( array $args ): void {
	learndash_course_reviews_fieldhelpers()->fields->do_field_select( $args['name'], $args );
}

/**
 * Outputs a number field.
 *
 * @since 1.0.0
 *
 * @param array{name: string, id?: string, group?: string, value?: mixed, prefix?: string, label?: string, default?: mixed, description?: string, description_placement?: string, description_tip_alignment?: string, wrapper_classes?: array<string>, no_init?: bool, sanitization?: callable, input_class?: string, input_atts?: array<string, string>, option_field?: bool, repeater?: bool, name_base?: string, description_tip?: bool, multi_field?: bool, postfix?: string, increase_interval?: float, alt_increase_interval?: float, max?: float, decrease_interval?: float, alt_decrease_interval?: float, min?: float} $args Field Args.
 *
 * @return void
 */
function learndash_course_reviews_do_field_number( array $args ): void {
	learndash_course_reviews_fieldhelpers()->fields->do_field_number( $args['name'], $args );
}

/**
 * Outputs an image field.
 *
 * @since 1.0.0
 *
 * @param array{name: string, id?: string, group?: string, value?: mixed, prefix?: string, label?: string, default?: mixed, description?: string, description_placement?: string, description_tip_alignment?: string, wrapper_classes?: array<string>, no_init?: bool, sanitization?: callable, input_class?: string, input_atts?: array<string, string>, option_field?: bool, repeater?: bool, name_base?: string, description_tip?: bool, multi_field?: bool, preview_size?: string, type?: string, placeholder?: string, media_preview_url?: string, l10n?: array{button_text?: string, button_remove_text?: string, window_title?: string, }} $args Field Args.
 *
 * @return void
 */
function learndash_course_reviews_do_field_media( array $args ): void {
	learndash_course_reviews_fieldhelpers()->fields->do_field_media( $args['name'], $args );
}

/**
 * Outputs a datepicker field.
 *
 * @since 1.0.0
 *
 * @param array{name: string, id?: string, group?: string, value?: mixed, prefix?: string, label?: string, default?: mixed, description?: string, description_placement?: string, description_tip_alignment?: string, wrapper_classes?: array<string>, no_init?: bool, sanitization?: callable, input_class?: string, input_atts?: array<string, string>, option_field?: bool, repeater?: bool, name_base?: string, description_tip?: bool, multi_field?: bool, format?: '', datepicker_args?: array{altInput?: bool, dateFormat?: string, altFormat?: string, }} $args Field Args.
 *
 * @return void
 */
function learndash_course_reviews_do_field_datepicker( array $args ): void {
	learndash_course_reviews_fieldhelpers()->fields->do_field_datepicker( $args['name'], $args );
}

/**
 * Outputs a timepicker field.
 *
 * @since 1.0.0
 *
 * @param array{name: string, id?: string, group?: string, value?: mixed, prefix?: string, label?: string, default?: mixed, description?: string, description_placement?: string, description_tip_alignment?: string, wrapper_classes?: array<string>, no_init?: bool, sanitization?: callable, input_class?: string, input_atts?: array<string, string>, option_field?: bool, repeater?: bool, name_base?: string, description_tip?: bool, multi_field?: bool, format?: '', datetimepicker_args?: array{enableTime?: bool, altInput?: bool, dateFormat?: string, altFormat?: string, }} $args Field Args.
 *
 * @return void
 */
function learndash_course_reviews_do_field_timepicker( array $args ): void {
	learndash_course_reviews_fieldhelpers()->fields->do_field_timepicker( $args['name'], $args );
}

/**
 * Outputs a datetimepicker field.
 *
 * @since 1.0.0
 *
 * @param array{name: string, id?: string, group?: string, value?: mixed, prefix?: string, label?: string, default?: mixed, description?: string, description_placement?: string, description_tip_alignment?: string, wrapper_classes?: array<string>, no_init?: bool, sanitization?: callable, input_class?: string, input_atts?: array<string, string>, option_field?: bool, repeater?: bool, name_base?: string, description_tip?: bool, multi_field?: bool, format?: '', datetimepicker_args?: array{enableTime?: bool, altInput?: bool, dateFormat?: string, altFormat?: string, }} $args Field Args.
 *
 * @return void
 */
function learndash_course_reviews_do_field_datetimepicker( array $args ): void {
	learndash_course_reviews_fieldhelpers()->fields->do_field_datetimepicker( $args['name'], $args );
}

/**
 * Outputs a colorpicker field.
 *
 * @since 1.0.0
 *
 * @param array{name: string, id?: string, group?: string, value?: mixed, prefix?: string, label?: string, default?: mixed, description?: string, description_placement?: string, description_tip_alignment?: string, wrapper_classes?: array<string>, no_init?: bool, sanitization?: callable, input_class?: string, input_atts?: array<string, string>, option_field?: bool, repeater?: bool, name_base?: string, description_tip?: bool, multi_field?: bool, colorpicker_options?: array<string, mixed>} $args Field Args.
 *
 * @return void
 */
function learndash_course_reviews_do_field_colorpicker( array $args ): void {
	learndash_course_reviews_fieldhelpers()->fields->do_field_colorpicker( $args['name'], $args );
}

/**
 * Outputs a list field.
 *
 * @since 1.0.0
 *
 * @param array{name: string, id?: string, group?: string, value?: mixed, prefix?: string, label?: string, default?: mixed, description?: string, description_placement?: string, description_tip_alignment?: string, wrapper_classes?: array<string>, no_init?: bool, sanitization?: callable, input_class?: string, input_atts?: array<string, string>, option_field?: bool, repeater?: bool, name_base?: string, description_tip?: bool, multi_field?: bool, items: array<string, string>, sortable_args?: array{axis?: string}} $args Field Args.
 *
 * @return void
 */
function learndash_course_reviews_do_field_list( array $args ): void {
	learndash_course_reviews_fieldhelpers()->fields->do_field_list( $args['name'], $args );
}

/**
 * Outputs a hidden field.
 *
 * @since 1.0.0
 *
 * @param array{name: string, id?: string, group?: string, value?: mixed, prefix?: string, label?: string, default?: mixed, description?: string, description_placement?: string, description_tip_alignment?: string, wrapper_classes?: array<string>, no_init?: bool, sanitization?: callable, input_class?: string, input_atts?: array<string, string>, option_field?: bool, repeater?: bool, name_base?: string, description_tip?: bool, multi_field?: bool} $args Field Args.
 *
 * @return void
 */
function learndash_course_reviews_do_field_hidden( array $args ): void {
	learndash_course_reviews_fieldhelpers()->fields->do_field_hidden( $args['name'], $args );
}

/**
 * Outputs a table field.
 *
 * @since 1.0.0
 *
 * @param array{name: string, id?: string, group?: string, value?: mixed, prefix?: string, label?: string, default?: mixed, description?: string, description_placement?: string, description_tip_alignment?: string, wrapper_classes?: array<string>, no_init?: bool, sanitization?: callable, input_class?: string, input_atts?: array<string, string>, option_field?: bool, repeater?: bool, name_base?: string, description_tip?: bool, multi_field?: bool, l10n?: array{add_row: string, add_column: string }} $args Field Args.
 *
 * @return void
 */
function learndash_course_reviews_do_field_table( array $args ): void {
	learndash_course_reviews_fieldhelpers()->fields->do_field_table( $args['name'], $args );
}

/**
 * Outputs a HTML field.
 *
 * @since 1.0.0
 *
 * @param array{name: string, html: string} $args Field Args.
 *
 * @return void
 */
function learndash_course_reviews_do_field_html( array $args ): void {
	learndash_course_reviews_fieldhelpers()->fields->do_field_html( $args['name'], $args );
}

/**
 * Outputs a repeater field.
 *
 * @since 1.0.0
 *
 * @param array{name: string, id?: string, group?: string, value?: mixed, prefix?: string, label?: string, default?: mixed, description?: string, description_placement?: string, description_tip_alignment?: string, wrapper_classes?: array<string>, no_init?: bool, sanitization?: callable, input_class?: string, input_atts?: array<string, string>, option_field?: bool, repeater?: bool, name_base?: string, description_tip?: bool, multi_field?: bool, collapsable?: bool, sortable?: bool, first_item_undeletable?: bool, l10n?: array{collapsable_title?: string, confirm_delete?: string, delete_item?: string, add_item?: string }, fields: array<string, array{type: string, args: array<string, mixed> }>} $args Field Args.
 *
 * @return void
 */
function learndash_course_reviews_do_field_repeater( array $args ): void {
	learndash_course_reviews_fieldhelpers()->fields->do_field_repeater( $args['name'], $args );
}

/**
 * Outputs a hook.
 *
 * @since 1.0.0
 *
 * @param array{name: string} $args Field Args.
 *
 * @return void
 */
function learndash_course_reviews_do_field_hook( array $args ): void {
	learndash_course_reviews_fieldhelpers()->fields->do_field_hook( $args['name'], $args );
}

/**
 * Outputs a String if a Callback Function does not exist for an Options Page Field.
 *
 * @since 1.0.0
 *
 * @param array{type: string} $args Field Args.
 *
 * @return void
 */
function learndash_course_reviews_missing_callback( array $args ): void {
	echo esc_html(
		sprintf(
			// translators: Field Type.
			__( 'A callback function called "learndash_course_reviews_do_field_%s" does not exist.', 'learndash-course-reviews' ),
			$args['type']
		)
	);
}

/**
 * Gets a field description tip.
 *
 * @since 1.0.0
 *
 * @param string $description Description text.
 *
 * @return string Description Tip HTML.
 */
function learndash_course_reviews_get_field_tip( string $description ): string {
	ob_start();
	?>
	<div class="fieldhelpers-field-description fieldhelpers-field-tip">
		<span class="fieldhelpers-field-tip-toggle dashicons dashicons-editor-help" data-toggle-tip></span>
		<p class="fieldhelpers-field-tip-text">
			<?php echo esc_html( $description ); ?>
		</p>
	</div>
	<?php

	return strval( ob_get_clean() );
}

/**
 * Outputs a field description tip.
 *
 * @since 1.0.0
 *
 * @param string $description Description text.
 *
 * @return void
 */
function learndash_course_reviews_field_tip( string $description ): void {
	// phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- Already escaped
	echo learndash_course_reviews_get_field_tip( $description );
}
