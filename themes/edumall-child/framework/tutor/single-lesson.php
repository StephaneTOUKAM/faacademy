<?php
defined( 'ABSPATH' ) || exit;

if ( ! class_exists( 'Edumall_Single_Lesson' ) ) {
	class Edumall_Single_Lesson {

		protected static $instance = null;

		public static function instance() {
			if ( null === self::$instance ) {
				self::$instance = new self();
			}

			return self::$instance;
		}

		public function initialize() {
			add_filter( 'body_class', [ $this, 'body_class' ] );

			add_filter( 'edumall_title_bar_type', [ $this, 'setup_title_bar' ] );

			add_filter( 'insight_core_breadcrumb_single_before', [
				$this,
				'add_breadcrumb_course_link_for_lessons',
			], 99, 3 );

			// Disable archive link for lessons.
			add_filter( 'register_post_type_args', [ $this, 'remove_archive_link' ], 11, 2 );
		}

		public function body_class( $classes ) {
			if ( Edumall_Tutor::instance()->is_single_lessons() ) {
				$enable_spotlight_mode = tutor_utils()->get_option( 'enable_spotlight_mode' );

				if ( '1' === $enable_spotlight_mode ) {
					$classes [] = 'lesson-spotlight-mode';
				}
			}

			return $classes;
		}

		public function setup_title_bar( $type ) {
			if ( Edumall_Tutor::instance()->is_single_lessons() ) {
				return '05';
			}

			return $type;
		}

		public function remove_archive_link( $args, $post_type ) {
			if ( Edumall_Tutor::instance()->is_course_lesson_type( $post_type ) ) {
				$args['has_archive'] = false;
			}

			return $args;
		}

		/**
		 * Improvement breadcrumb links.
		 *
		 * @param $breadcrumb_arr
		 * @param $post
		 * @param $args
		 *
		 * @return array
		 */
		public function add_breadcrumb_course_link_for_lessons( $breadcrumb_arr, $post, $args ) {
			$post_type = $post->post_type;

			$course_id = 0;

			switch ( $post_type ) {
				case Edumall_Tutor::instance()->get_assignment_type():
					$course_id = get_post_meta( get_the_ID(), '_tutor_course_id_for_assignments', true );
					break;
				case Edumall_Tutor::instance()->get_zoom_meeting_type():
					$course_id = get_post_meta( get_the_ID(), '_tutor_zm_for_course', true );
					break;
				case Edumall_Tutor::instance()->get_lesson_type():
					$course_id = get_post_meta( get_the_ID(), '_tutor_course_id_for_lesson', true );
					break;
				case Edumall_Tutor::instance()->get_quiz_type():
					$course    = tutor_utils()->get_course_by_quiz( get_the_ID() );
					$course_id = $course->ID;
					break;
			}

			if ( $course_id ) {
				$course_object = get_post_type_object( Edumall_Tutor::instance()->get_course_type() );

				if ( $course_object && $course_object->has_archive ) {
					$breadcrumb_arr[] = array(
						'title' => sprintf( $args['post_type_label'], $course_object->label ),
						'link'  => get_post_type_archive_link( $course_object->name ),
					);
				}

				$course = get_post( $course_id );
				if ( $course ) {
					$breadcrumb_arr [] = [
						'title' => sprintf( $args['attachment_label'], $course->post_title ),
						'link'  => get_permalink( $course->ID ),
					];
				}

				$lesson_object = get_post_type_object( $post_type );

				$breadcrumb_arr [] = [
					'title' => sprintf( $args['post_type_label'], $lesson_object->label ),
					'link'  => '',
				];
			}

			return $breadcrumb_arr;
		}
	}
}
