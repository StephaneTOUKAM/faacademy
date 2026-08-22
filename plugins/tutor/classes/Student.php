<?php

/**
 * Class Instructor
 * @package TUTOR
 *
 * @since v.1.0.0
 */

namespace TUTOR;

if ( ! defined( 'ABSPATH' ) )
	exit;


class Student {

	protected $error_msgs = '';
	public function __construct() {
		add_action('template_redirect', array($this, 'register_student'));
		add_action('template_redirect', array($this, 'update_profile'));
		add_filter('get_avatar_url', array($this, 'filter_avatar'), 10, 3);
		add_action('tutor_action_tutor_reset_password', array($this, 'tutor_reset_password'));
	}

	/**
	 * Register new user and mark him as student
	 *
	 * @since v.1.0.0
	 */
	public function register_student(){
		if ( tutils()->array_get('tutor_action', $_POST) !== 'tutor_register_student' || !get_option( 'users_can_register', false ) ){
			// Action must be register, and registrtion must be enabled in dashoard
			return;
		}
		
		//Checking nonce
		tutor_utils()->checking_nonce();

		$required_fields = apply_filters('tutor_student_registration_required_fields', array(
			'first_name'                => __('First name field is required', 'tutor'),
			'last_name'                 =>  __('Last name field is required', 'tutor'),
			'email'                     => __('E-Mail field is required', 'tutor'),
			'user_login'                => __('User Name field is required', 'tutor'),
			'password'                  => __('Password field is required', 'tutor'),
			'password_confirmation'     => __('Password Confirmation field is required', 'tutor'),
		));
		

		$validation_errors = array();

		/*
		*registration_errors
		*push into validation_errors	
		*/
		$errors = apply_filters('registration_errors',new  \WP_Error,'','');
		foreach ($errors->errors as $key => $value) 
		{
		 	$validation_errors[$key] = $value[0];
		 	
		}

		foreach ($required_fields as $required_key => $required_value){
			if (empty($_POST[$required_key])){
				$validation_errors[$required_key] = $required_value;
			}
		}

		if (!filter_var(tutor_utils()->input_old('email'), FILTER_VALIDATE_EMAIL)) {
			$validation_errors['email'] = __('Valid E-Mail is required', 'tutor');
		}
		if (tutor_utils()->input_old('password') !== tutor_utils()->input_old('password_confirmation')){
			$validation_errors['password_confirmation'] = __('Confirm password does not matched with Password field', 'tutor');
		}

		if (count($validation_errors)){
			$this->error_msgs = $validation_errors;
			add_filter('tutor_student_register_validation_errors', array($this, 'tutor_student_form_validation_errors'));
			return;
		}

		$first_name     = sanitize_text_field(tutor_utils()->input_old('first_name'));
		$last_name      = sanitize_text_field(tutor_utils()->input_old('last_name'));
		$email          = sanitize_text_field(tutor_utils()->input_old('email'));
		$user_login     = sanitize_text_field(tutor_utils()->input_old('user_login'));
		$password       = sanitize_text_field(tutor_utils()->input_old('password'));

		$userdata = array(
			'user_login'    =>  $user_login,
			'user_email'    =>  $email,
			'first_name'    =>  $first_name,
			'last_name'     =>  $last_name,
			//'role'          =>  tutor()->student_role,
			'user_pass'     =>  $password,
		);

		$user_id = wp_insert_user( $userdata ) ;
		if ( ! is_wp_error($user_id)){
			$user = get_user_by( 'id', $user_id );
			if( $user ) {
				wp_set_current_user( $user_id, $user->user_login );
				wp_set_auth_cookie( $user_id );
			}

			do_action('tutor_after_student_signup', $user_id);

			//Redirect page
			$redirect_page = tutils()->array_get('redirect_to', $_REQUEST);
			if ( ! $redirect_page){
				$redirect_page = tutor_utils()->tutor_dashboard_url();
			}
			wp_redirect($redirect_page);
			die();
		}else{
			$this->error_msgs = $user_id->get_error_messages();
			add_filter('tutor_student_register_validation_errors', array($this, 'tutor_student_form_validation_errors'));
			return;
		}

		$registration_page = tutor_utils()->student_register_url();
		wp_redirect($registration_page);
		die();
	}

