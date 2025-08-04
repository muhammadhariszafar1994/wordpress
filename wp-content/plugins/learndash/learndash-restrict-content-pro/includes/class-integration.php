<?php
/**
* Integration class
*/
class Learndash_Restrict_Content_Pro_Integration
{
	
	public function __construct() {
		global $wpdb;

		// Forms and save meta hooks
		add_action( 'rcp_add_subscription_form', array( $this, 'learndash_add_row' ) );
		add_action( 'rcp_edit_subscription_form', array( $this, 'learndash_edit_row' ) );
		add_action( 'rcp_add_subscription', array( $this, 'save_level_meta' ), 10, 2 );
		add_action( 'rcp_edit_subscription_level', array( $this, 'save_level_meta' ), 10, 2 );

		add_action( 'rcp_transition_membership_status', array( $this, 'transition_membership_status' ), 99, 3 );

		add_action( 'rcp_new_membership_added', [ $this, 'new_membership_added' ], 10, 2 );
		add_action( 'rcp_membership_post_disable', array( $this, 'membership_post_disable' ), 10, 2 );

		//////////////////////////////
		// RCP Group Accounts Hooks //
		//////////////////////////////

		// Group deletion handler for RCPGA < 2.0
		add_filter( $wpdb->prefix . 'rcp_group_members_pre_delete', array( $this, 'group_members_pre_delete' ), 10, 1 );

		// Group deletion handler for RCPGA > 2.0
		add_action( 'init', array( $this, 'store_membership_id_on_delete_group' ), 1 ); // Need high priority so it's fired before group record deletion
		add_action( 'rcpga_db_groups_post_delete', array( $this, 'groups_post_delete' ), 1 ); // Need high priority so it'll be fired before rcpga_delete_all_members_of_group() function

		// RCP group account addon hooks handler
		add_action( 'rcpga_add_member_to_group_after', array( $this, 'enroll_group_member_to_courses' ), 10, 2 );
		add_action( 'rcpga_remove_member', array( $this, 'unenroll_group_member_from_courses' ), 10, 2 );
	}

	public function learndash_fields( int $level_id = 0 )
	{
		$courses = self::get_learndash_courses();
		$groups  = self::get_learndash_groups();

		$saved_courses = [];
		$saved_groups = [];
		if ( ! empty( $level_id ) ) {
			$level_obj = new RCP_Levels();
			$saved_courses = (array) $level_obj->get_meta( $level_id, '_learndash_restrict_content_pro_courses', true );
			$saved_groups  = (array) $level_obj->get_meta( $level_id, '_learndash_restrict_content_pro_groups', true );
		}


		?>
		<!-- Courses -->
		<tr class="form-field">
			<th scope="row" valign="top">
				<label for="rcp-role"><?php _e( 'LearnDash Courses', 'learndash-restrict-content-pro' ); ?></label>
			</th>
			<td>
				<select name="_learndash_restrict_content_pro_courses[]" multiple="multiple">
					<?php foreach ( $courses as $course ) : ?>
					<option value="<?php echo esc_attr( $course->ID ); ?>" <?php self::selected( $course->ID, $saved_courses ); ?>>
						<?php echo esc_attr( $course->post_title ); ?>
					</option>
					<?php endforeach; ?>
				</select>
				<p class="description"><?php _e( 'LearnDash courses you want to associate this membership level with. Hold ctrl on Windows or cmd on Mac to select multiple courses.', 'learndash-restrict-content-pro' ); ?></p>
			</td>
		</tr>
		<!-- Groups -->
		<tr class="form-field">
			<th scope="row" valign="top">
				<label for="rcp-role"><?php _e( 'LearnDash Groups', 'learndash-restrict-content-pro' ); ?></label>
			</th>
			<td>
				<select name="_learndash_restrict_content_pro_groups[]" multiple="multiple">
					<?php foreach ( $groups as $group ) : ?>
					<option value="<?php echo esc_attr( $group->ID ); ?>" <?php self::selected( $group->ID, $saved_groups ); ?>>
						<?php echo esc_attr( $group->post_title ); ?>
					</option>
					<?php endforeach; ?>
				</select>
				<p class="description"><?php _e( 'LearnDash groups you want to associate this membership level with. Hold ctrl on Windows or cmd on Mac to select multiple groups.', 'learndash-restrict-content-pro' ); ?></p>
			</td>
		</tr>
		<?php
	}

