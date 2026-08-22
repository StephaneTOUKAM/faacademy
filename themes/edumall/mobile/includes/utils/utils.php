<?php

namespace edumallmobile\utils;
use edumallmobile\framework\Edumall_Tutor_Shortcode;


class Edumall_Mobile_Utils {


	public static function get_respone( $data, $status ) {
		$response = new \WP_REST_Response( $data );

		$response->set_status( $status );

		return $response;
	}

	public static function is_time_in_range( $date1 ) {
		$date1 = new \DateTime( $date1 );
		$unix1 = strtotime( $date1->format( 'Y-m-d H:i:s' ) );
		$date2 = new \DateTime( current_time( 'Y-m-d H:i:s' ) );
		$unix2 = strtotime( $date2->format( 'Y-m-d H:i:s' ) );
		$range = $unix1 - $unix2;

		if ( $range > 0 ) {
			return true;
		}

		return false;
	}

	public static function edumall_mobile_get_user() {
		$result = get_users( array(
			'meta_key'   => 'mobile_token',
			'meta_value' => substr( $_SERVER['HTTP_AUTHORIZATION'], 7 ),
		) );

		return $result[0];
	}

	public static function is_user_login() : bool {
		$result = get_users( array(
			'meta_key'   => 'mobile_token',
			'meta_value' => substr( $_SERVER['HTTP_AUTHORIZATION'], 7 ),
		) );

		if ( count( $result ) == 1 ) {
			return true;
		} else {
			return false;
		}
	}

	public static function role_user() : int {
		$result = get_users( array(
			'meta_key'   => 'mobile_token',
			'meta_value' => substr( $_SERVER['HTTP_AUTHORIZATION'], 7 ),
		) );

		if ( count( $result ) == 1 ) {
			$register_time = get_user_meta( $result[0]->ID, '_is_tutor_instructor', true );

			if ( empty( $register_time ) ) {
				return 1;
			}

			$instructor_status = get_user_meta( $result[0]->ID, '_tutor_instructor_status', true );

			if ( 'approved' !== $instructor_status ) {
				return 1;
			}

			return 2;
		} else {
			return 0;
		}
	}

	public static function get_level_label( $post_id ) {
		$level = Edumall_Mobile_Utils::get_level( $post_id );

		if ( $level ) {
			return Edumall_Mobile_Utils::course_levels( $level );
		}

		return 0;
	}

	public static function get_level( $post_id ) {
		return get_post_meta( $post_id, '_tutor_course_level', true );

	}


	public static function course_levels( $level = null ) {
		$levels = apply_filters( 'tutor_course_level', array(
			'all_levels'   => 3,
			'beginner'     => 0,
			'intermediate' => 1,
			'expert'       => 2,
		) );

		if ( $level ) {
			if ( isset( $levels[ $level ] ) ) {
				return $levels[ $level ];
			} else {
				return 0;
			}
		}

		return 0;
	}

	public static function is_course_on_sale( $course_id ) {
		if ( tutor_utils()->is_course_purchasable( $course_id ) ) {
			if ( tutor_utils()->has_wc() ) {
				$product_id = tutor_utils()->get_course_product_id( $course_id );
				$product    = wc_get_product( $product_id );
				if($product) {
					if ( $product->is_on_sale() ) {
						return true;
					}
				}
			}
		}

		return false;
	}

	public static function getPriceOfCourses( $course_id, $type = 0 ) {
		if ( tutor_utils()->is_course_purchasable( $course_id ) ) {
			if ( tutor_utils()->has_wc() ) {
				$product_id = tutor_utils()->get_course_product_id( $course_id );
				$product    = wc_get_product( $product_id );

				if ( $product ) {
					if ( $type == 0 ) {
						return $product->get_regular_price();
					} else {
						return $product->get_sale_price();
					}
				}
			}
		}

		return 0;
	}

	public static function get_course_categories() {
		$categories = get_terms( [
			'taxonomy'   => \Edumall_Tutor::instance()->get_tax_category(),
			'parent'     => 0,
			'hide_empty' => 0,
		] );

		$category_options = array();
		foreach ( $categories as $category ) {
			$object             = new \stdClass();
			$object->id         = $category->term_id;
			$object->name       = esc_html( $category->name );
			$category_options[] = $object;
		}


		return $category_options;
	}

