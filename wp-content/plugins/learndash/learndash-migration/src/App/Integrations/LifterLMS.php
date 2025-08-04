<?php
/**
 * Lifter LMS integration class file.
 *
 * @since 1.1.0
 *
 * @package LearnDash\Migration
 */

namespace LearnDash\Migration\Integrations;

use LDLMS_Post_Types;
use LearnDash\Core\Utilities\Cast;
use LearnDash\Migration\DTO;
use WP_Post;

/**
 * Lifter LMS integration class.
 *
 * @since 1.1.0
 */
class LifterLMS extends Base {
	/**
	 * Get integration key.
	 *
	 * @since 1.1.0
	 *
	 * @return string
	 */
	protected function get_key(): string {
		return 'lifterlms';
	}

	/**
	 * Get integration label.
	 *
	 * @since 1.1.0
	 *
	 * @return string
	 */
	protected function get_label(): string {
		return 'Lifter LMS';
	}

	/**
	 * Constructor.
	 *
	 * @since 1.1.0
	 */
	public function __construct() {
		$this->mapped_post_types = [
			'course'      => 'course',
			'section'     => 'section',
			'lesson'      => 'lesson',
			'topic'       => null,
			'quiz'        => 'llms_quiz',
			'question'    => 'llms_question',
			'certificate' => null,
			'group'       => null,
			'assignment'  => null,
		];

		$this->mapped_setting_keys = [
			'course'   => [],
			'lesson'   => [],
			'topic'    => [],
			'quiz'     => [],
			'question' => [],
		];

		$this->mapped_meta_keys = [
			'course'   => [],
			'lesson'   => [],
			'topic'    => [],
			'quiz'     => [],
			'question' => [],
		];

		parent::__construct();
	}

	/**
	 * Formats course data from source LMS to match with the LearnDash data.
	 *
	 * @since 1.1.0
	 *
	 * @return void
	 */
	protected function format_course(): void {
		if ( isset( $this->course_data->post->post_type ) ) {
			$this->course_data->post->post_type = $this->ld_post_type_slugs[ LDLMS_Post_Types::COURSE ];
		}

		$this->course_data->settings = $this->format_settings( $this->course_data );
		$this->course_data->meta     = $this->format_meta( $this->course_data );

		foreach ( $this->course_data->lessons as $key => $child ) {
			if ( $child instanceof DTO\Lesson ) {
				$child = $this->format_lesson( $child );
			} elseif ( $child instanceof DTO\Section ) {
				$child = $this->format_section( $child );
			}

			$this->course_data->lessons[ $key ] = $child;
		}
	}

	/**
	 * Formats section data from source LMS to match with the LearnDash data.
	 *
	 * @since 1.1.0
	 *
	 * @param DTO\Section $section Section DTO object.
	 *
	 * @return DTO\Section Formatted section DTO object.
	 */
	private function format_section( DTO\Section $section ): DTO\Section {
		foreach ( $section->lessons as $key => $lesson ) {
			$lesson = $this->format_lesson( $lesson );

			$section->lessons[ $key ] = $lesson;
		}

		return $section;
	}

	/**
	 * Formats lesson data from source LMS to match with the LearnDash data.
	 *
	 * @since 1.1.0
	 *
	 * @param DTO\Lesson $lesson Lesson DTO object.
	 *
	 * @return DTO\Lesson Formatted lesson DTO object.
	 */
	protected function format_lesson( DTO\Lesson $lesson ): DTO\Lesson {
		if ( isset( $lesson->post->post_type ) ) {
			$lesson->post->post_type = $this->ld_post_type_slugs[ LDLMS_Post_Types::LESSON ];
		}

		$lesson->settings = $this->format_settings( $lesson );
		$lesson->meta     = $this->format_meta( $lesson );

		foreach ( $lesson->quizzes as $key => $quiz ) {
			$quiz = $this->format_quiz( $quiz );

			$lesson->quizzes[ $key ] = $quiz;
		}

		return $lesson;
	}

	/**
	 * Formats topic data from source LMS to match with the LearnDash data.
	 *
	 * @since 1.1.0
	 *
	 * @param DTO\Topic $topic Topic DTO object.
	 *
	 * @return DTO\Topic Formatted topic DTO object.
	 */
	protected function format_topic( DTO\Topic $topic ): DTO\Topic {
		return $topic;
	}

	/**
	 * Formats quiz data from source LMS to match with the LearnDash data.
	 *
	 * @since 1.1.0
	 *
	 * @param DTO\Quiz $quiz Quiz DTO object.
	 *
	 * @return DTO\Quiz Formatted quiz DTO object.
	 */
	protected function format_quiz( DTO\Quiz $quiz ): DTO\Quiz {
		if ( isset( $quiz->post->post_type ) ) {
			$quiz->post->post_type = $this->ld_post_type_slugs[ LDLMS_Post_Types::QUIZ ];
		}

		$quiz->settings = $this->format_settings( $quiz );
		$quiz->meta     = $this->format_meta( $quiz );

		foreach ( $quiz->questions as $key => $question ) {
			$question = $this->format_question( $question );

			$quiz->questions[ $key ] = $question;
		}

		return $quiz;
	}

