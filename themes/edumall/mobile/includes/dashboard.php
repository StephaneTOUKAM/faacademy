<?php

namespace edumallmobile;

use edumallmobile\framework\Edumall_Tutor_DashBoard_Controller;
use edumallmobile\framework\Edumall_Tutor_Shortcode;
use edumallmobile\utils\Edumall_Mobile_Utils;

class Edumall_Dashboard {
	protected static $instance = null;


	public function __construct() {

	}

	public static function instance() {
		if ( null === self::$instance ) {
			self::$instance = new self();
		}

		return self::$instance;
	}

	public function wishlist($request ) {
		$user_role         = Edumall_Mobile_Utils::role_user();
		$data              = array();
		$data['user_role'] = $user_role;
		if($user_role != 0) {
			$user=Edumall_Mobile_Utils::edumall_mobile_get_user();
			$value = Edumall_Tutor_DashBoard_Controller::instance()->wishlist($user->ID);


			if(count($value)<= 0) {
				$data['message'] = html_entity_decode(translate( 'You haven\'t any courses on the wishlist yet.', 'edumall' ));
				$data['empty'] = true;
			}
			else {
				$data['wishlist'] = $value;
				$data['empty'] = false;
			}

			return Edumall_Mobile_Utils::get_respone( $data, 200 );
		}

		return Edumall_Mobile_Utils::get_respone( $data, 200 );
	}

	public function enrolled_courses( $request ) {
		$user_role         = Edumall_Mobile_Utils::role_user();
		$data              = array();
		$data['user_role'] = $user_role;
		if($user_role != 0) {
			$user=Edumall_Mobile_Utils::edumall_mobile_get_user();
			$value = Edumall_Tutor_DashBoard_Controller::instance()->enrolled_courses($user->ID);
			if(count($value)<= 0) {
				$data['message'] = html_entity_decode(translate( 'You didn\'t purchased any courses.', 'edumall' ));
				$data['empty'] = true;
			}
			else {
				$data['enrolled-courses'] = $value;
				$data['empty'] = false;
			}

			return Edumall_Mobile_Utils::get_respone( $data, 200 );
		}

		return Edumall_Mobile_Utils::get_respone( $data, 200 );
	}

	public function profile( $request ) {
		$user_role         = Edumall_Mobile_Utils::role_user();
		$data              = array();
		$data['user_role'] = $user_role;
		if($user_role != 0) {
			$user=Edumall_Mobile_Utils::edumall_mobile_get_user();
			if($user_role == 1 )
			{
				$value = Edumall_Tutor_DashBoard_Controller::instance()->profile($user->ID);
				$data['dashboard'] = $value;
			}
			else if($user_role == 2)
			{


			}


			return Edumall_Mobile_Utils::get_respone( $data, 200 );
		}

		return Edumall_Mobile_Utils::get_respone( $data, 200 );
	}

	public function settings( $request ) {
		$user_role         = Edumall_Mobile_Utils::role_user();
		$data              = array();
		$data['user_role'] = $user_role;
		if($user_role != 0) {
			$user = Edumall_Mobile_Utils::edumall_mobile_get_user();
			if($user_role == 1 )
			{
				$value = Edumall_Tutor_DashBoard_Controller::instance()->settings($user->ID,1);
				$data['dashboard'] = $value;
			}
			else if($user_role == 2)
			{
				$value = Edumall_Tutor_DashBoard_Controller::instance()->settings($user->ID,2);
				$data['dashboard'] = $value;
			}


			return Edumall_Mobile_Utils::get_respone( $data, 200 );
		}

		return Edumall_Mobile_Utils::get_respone( $data, 200 );
	}


	public function initialize() {

		$this->add_action_dashboard();
	}

	private function add_action_dashboard() {
		add_action( 'rest_api_init', [ $this, 'register_route_my_course' ] );
		add_action( 'rest_api_init', [ $this, 'register_route_wish_list' ] );
		add_action( 'rest_api_init', [ $this, 'register_route_profile' ] );
		add_action( 'rest_api_init', [ $this, 'register_route_settings' ] );

	}

	public function register_route_settings() {
		register_rest_route( EM_ENDPOINT, '/dashboard/settings', array(
			'methods'  => 'GET',
			'callback' => array( $this, 'settings' ),
			'permission_callback' => array( $this, 'permission_login' ),

		) );
	}

	public function register_route_my_course() {
		register_rest_route( EM_ENDPOINT, '/dashboard/enrolled-courses', array(
			'methods'  => 'GET',
			'callback' => array( $this, 'enrolled_courses' ),
			'permission_callback' => array( $this, 'permission_login' ),

		) );
	}

	public function register_route_wish_list() {
		register_rest_route( EM_ENDPOINT, '/dashboard/wishlist', array(
			'methods'  => 'GET',
			'callback' => array( $this, 'wishlist' ),
			'permission_callback' => array( $this, 'permission_login' ),

		) );
	}

	public function register_route_profile() {
		register_rest_route( EM_ENDPOINT, '/dashboard/profile', array(
			'methods'  => 'GET',
			'callback' => array( $this, 'profile' ),
			'permission_callback' => array( $this, 'permission_login' ),

		) );
	}

	public function permission_login() {
		return Edumall_Mobile_Utils::is_user_login();
	}



}

