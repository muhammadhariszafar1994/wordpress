<?php
/**
 * Migrate AJAX module.
 *
 * @since 1.0.0
 *
 * @package LearnDash\Migration
 */

namespace LearnDash\Migration\AJAX;

use LearnDash\Core\Modules\AJAX\Request_Handler;
use LearnDash\Core\Utilities;
use LearnDash\Core\App;
use LearnDash\Migration\DTO;
use LearnDash\Migration\Integrations;

/**
 * Migrate AJAX class.
 *
 * @since 1.0.0
 */
class Migrate extends Request_Handler {
	/**
	 * AJAX action.
	 *
	 * @since 1.0.0
	 *
	 * @var string
	 */
	public static $action = 'learndash_migration_migrate';

	/**
	 * Request.
	 *
	 * @since 1.0.0
	 *
	 * @var DTO\AJAX\Migrate_Request
	 */
	public $request;

	/**
	 * Response.
	 *
	 * @since 1.0.0
	 *
	 * @var DTO\AJAX\Migrate_Response
	 */
	protected $response;

	/**
	 * New course ID.
	 *
	 * @since 1.0.0
	 *
	 * @var int
	 */
	private $new_course_id;

	/**
	 * Set up and build `request` property.
	 *
	 * @since 1.0.0
	 *
	 * @return void
	 */
	protected function set_up_request(): void {
		$args = Utilities\Sanitize::array(
			$_POST, // phpcs:ignore WordPress.Security.NonceVerification.Missing -- Nonce verification is done in Request_Handler::verify_nonce().
			function ( $value ) {
				return ! empty( $value )
					? sanitize_text_field( $value )
					: null;
			}
		);

		$this->request = DTO\AJAX\Migrate_Request::create( $args );
	}

	/**
	 * Process request using specified parameters and build `results` property.
	 *
	 * @since 1.0.0
	 *
	 * @return void
	 */
	protected function process(): void {
		$integrations = App::get( Integrations::class );

		if ( ! $integrations instanceof Integrations ) {
			$this->new_course_id = 0;
			return;
		}

		$created_course_id = $integrations->get( $this->request->source )->migrate_course( $this->request->course_id );

		$this->new_course_id = $created_course_id;
	}

	/**
	 * Prepare response.
	 *
	 * @since 1.0.0
	 *
	 * @return void
	 */
	protected function prepare_response(): void {
		$this->response = DTO\AJAX\Migrate_Response::create(
			[
				'completed'      => true,
				'new_course_id'  => $this->new_course_id,
				'new_course_url' => $this->get_new_course_url(),
			]
		);
	}

	/**
	 * Get new course URL.
	 *
	 * @since 1.0.0
	 *
	 * @return string
	 */
	private function get_new_course_url(): string {
		$new_course_url = get_edit_post_link( $this->new_course_id, 'link' );

		if ( ! $new_course_url ) {
			$new_course_url = '';
		}

		/**
		 * Filter hook to get new course URL.
		 *
		 * @since 1.0.0
		 *
		 * @param string                   $new_course_url New course edit URL.
		 * @param int                      $new_course_id  New course ID.
		 * @param DTO\AJAX\Migrate_Request $request        Request DTO object.
		 *
		 * @return string Returned new course URL.
		 */
		return apply_filters(
			'learndash_migration_new_course_url',
			$new_course_url,
			$this->new_course_id,
			$this->request
		);
	}
}
