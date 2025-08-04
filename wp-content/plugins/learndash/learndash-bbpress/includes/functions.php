<?php
// Restrict participation in forum (replies & topic creation)

use Mpdf\Tag\P;

add_filter( 'bbp_current_user_can_publish_topics', 'ld_restrict_forum_participation' );
add_filter( 'bbp_current_user_can_publish_replies', 'ld_restrict_forum_participation' );
function ld_restrict_forum_participation( $can_post ) {

	// Always allow keymasters
	if ( bbp_is_user_keymaster() ) {
		return true;
	}

	if ( current_user_can( 'publish_topics' ) ) {
		$forum_id = bbp_get_forum_id();
		$user_id  = get_current_user_ID();

		$can_post = learndash_bbpress_user_has_forum_access( $user_id, $forum_id );
	}

	return $can_post;
}

// Disable topic subscription & favorite link for users except course students
add_filter( 'bbp_get_user_subscribe_link', 'ld_disable_topic_subscription', 10, 4 );
add_filter( 'bbp_get_user_favorites_link', 'ld_disable_topic_subscription', 10, 4 );
function ld_disable_topic_subscription( $html, $args, $user_id, $topic_id ) {
	// Always allow keymasters
	if ( bbp_is_user_keymaster() ) {
		return $html;
	}

	$forum_id = bbp_get_forum_id();

	$has_access = learndash_bbpress_user_has_forum_access( $user_id, $forum_id );
	if ( $has_access ) {
		return $html;
	} else {
		return '';
	}
}

// Restrict access to forum & topics completely & show take course message in place
add_filter( 'bbp_user_can_view_forum', 'ld_restrict_forum_access', 15, 3 );
function ld_restrict_forum_access( $can_view, $forum_id, $user_id ) {
	// Always allow keymasters
	if ( bbp_is_user_keymaster() ) {
		return true;
	}

	$allow_forum_view       = get_post_meta( $forum_id, '_ld_allow_forum_view', true );
	$message_without_access = get_post_meta( $forum_id, '_ld_message_without_access', true );
	$message_without_access = ! empty( $message_without_access ) ? $message_without_access : __( 'This forum is restricted to members of the associated course(s) and group(s).', 'learndash-bbpress' );

	$can_view = learndash_bbpress_user_has_forum_access( $user_id, $forum_id );

	$content = "<div id='bbpress-forums' class='ld-bbpress-forums'>
					<p class='pre-message'>" . $message_without_access . "</p>
				</div>";

	if ( $allow_forum_view == '1' ) {
		return true;
	}

	if ( ! $can_view ) {
		echo apply_filters( 'ld_forum_access_restricted_message', $content, $forum_id );
	}

	return $can_view;
}

// Show associated courses below the forum title in forum archive page
add_action( 'bbp_theme_after_forum_description', 'ld_associated_course_link' );
function ld_associated_course_link() {
	$content = '<span class="ld-bbpress-desc-link"><small><strong>' . __( 'Associated Courses and Groups', 'learndash-bbpress' ) . ':</strong>';
	$courses = get_post_meta( get_the_ID(), '_ld_associated_courses', true );
	$groups  = get_post_meta( get_the_ID(), '_ld_associated_groups', true );

	if ( is_array( $courses ) ) {
		foreach ( $courses as $course_id ) {
			if ( $course_id != null && $course_id > 0 )
			$content .= '<br /><a href="' . get_permalink( $course_id ) . '">' . get_the_title( $course_id ) . '</a>';
		}
	}

	if ( is_array( $groups ) ) {
		foreach ( $groups as $group_id ) {
			if ( $group_id != null && $group_id > 0 )
			$content .= '<br /><a href="' . get_permalink( $group_id ) . '">' . get_the_title( $group_id ) . '</a>';
		}
	}

	$content .= '</small></span>';
	if ( ! empty( $courses ) ) {
		echo $content;
	}
}

// Remove repetation of private twice in private forum titles
add_filter( 'bbp_get_forum_title', 'ld_bbp_forum_title', 10, 2 );
function ld_bbp_forum_title( $title, $forum_id ) {
	return str_replace( 'Private:', '', $title );
}

