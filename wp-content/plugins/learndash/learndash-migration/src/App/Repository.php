<?php
/**
 * Migration repository.
 *
 * @since 1.0.0
 *
 * @package LearnDash\Migration
 */

namespace LearnDash\Migration;

use Exception;
use LDLMS_Post_Types;
use LearnDash\Migration\DTO;
use stdClass;

/**
 * Migration addon Repository class.
 *
 * @since 1.0.0
 */
class Repository {
	/**
	 * Create WP post from DTO object and custom arguments.
	 *
	 * @since 1.0.0
	 *
	 * @throws Exception Post can't be created.
	 *
	 * @param object               $object DTO object.
	 * @param array<string, mixed> $args Post arguments.
	 *
	 * @return int New post ID.
	 */
	private function create_post( $object, $args = [] ): int {
		$args = wp_parse_args(
			$args,
			[
				'post_title'   => $object->post->post_title ?? '',
				'post_content' => $object->post->post_content ?? '',
				'post_type'    => $object->post->post_type ?? '',
				'post_status'  => 'publish',
			]
		);

		if ( empty( $args['post_type'] ) ) {
			throw new Exception( 'Missing post type from post parameters.' );
		}

		$post_id = wp_insert_post( $args, true );

		if ( is_wp_error( $post_id ) ) {
			throw new Exception( esc_html( $post_id->get_error_message() ) );
		}

		if ( ! is_int( $post_id ) ) {
			throw new Exception( esc_html( 'Unknown error creating post: ' . $args['post_title'] ) );
		}

		if ( isset( $object->settings ) && is_array( $object->settings ) ) {
			foreach ( $object->settings as $key => $values ) {
				foreach ( $values as $value ) {
					learndash_update_setting( $post_id, $key, $value );
				}
			}
		}

		if ( isset( $object->meta ) && is_array( $object->meta ) ) {
			foreach ( $object->meta as $key => $values ) {
				foreach ( $values as $value ) {
					add_post_meta( $post_id, $key, $value );
				}
			}
		}

		return $post_id;
	}

	/**
	 * Create course post.
	 *
	 * @since 1.0.0
	 *
	 * @param DTO\Course $course Course DTO object.
	 *
	 * @return int New course ID.
	 */
	public function create_course( DTO\Course $course ): int {
		return $this->create_post( $course );
	}

	/**
	 * Create section.
	 *
	 * @since 1.0.0
	 *
	 * @param DTO\Section $section   Section DTO object.
	 * @param int         $course_id New course ID.
	 *
	 * @return int New section ID.
	 */
	public function create_section( DTO\Section $section, int $course_id ): int {
		$section_object = new stdClass();

		$section_object->order      = $section->order;
		$section_object->ID         = $section->id;
		$section_object->post_title = $section->title;
		$section_object->url        = '';
		$section_object->edit_link  = '';
		$section_object->tree       = [];
		$section_object->expanded   = false;
		$section_object->type       = $section->type;

		$course_sections = get_post_meta( $course_id, 'course_sections', true );
		$course_sections = is_string( $course_sections ) ? json_decode( $course_sections ) : [];
		$course_sections = is_array( $course_sections ) ? $course_sections : [];

		$course_sections[] = $section_object;
		$course_sections   = wp_json_encode( $course_sections );

		update_post_meta( $course_id, 'course_sections', $course_sections );

		return $section_object->ID;
	}

	/**
	 * Create lesson post and add it as a child of a parent course.
	 *
	 * @since 1.0.0
	 *
	 * @param DTO\Lesson $lesson Lesson DTO object.
	 * @param int        $course_id Parent course ID of the lesson.
	 *
	 * @return int New lesson ID.
	 */
	public function create_lesson( DTO\Lesson $lesson, int $course_id ): int {
		$lesson_id = $this->create_post( $lesson );

		learndash_course_add_child_to_parent( $course_id, $lesson_id, $course_id );

		return $lesson_id;
	}

	/**
	 * Create topic post and add it as a child of a parent lesson.
	 *
	 * @since 1.0.0
	 *
	 * @param DTO\Topic $topic Topic DTO object.
	 * @param int       $course_id Parent course ID of the topic.
	 * @param int       $lesson_id Parent lesson ID of the topic.
	 *
	 * @return int New topic ID.
	 */
	public function create_topic( DTO\Topic $topic, int $course_id, int $lesson_id ): int {
		$topic_id = $this->create_post( $topic );

		learndash_course_add_child_to_parent( $course_id, $topic_id, $lesson_id );

		return $topic_id;
	}

	/**
	 * Create a quiz.
	 *
	 * @since 1.0.0
	 *
	 * @param DTO\Quiz $quiz      Quiz arguments.
	 * @param int      $course_id Parent course ID.
	 * @param int      $parent_id Parent ID.
	 *
	 * @return int New Quiz ID.
	 */
	public function create_quiz( DTO\Quiz $quiz, int $course_id, int $parent_id ): int {
		$quiz_id = $this->create_post( $quiz );

		$pro_quiz = new \WpProQuiz_Controller_Quiz();
		$pro_quiz->route(
			[
				'action'  => 'addUpdateQuiz',
				'quizId'  => 0, // New pro quiz.
				'post_id' => $quiz_id,
			],
			[
				'form'      => [],
				'post_ID'   => $quiz_id,
				'timeLimit' => $quiz->settings['time_limit'][0] ?? 0,
			]
		);

		learndash_course_add_child_to_parent( $course_id, $quiz_id, $parent_id );

		return $quiz_id;
	}