	/**
	 * Formats question data from source LMS to match with the LearnDash data.
	 *
	 * @since 1.1.0
	 *
	 * @param DTO\Question $question Question DTO object.
	 *
	 * @return DTO\Question Formatted question DTO object.
	 */
	protected function format_question( DTO\Question $question ): DTO\Question {
		if ( isset( $question->post->post_type ) ) {
			$question->post->post_type = $this->ld_post_type_slugs[ LDLMS_Post_Types::QUESTION ];
		}

		$question->settings = $this->format_settings( $question );
		$question->meta     = $this->format_meta( $question );

		return $question;
	}

	/**
	 * Gets integration-specific sections that group lessons together.
	 *
	 * @since 1.1.0
	 *
	 * @param int $course_id Course ID.
	 *
	 * @return array<DTO\Section>
	 */
	private function get_sections( int $course_id ): array {
		$args = [
			'post_type'      => $this->mapped_post_types['section'] ?? '',
			'posts_per_page' => -1,
			'post_status'    => 'publish',
			'meta_query'     => [
				'query_course' => [
					'key'     => '_llms_parent_course',
					'value'   => $course_id,
					'compare' => '=',
				],
				'query_order'  => [
					'key'     => '_llms_order',
					'compare' => 'EXISTS',
				],
			],
			'orderby'        => 'query_order',
			'order'          => 'ASC',
		];

		$lifter_sections = get_posts( $args );
		$sections        = [];
		$section_order   = 0;

		foreach ( $lifter_sections as $key => $lifter_section ) {
			$lessons = $this->get_section_lessons( $course_id, $lifter_section->ID );

			$sections[ $key ] = DTO\Section::create(
				[
					'id'      => $lifter_section->ID,
					'title'   => $lifter_section->post_title,
					'order'   => $section_order,
					'lessons' => $lessons,
				]
			);

			// Order for the next section.
			++$section_order;
			$section_order = $section_order + count( $lessons );
		}

		return $sections;
	}

	/**
	 * Gets section lessons.
	 *
	 * @since 1.1.0
	 *
	 * @param int $course_id  Lifter course ID.
	 * @param int $section_id Lifter section ID.
	 *
	 * @return DTO\Lesson[]
	 */
	private function get_section_lessons( int $course_id, int $section_id ): array {
		$args = [
			'post_type'      => $this->mapped_post_types['lesson'] ?? '',
			'posts_per_page' => -1,
			'post_status'    => 'publish',
			'meta_query'     => [
				'query_section' => [
					'key'     => '_llms_parent_section',
					'value'   => $section_id,
					'compare' => '=',
				],
				'query_course'  => [
					'key'     => '_llms_parent_course',
					'value'   => $course_id,
					'compare' => '=',
				],
				'query_order'   => [
					'key'     => '_llms_order',
					'compare' => 'EXISTS',
				],
			],
			'orderby'        => 'query_order',
			'order'          => 'ASC',
		];

		$lessons = get_posts( $args );

		return $this->create_lessons_object( $lessons, $course_id );
	}

	/**
	 * Creates lessons DTO object from existing list of lesson posts.
	 *
	 * @since 1.1.0
	 *
	 * @param WP_Post[] $lifter_lessons Lifter lesson posts.
	 * @param int       $course_id      Lifter course ID.
	 *
	 * @return DTO\Lesson[]
	 */
	private function create_lessons_object( array $lifter_lessons, int $course_id ): array {
		$lessons = array_map(
			function ( $lifter_lesson ) use ( $course_id ) {
				return DTO\Lesson::create(
					[
						'title'   => $lifter_lesson->post_title,
						'content' => $lifter_lesson->post_content,
						'post'    => $lifter_lesson,
						'meta'    => get_post_meta( $lifter_lesson->ID ),
						'quizzes' => $this->get_quizzes( $course_id, $lifter_lesson->ID ),
					]
				);
			},
			$lifter_lessons
		);

		return $lessons;
	}

	/**
	 * Gets integration-specific lessons.
	 *
	 * @since 1.1.0
	 *
	 * @param int $course_id Course ID.
	 *
	 * @return array<DTO\Lesson|DTO\Section> List of lesson or section DTO objects.
	 */
	protected function get_lessons( int $course_id ): array {
		return $this->get_sections( $course_id );
	}

	/**
	 * Get integration-specific topics.
	 *
	 * @since 1.1.0
	 *
	 * @param int $course_id Course ID.
	 * @param int $lesson_id Lesson ID.
	 *
	 * @return array<DTO\Topic> List of topic DTO objects.
	 */
	protected function get_topics( int $course_id, int $lesson_id ): array {
		return [];
	}

