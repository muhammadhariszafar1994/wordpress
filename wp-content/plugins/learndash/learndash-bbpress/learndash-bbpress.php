<?php
/**
* Plugin Name: LearnDash LMS - bbPress Integration
* Plugin URI: http://www.learndash.com
* Description: LearnDash integration with the bbPress plugin that allows to create private forums for user's enrolled to a course.
* Version: 2.2.4
* Author: LearnDash
* Author URI: http://www.learndash.com
* Text Domain: learndash-bbpress
* Domain Path: languages
* Requires PHP: 7.4
*/

// Plugin version
if ( ! defined( 'LEARNDASH_BBPRESS_VERSION' ) ) {
	define( 'LEARNDASH_BBPRESS_VERSION', '2.2.4' );
}

// Plugin file
if ( ! defined( 'LEARNDASH_BBPRESS_FILE' ) ) {
	define( 'LEARNDASH_BBPRESS_FILE', __FILE__ );
}

// Plugin folder path
if ( ! defined( 'LEARNDASH_BBPRESS_PLUGIN_PATH' ) ) {
	define( 'LEARNDASH_BBPRESS_PLUGIN_PATH', plugin_dir_path( __FILE__ ) );
}

// Plugin folder URL
if ( ! defined( 'LEARNDASH_BBPRESS_PLUGIN_URL' ) ) {
	define( 'LEARNDASH_BBPRESS_PLUGIN_URL', plugin_dir_url( __FILE__ ) );
}

