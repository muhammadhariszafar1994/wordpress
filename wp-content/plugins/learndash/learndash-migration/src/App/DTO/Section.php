<?php
/**
 * Section DTO class file.
 *
 * @since 1.0.0
 *
 * @package LearnDash\Migration
 */

namespace LearnDash\Migration\DTO;

use Learndash_DTO;

/**
 * Migration lesson DTO object class.
 *
 * @since 1.0.0
 */
class Section extends Learndash_DTO implements Interfaces\Course_Child {
	/**
	 * ID.
	 *
	 * @since 1.0.0
	 *
	 * @var int
	 */
	public $id;

	/**
	 * Title.
	 *
	 * @since 1.0.0
	 *
	 * @var string
	 */
	public $title;

	/**
	 * Order.
	 *
	 * @since 1.0.0
	 *
	 * @var int
	 */
	public $order;

	/**
	 * Type.
	 *
	 * @since 1.0.0
	 *
	 * @var string
	 */
	public $type = 'section-heading';

	/**
	 * Lessons.
	 *
	 * @since 1.0.0
	 *
	 * @var array<Lesson>
	 */
	public $lessons;

	/**
	 * Properties types.
	 *
	 * @since 1.0.0
	 *
	 * @var array<string, string>
	 */
	protected $cast = [
		'id'      => 'int',
		'title'   => 'string',
		'order'   => 'int',
		'type'    => 'string',
		'lessons' => 'array',
	];
}
