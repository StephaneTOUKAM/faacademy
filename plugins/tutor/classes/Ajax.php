<?php
namespace TUTOR;

if ( ! defined( 'ABSPATH' ) )
	exit;

if (class_exists('WC_Product_Download')) {
	error_log('⚠️ CHECK no1: Classe WC_Product_Download déjà déclarée par ' . debug_backtrace()[0]['file']);
	return; // Arrêter le chargement si la classe existe déjà
}


class Ajax{
	public function __construct() {

		add_action('wp_ajax_sync_video_playback', array($this, 'sync_video_playback'));
		add_action('wp_ajax_nopriv_sync_video_playback', array($this, 'sync_video_playback_noprev'));
		add_action('wp_ajax_tutor_place_rating', array($this, 'tutor_place_rating'));

		add_action('wp_ajax_tutor_ask_question', array($this, 'tutor_ask_question'));
		add_action('wp_ajax_tutor_add_answer', array($this, 'tutor_add_answer'));

		add_action('wp_ajax_tutor_course_add_to_wishlist', array($this, 'tutor_course_add_to_wishlist'));
		add_action('wp_ajax_nopriv_tutor_course_add_to_wishlist', array($this, 'tutor_course_add_to_wishlist'));

		/**
		 * Addon Enable Disable Control
		 */
		add_action('wp_ajax_addon_enable_disable', array($this, 'addon_enable_disable'));

		/**
		 * Update Rating/review
		 * @since  v.1.4.0
		 */
		add_action('wp_ajax_tutor_load_edit_review_modal', array($this, 'tutor_load_edit_review_modal'));
		add_action('wp_ajax_tutor_update_review_modal', array($this, 'tutor_update_review_modal'));

		/**
		 * Ajax login
		 * @since  v.1.6.3
		 */
		add_action('wp_ajax_nopriv_tutor_user_login', array($this, 'process_ajax_login'));

		/**
		 * Announcement
		 * @since  v.1.7.9
		 */
		add_action("wp_ajax_tutor_announcement_create", array($this,'create_or_update_annoucement'));
		add_action("wp_ajax_tutor_rehearsal_create", array($this,'create_or_update_rehearsal'));
        add_action("wp_ajax_tutor_announcement_delete", array($this,'delete_annoucement'));
        add_action("wp_ajax_tutor_rehearsal_delete", array($this,'delete_rehearsal'));
		
		add_action("wp_ajax_tutor_delete_account_user", array($this,'delete_account_user'));
	}



	/**
	 * Update video information and data when necessary
	 *
	 * @since v.1.0.0
	 */
	public function sync_video_playback(){
		tutor_utils()->checking_nonce();

		$user_id = get_current_user_id();
		$post_id = isset($_POST['post_id']) ? sanitize_text_field($_POST['post_id']) : 0;
		$duration = sanitize_text_field($_POST['duration']);
		$currentTime = sanitize_text_field($_POST['currentTime']);

		if(!tutils()->has_enrolled_content_access('lesson', $post_id)) {
			wp_send_json_error(array('message'=>__('Access Denied', 'tutor')));
			exit;
		}

		/**
		 * Update posts attached video
		 */
		$video = tutor_utils()->get_video($post_id);

		if ($duration) {
			$video['duration_sec'] = $duration; //secs
			$video['playtime']     = tutor_utils()->playtime_string( $duration );
			$video['runtime']      = tutor_utils()->playtime_array( $duration );
		}
		tutor_utils()->update_video($post_id, $video);

		/**
		 * Sync Lesson Reading Info by Users
		 */

		$best_watch_time = tutor_utils()->get_lesson_reading_info($post_id, $user_id, 'video_best_watched_time');
		if ($best_watch_time < $currentTime){
			tutor_utils()->update_lesson_reading_info($post_id, $user_id, 'video_best_watched_time', $currentTime);
		}

		if (tutor_utils()->avalue_dot('is_ended', $_POST)){
			tutor_utils()->mark_lesson_complete($post_id);
		}
		exit();
	}

	public function sync_video_playback_noprev(){

	}