// Assign participant forum role to new students
add_action( 'learndash_update_course_access', 'ld_bbp_assign_role', 10, 4 );
function ld_bbp_assign_role( $user_id, $course_id, $access_list, $remove ) {
	if ( true === $remove ) {
		return;
	}

	$role = bbp_get_user_role( $user_id );
	if ( empty( $role ) || false === $role || 'bbp_spectator' === $role ) {
		bbp_set_user_role( $user_id, 'bbp_participant' );
	}
}

/**
 * Get course forums in HTML output
 *
 * @param  array  $args Arguments in key value pair
 * @return string       HTML of course forums list
 */
function ld_bbpress_get_object_forums_html( $args = [] ) {
    if ( empty( $args['object_id'] ) ) {
    	if ( is_singular( [ 'sfwd-courses', 'sfwd-lessons', 'sfwd-topic', 'sfwd-quiz' ] ) ) {
    		$object_id = learndash_get_course_id( get_the_ID() );
    	} elseif ( is_singular( [ 'groups' ] ) ) {
    		$object_id = get_the_ID();
    	}
    } else {
    	$object_id = $args['object_id'];
    }

    if ( is_bbpress() ) {
    	$forum_id = bbp_get_forum_id();

    	$forum_associated_courses = (array) get_post_meta( $forum_id, '_ld_associated_courses', true );
    	$forum_associated_groups = (array) get_post_meta( $forum_id, '_ld_associated_groups', true );
    	$forum_associated_objects = array_merge( $forum_associated_courses, $forum_associated_groups );

		$forum_associated_objects = array_filter( $forum_associated_objects, function( $value ) {
			return ! empty( $value ) && is_numeric( $value );
		} );
    }

    $forums = new WP_Query( array(
    	'post_type'           => bbp_get_forum_post_type(),
    	'post_status'         => bbp_get_public_status_id(),
    	'posts_per_page'      => get_option( '_bbp_forums_per_page', 50 ),
    	'ignore_sticky_posts' => true,
    	'no_found_rows'       => true,
    	'orderby'             => 'menu_order title',
    	'order'               => 'ASC',
    ) );

    // Bail if no posts
    if ( ! $forums->have_posts() ) {
    	return;
    }

    $html = '<div class="ld-bbpress-course-forums">';
    $html .= '<ul>';

    $associated_courses = array();
    $associated_groups  = array();
    $associated_objects = array();
    while ( $forums->have_posts() ) {
    	$forums->the_post();

    	$associated_courses = (array) get_post_meta( $forums->post->ID, '_ld_associated_courses', true );
    	$associated_groups  = (array) get_post_meta( $forums->post->ID, '_ld_associated_groups', true );
    	$associated_objects = array_merge( $associated_courses, $associated_groups );

		$associated_objects = array_filter( $associated_objects, function( $value ) {
			return ! empty( $value ) && is_numeric( $value );
		} );

    	if ( ! is_bbpress() ) {
    		if ( empty( $associated_objects ) ) {
    			continue;
    		} elseif ( is_array( $associated_objects ) && ! empty( $object_id ) && ! in_array( $object_id, $associated_objects ) ) {
    			continue;
    		}
    	} else {
    		if ( empty( $forum_associated_objects ) || empty( $associated_objects ) ) {
    			continue;
    		} elseif ( is_array( $associated_objects ) && is_array( $forum_associated_objects ) ) {
    			$intersect = array_intersect( $associated_objects, $forum_associated_objects );

    			if ( empty( $intersect ) ) {
    				continue;
    			}
    		}
    	}

    	if ( in_array( bbp_get_forum_visibility( $forums->post->ID ), array( 'hidden' ) ) ) {
    		$html .= "<li><a  href='#' onClick='return false;'>". $forums->post->post_title ."</a></li>";
    	} else {
    		$html .= "<li><a  href='".get_permalink( $forums->post->ID )."'>". $forums->post->post_title ."</a></li>";
    	}
    }

    wp_reset_query();

    $html .= '</ul>';
    $html .= '</div>';

    return $html;
}

