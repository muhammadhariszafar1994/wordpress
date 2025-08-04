<?php
/**
 * Admin provider class file.
 *
 * @since 1.0.0
 *
 * @package LearnDash\Migration
 */

namespace LearnDash\Migration\Admin;

use StellarWP\Learndash\lucatume\DI52\ServiceProvider;

/**
 * Admin service provider class.
 *
 * @since 1.0.0
 */
class Provider extends ServiceProvider {
	/**
	 * Register service providers.
	 *
	 * @since 1.0.0
	 *
	 * @return void
	 */
	public function register(): void {
		$this->hooks();
	}

	/**
	 * Hooks wrapper.
	 *
	 * @since 1.0.0
	 *
	 * @return void
	 */
	public function hooks() {
		// Settings section.

		add_action( 'learndash_settings_sections_init', $this->container->callback( Settings_Section::class, 'add_section_instance' ) );

		add_filter( 'learndash_settings_show_section_submit', $this->container->callback( Settings_Section::class, 'show_section_submit' ), 10, 2 );

		add_action( 'admin_enqueue_scripts', $this->container->callback( Settings_Section::class, 'enqueue_admin_scripts' ) );

		// Translation.

		add_action( 'init', $this->container->callback( Translation::class, 'add_section_instance' ) );
	}
}
