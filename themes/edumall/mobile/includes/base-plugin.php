<?php

namespace edumallmobile;

use edumallmobile\framework\Edumall_Course_Query_Mb;
use edumallmobile\framework\Edumall_Tutor_DashBoard_Controller;
use edumallmobile\framework\Edumall_Tutor_Detail_Controller;
use edumallmobile\framework\Edumall_Tutor_Shortcode;


require( 'framework/shortcode.php' );
require( 'framework/course-query-mb.php' );
require( 'framework/detail-controller.php' );
require( 'framework/dashboard-controller.php' );
require( 'framework/woo-controller.php' );
require( 'authenticate.php' );
require( 'home.php' );
require( 'category.php' );
require( 'utils/utils.php' );
require( 'course-filter.php' );
require( 'course-detail.php' );
require( 'dashboard.php' );

class Edumall_Mobile_Base_Plugin {
	protected static $instance = null;
	public           $edumall_mobile_authenticate_instance;
	public           $edumall_mobile_home_instance;
	public           $edumall_mobile_category_instance;
	public           $edumall_mobile_course_filter;
	public           $edumall_mobile_course_detail;
	public           $edumall_mobile_dash_board;

	public function __construct() {
		//Initialize all the basics components of the plugin
	}

	public static function instance() {
		if ( null === self::$instance ) {
			self::$instance = new self();
		}

		return self::$instance;
	}

	public function initialize() {
		$this->get_instance_child();
		$this->initalize_child();


	}

	public function get_instance_child() {
		$this->edumall_mobile_authenticate_instance = Edumall_Mobile_Authenticate::instance();
		$this->edumall_mobile_home_instance         = Edumall_Mobile_Home::instance();
		$this->edumall_mobile_category_instance     = Edumall_Mobile_Category::instance();
		$this->edumall_mobile_course_filter         = Edumall_Mobile_Course_Filter::instance();
		$this->edumall_mobile_course_detail         = Edumall_Mobile_Course_Detail::instance();
		$this->edumall_mobile_dash_board            = Edumall_Dashboard::instance();
	}

	public function initalize_child() {
		$this->edumall_mobile_authenticate_instance->initialize();
		$this->edumall_mobile_home_instance->initialize();
		$this->edumall_mobile_category_instance->initialize();
		$this->edumall_mobile_course_filter->initialize();
		$this->edumall_mobile_course_detail->initialize();
		$this->edumall_mobile_dash_board->initialize();
	}

	public function is_user_login() {
		return true;
	}


}
