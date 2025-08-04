<?php
/*
Plugin Name: LearnDash LMS - Event Espresso
Plugin URI: http://www.learndash.com
Description: LearnDash Event Espresso integration plugin
Version: 1.1.0
Author: LearnDash
Author URI: http://www.learndash.com
Text Domain: learndash-event-espresso
Doman Path: /languages/
License: GPL2
*/

/**
 * Copyright (c) 2014 LearnDash. All rights reserved.
 *
 * Released under the GPL license
 * http://www.opensource.org/licenses/gpl-license.php
 *
 * This is an add-on for WordPress
 * http://wordpress.org/
 *
 * **********************************************************************
 * This program is free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation; either version 2 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program; if not, write to the Free Software
 * Foundation, Inc., 51 Franklin St, Fifth Floor, Boston, MA  02110-1301  USA
 * **********************************************************************
 */

// don't call the file directly
if ( !defined( 'ABSPATH' ) ) exit;

/**
 * LearnDash_Event_Espresso class
 *
 * @class LearnDash_Event_Espresso The class that holds the entire LearnDash_Event_Espresso plugin
 */
class LearnDash_Event_Espresso {

    private $db_version;

    /**
     * Constructor for the LearnDash_Event_Espresso class
     *
     * Sets up all the appropriate hooks and actions
     * within our plugin.
     *
     * @uses add_action()
     */
    public function __construct() {
        $this->db_version = '1.0.1';
        $this->setup_constants();

        register_activation_hook( __FILE__, array($this, 'activate') );
        add_action( 'admin_init', array( $this, 'upgrade_db' ) );

        // Localize our plugin
        add_action( 'plugins_loaded', array($this, 'localization_setup') );

        add_action( 'AHEE__event_tickets_datetime_ticket_row_template_after_desc', array($this, 'admin_render_ticket_courses'), 10, 2 );

        // on frontend
        add_action( 'AHEE__ticket_selector_chart_template__after_ticket_date', array($this, 'show_course_on_ticket') );

        // associate courses
        add_action( 'AHEE__EE_Registration__set_status__to_approved', array($this, 'complete_payment') );

        // register user
        add_action( 'AHEE__EE_Single_Page_Checkout__process_attendee_information__end', array($this, 'finalize_registration'), 20 );

        // add_action( 'AHEE__espresso_events_Pricing_Hooks___update_tkts_new_ticket', array( $this, 'create_new_ticket' ), 10, 4 );
        add_action( 'AHEE__espresso_events_Pricing_Hooks___update_tkts_update_ticket', array($this, 'update_ee_ticket'), 10, 3 );
        add_action( 'AHEE__espresso_events_Pricing_Hooks___update_tkts_delete_ticket', array($this, 'delete_ticket') );
    }

    /**
     * Initializes the LearnDash_Event_Espresso() class
     *
     * Checks for an existing LearnDash_Event_Espresso() instance
     * and if it doesn't find one, creates it.
	 *
	 * @since: 1.0
     */
    public static function init() {
        static $instance = false;

        if ( !$instance ) {
            $instance = new LearnDash_Event_Espresso();
        }

        return $instance;
    }

    /**
     * Setup constants
     */
    public function setup_constants()
    {
        if ( ! defined( 'LEARNDASH_EE_VERSION' ) ) {
            define( 'LEARNDASH_EE_VERSION', '1.1.0' );
        }

        // Plugin file
        if ( ! defined( 'LEARNDASH_EE_FILE' ) ) {
            define( 'LEARNDASH_EE_FILE', __FILE__ );
        }       

        // Plugin folder path
        if ( ! defined( 'LEARNDASH_EE_PLUGIN_PATH' ) ) {
            define( 'LEARNDASH_EE_PLUGIN_PATH', plugin_dir_path( __FILE__ ) );
        }

        // Plugin folder URL
        if ( ! defined( 'LEARNDASH_EE_PLUGIN_URL' ) ) {
            define( 'LEARNDASH_EE_PLUGIN_URL', plugin_dir_url( __FILE__ ) );
        }
    }

    /**
     * Placeholder for activation function
     *
     * Create tables
	 *
	 * @since: 1.0
     */
    public function activate() {
        global $wpdb;

		if (!function_exists('dbDelta'))
			require_once(ABSPATH . 'wp-admin/includes/upgrade.php');

		$charset_collate = '';
		if ( ! empty($wpdb->charset) )
			$charset_collate .= "DEFAULT CHARACTER SET $wpdb->charset";
		if ( ! empty($wpdb->collate) )
			$charset_collate .= " COLLATE $wpdb->collate";

        $table = "CREATE TABLE `{$wpdb->prefix}learn_espresso` (
          id int(20) unsigned NOT NULL AUTO_INCREMENT,
          ticket_id bigint(20) DEFAULT NULL,
          course_ids varchar(1000) DEFAULT NULL,
          PRIMARY KEY  (id),
          KEY ticket_id (ticket_id)
        ) ". $charset_collate .";";

