<?php
/**
 * Provides helper functions.
 *
 * TODO: Autoload this file.
 *
 * @since 1.0.0
 *
 * @package LearnDash\Course_Reviews
 */

use LearnDash\Core\Utilities\Cast;

if ( ! defined( 'ABSPATH' ) ) {
	die;
}

/**
 * Returns the main plugin object.
 *
 * @since 1.0.0
 *
 * @return LearnDash_Course_Reviews
 */
function LEARNDASHCOURSEREVIEWS() { // phpcs:ignore WordPress.NamingConventions.ValidFunctionName.FunctionNameInvalid
	return LearnDash_Course_Reviews::instance();
}

/**
 * Loads a template file from the theme if it exists, otherwise from the plugin.
 *
 * @since 1.0.0
 *
 * @param string               $template_name Template path, relative to the src/views directory.
 * @param array<string, mixed> $args          Array of variables to pass into the template file.
 *
 * @return void
 */
function learndash_course_reviews_locate_template( $template_name, $args = array() ) {
	/**
	 * Filter the template name to be located.
	 *
	 * @since 1.0.0
	 *
	 * @param string               $template_name Template path, relative to the src/views directory.
	 * @param array<string, mixed> $args          Array of variables to pass into the template file.
	 *
	 * @return string Template path, relative to the src/views directory.
	 */
	$template_name = apply_filters(
		'learndash_course_reviews_locate_template_name',
		$template_name,
		$args
	);

	/**
	 * Filter the args to use in the template.
	 *
	 * @since 1.0.0
	 *
	 * @param array<string, mixed> $args          Array of variables to pass into the template file.
	 * @param string               $template_name Template path, relative to the src/views directory.
	 *
	 * @return array<string, mixed> Array of variables to pass into the template file.
	 */
	$args = apply_filters(
		'learndash_course_reviews_locate_template_args',
		$args,
		$template_name
	);

	// phpcs:ignore WordPress.PHP.DontExtract.extract_extract -- TODO: Replace template loading logic to not require extract().
	extract(
		$args,
		EXTR_SKIP
	);

	$template_file = '';
	$theme_file    = locate_template( "/learndash-course-reviews/{$template_name}" );

	if ( $theme_file ) {
		$template_file = $theme_file;
	} else {
		$template_file = LEARNDASH_COURSE_REVIEWS_DIR . "src/views/{$template_name}";
	}

	/**
	 * Filter the template file to be located.
	 *
	 * @since 1.0.0
	 *
	 * @param string               $template_file Absolute template path to be loaded.
	 * @param string               $template_name Template path, relative to the src/views directory.
	 * @param array<string, mixed> $args          Array of variables to pass into the template file.
	 *
	 * @return string Absolute template path to be loaded.
	 */
	$template_file = apply_filters(
		'learndash_course_reviews_locate_template',
		$template_file,
		$template_name,
		$args
	);

	include $template_file;
}

/**
 * Check whether the Student has started the Course.
 *
 * @since 1.0.0
 *
 * @param int $course_id Course ID.
 * @param int $user_id   User ID.
 *
 * @return bool
 */
function learndash_course_reviews_user_has_started_course( int $course_id, int $user_id = 0 ): bool {
	$result = false;

	if ( $user_id <= 0 ) {
		$user_id = get_current_user_id();
	}

	// User is not logged in, they cannot have started the Course.
	if ( $user_id === 0 ) {
		return false;
	}

	// Note: If LearnDash is set up to wipe their Course Progress on expiration, we are unable to know whether they have started the Course or not so this will not find any completed Course Steps.
	$course_progress = learndash_course_progress(
		array(
			'array'     => true,
			'course_id' => $course_id,
			'user_id'   => $user_id,
		)
	);

	$result = ! empty( $course_progress['completed'] );

	// Useful to include in our Filter.
	$has_access = sfwd_lms_has_access( $course_id, $user_id );

	/**
	 * Filters whether the Student has started the Course.
	 */
	return apply_filters(
		'learndash_course_reviews_user_has_started_course',
		$result,
		$course_id,
		$user_id,
		$has_access,
		$course_progress
	);
}

/**
 * Output the Stars Input for the Review Form.
 *
 * @since 1.0.0
 *
 * @return void
 */
function learndash_course_reviews_stars_input(): void {
	learndash_course_reviews_locate_template( 'stars-input.php' );
}

/**
 * Outputs the Star Rating for a Review.
 *
 * @since 1.0.0
 *
 * @param float $rating Rating.
 *
 * @return void
 */
function learndash_course_reviews_star_rating( float $rating = 0 ): void {
	if ( ! is_numeric( $rating ) ) {
		$rating = 0;
	}

	learndash_course_reviews_locate_template(
		'star-rating.php',
		array(
			'rating' => $rating,
		)
	);
}

