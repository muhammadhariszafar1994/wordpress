<?php
/**
 * Question answer DTO class file.
 *
 * @since 1.0.0
 *
 * @package LearnDash\Migration
 */

namespace LearnDash\Migration\DTO;

use Learndash_DTO;
use WP_Post;

/**
 * Migration question answer DTO object class.
 *
 * @since 1.0.0
 */
class Answer extends Learndash_DTO {
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
	 * Flag to mark correct answer.
	 *
	 * @since 1.0.0
	 *
	 * @var boolean
	 */
	public $is_correct;

	/**
	 * Meta.
	 *
	 * @since 1.0.0
	 *
	 * @var array<string, array<mixed>>
	 */
	public $meta;

	/**
	 * Custom parameters.
	 *
	 * @since 1.0.0
	 *
	 * @var array<string, mixed>
	 */
	public $params;

	/**
	 * Properties types.
	 *
	 * @since 1.0.0
	 *
	 * @var array<string, string>
	 */
	protected $cast = [
		'title'      => 'string',
		'content'    => 'string',
		'post'       => 'object',
		'is_correct' => 'boolean',
		'meta'       => 'array',
		'params'     => 'array',
	];
}