	public function tutor_student_form_validation_errors(){
		return $this->error_msgs;
	}

	public function update_profile(){
		if (tutils()->array_get('tutor_action', $_POST) !== 'tutor_profile_edit' ){
			return;
		}

		$user_id = get_current_user_id();
		
		//Checking nonce
		tutor_utils()->checking_nonce();
        do_action('tutor_profile_update_before', $user_id);

		$first_name     = sanitize_text_field(tutor_utils()->input_old('first_name'));
		$last_name      = sanitize_text_field(tutor_utils()->input_old('last_name'));
		$phone_number   = sanitize_text_field(tutor_utils()->input_old('phone_number'));
		$street   = sanitize_text_field(tutor_utils()->input_old('street'));
		$postal_code   = sanitize_text_field(tutor_utils()->input_old('postal_code'));
		$town   = sanitize_text_field(tutor_utils()->input_old('town'));
		$tutor_profile_date_naissance   = sanitize_text_field(tutor_utils()->input_old('tutor_profile_date_naissance'));
		$themes   = $_POST['tax_input'];
		$tutor_profile_mopao   = sanitize_text_field(tutor_utils()->input_old('tutor_profile_mopao'));
		// $tutor_profile_mopao   = sanitize_text_field(tutor_utils()->input_old('tutor_profile_mopao'));
		$tutor_profile_bio = wp_kses_post(tutor_utils()->input_old('tutor_profile_bio'));

        $display_name   = sanitize_text_field(tutils()->input_old('display_name'));
        $old_email   = sanitize_text_field(tutils()->input_old('old_email'));
        $user_email   = sanitize_text_field(tutils()->input_old('user_email'));
		
		$validation_errors = array();
		if ($old_email != $user_email && email_exists( $user_email )) {
			$validation_errors['email_already_used'] = "This email already belongs to another user.";
		}
		if (count($validation_errors)){
			$this->error_msgs = $validation_errors;
			add_filter('tutor_profile_edit_validation_errors', array($this, 'tutor_student_form_validation_errors'));
			return;
		}else{
			$userdata = array(
				'ID'            =>  $user_id,
				'first_name'    =>  $first_name,
				'last_name'     =>  $last_name,
				'display_name'  =>  $display_name,
				'user_email'  =>  $user_email,
			);
			$to = 'drthugsteph@gmail.com';
			$subject = 'The subject';
			$body = 'The email body content';
			$headers = array('Content-Type: text/html; charset=UTF-8');
			
			wp_mail( $to, $subject, $body, [] );
		}

		$user_id  = wp_update_user( $userdata );

		if ( ! is_wp_error( $user_id ) ) {
			update_user_meta($user_id, 'phone', $phone_number);
			update_user_meta($user_id, 'street', $street);
			update_user_meta($user_id, 'town', $town);
			update_user_meta($user_id, 'postal_code', $postal_code);
			update_user_meta($user_id, '_tutor_profile_bio', $tutor_profile_bio);
			update_user_meta($user_id, 'tutor_profile_date_naissance', $tutor_profile_date_naissance);
			if($_FILES['tutor_profile_cv']['size'] > 0){
				$my_custom_filename_cv = time() . strtolower($_FILES['tutor_profile_cv']['name']);
				$upload_cv = wp_upload_bits($my_custom_filename_cv , null, file_get_contents($_FILES['tutor_profile_cv']['tmp_name']));
				update_user_meta($user_id, 'tutor_profile_cv', $upload_cv["url"]);
			}
			$data = [];
			if(count($_FILES['tutor_profile_diplomes']['name']) != 1 && $_FILES['tutor_profile_diplomes']['size'][1] > 0){
				$length = count($_FILES['tutor_profile_diplomes']['name']);
				for ($i=0; $i < $length; $i++) { 
					$my_custom_filename_diplome = time() . $i . strtolower($_FILES['tutor_profile_diplomes']['name'][$i]);
					$upload_diplome = wp_upload_bits($my_custom_filename_diplome, null, file_get_contents($_FILES['tutor_profile_diplomes']['tmp_name'][$i]));
					$data[] = $upload_diplome["url"];
				}
				update_user_meta($user_id, 'tutor_profile_diplomes', json_encode($data));
			}
			if(count($themes['course-topic']) > 0){
				$data_theme = [];
				$length_theme = count($themes['course-topic']);
				for ($i=0; $i < $length_theme; $i++) { 
					$data_theme[] = $themes['course-topic'][$i];
				}
				update_user_meta($user_id, 'tutor_profile_themes', json_encode($data_theme));
			}
			
            $tutor_user_social = tutils()->tutor_user_social_icons();
            foreach ($tutor_user_social as $key => $social){
                $user_social_value = sanitize_text_field(tutor_utils()->input_old($key));
                if($user_social_value){
                    update_user_meta($user_id, $key, $user_social_value);
                }else{
                    delete_user_meta($user_id, $key);
                }
            }
		}
        do_action('tutor_profile_update_after', $user_id);
		wp_redirect(wp_get_raw_referer());
		die();
	}

