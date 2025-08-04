<?php

if ( ! defined( 'ABSPATH' ) ) {
    exit();
}

class LearnDash_Forum_Course_Widget extends WP_Widget {
    /**
     * Init the class
     */
    public function __construct() {
        $widget_args = array(
            'classname' => 'ld-forum-course-widget',
            'description' => __( 'Display courses links that belong to a particular forum. This widget will be displayed only on bbpress pages.', 'learndash-bbpress' ),
        );

        $control_args = array();

        parent::__construct( 'ld_forum_course', __( 'Forum Course', 'learndash-bbpress' ), $widget_args, $control_args );
    }

    /**
     * Output widget form on admin page
     * @param  array  $instance Widget instance values
     * @return void
     */
    public function form( $instance ) {
        ?>

        <p>
            <label for="<?php echo $this->get_field_id( 'title' ); ?>"><?php _e( 'Title', 'learndash-bbpress' ); ?></label>
            <input class="widefat" id="<?php echo $this->get_field_id( 'title' ); ?>" name="<?php echo $this->get_field_name( 'title' ); ?>" type="text" value="<?php echo esc_attr( $instance['title'] ); ?>" />
        </p>

        <?php
    }

    /**
     * Update course instance values
     * @param  array  $new_instance Widget instance values
     * @param  array  $instance     Existing/old instance values
     * @return array                New sanitized values
     */
    public function update( $new_instance, $instance ) {
        $instance['title'] = sanitize_text_field( $new_instance['title'] );

        return $instance;
    }

    /**
     * Output widget HTML
     * @param  array  $args     Widget args
     * @param  array  $instance Widget inputs
     * @return void
     */
    public function widget( $args, $instance ) {
        if ( ! is_bbpress() ) {
            return;
        }

        $title = apply_filters( 'widget_title', empty( $instance['title'] ) ? '' : $instance['title'], $instance );
        $instance['show'] = $instance['show'] ?? 'all';
        
        $instance = apply_filters( 'learndash_bbpress_forum_course_widget_instance_args', $instance );
        
        $widget_content = ld_bbpress_get_forum_objects_html( $instance );
        
        echo $args['before_widget'];
        if ( ! empty( $title ) ) { 
            echo $args['before_title'] . $title . $args['after_title'];
        } 
        
        echo $widget_content;
        echo $args['after_widget'];
    }
}

add_action( 'widgets_init', function() {
    register_widget( 'LearnDash_Forum_Course_Widget' );
} );