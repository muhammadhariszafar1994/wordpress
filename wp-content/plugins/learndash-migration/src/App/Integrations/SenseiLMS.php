<?php
/**
 * Sensei LMS integration class file.
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
 * Sensei LMS integration class.
 *
 * @since 1.0.0
 */
class SenseiLMS extends Base {
	/**
	 * Get integration key.
	 *
	 * @since 1.0.0
	 *
	 * @return string
	 */
	protected function get_key(): string {
		return 'sensei-lms';
	}

	/**
	 * Get integration label.
	 *
	 * @since 1.0.0
	 *
	 * @return string
	 */
	protected function get_label(): string {
		return 'Sensei LMS';
	}

	/**
	 * Constructor.
	 *
	 * @since 1.0.0
	 */
	public function __construct() {
		$this->mapped_post_types = [
			'course'      => 'course',
			'lesson'      => 'lesson',
			'topic'       => null,
			'quiz'        => 'quiz',
			'question'    => 'question',
			'certificate' => null,
			'group'       => null,
			'assignment'  => null,
		];

		$this->mapped_setting_keys = [
			'course'   => [],
			'lesson'   => [],
			'topic'    => [],
			'quiz'     => [],
			'question' => [
				'_quiz_passmark'         => 'passingpercentage',
				'_random_question_order' => 'questionRandom',
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
	 * Format section data from source LMS to match with the LD data.
	 *
	 * @since 1.0.0
	 *
	 * @param DTO\Section $section Section DTO object.
	 *
	 * @return DTO\Section Formatted section DTO object.
	 */
	private function format_section( DTO\Section $section ): DTO\Section {
		$section_object             = new stdClass();
		$section_object->post_title = $section->title;

		foreach ( $section->lessons as $key => $lesson ) {
			$lesson = $this->format_lesson( $lesson );

			$section->lessons[ $key ] = $lesson;
		}

		return $section;
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
		$question_object               = new stdClass();
		$question_object->post_title   = $question->title;
		$question_object->post_content = $question->content;
		$question_object->post_type    = $this->ld_post_type_slugs[ LDLMS_Post_Types::QUESTION ];

		$question->post = $question_object;
		$question->meta = $this->format_meta( $question );

		return $question;
	}

	/**
	 * Sensei LMS - specific module type that groups lessons together.
	 *
	 * @since 1.0.0
	 *
	 * @param int $course_id Course ID.
	 *
	 * @return array<DTO\Section>
	 */
	private function get_sections( int $course_id ): array {
		global $wpdb;

		$modules = [];

		$sensei_modules_order = get_post_meta( $course_id, '_module_order', true );

		if (
			empty( $sensei_modules_order )
			|| ! is_array( $sensei_modules_order )
		) {
			return $modules;
		}

		$number_placeholders = str_repeat( ', %d', count( $sensei_modules_order ) );

		// phpcs:ignore WordPress.DB.PreparedSQLPlaceholders.ReplacementsWrongNumber, WordPress.DB.PreparedSQL.InterpolatedNotPrepared -- $number_placeholders is wpdb::prepare() placeholders for $sensei_modules_order.
		$query = $wpdb->prepare(
			"SELECT t.term_id, t.name, t.slug, tt.description
			FROM {$wpdb->base_prefix}terms t
				INNER JOIN {$wpdb->base_prefix}term_taxonomy tt
					ON t.term_id = tt.term_id
				INNER JOIN {$wpdb->base_prefix}term_relationships tr
					ON tr.term_taxonomy_id = tt.term_taxonomy_id
			WHERE tt.taxonomy = 'module'
				AND tr.object_id = %d
				ORDER BY FIELD( t.term_id{$number_placeholders} );", // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared -- The variable $number_placeholders is a placeholder for $sensei_modules_order.
			$course_id,
			...$sensei_modules_order
		);

		/**
		 * Modules data from database.
		 *
		 * @var array<object{
		 *      term_id: int,
		 *      name: string,
		 *      slug: string,
		 *      description: string
		 * }> $sensei_modules Module data.
		 */
		$sensei_modules = $wpdb->get_results( $query ); // phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared -- the query is already prepared.

		$order   = 0;
		$id_add  = 0;
		$modules = array_map(
			function ( $sensei_module ) use ( $course_id, &$order, &$id_add ) {
				$module_order   = $order;
				$current_id_add = $id_add;

				$lessons = $this->get_module_lessons(
					$course_id,
					$sensei_module->term_id
				);

				$add = ! empty( $lessons ) ? count( $lessons ) : 1;

				$order += $add;
				$id_add++;

				return DTO\Section::create(
					[
						// Add increment to make the ID unique.
						'id'      => intval( ceil( microtime( true ) ) + $id_add ),
						'title'   => $sensei_module->name,
						// Include the amount of previous modules.
						'order'   => $module_order + $current_id_add,
						'lessons' => $lessons,
					]
				);
			},
			$sensei_modules
		);

		return $modules;
	}

	/**
	 * Get module lessons.
	 *
	 * @since 1.0.0
	 *
	 * @param int $course_id Sensei course ID.
	 * @param int $module_id Sensei module ID.
	 *
	 * @return DTO\Lesson[]
	 */
	private function get_module_lessons( int $course_id, int $module_id ): array {
		$args = [
			'post_type'      => $this->mapped_post_types['lesson'] ?? '',
			'posts_per_page' => -1,
			'post_status'    => 'publish',
			'meta_key'       => '_order_module_' . $module_id,
			'orderby'        => 'meta_value_num',
			'order'          => 'ASC',
		];

		$sensei_lessons = get_posts( $args );

		return $this->create_lessons_object( $sensei_lessons, $course_id );
	}

	/**
	 * Get non module lessons.
	 *
	 * @since 1.0.0
	 *
	 * @param int $course_id Sensei course ID.
	 *
	 * @return DTO\Lesson[]
	 */
	private function get_non_module_lessons( int $course_id ): array {
		$sensei_modules = get_post_meta( $course_id, '_module_order', true );
		$sensei_modules = is_array( $sensei_modules ) ? $sensei_modules : [];

		$args = [
			'post_type'      => $this->mapped_post_types['lesson'] ?? '',
			'posts_per_page' => -1,
			'post_status'    => 'publish',
			'meta_key'       => '_order_' . $course_id,
			'orderby'        => 'meta_value_num',
			'order'          => 'ASC',
		];

		$sensei_lessons = get_posts( $args );

		return $this->create_lessons_object( $sensei_lessons, $course_id );
	}

	/**
	 * Create lessons DTO object from existing list of lesson posts.
	 *
	 * @since 1.0.0
	 *
	 * @param WP_Post[] $sensei_lessons Sensei lesson posts.
	 * @param int       $course_id      Sensei course ID.
	 *
	 * @return DTO\Lesson[]
	 */
	private function create_lessons_object( array $sensei_lessons, int $course_id ): array {
		$lessons = array_map(
			function ( $sensei_lesson ) use ( $course_id ) {
				return DTO\Lesson::create(
					[
						'title'   => $sensei_lesson->post_title,
						'content' => $sensei_lesson->post_content,
						'post'    => $sensei_lesson,
						'meta'    => get_post_meta( $sensei_lesson->ID ),
						'quizzes' => $this->get_quizzes( $course_id, $sensei_lesson->ID ),
					]
				);
			},
			$sensei_lessons
		);

		return $lessons;
	}

	/**
	 * Get integration-specific non-module lessons.
	 *
	 * @since 1.0.0
	 *
	 * @param int $course_id Course ID.
	 *
	 * @return array<DTO\Lesson|DTO\Section> List of lesson or section DTO objects.
	 */
	protected function get_lessons( int $course_id ): array {
		$sections = $this->get_sections( $course_id );
		$lessons  = $this->get_non_module_lessons( $course_id );

		return array_merge( $sections, $lessons );
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
		return [];
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
		$sensei_quizzes = get_posts(
			[
				'post_type'      => $this->mapped_post_types['quiz'] ?? '',
				'post_parent'    => $parent_id,
				'posts_per_page' => -1,
				'post_status'    => 'publish',
			]
		);

		$quizzes = array_map(
			function ( $sensei_quiz ) use ( $course_id, $parent_id ) {
				return DTO\Quiz::create(
					[
						'title'     => $sensei_quiz->post_title,
						'content'   => $sensei_quiz->post_content,
						'post'      => get_post( $sensei_quiz->ID ),
						'meta'      => get_post_meta( $sensei_quiz->ID ),
						'course_id' => $course_id,
						'parent_id' => $parent_id,
						'questions' => $this->get_questions( $sensei_quiz->ID ),
					]
				);
			},
			$sensei_quizzes
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
		$questions = get_post_meta( $quiz_id, '_question_order', true );

		$sensei_questions = get_posts(
			[
				'post_type'      => $this->mapped_post_types['question'] ?? '',
				'post_status'    => 'publish',
				'posts_per_page' => -1,
				'orderby'        => 'post__in',
				'include'        => is_array( $questions ) ? $questions : [],
			]
		);

		$questions = array_map(
			function ( $sensei_question ) {
				$question_type = $this->get_question_type( $sensei_question->ID );
				$question_type = ! empty( $question_type )
					? $question_type
					: 'multiple-choice';

				$question_blocks = parse_blocks( $sensei_question->post_content );

				$settings = [];
				$content  = '';

				foreach ( $question_blocks as $key => $question_block ) {
					if ( $question_block['blockName'] === 'sensei-lms/question-description' ) {
						$content = trim( render_block( $question_block ) );
					} elseif ( $question_block['blockName'] === 'sensei-lms/quiz-question-feedback-correct' ) {
						$settings['correct_message'] = [ wp_strip_all_tags( trim( render_block( $question_block ) ) ) ];
					} elseif ( $question_block['blockName'] === 'sensei-lms/quiz-question-feedback-incorrect' ) {
						$settings['incorrect_message'] = [ wp_strip_all_tags( trim( render_block( $question_block ) ) ) ];
					}
				}

				return DTO\Question::create(
					[
						'title'    => $sensei_question->post_title,
						'content'  => $content,
						'type'     => $this->format_question_type( $question_type ),
						'meta'     => get_post_meta( $sensei_question->ID ),
						'settings' => $settings,
						'answers'  => $this->get_answers( $sensei_question->ID, $question_type ),
					]
				);
			},
			$sensei_questions
		);

		return $questions;
	}

	/**
	 * Get question type.
	 *
	 * @since 1.0.0
	 *
	 * @param int $question_id Question ID.
	 *
	 * @return string Question type.
	 */
	private function get_question_type( int $question_id ): string {
		global $wpdb;

		$type = $wpdb->get_var(
			$wpdb->prepare(
				"SELECT t.slug
					FROM {$wpdb->base_prefix}terms t
						INNER JOIN {$wpdb->base_prefix}term_taxonomy tt
							ON t.term_id = tt.term_id
						INNER JOIN {$wpdb->base_prefix}term_relationships tr
							ON tt.term_taxonomy_id = tr.term_taxonomy_id
					WHERE tt.taxonomy = 'question-type'
					AND tr.object_id = %d;",
				$question_id
			)
		);

		return $type ?? '';
	}

	/**
	 * Get integration-specific question answers.
	 *
	 * @since 1.0.0
	 *
	 * @param int    $question_id   Question ID.
	 * @param string $question_type Sensei question type.
	 *
	 * @return array<DTO\Answer> List of answer DTO objects.
	 */
	protected function get_answers( int $question_id, string $question_type = 'multiple-choice' ): array {
		$sensei_right_answers = get_post_meta( $question_id, '_question_right_answer', true );
		if ( is_string( $sensei_right_answers ) ) {
			$sensei_right_answers = maybe_unserialize( $sensei_right_answers );
		}

		$sensei_wrong_answers = get_post_meta( $question_id, '_question_wrong_answers', true );
		if ( is_string( $sensei_wrong_answers ) ) {
			$sensei_wrong_answers = maybe_unserialize( $sensei_wrong_answers );
		}

		$params = [];

		if ( $question_type === 'gap-fill' ) {
			/**
			 * Define type.
			 *
			 * @var string $sensei_right_answers_temp Gap fill right answer.
			 */
			$sensei_right_answers_temp = $sensei_right_answers;

			/**
			 * The gap-fill (fill in the blank) answer is stored in this format:
			 * "pre-answer||answer||post-answer". We change it to a LearnDash
			 * specific format: "pre-answer {answer} post-answer".
			 *
			 * Regex parts:
			 *
			 * 1. ^(.*?)\|\| : Find the beginning of a pre-answer until || characters.
			 *
			 * 2. \|\|(.*?)\|\| : Find the answer between the two || characters.
			 *
			 * 3. \|\|(.*?)$ : Find the post-answer until the end of text.
			 */
			$sensei_right_answers = preg_match( '/^(.*?)\|\|(.*?)\|\|(.*?)$/', $sensei_right_answers_temp, $matches );

			$matches[2] = explode( '|', $matches[2] );

			if ( count( $matches[2] ) > 1 ) {
				$matches[2] = array_map(
					function ( $answer_text ) {
						return '[' . $answer_text . ']';
					},
					$matches[2]
				);
			}

			$matches[2] = implode( '', $matches[2] );
			$matches[1] = is_string( $matches[1] ) ? $matches[1] : '';
			$matches[3] = is_string( $matches[3] ) ? $matches[3] : '';

			$sensei_right_answers = [ $matches[1] . ' {' . $matches[2] . '} ' . $matches[3] ];
		} elseif ( $question_type === 'boolean' ) {
			$sensei_wrong_answers = $sensei_right_answers === 'true'
				? 'wrong' : 'true';
			$sensei_right_answers = [ $sensei_right_answers ];
			$sensei_wrong_answers = [ $sensei_wrong_answers ];
		} elseif ( $question_type === 'file-upload' ) {
			$params['graded_type'] = 'upload';
		}

		$sensei_right_answers = is_array( $sensei_right_answers ) ? $sensei_right_answers : [];
		$sensei_wrong_answers = is_array( $sensei_wrong_answers ) ? $sensei_wrong_answers : [];
		$sensei_answers       = array_merge( $sensei_right_answers, $sensei_wrong_answers );
		$sensei_answers       = ! empty( $sensei_answers )
			? $sensei_answers
			: [ '' ];

		$answers = array_map(
			function ( $sensei_answer ) use ( $sensei_right_answers, $params ) {
				$is_correct = is_array( $sensei_right_answers )
					&& in_array( $sensei_answer, $sensei_right_answers, true )
					? true
					: null;

				$answer = [
					'title'      => $sensei_answer,
					'is_correct' => $is_correct,
					'params'     => $params,
				];

				return DTO\Answer::create( $answer );
			},
			$sensei_answers
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
			case 'multiple-choice':
				$type = static::$question_type_key_multiple_choice;
				break;

			case 'gap-fill':
				$type = static::$question_type_key_fill_in_the_blank;
				break;

			case 'single-line':
			case 'multi-line':
			case 'file-upload':
				$type = static::$question_type_key_essay;
				break;

			default:
				$type = static::$question_type_key_single_choice;
				break;
		}

		return $type;
	}
}
