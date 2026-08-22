<?php
namespace edumallmobile\framework;

use edumallmobile\utils\Edumall_Mobile_Utils;

class Edumall_Woo_Controller {

	protected static $instance = null;

	public static function instance() {
		if ( null === self::$instance ) {
			self::$instance = new self();
		}

		return self::$instance;
	}

	public function isActive(){
		$monetize_by = tutils()->get_option('monetize_by');
		if ($monetize_by !== 'wc') {
			return false;
		}
		return true;
	}

	public function get_quantity_from_cart($user_id) {
		if($this->isActive()) {
			$session_handler = new \WC_Session_Handler();
			$session         = $session_handler->get_session( $user_id );
			$cart_items      = maybe_unserialize( $session['cart'] );

			return array_sum( wp_list_pluck( $cart_items, 'quantity' ) );
		}
		return 0;
	}

}