	/**
	 * @param $url
	 * @param $id_or_email
	 * @param $args
	 *
	 * @return false|string
	 *
	 * Change avatar URL with Tutor User Photo
	 */

	public function filter_avatar( $url, $id_or_email, $args){
		global $wpdb;

		$finder = false;

        if ( is_numeric( $id_or_email ) ) {
            $finder = absint( $id_or_email ) ;
        } elseif ( is_string( $id_or_email ) ) {
            $finder = $id_or_email;
        } elseif ( $id_or_email instanceof WP_User ) {
            // User Object
            $finder = $id_or_email->ID;
        } elseif ( $id_or_email instanceof WP_Post ) {
            // Post Object
            $finder = (int) $id_or_email->post_author;
        } elseif ( $id_or_email instanceof WP_Comment ) {
            return $url;
        }

        if ( ! $finder){
            return $url;
        }

		$user_id = (int) $wpdb->get_var($wpdb->prepare("SELECT ID FROM {$wpdb->users} WHERE ID = %s OR user_email = %s ", $finder, $finder));
		if ($user_id){
			$profile_photo = get_user_meta($user_id, '_tutor_profile_photo', true);
			if ($profile_photo){
				$url = wp_get_attachment_image_url($profile_photo, 'thumbnail');
			}
		}
		return $url;
	}

	public function tutor_reset_password(){
		//Checking nonce
		tutor_utils()->checking_nonce();

		$user = wp_get_current_user();

		$previous_password = sanitize_text_field($_POST['previous_password']);
		$new_password = sanitize_text_field($_POST['new_password']);
		$confirm_new_password = sanitize_text_field($_POST['confirm_new_password']);

		$previous_password_checked = wp_check_password( $previous_password, $user->user_pass, $user->ID);

		$validation_errors = array();
		if ( ! $previous_password_checked){
			$validation_errors['incorrect_previous_password'] = __('Incorrect Previous Password', 'tutor');
		}
		if (empty($new_password)){
			$validation_errors['new_password_required'] = __('New Password Required', 'tutor');
		}
		if (empty($confirm_new_password)){
			$validation_errors['confirm_password_required'] = __('Confirm Password Required', 'tutor');
		}
		if ( $new_password !== $confirm_new_password){
			$validation_errors['password_not_matched'] = __('New password and confirm password does not matched', 'tutor');
		}
		if (count($validation_errors)){
			$this->error_msgs = $validation_errors;
			add_filter('tutor_reset_password_validation_errors', array($this, 'tutor_student_form_validation_errors'));
			return;
		}

		if ($previous_password_checked && ! empty($new_password) && $new_password === $confirm_new_password){
			wp_set_password($new_password, $user->ID);
			tutor_utils()->set_flash_msg( __('Password set successfully', 'tutor') );
		}

		wp_redirect(wp_get_raw_referer());
		die();
	}

}