/**
 * Get forum courses in HTML output
 *
 * @param  array  $args List of arguments
 * @return string       HTML output
 */
function ld_bbpress_get_forum_objects_html( $args = [] ) {
	if ( empty( $args['forum_id'] ) ) {
    	$forum_id = bbp_get_forum_id();
	} else {
		$forum_id = $args['forum_id'];
	}

	$forum_associated_courses = (array) get_post_meta( $forum_id, '_ld_associated_courses', true );
    $forum_associated_groups  = (array) get_post_meta( $forum_id, '_ld_associated_groups', true );

	if ( $args['show'] === 'course' ) {
		$forum_associated_objects = $forum_associated_courses;
	} elseif ( $args['show'] === 'group' ) {
		$forum_associated_objects = $forum_associated_groups;
	} elseif( $args['show'] === 'all' ) {
		$forum_associated_objects = array_merge( $forum_associated_courses, $forum_associated_groups );
	} else {
		$forum_associated_objects = array();
	}

	$forum_associated_objects = array_filter( $forum_associated_objects, function( $value ) {
		return ! empty( $value ) && is_numeric( $value );
	} );

    $html = '<div class="ld-bbpress-course-forums">';
    $html .= '<ul>';

	if ( is_array( $forum_associated_objects ) ) {
		foreach ( $forum_associated_objects as $object_id ) {
			if ( ! Learndash_Shortcodes::can_current_user_access_post( intval( $object_id ) ) ) {
				continue;
			}

			$title = get_the_title( $object_id );
			$permalink = get_the_permalink( $object_id );

			$html .= '<li><a href="' . $permalink . '">'. $title . '</a></li>';
		}
	}

    $html .= '</ul>';
    $html .= "</div>";

    return $html;
}

/**
 * Check if a user has access to a forum based on LearnDash bbPress setting
 *
 * @param int $user_id
 * @param int $forum_id
 * @return bool true if has access | false otherwise
 */
function learndash_bbpress_user_has_forum_access( $user_id, $forum_id )
{
	$associated_courses = get_post_meta( $forum_id, '_ld_associated_courses', true );
	$associated_groups  = get_post_meta( $forum_id, '_ld_associated_groups', true );
	$allow_post = get_post_meta( $forum_id, '_ld_post_limit_access', true );

	$has_access_course = false;
	$has_access_group  = false;

	if ( empty( $associated_courses ) && empty( $associated_groups ) ) {
		$has_access_course = true;
		$has_access_group = true;
	} elseif ( empty( $associated_courses ) && ! empty( $associated_groups ) ) {
		$has_access_course = true;
	} elseif ( ! empty( $associated_courses ) && empty( $associated_groups ) ) {
		$has_access_group = true;
	}

	if ( is_array( $associated_courses ) && ! empty( $associated_courses ) ) {
		foreach( $associated_courses as $associated_course ) {
			// Default value of $allow_post is 'all'
			if ( $allow_post == 'all' ) {
				if ( ! sfwd_lms_has_access( $associated_course, $user_id ) || ! is_user_logged_in() ) {
					return false;
				} else {
					$has_access_course = true;
				}
			} else {
				if ( sfwd_lms_has_access( $associated_course, $user_id ) && is_user_logged_in() ) {
					return true;
				} else {
					$has_access_course = false;
				}
			}
		}
	}

	if ( is_array( $associated_groups ) && ! empty( $associated_groups ) ) {
		foreach ( $associated_groups as $associated_group ) {
			// Default value of $allow_post is 'all'
			if ( $allow_post == 'all' ) {
				if ( ! learndash_is_user_in_group( $user_id, $associated_group ) || ! is_user_logged_in() ) {
					return false;
				} else {
					$has_access_group = true;
				}
			} else {
				if ( learndash_is_user_in_group( $user_id, $associated_group ) && is_user_logged_in() ) {
					return true;
				} else {
					$has_access_group = false;
				}
			}
		}
	}

	if ( $has_access_course && $has_access_group ) {
		return true;
	} else {
		return false;
	}
}
