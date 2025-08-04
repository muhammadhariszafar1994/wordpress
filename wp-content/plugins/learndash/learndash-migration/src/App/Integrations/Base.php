<?php
/**
 * Abstract base integration class file.
 *
 * @since 1.0.0
 *
 * @package LearnDash\Migration
 */

namespace LearnDash\Migration\Integrations;

use LearnDash\Core\App;
use LearnDash\Migration\DTO;
use LearnDash\Migration\Repository;
use WP_Post;

/**
 * Abstract base integration class.
 *
 * @since 1.0.0
 */
abstract class Base {
	/**
	 * Single choice question type key.
	 *
	 * @since 1.0.0
	 *
	 * @var string
	 */
	public static $question_type_key_single_choice = 'single';

	/**
	 * Multiple choice question type key.
	 *
	 * @since 1.0.0
	 *
	 * @var string
	 */
	public static $question_type_key_multiple_choice = 'multiple';

	/**
	 * Free question type key.
	 *
	 * @since 1.0.0
	 *
	 * @var string
	 */
	public static $question_type_key_free_choice = 'free_answer';

	/**
	 * Sort question type key.
	 *
	 * @since 1.0.0
	 *
	 * @var string
	 */
	public static $question_type_key_sorting_choice = 'sort_answer';

	/**
	 * Matrix sort question type key.
	 *
	 * @since 1.0.0
	 *
	 * @var string
	 */
	public static $question_type_key_matrix_sorting_choice = 'matrix_sort_answer';

	/**
	 * Fill in the blank question type key.
	 *
	 * @since 1.0.0
	 *
	 * @var string
	 */
	public static $question_type_key_fill_in_the_blank = 'cloze_answer';

	/**
	 * Assessment question type key.
	 *
	 * @since 1.0.0
	 *
	 * @var string
	 */
	public static $question_type_key_assessment = 'assessment_answer';

	/**
	 * Essay question type key.
	 *
	 * @since 1.0.0
	 *
	 * @var string
	 */
	public static $question_type_key_essay = 'essay';

	/**
	 * Integration key.
	 *
	 * @since 1.0.0
	 *
	 * @var string
	 */
	public $key;

	/**
	 * Integration label.
	 *
	 * @since 1.0.0
	 *
	 * @var string
	 */
	public $label;

	/**
	 * Repository class.
	 *
	 * @since 1.0.0
	 *
	 * @var Repository
	 */
	protected $repository;

	/**
	 * Source LMS data.
	 *
	 * @since 1.0.0
	 *
	 * @var DTO\Course
	 */
	protected $course_data;

	/**
	 * LearnDash post type slugs.
	 *
	 * @since 1.0.0
	 *
	 * @var array<string, string>
	 */
	protected $ld_post_type_slugs;

	/**
	 * Mapped post types between LearnDash post type key and integration.
	 *
	 * @since 1.0.0
	 *
	 * @var array<string, string|null>
	 */
	public $mapped_post_types;

	/**
	 * Mapped post meta keys between LearnDash post types and integrations.
	 *
	 * @since 1.0.0
	 *
	 * @var array<string, array<string, string>>
	 */
	protected $mapped_meta_keys;

	/**
	 * Mapped post meta keys between LearnDash post types and integrations used for
	 * LD specific post type settings.
	 *
	 * @since 1.0.0
	 *
	 * @var array<string, array<string, string>>
	 */
	protected $mapped_setting_keys;

	/**
	 * Get integration key.
	 *
	 * @since 1.0.0
	 *
	 * @return string
	 */
	abstract protected function get_key(): string;

	/**
	 * Get integration label.
	 *
	 * @since 1.0.0
	 *
	 * @return string
	 */
	abstract protected function get_label(): string;

	/**
	 * Constructor.
	 *
	 * @since 1.0.0
	 */
	public function __construct() {
		$this->key   = $this->get_key();
		$this->label = $this->get_label();

		$this->ld_post_type_slugs = [
			'course'      => learndash_get_post_type_slug( 'course' ),
			'lesson'      => learndash_get_post_type_slug( 'lesson' ),
			'topic'       => learndash_get_post_type_slug( 'topic' ),
			'quiz'        => learndash_get_post_type_slug( 'quiz' ),
			'question'    => learndash_get_post_type_slug( 'question' ),
			'certificate' => learndash_get_post_type_slug( 'certificate' ),
			'group'       => learndash_get_post_type_slug( 'group' ),
			'assignment'  => learndash_get_post_type_slug( 'assignment' ),
		];

		$this->set_repository();
	}

