<?php
/**
 * Makes additions to the Course Edit screen.
 *
 * TODO: Refactor to use a namespace and autoload.
 *
 * @since 1.0.0
 *
 * @package LearnDash\Course_Reviews
 */

if ( ! defined( 'ABSPATH' ) ) {
	die;
}

/**
 * Main LearnDash_Course_Reviews_Course_Edit class.
 *
 * @since 1.0.0
 */
final class LearnDash_Course_Reviews_Course_Edit {
	/**
	 * LearnDash_Course_Reviews_Course_Edit Constructor
	 *
	 * @since 1.0.0
	 *
	 * @return void
	 */
	public function __construct() {
		add_action( 'add_meta_boxes', array( $this, 'add_course_metaboxes' ) );
	}

	/**
	 * Adds our Meta Box to the Course Edit screen.
	 *
	 * @since 1.0.0
	 *
	 * @param string $post_type Current Post Type.
	 *
	 * @return void
	 */
	public function add_course_metaboxes( $post_type ) {
		if ( $post_type !== 'sfwd-courses' ) {
			return;
		}

		add_meta_box(
			'learndash-course-reviews',
			sprintf(
				// translators: Singular name for Courses.
				__( '%s Reviews', 'learndash-course-reviews' ),
				learndash_get_custom_label( 'course' )
			),
			array( $this, 'review_display_metabox' ),
			$post_type,
			'side',
			'default'
		);
	}

	/**
	 * Outputs our Meta Box Content.
	 *
	 * @since 1.0.0
	 *
	 * @return void
	 */
	public function review_display_metabox() {
		learndash_course_reviews_do_field_toggle(
			array(
				'name'            => 'show_reviews',
				'label'           => sprintf(
					// translators: Singular name for Courses.
					__( 'Allow Reviews for this %s?', 'learndash-course-reviews' ),
					learndash_get_custom_label( 'course' )
				),
				'checked_value'   => 'y',
				'unchecked_value' => 'n',
				'default'         => 'y',
				'group'           => 'course_edit',
			)
		);

		learndash_course_reviews_init_field_group( 'course_edit' );
	}
}

// phpcs:ignore WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedVariableFound
$instance = new LearnDash_Course_Reviews_Course_Edit();
