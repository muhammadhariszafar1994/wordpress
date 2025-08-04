<?php
/**
 * Tutor LMS integration class file.
 *
 * @since 1.0.0
 *
 * @package LearnDash\Migration
 */

namespace LearnDash\Migration\Integrations;

use LDLMS_Post_Types;
use LearnDash\Migration\DTO;
use stdClass;

/**
 * Tutor LMS integration class.
 *
 * @since 1.0.0
 */
class TutorLMS extends Base {
	/**
	 * Mapped post types between LearnDash post type key and integration.
	 *
	 * @since 1.0.0
	 *
	 * @var array{
	 *  course: string,
	 *  lesson: string,
	 *  topic: string,
	 *  quiz: string,
	 *  question: null,
	 *  certificate: null,
	 *  group: null,
	 *  assignment: null
	 * }
	 */
	public $mapped_post_types;

	/**
	 * Get integration key.
	 *
	 * @since 1.0.0
	 *
	 * @return string
	 */
	protected function get_key(): string {
		return 'tutor';
	}

	/**
	 * Get integration label.
	 *
	 * @since 1.0.0
	 *
	 * @return string
	 */
	protected function get_label(): string {
		return 'Tutor LMS';
	}

	/**
	 * Constructor.
	 *
	 * @since 1.0.0
	 */
	public function __construct() {
		$this->mapped_post_types = [
			'course'      => 'courses',
			'lesson'      => 'topics',
			'topic'       => 'lesson',
			'quiz'        => 'tutor_quiz',
			'question'    => null,
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

		add_filter( 'learndash_migration_format_settings', [ $this, 'filter_format_settings' ], 10, 3 );
	}

	/**
	 * Format course data from source LMS to match with the LD data.
	 *
	 * @since 1.0.0
	 *
	 * @return void
	 */
	public function format_course(): void {
		if ( isset( $this->course_data->post->post_type ) ) {
			$this->course_data->post->post_type = $this->ld_post_type_slugs[ LDLMS_Post_Types::COURSE ];
		}

		$this->course_data->settings = $this->format_settings( $this->course_data );

		$this->course_data->meta = $this->format_meta( $this->course_data );

		foreach ( $this->course_data->lessons as $key => $lesson ) {
			if ( $lesson instanceof DTO\Lesson ) {
				$lesson = $this->format_lesson( $lesson );
			}

			$this->course_data->lessons[ $key ] = $lesson;
		}
	}

	/**
	 * Format lesson data from source LMS to match with the LD data.
	 *
	 * @since 1.0.0
	 *
	 * @param DTO\Lesson $lesson Lesson DTO object.
	 *
	 * @return DTO\Lesson Formatted lesson DTO object.
	 */
	public function format_lesson( DTO\Lesson $lesson ): DTO\Lesson {
		$lesson_object               = new stdClass();
		$lesson_object->post_title   = $lesson->title;
		$lesson_object->post_content = $lesson->content;
		$lesson_object->post_type    = $this->ld_post_type_slugs[ LDLMS_Post_Types::LESSON ];

		$lesson->post = $lesson_object;

		foreach ( $lesson->topics as $key => $topic ) {
			$topic = $this->format_topic( $topic );

			$lesson->topics[ $key ] = $topic;
		}

		foreach ( $lesson->quizzes as $key => $quiz ) {
			$quiz = $this->format_quiz( $quiz );

			$lesson->quizzes[ $key ] = $quiz;
		}

		return $lesson;
	}

	/**
	 * Format topic data from source LMS to match with the LD data.
	 *
	 * @since 1.0.0
	 *
	 * @param DTO\Topic $topic Topic DTO object.
	 *
	 * @return DTO\Topic Formatted topic DTO object.
	 */
	public function format_topic( DTO\Topic $topic ): DTO\Topic {
		if ( isset( $topic->post->post_type ) ) {
			$topic->post->post_type = $this->ld_post_type_slugs[ LDLMS_Post_Types::TOPIC ];
		}

		$topic->settings = $this->format_settings( $topic );

		$topic->meta = $this->format_meta( $topic );

		return $topic;
	}

	/**
	 * Format quiz data from source LMS to match with the LD data.
	 *
	 * @since 1.0.0
	 *
	 * @param DTO\Quiz $quiz Quiz DTO object.
	 *
	 * @return DTO\Quiz Formatted quiz DTO object.
	 */
	public function format_quiz( DTO\Quiz $quiz ): DTO\Quiz {
		if ( isset( $quiz->post->post_type ) ) {
			$quiz->post->post_type = $this->ld_post_type_slugs[ LDLMS_Post_Types::QUIZ ];
		}

		$quiz->settings = $this->format_settings( $quiz );

		$quiz->meta = $this->format_meta( $quiz );

		foreach ( $quiz->questions as $key => $question ) {
			$question = $this->format_question( $question );

			$quiz->questions[ $key ] = $question;
		}

		return $quiz;
	}

	/**
	 * Format question data from source LMS to match with the LD data.
	 *
	 * @since 1.0.0
	 *
	 * @param DTO\Question $question Question DTO object.
	 *
	 * @return DTO\Question Formatted question DTO object.
	 */
	public function format_question( DTO\Question $question ): DTO\Question {
		if ( isset( $question->post->post_type ) ) {
			$question->post->post_type = $this->ld_post_type_slugs[ LDLMS_Post_Types::QUESTION ];
		}

		return $question;
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
	protected function get_lessons( int $course_id ): array {
		$tutor_lessons = get_posts(
			[
				'post_type'      => $this->mapped_post_types['lesson'],
				'post_parent'    => $course_id,
				'posts_per_page' => -1,
				'orderby'        => 'menu_order',
				'order'          => 'ASC',
			]
		);

		$lessons = array_map(
			function ( $tutor_lesson ) use ( $course_id ) {
				return DTO\Lesson::create(
					[
						'title'   => $tutor_lesson->post_title,
						'content' => $tutor_lesson->post_content,
						'post'    => $tutor_lesson,
						'meta'    => get_post_meta( $tutor_lesson->ID ),
						'topics'  => $this->get_topics( $course_id, $tutor_lesson->ID ),
						'quizzes' => $this->get_quizzes( $course_id, $tutor_lesson->ID ),
					]
				);
			},
			$tutor_lessons
		);

		return $lessons;
	}

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
	protected function get_topics( int $course_id, int $lesson_id ): array {
		$tutor_topics = get_posts(
			[
				'post_type'      => $this->mapped_post_types['topic'],
				'post_parent'    => $lesson_id,
				'posts_per_page' => -1,
				'orderby'        => 'menu_order',
				'order'          => 'ASC',
			]
		);

		$topics = array_map(
			function ( $tutor_topic ) {
				return DTO\Topic::create(
					[
						'title'   => $tutor_topic->post_title,
						'content' => $tutor_topic->post_content,
						'post'    => $tutor_topic,
						'meta'    => get_post_meta( $tutor_topic->ID ),
					]
				);
			},
			$tutor_topics
		);

		return $topics;
	}

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
	protected function get_quizzes( int $course_id, int $parent_id = 0 ): array {
		$tutor_quizzes = get_posts(
			[
				'post_type'      => $this->mapped_post_types['quiz'],
				'post_parent'    => $parent_id,
				'posts_per_page' => -1,
				'orderby'        => 'menu_order',
				'order'          => 'ASC',
			]
		);

		$quizzes = array_map(
			function ( $tutor_quiz ) use ( $course_id, $parent_id ) {
				return DTO\Quiz::create(
					[
						'title'     => $tutor_quiz->post_title,
						'content'   => $tutor_quiz->post_content,
						'post'      => get_post( $tutor_quiz->ID ),
						'meta'      => get_post_meta( $tutor_quiz->ID ),
						'course_id' => $course_id,
						'parent_id' => $parent_id,
						'questions' => $this->get_questions( $tutor_quiz->ID ),
					]
				);
			},
			$tutor_quizzes
		);

		return $quizzes;
	}

	/**
	 * Get integration-specific quiz questions.
	 *
	 * @since 1.0.0
	 *
	 * @param int $quiz_id Quiz ID.
	 *
	 * @return array<DTO\Question> List of quiz DTO objects.
	 */
	protected function get_questions( int $quiz_id ): array {
		global $wpdb;

		$tutor_questions = $wpdb->get_results(
			$wpdb->prepare(
				"SELECT *
					FROM {$wpdb->base_prefix}tutor_quiz_questions qq
					WHERE qq.quiz_id = %d
					ORDER BY qq.question_order ASC",
				$quiz_id
			)
		);

		$questions = array_map(
			function ( $tutor_question ) {
				$meta = [];
				foreach ( $tutor_question as $key => $value ) {
					if ( in_array(
						$key,
						[ 'question_title', 'question_description', 'question_type', 'question_order', 'question_mark' ],
						true
					) ) {
						continue;
					}

					// Wrap value in array to match meta data structure.
					$meta[ $key ] = [ $value ];
				}

				// Wrap value in array to match meta and settings data structure.

				$settings = [
					'points' => [ $tutor_question->question_mark ],
				];

				return DTO\Question::create(
					[
						'title'    => $tutor_question->question_title,
						'content'  => $tutor_question->question_description,
						'type'     => $this->format_question_type( $tutor_question->question_type ),
						'meta'     => $meta,
						'settings' => $settings,
						'answers'  => $this->get_answers( $tutor_question->question_id ),
					]
				);
			},
			$tutor_questions
		);

		return $questions;
	}

	/**
	 * Get integration-specific question answers.
	 *
	 * @since 1.0.0
	 *
	 * @param int $question_id Question ID.
	 *
	 * @return array<DTO\Answer> List of answer DTO objects.
	 */
	protected function get_answers( int $question_id ): array {
		global $wpdb;

		$question_type = $wpdb->get_var(
			$wpdb->prepare(
				"SELECT question_type
					FROM {$wpdb->base_prefix}tutor_quiz_questions
					WHERE question_id = %d",
				$question_id
			)
		);

		$tutor_answers = $wpdb->get_results(
			$wpdb->prepare(
				"SELECT *
					FROM {$wpdb->base_prefix}tutor_quiz_question_answers
					WHERE belongs_question_id = %d",
				$question_id
			)
		);

		$tutor_answers = array_filter(
			$tutor_answers,
			function ( $tutor_answer ) use ( $question_type ) {
				return $tutor_answer->belongs_question_type === $question_type;
			}
		);

		$answers = array_map(
			function ( $tutor_answer ) {
				$answer = [
					'title'      => $tutor_answer->answer_title,
					'is_correct' => $tutor_answer->is_correct ?? false,
				];

				unset( $tutor_answer->answer_title );

				if ( $tutor_answer->belongs_question_type === 'fill_in_the_blank' ) {
					$answer_substitutes = explode( '|', $tutor_answer->answer_two_gap_match );

					$answer_substitutes = array_map(
						function ( $answer_substitute ) {
							return '{' . trim( $answer_substitute ) . '}';
						},
						$answer_substitutes
					);

					$patterns = array_fill( 0, count( $answer_substitutes ), '/\{dash}/' );

					$answer['title'] = preg_replace( $patterns, $answer_substitutes, $answer['title'], 1 );
				}

				$meta = [];
				foreach ( $tutor_answer as $key => $value ) {
					$meta[ $key ] = [ $value ];
				}

				$answer['meta'] = $meta;

				return DTO\Answer::create( $answer );
			},
			$tutor_answers
		);

		return $answers;
	}

	/**
	 * Format question type data from source LMS to match with the LD data.
	 *
	 * @since 1.0.0
	 *
	 * @param string $orig_type Original question type.
	 *
	 * @return string Formatted question type
	 */
	protected function format_question_type( string $orig_type ): string {
		switch ( $orig_type ) {
			case 'single_choice':
				$type = 'single';
				break;

			case 'multiple_choice':
				$type = 'multiple';
				break;

			case 'fill_in_the_blank':
				$type = 'cloze_answer';
				break;

			case 'open_ended':
				$type = 'essay';
				break;

			default:
				$type = 'single';
				break;
		}

		return $type;
	}

	/**
	 * Filter formatted settings.
	 *
	 * Handler for `learndash_migration_format_settings` filter hook.
	 *
	 * @since 1.0.0
	 *
	 * @param array<string, array<mixed>> $settings Post formatted settings.
	 * @param object                      $object Post object.
	 * @param Base                        $integration Integration object.
	 *
	 * @return array<string, array<mixed>> Filtered formatted settings.
	 */
	public function filter_format_settings( $settings, object $object, Base $integration ): array {
		if ( $integration->key !== $this->key ) {
			return $settings;
		}

		if ( $object instanceof DTO\Course ) {
			$settings['course_price_type'] = [ 'paynow' ];

			foreach ( $object->meta['_tutor_course_settings'] as $tutor_setting ) {
				/**
				 * Tutor course setting.
				 *
				 * @var string $tutor_setting_temp Tutor course setting value.
				 */
				$tutor_setting_temp = $tutor_setting;

				$tutor_setting = maybe_unserialize( $tutor_setting_temp );

				if (
					is_array( $tutor_setting )
					&& ! empty( $tutor_setting['maximum_students'] )
				) {
					$settings['course_seats_limit'] = [ intval( $tutor_setting['maximum_students'] ) ];
				}
			}

			foreach ( $object->meta['_tutor_is_public_course'] as $tutor_setting ) {
				if (
					! empty( $tutor_setting ) && $tutor_setting === 'yes'
				) {
					$settings['course_price_type'] = [ 'open' ];
				}
			}
		}

		return $settings;
	}
}