		dbDelta( $table );

        update_option( 'ld_ee_db_version', $this->db_version );
    }

    public function upgrade_db()
    {
        $db_version = get_option( 'ld_ee_db_version' );
        if ( $db_version == $this->db_version ) {
            return;
        }

        global $wpdb;

        if ( ! function_exists( 'dbDelta' ) )
            require_once( ABSPATH . 'wp-admin/includes/upgrade.php' );

        $charset_collate = '';
        if ( ! empty( $wpdb->charset ) )
            $charset_collate .= "DEFAULT CHARACTER SET $wpdb->charset";
        if ( ! empty( $wpdb->collate ) )
            $charset_collate .= " COLLATE $wpdb->collate";

        $table = "CREATE TABLE `{$wpdb->prefix}learn_espresso` (
          id int(20) unsigned NOT NULL AUTO_INCREMENT,
          ticket_id bigint(20) DEFAULT NULL,
          course_ids varchar(1000) DEFAULT NULL,
          PRIMARY KEY  (id),
          KEY ticket_id (ticket_id)
        ) ". $charset_collate .";";

        dbDelta( $table );

        update_option( 'ld_ee_db_version', $this->db_version );
    }

    /**
     * Initialize plugin for localization
     *
     * @uses load_plugin_textdomain()
	 *
	 * @since: 1.0
     */
    public function localization_setup() {
        load_plugin_textdomain( 'learndash-espresso', false, dirname( plugin_basename( __FILE__ ) ) . '/languages/' );

        // include translation/update class
        include LEARNDASH_EE_PLUGIN_PATH . 'includes/class-translations-ld-event-espresso.php';
    }

    /**
     * Prints courses dropdown on each ticket
     *
     * @param int $row_id
     * @param int $ticket_id
	 *
	 * @since: 1.0
     */
    function admin_render_ticket_courses( $row_id = 0, $ticket_id = 0 ) {

        $ticket = $this->get_ticket( $ticket_id );
        $courses = $this->get_post_list( 'sfwd-courses' );

        $selected = array();
        if ( $ticket ) {
            $selected = maybe_unserialize( $ticket->course_ids );
        }
        ?>

        <label for="_learndash_courses"><?php _e( 'LearnDash Courses:', 'learndash-espresso' ); ?></label>
        <br/>

        <select name="edit_tickets[<?php echo $row_id; ?>][learndash_course][]" multiple="multiple">
            <?php foreach ($courses as $course_id => $course_name) { ?>
                <option value="<?php echo esc_attr( $course_id ); ?>"<?php selected( in_array( $course_id, $selected ), true ); ?>><?php echo esc_html( $course_name ); ?></option>
            <?php } ?>
        </select>

        <?php
    }

    /**
     * Retrieve or display list of posts as a dropdown (select list).
     *
     * @param string $post_type
     * @return array
	 *
	 * @since: 1.0
     */
    function get_post_list( $post_type ) {

        $array = array();
        $courses = get_posts( array('post_type' => $post_type, 'posts_per_page' => -1) );
        if ( $courses ) {
            foreach ($courses as $course) {
                $array[$course->ID] = $course->post_title;
            }
        }

        return $array;
    }


    /**
     * Get a ticket
     *
     * @global WPDB $wpdb
     * @param int $ticket_id
     * @return obj
	 *
	 * @since: 1.0
     */
    function get_ticket( $ticket_id ) {
        global $wpdb;

        $row = $wpdb->get_row( $wpdb->prepare( "SELECT * FROM {$wpdb->prefix}learn_espresso WHERE ticket_id = %d", $ticket_id ) );

        return $row;
    }

    /**
     * Insert a new ticket
     *
     * @global WPDB $wpdb
     * @param int $ticket_id
     * @param array $courses
     * @return int
	 *
	 * @since: 1.0
     */
    function insert_ticket( $ticket_id, $courses ) {
        global $wpdb;

        $insert = $wpdb->insert( $wpdb->prefix . 'learn_espresso', array(
            'ticket_id' => $ticket_id,
            'course_ids' => maybe_serialize( $courses )
        ) );

        return $insert;
    }

    /**
     * Update a ticket
     *
     * @global WPDB $wpdb
     * @param int $ticket_id
     * @param array $courses
     * @return int
	 *
	 * @since: 1.0
     */
    function update_ticket( $ticket_id, $courses ) {
        global $wpdb;

        $update = $wpdb->update( $wpdb->prefix . 'learn_espresso',
            array('course_ids' => maybe_serialize( $courses )),
            array('ticket_id' => $ticket_id)
        );

        return $update;
    }


    /**
     * Create a new ticket
     *
     * @global WPDB $wpdb
     * @param obj $new_ticket
     * @param int $row_id
     * @param array $ticket_row
     * @return void
	 *
	 * @since: 1.0
     */
    function create_new_ticket( $new_ticket, $row_id, $ticket_row ) {
        global $wpdb;

        $courses = isset( $ticket_row['learndash_course'] ) ? $ticket_row['learndash_course'] : array();

        if ( !$courses ) {
            return;
        }

        $this->insert_ticket( $new_ticket->ID(), $courses );
    }

    /**
     * Update a ticket
     *
     * @param obj $new_ticket
     * @param int $row_id
     * @param array $ticket_row
	 *
	 * @since: 1.0
     */
    function update_ee_ticket( $new_ticket, $row_id, $ticket_row ) {

        $ticket_id = $new_ticket->ID();
        $courses = isset( $ticket_row['learndash_course'] ) ? $ticket_row['learndash_course'] : array();

        if ( $this->get_ticket( $ticket_id ) ) {
            $this->update_ticket( $ticket_id, $courses );
        } else {
            $this->insert_ticket( $ticket_id, $courses );
        }
    }

    /**
     * Delete a ticket
     *
     * @global WPDB $wpdb
     * @param obj $ticket
	 *
	 * @since: 1.0
     */
    function delete_ticket( $ticket ) {
        global $wpdb;

        $wpdb->delete( $wpdb->prefix . 'learn_espresso', array('ticket_id' => $ticket->ID()) );
    }


    /**
     * Show courses on a ticket in frontend
     *
     * @param obj $ticket
     * @return void
	 *
	 * @since: 1.0
     */
    function show_course_on_ticket( $ticket ) {
        $ticket_id = $ticket->ID();

        $learn = $this->get_ticket( $ticket_id );
        if ( !$learn ) {
            return;
        }

        $courses = maybe_unserialize( $learn->course_ids );
        if ( !$courses ) {
            return;
        }
        ?>
        <h5><?php _e( 'Associated Courses', 'learndash-espresso' ); ?></h5>

        <ul>
            <?php foreach ($courses as $course_id) { ?>
                <li><?php printf( '<a href="%s" target="_blank">%s</a>', get_permalink( $course_id ), get_the_title( $course_id ) ); ?></li>
            <?php } ?>
        </ul>

        <?php
    }


    /**
     * Associate a course when payment completes
     *
     * @param obj $registration
     * @return void
	 *
	 * @since: 1.0
     */
    function complete_payment( $registration ) {

        $transaction = $registration->transaction();
        $transaction_id = $transaction->ID();

        $users = get_users( array( 'meta_key' => '_ld_ee_trans_id', 'meta_value' => $transaction_id, 'number' => 1) );

        if ( !$users ) {
            return;
        }

        $user = reset( $users );
        $user_id = $user->ID;

        // bail out if no ticket found
        $ticket = $registration->ticket();
        $learn = $this->get_ticket( $ticket->ID() );

        if ( !$learn ) {
            return;
        }

        // bail out if no courses found
        $courses = maybe_unserialize( $learn->course_ids );
        if ( !$courses ) {
            return;
        }

        // associate courses
        foreach ($courses as $course_id) {
            ld_update_course_access( $user_id, $course_id, false );
        }
    }

    /**
     * Try to register the user if s/he isn't loggedin yet
     *
     * @param EE_Transaction $transaction
     * @return void
	 *
	 * @since: 1.0
     */
    function finalize_registration( EE_SPCO_Reg_Step_Attendee_Information $spco ) {

        $transaction = $spco->checkout->transaction;
        $transaction_id = $transaction->ID();
		
        if ( is_user_logged_in() ) {
            update_user_meta( get_current_user_id(), '_ld_ee_trans_id', $transaction_id );
            return;
        }

        // not logged in, try to register the user
        $registration = reset( $transaction->registrations() );
        $attendee = $registration->attendee();

        $email = $attendee->email();
        $name = $attendee->full_name();

        $user_exists = get_user_by( 'email', $email );

        if ( !$user_exists ) {
            $user_pass = wp_generate_password( 12, false );
            $user_id = wp_create_user( $email, $user_pass, $email );

            if ( $user_id && !is_wp_error( $user_id ) ) {
				if (version_compare($wp_version, '4.3.0', '<')) {
					wp_new_user_notification( $user_id, $user_pass );
				} else if (version_compare($wp_version, '4.3.0', '=')) {
					wp_new_user_notification( $user_id, 'both' );						
				} else if (version_compare($wp_version, '4.3.1', '>=')) {
					wp_new_user_notification( $user_id, null, 'both' );
				}
            }

        } else {
            $user_id = $user_exists->ID;
        }

        // set reference for IPN
        if ( $user_id && !is_wp_error( $user_id ) ) {
            update_user_meta( $user_id, '_ld_ee_trans_id', $transaction_id );
        }
    }
}

// LearnDash_Event_Espresso
LearnDash_Event_Espresso::init();