	public static function get_video_source() {

		$video_info = tutor_utils()->get_video_info();
		switch ( $video_info->source ) {
			case 'youtube':
				$disable_default_player_youtube = tutor_utils()->get_option( 'disable_default_player_youtube' );
				$youtube_video_id               = tutor_utils()->get_youtube_video_id( tutor_utils()->avalue_dot( 'source_youtube', $video_info ) );
				if ( $disable_default_player_youtube ) {
					return 'https://www.youtube.com/embed/' . $youtube_video_id;
				}
				break;
			case 'embedded':
				return tutor_utils()->array_get( 'source_embedded', $video_info );
				break;
			case 'vimeo':
				$disable_default_player_vimeo = tutor_utils()->get_option( 'disable_default_player_vimeo' );
				$video_id                     = tutor_utils()->get_vimeo_video_id( tutor_utils()->avalue_dot( 'source_vimeo', $video_info ) );
				if ( $disable_default_player_vimeo ) {
					return 'https://player.vimeo.com/video/' . $video_id;
				}

				return 'https://player.vimeo.com/video/' . $video_id . '?loop=false&amp;byline=false&amp;portrait=false&amp;title=false&amp;speed=true&amp;transparent=0&amp;gesture=media';
				break;
			case 'html5':
				return tutor_utils()->get_video_stream_url();
				break;
			case 'external_url':
				return tutor_utils()->array_get( 'source_external_url', $video_info );
				break;

		}

		return '';
	}

	public static function get_avatar_mb( $user_id = null, $size = 'thumbnail' ) {
		if ( ! $user_id ) {
			return '';
		}

		$user = tutor_utils()->get_tutor_user( $user_id );
		if ( $user->tutor_profile_photo ) {
			return Edumall_Tutor_Shortcode::instance()->get_attachment_by_id_mb( [
				'id'        => $user->tutor_profile_photo,
				'size'      => $size,
				'img_attrs' => [
					'class' => 'tutor-image-avatar',
				],
			] );
		}

		$name = $user->display_name;
		$arr  = explode( ' ', trim( $name ) );

		if ( count( $arr ) > 1 ) {
			$first_char  = substr( $arr[0], 0, 1 );
			$second_char = substr( $arr[1], 0, 1 );
		} else {
			$first_char  = substr( $arr[0], 0, 1 );
			$second_char = substr( $arr[0], 1, 1 );
		}
		$initial_avatar = strtoupper( $first_char . $second_char );

		return $initial_avatar;
	}

	public static function mark_lesson_title_preview( $post_id ) {
		$is_preview = (bool) get_post_meta( $post_id, '_is_preview', true );
		if ( $is_preview ) {
			return esc_html__( 'Preview', 'edumall' );
		} else {
			return 'lock';
		}

		return $newTitle;
	}


	public static function get_course_completed_percent_mb( $course_id = 0, $user_id = 0 ) {
		$course_id 	      = tutils()->get_post_id($course_id);
		$user_id          = tutils()->get_user_id($user_id);
		$completed_lesson = tutils()->get_completed_lesson_count_by_course($course_id, $user_id);
		$course_contents  = tutils()->get_course_contents_by_id($course_id);


		$totalContents    = tutils()->count($course_contents);
		$totalContents    = $totalContents ? $totalContents : 0;
		$completedCount   = $completed_lesson;

		if ( tutils()->count( $course_contents ) ) {

			foreach ( $course_contents as $content ) {

				if ( $content->post_type === 'tutor_quiz' ) {

					$attempt = tutils()->get_quiz_attempt( $content->ID ,$user_id);

					if ( $attempt) {
						$completedCount++;
					}
				} elseif ( $content->post_type === 'tutor_assignments' ) {
					$isSubmitted = tutils()->is_assignment_submitted( $content->ID,$user_id );
					if ( $isSubmitted ) {

						$completedCount++;
					}
				}
			}

		}

		if ( $totalContents > 0 && $completedCount > 0 ) {
			return number_format( ( $completedCount * 100 ) / $totalContents );
		}

		return 0;
	}





}
