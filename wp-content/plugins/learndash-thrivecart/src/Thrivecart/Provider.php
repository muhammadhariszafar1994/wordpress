<?php
/**
 * Main provider class file.
 *
 * @since 1.0.2
 *
 * @package LearnDash\Thrivecart
 */

namespace LearnDash\Thrivecart;

use StellarWP\Learndash\lucatume\DI52\ServiceProvider;

/**
 * Service provider class the plugin.
 *
 * @since 1.0.2
 */
class Provider extends ServiceProvider {
	/**
	 * Register service providers.
	 *
	 * @since 1.0.2
	 *
	 * @return void
	 */
	public function register(): void {
		$this->hooks();
	}

	/**
	 * Hooks wrapper.
	 *
	 * @since 1.0.2
	 *
	 * @return void
	 */
	public function hooks() {}
}