	/**
	 * Set repository property.
	 *
	 * @since 1.0.0
	 *
	 * @return void
	 */
	protected function set_repository(): void {
		$repository = App::get( Repository::class );

		if ( $repository instanceof Repository ) {
			$this->repository = $repository;
		}
	}

	/**
	 * Format course data from source LMS to match with the LD data.
	 *
	 * @since 1.0.0
	 *
	 * @return void
	 */
	abstract protected function format_course(): void;

	/**
	 * Format lesson data from source LMS to match with the LD data.
	 *
	 * @since 1.0.0
	 *
	 * @param DTO\Lesson $lesson Lesson DTO object.
	 *
	 * @return DTO\Lesson Formatted lesson DTO object.
	 */
	abstract protected function format_lesson( DTO\Lesson $lesson ): DTO\Lesson;

	/**
	 * Format topic data from source LMS to match with the LD data.
	 *
	 * @since 1.0.0
	 *
	 * @param DTO\Topic $topic Topic DTO object.
	 *
	 * @return DTO\Topic Formatted topic DTO object.
	 */
	abstract protected function format_topic( DTO\Topic $topic ): DTO\Topic;

	/**
	 * Format quiz data from source LMS to match with the LD data.
	 *
	 * @since 1.0.0
	 *
	 * @param DTO\Quiz $quiz Quiz DTO object.
	 *
	 * @return DTO\Quiz Formatted quiz DTO object.
	 */
	abstract protected function format_quiz( DTO\Quiz $quiz ): DTO\Quiz;

	/**
	 * Format question data from source LMS to match with the LD data.
	 *
	 * @since 1.0.0
	 *
	 * @param DTO\Question $question Question DTO object.
	 *
	 * @return DTO\Question Formatted question DTO object.
	 */
	abstract protected function format_question( DTO\Question $question ): DTO\Question;

	/**
	 * Format question type data from source LMS to match with the LD data.
	 *
	 * @since 1.0.0
	 *
	 * @param string $type Original question type.
	 *
	 * @return string Formatted question type
	 */
	abstract protected function format_question_type( string $type ): string;

	/**
	 * Get course data from the source LMS and set the course_data property.
	 *
	 * @since 1.0.0
	 *
	 * @param int $course_id Source course ID.
	 *
	 * @return void
	 */
	public function pull_course( int $course_id ): void {
		$this->course_data = DTO\Course::create(
			[
				'post'    => get_post( $course_id ),
				'meta'    => get_post_meta( $course_id ),
				'lessons' => $this->get_lessons( $course_id ),
				'quizzes' => $this->get_quizzes( $course_id, $course_id ),
			]
		);
	}

	/**
	 * Get integration-specific lessons.
	 *
	 * @since 1.0.0
	 *
	 * @param int $course_id Course ID.
	 *
	 * @return array<DTO\Lesson> List of lesson DTO objects.
	 */
	abstract protected function get_lessons( int $course_id ): array;

	/**
	 * Get integration-specific topics.
	 *
	 * @since 1.0.0
	 *
	 * @param int $course_id Course ID.
	 * @param int $lesson_id Lesson ID.
	 *
	 * @return array<DTO\Topic> List of topic DTO objects.
	 */
	abstract protected function get_topics( int $course_id, int $lesson_id ): array;

	/**
	 * Get integration-specific quizzes.
	 *
	 * @since 1.0.0
	 *
	 * @param int $course_id Course ID.
	 * @param int $parent_id Parent ID.
	 *
	 * @return array<DTO\Quiz> List of quiz DTO objects.
	 */
	abstract protected function get_quizzes( int $course_id, int $parent_id = 0 ): array;

	/**
	 * Get integration-specific quiz questions.
	 *
	 * @since 1.0.0
	 *
	 * @param int $quiz_id Quiz ID.
	 *
	 * @return array<DTO\Question> List of quiz DTO objects.
	 */
	abstract protected function get_questions( int $quiz_id ): array;