	public function tutor_place_rating(){
		global $wpdb;

		tutils()->checking_nonce();

		$rating = sanitize_text_field(tutor_utils()->avalue_dot('rating', $_POST));
		$course_id = sanitize_text_field(tutor_utils()->avalue_dot('course_id', $_POST));
		$review = wp_kses_post(tutor_utils()->avalue_dot('review', $_POST));

		!$rating ? $rating = 0 : 0;
		$rating>5 ? $rating = 5 : 0;

		$user_id = get_current_user_id();
		$user = get_userdata($user_id);
		$date = date("Y-m-d H:i:s", tutor_time());

		if(!tutils()->has_enrolled_content_access('course', $course_id)) {
			wp_send_json_error(array('message'=>__('Access Denied', 'tutor')));
			exit;
		}

		do_action('tutor_before_rating_placed');

		$previous_rating_id = $wpdb->get_var($wpdb->prepare("select comment_ID from {$wpdb->comments} WHERE comment_post_ID = %d AND user_id = %d AND comment_type = 'tutor_course_rating' LIMIT 1;", $course_id, $user_id));

		$review_ID = $previous_rating_id;
		if ( $previous_rating_id){
			$wpdb->update( $wpdb->comments, array('comment_content' => esc_sql( $review ) ),
				array('comment_ID' => $previous_rating_id)
			);

			$rating_info = $wpdb->get_row($wpdb->prepare("SELECT * FROM {$wpdb->commentmeta} WHERE comment_id = %d AND meta_key = 'tutor_rating'; ", $previous_rating_id));
			if ($rating_info){
				$wpdb->update( $wpdb->commentmeta, array('meta_value' => $rating), array('comment_id' => $previous_rating_id, 'meta_key' => 'tutor_rating') );
			}else{
				$wpdb->insert( $wpdb->commentmeta, array('comment_id' => $previous_rating_id, 'meta_key' => 'tutor_rating', 'meta_value' => $rating) );
			}
		}else{
			$data = array(
				'comment_post_ID'   => esc_sql( $course_id ) ,
				'comment_approved'  => 'approved',
				'comment_type'      => 'tutor_course_rating',
				'comment_date'      => $date,
				'comment_date_gmt'  => get_gmt_from_date($date),
				'user_id'           => $user_id,
				'comment_author'    => $user->user_login,
				'comment_agent'     => 'TutorLMSPlugin',
			);
			if ($review){
				$data['comment_content'] = $review;
			}

			$wpdb->insert($wpdb->comments, $data);
			$comment_id = (int) $wpdb->insert_id;
			$review_ID = $comment_id;

			if ($comment_id){
				$result = $wpdb->insert( $wpdb->commentmeta, array(
					'comment_id' => $comment_id,
					'meta_key' => 'tutor_rating',
					'meta_value' => $rating
				) );

				do_action('tutor_after_rating_placed', $comment_id);
			}
		}

		$data = array('msg' => __('Rating placed success', 'tutor'), 'review_id' => $review_ID, 'review' => $review);
		wp_send_json_success($data);
	}

	public function tutor_ask_question(){
		tutor_utils()->checking_nonce();

		global $wpdb;

		$course_id = (int) sanitize_text_field($_POST['tutor_course_id']);
		$question_title = sanitize_text_field($_POST['question_title']);
		$question = wp_kses_post($_POST['question']);

		if(!tutils()->has_enrolled_content_access('course', $course_id)) {
			wp_send_json_error(array('message'=>__('Access Denied', 'tutor')));
			exit;
		}

		if (empty($question) || empty($question_title)){
			wp_send_json_error(__('Empty question title or body', 'tutor'));
		}

		$user_id = get_current_user_id();
		$user = get_userdata($user_id);
		$date = date("Y-m-d H:i:s", tutor_time());

		do_action('tutor_before_add_question', $course_id);
		$data = apply_filters('tutor_add_question_data', array(
			'comment_post_ID'   => $course_id,
			'comment_author'    => $user->user_login,
			'comment_date'      => $date,
			'comment_date_gmt'  => get_gmt_from_date($date),
			'comment_content'   => $question,
			'comment_approved'  => 'waiting_for_answer',
			'comment_agent'     => 'TutorLMSPlugin',
			'comment_type'      => 'tutor_q_and_a',
			'user_id'           => $user_id,
		));

		$wpdb->insert($wpdb->comments, $data);
		$comment_id = (int) $wpdb->insert_id;

		if ($comment_id){
			$result = $wpdb->insert( $wpdb->commentmeta, array(
				'comment_id' => $comment_id,
				'meta_key' => 'tutor_question_title',
				'meta_value' => $question_title
			) );
		}
		do_action('tutor_after_add_question', $course_id, $comment_id);

		wp_send_json_success(__('Question has been added successfully', 'tutor'));
	}


