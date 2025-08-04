<?php
/**
 * Metabox
 *
 * @package     LearnDash\EDD\Metabox
 * @since       1.0.0
 */


// Exit if accessed directly
if( !defined( 'ABSPATH' ) ) exit;


/**
 * Register meta box for LearnDash
 *
 * @since       1.0.0
 * @return      void
 */
function learndash_edd_add_meta_box() {
    add_meta_box(
        'learndash',
        __( 'LearnDash EDD Integration', 'learndash-edd' ),
        'learndash_edd_render_meta_box',
        'download',
        'normal',
        'high'
    );
}
add_action( 'add_meta_boxes', 'learndash_edd_add_meta_box' );


/**
 * Render meta box
 *
 * @since       1.0.0
 * @global      object $post The post we are editing
 * @return      void
 */
function learndash_edd_render_meta_box() {
    global $post;

    $post_id            = $post->ID;
    $learndash_course   = get_post_meta( $post_id, '_edd_learndash_course', true );
    $learndash_course   = ! empty( $learndash_course ) ? $learndash_course : [];
    $learndash_group    = get_post_meta( $post_id, '_edd_learndash_group', true );
    $learndash_group    = ! empty( $learndash_group ) ? $learndash_group : [];

    $courses = get_posts( array( 
        'post_type' => 'sfwd-courses', 
        'posts_per_page' => -1, 
        'orderby' => 'post_title', 
        'order' => 'ASC',
    ) );
    $groups  = get_posts( array( 
        'post_type' => 'groups', 
        'posts_per_page' => -1, 
        'orderby' => 'post_title', 
        'order' => 'ASC',
    ) );

    $course_list = array();
    $group_list  = array();
    $selected_courses = array();
    $selected_groups  = array();

    if ( $courses ) {
        foreach ( $courses as $course ) {
            $course_list[ $course->ID ] = $course->post_title;
            if ( in_array( $course->ID, $learndash_course ) ) {
                $selected_courses[] = $course->ID;
            }
        }
    }

    if ( $groups ) {
        foreach ( $groups as $group ) {
            $group_list[ $group->ID ] = $group->post_title;
            if ( in_array( $group->ID, $learndash_group ) ) {
                $selected_groups[] = $group->ID;
            }
        }
    }
    $fields = new EDD_HTML_Elements();
    ?>

    <div id="edd_learndash_course_wrapper" class="edd_learndash_field_wrapper">
        <label class="edd-repeatable-row-setting-label" for="_edd_learndash_course"><?php _e( 'Associated Courses', 'learndash-edd' ); ?></label>
        <?php
            if ( $courses ) {
                echo $fields->select( array(
                    'id'               => '_edd_learndash_course',
                    'name'             => '_edd_learndash_course[]',
                    'class'            => 'select2',
                    'options'          => $course_list,
                    'multiple'         => true,
                    'selected'         => $selected_courses,
                    'chosen'           => false,
                    'show_option_none' => false,
                    'show_option_all'  => false,
                    'placeholder'      => __( 'Select one or more courses', 'learndash-edd' ),
                ) );
            } else {
                printf( __( 'No LearnDash courses found! Do you need to <a href="%s">create one</a>?', 'learndash-edd' ), admin_url( 'post-new.php?post_type=sfwd-courses' ) );
            }
        ?>
    </div>

    <div id="edd_learndash_group_wrapper" class="edd_learndash_field_wrapper">
        <label class="edd-repeatable-row-setting-label" for="_edd_learndash_group"><?php _e( 'Associated Groups', 'learndash-edd' ); ?></label>
        <?php
            if ( $groups ) {
                echo $fields->select( array(
                    'id'               => '_edd_learndash_group',
                    'name'             => '_edd_learndash_group[]',
                    'class'            => 'select2',
                    'options'          => $group_list,
                    'multiple'         => true,
                    'selected'         => $selected_groups,
                    'chosen'           => false,
                    'show_option_none' => false,
                    'show_option_all'  => false,
                    'placeholder'      => __( 'Select one or more groups', 'learndash-edd' ),
                ) );
            } else {
                printf( __( 'No LearnDash groups found! Do you need to <a href="%s">create one</a>?', 'learndash-edd' ), admin_url( 'post-new.php?post_type=groups' ) );
            }
        ?>
    </div>
    <?php
    
    wp_nonce_field( basename( __FILE__ ), 'learndash_edd_meta_box_nonce' );
}

