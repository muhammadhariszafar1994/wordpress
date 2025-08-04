<?php
/**
* Cron class
*/
class Learndash_Restrict_Content_Pro_Cron
{
	
	public function __construct()
	{
		add_filter( 'cron_schedules', array( $this, 'cron_schedules' ) );
		add_action( 'init', array( $this, 'update_cron' ) );
		register_activation_hook( LEARNDASH_RESTRICT_CONTENT_PRO_FILE, array( $this, 'register_cron' ) );
		register_deactivation_hook( LEARNDASH_RESTRICT_CONTENT_PRO_FILE, array( $this, 'deregister_cron' ));

		// Run cron job
		if ( class_exists( 'Learndash_Restrict_Content_Pro_Integration' ) ) {
			add_action( 'learndash_restrict_content_pro_cron', array( $this, 'cron_jobs' ) );
		}
	}

	public function cron_schedules()
	{
		$schedules['every_minute'] = array(
			'interval' => 60,
			'display'  => __( 'Every Minute' ),
		);

		return $schedules;
	}

	public function register_cron()
	{
		if ( ! wp_next_scheduled( 'learndash_restrict_content_pro_cron' ) ) {
			wp_schedule_event( time(), 'every_minute', 'learndash_restrict_content_pro_cron' );
		}
	}

	public function update_cron()
	{
		$saved_version   = get_option( 'learndash_restrict_content_pro_version' );
		$current_version = LEARNDASH_RESTRICT_CONTENT_PRO_VERSION;
		if ( $saved_version === false || version_compare( $saved_version, $current_version, '!=' ) ) {
			wp_clear_scheduled_hook( 'learndash_restrict_content_pro_cron' );

			if( ! wp_next_scheduled( 'learndash_restrict_content_pro_cron' ) ) {
				wp_schedule_event( time(), 'every_minute', 'learndash_restrict_content_pro_cron' );
			}

			update_option( 'learndash_restrict_content_pro_version', $current_version );
		}
	}

	public function deregister_cron() {
		wp_clear_scheduled_hook( 'learndash_restrict_content_pro_cron' );
	}

	public function cron_jobs()
	{
		$lock_file = WP_CONTENT_DIR . '/uploads/learndash/learndash-restrict-content-pro-lock.txt';
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
		Learndash_Restrict_Content_Pro_Integration::cron_update_course_access();
	}
}

new Learndash_Restrict_Content_Pro_Cron();