	/**
	 * Get integration-specific question answers.
	 *
	 * @since 1.0.0
	 *
	 * @param int $question_id Question ID.
	 *
	 * @return array<DTO\Answer> List of answer DTO objects.
	 */
	abstract protected function get_answers( int $question_id ): array;

	/**
	 * Format post settings.
	 *
	 * @since 1.0.0
	 *
	 * @param object $object Post object.
	 *
	 * @return array<string, array<mixed>> List of formatted settings.
	 */
	protected function format_settings( object $object ): array {
		$type = isset( $object->post->post_type )
			? learndash_get_post_type_key( $object->post->post_type )
			: '';

		$settings = [];
		if ( isset( $object->meta ) && is_array( $object->meta ) ) {
			foreach ( $object->meta as $key => $values ) {
				if (
					isset( $this->mapped_setting_keys[ $type ] )
					&& in_array( $key, array_keys( $this->mapped_setting_keys[ $type ] ), true )
				) {
					$key_name              = $this->mapped_setting_keys[ $type ][ $key ];
					$settings[ $key_name ] = $values;
				}
			}
		}

		/**
		 * Post object settings filter hook.
		 *
		 * Post object settings are WP post meta data that are specific to LearnDash specific post types, e.g. Course price, Lesson expiration, etc. Usually stored in LD post type meta key such as "sfwd-courses", "sfwd-lessons", etc.
		 *
		 * @since 1.0.0
		 *
		 * @param array<string, array<mixed>> $settings    Formatted settings.
		 * @param object                      $object      Post DTO object.
		 * @param Base                        $integration Current integration object.
		 *
		 * @return array<string, array<mixed>> New formatted settings.
		 */
		return apply_filters( 'learndash_migration_format_settings', $settings, $object, $this );
	}

	/**
	 * Format DTO object meta data before being migrated.
	 *
	 * @since 1.0.0
	 *
	 * @param object $object Any migration post DTO object.
	 *
	 * @return array<string, array<mixed>>
	 */
	public function format_meta( object $object ): array {
		$type = isset( $object->post->post_type )
			? learndash_get_post_type_key( $object->post->post_type )
			: '';

		$meta = [];
		if (
			isset( $object->meta )
			&& is_array( $object->meta )
		) {
			foreach ( $object->meta as $key => $values ) {
				if (
					isset( $this->mapped_setting_keys[ $type ] )
					&& in_array( $key, array_keys( $this->mapped_meta_keys[ $type ] ), true )
				) {
					$key_name          = $this->mapped_meta_keys[ $type ][ $key ];
					$meta[ $key_name ] = $values;
				}
			}
		}

		/**
		 * Extra meta data.
		 *
		 * Values wrapped in array to match with get_post_meta( $post_id ) value.
		 */

		$extra_meta = [
			'_ld_migration_imported_from' => [ $this->key ],
		];

		if (
			isset( $object->post )
			&& $object->post instanceof WP_Post
		) {
			$extra_meta['_ld_migration_source_post_id'] = [ $object->post->ID ];
			$extra_meta['_thumbnail_id']                = [ get_post_meta( $object->post->ID, '_thumbnail_id', true ) ];
		}

		$meta = array_merge( $meta, $extra_meta );

		/**
		 * Post object meta filter hook.
		 *
		 * Post object meta is WP post meta data that are not specific to LearnDash specific post types.
		 *
		 * @since 1.0.0
		 *
		 * @param array<string, array<mixed>> $meta        Formatted meta.
		 * @param object                      $object      Post DTO object.
		 * @param Base                        $integration Current integration object.
		 *
		 * @return array<string, array<mixed>> New formatted meta.
		 */
		return apply_filters( 'learndash_migration_format_meta', $meta, $object, $this );
	}

