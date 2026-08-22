<?php
defined( 'ABSPATH' ) || exit;

if ( ! class_exists( 'Edumall_Tutor_Prerequisites' ) ) {
	class Edumall_Tutor_Prerequisites {

		protected static $instance = null;

		public static function instance() {
			if ( null === self::$instance ) {
				self::$instance = new self();
			}

			return self::$instance;
		}

		public function initialize() {
			if ( ! $this->is_activate() ) {
				return;
			}

			/**
			 * Custom template for lessons page when course have prerequisites.
			 * Priority 100 to run after plugin's hook (99).
			 */
			add_filter( 'template_include', [ $this, 'template_required_lesson' ], 100 );
		}

		public function is_activate() {
			if ( ! function_exists( 'TUTOR_PREREQUISITES' ) ) {
				return false;
			}

			$addonConfig = tutor_utils()->get_addon_config( TUTOR_PREREQUISITES()->basename );
			$isEnable    = (bool) tutor_utils()->avalue_dot( 'is_enable', $addonConfig );

			if ( $isEnable ) {
				return true;
			}

			return false;
		}

		public function template_required_lesson( $template ) {
			global $wp_query;

			if ( $wp_query->is_single && ! empty( $wp_query->query_vars['post_type'] ) && in_array( $wp_query->query_vars['post_type'], Edumall_Tutor::instance()->get_course_lesson_types() ) ) {
				if ( is_user_logged_in() ) {
					$course_id = 0;
					$post_type = $wp_query->query_vars['post_type'];

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

					$requiredComplete      = false;
					$savedPrerequisitesIDS = maybe_unserialize( get_post_meta( $course_id, '_tutor_course_prerequisites_ids', true ) );

					if ( is_array( $savedPrerequisitesIDS ) && count( $savedPrerequisitesIDS ) ) {
						foreach ( $savedPrerequisitesIDS as $courseID ) {
							if ( ! tutor_utils()->is_completed_course( $courseID ) ) {
								$requiredComplete = true;
								break;
							}
						}
					}

					if ( $requiredComplete ) {
						$template = tutor_get_template( 'single-prerequisites-lesson' );
					}
				} else {
					$template = tutor_get_template('login');
				}
			}

			return $template;
		}
	}
}
