<?php
/**
 * Plugin Name: LearnDash LMS - Integrity
 * Plugin URI: https://www.learndash.com/
 * Description: Protect your LearnDash site from content theft.
 * Version: 1.2.0
 * Author: LearnDash
 * Author URI: https://www.learndash.com/
 * Text Domain: learndash-integrity
 * Domain Path: languages
 */

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) exit;

/**
 * Define LD Integrity version
 *
 * @since 1.2.0
 */
define( 'LEARNDASH_INTEGRITY_VERSION', '1.2.0' );

// Check if class LearnDash_Integrity already existed
if ( ! class_exists( 'LearnDash_Integrity' ) ) :
/**
* Main LearnDash_LearnDash_Integrity class.
*
* This main class is responsible for instantiating the class, including the necessary files
* used throughout the plugin, and loading the plugin translation files.
*
* @since 1.0
*/
final class LearnDash_Integrity {
	/**
	 * The one and only true LearnDash_Integrity instance.
	 *
	 * @since 1.0
	 * @access private
	 * @var object
	 */
	private static $instance;

	/**
	 * Instantiate the main class.
	 *
	 * This function instantiates the class and return the object.
	 *
	 * @since 1.0
	 * @return object The one and only true LearnDash_Integrity instance.
	 */
	public static function instance() {
		if ( ! isset( self::$instance ) && ( ! self::$instance instanceof LearnDash_Integrity ) ) {
			self::$instance = new LearnDash_Integrity;
			self::$instance->define_constants();

			self::$instance->check_dependency();
			self::$instance->includes();

			add_action( 'plugins_loaded', function() {
			    if ( LearnDash_Dependency_Check_LD_Integrity::get_instance()->check_dependency_results() ) {
					self::$instance->includes_on_plugins_loaded();
			    }
			} );
		}

		return self::$instance;
	}

	/**
	 * Define plugin constants
	 * @return void
	 */
	public function define_constants() {
		if ( ! defined( 'LEARNDASH_INTEGRITY_PLUGIN_URL' ) ) {
			define( 'LEARNDASH_INTEGRITY_PLUGIN_URL', plugin_dir_url( __FILE__ ) );
		}

		if ( ! defined( 'LEARNDASH_INTEGRITY_PLUGIN_PATH' ) ) {
			define( 'LEARNDASH_INTEGRITY_PLUGIN_PATH', plugin_dir_path( __FILE__ ) );
		}

		if ( ! defined( 'LEARNDASH_INTEGRITY_PLUGIN_FILE' ) ) {
			define( 'LEARNDASH_INTEGRITY_PLUGIN_FILE', __FILE__ );
		}
	}

	/**
	 * Check and set dependencies
	 *
	 * @return void
	 */
	public function check_dependency()
	{
	    include LEARNDASH_INTEGRITY_PLUGIN_PATH . 'includes/class-dependency-check.php';

	    LearnDash_Dependency_Check_LD_Integrity::get_instance()->set_dependencies(
	        array(
	            'sfwd-lms/sfwd_lms.php' => array(
	                'label'       => '<a href="https://learndash.com">LearnDash LMS</a>',
	                'class'       => 'SFWD_LMS',
	                'min_version' => '3.0.0',
	            ),
	        )
	    );

	    LearnDash_Dependency_Check_LD_Integrity::get_instance()->set_message(
	        __( 'LearnDash LMS - Integrity Add-on requires the following plugin(s) to be active:', 'learndash-integrity' )
	    );
	}

	/**
	 * Includes necessary files
	 * @return void
	 */
	public function includes() {
		include LEARNDASH_INTEGRITY_PLUGIN_PATH . 'includes/class-utilities.php';
		include LEARNDASH_INTEGRITY_PLUGIN_PATH . 'includes/class-prevent-hotlinking.php';
	}

	/**
	 * Includes necessary files on plugins_loaded hook
	 * @return void
	 */
	public function includes_on_plugins_loaded() {
		if ( is_admin() ) {
			include LEARNDASH_INTEGRITY_PLUGIN_PATH . 'includes/admin/class-settings-page.php';
			include LEARNDASH_INTEGRITY_PLUGIN_PATH . 'includes/admin/class-settings-section.php';
		}

		include LEARNDASH_INTEGRITY_PLUGIN_PATH . 'includes/class-prevent-concurrent-login.php';
		include LEARNDASH_INTEGRITY_PLUGIN_PATH . 'includes/class-prevent-content-copy.php';
		include LEARNDASH_INTEGRITY_PLUGIN_PATH . 'includes/class-recaptcha.php';
		include LEARNDASH_INTEGRITY_PLUGIN_PATH . 'includes/class-recaptcha-v3.php';
		include LEARNDASH_INTEGRITY_PLUGIN_PATH . 'includes/class-recaptcha-v2.php';
	}
}
endif; // End if class_exist check

/**
 * The main function for returning LearnDash_Integrity instance.
 *
 * This function returns LearnDash_Integrity instance to make sure the plugin works.
 *
 * @since 1.0
 * @return object The one and only true LearnDash_Integrity instance.
 */
function learndash_integrity() {
	return LearnDash_Integrity::instance();
}

// Run LearnDash_Integrity
learndash_integrity();