function learndash_edd_variable_price_options( $key, $args, $post_id, $index ) {
    ?>
    <div class="edd-repeatable-row-standard-fields learndash-options">
        <h4><?php _e( 'LearnDash EDD Integration', 'learndash-edd' ) ?></h4>
        <div class="fields-wrapper">
            <?php learndash_edd_output_learndash_options( $post_id, $key ); ?>
        </div>
    </div>
    <?php
}

add_action( 'edd_render_price_row', 'learndash_edd_variable_price_options', 20, 4 );

/**
 * Save post meta when the save_post action is called
 *
 * @since       1.0.0
 * @param       int $post_id The ID of the post we are saving
 * @global      object $post The post we are saving
 * @return      void
 */
function learndash_edd_meta_box_save( $post_id ) {
    global $post;

    // Don't process if nonce can't be validated
    if ( ! isset( $_POST['learndash_edd_meta_box_nonce'] ) || ! wp_verify_nonce( $_POST['learndash_edd_meta_box_nonce'], basename( __FILE__ ) ) ) {
        return $post_id;
    }

    // Don't process if this is an autosave
    if ( ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) || ( defined( 'DOING_AJAX' ) && DOING_AJAX ) || isset( $_REQUEST['bulk_edit'] ) ) {
        return $post_id;   
    }

    // Don't process if this is a revision
    if ( isset( $post->post_type ) && $post->post_type == 'revision' ) {
        return $post_id;
    }

    // Don't process if the current user shouldn't be editing this product
    if ( ! current_user_can( 'edit_product', $post_id ) ) {
        return $post_id;
    }    

    if ( isset( $_POST['_variable_pricing'] ) && $_POST['_variable_pricing'] == 1 ) {
        $ld_courses = ! empty( $_POST['_edd_learndash_course_variable'] ) ? $_POST['_edd_learndash_course_variable'] : [];
        $old_courses = (array) learndash_edd_get_variable_product_courses( $post_id );
        array_walk_recursive( $ld_courses, function( &$value ) {
            if ( ! is_array( $value ) ) {
                $value = intval( $value );
            }
        } );

        $ld_groups = ! empty( $_POST['_edd_learndash_group_variable'] ) ? $_POST['_edd_learndash_group_variable'] : [];
        $old_groups = (array) learndash_edd_get_variable_product_groups( $post_id );
        array_walk_recursive( $ld_groups, function( &$value ) {
            if ( ! is_array( $value ) ) {
                $value = intval( $value );
            }
        } );

        // Update associated course in DB so that it will be executed in cron
        $course_update_queue = get_option( 'learndash_edd_course_access_update', array() );

        $course_update_queue[ $post_id ] = array(
            'type'          => 'variable',
            'old_courses'   => $old_courses,
            'new_courses'   => $ld_courses,
            'old_groups'    => $old_groups,
            'new_groups'    => $ld_groups,
        );

        update_option( 'learndash_edd_course_access_update', $course_update_queue );

        update_post_meta( $post_id, '_edd_learndash_course_variable', $ld_courses );
        update_post_meta( $post_id, '_edd_learndash_group_variable', $ld_groups );

    } else {
        $ld_courses = ! empty( $_POST['_edd_learndash_course'] ) ? $_POST['_edd_learndash_course'] : [];
        $old_courses = (array) get_post_meta( $post_id, '_edd_learndash_course', true );
        $new_courses = array_unique( (array) array_map( 'intval', $ld_courses ) );

        $ld_groups = ! empty( $_POST['_edd_learndash_group'] ) ? $_POST['_edd_learndash_group'] : [];
        $old_groups = (array) get_post_meta( $post_id, '_edd_learndash_group', true );
        $new_groups = array_unique( (array) array_map( 'intval', $ld_groups ) );

        // Update associated course in DB so that it will be executed in cron
        $course_update_queue = get_option( 'learndash_edd_course_access_update', array() );

        $course_update_queue[ $post_id ] = array(
            'type'          => 'single',
            'old_courses'   => $old_courses,
            'new_courses'   => $new_courses,
            'old_groups'    => $old_groups,
            'new_groups'    => $new_groups,
        );

        update_option( 'learndash_edd_course_access_update', $course_update_queue );

        update_post_meta( $post_id, '_edd_learndash_course', $new_courses );
        update_post_meta( $post_id, '_edd_learndash_group', $new_groups );
    }
}
add_action( 'save_post', 'learndash_edd_meta_box_save' );