	/**
	 * Output course select HTML on RCP subscription add page
	 */
	public function learndash_add_row() {
		$this->learndash_fields();
	}

	/**
	 * Output course select HTML on RCP subscription edit page
	 * 
	 * @param  object  $level Restrict Content Pro sub level object
	 */
	public function learndash_edit_row( $level ) {
		$this->learndash_fields( $level->id );
	}

	/**
	 * Save Restrict Content Pro meta
	 * 
	 * @param  int    $level_id ID of RCP_Levels
	 * @param  array  $product  Restrict Content Pro product information
	 */
	public function save_level_meta( $level_id, $args ) {
		$level = new RCP_Levels();

		$old_courses = (array) $level->get_meta( $level_id, '_learndash_restrict_content_pro_courses', true );
		$new_courses = array_map( 'intval', $args['_learndash_restrict_content_pro_courses'] ?? [] );

		$old_groups = (array) $level->get_meta( $level_id, '_learndash_restrict_content_pro_groups', true );
		$new_groups = array_map( 'intval', $args['_learndash_restrict_content_pro_groups'] ?? [] );

		// Queue associated courses/groups update in DB so that it will be executed by cron job
		$course_update_queue = get_option( 'learndash_restrict_content_pro_course_access_update', array() );

		$course_update_queue[] = array(
			'level_id'    => $level_id,
			'old_courses' => $old_courses,
			'new_courses' => $new_courses,
			'old_groups'  => $old_groups,
			'new_groups'  => $new_groups,
		);

		update_option( 'learndash_restrict_content_pro_course_access_update', $course_update_queue );

		$level->update_meta( $level_id, '_learndash_restrict_content_pro_courses', $new_courses );
		$level->update_meta( $level_id, '_learndash_restrict_content_pro_groups', $new_groups );
	}

	/**
	 * Cron job: update user course access
	 */
	public static function cron_update_course_access()
	{
		// Get course update queue
		$updates = get_option( 'learndash_restrict_content_pro_course_access_update', array() );

		foreach ( $updates as $key => $update ) {
			$old_courses = $update['old_courses'] ?: array();
			$new_courses = $update['new_courses'] ?: array();

			$old_groups = $update['old_groups'] ?: array();
			$new_groups = $update['new_groups'] ?: array();

			$per_batch = 50;
			$batch  = $update['batch'] ?? 1;
			$offset = ( $batch - 1 ) * $per_batch;

			if ( defined( 'RCP_PLUGIN_VERSION' ) && version_compare( RCP_PLUGIN_VERSION, '3.0', '>=' ) ) {
				$memberships = rcp_get_memberships( array(
					'number'      => $per_batch,
					'offset'	  => $offset,
					'object_type' => 'membership',
					'object_id'   => $update['level_id'],
					'status'	  => 'active',
				) );

				// Remove or give access for each membership
				foreach ( $memberships as $membership ) {
					$customer = $membership->get_customer();
					$user_id  = $customer->get_user_id();

					foreach ( $old_courses as $course_id ) {
						self::remove_course_access( $course_id, $user_id, $membership->get_id() );
					}

					foreach ( $new_courses as $course_id ) {
						self::add_course_access( $course_id, $user_id, $membership->get_id() );
					}

					foreach ( $old_groups as $group_id ) {
						self::remove_group_access( $group_id, $user_id, $membership->get_id() );
					}

					foreach ( $new_groups as $group_id ) {
						self::add_group_access( $group_id, $user_id, $membership->get_id() );
					}
				}

				if ( ! empty( $memberships ) ) {
					$next_batch = $batch + 1;
				} else {
					$next_batch = false;
				}
			} else {
				$members = rcp_get_members( $status = 'active', $update['level_id'], $offset, $per_batch );
				// Remove or give access for each member
				foreach ( $members as $member ) {
					$member = new RCP_Member( $member->ID );

					foreach ( $old_courses as $course_id ) {
						self::remove_course_access( $course_id, $member->ID, $member->get_subscription_id() );
					}

					foreach ( $new_courses as $course_id ) {
						self::add_course_access( $course_id, $member->ID, $member->get_subscription_id() );
					}

					foreach ( $old_groups as $group_id ) {
						self::remove_group_access( $group_id, $member->ID, $member->get_subscription_id() );
					}

					foreach ( $new_groups as $group_id ) {
						self::add_group_access( $group_id, $member->ID, $member->get_subscription_id() );
					}
				}

				if ( ! empty( $members ) ) {
					$next_batch = $batch + 1;
				} else {
					$next_batch = false;
				}
			}

			if ( $next_batch ) {
				// Only run 1 udpate per process
				$updates[ $key ]['batch'] = $next_batch;
				break;
			} else {
				unset( $updates[ $key ] );
			}
		}

		update_option( 'learndash_restrict_content_pro_course_access_update', $updates );
	}