	/**
	 * Create a question.
	 *
	 * TODO: refactor the method to smaller pieces.
	 *
	 * @since 1.0.0
	 *
	 * @throws Exception Throw exception if question can't be created.
	 *
	 * @param DTO\Question $question Question DTO object.
	 * @param int          $quiz_id  Parent quiz ID.
	 *
	 * @return int New question ID.
	 */
	public function create_question( DTO\Question $question, int $quiz_id ): int {
		// Create LD question WP_Post object.

		global $wpdb;

		$title = '';
		if ( ! empty( $question->post->post_title ) ) {
			$title = $question->post->post_title;
		} elseif ( ! empty( $question->title ) ) {
			$title = $question->title;
		}

		$content = '';
		if ( ! empty( $question->post->post_content ) ) {
			$content = $question->post->post_content;
		} elseif ( ! empty( $question->content ) ) {
			$content = $question->content;
		}

		$args = [
			'action'       => 'new_step',
			'post_type'    => learndash_get_post_type_slug( LDLMS_Post_Types::QUESTION ),
			'post_status'  => 'publish',
			'post_title'   => $title,
			'post_content' => $content,
		];

		$question_id = $this->create_post( $question, $args );

		// Update guid to follow LD format. Taken from Learndash_Admin_Metabox_Quiz_Builder::learndash_builder_selector_step_new().

		$wpdb->update( // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching
			$wpdb->posts,
			[
				'guid' => add_query_arg(
					[
						'post_type' => learndash_get_post_type_slug( LDLMS_Post_Types::QUESTION ),
						'p'         => $question_id,
					],
					home_url()
				),
			],
			[ 'ID' => $question_id ]
		);

		// Create WP Pro Quiz question object.

		$question_pro_id = learndash_update_pro_question( 0, $args );

		if ( empty( $question_pro_id ) ) {
			throw new Exception( esc_html( 'Failed to fetch question pro ID after creating question: ' . $question->title ) );
		}

		// Associate LD quiz object with WP Pro Quiz question object.

		$questions = get_post_meta( $quiz_id, 'ld_quiz_questions', true );
		$questions = is_array( $questions ) ? $questions : [];

		$questions[ $question_id ] = $question_pro_id;

		update_post_meta( $quiz_id, 'ld_quiz_questions', $questions );
		update_post_meta( $question_id, 'question_pro_id', absint( $question_pro_id ) );
		learndash_proquiz_sync_question_fields( $question_id, $question_pro_id );

		learndash_update_setting( $question_id, 'quiz', $quiz_id );
		update_post_meta( $question_id, 'quiz_id', $quiz_id );

		// Build question and its answers data.

		$question_mapper     = new \WpProQuiz_Model_QuestionMapper();
		$question_model      = $question_mapper->fetch( $question_pro_id );
		$question_pro_params = [
			'_answerData' => [],
			'_answerType' => $question->type,
			'_points'     => $question->settings['points'][0] ?? 1,
		];

		$answer_text         = '';
		$sort_string         = '';
		$grading_progression = 'not-graded-none';

		$default_answer_data = [
			'_answer'             => $answer_text,
			'_correct'            => false,
			'_graded'             => '1',
			'_gradedType'         => 'text',
			'_gradingProgression' => $grading_progression,
			'_html'               => false,
			'_points'             => 1,
			'_sortString'         => $sort_string,
			'_sortStringHtml'     => false,
			'_type'               => 'answer',
		];

		foreach ( $question->answers as $answer_key => $answer ) {
			if ( $question->type === 'matrix_sort_answer' ) {
				$answer_text = $answer->params['criterion'];
				$sort_string = $answer->params['criterion_value'];
			} elseif ( $question->type === 'essay' ) {
				$answer_text         = $answer->title;
				$grading_progression = '';
			} else {
				$answer_text = $answer->title;
			}

			if ( ! empty( $answer->params['graded_type'] ) ) {
				$graded_type = $answer->params['graded_type'];
			} else {
				$graded_type = 'text';
			}

			$answer_data = wp_parse_args(
				[
					'_answer'             => $answer_text,
					'_correct'            => $answer->is_correct,
					'_gradingProgression' => $grading_progression,
					'_sortString'         => $sort_string,
					'_gradedType'         => $graded_type,
					'_html'               => $answer->params['html'] ?? null,
				],
				$default_answer_data
			);

			$question_pro_params['_answerData'][] = $answer_data;
		}

		if ( empty( $question->title ) ) {
			if ( $question->type === 'cloze_answer' ) {
				$question->title = __( 'Fill in the blank the following statement', 'learndash-migration' );
			}
		}

		if ( ! empty( $question->title ) ) {
			$question_text = $question->title;

			$question_pro_params['_question'] = $question_text;

			// Update question content.

			wp_update_post(
				[
					'ID'           => $question_id,
					'post_content' => wp_slash( $question_pro_params['_question'] ),
				]
			);
		}

		$question_pro_params['_tipMsg']          = $question->settings['hint'][0] ?? '';
		$question_pro_params['_tipEnabled']      = ! empty( $question->settings['hint'][0] );
		$question_pro_params['_correctMsg']      = $question->settings['correct_message'][0] ?? '';
		$question_pro_params['_incorrectMsg']    = $question->settings['incorrect_message'][0] ?? '';
		$question_pro_params['_correctSameText'] =
			! empty( $question->settings['correct_message'][0] )
			&& ! empty( $question->settings['incorrect_message'][0] )
			&& $question->settings['correct_message'][0] === $question->settings['incorrect_message'][0];

		// Associate question data to question model and save it.

		$question_model->set_array_to_object( $question_pro_params );
		$question_mapper->save( $question_model );

		return $question_id;
	}
}