	/**
	 * Push course to LearnDash database.
	 *
	 * @since 1.0.0
	 *
	 * @return int New course ID.
	 */
	protected function push_course(): int {
		$course_id = $this->repository->create_course( $this->course_data );

		foreach ( $this->course_data->lessons as $key => $lesson ) {
			if ( $lesson instanceof DTO\Section ) {
				$this->push_section( $lesson, $course_id );
			} elseif ( $lesson instanceof DTO\Lesson ) {
				$this->push_lesson( $lesson, $course_id );
			}
		}

		foreach ( $this->course_data->quizzes as $key => $quiz ) {
			$this->push_quiz( $quiz, $course_id );
		}

		return $course_id;
	}

	/**
	 * Push section to LearnDash database.
	 *
	 * @since 1.0.0
	 *
	 * @param DTO\Section $section   Section DTO object.
	 * @param int         $course_id Course ID.
	 *
	 * @return int New section ID.
	 */
	protected function push_section( DTO\Section $section, int $course_id ): int {
		$section_id = $this->repository->create_section( $section, $course_id );

		foreach ( $section->lessons as $key => $lesson ) {
			$this->push_lesson( $lesson, $course_id );
		}

		return $section_id;
	}

	/**
	 * Push lesson to LearnDash database.
	 *
	 * @since 1.0.0
	 *
	 * @param DTO\Lesson $lesson Lesson DTO object.
	 * @param int        $course_id Course ID.
	 *
	 * @return int New lesson ID.
	 */
	protected function push_lesson( DTO\Lesson $lesson, int $course_id ): int {
		$lesson_id = $this->repository->create_lesson( $lesson, $course_id );

		foreach ( $lesson->topics as $key => $topic ) {
			$this->push_topic( $topic, $course_id, $lesson_id );
		}

		foreach ( $lesson->quizzes as $key => $quiz ) {
			$this->push_quiz( $quiz, $course_id, $lesson_id );
		}

		return $lesson_id;
	}

	/**
	 * Push topic to LearnDash database.
	 *
	 * @since 1.0.0
	 *
	 * @param DTO\Topic $topic Topic DTO object.
	 * @param int       $course_id Course ID.
	 * @param int       $lesson_id Lesson ID.
	 *
	 * @return int New topic ID.
	 */
	protected function push_topic( DTO\Topic $topic, int $course_id, int $lesson_id ): int {
		$topic_id = $this->repository->create_topic( $topic, $course_id, $lesson_id );

		foreach ( $topic->quizzes as $key => $quiz ) {
			$this->push_quiz( $quiz, $course_id, $lesson_id, $topic_id );
		}

		return $topic_id;
	}

	/**
	 * Push quiz to LearnDash database.
	 *
	 * @since 1.0.0
	 *
	 * @param DTO\Quiz $quiz Quiz DTO object.
	 * @param int      $course_id Course ID.
	 * @param int      $lesson_id Lesson ID.
	 * @param int      $topic_id Topic ID.
	 *
	 * @return int New quiz ID.
	 */
	protected function push_quiz( DTO\Quiz $quiz, int $course_id, int $lesson_id = 0, int $topic_id = 0 ): int {
		if ( ! empty( $topic_id ) ) {
			$parent_id = $topic_id;
		} elseif ( ! empty( $lesson_id ) ) {
			$parent_id = $lesson_id;
		} else {
			$parent_id = $course_id;
		}

		$quiz_id = $this->repository->create_quiz( $quiz, $course_id, $parent_id );

		foreach ( $quiz->questions as $key => $question ) {
			$this->push_question( $question, $quiz_id );
		}

		return $quiz_id;
	}

	/**
	 * Push question to LearnDash database.
	 *
	 * @since 1.0.0
	 *
	 * @param DTO\Question $question Question DTO object.
	 * @param int          $quiz_id Quiz ID.
	 *
	 * @return int New question ID.
	 */
	protected function push_question( DTO\Question $question, int $quiz_id ): int {
		return $this->repository->create_question( $question, $quiz_id );
	}

	/**
	 * Migrate course from source LMS to LearnDash.
	 *
	 * @since 1.0.0
	 *
	 * @param int $course_id Source course ID.
	 *
	 * @return int New course ID.
	 */
	public function migrate_course( int $course_id ): int {
		// Get source LMS data from database.
		$this->pull_course( $course_id );

		// Format the data to match with LearnDash data.
		$this->format_course();

		// Push the formatted data to DB as LearnDash objects.
		return $this->push_course();
	}
}
