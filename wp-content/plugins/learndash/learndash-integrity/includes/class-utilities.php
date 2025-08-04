<?php
namespace LearnDash\Integrity;

use LearnDash\Integrity\Prevent_Hotlinking;

/**
 * Class for plugin utilities
 */
class Utilities {
	/**
	 * Init hooks
	 * @return void
	 */
	public static function init() {
		register_activation_hook( LEARNDASH_INTEGRITY_PLUGIN_FILE, array( __CLASS__, 'activation' ) );
		register_deactivation_hook( LEARNDASH_INTEGRITY_PLUGIN_FILE, array( __CLASS__, 'deactivation' ) );
		register_uninstall_hook( LEARNDASH_INTEGRITY_PLUGIN_FILE, array( __CLASS__, 'uninstall' ) );
	}

	/**
	 * Run this function on plugin activation
	 * @return void
	 */
	public static function activation() {
		// Set up default options
		$default = array(
			'prevent_hotlinking' => 'yes',
			'prevent_concurrent_login' => 'yes',
			'prevent_content_copy' => 'no',
		);

		$options = get_option( 'learndash_settings_ld_integrity' );

		if ( $options === false ) {
			$options = $default;
			update_option( 'learndash_settings_ld_integrity', $default );
		}

		if (
			isset( $options['prevent_hotlinking'] )
			&& $options['prevent_hotlinking'] === 'yes'
		) {
			Prevent_Hotlinking::update_htaccess_on_activation();
		}
	}

	/**
	 * Run this function on plugin deactivation
	 * @return void
	 */
	public static function deactivation()
	{
		Prevent_Hotlinking::remove_htaccess_rule();
	}

	/**
	 * Run this function on plugin uninstall
	 * @return void
	 */
	public static function uninstall() {
		// Remove plugin option
		delete_option( 'learndash_settings_ld_integrity' );

		// Prevent hotlinking
		Prevent_Hotlinking::update_htaccess_on_uninstall();
	}
}

Utilities::init();
