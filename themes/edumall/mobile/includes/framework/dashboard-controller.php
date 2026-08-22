<?php

namespace edumallmobile\framework;

use edumallmobile\utils\Edumall_Mobile_Utils;

class Edumall_Tutor_DashBoard_Controller extends \Edumall_Tutor
{

    protected static $instance = null;

    public static function instance()
    {
        if (null === self::$instance) {
            self::$instance = new self();
        }

        return self::$instance;
    }

    public function enrolled_courses($user_id)
    {

        $data = array();
        $my_courses = tutor_utils()->get_enrolled_courses_by_user($user_id);

        global $post;
        if ($my_courses && $my_courses->have_posts()) {
            while ($my_courses->have_posts()) :
                $my_courses->the_post();
                $object = new \stdClass();
                $object->id = get_the_ID();
                $object->name = get_the_title();
                $object->rating = tutor_utils()->get_course_rating()->rating_avg;
                $object->totalLession = number_format_i18n(tutor_utils()->get_lesson_count_by_course());
                $object->completeLession = tutor_utils()->get_completed_lesson_count_by_course(get_the_ID(), $user_id);
                $object->totalTime = '';
                $disable_course_duration = get_tutor_option('disable_course_duration');
                $course_duration         = \Edumall_Tutor::instance()->get_course_duration_context();
                if (! empty($course_duration) && ! $disable_course_duration) {
                    $object->totalTime = $course_duration;
                }
                $object->urlThumnails =     \Edumall_Image::get_the_post_thumbnail_url([

                    'size' => '480x295',
                ]);

                $object->percentCompleted = Edumall_Mobile_Utils::get_course_completed_percent_mb(get_the_ID(), $user_id);
                $data[] = $object;
            endwhile;
        }
        wp_reset_postdata();

        return $data;
    }

    public function wishlist($user_id)
    {
        $data = array();
        global $post;
        $wishlists = tutor_utils()->get_wishlist($user_id);
        if (is_array($wishlists) && count($wishlists)) :
            global $edumall_course;
            $edumall_course_clone = $edumall_course;
            foreach ($wishlists as $post) :
                setup_postdata($post);
                $edumall_course = new \Edumall_Course();
                /**
                 * Setup course object.
                 */
                $object               = new \stdClass();
                $object->idCourse     = $post->ID;
                $object->permalink    = get_permalink($post->ID);
                $object->courseName   = get_the_title($post->ID);
                $category             = \Edumall_Tutor::instance()->get_the_category();
                $link                 = get_term_link($category);
                $object->idCategory   = $category->term_id;
                $object->categoryName = esc_html($category->name);
                $object->categoryLink = esc_url($link);
                $object->isBestseller = $edumall_course->is_featured();
                $object->isDiscount   = false;
                $object->discount     = '';
                if (! empty($edumall_course->on_sale_text())) {
                    $object->isDiscount = true;
                    $object->discount   = $edumall_course->on_sale_text();
                }
                $object->level      = Edumall_Mobile_Utils::get_level_label($post->ID);
                $object->authorName = '';
                $instructors        = $edumall_course->get_instructors();

                if (! empty($instructors)) {
                    $first_instructor   = $instructors[0];
                    $object->authorName = esc_html($first_instructor->display_name);
                }
                $object->fixedPrice = Edumall_Mobile_Utils::getPriceOfCourses($post->ID, 0);
                $object->isFree     = true;
                if ($object->fixedPrice > 0) {
                    $object->isFree = false;
                }
                $object->salePrice = 0;
                if (Edumall_Mobile_Utils::is_course_on_sale($post->ID)) {
                    $object->salePrice = Edumall_Mobile_Utils::getPriceOfCourses($post->ID, 1);
                }
                $object->urlThumnails = \Edumall_Image::get_the_post_thumbnail_url(array( 'size' => '226x150' ));
                $object->rating       = '0.00';
                $object->totalRating  = 0;
                $course_rating        = $edumall_course->get_rating();
                $rating_count         = intval($course_rating->rating_count);
                if ($rating_count > 0) {
                    $object->rating      = $course_rating->rating_avg;
                    $object->totalRating = intval($course_rating->rating_count);
                }
                $data[] = $object;
            endforeach;
            wp_reset_postdata();
            $edumall_course = $edumall_course_clone;
        endif;


        return $data;
    }

	/*
    * type: student,instructor
    * user_id: id_user
    */
    public function settings($user_id,$type)
    {
        $data = array();
	    $user = get_userdata($user_id);
        $data['profile'] = $this->get_profile_settings($user);


        if($type == 2)
        {

        }


        return $data;
    }

