<?php

namespace edumallmobile;

use edumallmobile\framework\Edumall_Tutor_Detail_Controller;

use edumallmobile\utils\Edumall_Mobile_Utils;

class Edumall_Mobile_Course_Detail {
	protected static $instance = null;


	public function __construct() {
	}

	public static function instance() {
		if ( null === self::$instance ) {
			self::$instance = new self();
		}

		return self::$instance;
	}

	public function index( $request ) {
		$user_role         = Edumall_Mobile_Utils::role_user();
		$data              = array();
		$data['user_role'] = $user_role;
		if ( ! isset( $request['post_id'] ) ) {

			return Edumall_Mobile_Utils::get_respone( $data, 400 );
		}

		$data['course_detail'] = Edumall_Tutor_Detail_Controller::instance()->detail( $request['post_id'] );

		return Edumall_Mobile_Utils::get_respone( $data, 200 );


	}

	public function initialize() {
		//require_once dirname( __FILE__ ) . '/utils/utils.php';
		$this->add_action_home();
	}

	private function add_action_home() {
		add_action( 'rest_api_init', [ $this, 'register_route_course_detail' ] );
	}


	public function register_route_course_detail() {
		register_rest_route( EM_ENDPOINT, '/course/detail', array(
			'methods'  => 'POST',
			'callback' => array( $this, 'index' ),
			'permission_callback' => '__return_true',

		) );
	}
}

