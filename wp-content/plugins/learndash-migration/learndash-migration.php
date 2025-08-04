<?php
/**
 * Plugin Name: LearnDash LMS - Migration
 * Plugin URI: https://www.learndash.com/
 * Description: Migrate from other LMS platform to LearnDash with a few clicks.
 * Version: 1.1.0
 * Author: LearnDash
 * Author URI: https://www.learndash.com/
 * Text Domain: learndash-migration
 * Domain Path: languages
 * Requires PHP: 7.4
 * Tested up to: 6.5
 *
 * @since 1.0.0
 *
 * @package LearnDash\Migration
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

define( 'LEARNDASH_MIGRATION_VERSION', '1.1.0' );
define( 'LEARNDASH_MIGRATION_FILE', __FILE__ );
define( 'LEARNDASH_MIGRATION_PLUGIN_DIR', plugin_dir_path( __FILE__ ) );
define( 'LEARNDASH_MIGRATION_PLUGIN_URL', plugin_dir_url( __FILE__ ) );
define( 'LEARNDASH_MIGRATION_VIEWS_DIR', plugin_dir_path( __FILE__ ) . 'src/views/' );
define( 'LEARNDASH_MIGRATION_VIEWS_URL', plugin_dir_url( __FILE__ ) . 'src/views/' );

require_once plugin_dir_path( __FILE__ ) . 'vendor/autoload.php';
require_once plugin_dir_path( __FILE__ ) . 'vendor-prefixed/autoload.php';

use LearnDash\Core\Autoloader;
use LearnDash\Migration\Plugin;
use LearnDash\Migration\Dependency_Checker;

$learndash_migration_dependency_checker = new Dependency_Checker();

$learndash_migration_dependency_checker->set_dependencies(
	[
		'sfwd-lms/sfwd_lms.php' => [
			'label'       => '<a href="https://learndash.com">LearnDash LMS</a>',
			'class'       => 'SFWD_LMS',
			'min_version' => '4.10.0',
		],
	]
);

$learndash_migration_dependency_checker->set_message(
	esc_html__( 'LearnDash LMS - Migration add-on requires the following plugin(s) be active:', 'learndash-migration' )
);

add_action(
	'learndash_init',
	function () use ( $learndash_migration_dependency_checker ) {
		if (
			! $learndash_migration_dependency_checker->check_dependency_results()
			|| php_sapi_name() === 'cli'
		) {
			return;
		}

		learndash_migration_extra_autoloading();

		learndash_register_provider( Plugin::class );
	}
);

/**
 * Setup the autoloader for extra classes, which are not in the src/Migration directory.
 *
 * @since 1.0.0
 * @since 1.0.1 Added namespaced classes support.
 *
 * @return void
 */
function learndash_migration_extra_autoloading(): void {
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

	foreach ( $glob_recursive( LEARNDASH_MIGRATION_PLUGIN_DIR . 'src/deprecated/*.php' ) as $file ) {
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
