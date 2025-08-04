<?php
/**
 * Plugin service provider class file.
 *
 * @since 1.1.0
 *
 * @package LearnDash\Migration
 */

namespace LearnDash\Migration;

use StellarWP\Learndash\lucatume\DI52\ServiceProvider;
use StellarWP\Learndash\lucatume\DI52\ContainerException;

/**
 * Plugin service provider class.
 *
 * @since 1.1.0
 */
class Plugin extends ServiceProvider {
	/**
	 * Register service providers.
	 *
	 * @since 1.0.0
	 *
	 * @throws ContainerException If a service provider is not found.
	 *
	 * @return void
	 */
	public function register(): void {
		$this->container->register( Admin\Provider::class );
		$this->container->register( AJAX\Provider::class );

		$this->container->singleton( Integrations::class );
		$this->container->singleton( Repository::class );

		$this->hooks();
	}

	/**
	 * Hooks wrapper.
	 *
	 * @since 1.0.0
	 *
	 * @throws ContainerException If a service provider is not found.
	 *
	 * @return void
	 */
	public function hooks() {
		add_action( 'init', $this->container->callback( Integrations::class, 'register_integrations' ) );
	}
}
