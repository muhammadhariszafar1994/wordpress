<?php
/**
* Cron class
*/
class Learndash_Paidmemberships_Cron
{
    
    public function __construct()
    {
        add_filter( 'cron_schedules', array( $this, 'cron_schedules' ) );
        register_activation_hook( LEARNDASH_PMP_FILE, array( $this, 'register_cron' ) );
        register_deactivation_hook( LEARNDASH_PMP_FILE, array( $this, 'deregister_cron' ) );
        add_action( 'init', array( $this, 'update_cron' ) );

        // Run cron jobs
        add_action( 'learndash_pmp_cron', array( $this, 'cron_jobs' ) );
        add_action( 'learndash_pmp_silent_course_access_update', array( $this, 'cron_silent_course_access_update' ) );
    }

    public function cron_schedules( $schedules )
    {
        $schedules['every_minute'] = array(
            'interval' => 60,
            'display'  => __( 'Every Minute' ),
        );

        return $schedules;
    }

    public function register_cron()
    {
        if ( ! wp_next_scheduled( 'learndash_pmp_cron' ) ) {
            wp_schedule_event( time(), 'every_minute', 'learndash_pmp_cron' );
        }

        if ( ! wp_next_scheduled( 'learndash_pmp_silent_course_access_update' ) ) {
            wp_schedule_event( time(), 'every_minute', 'learndash_pmp_silent_course_access_update' );
        }
    }

    public function update_cron()
    {
        $saved_version   = get_option( 'learndash_pmp_version' );
        $current_version = LEARNDASH_PMP_VERSION;
        if ( $saved_version === false || version_compare( $saved_version, $current_version, '!=' ) ) {
            wp_clear_scheduled_hook( 'learndash_pmp_cron' );

            if ( ! wp_next_scheduled( 'learndash_pmp_cron' ) ) {
                wp_schedule_event( time(), 'every_minute', 'learndash_pmp_cron' );
            }

            wp_clear_scheduled_hook( 'learndash_pmp_silent_course_access_update' );

            if ( ! wp_next_scheduled( 'learndash_pmp_silent_course_access_update' ) ) {
                wp_schedule_event( time(), 'every_minute', 'learndash_pmp_silent_course_access_update' );
            }

            update_option( 'learndash_pmp_version', $current_version );
        }
    }

    public function deregister_cron() {
        wp_clear_scheduled_hook( 'learndash_pmp_cron' );
        wp_clear_scheduled_hook( 'learndash_pmp_silent_course_access_update' );
    }

    public function cron_jobs()
    {
        $lock_file = WP_CONTENT_DIR . '/uploads/learndash/learndash-pmp-lock.txt';
        $dirname   = dirname( $lock_file );

        if ( ! is_dir( $dirname ) ) {
            wp_mkdir_p( $dirname );
        }

        $lock_fp = fopen( $lock_file, 'c+' );

        // Now try to get exclusive lock on the file. 
        if ( ! flock( $lock_fp, LOCK_EX | LOCK_NB ) ) { 
            // If you can't lock then abort because another process is already running
            exit(); 
        }

        // Run cron job functions
        Learndash_Paidmemberships::cron_update_object_access();
    }

    public function cron_silent_course_access_update() {
        $lock_file = WP_CONTENT_DIR . '/uploads/learndash/learndash-pmp-course-access-update.txt';
        $dirname   = dirname( $lock_file );

        if ( ! is_dir( $dirname ) ) {
            wp_mkdir_p( $dirname );
        }

        $lock_fp = fopen( $lock_file, 'c+' );

        // Now try to get exclusive lock on the file. 
        if ( ! flock( $lock_fp, LOCK_EX | LOCK_NB ) ) { 
            // If you can't lock then abort because another process is already running
            exit(); 
        }

        Learndash_Paidmemberships::cron_process_silent_object_enrollment();
    }
}

new Learndash_Paidmemberships_Cron();