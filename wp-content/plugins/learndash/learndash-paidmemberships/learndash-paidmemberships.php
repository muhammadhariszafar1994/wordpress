<?php
/**
 * Plugin Name: LearnDash LMS - Paid Memberships Pro
 * Plugin URI: http://www.learndash.com
 * Description: LearnDash integration with the Paid Memberships Pro plugin that allows to control the course's access by a user level.
 * Version: 1.3.5
 * Author: LearnDash
 * Author URI: http://www.learndash.com
 * Text Domain: learndash-paidmemberships
 * Doman Path: /languages/
 */

if ( ! class_exists( 'Learndash_Paidmemberships' ) ) {

class Learndash_Paidmemberships {
	/**
	 * Define constants used in the plugin
	 * 
	 * @return void
	 */
	public static function define_constants() 
	{
		// Plugin version
		if ( ! defined( 'LEARNDASH_PMP_VERSION' ) ) {
			define( 'LEARNDASH_PMP_VERSION', '1.3.5' ); 
		}

		// Plugin file
		if ( ! defined( 'LEARNDASH_PMP_FILE' ) ) {
			define( 'LEARNDASH_PMP_FILE', __FILE__ );
		}		

		// Plugin folder path
		if ( ! defined( 'LEARNDASH_PMP_PLUGIN_PATH' ) ) {
			define( 'LEARNDASH_PMP_PLUGIN_PATH', plugin_dir_path( __FILE__ ) );
		}

		// Plugin folder URL
		if ( ! defined( 'LEARNDASH_PMP_PLUGIN_URL' ) ) {
			define( 'LEARNDASH_PMP_PLUGIN_URL', plugin_dir_url( __FILE__ ) );
		}
	}

	/**
	 * Include necessary files on plugins_loaded hook
	 * 
	 * @return void
	 */
	public static function includes_on_plugins_loaded() 
	{
		include LEARNDASH_PMP_PLUGIN_PATH . 'includes/class-tools.php';
	}

	/**
	 * Include necessary files
	 * 
	 * @return void
	 */
	public static function includes()
	{
		include LEARNDASH_PMP_PLUGIN_PATH . 'includes/class-cron.php';
	}

	/**
	 * Update plugin data when the plugin is updated
	 *
	 * @return void
	 */
	public static function update_plugin_data()
	{
		$plugin_version = '1.1.0';
		$saved_version  = get_option( 'ld_pmpro_version' );

		if ( false === $saved_version || version_compare( $saved_version, $plugin_version, '<' ) ) {

			$lvl_courses = get_option( '_level_course_option' );

			if ( is_array( $lvl_courses ) ) {
				foreach ( $lvl_courses as $course_id => $level_string ) {
					self::delete_object_by_object_id( $course_id );

					if ( empty( trim( $level_string ) ) ) {
						continue;
					}

					$levels = explode( ',', $level_string );

					foreach ( $levels as $lvl ) {
						self::insert_object( $lvl, $course_id );
					}
				}
			}

			update_option( 'ld_pmpro_version', $plugin_version );
		}
	}

	/**
	 * Load language files
	 * 
	 * @return void
	 */
	public static function i18nize() 
	{
		load_plugin_textdomain( 'learndash-paidmemberships', false, dirname( plugin_basename( __FILE__ ) ) . '/languages/' ); 	

		// include translations class
		include LEARNDASH_PMP_PLUGIN_PATH . 'includes/class-translations-ld-paidmemberships.php';
	}

	/**
	 * Check plugin dependency
	 * 
	 * @return void
	 */
	public static function check_dependency() 
	{
		include LEARNDASH_PMP_PLUGIN_PATH . 'includes/class-dependency-check.php';
		
		LearnDash_Dependency_Check_LD_Paidmemberships::get_instance()->set_dependencies(
			array(
				'sfwd-lms/sfwd_lms.php' => array(
					'label'       => '<a href="https://learndash.com">LearnDash LMS</a>',
					'class'       => 'SFWD_LMS',
					'min_version' => '3.0.0',
				),
				'paid-memberships-pro/paid-memberships-pro.php' => array(
					'label'       => '<a href="https://paidmembershipspro.com">Paid Memberships Pro</a>',
					'class'       => '',
					'min_version' => '2.0.0',		
				)
			)
		);

		LearnDash_Dependency_Check_LD_Paidmemberships::get_instance()->set_message(
			esc_html__( 'LearnDash LMS Paid Memberships Pro Add-on requires the following plugin(s) to be active:', 'learndash-paidmemberships' )
		);
	}

	/**
	 * Register action and filter hooks used in the plugin
	 * 
	 * @return void
	 */
	public static function hooks()
	{
		if ( ! LearnDash_Dependency_Check_LD_Paidmemberships::get_instance()->check_dependency_results() ) {
			return;
		}

		Learndash_Paidmemberships::includes_on_plugins_loaded();

		add_action( 'init', array( 'Learndash_Paidmemberships', 'update_plugin_data' ) );
		add_action( 'plugins_loaded', array( 'Learndash_Paidmemberships', 'i18nize' ) );
		add_action( 'admin_init', array( 'Learndash_Paidmemberships', 'register_meta_box' ) );
		add_action( 'save_post', array( 'Learndash_Paidmemberships', 'save_object_settings' ), 10, 3 );

		add_action( 'admin_head', [ 'Learndash_Paidmemberships', 'admin_header_scripts' ] );
		add_action( 'admin_footer', [ 'Learndash_Paidmemberships', 'admin_footer_scripts' ] );

		add_action( 'wp_head', array( __CLASS__, 'frontend_header_scripts' ) );

		// Integration hooks
		add_action( 'pmpro_membership_level_after_other_settings', array( 'Learndash_Paidmemberships', 'output_level_settings' ) );
		add_action( 'pmpro_save_membership_level', array( 'Learndash_Paidmemberships', 'save_level_settings' ) );

		// Update course access when user change membership level
		add_action( 'pmpro_before_change_membership_level', array( 'Learndash_Paidmemberships', 'before_change_membership_level' ), 10, 4 );
		// Email confirmation addon hook
		add_action( 'pmproec_after_validate_user', array( 'Learndash_Paidmemberships', 'update_access_on_email_confirmation' ), 10, 2 );
		add_action( 'learndash_course_before', array( 'Learndash_Paidmemberships', 'object_email_confirmation_message' ), 1, 3 );
		add_action( 'learndash_group_before', array( 'Learndash_Paidmemberships', 'object_email_confirmation_message' ), 1, 3 );
		add_filter( 'sfwd_lms_has_access', array( __CLASS__, 'check_user_access' ), 10, 3 );
		// Update course access on member approval update
		add_action( 'update_user_meta', array( 'Learndash_Paidmemberships', 'update_access_on_approval' ), 10, 4 );
		// Update course access when an order is updated
		add_action( 'pmpro_updated_order', array( 'Learndash_Paidmemberships', 'update_object_access_on_order_update' ), 10, 1 );
		// Update course access when an order is deleted
		add_action( 'pmpro_delete_order', array( 'Learndash_Paidmemberships', 'remove_object_access_on_order_deletion' ), 10, 2 );
		// Update course access when a subscription is cancelled, failed, or payment refunded
		add_action( 'pmpro_subscription_expired', array( 'Learndash_Paidmemberships', 'remove_object_access_by_order' ), 10, 1 );

		// Regain access to course when subscription recurring is restarted
		add_action( 'pmpro_subscription_recuring_restarted', array( 'Learndash_Paidmemberships', 'add_object_access_by_order' ), 10, 1 );

		// Remove membership access message if user already has access to a particular course
		add_filter( 'pmpro_has_membership_access_filter', array( 'Learndash_Paidmemberships', 'has_object_access' ), 99, 4 ); // priority 99 to make sure the value is returned
	}

	/**
	 * Add scripts and styles to admin head
	 *
	 * @since 1.3.0
	 * @return void
	 */
	public static function admin_header_scripts() 
	{
		$screen = get_current_screen();

		if ( 
			( ! empty( $_GET['page'] ) && 'pmpro-membershiplevels' === $_GET['page'] && isset( $_GET['edit'] ) )
			|| $screen->post_type === 'groups'
			|| $screen->post_type === 'sfwd-courses'
		) {
			?>
			<style>
				.learndash .select2-container {
				    width: 100% !important;
				    border: 1px solid #ddd;
				    border-radius: 5px;
				}

				.learndash .select2-container ul {
				    width: 100%;
				}

				.learndash .select2-container li {
				    width: auto;
				    float: left;
				    border: 1px solid #ddd;
				    padding: 3px;
				    border-radius: 10px;
				    margin-right: 5px;
				}

				.learndash .select2-container li.select2-search {
				    clear: both;
				    border: none;
				    width: 99%;
				}

				.learndash .select2-container li.select2-search input {
				    width: 99% !important;
				    padding: 0 3px;
				    border: 1px solid #ddd;
				}

				.learndash .select2-container .select2-selection:focus {
				    outline: none;
				}

				/* Select2 Dropdown */
				.select2-container.select2-container--open .select2-dropdown {
				    border-color: #ddd;
				    border-top: 1px solid #ddd;
				}

				.select2-dropdown .select2-results__options {
				    max-height: 300px;
				    overflow: auto;
				}

				.select2-dropdown .select2-results__options .select2-results__option {
				    margin: 0;
				}

				.select2-dropdown .select2-results__options .select2-results__option[aria-selected="true"] {
				    background-color: #ddd;
				}
			</style>
			<?php
		}
	}

	/**
	 * Add scripts and styles to admin footer
	 *
	 * @since 1.3.0
	 * @return void
	 */
	public static function admin_footer_scripts()
	{
		$screen = get_current_screen();

		if (
			( ! empty( $_GET['page'] ) && 'pmpro-membershiplevels' === $_GET['page']  && isset( $_GET['edit'] ) )
			|| $screen->post_type === 'groups'
			|| $screen->post_type === 'sfwd-courses'
		) {
			?>
			<script type="text/javascript">
				jQuery( document ).ready( function( $ ) {
					$( '.select2' ).select2({
						closeOnSelect: false,
					});
				} );
			</script>
			<?php
		}
	}

	/**
	 * Print scripts and styles to frontend header tag
	 *
	 * @since 1.3.5
	 * @return void
	 */
	public static function frontend_header_scripts()
	{
		?>
		<style>
			.learndash-paidmemberships-notice.warning {
				background-color: #f5e942;
				border: 2px solid #f5da42;
				border-radius: 5px;
				padding: 10px 15px;
			}
		</style>
		<?php
	}

	/**
	 * Register meta box
	 * 
	 * @return void
	 */
	public static function register_meta_box() 
	{
		add_meta_box( 'credits_meta', 'Require Membership', array( 'Learndash_Paidmemberships', 'output_object_settings' ), [ 'sfwd-courses', 'groups' ], 'side', 'low' );
	}

	/**
	 * Output course meta box
	 * 
	 * @return void
	 */
	public static function output_object_settings() 
	{
		global $post, $wpdb;

		if ( ! isset( $wpdb->pmpro_membership_levels ) ) {
			_e( 'Please enable Paid Memberships Pro plugin and create some levels', 'learndash-paidmemberships' );
			return;
		}

		$membership_levels = $wpdb->get_results( "SELECT * FROM {$wpdb->pmpro_membership_levels}", OBJECT );
		
		$object_id = $post->ID;
		$level_course_option = get_option( '_level_course_option' );
		$array_levels = explode( ',', $level_course_option[ $object_id ] );

		wp_nonce_field( 'ld_pmpro_save_object_metabox', 'ld_pmpro_nonce' );
		
		?>
		<div class="learndash">
			<select name="level-curso[]" id="learndash-pmp-level" class="select2" multiple="">
			<?php
			for ( $num_cursos = 0; $num_cursos < sizeof( $membership_levels ); $num_cursos++ )
			{
				$selected = '';
				for ( $tmp_array_levels = 0; $tmp_array_levels < sizeof( $array_levels ); $tmp_array_levels++ ) {
					if ( $array_levels[ $tmp_array_levels ] == $membership_levels[ $num_cursos ]->id ) {	
						$selected = 'selected';
					}
				}
				?>
				<!-- <p><input type="checkbox" name="level-curso[<?php echo $num_cursos ?>]" value="<?php echo $membership_levels[ $num_cursos ]->id; ?>" <?php echo $checked; ?>> <?php echo $membership_levels[ $num_cursos ]->name; ?></p> -->

				<option value="<?php echo esc_attr( $membership_levels[ $num_cursos ]->id ) ?>" <?php echo $selected; ?> ><?php echo $membership_levels[ $num_cursos ]->name; ?></option>
				<?php
			}
			?>
			</select>
		</div>
		<?php
	}

	/**
	 * Save LearnDash course/group edit page LearnDash settings
	 * 
	 * @param  int    $post_id ID of a WP_Post
	 * @param  object $post    WP_Post object
	 * @param  bool   $update  Whether this action hook is an update
	 * @return void
	 */
	public static function save_object_settings( $post_id, $post, $update ) 
	{
		if ( ! current_user_can( 'publish_posts' ) ) {
			return;
		}

		if ( isset( $_POST['ld_pmpro_nonce'] ) && ! wp_verify_nonce( $_POST['ld_pmpro_nonce'], 'ld_pmpro_save_object_metabox' ) ) {
			return;
		}

		global $wpdb;
		$object_id = $post_id;

		if ( isset( $post->post_type ) && ( $post->post_type == 'sfwd-courses' || $post->post_type == 'groups' ) ) {
			$level_course_option = get_option( '_level_course_option' );

			if ( isset( $_POST['level-curso'] ) && is_array( $_POST['level-curso'] ) ) {
				$_POST['level-curso'] = array_map( 'intval', $_POST['level-curso'] );

				$access_list = array();
				$levels_list = array();

				// Delete old course page ID from pmpro_membership_pages table
				self::delete_object_by_object_id( $object_id );

				// Existing object level is not in current updated settings, so remove access for current object
				$object_levels = self::get_object_levels( $object_id );
				foreach ( $object_levels as $level_id ) {
					if ( ! in_array( $level_id, $_POST['level-curso'] ) ) {
						$new_objects = $old_objects = self::get_level_objects( $level_id );
						$object_key = array_search( $object_id, $new_objects );
						if ( $object_key ) {
							unset( $new_objects[ $object_key ] );

							self::enqueue_object_access_update( $level_id, $old_objects, $new_objects );
						}
					}
				}

				foreach ( $_POST['level-curso'] as $level_id ) {
					// Add new course page IDs to pmpro_membership_pages table
					self::insert_object( $level_id, $object_id );

					$object_levels = self::get_object_levels( $object_id );

					if ( ! in_array( $level_id, $object_levels ) ) {
						$new_objects = $old_objects = self::get_level_objects( $level_id );
						$new_objects[] = $object_id;

						self::enqueue_object_access_update( $level_id, $old_objects, $new_objects );
					}

					$levels_list[] = $level_id;
				}

				$levels_list_tmp = implode( ',', $levels_list );
				$level_course_option[ $object_id ] = $levels_list_tmp;
			} else {
				$objects_levels = get_option( '_level_course_option', array() );

				foreach ( $objects_levels as $level_object_id => $levels ) {
					if ( $object_id != $level_object_id ) {
						continue;
					}

					$levels = array_map( 'trim', explode( ',', $levels ) );

					foreach ( $levels as $level_id ) {
						$new_objects = $old_objects = self::get_level_objects( $level_id );
						$object_key = array_search( $object_id, $new_objects );
						if ( $object_key ) {
							unset( $new_objects[ $object_key ] );

							self::enqueue_object_access_update( $level_id, $old_objects, $new_objects );
						}
					}
				}

				// Delete old course page ID from pmpro_membership_pages table
				self::delete_object_by_object_id( $object_id );

				$level_course_option[ $object_id ] = '';
			}

			update_option( '_level_course_option', $level_course_option );
		}
	}

	/**
	 * Output settings for membership level add/edit page
	 * 
	 * @return void
	 */
	public static function output_level_settings() 
	{
		$courses = self::get_courses();
		$groups  = self::get_groups();
		$object_levels = get_option( '_level_course_option' );
		$current_level = $_REQUEST['edit'];

		wp_nonce_field( 'ld_pmpro_save_level_settings', 'ld_pmpro_nonce' );
		?>		
		<h3 class="topborder"><?php _e( 'LearnDash', 'learndash-paidmemberships' );?></h3>
		<table class="form-table learndash">
			<tbody>
				<tr>
					<th scope="row" valign="top"><label><?php _e( 'Courses', 'learndash-paidmemberships' ) ?>:</label></th>
					<td>
						<select name="cursos[]" id="cursos" class="select2" multiple>
							<?php foreach ( $courses as $course ) : ?>		
								<option value="<?php echo esc_attr( $course->ID ) ?>" <?php if ( in_array( $current_level, explode( ',', @$object_levels[ $course->ID ] ) ) ) echo 'selected ' ?>><?php echo $course->post_title ?></option>
							<?php endforeach; ?>
						</select>
					</td>
				</tr>
				<tr>
					<th scope="row" valign="top">
						<label><?php _e( 'Groups', 'learndash-paidmemberships' ) ?></label>
					</th>
					<td>
						<select name="learndash_groups[]" id="learndash-groups" class="select2" multiple>
							<?php foreach ( $groups as $group ) : ?>		
								<option value="<?php echo esc_attr( $group->ID ) ?>" <?php if ( in_array( $current_level, explode( ',', @$object_levels[ $group->ID ] ) ) ) echo 'selected ' ?>><?php echo $group->post_title ?></option>
							<?php endforeach; ?>
						</select>
					</td>
				</tr>
			</tbody>
		</table>
		<?php
	}

	/**
	 * Save level edit page LearnDash settings
	 * 
	 * @param  int    $level_id Membership level ID
	 * @return void
	 */
	public static function save_level_settings( $level_id ) 
	{
		if ( isset( $_POST['ld_pmpro_nonce'] ) && ! wp_verify_nonce( $_POST['ld_pmpro_nonce'], 'ld_pmpro_save_level_settings' ) ) {
			return;
		}

		$old_objects = self::get_level_objects( $level_id );

		$new_courses = isset( $_POST['cursos'] ) ? array_map( 'intval', $_POST['cursos'] ) : array();
		$new_groups  = isset( $_POST['learndash_groups'] ) ? array_map( 'intval', $_POST['learndash_groups'] ) : array();
		$new_objects = array_merge( $new_courses, $new_groups );

		$courses = self::get_courses();
		$groups  = self::get_groups();
		$objects = array_merge( $courses, $groups );

		$object_levels = get_option( '_level_course_option' );
		$object_levels = ! empty( $object_levels ) && is_array( $object_levels ) ? $object_levels : [];

		foreach ( $objects as $object ) {
			$refresh = false;
			$levels = @$object_levels[ $object->ID ] ? explode( ',', @$object_levels[ $object->ID ] ) : array();

			// If the course is in the level and it wasn't add it
			if ( array_search( $object->ID, $new_objects ) !== FALSE && array_search( $level_id, $levels ) === FALSE ) {
				$refresh = true;
				$levels[] = $level_id;
				$object_levels[ $object->ID ] = implode( ',', $levels );

				self::insert_object( $level_id, $object->ID );
			}

			// When the object is not in the level but it was
			else if ( array_search( $object->ID, $new_objects ) === FALSE && array_search( $level_id, $levels ) !== FALSE ){				
				$refresh = true;
				$level_index = array_search( $level_id, $levels );
				unset( $levels[ $level_index ] );
				$object_levels[ $object->ID ] = implode( ',', $levels );

				self::delete_object_by_membership_id_object_id( $level_id, $object->ID );
			}
		}

		self::enqueue_object_access_update( $level_id, $old_objects, $new_objects );

		update_option( '_level_course_option' , $object_levels );
	}

	/////////////////////
	/// Hooks Methods ///
	/////////////////////

	/**
	 * Update user course access on user memberhip level change
	 * 
	 * @param  int $level_id     ID of new membership level
	 * @param  int $user_id      ID of a WP_User
	 * @param  int $cancel_level ID of old membership level
	 * @return void
	 */
	public static function before_change_membership_level( $level_id, $user_id, $old_levels, $cancel_level ) 
	{
		// Add approval check if PMPro approval addon is active
		if ( class_exists( 'PMPro_Approvals' ) ) {
			if ( PMPro_Approvals::requiresApproval( $level_id ) && ! PMPro_Approvals::isApproved( $user_id, $level_id ) ) {
				return;
			}
		}

		// Add email confirmation check.
		if ( ! self::is_user_email_confirmed( $user_id, $level_id ) ) {
			return;
		}

		self::update_user_level_object_access_on_membership_change( $level_id, $user_id, $old_levels, $cancel_level );
	}

	/**
	 * Update user course access after email confirmation (requires email confirmation addon)
	 * 
	 * @param  int    $user_id  WP_User ID
	 * @param  string $validate User validation key or 'validated' if already validated
	 * @return void
	 */
	public static function update_access_on_email_confirmation( $user_id, $validate )
	{
		self::update_user_level_object_access_on_email_confirmation( $user_id );
	}

	/**
	 * Check if user has clicked email confirmation link sent by PMP Email
	 * confirmation extension.
	 *
	 * @param int $user_id
	 * @param int $level_id PMP membership level ID
	 * 
	 * @return bool True if confirmed or email confirmation extension 
	 * 				is not enabled|false otherwise.
	 */
	public static function is_user_email_confirmed( $user_id, $level_id )
	{
		if ( function_exists( 'pmproec_isEmailConfirmationLevel' ) && pmproec_isEmailConfirmationLevel( $level_id ) ) {
			$status = get_user_meta( $user_id, 'pmpro_email_confirmation_key', true );

			if ( ! empty( $status ) && $status === 'validated' ) {
				return true;
			} else {
				return false;
			}
		}

		// Default value when email confirmation is not enabled.
		return true;
	}

	/**
	 * Show email confirmation message on course page
	 * 
	 * @param  int 	  $post_id 		Post ID from get_the_ID()
	 * @param  int 	  $resource_id 	Course/Group ID
	 * @param  int 	  $user_id 		User ID
	 * @return void
	 */
	public static function object_email_confirmation_message( $post_id, $resource_id, $user_id )
	{
		$post = get_post( $resource_id );

		if ( 'sfwd-courses' == $post->post_type || 'groups' == $post->post_type ) {
			$membership_level   = pmpro_getMembershipLevelForUser();
			$membership_courses = get_option( '_level_course_option', [] );
			$resource_levels	= isset( $membership_courses[ $post->ID ] ) ? explode( ',', $membership_courses[ $post->ID ] ) : array();
			
			$user = wp_get_current_user();

			if ( ! self::is_user_email_confirmed( $user->ID, $membership_level->ID ) ) {
				// Check if course is part of group.
				if ( $post->post_type === 'sfwd-courses' ) {
					$course_groups_ids = learndash_get_course_groups( $post->ID );
					$course_groups_levels = array();
					foreach ( $course_groups_ids as $course_group_id ) {
						$course_group_levels = explode( ',', $membership_courses[ $course_group_id ] );
	
						$course_groups_levels = array_merge( $course_groups_levels, $course_group_levels );
					}
	
					if ( in_array( $membership_level->ID, $course_groups_levels ) ) {
						$is_course_part_of_group = true;
					}
				}
	
				if ( (
					isset( $membership_courses[ $post->ID ] )
					&& isset( $membership_level->ID )
					&& in_array( $membership_level->ID, $resource_levels )
				) || (
					$post->post_type === 'sfwd-courses'
					&& isset( $is_course_part_of_group )
					&& $is_course_part_of_group === true
				) ) {
					$message = '<p class="learndash-paidmemberships-notice warning">' . sprintf( _x( '<strong>Important! You must click on the confirmation URL sent to %s before you gain full access to your courses.</strong> The courses will be activated as soon as you confirm your email address.', 'User email address', 'learndash-paidmemberships' ), $user->user_email ) . '</p>';

					echo $message;
				}
			}
		}
	}

	/**
	 * Check user access for non validated user email when using PMP Email
	 * Confirmatio extension.
	 *
	 * @param bool 	$has_access Whether user has access to a resource
	 * @param int 	$post_id	Resource post ID
	 * @param int 	$user_id	User ID
	 * @return bool				Filtered $has_access value
	 */
	public static function check_user_access( $has_access, $post_id, $user_id )
	{
		if ( $has_access === true ) {
			$post = get_post( $post_id );

			if ( is_a( $post, 'WP_Post' ) && in_array( $post->post_type, array( 'sfwd-lessons', 'sfwd-topic', 'sfwd-quiz' ) ) ) {
				$course_id = learndash_get_course_id( $post->ID );
				if ( ! $course_id ) {
					return $has_access;
				}
	
				$post = get_post( $course_id );
			}
	
			if ( 'sfwd-courses' == $post->post_type || 'groups' == $post->post_type ) {
				$membership_level   = pmpro_getMembershipLevelForUser();
				$membership_courses = get_option( '_level_course_option', [] );
	
				if ( isset( $membership_courses[ $post->ID ] ) && isset( $membership_level->ID ) && in_array( $membership_level->ID, explode( ',', $membership_courses[ $post->ID ] ) ) ) {
					if ( ! self::is_user_email_confirmed( $user_id, $membership_level->ID ) ) {
						return false;
					}
				}
			}
		}

		return $has_access;
	}

	/**
	 * Update user course access on approval (requires approval add-on)
	 * 
	 * @param  int    $meta_id    ID of meta key
	 * @param  int    $object_id  ID of a WP_User
	 * @param  string $meta_key   Meta key
	 * @param  string $meta_value Meta value
	 * @return void
	 */
	public static function update_access_on_approval( $meta_id, $object_id, $meta_key, $meta_value ) 
	{
		preg_match( '/pmpro_approval_(\d+)/', $meta_key, $matches );

		if ( isset( $matches[0] ) && false !== strpos( $matches[0], 'pmpro_approval' ) ) {
			$level_id = $matches[1];

			if ( 'approved' == $meta_value['status'] ) {
				Learndash_Paidmemberships::update_object_access( $level_id, $object_id, false );
			} else {
				Learndash_Paidmemberships::update_object_access( $level_id, $object_id, $remove = true );
			}
		}
	}

	/**
	 * Update course access when order is updated
	 * 
	 * @param  object $order Object of an order
	 * @return void
	 */
	public static function update_object_access_on_order_update( $order ) 
	{		
		switch ( $order->status ) {
			case 'success':
				self::add_object_access_by_order( $order );
				break;
			
			case 'cancelled':
			case 'error':
			case 'pending':
			case 'refunded':
			case 'review':
				self::remove_object_access_by_order( $order );
				break;
		}
	}

	/**
	 * Remove user course access when an order is deleted
	 * 
	 * @param  int    $order_id ID of an order
	 * @param  object $order    Order object
	 * @return void
	 */
	public static function remove_object_access_on_order_deletion( $order_id, $order ) 
	{
		$level    = $order->getMembershipLevel();
		$user     = $order->getUser();

		self::update_object_access( $level->id, $user->ID, true );
	}

	/**
	 * Remove course access by given order
	 * 
	 * @param  object $order Order object
	 * @return void
	 */
	public static function remove_object_access_by_order( $order ) 
	{
		$level    = $order->getMembershipLevel();
		$user     = $order->getUser();

		self::update_object_access( $level->id, $user->ID, true );
	}

	/**
	 * Give LearnDash course and group access by given order
	 *
	 * @param object $order Order object
	 * @return void
	 */
	public static function add_object_access_by_order( $order ) 
	{
		$level = $order->getMembershipLevel();
		$user  = $order->getUser();

		self::update_object_access( $level->id, $user->ID, false );
	}

	/**
	 * Give user course access if he already has access to a particular course even though he's not a member of the course's membership
	 *
	 * @param bool    $hasaccess Whether user has access or not
	 * @param WP_Post $mypost Course WP_Post
	 * @param int     $myuser WP_User
	 * @param array   $mypost List of membership levels that protect this course
	 * @return boolean Returned $hasaccess
	 */
	public static function has_object_access( $hasaccess, $mypost, $myuser, $post_membership_levels ) 
	{
		if ( 'sfwd-courses' == $mypost->post_type || 'groups' == $mypost->post_type ) {
			$hasaccess = true;
		}

		return $hasaccess;
	}

	////////////
	/// Cron ///
	////////////

	/**
	 * Enqueue object access update
	 * @param  int    $level_id    Membership/level ID
	 * @param  array  $old_objects Array of object IDs
	 * @param  array  $new_objects Array of object IDs
	 * @return void
	 */
	public static function enqueue_object_access_update( $level_id, $old_objects, $new_objects )
	{
		sort( $old_objects );
		sort( $new_objects );
		if ( $old_objects != $new_objects ) {
			// Update associated course in DB so that it will be executed in cron
			$course_update_queue = get_option( 'learndash_pmp_object_access_update', array() );

			$course_update_queue[ $level_id ] = array(
				'old_objects'   => $old_objects,
				'new_objects'   => $new_objects,
			);

			update_option( 'learndash_pmp_object_access_update', $course_update_queue );
		}
	}

	/**
	 * Cron job: update user object access
	 */
	public static function cron_update_object_access()
	{
		// Get object update queue
		$updates = get_option( 'learndash_pmp_object_access_update', array() );

		foreach ( $updates as $level_id => $update ) {
			$batch   = $update['batch'] ?? 1;
			$members = self::get_active_members( $level_id, $update );

			$old_objects = $update['old_objects'] ?? [];
			$new_objects = $update['new_objects'] ?? [];

			$added_objects = array_diff( $new_objects, $old_objects );
			$removed_objects = array_diff( $old_objects, $new_objects );

			foreach ( $members as $member ) {
				foreach ( $removed_objects as $object_id ) {
					$post_type = get_post_type( $object_id );
					if ( $post_type == 'sfwd-courses' ) {
						ld_update_course_access( $member->user_id, $object_id, true );
					} elseif ( $post_type == 'groups' ) {
						ld_update_group_access( $member->user_id, $object_id, true );
					}
				}

				foreach ( $added_objects as $object_id ) {
					$post_type = get_post_type( $object_id );
					if ( $post_type == 'sfwd-courses' ) {
						ld_update_course_access( $member->user_id, $object_id );
					} elseif ( $post_type == 'groups' ) {
						ld_update_group_access( $member->user_id, $object_id );
					}
				}
			}

			if ( ! empty( $members )  ) {
				$updates[ $level_id ]['batch'] = $batch + 1;
				// Bail, still processing the same membership ID
				break;
			}

			unset( $updates[ $level_id ] );
			// Not necessary to bail since it can handle the next iteration since current iteration processes 0 member
		}

		update_option( 'learndash_pmp_object_access_update', $updates );
	}

	/**
	 * Enqueue object (course/group) enrollment in database for product with many courses
	 * 
	 * @param  array  $args Order args in this 
	 *                      key value pair: 
	 *                      'membership_id' => $level_id,
	 *                      'user_id' => $user_id,
	 *                      'action' => 'enroll' or 'unenroll'
	 * @return void
	 */
	public static function enqueue_silent_object_enrollment( $args ) {
	    $queue = get_option( 'learndash_pmp_silent_object_enrollment_queue', array() );

	    if ( ! empty( $args ) ) {
	        $queue[] = $args;
	    }

	    update_option( 'learndash_pmp_silent_object_enrollment_queue', $queue );
	}

	/**
	 * Process silent background course enrollment using cron
	 * 
	 * @return void
	 */
	public static function cron_process_silent_object_enrollment() {
		global $learndash_pmp_silent_enrollment;
		$learndash_pmp_silent_enrollment = true;

	    $queue = get_option( 'learndash_pmp_silent_object_enrollment_queue', array() );
	    $process_count = apply_filters( 'learndash_pmp_silent_enrollment_processes_per_batch', 5 );
	    $processed_queue = array_slice( $queue, 0, $process_count, true );

	    foreach ( $processed_queue as $key => $args ) {
	        if ( ! empty( $args ) ) {
				if ( $args['action'] == 'enroll' ) {
					self::update_object_access( $args['membership_id'], $args['user_id'], false );
				} elseif ( $args['action'] == 'unenroll' ) {
					self::update_object_access( $args['membership_id'], $args['user_id'], true );
				}
	        }

	        unset( $queue[ $key ] );

        	update_option( 'learndash_pmp_silent_object_enrollment_queue', $queue );
	    }
	}

	//////////////////////////
	/// Enrollment Methods ///
	//////////////////////////
	
	/**
	 * Update user LearnDash course/group access based on his/her active levels
	 * on approval email confirmation
	 * 
	 * @param  int    $user_id WP_User ID
	 * @return void
	 */
	public static function update_user_level_object_access_on_email_confirmation( $user_id )
	{
		$active_levels = pmpro_getMembershipLevelsForUser( $user_id );

		$active_levels_ids = array();
		if ( is_array( $active_levels ) ) {
			foreach ( $active_levels as $active_level ) {
				$active_levels_ids[] = $active_level->id;
			}
		}

		foreach ( $active_levels_ids as $active_level_id ) {
			Learndash_Paidmemberships::update_object_access( $active_level_id, $user_id, false );	
		}
	}

	/**
	 * Update user object access when his/her membership change
	 *
	 * @param int   $user_id	  User ID
	 * @param array $old_levels   Array of old level ids
	 * @return void
	 */
	public static function update_user_level_object_access_on_membership_change( $level_id, $user_id, $old_levels, $cancel_level ) 
	{
		if ( ! empty( $cancel_level ) && is_numeric( $cancel_level ) ) {
			Learndash_Paidmemberships::update_object_access( $cancel_level, $user_id, $remove = true );	
		}
		
		// Check if PMP Multiple Memberships Per User addon active.
		if ( ! function_exists( 'pmprommpu_init' ) ) {
			if ( is_array( $old_levels ) ) {
				foreach ( $old_levels as $old_level ) {
					Learndash_Paidmemberships::update_object_access( $old_level->id, $user_id, $remove = true );	
				}
			}
		}

		if ( ! empty( $level_id ) && is_numeric( $level_id ) ) {
			Learndash_Paidmemberships::update_object_access( $level_id, $user_id );
		}
	}

	/**
	 * Update LearnDash object (course and group) access
	 * 
	 * @param  int  $level_id	    ID of a membership level
	 * @param  int  $user_id 		ID of WP_User
	 * @param  boolean $remove  	True to remove course access|false otherwise
	 * @return void
	 */
	public static function update_object_access( $level_id, $user_id, $remove = false ) 
	{
		global $learndash_pmp_silent_enrollment;

		$objects = Learndash_Paidmemberships::get_level_objects( $level_id );

		if ( count( $objects ) >= self::get_min_courses_count_for_silent_course_enrollment() && ( ! isset( $learndash_pmp_silent_enrollment ) || ! $learndash_pmp_silent_enrollment ) ) {
			$action = $remove ? 'unenroll' : 'enroll';
			self::enqueue_silent_object_enrollment( [ 'membership_id' => $level_id, 'user_id' => $user_id, 'action' => $action ] );
		} else {
			foreach ( $objects as $object_id ) {
				if ( ! $remove ) {
					self::increment_object_access_counter( $object_id, $user_id, $level_id );
				} else {
					self::decrement_object_access_counter( $object_id, $user_id, $level_id );
				}

				self::toggle_object_access( $user_id, $object_id, $remove );
			}
		}
	}

	/**
	 * Toggle user object access
	 *
	 * @param int $user_id		User ID
	 * @param int $object_id	LearnDash object (course/group) ID
	 * @param boolean $remove	True if remove access|false if add access
	 * @return void
	 */
	public static function toggle_object_access( $user_id, $object_id, $remove = false )
	{
		if ( $remove && self::user_has_access_to_object( $user_id, $object_id ) ) {
			return;
		}

		$post_type = get_post_type( $object_id );

		switch ( $post_type ) {
			case 'sfwd-courses':
				ld_update_course_access( $user_id, $object_id, $remove );
				break;
			
			case 'groups':
				ld_update_group_access( $user_id, $object_id, $remove );
				break;
		}
	}
	
	/**
	 * Add new course page IDs to pmpro_membership_pages table
	 * 
	 * @since  1.0.7
	 * @param  int    $level_id 	ID of PMP membership level
	 * @param  int    $object_id        ID of a Learndash course or group
	 * @return void
	 */
	public static function insert_object( $level_id, $object_id )
	{
		global $wpdb;

		$count = $wpdb->get_var( $wpdb->prepare( "SELECT COUNT(*) FROM {$wpdb->pmpro_memberships_pages} WHERE membership_id = %d AND page_id = %d", $level_id, $object_id ) );

		if ( ! $count ) {
			$wpdb->insert(
				"{$wpdb->pmpro_memberships_pages}",
				array( 
					'membership_id' => $level_id,
					'page_id' => $object_id,
				),
				array( '%d', '%d' )
			);
		}
	}

	/**
	 * Delete course/group page ID from pmpro_membership_pages table
	 * 
	 * @since 1.0.7
	 * @param  int  $object_id ID of a LearnDash course or group
	 * @return void
	 */
	public static function delete_object_by_object_id( $object_id )
	{
		global $wpdb;

		$wpdb->delete(
			"{$wpdb->pmpro_memberships_pages}",
			array( 'page_id' => $object_id ),
			array( '%d' )
		);
	}

	/**
	 * Delete course/group page ID from pmpro_membership_pages table
	 * 
	 * @since 1.0.7
	 * @param  int  $level_id ID of a PMPro membership
	 * @param  int  $object_id     ID of a LearnDash course or group
	 * @return void
	 */
	public static function delete_object_by_membership_id_object_id( $level_id, $object_id )
	{
		global $wpdb;

		$wpdb->delete(
			"{$wpdb->pmpro_memberships_pages}",
			array( 'membership_id' => $level_id, 'page_id' => $object_id ),
			array( '%d', '%d' )
		);
	}

	///////////////
	/// Helpers	///
	///////////////

	/**
	 * Minimum courses count in a transaction so that its courses enrollment will be processed in the background using cron job
	 * @return int
	 */
	public static function get_min_courses_count_for_silent_course_enrollment() {
	    return apply_filters( 'learndash_pmp_min_courses_count_for_silent_object_enrollment', 5 );
	}

	/**
	 * Get PMP active members
	 * 
	 * @param 	int   $level_id Membership ID
	 * @param 	array $data Query args data
	 * @return 	array Members returned from DB query
	 */
	public static function get_active_members( $level_id, $data )
	{
		global $wpdb;

		$per_batch = apply_filters( 'learndash_pmp_cron_update_course_access_per_batch', 50 );
		$per_batch = intval( $per_batch / 2 );
		$batch = $data['batch'] ?? 1;
		$offset = ( $batch - 1 ) * $per_batch;

		$members = $wpdb->get_results( $wpdb->prepare( "SELECT * FROM {$wpdb->pmpro_memberships_users} WHERE membership_id = %d AND status = 'active' LIMIT %d OFFSET %d", $level_id, $per_batch, $offset ) );

		return $members;
	}

	/**
	 * Get the last order of a user by level ID
	 *
	 * @param int $user_id  User ID
	 * @param int $level_id Membership level ID
	 * @return mixed Integer if exists|empty string if not
	 */
	public static function get_last_order( $user_id, $level_id )
	{
		global $wpdb;

		$order_id = $wpdb->get_var( $wpdb->prepare( "SELECT id FROM {$wpdb->pmpro_membership_orders} WHERE user_id = %d AND membership_id = %d ORDER BY id DESC LIMIT 1", $user_id, $level_id ) );

		return $order_id;
	}

	/**
	 * Get LearnDash courses
	 *
	 * @since 1.3.0
	 * @return array Array of WP_Post objects
	 */
	public static function get_courses() 
	{
		return get_posts( [
			'post_type' => 'sfwd-courses',
			'posts_per_page' => -1,
			'orderby' => 'title',
			'order' => 'ASC',
		] );
	}

	/**
	 * Get LearnDash groups
	 *
	 * @since 1.3.0
	 * @return array Array of WP_Post objects
	 */
	public static function get_groups() 
	{
		return get_posts( [
			'post_type' => 'groups',
			'posts_per_page' => -1,
			'orderby' => 'title',
			'order' => 'ASC',
		] );
	}

	/**
	 * Get a membership level's associated courses
	 * 
	 * @param  int    $level ID of a membership level
	 * @return array         LearnDash courses and group IDs that belong to a level
	 */
	public static function get_level_objects( $level ) 
	{
		$objects_levels = get_option( '_level_course_option', array() );

		$objects = array();
		foreach ( $objects_levels as $object_id => $levels ) {
			$levels = explode( ',', $levels );
			if ( in_array( $level, $levels ) ) {
				$objects[] = $object_id;
			}
		}

		return $objects;
	}

	/**
	 * Get an object level IDs
	 * 
	 * @param  int    $level ID of a LearnDash obejct (course/group)
	 * @return array         PMP level IDs that belong to a LearnDash object
	 */
	public static function get_object_levels( $object_id )
	{
		$objects_levels = get_option( '_level_course_option', array() );

		if ( ! empty( $object_levels[ $object_id ] ) ) {
			return array_map( 'trim', explode( ',', $object_levels[ $object_id ] ) );
		} else {
			return [];
		}
	}

	/**
	 * Add enrolled LearnDash object record to a user
	 *
	 * @param int $object_id ID of a object (course/group)
	 * @param int $user_id   ID of a user
	 * @param int $level_id  ID of a membership level
	 */
	public static function increment_object_access_counter( $object_id, $user_id, $level_id )
	{
		$objects = self::get_objects_access_counter( $user_id );

		if ( isset( $objects[ $object_id ] ) && ! is_array( $objects[ $object_id ] ) ) {
			$objects[ $object_id ] = array();
		}

		if ( ! isset( $objects[ $object_id ] ) || ( isset( $objects[ $object_id] ) && array_search( $level_id, $objects[ $object_id ] ) === false ) ) {
			// Add order ID to object access counter
			$objects[ $object_id ][] = $level_id;
		}

		update_user_meta( $user_id, '_learndash_pmp_enrolled_objects_access_counter', $objects );

		return $objects;
	}

	/**
	 * Delete enrolled object record from a user
	 * 
	 * @param int $object_id ID of a object
	 * @param int $user_id   ID of a user
	 * @param int $level_id  ID of a membership level
	 */
	public static function decrement_object_access_counter( $object_id, $user_id, $level_id )
	{
		$objects = self::get_objects_access_counter( $user_id );
		
		if ( isset( $objects[ $object_id ] ) && ! is_array( $objects[ $object_id ] ) ) {
			$objects[ $object_id ] = array();
		}

		if ( isset( $objects[ $object_id ] ) ) {
			$keys = array_keys( $objects[ $object_id ], $level_id );
			if ( is_array( $keys ) ) {
				foreach ( $keys as $key ) {
					unset( $objects[ $object_id ][ $key ] );
				}
			}
		}

		update_user_meta( $user_id, '_learndash_pmp_enrolled_objects_access_counter', $objects );

		return $objects;
	}

	/**
	 * Reset object access counter
	 * 
	 * @param  int 	  $object_id object ID
	 * @param  int 	  $user_id   User ID
	 * @return void
	 */
	public static function reset_object_access_counter( $object_id, $user_id ) {
		$objects = self::get_objects_access_counter( $user_id );
		
		if ( isset( $objects[ $object_id ] ) ) {
			unset( $objects[ $object_id ] );
		}

		update_user_meta( $user_id, '_learndash_pmp_enrolled_objects_access_counter', $objects );
	}

	/**
	 * Get user enrolled object access counter
	 * 
	 * @param  int $user_id ID of a user
	 * @return array        object access counter array
	 */
	public static function get_objects_access_counter( $user_id )
	{
		$objects = get_user_meta( $user_id, '_learndash_pmp_enrolled_objects_access_counter', true );

		if ( ! empty( $objects ) ) {
			$objects = maybe_unserialize( $objects );
		} else {
			$objects = array();
		}
		
		return $objects;
	}

	/**
	 * Check whether a user has access to an object or not
	 *
	 * @param int $user_id		User ID
	 * @param int $object_id	LearDash object (course/group) ID
	 * @return boolean True if has access|false otherwise
	 */
	public static function user_has_access_to_object( $user_id, $object_id )
	{
		$objects = get_user_meta( $user_id, '_learndash_pmp_enrolled_objects_access_counter', true );

		if ( ! empty( $objects[ $object_id ] ) ) {
			return true;
		} else {
			return false;
		}
	}
} // end class

Learndash_Paidmemberships::define_constants();
Learndash_Paidmemberships::includes();
Learndash_Paidmemberships::check_dependency();
add_action( 'plugins_loaded', array( 'Learndash_Paidmemberships', 'hooks' ) );

} // end if class_exists