	/**
	 * Update user course access when member is updated
	 *
	 * For RCP 3.0+
	 * 
	 * @param  string $old_status    Old membership status
	 * @param  string $new_status    New membership status
	 * @param  int    $membership_id Membership ID
	 * @return void
	 */
	public function transition_membership_status( $old_status, $new_status, $membership_id ) 
	{
		$membership = rcp_get_membership( $membership_id );
		$user_id    = self::get_user_id_from_membership_id( $membership_id );

		if ( 'active' === $new_status || 'free' === $new_status ) {
			self::add_access_to_user( $user_id, $membership_id );
			self::add_access_for_group_members( $membership_id );
		} else {
			if ( 'expired' === $new_status || 'none' === $membership->calculate_expiration() ) {
				self::remove_access_from_user( $user_id, $membership_id );
				self::remove_access_from_group_members( $membership_id );
			}

		}
	}

	/**
	 * Enroll user to a course when a new membership is added
	 * especially when user switches membership
	 * 
	 * @param  int    $membership_id New membership ID
	 * @param  array  $data          Membership data
	 * @return void
	 */
	public function new_membership_added( $membership_id, $data )
	{
		$status = $data['status'];

		if ( $status === 'active' || $status === 'free' ) {
			$user_id = $data['user_id'];

			self::add_access_to_user( $user_id, $membership_id );
			self::add_access_for_group_members( $membership_id );
		}
	}

	/**
	 * Update user course access when membership is disabled/deleted.
	 *
	 * For RCP 3.0+.
	 * 
	 * @param  int    $membership_id Membership ID
	 * @param  object $membership    Membership object
	 * @return void
	 */
	public function membership_post_disable( $membership_id, $membership ) {
		$user_id = self::get_user_id_from_membership_id( $membership_id );
		$members = self::get_group_members_by_membership_id( $membership_id );

		self::remove_access_from_user( $user_id, $membership_id );

		foreach ( $members as $member ) {
			self::remove_access_from_user( $member->user_id, $membership_id );
		}
	}

	/**
	 * Store user role in transient before being deleted so the plugin can 
	 * use it
	 * 
	 * @param  int    $user_id WP_User ID
	 * @return int             WP_User ID
	 */
	public function group_members_pre_delete( $user_id ) {
		$user_role = rcpga_group_accounts()->members->get_role( $user_id );

		set_transient( 'ld_rcp_member_role_' . $user_id, $user_role, MINUTE_IN_SECONDS );

		return $user_id;
	}

