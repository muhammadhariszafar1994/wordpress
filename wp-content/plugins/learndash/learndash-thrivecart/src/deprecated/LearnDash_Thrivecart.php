<?php
/**
 * Deprecated LearnDash_Thrivecart class file.
 *
 * @deprecated 1.0.2
 *
 * @package LearnDash\Thrivecart\Deprecated
 */

_deprecated_file( __FILE__, '1.0.3' );

/**
 * Deprecated LearnDash_Thrivecart class.
 *
 * @deprecated 1.0.2
 *
 * @since 1.0
 */
class LearnDash_Thrivecart {
	/**
	 * The one and only true LearnDash_Thrivecart instance
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
	 * @return object The one and only true LearnDash_Thrivecart instance.
	 */
	public static function instance() {
		if ( ! isset( self::$instance ) && ( ! self::$instance instanceof LearnDash_Thrivecart ) ) {

			self::$instance = new LearnDash_Thrivecart();
			self::$instance->setup_constants();

			add_action( 'plugins_loaded', array( self::$instance, 'load_textdomain' ) );

			self::$instance->check_dependency();

			add_action(
				'plugins_loaded',
				function() {
					if ( LearnDash_Dependency_Check_LD_Thrivecart::get_instance()->check_dependency_results() ) {
						self::$instance->includes();
						self::$instance->includes_on_plugins_loaded();
					}
				}
			);
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
		// Plugin version.
		if ( ! defined( 'LEARNDASH_THRIVECART_VERSION' ) ) {
			define( 'LEARNDASH_THRIVECART_VERSION', '1.0.1' );
		}

		// Plugin file.
		if ( ! defined( 'LEARNDASH_THRIVECART_FILE' ) ) {
			define( 'LEARNDASH_THRIVECART_FILE', __FILE__ );
		}

		// Plugin folder path.
		if ( ! defined( 'LEARNDASH_THRIVECART_PLUGIN_PATH' ) ) {
			define( 'LEARNDASH_THRIVECART_PLUGIN_PATH', plugin_dir_path( __FILE__ ) );
		}

		// Plugin folder URL.
		if ( ! defined( 'LEARNDASH_THRIVECART_PLUGIN_URL' ) ) {
			define( 'LEARNDASH_THRIVECART_PLUGIN_URL', plugin_dir_url( __FILE__ ) );
		}
	}

	/**
	 * Load text domain used for translation.
	 *
	 * This function loads mo and po files used to translate text strings used throughout the
	 * plugin.
	 *
	 * @since 0.1
	 */
	public function load_textdomain() {
		// Set filter for plugin language directory.
		$lang_dir = dirname( plugin_basename( LEARNDASH_THRIVECART_FILE ) ) . '/languages/';
		$lang_dir = apply_filters( 'learndash_thrivecart_languages_directory', $lang_dir );

		// Load plugin translation file.
		load_plugin_textdomain( 'learndash-thrivecart', false, $lang_dir );

		// include translations class.
		include LEARNDASH_THRIVECART_PLUGIN_PATH . 'includes/class-translations-ld-thrivecart.php';
	}

	/**
	 * Check and set dependencies
	 *
	 * @return void
	 */
	public function check_dependency() {
		include LEARNDASH_THRIVECART_PLUGIN_PATH . 'includes/class-dependency-check.php';

		LearnDash_Dependency_Check_LD_Thrivecart::get_instance()->set_dependencies(
			array(
				'sfwd-lms/sfwd_lms.php' => array(
					'label'       => '<a href="https://learndash.com">LearnDash LMS</a>',
					'class'       => 'SFWD_LMS',
					'min_version' => '3.0.0',
				),
			)
		);

		LearnDash_Dependency_Check_LD_Thrivecart::get_instance()->set_message(
			__( 'LearnDash LMS - Thrivecart Integration Add-on requires the following plugin(s) to be active:', 'learndash-thrivecart' )
		);
	}

	/**
	 * Includes files after plugins loaded
	 */
	public function includes_on_plugins_loaded() {
		include LEARNDASH_THRIVECART_PLUGIN_PATH . '/includes/admin/class-settings-page.php';
		include LEARNDASH_THRIVECART_PLUGIN_PATH . '/includes/admin/class-settings-section.php';
		include LEARNDASH_THRIVECART_PLUGIN_PATH . '/includes/admin/class-settings-section-submit.php';
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
			include LEARNDASH_THRIVECART_PLUGIN_PATH . '/includes/admin/settings/class-settings.php';
		}

		include LEARNDASH_THRIVECART_PLUGIN_PATH . '/includes/class-thrivecart-product.php';
		include LEARNDASH_THRIVECART_PLUGIN_PATH . '/includes/class-thrivecart-integration.php';
	}
}
