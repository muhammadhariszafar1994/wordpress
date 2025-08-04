<?php

if ( ! defined( 'ABSPATH' ) ) {
    exit();
}

/**
* Cron class
*/
class LearnDash_EDD_Cron {
    
    /**
     * Hook functions
     */
    public function __construct() {
        add_filter( 'cron_schedules', [ $this, 'add_cron_schedule' ] );

        register_activation_hook( LEARNDASH_EDD_FILE, [ $this, 'register_cron' ] );
        register_deactivation_hook( LEARNDASH_EDD_FILE, [ $this, 'deregister_cron' ] );

        // Run cron jobs
        add_action( 'learndash_edd_cron', array( $this, 'cron_jobs' ) );
    }

    /**
     * Add cron schedule
     * 
     * @param array  $schedules Cron schedules
     * @return array           Modifed cron_schedules filter hook value
     */
    public function add_cron_schedule( $schedules ) {
        $schedules['per_minute'] = [
            'interval' => MINUTE_IN_SECONDS,
            'display'  => __( 'Once per Minute', 'learndash-edd' ),
        ];

        return $schedules;
    }

    /**
     * Register cron hook
     *
     * @return void
     */
    public function register_cron() {
        if ( ! wp_next_scheduled( 'learndash_edd_cron' ) ) {
            wp_schedule_event( time(), 'per_minute', 'learndash_edd_cron' );
        }
    }

    /**
     * Deregister cron hook
     * 
     * @return void
     */
    public function deregister_cron() {
        wp_clear_scheduled_hook( 'learndash_edd_cron' );
    }

    /**
     * Run cron jobs
     * 
     * @return void
     */
    public function cron_jobs()
    {
        $lock_file = WP_CONTENT_DIR . '/uploads/learndash/learndash-edd-jobs.txt';
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
        LearnDash_EDD::cron_update_course_access();
    }
}

new LearnDash_EDD_Cron();