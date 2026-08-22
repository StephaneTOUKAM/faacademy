<?php

namespace edumallmobile\framework;

use edumallmobile\utils\Edumall_Mobile_Utils;

class Edumall_Tutor_Detail_Controller extends \Edumall_Tutor {

	protected static $instance = null;

	public static function instance() {
		if ( null === self::$instance ) {
			self::$instance = new self();
		}

		return self::$instance;
	}

	public function detail( $post_id ) {
		$data = array();
		global $post;
		global $edumall_course;
		$edumall_course_clone = $edumall_course;

		$post = get_post( $post_id );

		if ( $post ) {
			setup_postdata( $post );
			$edumall_course = new \Edumall_Course();

			$price_badge          = \Edumall_Tutor::instance()->get_course_price_badge_text( $post_id );
			$object               = new \stdClass();
			$object->idCourse     = $post_id;
			$object->courseName   = get_the_title();
			$category             = \Edumall_Tutor::instance()->get_the_category();
			$link                 = get_term_link( $category );
			$object->idCategory   = $category->term_id;
			$object->categoryName = esc_html( $category->name );
			$object->categoryLink = esc_url( $link );
			$object->permalink    = get_permalink( $post->ID );

			$object->isBestseller = $edumall_course->is_featured();
			$object->isDiscount   = false;
			$object->discount     = '';
			if ( ! empty( $edumall_course->on_sale_text() ) ) {
				$object->isDiscount = true;
				$object->discount   = $price_badge;
			}
			$object->level      = Edumall_Mobile_Utils::get_level_label( $post->ID );
			$object->authorName = '';
			$instructors        = $edumall_course->get_instructors();
			$first_instructor   = -1;
			if ( ! empty( $instructors ) ) {
				$first_instructor   = $instructors[0];
				$object->authorName = esc_html( $first_instructor->display_name );

			}
			$object->avartarAuthor = Edumall_Mobile_Utils::get_avatar_mb( $first_instructor->ID, '32x32' );
			$object->fixedPrice    = Edumall_Mobile_Utils::getPriceOfCourses( $post->ID, 0 );
			$object->isFree        = true;
			if ( $object->fixedPrice > 0 ) {
				$object->isFree = false;
			}
			$object->salePrice = 0;
			if ( Edumall_Mobile_Utils::is_course_on_sale( $post->ID ) ) {
				$object->salePrice = Edumall_Mobile_Utils::getPriceOfCourses( $post->ID, 1 );
			}
			$object->urlThumnails = \Edumall_Image::get_the_post_thumbnail_url( array( 'size' => '226x150' ) );
			$object->rating       = '0.00';
			$object->totalRating  = 0;
			$course_rating        = $edumall_course->get_rating();
			$rating_count         = intval( $course_rating->rating_count );
			if ( ! get_tutor_option( 'disable_course_review' ) ) {
				if ( $rating_count > 0 ) {
					$object->rating      = $course_rating->rating_avg;
					$object->totalRating = intval( $course_rating->rating_count );
				}
			}
			if ( ! get_tutor_option( 'disable_course_total_enrolled' ) ) {
				$object->enrolledPeople = $edumall_course->get_enrolled_users_count();
			}
			$disable_update_date = get_tutor_option( 'disable_course_update_date' );
			if ( ! $disable_update_date ) {
				$object->updateDate = esc_html__( 'Last Update', 'edumall' ) . ' ' . get_the_modified_date();
			}

			$object->urlVideo = '';
			$object->srcVideo = '';
			$object->hasVideo = false;
			if ( $edumall_course->has_video() ) {
				$object->urlVideo = Edumall_Mobile_Utils::get_video_source();
				$object->srcVideo = tutor_utils()->get_video_info()->source;
				$object->hasVideo = true;
			}
			$object->dayLeft         = '';
			$object->duration        = '';
			$disable_course_duration = get_tutor_option( 'disable_course_duration' );
			$course_duration         = \Edumall_Tutor::instance()->get_course_duration_context();
			if ( ! empty( $course_duration ) && ! $disable_course_duration ) {
				$object->duration = $course_duration;
			}
			$object->subject    = esc_html( $category->name );
			$object->lectures   = '';
			$tutor_lesson_count = $edumall_course->get_lesson_count();
			if ( $tutor_lesson_count ) {
				$object->lectures = esc_html( sprintf( _n( '%s lecture', '%s lectures', $tutor_lesson_count, 'edumall' ), $tutor_lesson_count ) );
			}

			$object->language = '';
			$disabled         = get_tutor_option( 'disable_course_language' );
			if ( '1' !== $disabled ) {
				$terms = $this->get_course_language();

				if ( empty( $terms ) || is_wp_error( $terms ) ) {

				}
				$temp = '';
				foreach ( $terms as $term ):
					$temp .= esc_html( $term->name );
				endforeach;
				$object->language = $temp;

			}
			$object->starts     = '';
			$object->about      = wp_strip_all_tags( get_the_content() );
			$object->isEnrolled = $edumall_course->is_enrolled();

			$total_enrolled    = $edumall_course->get_enrolled_users_count();
			$maximum_students  = (int) tutor_utils()->get_course_settings( null, 'maximum_students' );
			$object->addtocart = 'addtocart';

			if ( $maximum_students && $maximum_students <= $total_enrolled ) {
				$object->addtocart = 'book';
			} else {
				$object->addtocart = 'addtocart';
			}
			$object->benefits = array();
			$benefits         = $edumall_course->get_benefits();
			if ( ! empty( $benefits ) ) {
				$object->benefits = $benefits;
			}

			$object->requirements = array();
			$course_requirements  = tutor_course_requirements();

			if ( ! empty( $course_requirements ) ) {
				if ( is_array( $course_requirements ) && count( $course_requirements ) ) {
					$object->requirements = $course_requirements;
				}
			}

			$object->targetAudience = array();
			$target_audience        = tutor_course_target_audience();
			if ( ! empty( $target_audience ) ) {
				if ( is_array( $target_audience ) && count( $target_audience ) ) {
					$object->targetAudience = $target_audience;
				}
			}

			$topics = $edumall_course->get_topics();

			if ( $topics->have_posts() ) {

				$tutor_lesson_count         = $edumall_course->get_lesson_count();
				$curriculums                = new \stdClass();
				$curriculums->totalLectures = 0;

				if ( $tutor_lesson_count ) {
					$curriculums->totalLectures = $tutor_lesson_count;
				}


				$curriculums->totalHours = 0;
				$tutor_course_duration   = get_tutor_course_duration_context( $post_id );
				if ( $tutor_course_duration ) {
					$curriculums->totalHours = $tutor_course_duration;

				}
				$index      = 0;
				$topics_arr = array();
				while ( $topics->have_posts() ) :

					$topics->the_post();
					$topic_item              = new \stdClass();
					$topic_item->ID          = get_the_ID();
					$topic_item->nameLecture = get_the_title();
					$lessons                 = tutor_utils()->get_course_contents_by_topic( get_the_ID(), -1 );
					$lesson_arr              = array();
					if ( $lessons->have_posts() ) :
						while ( $lessons->have_posts() ) :

							$lessons->the_post();
							global $post;
							$lession_item     = new \stdClass();
							$lession_item->id = get_the_ID();

							$video                  = tutor_utils()->get_video_info();
							$lession_item->duration = '';

							if ( $video ) {
								$play_time              = $video->playtime;
								$lession_item->duration = $play_time;
							}
							$lession_item->type           = 0;
							$lession_item->totalQuestions = 0;
							$lession_item->resourceName   = '';
							$lession_item->preview        = 'lock';
							if ( $post->post_type === 'tutor_quiz' ) {
								$lession_item->type = 2;
								$questions          = tutor_utils()->get_questions_by_quiz( get_the_ID() );
								if ( $questions ) {
									$lession_item->totalQuestions = count( $questions );
								}
							} else if ( $post->post_type === 'tutor_assignments' ) {
								$lession_item->type = 1;
								$attachments        = tutor_utils()->get_attachments( get_the_ID() );

								if ( is_array( $attachments ) && count( $attachments ) ) {
									$lession_item->resourceName = printf( esc_html__( 'Article Resource: (%1$s)', 'edumall' ), number_format_i18n( count( $attachments ) ) );
								}

							} else {
								$lession_item->preview = Edumall_Mobile_Utils::mark_lesson_title_preview( get_the_ID() );
							}

							$lession_item->thumbnailUrl = '';


							if ( has_post_thumbnail() ) {
								$lession_item->thumbnailUrl = get_the_post_thumbnail_url( get_the_ID(), 'thumbnail' );
							}
							$lession_item->name = get_the_title();
							$lession_item->link = '';
							if ( $edumall_course->is_enrolled() || ( get_post_meta( $post_id, '_tutor_is_public_course', true ) === 'yes' && ! tutor_utils()->is_course_purchasable( $post_id ) ) ) {
								$lession_item->link = get_the_permalink();
							}
							$lesson_arr[] = $lession_item;

						endwhile;
						$topic_item->items = $lesson_arr;
						$lessons->reset_postdata();
					endif;


					$topics_arr[] = $topic_item;
					$index++;
				endwhile;
				$topics->reset_postdata();
				$curriculums->lectures = $topics_arr;

			}

			$object->curriculum    = $curriculums;
			$object->resources     = array();
			$object->qas           = array();
			$object->announcements = array();
			if ( $edumall_course->is_enrolled() ) {
				//attachments
				$attachments = tutor_utils()->get_attachments( $post_id );

				if ( ! empty( $attachments ) ) {
					$temp_resource = array();
					foreach ( $attachments as $attachment ) {
						$attachments_o          = new \stdClass();
						$attachments_o->urlLink = esc_url( $attachment->url );
						$attachments_o->name    = \Edumall_Helper::e( $attachment->name );
						$attachments_o->mgbyte  = \Edumall_Helper::e( $attachment->size );
						$temp_resource[]        = $attachments_o;

					}
					$object->resources = $temp_resource;
				}

				//qa

				$enable_q_and_a_on_course = tutor_utils()->get_option( 'enable_q_and_a_on_course' );
				if ( $enable_q_and_a_on_course ) {
					$questions = tutor_utils()->get_top_question( $post_id, 0, 0, 8, true );

					if ( is_array( $questions ) && count( $questions ) ) {
						$temp_questions = array();

						foreach ( $questions as $question ) {

							$questions_o = new \stdClass();

							$questions_o->user_id         = $question->user_id;
							$questions_o->avartarURl      = \Edumall_Tutor::instance()->get_avatar( $question->user_id, '52x52' );
							$questions_o->authorName      = $question->display_name;
							$questions_o->time            = esc_html( sprintf( __( '%s ago', 'edumall' ), human_time_diff( strtotime( $question->comment_date ) ) ) );
							$questions_o->header_Question = esc_html( $question->question_title );
							$questions_o->message         = wpautop( stripslashes( $question->comment_content ) );
							$questions_o->id              = $question->comment_ID;
							$questions_o->answers         = array();
							$answers                      = tutor_utils()->get_qa_answer_by_question( $question->comment_ID );
							$temp_answers                 = array();
							if ( is_array( $answers ) && count( $answers ) ) {
								foreach ( $answers as $answer ) {

									$answr_o             = new \stdClass();
									$answr_o->avartarURl = \Edumall_Tutor::instance()->get_avatar( $answer->user_id, '52x52' );;
									$answr_o->authorName = $answer->display_name;
									$answr_o->time       = esc_html( sprintf( __( '%s ago', 'edumall' ), human_time_diff( strtotime( $answer->comment_date ) ) ) );
									$answr_o->message    = wpautop( stripslashes( $answer->comment_content ) );
									$answr_o->user_id    = $answer->user_id;
									$answr_o->id         = $answer->comment_ID;

									$temp_answers[] = $answr_o;

								}
							}
							$questions_o->answers = $temp_answers;
							$temp_questions[]     = $questions_o;
						}
						$object->qas = $temp_questions;
					}

				}

				//annoucemnet
				$announcements = tutor_utils()->get_announcements( get_the_ID() );
				if ( is_array( $announcements ) && count( $announcements ) ) {
					$temp_announcements = array();
					foreach ( $announcements as $announcement ) :
						$announcement_o            = new \stdClass();
						$announcement_o->title     = esc_html( $announcement->post_title );
						$announcement_o->posted_by = sprintf( esc_html__( 'Posted by %s', 'edumall' ), 'admin' );
						$announcement_o->time      = sprintf( esc_html__( '%s ago', 'edumall' ), human_time_diff( strtotime( $announcement->post_date ) ) );
						$announcement_o->message   = tutor_utils()->announcement_content( wpautop( stripslashes( $announcement->post_content ) ) );
						$temp_announcements        = $announcement_o;
					endforeach;
					$object->announcements = $temp_announcements;
				}

			}

			//instructors
			$object->instructor         = array();
			$display_course_instructors = tutor_utils()->get_option( 'display_course_instructors' );
			if ( $display_course_instructors ) {
				$instructors = tutor_utils()->get_instructors_by_course( $post_id );

				$temp_arr_instructors = array();
				if ( $instructors ) {
					foreach ( $instructors as $instructor ) {

						$instructor_o                = new \stdClass();
						$instructor_o->id            = $instructor->ID;
						$instructor_o->avatar        = Edumall_Mobile_Utils::get_avatar_mb( $instructor->ID, '200x236' );
						$instructor_o->name          = esc_html( $instructor->display_name );
						$instructor_rating           = tutor_utils()->get_instructor_ratings( $instructor->ID );
						$instructor_o->rating        = \Edumall_Helper::number_format_nice_float( $instructor_rating->rating_avg );
						$total_courses               = tutor_utils()->get_course_count_by_instructor( $instructor->ID );
						$instructor_o->totalCourse   = $total_courses;
						$instructor_o->totalComments = $instructor_rating->rating_count;
						$total_students              = tutor_utils()->get_total_students_by_instructor( $instructor->ID );
						$instructor_o->totalStudents = $total_students;
						$temp_arr_instructors[]      = $instructor_o;

					}
					$object->instructor = $temp_arr_instructors;

				}
			}

			//reviews

			$object->review         = array();
			$object->studenFeedBack = array();
			if ( ! get_tutor_option( 'disable_course_review' ) ) {
				$reviews         = tutor_utils()->get_course_reviews( $post_id );
				$temp_arr_review = array();
				if ( is_array( $reviews ) && count( $reviews ) ) {
					foreach ( $reviews as $review ) {
						$reivew_o             = new \stdClass();
						$reivew_o->avartarURl = Edumall_Mobile_Utils::get_avatar_mb( $first_instructor->ID, '32x32' );
						$reivew_o->authorName = esc_html( $review->display_name );
						$reivew_o->time       = sprintf( esc_html__( '%s ago', 'edumall' ), human_time_diff( strtotime( $review->comment_date ) ) );
						$reivew_o->message    = wp_strip_all_tags( wpautop( stripslashes( $review->comment_content ) ) );
						$reivew_o->rating     = $review->rating;
						$reivew_o->id         = $review->comment_ID;
						$temp_arr_review[]    = $reivew_o;
					}
				}
				$object->review = $temp_arr_review;

				//student feedback
				$rating                    = tutor_utils()->get_course_rating( $post_id );
				$temp_arr_student_feedback = array();

				if ( is_array( $reviews ) && count( $reviews ) ) {
					foreach ( $rating->count_by_value as $rating_point => $rating_numbers ) {
						$rating_o                    = new \stdClass();
						$rating_o->type              = $rating_point;
						$rating_o->rating            = \Edumall_Helper::calculate_percentage( $rating_numbers, $rating->rating_count );
						$temp_arr_student_feedback[] = $rating_o;
					}
				}
				$object->studenFeedBack = $temp_arr_student_feedback;
			}
			$object->featured = Edumall_Tutor_Shortcode::instance()->get_courses( 'popular' );
			wp_reset_postdata();
			$edumall_course = $edumall_course_clone;
			$data[]         = $object;
		}

		return $data;

	}
}
