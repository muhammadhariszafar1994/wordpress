<?php
/**
 * Quiz DTO class file.
 *
 * @since 1.0.0
 *
 * @package LearnDash\Migration
 */

namespace LearnDash\Migration\DTO;

use Learndash_DTO;
use WP_Post;

/**
 * Migration quiz DTO object class.
 *
 * @since 1.0.0
 */
class Quiz extends Learndash_DTO {
	/**
	 * Title.
	 *
	 * @since 1.0.0
	 *
	 * @var string
	 */
	public $title;

	/**
	 * Content.
	 *
	 * @since 1.0.0
	 *
	 * @var string
	 */
	public $content;

	/**
	 * Post object.
	 *
	 * It can be either WP_Post or standard object because not all source post is a WP_Post object.
	 *
	 * @since 1.0.0
	 *
	 * @var WP_Post|object
	 */
	public $post;

	/**
	 * Settings. Used to set LD post type specific settings.
	 *
	 * @since 1.0.0
	 *
	 * @var array<string, array<mixed>>
	 */
	public $settings;

	/**
	 * Meta.
	 *
	 * @since 1.0.0
	 *
	 * @var array<string, array<mixed>>
	 */
	public $meta;

	/**
	 * Course ID the quiz belongs to.
	 *
	 * @since 1.0.0
	 *
	 * @var int
	 */
	public $course_id;

	/**
	 * Lesson ID the quiz belongs to.
	 *
	 * @since 1.0.0
	 *
	 * @var int
	 */
	public $lesson_id;

	/**
	 * Topic ID the quiz belongs to.
	 *
	 * @since 1.0.0
	 *
	 * @var int
	 */
	public $topic_id;

	/**
	 * Parent ID the quiz belongs to.
	 *
	 * @since 1.0.0
	 *
	 * @var int
	 */
	public $parent_id;

	/**
	 * Questions.
	 *
	 * @since 1.0.0
	 *
	 * @var array<Question>
	 */
	public $questions;

	/**
	 * Properties types.
	 *
	 * @since 1.0.0
	 *
	 * @var array<string, string>
	 */
	protected $cast = [
		'title'     => 'string',
		'content'   => 'string',
		'post'      => 'object',
		'settings'  => 'array',
		'meta'      => 'array',
		'course_id' => 'int',
		'lesson_id' => 'int',
		'topic_id'  => 'int',
		'parent_id' => 'int',
		'questions' => 'array',
	];
}
