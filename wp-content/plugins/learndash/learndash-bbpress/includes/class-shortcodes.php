<?php
if ( ! defined( 'ABSPATH' ) ) {
    exit();
}

/**
 * Learndash_Shortcodes
 */
class Learndash_Shortcodes {

    /**
     * Init the class
     * @return void
     */
    public static function init() {
        add_action( 'init', [ self::class, 'register_shortcodes' ] );
    }

    /**
     * Register shortcode tags and its callback
     * @return void
     */
    public static function register_shortcodes() {
        add_shortcode( 'ld_bbpress_object_forums', [ self::class, 'ld_bbpress_object_forums' ] );
        add_shortcode( 'ld_bbpress_forum_objects', [ self::class, 'ld_bbpress_forum_objects'] );
    }

    /**
     * Output value for shortcode tag [ld_bbpress_object_forums]
     * @param  array  $atts Shortcode attributes
     * @return string       Output value
     */
    public static function ld_bbpress_object_forums( $atts ) {
        $atts = shortcode_atts( [
            'object_id' => null,
        ], $atts );

        return ld_bbpress_get_object_forums_html( $atts );
    }

    /**
     * Output value for shortcode tag [ld_bbpress_forum_objects]
     * @param  array  $atts Shortcode attributes
     * @return string       Output value
     */
    public static function ld_bbpress_forum_objects( $atts ) {
        $atts = shortcode_atts( [
            'forum_id' => null,
            'show' => 'all',
        ], $atts );

        return ld_bbpress_get_forum_objects_html( $atts );
    }

	/**
	 * Checks if the current user can access the post.
	 *
	 * If the post ID is not set, the current user can access the post. It allows the shortcode's optional parameters to be skipped.
	 * If the post is password protected, then only admins can access the post.
	 * If the current user is a guest, the user can only access published posts.
	 * If the current user is logged in, they can only access posts they have access to.
	 *
	 * @since 2.2.4
	 *
	 * @param int $post_id Post ID.
	 *
	 * @return bool
	 */
	public static function can_current_user_access_post( int $post_id ): bool {
		// We have to duplicate the learndash_shortcode_can_current_user_access_post function content,
		// because it's available since LearnDash Core version 4.11.0 only.
		if ( function_exists( 'learndash_shortcode_can_current_user_access_post' ) ) {
			return learndash_shortcode_can_current_user_access_post( $post_id );
		}

		/**
		 * If post ID is not set, assume the user can access it. It allows shortcode's optional parameters to be skipped.
		 *
		 * Some shortcodes have optional post IDs parameters, such as course_id, group_id, step_id, etc.
		 * This check allows us to pass the post IDs without checking if they're set.
		 *
		 * See includes/shortcodes/ld_course_content.php in LD Core for example. Users can pass course_id, group_id, and post_id, but they're optional.
		 */

		if ( $post_id <= 0 ) {
			return true;
		}

		$current_user_id = get_current_user_id();

		// Admins can access any post.

		if ( learndash_is_admin_user( $current_user_id ) ) {
			return true;
		}

		// Only admins can access password protected posts.

		if ( post_password_required( $post_id ) ) {
			return false;
		}

		// If guest user, check if the post is published.

		if ( $current_user_id <= 0 ) {
			return get_post_status( $post_id ) === 'publish';
		}

		// If logged in user, check if the user has access to the post.

		$post_type_object = get_post_type_object(
			(string) get_post_type( $post_id )
		);

		return (
			$post_type_object instanceof WP_Post_Type
			&& user_can( $current_user_id, $post_type_object->cap->read_post, $post_id )
		);
	}
}

Learndash_Shortcodes::init();