    public function get_profile_settings($user) {
		$object = new \stdClass();
		$object->firstname =  $user->first_name;
		$object->lastname =  $user->last_name;
		$object->jobtitle = strip_tags(get_user_meta( $user->ID, '_tutor_profile_job_title', true ));
		$object->phonenumber = strip_tags(get_user_meta( $user->ID, 'phone_number', true ));
		$object->bio = strip_tags( get_user_meta( $user->ID, '_tutor_profile_bio', true ) );

		$tutor_user_social_icons = tutor_utils()->tutor_user_social_icons();

		foreach ( $tutor_user_social_icons as $key => $social_icon ) {
			$object->$key = get_user_meta( $user->ID, $key, true );
		}

		$public_display                     = array();
		$public_display['display_nickname'] = $user->nickname;
		$public_display['display_username'] = $user->user_login;

		if ( ! empty( $user->first_name ) ) {
			$public_display['display_firstname'] = $user->first_name;
		}

		if ( ! empty( $user->last_name ) ) {
			$public_display['display_lastname'] = $user->last_name;
		}

		if ( ! empty( $user->first_name ) && ! empty( $user->last_name ) ) {
			$public_display['display_firstlast'] = $user->first_name . ' ' . $user->last_name;
			$public_display['display_lastfirst'] = $user->last_name . ' ' . $user->first_name;
		}

		if ( ! in_array( $user->display_name, $public_display ) ) { // Only add this if it isn't duplicated elsewhere
			$public_display = array( 'display_displayname' => $user->display_name ) + $public_display;
		}

		$public_display = array_map( 'trim', $public_display );
		$public_display = array_unique( $public_display );
		$temp = array();
		foreach ($public_display as $key=>$value){
			$object1= new \stdClass();
			$object1->key = $key;
			$object1->value = $value;
			$temp [] =$object1;
		}
		$object->public_display_list = $temp;

		$profile_placeholder = \Edumall_Helper::placeholder_avatar_src();
		$profile_photo_src   = $profile_placeholder;
		$profile_photo_id    = get_user_meta( $user->ID, '_tutor_profile_photo', true );
		if ( $profile_photo_id ) {
			$url = wp_get_attachment_image_url( $profile_photo_id, 'full' );
			! empty( $url ) ? $profile_photo_src = $url : 0;
		}
		$object->avatar_url = $profile_photo_src;


		$cover_placeholder = tutor()->url . 'assets/images/cover-photo.jpg';
		$cover_photo_src   = $cover_placeholder;
		$cover_photo_id    = get_user_meta( $user->ID, '_tutor_cover_photo', true );
		if ( $cover_photo_id ) {
			$url = wp_get_attachment_image_url( $cover_photo_id, 'full' );
			!
			empty( $url ) ? $cover_photo_src = $url : 0;
		}
		$object->cover_photo_url = $cover_photo_src;

		return $object;
	}

	public function profile_instructors($user_id)
    {

    }

	public function profile($user_id)
	{
		$data = array();
		$data['quantity'] = Edumall_Woo_Controller::instance()->get_quantity_from_cart($user_id);
		$data['dashboard'] =$this->dashboard_profile($user_id);
		$data['myprofile'] =$this->dashboard_my_profile($user_id);
		$reviews_obj = $this->dashboard_reviews($user_id);

		if(!$reviews_obj) {
			$data['review_type'] = 0;
			$data['review_message'] = translate( 'You haven\'t given any reviews yet.', 'edumall' );
		}
		else
		{
			$data['review_type'] = 1;
			$data['reviews'] = $reviews_obj;
		}


		return $data;
	}

    public function dashboard_profile($user_id)
    {

        $enrolled_course   = tutor_utils()->get_enrolled_courses_by_user($user_id);
        $completed_courses = tutor_utils()->get_completed_courses_ids_by_user($user_id);

        $enrolled_course_count  = $enrolled_course ? $enrolled_course->post_count : 0;
        $completed_course_count = count($completed_courses);
        $active_course_count    = $enrolled_course_count - $completed_course_count;
        $active_course_count    = $active_course_count > 0 ? $active_course_count : 0;
        $object = new \stdClass();
        $object->enrolled_courses= number_format_i18n($enrolled_course_count);
        $object->active_courses= number_format_i18n($active_course_count);
        $object->completed_courses= number_format_i18n($completed_course_count);

        return $object;
    }

    public function dashboard_my_profile($user_id)
    {

        $user = get_userdata($user_id);
        $rdate                 = wp_date('D d M Y, h:i:s a', strtotime($user->user_registered));
        $fname                 = $user->first_name;
        $lname                 = $user->last_name;
        $uname                 = $user->user_login;
        $email                 = $user->user_email;
        $phone                 = get_user_meta($user_id, 'phone_number', true);
        $bio                   = nl2br(strip_tags(get_user_meta($user_id, '_tutor_profile_bio', true)));
        $job_title             = strip_tags(get_user_meta($user_id, '_tutor_profile_job_title', true));
        $avatar                = Edumall_Mobile_Utils::get_avatar_mb( $user_id, '32x32' );

        $object = new \stdClass();
        $object->rdate     = $rdate    ;
        $object->fname     = $fname    ;
        $object->lname     = $lname    ;
        $object->uname     = $uname    ;
        $object->email     = $email    ;
        $object->phone     = $phone    ;
        $object->bio       = $bio      ;
        $object->job_title = $job_title;
	    $object->avatar = $avatar;
        return $object;
    }

    public function dashboard_reviews($user_id)
    {

        $reviews = tutor_utils()->get_reviews_by_user($user_id);

        if (empty($reviews)) {
            return false;
        }
        $data = [];

        foreach ($reviews as $review) {
            $name = get_the_title($review->comment_post_ID);
            $starview = $review->rating;
            $time = sprintf(esc_html__('%s ago', 'edumall'), human_time_diff(strtotime($review->comment_date)));
            $id = $review->comment_post_ID;
            $comment = wpautop(stripslashes($review->comment_content));
            $url_thumnail = Edumall_Tutor_Shortcode::instance()->get_the_post_thumbnail_mb([
            	'post_id' => $review->comment_post_ID,
            'size'    => '150x92',
            ]);
            $object = new \stdClass();
	        $object->name = $name;
	        $object->starview = $starview;
	        $object->time = $time;
	        $object->id = $id;
	        $object->comment = strip_tags($comment);
	        $object->url_thumnail = $url_thumnail;
	        $data[] = $object;
        }

        return $data;
    }
}
