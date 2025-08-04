<?php
/**
 * Plugin Name: LearnDash LMS - Samcart Integration
 * Plugin URI: http://www.learndash.com/
 * Description:	Integrate Samcart with LearnDash. 
 * Version: 1.1.0
 * Author: LearnDash
 * Author URI: http://www.learndash.com/
 * Text Domain: learndash-samcart
 * Domain Path: languages
 * Requires PHP: 5.6
 */

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) {
	exit();
}

// Check if class name already exists
if ( ! class_exists( 'LearnDash_Samcart' ) ) :

/**
* Main class
*
* @since  0.1
*/
final class LearnDash_Samcart {
	
	/**
	 * The one and only true LearnDash_Samcart instance
	 *
	 * @since 0.1
	 * @access private
	 * @var object $instance
	 */
	private static $instance;

	/**
	 * Instantiate the main class
	 *
	 * This function instantiates the class, initialize all functions and return the object.
	 * 
	 * @since 0.1
	 * @return object The one and only true LearnDash_Samcart instance.
	 */
	public static function instance() {

		if ( ! isset( self::$instance ) && ( ! self::$instance instanceof LearnDash_Samcart ) ) {

			self::$instance = new LearnDash_Samcart();
			self::$instance->setup_constants();
			
			add_action( 'plugins_loaded', array( self::$instance, 'load_textdomain' ) );

			self::$instance->check_dependency();
			add_action( 'plugins_loaded', function() {
				if ( LearnDash_Dependency_Check_LD_Samcart::get_instance()->check_dependency_results() ) {
					self::$instance->includes();
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
	 * @since 0.1
	 */
	public function setup_constants() {

		// Plugin version
		if ( ! defined( 'LEARNDASH_SAMCART_VERSION' ) ) {
			define( 'LEARNDASH_SAMCART_VERSION', '1.1.0' );
		}

		// Plugin file
		if ( ! defined( 'LEARNDASH_SAMCART_FILE' ) ) {
			define( 'LEARNDASH_SAMCART_FILE', __FILE__ );
		}		

		// Plugin folder path
		if ( ! defined( 'LEARNDASH_SAMCART_PLUGIN_PATH' ) ) {
			define( 'LEARNDASH_SAMCART_PLUGIN_PATH', plugin_dir_path( __FILE__ ) );
		}

		// Plugin folder URL
		if ( ! defined( 'LEARNDASH_SAMCART_PLUGIN_URL' ) ) {
			define( 'LEARNDASH_SAMCART_PLUGIN_URL', plugin_dir_url( __FILE__ ) );
		}
	}

	/**
	 * Load text domain used for translation
	 *
	 * This function loads mo and po files used to translate text strings used throughout the 
	 * plugin.
	 *
	 * @since 0.1
	 */
	public function load_textdomain() {

		// Set filter for plugin language directory
		$lang_dir = dirname( plugin_basename( LEARNDASH_SAMCART_FILE ) ) . '/languages/';
		$lang_dir = apply_filters( 'learndash_samcart_languages_directory', $lang_dir );

		// Load plugin translation file
		load_plugin_textdomain( 'learndash-samcart', false, $lang_dir );

		// include translations class
		include LEARNDASH_SAMCART_PLUGIN_PATH . 'includes/class-translations-ld-samcart.php';
	}

	/**
	 * Check and set plugin dependencies
	 * 
	 * @return void
	 */
	public function check_dependency()
	{
		include LEARNDASH_SAMCART_PLUGIN_PATH . 'includes/class-dependency-check.php';

		LearnDash_Dependency_Check_LD_Samcart::get_instance()->set_dependencies(
			array(
				'sfwd-lms/sfwd_lms.php' => array(
					'label'       => '<a href="https://learndash.com">LearnDash LMS</a>',
					'class'       => 'SFWD_LMS',
					'min_version' => '3.0.0',
				),
			)
		);

		LearnDash_Dependency_Check_LD_Samcart::get_instance()->set_message(
			__( 'LearnDash LMS - Samcart Integration Add-on requires the following plugin(s) to be active:', 'learndash-samcart' )
		);
	}

	/**
	 * Includes all necessary PHP files
	 *
	 * This function is responsible for including all necessary PHP files.
	 *
	 * @since  0.1
	 */
	public function includes() {		
		
		if ( is_admin() ) {
			include LEARNDASH_SAMCART_PLUGIN_PATH . '/includes/admin/settings/class-settings.php';
		}

		include LEARNDASH_SAMCART_PLUGIN_PATH . '/includes/class-samcart-product.php';
		include LEARNDASH_SAMCART_PLUGIN_PATH . '/includes/class-samcart-integration.php';
	}
}

endif; // End if class exists check

/**
 * The main function for returning instance
 *
 * @since 0.1
 * @return object The one and only true instance.
 */
function learndash_samcart() {
	return LearnDash_Samcart::instance();
}

// Run plugin
learndash_samcart();