if ( ! class_exists( 'Learndash_BBPress' ) ) {

class Learndash_BBPress {

	function __construct() {
		add_action( 'plugins_loaded', array( $this, 'load_textdomain' ) );

		$this->check_dependency();

		add_action( 'plugins_loaded', function() {
			if ( Learndash_Dependency_Check_LD_Bbpress::get_instance()->check_dependency_results() ) {
				add_action( 'admin_enqueue_scripts', [ $this, 'admin_enqueue_scripts' ] );
				add_action( 'add_meta_boxes', array( $this, 'ld_display_course_selector' ) );
				add_action( 'save_post_forum', array( $this, 'ld_save_associated_object' ) );
				$this->includes();
			}
		} );
   	}

   	/**
   	 * Check plugin dependencies
   	 * @return void
   	 */
   	function check_dependency()
   	{
   		include LEARNDASH_BBPRESS_PLUGIN_PATH . 'includes/class-dependency-check.php';

   		Learndash_Dependency_Check_LD_Bbpress::get_instance()->set_dependencies(
   			array(
   				'sfwd-lms/sfwd_lms.php' => array(
   					'label'       => '<a href="https://learndash.com">LearnDash LMS</a>',
   					'class'       => 'SFWD_LMS',
   					'min_version' => '3.0.0',
   				),
   				'bbpress/bbpress.php' => array(
   					'label'       => '<a href="https://bbpress.org/">bbPress</a>',
   					'class'       => 'bbPress',
   					'min_version' => '2.0.0',
   				),
   			)
   		);

   		Learndash_Dependency_Check_LD_Bbpress::get_instance()->set_message(
   			__( 'LearnDash LMS - bbPress Add-on requires the following plugin(s) to be active:', 'learndash-2checkout' )
   		);
   	}

   	/**
	 * Load text domain used for translation
	 *
	 * This function loads mo and po files used to translate text strings used throughout the
	 * plugin.
	 *
	 * @since 1.3.0
	 */
	public function load_textdomain() {

		global $wp_version;
		// Set filter for plugin language directory
		$lang_dir = dirname( plugin_basename( __FILE__ ) ) . '/languages/';
		$lang_dir = apply_filters( 'ld_bbpress_languages_directory', $lang_dir );

		$get_locale = get_locale();

		if ( $wp_version >= '4.7' ) {
			$get_locale = get_user_locale();
		}

		$mofile = sprintf( '%s-%s.mo', 'learndash-bbpress', $get_locale );
		$mofile = WP_LANG_DIR . 'plugins/' . $mofile;

		if ( file_exists( $mofile ) ) {
			load_textdomain( 'learndash-bbpress', $mofile );
		} else {
			load_plugin_textdomain( 'learndash-bbpress', $deprecated = false, $lang_dir );
		}

		// include translation/update class
		include LEARNDASH_BBPRESS_PLUGIN_PATH . 'includes/class-translations-ld-bbpress.php';
	}

	/**
	 * Enqueue scripts and styles on admin pages
	 *
	 * @return void
	 */
	public function admin_enqueue_scripts()
	{
		$screen = get_current_screen();
		if ( $screen->post_type === 'forum' ) {
			wp_enqueue_script( 'learndash-bbpress-admin-scripts', LEARNDASH_BBPRESS_PLUGIN_URL . 'assets/js/admin.js', [ 'jquery', 'learndash-select2-jquery-script' ], LEARNDASH_BBPRESS_VERSION, true );

			wp_localize_script( 'learndash-bbpress-admin-scripts', 'Learndash_BBPress', [
				'string' => [
					'placeholder' => __( 'Click to select', 'learndash-bbpress' ),
				]
			] );

			wp_enqueue_style( 'learndash-bbpress-admin-styles', LEARNDASH_BBPRESS_PLUGIN_URL . 'assets/css/admin.css', [ 'learndash-select2-jquery-style' ], LEARNDASH_BBPRESS_VERSION, 'all' );
		}
	}

	public function includes(){
		require_once( plugin_dir_path( __FILE__ ) . 'includes/functions.php');
		require_once( plugin_dir_path( __FILE__ ) . 'includes/class-shortcodes.php');
		require_once( plugin_dir_path( __FILE__ ) . 'includes/class-course-forum-widget.php');
		require_once( plugin_dir_path( __FILE__ ) . 'includes/class-forum-course-widget.php');
	}

	public function ld_display_course_selector() {
    	add_meta_box( 'ld_course_selector', __( 'LearnDash bbPress Settings', 'learndash-bbpress' ), array($this, 'ld_display_course_selector_callback'), 'forum', 'advanced', 'high' );
	}

	public function ld_display_course_selector_callback(){

		wp_nonce_field( 'ld_bbpress_meta_box', 'ld_bbpress_nonce' );

		$courses = $this->ld_get_course_list();
		$groups  = $this->ld_get_group_list();

		$associated_courses     = get_post_meta( get_the_ID(), '_ld_associated_courses', true );
		$associated_groups      = get_post_meta( get_the_ID(), '_ld_associated_groups', true );
		$limit_post_access      = get_post_meta( get_the_ID(), '_ld_post_limit_access', true );
		$allow_forum_view       = get_post_meta( get_the_ID(), '_ld_allow_forum_view', true );
		$message_without_access = get_post_meta( get_the_ID(), '_ld_message_without_access', true );
		$message_without_access = ! empty( $message_without_access ) ? $message_without_access : __( 'This forum is restricted to members of the associated course(s) and group(s).', 'learndash-bbpress' );
		$selected = null;
		?>

			<script>
				jQuery( document ).ready( function( $ ){
					$( '#ld_clearcourse' ).click( function( e ) {
						e.preventDefault();
						$( "#ld_course_selector_dd option:selected" ).each( function() {
								$( this ).removeAttr( 'selected' ); //or whatever else
						} );
					} );
				});
			</script>

			<table class="form-table">
				<tbody>
				<tr>
					<td>
						<label for="ld_course_selector_dd"><strong><?php _e( 'Associated Course(s)', 'learndash-bbpress' ); ?>: </strong></label>
						<select name="ld_course_selector_dd[]" size="4" id="ld_course_selector_dd" class="select2" multiple="multiple">
							<?php if ( is_array( $courses ) ) {
							foreach ( $courses as $course ) {
								$selected = null;
								if ( is_array( $associated_courses ) && in_array( $course->ID, $associated_courses ) ) {
									$selected = "selected";
								} ?>
								<option value="<?php echo $course->ID; ?>" <?php echo $selected; ?>><?php echo get_the_title( $course->ID ); ?></option>
							<?php } } ?>
						</select>
					</td>
				</tr>
				<tr>
					<td>
						<label for="ld_group_selector_dd"><strong><?php _e( 'Associated Group(s)', 'learndash-bbpress' ); ?>: </strong></label>
						<select name="ld_group_selector_dd[]" size="4" id="ld_group_selector_dd" class="select2" multiple="multiple">
							<?php if ( is_array( $groups ) ) :
								foreach( $groups as $group ) :
									$selected = null;
									if ( is_array( $associated_groups ) && in_array( $group->ID, $associated_groups ) ){
										$selected = "selected";
									} ?>
									<option value="<?php echo $group->ID; ?>" <?php echo $selected; ?>><?php echo get_the_title( $group->ID ); ?></option>
								<?php endforeach;
							endif; ?>
						</select>
					</td>
				</tr>
				<tr>
					<td>
						<label for="ld_post_limit_access"><strong><?php _e( 'Post Limit Access', 'learndash-bbpress' ); ?>: </strong></label>
						<select name="ld_post_limit_access" id="ld_post_limit_access">
							<option value="all" <?php selected( 'all', $limit_post_access, true ); ?>><?php _e( 'All', 'learndash-bbpress' ); ?></option>
							<option value="any" <?php selected( 'any', $limit_post_access, true ); ?>><?php _e( 'Any', 'learndash-bbpress' ); ?></option>
						</select>
						<p class="desc"><?php _e( 'If you select ALL, then users must have access to all of the associated courses and groups in order to post.', 'learndash-bbpress' ); ?></p>
						<p class="desc"><?php _e( 'If you select ANY, then users only need to have access to any one of the selected courses or groups in order to post.', 'learndash-bbpress' ); ?></p>
					</td>
				</tr>
				<tr>
					<td>
						<label for="ld_message_without_access"><strong><?php _e( 'Message shown to users without access', 'learndash-bbpress' ); ?>: </strong></label>
						<textarea cols="100" rows="5" name="ld_message_without_access" id="ld_message_without_access"><?php echo esc_attr( $message_without_access ); ?></textarea>
					</td>
				</tr>
				<tr>
					<td>
						<label><strong><?php _e( 'Forum View', 'learndash-bbpress' ); ?>: </strong></label>
						<input type="hidden" name="ld_allow_forum_view" value="0">
						<label for="ld_allow_forum_view">
							<input type="checkbox" name="ld_allow_forum_view" id="ld_allow_forum_view" value="1" <?php checked( '1', $allow_forum_view, true ); ?>>&nbsp;<?php _e( 'Check this box to allow non-enrolled users to view forum threads and topics (they will not be able to post replies).', 'learndash-bbpress' ); ?>
						</label>
					</td>
				</tr>
				</tbody>
			</table>
		 <?php
	}

	public function ld_save_associated_object( $post_id ) {
		if ( ! isset( $_POST['ld_bbpress_nonce'] ) ) {
			return;
		}

		if ( ! wp_verify_nonce( $_POST['ld_bbpress_nonce'], 'ld_bbpress_meta_box' ) ) {
			return;
		}

		$old_associated_courses = get_post_meta( $post_id, '_ld_associated_courses', true );
		$old_associated_groups  = get_post_meta( $post_id, '_ld_associated_groups', true );

		if ( isset( $_POST['ld_course_selector_dd'] ) && ! empty( $_POST['ld_course_selector_dd'] ) ) {
			update_post_meta( $post_id, '_ld_associated_courses', array_map( function( $value ) { return intval( $value ); }, $_POST['ld_course_selector_dd'] ) );
		} else {
			delete_post_meta( $post_id, '_ld_associated_courses' );
		}

		if ( isset( $_POST['ld_group_selector_dd'] ) && ! empty( $_POST['ld_group_selector_dd'] ) ) {
			update_post_meta( $post_id, '_ld_associated_groups', array_map( function( $value ) { return intval( $value ); }, $_POST['ld_group_selector_dd'] ) );
		} else {
			delete_post_meta( $post_id, '_ld_associated_groups' );
		}

		$new_associated_courses = get_post_meta( $post_id, '_ld_associated_courses', true );
		$new_associated_groups  = get_post_meta( $post_id, '_ld_associated_groups', true );

		if ( ! empty( $old_associated_courses ) ) {
			foreach( $old_associated_courses as $old_course ) {
				delete_post_meta( $old_course, '_ld_associated_forum_' . $post_id );
			}
		}

		if ( ! empty( $new_associated_courses ) ) {
			foreach( $new_associated_courses as $new_course ) {
				update_post_meta( $new_course, '_ld_associated_forum_' . $post_id, $post_id );
			}
		}

		if ( ! empty( $old_associated_groups ) ) {
			foreach( $old_associated_groups as $old_group ) {
				delete_post_meta( $old_group, '_ld_associated_forum_' . $post_id );
			}
		}

		if ( ! empty( $new_associated_groups ) ) {
			foreach( $new_associated_groups as $new_group ) {
				update_post_meta( $new_group, '_ld_associated_forum_' . $post_id, $post_id );
			}
		}

		// Save post limit access option
		update_post_meta( $post_id, '_ld_post_limit_access', sanitize_text_field( $_POST['ld_post_limit_access'] ) );

		update_post_meta( $post_id, '_ld_message_without_access', wp_kses_post( $_POST['ld_message_without_access'] ) );

		update_post_meta( $post_id, '_ld_allow_forum_view', sanitize_text_field( $_POST['ld_allow_forum_view'] ) );
	}

	public function ld_get_course_list() {
		$args = array(
			'posts_per_page'   => -1,
			'post_type'        => 'sfwd-courses',
			'post_status'      => 'publish'
		);

		$courses = get_posts( $args );
		return $courses;
	}

	public function ld_get_group_list()
	{
		return get_posts( [
			'post_type' => 'groups',
			'posts_per_page' => -1,
			'post_status' => 'publish',
		] );
	}
} // Class Learndash_BBPress

} // Endif class_exists
new Learndash_BBPress();
