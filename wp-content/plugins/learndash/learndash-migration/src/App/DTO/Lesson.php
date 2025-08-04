<?php
/**
 * Lesson DTO class file.
 *
 * @since 1.0.0
 *
 * @package LearnDash\Migration
 */

namespace LearnDash\Migration\DTO;

use Learndash_DTO;
use WP_Post;

/**
 * Migration lesson DTO object class.
 *
 * @since 1.0.0
 */
class Lesson extends Learndash_DTO implements Interfaces\Course_Child {
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
	 * Topics.
	 *
	 * @since 1.0.0
	 *
	 * @var array<Topic>
	 */
	public $topics;

	/**
	 * Quizzes.
	 *
	 * @since 1.0.0
	 *
	 * @var array<Quiz>
	 */
	public $quizzes;

	/**
	 * Properties types.
	 *
	 * @since 1.0.0
	 *
	 * @var array<string, string>
	 */
	protected $cast = [
		'title'    => 'string',
		'content'  => 'string',
		'post'     => 'object',
		'settings' => 'array',
		'meta'     => 'array',
		'topics'   => 'array',
		'quizzes'  => 'array',
	];
}