	public function tutor_add_answer(){
		tutor_utils()->checking_nonce();
		global $wpdb;

		$answer = wp_kses_post($_POST['answer']);
		if ( ! $answer){
			wp_send_json_error(__('Please write answer', 'tutor'));
		}

		$question_id = (int) sanitize_text_field($_POST['question_id']);
		$question = tutor_utils()->get_qa_question($question_id);

		$user_id = get_current_user_id();
		$user = get_userdata($user_id);
		$date = date("Y-m-d H:i:s", tutor_time());

		if(!tutils()->has_enrolled_content_access('qa_question', $question_id)) {
			wp_send_json_error(array('message'=>__('Access Denied', 'tutor')));
			exit;
		}

		do_action('tutor_before_answer_to_question');
		$data = apply_filters('tutor_add_answer_data', array(
			'comment_post_ID'   => $question->comment_post_ID,
			'comment_author'    => $user->user_login,
			'comment_date'      => $date,
			'comment_date_gmt'  => get_gmt_from_date($date),
			'comment_content'   => $answer,
			'comment_approved'  => 'approved',
			'comment_agent'     => 'TutorLMSPlugin',
			'comment_type'      => 'tutor_q_and_a',
			'comment_parent'    => $question_id,
			'user_id'           => $user_id,
		));

		$wpdb->insert($wpdb->comments, $data);
		$comment_id = (int) $wpdb->insert_id;
		do_action('tutor_after_answer_to_question', $comment_id);

		wp_send_json_success(__('Answer has been added successfully', 'tutor'));
	}


	public function tutor_course_add_to_wishlist(){
		tutils()->checking_nonce();

		$course_id = (int) sanitize_text_field($_POST['course_id']);
		if ( ! is_user_logged_in()){
			wp_send_json_error(array('redirect_to' => wp_login_url( wp_get_referer() ) ) );
		}
		global $wpdb;

		$user_id = get_current_user_id();
		$if_added_to_list = $wpdb->get_row($wpdb->prepare("SELECT * from {$wpdb->usermeta} WHERE user_id = %d AND meta_key = '_tutor_course_wishlist' AND meta_value = %d;", $user_id, $course_id));

		if ( $if_added_to_list){
			$wpdb->delete($wpdb->usermeta, array('user_id' => $user_id, 'meta_key' => '_tutor_course_wishlist', 'meta_value' => $course_id ));
			wp_send_json_success(array('status' => 'removed', 'msg' => __('Course removed from wish list', 'tutor')));
		}else{
			add_user_meta($user_id, '_tutor_course_wishlist', $course_id);
			wp_send_json_success(array('status' => 'added', 'msg' => __('Course added to wish list', 'tutor')));
		}
	}

	/**
	 * Method for enable / disable addons
	 */
	public function addon_enable_disable(){

		if(!current_user_can( 'manage_options' )) {
			wp_send_json_error( array('message'=>__('Access Denied', 'tutor')) );
		}

		$addonsConfig = maybe_unserialize(get_option('tutor_addons_config'));

		$isEnable = (bool) sanitize_text_field(tutor_utils()->avalue_dot('isEnable', $_POST));
		$addonFieldName = sanitize_text_field(tutor_utils()->avalue_dot('addonFieldName', $_POST));

		do_action('tutor_addon_before_enable_disable');
		if ($isEnable){
			do_action("tutor_addon_before_enable_{$addonFieldName}");
			do_action('tutor_addon_before_enable', $addonFieldName);
			$addonsConfig[$addonFieldName]['is_enable'] = 1;
			update_option('tutor_addons_config', $addonsConfig);

			do_action('tutor_addon_after_enable', $addonFieldName);
			do_action("tutor_addon_after_enable_{$addonFieldName}");
		}else{
			do_action("tutor_addon_before_disable_{$addonFieldName}");
			do_action('tutor_addon_before_disable', $addonFieldName);
			$addonsConfig[$addonFieldName]['is_enable'] = 0;
			update_option('tutor_addons_config', $addonsConfig);

			do_action('tutor_addon_after_disable', $addonFieldName);
			do_action("tutor_addon_after_disable_{$addonFieldName}");
		}

		do_action('tutor_addon_after_enable_disable');
		wp_send_json_success();
	}

	/**
	 * Load review edit form
	 * @since v.1.4.0
	 */
	public function tutor_load_edit_review_modal(){
		tutor_utils()->checking_nonce();

		$review_id = (int) sanitize_text_field(tutils()->array_get('review_id', $_POST));
		$rating = tutils()->get_rating_by_id($review_id);

		if(!tutils()->has_enrolled_content_access('review', $review_id)) {
			wp_send_json_error(array('message'=>__('Access Denied', 'tutor')));
			exit;
		}

		ob_start();
		tutor_load_template('dashboard.reviews.edit-review-form', array('rating' => $rating));
		$output = ob_get_clean();

		wp_send_json_success(array('output' => $output));
	}

