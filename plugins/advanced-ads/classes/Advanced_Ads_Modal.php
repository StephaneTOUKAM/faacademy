<?php

/**
 * Basic Modal class to separate concerns.
 */
class Advanced_Ads_Modal {
	/**
	 * Default values for the view file.
	 *
	 * @var array
	 */
	private $view_arguments = array(
		'modal_slug'       => '',
		'modal_content'    => '',
		'modal_title'      => '',
		'close_action'     => '',
		'close_form'       => '',
		'close_validation' => '',
	);

	/**
	 * Modal constructor.
	 *
	 * @param array $arguments The passed view arguments, overwriting the default values.
	 * @param bool  $render    Whether to render the modal from the constructor. Defaults to true.
	 */
	public function __construct( array $arguments, $render = true ) {
		$this->view_arguments = array_intersect_key( wp_parse_args( array_map( 'strval', $arguments ), $this->view_arguments ), $this->view_arguments );

		if ( $render ) {
			$this->render();
		}
	}

	/**
	 * Render the modal.
	 *
	 * @return void
	 */
	public function render() {
		// phpcs:ignore WordPress.PHP.DontExtract.extract_extract -- we have ensured, the array only contains the variables defined by the defaults. Variables are documented and escaped in the view file.
		extract( $this->view_arguments, EXTR_OVERWRITE );

		require ADVADS_BASE_PATH . 'admin/views/modal.php';
	}
}