	/**
	 * Gets integration-specific quizzes.
	 *
	 * @since 1.1.0
	 *
	 * @param int $course_id Course ID.
	 * @param int $parent_id Parent ID.
	 *
	 * @return array<DTO\Quiz> List of quiz DTO objects.
	 */
	protected function get_quizzes( int $course_id, int $parent_id = 0 ): array {
		$lifter_quizzes = get_posts(
			[
				'post_type'      => $this->mapped_post_types['quiz'] ?? '',
				'posts_per_page' => -1,
				'post_status'    => 'any', // Lifter LMS store quizzes as draft.
				'meta_query'     => [
					'query_lesson' => [
						'key'     => '_llms_lesson_id',
						'value'   => $parent_id,
						'compare' => '=',
					],
				],
			]
		);

		$quizzes = array_map(
			function ( $lifter_quiz ) use ( $course_id, $parent_id ) {
				return DTO\Quiz::create(
					[
						'title'     => $lifter_quiz->post_title,
						'content'   => $lifter_quiz->post_content,
						'post'      => get_post( $lifter_quiz->ID ),
						'meta'      => get_post_meta( $lifter_quiz->ID ),
						'course_id' => $course_id,
						'parent_id' => $parent_id,
						'questions' => $this->get_questions( $lifter_quiz->ID ),
					]
				);
			},
			$lifter_quizzes
		);

		return $quizzes;
	}

	/**
	 * Gets integration-specific quiz questions.
	 *
	 * @since 1.1.0
	 *
	 * @param int $quiz_id Quiz ID.
	 *
	 * @return array<DTO\Question> List of quiz DTO objects.
	 */
	protected function get_questions( int $quiz_id ): array {
		$lifter_questions = get_posts(
			[
				'post_type'      => $this->mapped_post_types['question'] ?? '',
				'post_status'    => 'publish',
				'posts_per_page' => -1,
				'meta_query'     => [
					[
						'key'     => '_llms_parent_id',
						'value'   => $quiz_id,
						'compare' => '=',
					],
				],
			]
		);

		$questions = array_map(
			function ( $lifter_question ) {
				$question_type = $this->get_question_type( $lifter_question->ID );
				$question_type = ! empty( $question_type ) ? $question_type : 'choice';

				return DTO\Question::create(
					[
						'title'   => $lifter_question->post_title,
						'content' => $lifter_question->post_content,
						'type'    => $this->format_question_type( $question_type ),
						'meta'    => get_post_meta( $lifter_question->ID ),
						'answers' => $this->get_answers( $lifter_question->ID, $question_type ),
					]
				);
			},
			$lifter_questions
		);

		return $questions;
	}

	/**
	 * Gets question type.
	 *
	 * @since 1.1.0
	 *
	 * @param int $question_id Question ID.
	 *
	 * @return string Question type.
	 */
	private function get_question_type( int $question_id ): string {
		return Cast::to_string(
			get_post_meta( $question_id, '_llms_question_type', true )
		);
	}

	/**
	 * Gets integration-specific question answers.
	 *
	 * @since 1.1.0
	 *
	 * @param int    $question_id   Question ID.
	 * @param string $question_type Lifter question type.
	 *
	 * @return array<DTO\Answer> List of answer DTO objects.
	 */
	protected function get_answers( int $question_id, string $question_type = 'choice' ): array {
		global $wpdb;

		$lifter_answers = $wpdb->get_results(
			$wpdb->prepare(
				"SELECT meta_value
					FROM {$wpdb->prefix}postmeta
					WHERE post_id = %d
					  AND meta_key LIKE %s
					ORDER BY meta_key",
				$question_id,
				'_llms_choice_%'
			)
		);

		foreach ( $lifter_answers as $key => $lifter_answer ) {
			$lifter_answers[ $key ] = maybe_unserialize( $lifter_answer->meta_value );
		}

		$answers = array_map(
			function ( $lifter_answer ) use ( $question_type ) {
				if ( $question_type === 'picture_choice' ) {
					$answer_title = wp_get_attachment_image( $lifter_answer['choice']['id'] );

					$params = [
						'html' => true,
					];
				} else {
					$answer_title = $lifter_answer['choice'];
					$params       = [];
				}

				$is_correct = isset( $lifter_answer['correct'] )
				&& $lifter_answer['correct'] === true
					? true
					: null;

				$answer = [
					'title'      => $answer_title,
					'is_correct' => $is_correct,
					'params'     => $params,
				];

				return DTO\Answer::create( $answer );
			},
			$lifter_answers
		);

		return $answers;
	}

	/**
	 * Formats question type data from source LMS to match with the LearnDash data.
	 *
	 * @since 1.1.0
	 *
	 * @param string $orig_type Original question type.
	 *
	 * @return string Formatted question type
	 */
	protected function format_question_type( string $orig_type ): string {
		switch ( $orig_type ) {
			case 'choice':
			case 'picture_choice':
			default:
				$type = static::$question_type_key_multiple_choice;
				break;

			case 'true_false':
				$type = static::$question_type_key_single_choice;
				break;
		}

		return $type;
	}
}