	public function tutor_update_review_modal(){
		global $wpdb;

		tutor_utils()->checking_nonce();

		$review_id = (int) sanitize_text_field(tutils()->array_get('review_id', $_POST));
		$rating = sanitize_text_field(tutor_utils()->avalue_dot('rating', $_POST));
		$review = wp_kses_post(tutor_utils()->avalue_dot('review', $_POST));

		if(!tutils()->has_enrolled_content_access('review', $review_id)) {
			wp_send_json_error(array('message'=>__('Access Denied', 'tutor')));
			exit;
		}

		$is_exists = $wpdb->get_var($wpdb->prepare("SELECT comment_ID from {$wpdb->comments} WHERE comment_ID=%d AND comment_type = 'tutor_course_rating' ;", $review_id));

		if ( $is_exists) {
			$wpdb->update( $wpdb->comments, array( 'comment_content' => $review ),
				array( 'comment_ID' => $review_id )
			);
			$wpdb->update( $wpdb->commentmeta, array( 'meta_value' => $rating ),
				array( 'comment_id' => $review_id, 'meta_key' => 'tutor_rating' )
			);

			do_action('tutor_after_review_update', $review_id, $is_exists);

			wp_send_json_success();
		}
		wp_send_json_error();
	}

	/**
	 * Process ajax login
	 * @since v.1.6.3
	 */
	public function process_ajax_login(){
		tutils()->checking_nonce();

		$username = tutils()->array_get('log', $_POST);
		$password = tutils()->array_get('pwd', $_POST);
		$redirect_to = tutils()->array_get('redirect_to', $_POST);

		try {
			$creds = array(
				'user_login'    => trim( wp_unslash( $username ) ), // phpcs:ignore WordPress.Security.ValidatedSanitizedInput.InputNotSanitized
				'user_password' => $password, // phpcs:ignore WordPress.Security.ValidatedSanitizedInput.InputNotSanitized, WordPress.Security.ValidatedSanitizedInput.MissingUnslash
				'remember'      => isset( $_POST['rememberme'] ), // phpcs:ignore WordPress.Security.ValidatedSanitizedInput.InputNotSanitized
			);

			$validation_error = new \WP_Error();
			// var_dump($validation_error->get_error_code());
			// die();
			$validation_error = apply_filters( 'tutor_process_login_errors', $validation_error, $creds['user_login'], $creds['user_password'] );
			if ( $validation_error->get_error_code() ) {
				wp_send_json_error( '<strong>' . __( 'ERROR:', 'tutor' ) . '</strong> ' . $validation_error->get_error_message() );
			}

			if ( empty( $creds['user_login'] ) ) {
				wp_send_json_error( '<strong>' . __( 'ERROR:', 'tutor' ) . '</strong> ' . __( 'Username is required.', 'tutor' ) );
			}

			// On multisite, ensure user exists on current site, if not add them before allowing login.
			if ( is_multisite() ) {
				$user_data = get_user_by( is_email( $creds['user_login'] ) ? 'email' : 'login', $creds['user_login'] );

				if ( $user_data && ! is_user_member_of_blog( $user_data->ID, get_current_blog_id() ) ) {
					add_user_to_blog( get_current_blog_id(), $user_data->ID, 'customer' );
				}
			}

			// Perform the login.
			$user = wp_signon( apply_filters( 'tutor_login_credentials', $creds ), is_ssl() );

			if ( is_wp_error( $user ) ) {
				$message = $user->get_error_message();
				$message = str_replace( '<strong>' . esc_html( $creds['user_login'] ) . '</strong>', '<strong>' . esc_html( $creds['user_login'] ) . '</strong>', $message );
				
				wp_send_json_error( $message );
			} else {
				wp_send_json_success([
					'redirect' => apply_filters('tutor_login_redirect_url', $redirect_to)
				]);
			}
		} catch ( \Exception $e ) {
			wp_send_json_error( apply_filters( 'login_errors', $e->getMessage()) );
			do_action( 'tutor_login_failed' );
		}
	}
	