	/**
	 * Store membership ID of a group in transient
	 *
	 * Upon group deletion, the group record in DB is already deleted so we
	 * can't pull any data.
	 * 
	 * @return void
	 */
	public function store_membership_id_on_delete_group() {
		if ( empty( $_REQUEST['rcpga-action'] ) || 'delete-group' != $_REQUEST['rcpga-action'] ) {
			return;
		}

		if ( empty( $_REQUEST['_wpnonce'] ) || ! wp_verify_nonce( $_REQUEST['_wpnonce'], 'rcpga_delete_group_nonce' ) ) {
			return;
		}

		$group_id = ! empty( $_REQUEST['rcpga-group'] ) ? absint( $_REQUEST['rcpga-group'] ) : 0;

		if ( empty( $group_id ) ) {
			return;
		}

		global $wpdb;
		$group_table   = rcpga_group_accounts()->table_groups->get_table_name();
		$membership_id = $wpdb->get_var( $wpdb->prepare( "SELECT membership_id FROM {$group_table} WHERE group_id = %d", $group_id ) );

		set_transient( 'ld_rcp_membership_id_' . $group_id, $membership_id, MINUTE_IN_SECONDS );
	}

	/**
	 * RCP group account addon rcpga_add_member_to_group_after hook handler
	 *
	 * Enroll group member to courses associated with the group.
	 * 
	 * @param int 	 $user_id WP_User ID
	 * @param array  $args    Member addition function args
	 *                        Sample:
	 *                        [user_email] => test.rcp.1@localhost.dev
	 *                        [group_id] => 1
	 *                        [send_invite] => 1
	 *                        [user_login] => test.rcp.1
	 *                        [first_name] => First
	 *                        [last_name] => Last
	 *                        [user_pass] => password123
	 *                        [role] => rcp-invited
	 * @return void
	 */
	public function enroll_group_member_to_courses( $user_id, $args ) {
		$membership_id = rcpga_group_accounts()->groups->get_membership_id( $args['group_id'] );

		if ( ! $membership_id ) {
			return;
		}

		self::add_access_to_user( $user_id, $membership_id );
	}

	/**
	 * RCP group account addon rcpga_remove_member hook handler
	 *
	 * Unenroll group member from courses associated with the group.
	 * 
	 * @param  int    $user_id  WP_User ID
	 * @param  int    $group_id RCP Group ID
	 * @return void
	 */
	public function unenroll_group_member_from_courses( $user_id, $group_id ) {
		$membership_id = rcpga_group_accounts()->groups->get_membership_id( $group_id );

		if ( ! $membership_id ) {
			return;
		}

		$user_role  = rcpga_group_accounts()->members->get_role( $user_id );
		if ( empty( $user_role ) ) {
			$user_role = get_transient( 'ld_rcp_member_role_' . $user_id );
			delete_transient( 'ld_rcp_member_role_' . $user_id );
		}

		if ( $user_role !== 'owner' ) {
			self::remove_access_from_user( $user_id, $membership_id );
		}
	}

	/**
	 * Remove group members course access upon group deletion
	 * 
	 * @param  int    $group_id RCP group ID
	 * @return void
	 */
	public function groups_post_delete( $group_id ) {
		if ( ! function_exists( 'rcpga_group_accounts' ) ) {
			return;
		}

		global $wpdb;
		$group_table = rcpga_group_accounts()->table_groups->get_table_name();
		$group_members_table = rcpga_group_accounts()->table_group_members->get_table_name();
		$members = $wpdb->get_results( $wpdb->prepare(
			"SELECT * FROM {$group_members_table} WHERE group_id = %d AND role != 'owner'",
			$group_id
		) );
		$membership_id = get_transient( 'ld_rcp_membership_id_' . $group_id );
		$course_ids = self::get_course_ids_from_membership_id( $membership_id );
		$group_ids  = self::get_group_ids_from_membership_id( $membership_id );
		delete_transient( 'ld_rcp_membership_id_' . $group_id );

		foreach ( $members as $member ) {
			foreach ( $course_ids as $course_id ) {
				self::remove_course_access( $course_id, $member->user_id, $membership_id );
			}

			foreach ( $group_ids as $group_id ) {
				self::remove_group_access( $group_id, $member->user_id, $membership_id );
			}
		}
	}