function learndash_edd_output_learndash_options( $post_id, $key ) {
    $learndash_course = learndash_edd_get_variable_product_courses( $post_id, $key );
    $learndash_group  = learndash_edd_get_variable_product_groups( $post_id, $key );

    $courses = get_posts( array( 
        'post_type' => 'sfwd-courses', 
        'posts_per_page' => -1, 
        'orderby' => 'post_title', 
        'order' => 'ASC',
    ) );
    
    $groups  = get_posts( array( 
        'post_type' => 'groups', 
        'posts_per_page' => -1, 
        'orderby' => 'post_title', 
        'order' => 'ASC',
    ) );

    $course_list = array();
    $group_list  = array();
    $selected_courses = array();
    $selected_groups  = array();

    if ( ! empty( $courses ) && is_array( $courses ) ) {
        foreach ( $courses as $course ) {
            $course_list[ $course->ID ] = $course->post_title;
            if ( in_array( $course->ID, $learndash_course ) ) {
                $selected_courses[] = $course->ID;
            }
        }
    }

    if ( ! empty( $groups ) && is_array( $groups ) ) {
        foreach ( $groups as $group ) {
            $group_list[ $group->ID ] = $group->post_title;
            if ( in_array( $group->ID, $learndash_group ) ) {
                $selected_groups[] = $group->ID;
            }
        }
    }
    $fields = new EDD_HTML_Elements();
    ?>

    <div id="edd_learndash_course_wrapper" class="edd_learndash_field_wrapper">
        <label class="edd-repeatable-row-setting-label" for="_edd_learndash_course"><?php _e( 'Associated Courses', 'learndash-edd' ); ?></label>
        <?php
            if ( $courses ) {
                echo $fields->select( array(
                    'id'               => '_edd_learndash_course_variable_' . $key,
                    'name'             => '_edd_learndash_course_variable[' . $key .  '][]',
                    'class'            => 'select2',
                    'options'          => $course_list,
                    'multiple'         => true,
                    'selected'         => $selected_courses,
                    'chosen'           => false,
                    'show_option_none' => false,
                    'show_option_all'  => false,
                    'placeholder'      => __( 'Select one or more courses', 'learndash-edd' ),
                ) );
            } else {
                printf( __( 'No LearnDash courses found! Do you need to <a href="%s">create one</a>?', 'learndash-edd' ), admin_url( 'post-new.php?post_type=sfwd-courses' ) );
            }
        ?>
    </div>

    <div id="edd_learndash_group_wrapper" class="edd_learndash_field_wrapper">
        <label class="edd-repeatable-row-setting-label" for="_edd_learndash_group"><?php _e( 'Associated Groups', 'learndash-edd' ); ?></label>
        <?php
            if ( $groups ) {
                echo $fields->select( array(
                    'id'               => '_edd_learndash_group_variable_' . $key,
                    'name'             => '_edd_learndash_group_variable[' . $key .  '][]',
                    'class'            => 'select2',
                    'options'          => $group_list,
                    'multiple'         => true,
                    'selected'         => $selected_groups,
                    'chosen'           => false,
                    'show_option_none' => false,
                    'show_option_all'  => false,
                    'placeholder'      => __( 'Select one or more groups', 'learndash-edd' ),
                ) );
            } else {
                printf( __( 'No LearnDash groups found! Do you need to <a href="%s">create one</a>?', 'learndash-edd' ), admin_url( 'post-new.php?post_type=groups' ) );
            }
        ?>
    </div>
    <?php
}