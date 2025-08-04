<?php

//smart widget displayed on single course page.
//displayed only if a course has associated forum.

class LearnDash_Course_Forum_Widget extends WP_Widget {

	public function __construct() {
		$widget_ops = array('classname' => 'widget_ldcourseprogress', 'description' => __('Course Forum', 'learndash-bbpress'));
		$control_ops = array();//'width' => 400, 'height' => 350);
		parent::__construct('ldcourseforum', __('Course Forum', 'learndash-bbpress'), $widget_ops, $control_ops);
	}

	public function widget( $args, $instance ) {
		global $wpdb;
		extract( $args );
		$title = apply_filters( 'widget_title', empty( $instance['title'] ) ? '' : $instance['title'], $instance );

		if ( ! is_singular() ) {
			return;
		}
		
		$content_widget = ld_bbpress_get_object_forums_html();

		echo $before_widget;
		if ( !empty( $title ) ) { echo $before_title . $title . $after_title; } 
		
		echo $content_widget;
		echo $after_widget;
	}

	public function update( $new_instance, $old_instance ) {
		$instance = $old_instance;
		$instance['title'] = strip_tags($new_instance['title']);

		return $instance;
	}

	public function form( $instance ) {
		$instance = wp_parse_args( (array) $instance, array( 'title' => '') );
		$title = strip_tags($instance['title']);
	?>
		<p><label for="<?php echo $this->get_field_id('title'); ?>"><?php _e( 'Title:', 'learndash-bbpress' ); ?></label>
		<input class="widefat" id="<?php echo $this->get_field_id('title'); ?>" name="<?php echo $this->get_field_name('title'); ?>" type="text" value="<?php echo esc_attr($title); ?>" /></p>
	<?php
	}
}

add_action( 'widgets_init', function() {
	return register_widget( 'LearnDash_Course_Forum_Widget' );
} );