	/**
	 * Get WP_User ID from RCP membership ID
	 * @param  int    $membership_id RCP membership ID
	 * @return int                   WP_User ID of the membership owner
	 */
	public static function get_user_id_from_membership_id( $membership_id ) {
		$membership = rcp_get_membership( $membership_id );
		$customer   = $membership->get_customer();
		$user_id    = $customer->get_user_id();

		return $user_id;
	}

	/**
	 * Get LearnDash course IDs from a membership ID
	 * @param  int    $membership_id RCP membership ID
	 * @return array                 List of LearnDash courses assoicated with
	 *                               the membership ID
	 */
	public static function get_course_ids_from_membership_id( $membership_id ) {
		$membership = rcp_get_membership( $membership_id );
		$level_id   = $membership->get_object_id();
		$level      = new RCP_Levels();
		$course_ids = (array) $level->get_meta( $level_id, '_learndash_restrict_content_pro_courses', true );

		if ( ! empty( $course_ids ) ) {
			return $course_ids;
		} else {
			return array();
		}
	}

	/**
	 * Get LearnDash group IDs from a membership ID
	 * @param  int    $membership_id RCP membership ID
	 * @return array                 List of LearnDash groups assoicated with
	 *                               the membership ID
	 */
	public static function get_group_ids_from_membership_id( $membership_id )
	{
		$membership = rcp_get_membership( $membership_id );
		$level_id   = $membership->get_object_id();
		$level      = new RCP_Levels();
		$group_ids  = (array) $level->get_meta( $level_id, '_learndash_restrict_content_pro_groups', true );

		if ( ! empty( $group_ids ) ) {
			return $group_ids;
		} else {
			return array();
		}
	}

	/**
	 * Add accesss to user using membership ID.
	 * 
	 * @param int    $user_id       WP_User ID
	 * @param int    $membership_id RCP Membership ID
	 */
	public static function add_access_to_user( $user_id, $membership_id )
	{
		$ld_courses = self::get_course_ids_from_membership_id( $membership_id );
		$ld_groups  = self::get_group_ids_from_membership_id( $membership_id );

		foreach ( $ld_courses as $course_id ) {
			self::add_course_access( $course_id, $user_id, $membership_id );
		}

		foreach ( $ld_groups as $group_id ) {
			self::add_group_access( $group_id, $user_id, $membership_id );
		}
	}

	/**
	 * Remove access from user using membership ID.
	 * 
	 * @param int    $user_id       WP_User ID
	 * @param int    $membership_id RCP Membership ID
	 */
	public static function remove_access_from_user( $user_id, $membership_id )
	{
		$ld_courses = self::get_course_ids_from_membership_id( $membership_id );
		$ld_groups  = self::get_group_ids_from_membership_id( $membership_id );

		foreach ( $ld_courses as $course_id ) {
			self::remove_course_access( $course_id, $user_id, $membership_id );
		}

		foreach ( $ld_groups as $group_id ) {
			self::remove_group_access( $group_id, $user_id, $membership_id );
		}
	}

	/**
	 * Add course/group access for group members
	 * 
	 * @param int    $membership_id RCP membership ID
	 */
	public static function add_access_for_group_members( int $membership_id ) {
		$members = self::get_group_members_by_membership_id( $membership_id );

		foreach ( $members as $member ) {
			self::add_access_to_user( $member->user_id, $membership_id );
		}
	}

