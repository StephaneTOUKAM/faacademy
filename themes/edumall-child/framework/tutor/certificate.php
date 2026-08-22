<?php
defined( 'ABSPATH' ) || exit;

if ( ! class_exists( 'Edumall_Tutor_Certificate' ) ) {
	class Edumall_Tutor_Certificate {

		protected static $instance = null;

		public static function instance() {
			if ( null === self::$instance ) {
				self::$instance = new self();
			}

			return self::$instance;
		}

		public function is_certificate_page() {
			if ( isset( $_GET['cert_hash'] ) ) {
				return true;
			}

			return false;
		}

		public function initialize() {
			add_filter( 'body_class', [ $this, 'body_class' ] );

			add_filter( 'edumall_title_bar_heading_text', [ $this, 'title_bar_heading_text' ] );
		}

		public function body_class( $classes ) {
			if ( $this->is_certificate_page() ) {
				$classes [] = 'course-certificate-page';
			}

			return $classes;
		}

		public function title_bar_heading_text( $text ) {
			if ( $this->is_certificate_page() ) {
				return __( 'Certificate', 'edumall' );
			}

			return $text;
		}
	}
}
