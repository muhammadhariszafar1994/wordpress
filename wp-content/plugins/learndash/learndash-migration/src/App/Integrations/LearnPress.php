<?php
/**
 * LearnPress integration class file.
 *
 * @since 1.0.0
 *
 * @package LearnDash\Migration
 */

namespace LearnDash\Migration\Integrations;

use LDLMS_Post_Types;
use LearnDash\Migration\DTO;
use stdClass;
use WP_Post;

/**
 * LearnPress integration class.
 *
 * @since 1.0.0
 */
class LearnPress extends Base {
	/**
	 * Get integration key.
	 *
	 * @since 1.0.0
	 *
	 * @return string
	 */
	protected function get_key(): string {
		return 'learnpress';
	}

	/**
	 * Get integration label.
	 *
	 * @since 1.0.0
	 *
	 * @return string
	 */
	protected function get_label(): string {
		return 'LearnPress';
	}

	/**
	 * Constructor.
	 *
	 * @since 1.0.0
	 */
	public function __construct() {
		$this->mapped_post_types = [
			'course'      => 'lp_course',
			'lesson'      => 'lp_lesson',
			'topic'       => null,
			'quiz'        => 'lp_quiz',
			'question'    => 'lp_question',
			'certificate' => null,
			'group'       => null,
			'assignment'  => null,
		];

		$this->mapped_setting_keys = [
			'course' => [
				'_lp_price'        => 'course_price',
				'_lp_max_students' => 'course_seats_limit',
			],
			'quiz'   => [
				'_lp_retake_count' => 'repeats',
			],
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
	protected function format_course(): void {
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
	protected function format_lesson( DTO\Lesson $lesson ): DTO\Lesson {
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
	protected function format_topic( DTO\Topic $topic ): DTO\Topic {
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
	protected function format_quiz( DTO\Quiz $quiz ): DTO\Quiz {
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
	protected function format_question( DTO\Question $question ): DTO\Question {
		if ( isset( $question->post->post_type ) ) {
			$question->post->post_type = $this->ld_post_type_slugs[ LDLMS_Post_Types::QUESTION ];
		}

		if ( $question->post instanceof WP_Post ) {
			$question->post->post_content = $question->post->post_title;
		}

		if ( $question->type === static::$question_type_key_fill_in_the_blank ) {
			$question_title = preg_replace( '/\{.*?\}/', '____', $question->answers[0]->title ) ?? '';

			$question->title = $question_title;
		}

		$question->settings['hint'] = $question->meta['_lp_hint'];

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
		global $wpdb;

		$lp_lessons = $wpdb->get_results(
			$wpdb->prepare(
				"SELECT
					s.section_id AS id,
					s.section_name AS title,
					s.section_description AS content
					FROM {$wpdb->base_prefix}learnpress_sections s
					WHERE s.section_course_id = %d
					ORDER BY s.section_order ASC",
				$course_id
			)
		);

		$lessons = array_map(
			function ( $lp_lesson ) use ( $course_id ) {
				return DTO\Lesson::create(
					[
						'title'   => $lp_lesson->title,
						'content' => $lp_lesson->content,
						'topics'  => $this->get_topics( $course_id, $lp_lesson->id ),
						'quizzes' => $this->get_quizzes( $course_id, $lp_lesson->id ),
					]
				);
			},
			$lp_lessons
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
		global $wpdb;

		$lp_topics = $wpdb->get_results(
			$wpdb->prepare(
				"SELECT si.item_id AS id
					FROM {$wpdb->base_prefix}learnpress_sections s
					INNER JOIN {$wpdb->base_prefix}learnpress_section_items si
						ON s.section_id = si.section_id
					WHERE s.section_course_id = %d
						AND si.item_type = 'lp_lesson'
						AND s.section_id = %d
					ORDER BY si.item_order ASC",
				$course_id,
				$lesson_id
			)
		);

		$topics = array_map(
			function ( $lp_topic ) {
				return DTO\Topic::create(
					[
						'post' => get_post( $lp_topic->id ),
					]
				);
			},
			$lp_topics
		);

		return $topics;
	}

	/**
	 * Get integration-specific quizzes.
	 *
	 * TODO: use StellarWP DB library to replace wpdb operation.
	 *
	 * @since 1.0.0
	 *
	 * @param int $course_id Course ID.
	 * @param int $parent_id Parent ID.
	 *
	 * @return array<DTO\Quiz> List of quiz DTO objects.
	 */
	protected function get_quizzes( int $course_id, int $parent_id = 0 ): array {
		global $wpdb;

		$lp_quizzes = $wpdb->get_results(
			$wpdb->prepare(
				"SELECT si.item_id AS id
					FROM {$wpdb->base_prefix}learnpress_sections s
					INNER JOIN {$wpdb->base_prefix}learnpress_section_items si
						ON s.section_id = si.section_id
					WHERE s.section_course_id = %d
						AND si.section_id = %d
						AND si.item_type = 'lp_quiz'
					ORDER BY si.item_order ASC",
				$course_id,
				$parent_id
			)
		);

		$quizzes = array_map(
			function ( $lp_quiz ) use ( $course_id, $parent_id ) {
				return DTO\Quiz::create(
					[
						'post'      => get_post( $lp_quiz->id ),
						'meta'      => get_post_meta( $lp_quiz->id ),
						'course_id' => $course_id,
						'parent_id' => $parent_id,
						'questions' => $this->get_questions( $lp_quiz->id ),
					]
				);
			},
			$lp_quizzes
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

		$lp_questions = $wpdb->get_results(
			$wpdb->prepare(
				"SELECT qq.question_id AS id
					FROM {$wpdb->base_prefix}learnpress_quiz_questions qq
					WHERE qq.quiz_id = %d
					ORDER BY qq.question_order ASC",
				$quiz_id
			)
		);

		$questions = array_map(
			function ( $lp_question ) {
				$question_type = $this->get_learndash_question_type( $lp_question->id );

				return DTO\Question::create(
					[
						'type'    => $question_type,
						'post'    => get_post( $lp_question->id ),
						'meta'    => get_post_meta( $lp_question->id ),
						'answers' => $this->get_answers( $lp_question->id ),
					]
				);
			},
			$lp_questions
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

		$question_type = $this->get_learndash_question_type( $question_id );

		$lp_answers = $wpdb->get_results(
			$wpdb->prepare(
				"SELECT *
					FROM {$wpdb->base_prefix}learnpress_question_answers
					WHERE question_id = %d",
				$question_id
			)
		);

		$answers = array_map(
			function ( $lp_answer ) use ( $question_type ) {
				if ( $question_type === static::$question_type_key_fill_in_the_blank ) {
					$answer_title = $this->format_fill_in_the_blank_answer( $lp_answer->title );
				} else {
					$answer_title = $lp_answer->title;
				}

				$answer = [
					'title'      => $answer_title,
					'is_correct' => $lp_answer->is_true === 'yes',
				];

				unset( $lp_answer->title );
				unset( $lp_answer->is_true );

				$meta = [];
				foreach ( $lp_answer as $key => $value ) {
					$meta[ $key ] = $value;
				}

				$answer['meta'] = $meta;

				return DTO\Answer::create( $answer );
			},
			$lp_answers
		);

		return $answers;
	}

	/**
	 * Filter formatted settings.
	 *
	 * Handler for `learndash_migration_format_settings` filter hook.
	 *
	 * @since 1.0.0
	 *
	 * @param array<string, array<mixed>> $settings    Post formatted settings.
	 * @param object                      $dto_object  Post object.
	 * @param Base                        $integration Integration object.
	 *
	 * @return array<string, array<mixed>> Filtered formatted settings.
	 */
	public function filter_format_settings( $settings, object $dto_object, Base $integration ): array {
		if ( $integration->key !== $this->key ) {
			return $settings;
		}

		if ( $dto_object instanceof DTO\Course ) {
			if (
				! empty( $dto_object->meta['_lp_no_required_enroll'] )
				&& $dto_object->meta['_lp_no_required_enroll'][0] === 'yes'
			) {
				$settings['course_price_type'] = [ 'open' ];
			} else {
				$settings['course_price_type'] = [ 'paynow' ];
			}

			if ( is_array( $dto_object->meta['_lp_duration'] ) ) {
				foreach ( $dto_object->meta['_lp_duration'] as $duration ) {
					if ( empty( $duration ) ) {
						continue;
					}

					$duration = is_string( $duration ) ? $duration : '';
					$duration = $this->format_duration( $duration, 'day' );

					if ( $duration < 1 ) {
						continue;
					}

					$settings['expire_access']      = [ 'on' ];
					$settings['expire_access_days'] = [ $duration ];
					break;
				}
			}
		}

		if (
			(
				$dto_object instanceof DTO\Course
				|| $dto_object instanceof DTO\Topic
			)
			&& ! empty( $dto_object->post->ID )
		) {
			$materials = $this->get_learndash_materials( $dto_object->post->ID );

			if ( ! empty( $materials ) ) {
				$key_name_prefix = $dto_object instanceof DTO\Course ? 'course_' : 'topic_';

				$settings[ $key_name_prefix . 'materials_enabled' ] = [ 'on' ];
				$settings[ $key_name_prefix . 'materials' ]         = [ $materials ];
			}
		}

		if ( $dto_object instanceof DTO\Quiz ) {
			if ( ! empty( $dto_object->meta['_lp_retake_count'][0] ) ) {
				$settings['retry_restrictions'] = [ 'on' ];
			}

			if ( ! empty( $dto_object->meta['_lp_duration'] [0] ) ) {
				$settings['quiz_time_limit_enabled'] = [ 'on' ];

				/**
				 * Original quiz duration.
				 *
				 * @var string $orig_quiz_duration Original quiz duration.
				 */
				$orig_quiz_duration = $dto_object->meta['_lp_duration'][0];

				$quiz_duration = $this->format_duration( $orig_quiz_duration, 'second' );

				// Need two different key names for LearnDash quiz setting and LearnDash Migration setting key format.
				$settings['timeLimit']  = [ $quiz_duration ];
				$settings['time_limit'] = [ $quiz_duration ];
			}
		}

		return $settings;
	}

	/**
	 * Format LearnPress fill in the blank answer to LearnDash format.
	 *
	 * @since 1.0.0
	 *
	 * @param string $answer Original answer.
	 *
	 * @return string LearnDash formatted answer.
	 */
	private function format_fill_in_the_blank_answer( string $answer ): string {
		/**
		 * Match all answer pattern in LearnPress answer text.
		 *
		 * Regex description:
		 *
		 * '\[fib.*?' : match answer bracket started with [fib string and all characters until it matches 'fill' attributes
		 * 'fill="(.*?)"' : match fill attributes and capture its content
		 */
		preg_match_all( '/\[fib.*?fill="(.*?)"/', $answer, $matches );

		if ( ! isset( $matches[1] ) ) {
			return '';
		}

		$answer_replacements = [];

		foreach ( $matches[1] as $answer_group ) {
			$answer_temp = explode( ',', $answer_group );

			if ( count( $answer_temp ) > 1 ) {
				$answer_temp = array_map(
					function ( $answer_temp_string ) {
						return '[' . trim( $answer_temp_string ) . ']';
					},
					$answer_temp
				);

				$answer_replacements[] = '{' . implode( '', $answer_temp ) . '}';
			} elseif ( count( $answer_temp ) === 1 ) {
				$answer_replacements[] = '{' . trim( $answer_temp[0] ) . '}';
			}
		}

		// Create patterns according the sum of answer replacements.
		$patterns = array_fill( 0, count( $answer_replacements ), '/\[fib.*?\]/' );

		$formatted_answer = preg_replace( $patterns, $answer_replacements, $answer, 1 ) ?? '';

		return $formatted_answer;
	}

	/**
	 * Get LearnDash question type from question ID.
	 *
	 * @since 1.0.0
	 *
	 * @param int $question_id Source question ID.
	 *
	 * @return string LearnDash question type.
	 */
	private function get_learndash_question_type( int $question_id ): string {
		$question_type = get_post_meta( $question_id, '_lp_type', true );

		if ( ! is_string( $question_type ) ) {
			$question_type = '';
		}

		return $this->format_question_type( $question_type );
	}

	/**
	 * Format learnpress duration value to match with LearnDash value.
	 *
	 * @since 1.0.0
	 *
	 * @param string $duration Original duration string.
	 * @param string $output   Output duration in this unit.
	 *
	 * @return int Formatted duration numeric value.
	 */
	private function format_duration( string $duration, string $output = 'day' ): int {
		$duration_returned = 0;

		if ( $duration !== 0 ) {
			$duration_seconds = false;

			$duration        = explode( ' ', $duration );
			$duration_number = ! empty( $duration[0] ) ? intval( $duration[0] ) : 0;

			if ( $duration_number > 0 ) {
				switch ( $duration[1] ) {
					case 'minute':
						$duration_seconds = $duration_number * MINUTE_IN_SECONDS;
						break;

					case 'hour':
						$duration_seconds = $duration_number * HOUR_IN_SECONDS;
						break;

					case 'day':
						$duration_seconds = $duration_number * DAY_IN_SECONDS;
						break;

					case 'week':
						$duration_seconds = $duration_number * WEEK_IN_SECONDS;
						break;
				}
			}

			if ( ! empty( $duration_seconds ) ) {
				switch ( $output ) {
					case 'day':
						$duration_returned = (int) ceil( $duration_seconds / DAY_IN_SECONDS );
						break;

					case 'second':
						$duration_returned = $duration_seconds;
						break;
				}
			}
		}

		return $duration_returned;
	}

	/**
	 * Get object materials and convert them to LearnDash formatted materials input string.
	 *
	 * @since 1.0.0
	 *
	 * @param integer $object_id LearnPress post ID such as course, and topic.
	 *
	 * @return string LearnDash materials input string.
	 */
	private function get_learndash_materials( int $object_id ): string {
		global $wpdb;

		$query = $wpdb->prepare(
			"SELECT * FROM {$wpdb->base_prefix}learnpress_files WHERE item_id = %d ORDER BY orders ASC;",
			$object_id
		);

		// phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared -- The $query is already prepared.
		$files     = $wpdb->get_results( $query );
		$materials = '';

		foreach ( $files as $file ) {
			if ( $file->method === 'upload' ) {
				$wp_upload_dir = wp_upload_dir();

				$url = $wp_upload_dir['baseurl'] . $file->file_path;
			} else {
				$url = $file->file_path;
			}

			$materials .= '<p><a href="' . esc_url( $url ) . '">' . $file->file_name . '</a></p>';
		}

		return $materials;
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
				$type = static::$question_type_key_single_choice;
				break;

			case 'multi_choice':
				$type = static::$question_type_key_multiple_choice;
				break;

			case 'fill_in_blanks':
				$type = static::$question_type_key_fill_in_the_blank;
				break;

			default:
				$type = static::$question_type_key_single_choice;
				break;
		}

		return $type;
	}
}