	/**
	 * Remove course/group access from group members.
	 * 
	 * @param int    $membership_id RCP membership ID
	 */
	public static function remove_access_from_group_members( int $membership_id ) {
		$members = self::get_group_members_by_membership_id( $membership_id );

		foreach ( $members as $member ) {
			self::remove_access_from_user( $member->user_id, $membership_id );
		}
	}

	/**
	 * Get members of a RCP group by membership ID
	 * 
	 * @param  int    $membership_id RCP membership ID
	 * @return array                 List of members WP_User ID
	 */
	public static function get_group_members_by_membership_id( $membership_id ) {
		if ( ! function_exists( 'rcpga_group_accounts' ) ) {
			return array();
		}

		$members = array();
		$groups = rcpga_group_accounts()->groups->get_groups( array(
			'where' => "WHERE membership_id = {$membership_id}"
		) );

		if ( ! empty( $groups ) ) {
			$members = rcpga_group_accounts()->members->get_members( $groups[0]->group_id, array( 'number' => -1 ) );
		}

		return $members;
	}

	/**
	 * Add course access
	 * 
	 * @param int $course_id ID of a course
	 * @param int $user_id   ID of a user
	 */
	public static function add_course_access( $course_id, $user_id, $membership_id ) {
		self::increment_course_access_counter( $course_id, $user_id, $membership_id );

		// check if user already enrolled
		if ( ! sfwd_lms_has_access( $course_id, $user_id ) ) {
			ld_update_course_access( $user_id, $course_id );
		} elseif ( sfwd_lms_has_access( $course_id, $user_id ) && ld_course_access_expired( $course_id, $user_id ) ) {
			
			// Remove access first
			self::decrement_course_access_counter( $course_id, $user_id, $membership_id );
			ld_update_course_access( $user_id, $course_id, $remove = true );

			// Re-enroll to get new access from value
			self::increment_course_access_counter( $course_id, $user_id, $membership_id );
			ld_update_course_access( $user_id, $course_id );
		}
	}

	/**
	 * Remove course access
	 * 
	 * @param int $course_id ID of a course
	 * @param int $user_id   ID of a user
	 */
	public static function remove_course_access( $course_id, $user_id, $membership_id ) {
		$access = self::decrement_course_access_counter( $course_id, $user_id, $membership_id );

		if ( ! isset( $access[ $course_id ] ) || empty( $access[ $course_id ] ) ) {
			ld_update_course_access( $user_id, $course_id, $remove = true );
		}
	}

	/**
	 * Add group access
	 * 
	 * @param int $group_id ID of a group
	 * @param int $user_id   ID of a user
	 */
	public static function add_group_access( $group_id, $user_id, $membership_id ) {
		self::increment_course_access_counter( $group_id, $user_id, $membership_id );

		if ( ! learndash_is_user_in_group( $user_id, $group_id ) ) {
			ld_update_group_access( $user_id, $group_id );
		}
	}

	/**
	 * Remove group access
	 * 
	 * @param int $group_id  ID of a group
	 * @param int $user_id   ID of a user
	 */
	public static function remove_group_access( $group_id, $user_id, $membership_id ) {
		$access = self::decrement_course_access_counter( $group_id, $user_id, $membership_id );

		if ( ! isset( $access[ $group_id ] ) || empty( $access[ $group_id ] ) ) {
			ld_update_group_access( $user_id, $group_id, $remove = true );
		}
	}

	/**
	 * Add enrolled course record to a user
	 *
	 * @param int $course_id ID of a course
	 * @param int $user_id   ID of a user
	 * @param int $membership_id  ID of a membership
	 */
	public static function increment_course_access_counter( $course_id, $user_id, $membership_id )
	{
		$courses = self::get_courses_access_counter( $user_id );

		if ( isset( $courses[ $course_id ] ) && ! is_array( $courses[ $course_id ] ) ) {
			$courses[ $course_id ] = array();
		}

		if ( ! isset( $courses[ $course_id ] ) || ( isset( $courses[ $course_id ] ) && array_search( $membership_id, $courses[ $course_id ] ) === false ) ) {
			// Add membership ID to course access counter
			$courses[ $course_id ][] = $membership_id;
		}

		update_user_meta( $user_id, '_learndash_rcp_enrolled_courses_access_counter', $courses );

		return $courses;
	}

