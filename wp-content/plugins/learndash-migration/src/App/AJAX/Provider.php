<?php
/**
 * AJAX provider class.
 *
 * @since 1.0.0
 *
 * @package LearnDash\Migration
 */

namespace LearnDash\Migration\AJAX;

use StellarWP\Learndash\lucatume\DI52\ServiceProvider;

/**
 * Service provider class for AJAX module.
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
	 * Register WordPress hooks.
	 *
	 * @since 1.0.0
	 *
	 * @return void
	 */
	public function hooks(): void {
		add_action( 'wp_ajax_' . Migrate::$action, $this->container->callback( Migrate::class, 'handle_request' ) );
	}
}
