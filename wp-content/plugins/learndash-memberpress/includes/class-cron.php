<?php
/**
* Cron class
*/
class Learndash_Memberpress_Cron
{
	
	public function __construct()
	{
		add_filter( 'cron_schedules', array( $this, 'cron_schedules' ) );
		register_activation_hook( LEARNDASH_MEMBERPRESS_FILE, array( $this, 'register_cron' ) );
		add_action( 'init', array( $this, 'update_cron' ) );
		register_deactivation_hook( LEARNDASH_MEMBERPRESS_FILE, array( $this, 'deregister_cron' ) );
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
		if ( ! wp_next_scheduled( 'learndash_memberpress_cron' ) ) {
			wp_schedule_event( time(), 'every_minute', 'learndash_memberpress_cron' );
		}

		if ( ! wp_next_scheduled( 'learndash_memberpress_silent_course_access_update' ) ) {
			wp_schedule_event( time(), 'every_minute', 'learndash_memberpress_silent_course_access_update' );
		}
	}

	public function update_cron()
	{
		$saved_version   = get_option( 'learndash_memberpress_version' );
		$current_version = LEARNDASH_MEMBERPRESS_VERSION;
		if ( $saved_version === false || version_compare( $saved_version, $current_version, '!=' ) ) {
			wp_clear_scheduled_hook( 'learndash_memberpress_cron' );

			if ( ! wp_next_scheduled( 'learndash_memberpress_cron' ) ) {
				wp_schedule_event( time(), 'every_minute', 'learndash_memberpress_cron' );
			}

			wp_clear_scheduled_hook( 'learndash_memberpress_silent_course_access_update' );

			if ( ! wp_next_scheduled( 'learndash_memberpress_silent_course_access_update' ) ) {
				wp_schedule_event( time(), 'every_minute', 'learndash_memberpress_silent_course_access_update' );
			}

			update_option( 'learndash_memberpress_version', $current_version );
		}
	}

	public function deregister_cron() {
		wp_clear_scheduled_hook( 'learndash_memberpress_cron' );
		wp_clear_scheduled_hook( 'learndash_memberpress_silent_course_access_update' );
	}
}

new Learndash_Memberpress_Cron();