	/**
	 * Create/Update announcement
	 * @since  v.1.7.9
	 */
	public function create_or_update_annoucement() {   
        //prepare alert message
        $create_success_msg = __("Announcement created successfully",'tutor');
        $update_success_msg = __("Announcement updated successfully",'tutor');
        $create_fail_msg = __("Announcement creation failed",'tutor');
        $update_fail_msg = __("Announcement update failed",'tutor');

        $error = array();
        $response = array();
		tutils()->checking_nonce();

		$course_id = sanitize_text_field($_POST['tutor_announcement_course']);
		$announcement_title = sanitize_text_field($_POST['tutor_announcement_title']);
		$announcement_summary = sanitize_textarea_field($_POST['tutor_announcement_summary']);
		
		if(!tutils()->can_user_manage('course', $course_id)) {
			wp_send_json_error( array('message'=>__('Access Denied', 'tutor')) );
		}
        
        //set data and sanitize it
        $form_data = array(
			'post_type' => 'tutor_announcements',
			'post_title' => $announcement_title,
			'post_content' => $announcement_summary,
			'post_parent' => $course_id,
			'post_status' => 'publish'
        );

        if (isset($_POST['announcement_id'])) {
            $form_data['ID'] = sanitize_text_field($_POST['announcement_id']);
        }

        //validation message set
        if (empty($form_data['post_parent'])) {
            $error['post_parent'] = __('Course name required','tutor'); 

		}
		
        if (empty($form_data['post_title'])) {
            $error['post_title'] = __('Announcement title required','tutor'); 
		}
		
        if (empty($form_data['post_content'])) {
            $error['post_content'] = __('Announcement summary required','tutor'); 

        }

        if (count($error)>0) {
            $response['status']     = 'validation_error';
            $response['message']    = $error;
            wp_send_json($response);
        } else {
            //insert or update post
            $post_id = wp_insert_post($form_data);
            if ($post_id > 0) {
				$announcement = get_post($post_id);
				$action_type = sanitize_textarea_field($_POST['action_type']);
                $response['status'] = 'success';
                //set reponse message as per action type
                $response['message'] = ($action_type == 'create') ? $create_success_msg : $update_success_msg;

                //provide action hook
                if (isset($_POST['tutor_notify_students']) && $_POST['tutor_notify_students']) {
                    do_action('tutor_announcements_notify_students', $post_id, $announcement, $action_type);
				}
				
				do_action('tutor_announcements/after/save', $post_id, $announcement);
                
                wp_send_json($response);
            } else {
                //failure message
                $response['status']     = 'fail';
                if($_POST['action_type'] == 'create'){
                    $response['message'] = $create_fail_msg;
                }
                if($_POST['action_type'] == 'update'){
                    $response['message'] = $update_fail_msg;
                }
                wp_send_json($response);
            }
        }
    }

	/**
	 * Delete announcement
	 * @since  v.1.7.9
	 */
    public function delete_annoucement() {
		$announcement_id = sanitize_text_field($_POST['announcement_id']);
		tutils()->checking_nonce();

		if(!tutils()->can_user_manage('announcement', $announcement_id)) {
			wp_send_json_error( array('message'=>__('Access Denied', 'tutor')) );
		}

        $delete = wp_delete_post($announcement_id);
        if ($delete) {
            $response = array(
                'status'    => 'success',
                'message'   => __('Announcement deleted successfully','tutor')
            );
            wp_send_json($response);
        } else {
            $response = array(
                'status'    => 'fail',
                'message'   => __('Announcement delete failed','tutor')
            );      
            wp_send_json($response);     
        }
    }

