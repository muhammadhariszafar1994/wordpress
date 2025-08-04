<?php
/**
 * Integrations class file.
 *
 * @since 1.0.0
 *
 * @package LearnDash\Migration
 */

namespace LearnDash\Migration;

/**
 * Integrations class.
 *
 * @since 1.0.0
 */
class Integrations {
	/**
	 * Integrations collection.
	 *
	 * In key - integrations object pair. We use integration plugin/app slug as the key.
	 *
	 * @since 1.0.0
	 *
	 * @var array<string, Integrations\Base>
	 */
	private $collection = [];

	/**
	 * Get integration classes.
	 *
	 * @since 1.0.0
	 *
	 * @return array<Integrations\Base>
	 */
	private function get_integration_classes(): array {
		/**
		 * Filter hook to get integration classes.
		 *
		 * @since 1.0.0
		 *
		 * @param array $integrations Integration classes.
		 */
		return apply_filters(
			'learndash_migration_integration_classes',
			[
				Integrations\LearnPress::class,
				Integrations\TutorLMS::class,
				Integrations\SenseiLMS::class,
				Integrations\LifterLMS::class,
			]
		);
	}

	/**
	 * Register integrations.
	 *
	 * @since 1.0.0
	 *
	 * @return void
	 */
	public function register_integrations(): void {
		$integration_classes = $this->get_integration_classes();

		foreach ( $integration_classes as $integration_class ) {
			$integration_object = new $integration_class();

			$this->collection[ $integration_object->key ] = $integration_object;
		}
	}

	/**
	 * Get all integrations.
	 *
	 * @since 1.0.0
	 *
	 * @return array<string, Integrations\Base>
	 */
	public function get_all(): array {
		return $this->collection;
	}

	/**
	 * Get an integration.
	 *
	 * @since 1.0.0
	 *
	 * @param string $key Integration key.
	 *
	 * @return Integrations\Base
	 */
	public function get( string $key ): Integrations\Base {
		return $this->collection[ $key ];
	}
}