/**
 * Creates a Review based on passed Args.
 *
 * @since 1.0.0
 *
 * @param array<mixed> $args wp_insert_comment() args, with the addition of our own.
 *
 * @return int|false|WP_Error Integer on success, False on not logged in, WP_Error on failure.
 */
function learndash_course_reviews_add_review( array $args ) {
	if ( ! is_user_logged_in() ) {
		return false;
	}

	$user = wp_get_current_user();

	$review_author       = wp_slash( $user->display_name );
	$review_author_email = wp_slash( $user->user_email );
	$review_author_url   = wp_slash( $user->user_url );

	// phpcs:ignore WordPress.Security.ValidatedSanitizedInput.InputNotValidated, WordPress.Security.ValidatedSanitizedInput.InputNotSanitized -- WP Core does this too, see wp_new_comment().
	$comment_author_ip = wp_unslash( $_SERVER['REMOTE_ADDR'] );
	$comment_author_ip = preg_replace( '/[^0-9a-fA-F:., ]/', '', $comment_author_ip );

	$comment_agent = '';
	if ( ! empty( $_SERVER['HTTP_USER_AGENT'] ) ) {
		$comment_agent = sanitize_text_field(
			wp_unslash(
				$_SERVER['HTTP_USER_AGENT']
			)
		);
	}

	$args = wp_parse_args(
		$args,
		array(
			'comment_type'         => 'ld_review',
			'comment_parent'       => '',
			'comment_approved'     => true,
			'comment_date'         => current_time( 'mysql' ),
			'comment_date_gmt'     => current_time( 'mysql', true ),
			'user_id'              => get_current_user_id(),
			'comment_author'       => $review_author,
			'comment_author_email' => $review_author_email,
			'comment_author_url'   => $review_author_url,
			'comment_author_IP'    => $comment_author_ip,
			'comment_agent'        => $comment_agent,
		)
	);

	$comment_allowed = wp_allow_comment( $args, true );

	if ( is_wp_error( $comment_allowed ) ) {
		return $comment_allowed;
	}

	$review_id = wp_insert_comment( $args );

	if ( ! $review_id ) {
		return $review_id;
	}

	add_comment_meta(
		$review_id,
		'rating',
		$args['rating']
	);

	add_comment_meta(
		$review_id,
		'review_title',
		$args['review_title']
	);

	return $review_id;
}

/**
 * Checks if the given Email Address has submitted a Review before for a given Course.
 *
 * If no Email Address is defined, then the currently logged in User will be used.
 *
 * @since 1.0.0
 *
 * @param int    $course_id     Course ID.
 * @param string $email_address Email Address.
 *
 * @return int|false            Comment ID or false if not found.
 */
function learndash_course_reviews_get_user_review( int $course_id, string $email_address = '' ) {
	$args = array(
		'type'    => 'ld_review',
		'post_id' => $course_id,
		'parent'  => 0,
		'number'  => 1,
	);

	if ( empty( $email_address ) ) {
		if ( ! is_user_logged_in() ) {
			return false;
		}

		$args['user_id'] = get_current_user_id();
	} else {
		$args['author_email'] = $email_address;
	}

	$comments = get_comments( $args );

	if ( ! is_array( $comments ) ) {
		return false;
	}

	$comments = array_values(
		array_filter(
			$comments,
			function( $comment ) {
				return $comment instanceof WP_Comment;
			}
		)
	);

	if ( empty( $comments ) ) {
		return false;
	}

	return Cast::to_int( $comments[0]->comment_ID );
}

/**
 * Gets an Average Review Score for a given Course.
 *
 * @param int          $course_id Course ID.
 * @param array<mixed> $args      get_comments() args.
 *
 * @since 1.0.0
 * @return float|false            Average Review Score, false if no reviews found.
 */
function learndash_course_reviews_get_average_review_score( int $course_id, array $args = array() ) {
	$reviews = get_comments(
		wp_parse_args(
			$args,
			array(
				'post_id' => $course_id,
				'type'    => 'ld_review',
				'status'  => 'approve',
				'fields'  => 'ids',
			)
		)
	);

	if ( ! is_array( $reviews ) ) {
		return false;
	}

	$reviews = array_filter(
		$reviews,
		'is_int'
	);

	if ( empty( $reviews ) ) {
		return false;
	}

	$sum = 0;

	foreach ( $reviews as $comment_id ) {
		$rating = get_comment_meta( $comment_id, 'rating', true );

		if ( ! is_numeric( $rating ) ) {
			$rating = 0;
		}

		$sum = $sum + $rating;
	}

	return $sum / count( $reviews );
}