	/**
	 * Create/Update  rehearsal
	 * @since  v.1.7.9
	 */
	public function create_or_update_rehearsal() {   
		// var_dump($_POST['tutor_rehearsal_level']);
		// die();
		include(dirname(__FILE__,3).'/woocommerce/includes/class-wc-product-download.php');

        //prepare alert message
        $create_success_msg = __("Rehearsal course created successfully",'tutor');
        $update_success_msg = __("Rehearsal course updated successfully",'tutor');
        $create_fail_msg = __("Rehearsal course creation failed",'tutor');
        $update_fail_msg = __("Rehearsal course update failed",'tutor');

        $error = array();
        $response = array();
		tutils()->checking_nonce();

		$tutor_rehearsal_type = sanitize_text_field($_POST['tutor_rehearsal_type']);
		$tutor_rehearsal_service_type = sanitize_text_field($_POST['tutor_rehearsal_service_type']);
		$tutor_rehearsal_title = sanitize_text_field($_POST['tutor_rehearsal_title']);
		$tutor_rehearsal_description = $_POST['tutor_rehearsal_description'];
		$tutor_rehearsal_keywords = sanitize_text_field($_POST['tutor_rehearsal_keywords']);
		$tutor_rehearsal_level = array_key_exists('tutor_rehearsal_level', $_POST) ? json_encode($_POST['tutor_rehearsal_level']) : "";
		$tutor_rehearsal_language = array_key_exists('tutor_rehearsal_language', $_POST) ? sanitize_text_field($_POST['tutor_rehearsal_language']) : "";
		$tutor_rehearsal_subject = sanitize_text_field($_POST['tutor_rehearsal_subject']);
		$tutor_rehearsal_date = sanitize_text_field($_POST['tutor_rehearsal_date']);
		$tutor_rehearsal_date_to = sanitize_text_field($_POST['tutor_rehearsal_date_to']);
		$tutor_rehearsal_hour = sanitize_text_field($_POST['tutor_rehearsal_hour']);
		$tutor_rehearsal_price = sanitize_text_field($_POST['tutor_rehearsal_price']);
		$tutor_rehearsal_platform = sanitize_text_field($_POST['tutor_rehearsal_platform']);
		$tutor_rehearsal_link = sanitize_text_field($_POST['tutor_rehearsal_link']);
		$tutor_rehearsal_identifiers = sanitize_text_field($_POST['tutor_rehearsal_identifiers']);
		$tutor_rehearsal_type_data = sanitize_text_field($_POST['tutor_rehearsal_type_data']);
		$tutor_rehearsal_date_limite = sanitize_text_field($_POST['tutor_rehearsal_date_limite']);
		$tutor_rehearsal_min_student_number = sanitize_text_field($_POST['tutor_rehearsal_min_student_number']);
		$tutor_rehearsal_max_student_number = sanitize_text_field($_POST['tutor_rehearsal_max_student_number']);
        
        //set data and sanitize it
        $form_data = array(
			'post_type'    => 'product',
			'post_title'   => $tutor_rehearsal_title,
			'post_content' => $tutor_rehearsal_description,
			'post_excerpt' => "rehearsal-course-".get_current_user_id(),
			'post_status'  => 'draft'
        );

        if (isset($_POST['rehearsal_id'])) {
            $form_data['ID'] = sanitize_text_field($_POST['rehearsal_id']);
        }

        //validation message set 
        if (empty($form_data['post_title'])) {
			if ($tutor_rehearsal_type_data == "Rehearsal Course") {
				if ($tutor_rehearsal_service_type == "Rehearsal Course") {
					$error['post_title'] = __('Private tutoring title required','tutor'); 
				}else{
					$error['post_title'] = __('Exam preparation course title required','tutor'); 
				}
			}else{
            	$error['post_title'] = __('E-Book title required','tutor');
			}
		}
		
        if (empty($form_data['post_content'])) {
			if ($tutor_rehearsal_type_data == "Rehearsal Course") {
				if ($tutor_rehearsal_service_type == "Rehearsal Course") {
					$error['post_content'] = __('Private tutoring summary required','tutor'); 
				}else{
					$error['post_content'] = __('Exam preparation course summary required','tutor'); 
				}
			}else{
            	$error['post_content'] = __('E-Book summary required','tutor');
			}
        }
		
        if (empty($tutor_rehearsal_price)) {
			if ($tutor_rehearsal_type_data == "Rehearsal Course") {
				if ($tutor_rehearsal_service_type == "Rehearsal Course") {
					$error['tutor_rehearsal_price'] = __('Private tutoring price required','tutor'); 
				}else{
					$error['tutor_rehearsal_price'] = __('Exam preparation course price required','tutor'); 
				}
			}else{
            	$error['tutor_rehearsal_price'] = __('E-Book price required','tutor');
			}
        }
		
        if (empty($tutor_rehearsal_level)) {
			if ($tutor_rehearsal_type_data == "Rehearsal Course") {
				if ($tutor_rehearsal_service_type == "Rehearsal Course") {
					$error['tutor_rehearsal_level'] = __('Private tutoring level required','tutor'); 
				}else{
					$error['tutor_rehearsal_level'] = __('Exam preparation course level required','tutor'); 
				}
			}else{
            	// $error['tutor_rehearsal_level'] = __('E-Book level required','tutor');
			}
        }
		
        if (empty($tutor_rehearsal_language)) {
			if ($tutor_rehearsal_type_data == "Rehearsal Course") {
				if ($tutor_rehearsal_service_type == "Rehearsal Course") {
					$error['tutor_rehearsal_language'] = __('Private tutoring language required','tutor');
				}else{
					$error['tutor_rehearsal_language'] = __('Exam preparation course language required','tutor');
				} 
			}else{
            	// $error['tutor_rehearsal_language'] = __('E-Book language required','tutor');
			}
        }
		
        if (empty($tutor_rehearsal_subject)) {
			if ($tutor_rehearsal_type_data == "Rehearsal Course") {
				if ($tutor_rehearsal_service_type == "Rehearsal Course") {
					$error['tutor_rehearsal_subject'] = __('Private tutoring subject required','tutor');
				}else{
					$error['tutor_rehearsal_subject'] = __('Exam preparation course subject required','tutor');
				}  
			}
        }
		
        if (empty($tutor_rehearsal_min_student_number)) {
			if ($tutor_rehearsal_type_data == "Rehearsal Course") {
				if ($tutor_rehearsal_service_type == "Rehearsal Course") {
					$error['tutor_rehearsal_min_student_number'] = __( 'Minimum students number required', 'edumall' ); 
				}else{
					$error['tutor_rehearsal_min_student_number'] = __( 'Minimum students number required', 'edumall' ); 
				}  
			}
        }
		
        if (empty($tutor_rehearsal_max_student_number)) {
			if ($tutor_rehearsal_type_data == "Rehearsal Course") {
				if ($tutor_rehearsal_service_type == "Rehearsal Course") {
					$error['tutor_rehearsal_max_student_number'] = __( 'Maximum students number required', 'edumall' ); 
				}else{
					$error['tutor_rehearsal_max_student_number'] = __( 'Maximum students number required', 'edumall' ); 
				}  
			}
        }
		$image_rehearsal_ebook = "";
		$image_rehearsal_ebook_id = "";
		$my_custom_filename_cv__1 = '';
		if($_FILES['tutor_rehearsal_image']['size'] > 0){
			$my_custom_filename_cv = time() . strtolower($_FILES['tutor_rehearsal_image']['name']);
			$image_rehearsal_ebook = wp_upload_bits($my_custom_filename_cv , null, file_get_contents($_FILES['tutor_rehearsal_image']['tmp_name']));
			$image_rehearsal_ebook = $image_rehearsal_ebook['url'];
		}

		$file_url = [];
		if(count($_FILES['tutor_rehearsal_file']['name']) != 1 && $_FILES['tutor_rehearsal_file']['size'][1] > 0){
			$length = count($_FILES['tutor_rehearsal_file']['name']);
			for ($i=0; $i < $length; $i++) { 
				$my_custom_filename_diplome = time() . $i . strtolower($_FILES['tutor_rehearsal_file']['name'][$i]);
				$upload_diplome = wp_upload_bits($my_custom_filename_diplome, null, file_get_contents($_FILES['tutor_rehearsal_file']['tmp_name'][$i]));
				$file_url[] = $upload_diplome["url"];
			}
		}
        if (count($error)>0) {
            $response['status']     = 'validation_error';
            $response['message']    = $error;
            wp_send_json($response);
        } else {
            //insert or update post
            $post_id = wp_insert_post($form_data);
			$old_max_student_number = get_post_meta($post_id, "tutor_rehearsal_max_student_number", true);
            if ($post_id > 0) {
				// $announcement = get_post($post_id);
				update_post_meta($post_id, '_visibility', 'visible' );
				update_post_meta($post_id, 'tutor_rehearsal_type', $tutor_rehearsal_type);
				update_post_meta($post_id, 'tutor_rehearsal_service_type', $tutor_rehearsal_service_type);
				update_post_meta($post_id, 'tutor_rehearsal_title', $tutor_rehearsal_title);
				update_post_meta($post_id, 'tutor_rehearsal_description', $tutor_rehearsal_description);
				update_post_meta($post_id, 'tutor_rehearsal_keywords', $tutor_rehearsal_keywords);
				update_post_meta($post_id, 'tutor_rehearsal_level', $tutor_rehearsal_level);
				update_post_meta($post_id, 'tutor_rehearsal_language', $tutor_rehearsal_language);
				update_post_meta($post_id, 'tutor_rehearsal_subject', $tutor_rehearsal_subject);
				update_post_meta($post_id, 'tutor_rehearsal_date', $tutor_rehearsal_date);
				update_post_meta($post_id, 'tutor_rehearsal_date_to', $tutor_rehearsal_date_to);
				update_post_meta($post_id, 'tutor_rehearsal_hour', $tutor_rehearsal_hour);
				update_post_meta($post_id, 'tutor_rehearsal_price', $tutor_rehearsal_price);
				update_post_meta($post_id, 'tutor_rehearsal_platform', $tutor_rehearsal_platform);
				update_post_meta($post_id, 'tutor_rehearsal_link', $tutor_rehearsal_link);
				update_post_meta($post_id, 'tutor_rehearsal_identifiers', $tutor_rehearsal_identifiers);
				update_post_meta($post_id, 'tutor_rehearsal_type_data', $tutor_rehearsal_type_data);
				update_post_meta($post_id, 'tutor_rehearsal_date_limite', $tutor_rehearsal_date_limite);
				update_post_meta($post_id, 'tutor_rehearsal_min_student_number', $tutor_rehearsal_min_student_number);
				update_post_meta($post_id, 'tutor_rehearsal_max_student_number', $tutor_rehearsal_max_student_number);
				if ($image_rehearsal_ebook != "" && $image_rehearsal_ebook != "undefined" && $image_rehearsal_ebook != null) {
					update_post_meta($post_id, 'tutor_rehearsal_image', $image_rehearsal_ebook);
				}
				update_post_meta($post_id, 'tutor_rehearsal_file', json_encode($file_url));

				$product = wc_get_product( $post_id );
				$product->set_sku( "rehearsal-course-".$post_id );
				$product->set_name($tutor_rehearsal_title);
				//$product->set_status("publish");  // can be publish,draft or any wordpress post status
				$product->set_catalog_visibility('visible'); // add the product visibility status
				$product->set_description($tutor_rehearsal_description);
				$product->set_price($tutor_rehearsal_price); // set product price
				$product->set_regular_price($tutor_rehearsal_price); // set product regular price
				$product->set_sale_price($tutor_rehearsal_price);
				$product->set_stock_status('instock'); // in stock or out of stock value
				if ($tutor_rehearsal_max_student_number > 0 && $old_max_student_number != $tutor_rehearsal_max_student_number) {
					$product->set_manage_stock(true);
					$product->set_stock_quantity($tutor_rehearsal_max_student_number);
				}
				
								
				if ($tutor_rehearsal_type_data == "E-Book") {
					foreach ($file_url as $key => $value) {
						$pd_object = new \WC_Product_Download();
						$download_id = md5( $value );
						$pd_object->set_id( $download_id );
						$pd_object->set_name( $tutor_rehearsal_title.'-'.$key );
						$pd_object->set_file( $value );
	
						$downloads = $product->get_downloads();
						$downloads[$download_id] = $pd_object;
						$product->set_downloads($downloads);
					}
					$product->set_downloadable(true);
					
					if (class_exists('WC_Product_Download')) {
						error_log('⚠️ CHECK no.2: Classe WC_Product_Download déjà déclarée par ' . debug_backtrace()[0]['file']);						
					}
				}
				
				$product->set_backorders('no');
				$product->set_reviews_allowed(true);
				$product->set_sold_individually(false);
				$product->set_category_ids(array(404)); 

				$wp_upload_dir = wp_upload_dir();
				$attachment = array(
					'guid'=> $image_rehearsal_ebook, 
					'post_mime_type' => 'image/png',
					'post_title' => 'my description',
					'post_content' => 'my description',
					'post_status' => 'inherit'
				);
				if ($image_rehearsal_ebook != "") {
					$image_rehearsal_ebook_id = wp_insert_attachment($attachment, explode("uploads/",$image_rehearsal_ebook)[1], $post_id);
	
					$product->set_image_id($image_rehearsal_ebook_id);
				}

				$product_id = $product->save();

				update_post_meta($post_id, '_tutor_course_product_id', $product_id);
				wp_update_post( array(
					'ID' => $post_id,
					'post_status' => 'draft',
				) );
				$action_type = sanitize_textarea_field($_POST['action_type']);
                $response['status'] = 'success';
                //set reponse message as per action type
                $response['message'] = ($action_type == 'create') ? $create_success_msg : $update_success_msg;

                //provide action hook
                // if (isset($_POST['tutor_notify_students']) && $_POST['tutor_notify_students']) {
				$rehearsal_ebook = get_post($post_id);
				$action_type = sanitize_textarea_field($_POST['action_type']);
				
                do_action('tutor_rehearsal_notify_everyone', $post_id, $rehearsal_ebook, $action_type);
				// var_dump($response);
				// die();
				// }
				
				// do_action('tutor_announcements/after/save', $post_id, $announcement);
                
                wp_send_json($response);
            } else {
                //failure message
                $response['status']     = 'fail';
                if($_POST['action_type'] == 'create'){
                    $response['message'] = $create_fail_msg;
                }
                if($_POST['action_type'] == 'update'){
                    $response['message'] = $update_fail_msg;
                }
                // $response['status']     = 'fail';
                wp_send_json($response);
            }
        }
    }

	
    public function delete_rehearsal() {
		$rehearsal_id = sanitize_text_field($_POST['rehearsal_id']);
		tutils()->checking_nonce();
		$name = get_the_title($_POST['rehearsal_id']);
		$action_type = get_post_meta($_POST['rehearsal_id'], "tutor_rehearsal_type_data", true);
        $delete = wp_delete_post($rehearsal_id);
        if ($delete) {
            $response = array(
                'status'    => 'success',
                'message'   => __('Rehearsal course deleted successfully','tutor')
            );
			do_action('tutor_rehearsal_notify_admin_deletion_rehearsal', $name, $action_type);
            wp_send_json($response);
        } else {
            $response = array(
                'status'    => 'fail',
                'message'   => __('Rehearsal course delete failed','tutor')
            );      
            wp_send_json($response);     
        }
    }
	
    public function delete_account_user() {
		$user_id = sanitize_text_field($_POST['user_id']);
		tutils()->checking_nonce();
        $total_students = \Edumall_Tutor::instance()->get_total_students_by_instructor( $user_id );
		if ($total_students > 0) {
			$response = array(
				'status'    => 'fail',
				'message'   => __('You have '.$total_students.' students enrolled to your course, before deleting your account you must contact the administrator on this email: <a style="color:blue;" href="mailto:'.get_option( 'admin_email' ).'">'.get_option( 'admin_email' ).'</a>','tutor')
			);
			wp_send_json($response);
			wp_die();
		} else {
			$delete = wp_delete_user($user_id);
	
			if ($delete) {
				$response = array(
					'status'    => 'success',
					'message'   => __('Account deleted successfully','tutor')
				);
				wp_send_json($response);
				wp_logout();
        		header("refresh:0.5;url=".$_SERVER['REQUEST_URI']."");
			} else {
				$response = array(
					'status'    => 'fail',
					'message'   => __('Account delete failed','tutor')
				);      
				wp_send_json($response);     
			}
		}
    }
}