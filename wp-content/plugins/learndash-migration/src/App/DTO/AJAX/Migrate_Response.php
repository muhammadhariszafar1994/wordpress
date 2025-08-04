<?php
/**
 * Migrate Response DTO for AJAX module.
 *
 * @since 1.0.0
 *
 * @package LearnDash\Migration
 */

namespace LearnDash\Migration\DTO\AJAX;

use Learndash_DTO;

/**
 * Migrate Response DTO class.
 *
 * @since 1.0.0
 */
class Migrate_Response extends Learndash_DTO {
	/**
	 * Flag to check if the whole process is completed.
	 *
	 * @since 1.0.0
	 *
	 * @var bool
	 */
	public $completed;

	/**
	 * New course ID.
	 *
	 * @since 1.0.0
	 *
	 * @var int
	 */
	public $new_course_id;

	/**
	 * New course URL.
	 *
	 * @since 1.0.0
	 *
	 * @var string
	 */
	public $new_course_url;

	/**
	 * Properties are being cast to the specified type on construction.
	 *
	 * @since 1.0.0
	 *
	 * @var array<string, string>
	 */
	protected $cast = [
		'completed'      => 'bool',
		'new_course_id'  => 'int',
		'new_course_url' => 'string',
	];
}
