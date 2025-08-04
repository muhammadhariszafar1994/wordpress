<?php
/**
 * Plugin Name: LearnDash LMS - Restrict Content Pro Integration
 * Plugin URI: http://www.learndash.com/
 * Description: Integrate LearnDash LMS with Restrict Content Pro.
 * Version: 1.1.1
 * Author: LearnDash
 * Author URI: http://www.learndash.com/
 * Text Domain: learndash-restrict-content-pro
 * Domain Path: languages
 */

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) exit;

// Check if class Learndash_Restrict_Content_Pro already exists
if ( ! class_exists( 'Learndash_Restrict_Content_Pro' ) ) :

/**
* Main Learndash_Restrict_Content_Pro class
*
* This main class is responsible for instantiating the class, including the necessary files
* used throughout the plugin, and loading the plugin translation files.
*
* @since 1.0
*/
final class Learndash_Restrict_Content_Pro {

	/**
	 * The one and only true Learndash_Restrict_Content_Pro instance
	 *
	 * @since 1.0
	 * @access private
	 * @var object $instance
	 */
	private static $instance;

	/**
	 * Instantiate the main class
	 *
	 * This function instantiates the class, initialize all functions and return the object.
	 *
	 * @since 1.0
	 * @return object The one and only true Learndash_Restrict_Content_Pro instance.
	 */
	public static function instance() {

		if ( ! isset( self::$instance ) && ( ! self::$instance instanceof Learndash_Restrict_Content_Pro ) ) {

			self::$instance = new Learndash_Restrict_Content_Pro;
			self::$instance->setup_constants();
			self::$instance->includes();

			add_action( 'plugins_loaded', array( self::$instance, 'load_textdomain' ) );

			self::$instance->check_dependency();
			add_action( 'plugins_loaded', function() {
				if ( LearnDash_Dependency_Check_LD_RCP::get_instance()->check_dependency_results() ) {
					self::$instance->includes_on_plugins_loaded();
				}
			} );
		}

		return self::$instance;
	}

	/**
	 * Function for setting up constants
	 *
	 * This function is used to set up constants used throughout the plugin.
	 *
	 * @since 1.0
	 */
	public function setup_constants() {

		// Plugin version
		if ( ! defined( 'LEARNDASH_RESTRICT_CONTENT_PRO_VERSION' ) ) {
			define( 'LEARNDASH_RESTRICT_CONTENT_PRO_VERSION', '1.1.1' );
		}

		// Plugin file
		if ( ! defined( 'LEARNDASH_RESTRICT_CONTENT_PRO_FILE' ) ) {
			define( 'LEARNDASH_RESTRICT_CONTENT_PRO_FILE', __FILE__ );
		}

		// Plugin folder path
		if ( ! defined( 'LEARNDASH_RESTRICT_CONTENT_PRO_PLUGIN_PATH' ) ) {
			define( 'LEARNDASH_RESTRICT_CONTENT_PRO_PLUGIN_PATH', plugin_dir_path( __FILE__ ) );
		}

		// Plugin folder URL
		if ( ! defined( 'LEARNDASH_RESTRICT_CONTENT_PRO_PLUGIN_URL' ) ) {
			define( 'LEARNDASH_RESTRICT_CONTENT_PRO_PLUGIN_URL', plugin_dir_url( __FILE__ ) );
		}
	}

	/**
	 * Load text domain used for translation
	 *
	 * This function loads mo and po files used to translate text strings used throughout the
	 * plugin.
	 *
	 * @since 1.0
	 */
	public function load_textdomain() {

		// Set filter for plugin language directory
		$lang_dir = dirname( plugin_basename( LEARNDASH_RESTRICT_CONTENT_PRO_FILE ) ) . '/languages/';
		$lang_dir = apply_filters( 'learndash_restrict_content_pro_languages_directory', $lang_dir );

		// Load plugin translation file
		load_plugin_textdomain( 'learndash-restrict-content-pro', false, $lang_dir );

		// include translations/update class
		include LEARNDASH_RESTRICT_CONTENT_PRO_PLUGIN_PATH . 'includes/class-translations-ld-restrict-content-pro.php';
	}

	/**
	 * Check plugin dependencies
	 * @return void
	 */
	public function check_dependency()
	{
		include LEARNDASH_RESTRICT_CONTENT_PRO_PLUGIN_PATH . '/includes/class-dependency-check.php';

		LearnDash_Dependency_Check_LD_RCP::get_instance()->set_dependencies(
			array(
				'sfwd-lms/sfwd_lms.php' => array(
					'label'       => '<a href="https://learndash.com">LearnDash LMS</a>',
					'class'       => 'SFWD_LMS',
					'min_version' => '3.0.0',
				),
				'restrict-content-pro/restrict-content-pro.php' => array(
					'label'       => '<a href="https://restrictcontentpro.com/">Restrict Content Pro</a>',
					'class'       => '',
					'min_version' => '3.0.0',
				)
			)
		);

		LearnDash_Dependency_Check_LD_RCP::get_instance()->set_message(
			__( 'LearnDash LMS - Restrict Content Pro Add-on requires the following plugin(s) to be active:', 'learndash-paidmemberships' )
		);
	}

	/**
	 * Includes all necessary PHP files
	 *
	 * @since  1.0
	 */
	public function includes()
	{
		include LEARNDASH_RESTRICT_CONTENT_PRO_PLUGIN_PATH . '/includes/class-cron.php';
	}

	/**
	 * Includes all necessary PHP files after "plugins_loaded" hook
	 *
	 * This function is responsible for including all necessary PHP files.
	 *
	 * @since  1.0
	 */
	public function includes_on_plugins_loaded() {
		include LEARNDASH_RESTRICT_CONTENT_PRO_PLUGIN_PATH . '/includes/class-tools.php';
		include LEARNDASH_RESTRICT_CONTENT_PRO_PLUGIN_PATH . '/includes/class-integration.php';
	}
}
endif; // End if class_exist check

/**
 * The main function for returning Learndash_Restrict_Content_Pro instance
 *
 * @since 1.0
 * @return object The one and only true Learndash_Restrict_Content_Pro instance.
 */
function learndash_restrict_content_pro() {
	return Learndash_Restrict_Content_Pro::instance();
}

// Run plugin
learndash_restrict_content_pro();
