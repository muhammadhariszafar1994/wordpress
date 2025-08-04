<?php
/**
 * Migrate Request DTO for AJAX module.
 *
 * @since 1.0.0
 *
 * @package LearnDash\Migration
 */

namespace LearnDash\Migration\DTO\AJAX;

use Learndash_DTO;

/**
 * Migrate Request DTO class.
 *
 * @since 1.0.0
 */
class Migrate_Request extends Learndash_DTO {
	/**
	 * Migration integration source key.
	 *
	 * @since 1.0.0
	 *
	 * @var string
	 */
	public $source;

	/**
	 * Source course ID.
	 *
	 * @since 1.0.0
	 *
	 * @var int
	 */
	public $course_id;

	/**
	 * Step number in the migration batch process.
	 *
	 * @since 1.0.0
	 *
	 * @var int
	 */
	public $step;

	/**
	 * Properties are being cast to the specified type on construction.
	 *
	 * @since 1.0.0
	 *
	 * @var array<string, string>
	 */
	protected $cast = [
		'source'    => 'string',
		'course_id' => 'int',
		'step'      => 'int',
	];
}
