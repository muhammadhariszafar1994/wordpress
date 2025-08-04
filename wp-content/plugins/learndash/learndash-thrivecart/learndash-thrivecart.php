<?php
/**
 * Plugin Name: LearnDash LMS - ThriveCart Integration
 * Plugin URI: http://www.learndash.com/
 * Description: Integrate Thrivecart with LearnDash.
 * Version: 1.0.3
 * Author: LearnDash
 * Author URI: http://www.learndash.com/
 * Text Domain: learndash-thrivecart
 * Domain Path: languages
 * Requires PHP: 7.4
 * Tested up to: 6.5
 *
 * @package LearnDash\Thrivecart
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

define( 'LEARNDASH_THRIVECART_VERSION', '1.0.3' );
define( 'LEARNDASH_THRIVECART_PLUGIN_PATH', plugin_dir_path( __FILE__ ) );
define( 'LEARNDASH_THRIVECART_PLUGIN_DIR', plugin_dir_path( __FILE__ ) );
define( 'LEARNDASH_THRIVECART_PLUGIN_URL', plugin_dir_url( __FILE__ ) );
define( 'LEARNDASH_THRIVECART_VIEWS_DIR', plugin_dir_path( __FILE__ ) . 'src/views/' );
define( 'LEARNDASH_THRIVECART_VIEWS_URL', plugin_dir_url( __FILE__ ) . 'src/views/' );

require_once plugin_dir_path( __FILE__ ) . 'vendor/autoload.php';
require_once plugin_dir_path( __FILE__ ) . 'vendor-prefixed/autoload.php';

use LearnDash\Core\Autoloader;
use LearnDash\Thrivecart\Provider;
use LearnDash\Thrivecart\Utilities\Dependency_Checker;

Dependency_Checker::get_instance()->set_dependencies(
	[
		'sfwd-lms/sfwd_lms.php' => [
			'label'       => '<a href="https://learndash.com">LearnDash LMS</a>',
			'class'       => 'SFWD_LMS',
			'min_version' => '4.7.0',
		],
	]
);

Dependency_Checker::get_instance()->set_message(
	esc_html__( 'LearnDash LMS - ThriveCart Integration add-on requires the following plugin(s) be active:', 'learndash-thrivecart' )
);

add_action(
	'plugins_loaded',
	function () {
		if (
			! Dependency_Checker::get_instance()->check_dependency_results()
		) {
			return;
		}

		learndash_thrivecart_extra_autoloading();

		learndash_register_provider( Provider::class );

		learndash_thrivecart_include();
	}
);

/**
 * Setup the autoloader for extra classes, which are not in the src/Thrivecart directory.
 *
 * @since 1.0.2
 * @since 1.0.3 Added namespaced classes support.
 *
 * @return void
 */
function learndash_thrivecart_extra_autoloading(): void {
	// From https://www.php.net/manual/en/function.glob.php#106595.
	$glob_recursive = function ( string $pattern, int $flags = 0 ) use ( &$glob_recursive ): array {
		$files = glob( $pattern, $flags );
		$files = $files === false ? [] : $files;

		$directories = glob(
			dirname( $pattern ) . '/*',
			GLOB_ONLYDIR | GLOB_NOSORT // cspell: disable-line -- GLOB_ONLYDIR and GLOB_NOSORT are constants.
		);

		if ( is_array( $directories ) ) {
			foreach ( $directories as $dir ) {
				$files = array_merge(
					$files,
					$glob_recursive( $dir . '/' . basename( $pattern ), $flags )
				);
			}
		}

		return $files;
	};

	$autoloader = Autoloader::instance();

	foreach ( $glob_recursive( LEARNDASH_THRIVECART_PLUGIN_DIR . 'src/deprecated/*.php' ) as $file ) {
		if ( ! strstr( $file, 'functions' ) ) {
			// Get the clean path to the file without the extension and the src/deprecated directory.
			$class_mapped_from_file = mb_substr( $file, mb_strpos( $file, 'src/deprecated/' ) + 15, -4 );

			// Convert directory separator to namespace separator.
			// If the class is in a subdirectory, add the root namespace.
			$class_mapped_from_file = strpos( $class_mapped_from_file, '/' )
				? str_replace( '/', '\\', 'LearnDash/' . $class_mapped_from_file )
				: $class_mapped_from_file;

			$autoloader->register_class( $class_mapped_from_file, (string) $file );
		} else {
			include_once $file;
		}
	}

	$autoloader->register_autoloader();
}

/**
 * Include necessary files for the plugin.
 *
 * @since 1.0.2
 *
 * @return void
 */
function learndash_thrivecart_include(): void {
	include LEARNDASH_THRIVECART_PLUGIN_PATH . '/includes/admin/settings/class-settings.php';
	include LEARNDASH_THRIVECART_PLUGIN_PATH . '/includes/admin/class-settings-page.php';
	include LEARNDASH_THRIVECART_PLUGIN_PATH . '/includes/admin/class-settings-section.php';
	include LEARNDASH_THRIVECART_PLUGIN_PATH . '/includes/admin/class-settings-section-submit.php';

	include LEARNDASH_THRIVECART_PLUGIN_PATH . '/includes/class-thrivecart-product.php';
	include LEARNDASH_THRIVECART_PLUGIN_PATH . '/includes/class-thrivecart-integration.php';
}