	/**
	 * Delete enrolled course record from a user
	 * 
	 * @param int $course_id ID of a course
	 * @param int $user_id   ID of a user
	 * @param int $membership_id  ID of a membership
	 */
	public static function decrement_course_access_counter( $course_id, $user_id, $membership_id )
	{
		$courses = self::get_courses_access_counter( $user_id );
		
		if ( isset( $courses[ $course_id ] ) && ! is_array( $courses[ $course_id ] ) ) {
			$courses[ $course_id ] = array();
		}

		if ( isset( $courses[ $course_id ] ) ) {
			$keys = array_keys( $courses[ $course_id ], $membership_id );
			if ( is_array( $keys ) ) {
				foreach ( $keys as $key ) {
					unset( $courses[ $course_id ][ $key ] );
				}
			}
		}

		update_user_meta( $user_id, '_learndash_rcp_enrolled_courses_access_counter', $courses );

		return $courses;
	}

	/**
	 * Reset course access counter
	 * 
	 * @param  int 	  $course_id Course ID
	 * @param  int 	  $user_id   User ID
	 * @return void
	 */
	public static function reset_course_access_counter( $course_id, $user_id ) {
		$courses = self::get_courses_access_counter( $user_id );
		
		if ( isset( $courses[ $course_id ] ) ) {
			unset( $courses[ $course_id ] );
		}

		update_user_meta( $user_id, '_learndash_rcp_enrolled_courses_access_counter', $courses );
	}

	/**
	 * Get user enrolled course access counter
	 * 
	 * @param  int $user_id ID of a user
	 * @return array        Course access counter array
	 */
	public static function get_courses_access_counter( $user_id )
	{
		$courses = get_user_meta( $user_id, '_learndash_rcp_enrolled_courses_access_counter', true );

		if ( empty( $courses ) ) {
			$courses = array();
		}
		
		return $courses;
	}

	/**
	 * Get all LearnDash courses
	 * 
	 * @return object LearnDash courses
	 */
	private static function get_learndash_courses()
	{
		return get_posts([
			'post_type' => learndash_get_post_type_slug( 'course' ),
			'posts_per_page' => -1,
			'post_status' => 'publish',
			'orderby' => 'post_title',
			'order' => 'ASC',
		]);
	}

	/**
	 * Get all learndash groups.
	 * @return object LearnDash groups
	 */
	public static function get_learndash_groups()
	{
		return get_posts([
			'post_type' => learndash_get_post_type_slug( 'group' ),
			'posts_per_page' => -1,
			'post_status' => 'publish',
			'orderby' => 'post_title',
			'order' => 'ASC',
		]);
	}

	/**
	 * Check if an object ID belongs to an objects array.
	 * If true, output HTML attribute checked="checked".
	 * 
	 * @param  int    $course_id     Course ID
	 * @param  array  $courses_array Course IDs array
	 * @return void
	 */
	private static function checked( $object_id, $objects_array )
	{
		if ( in_array( $object_id, $objects_array ) ) {
			echo 'checked="checked"';
		}
	}

	/**
	 * Check if an object ID belongs to an object array.
	 *
	 * If true, output HTML attribute selected="selected".
	 * 
	 * @param  int    $object_id     Object ID
	 * @param  array  $objects_array Object IDs array
	 * @return void
	 */
	private static function selected( $object_id, $objects_array )
	{
		if ( in_array( $object_id, $objects_array ) ) {
			echo 'selected="selected"';
		}
	}
}

new Learndash_Restrict_Content_Pro_Integration();