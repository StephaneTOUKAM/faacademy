<?php

	namespace PRAMADILLO\INTEGRATIONS;

	use Elementor\Widget_Base;
	use Elementor\Widget_Common;
	use PRAMADILLO\Woocommerce_Pay_Per_Post_Restrict_Content;
	use Woocommerce_Pay_Per_Post_Helper;
	use Woocommerce_Pay_Per_Post_Protection_Checks;
	use Woocommerce_Pay_Per_Post_Public;
	use Elementor\Element_Base;
	use Elementor\Controls_Stack;
	use Elementor\Controls_Manager;

	final class Elementor {

		const MINIMUM_ELEMENTOR_VERSION = '2.8.0';

		private static $_instance = null;
		private $products = null;
		private $has_access = false;
		private $protection_checks;

		public static function instance(): ?Elementor {

			if ( is_null( self::$_instance ) ) {
				self::$_instance = new self();
			}

			return self::$_instance;

		}

		public function __construct() {
			add_action( 'plugins_loaded', [ $this, 'init' ] );
		}

		public function init() {

			// Check if Elementor installed and activated.
			if ( ! did_action( 'elementor/loaded' ) ) {
				add_action( 'admin_notices', [ $this, 'admin_notice_missing_main_plugin' ] );

				return;
			}

			// Check for required Elementor version.
			if ( ! version_compare( ELEMENTOR_VERSION, self::MINIMUM_ELEMENTOR_VERSION, '>=' ) ) {
				add_action( 'admin_notices', [ $this, 'admin_notice_minimum_elementor_version' ] );

				return;
			}

			// Register a new Protection tab.
			Controls_Manager::add_tab( WC_PPP_SLUG . '_protection', __( 'Pay for Post', 'wc_pay_per_post' ) );

			$this->actions_and_filters();

		}


		public function actions_and_filters() {
			add_filter( 'elementor/frontend/builder_content_data', [ $this, 'elementor_restrict_content' ], 10, 2 );
			add_filter( 'elementor/frontend/section/should_render', [ $this, 'should_render' ], 10, 2 );
			add_filter( 'elementor/frontend/column/should_render', [ $this, 'should_render' ], 10, 2 );
			add_filter( 'elementor/frontend/widget/should_render', [ $this, 'should_render' ], 10, 2 );

			add_action( 'elementor/element/after_section_end', [ $this, 'controls' ], 10, 2 );
			add_action( 'elementor/frontend/after_render', [ $this, 'element' ], 10, 1 );
			add_action( 'elementor/editor/after_enqueue_styles', [ $this, 'enqueue_styles' ] );
			add_action( 'elementor/editor/after_enqueue_scripts', [ $this, 'enqueue_scripts' ] );
		}

		public function enqueue_styles() {

			wp_enqueue_style(
				WC_PPP_SLUG . '_elementor_icons',
				plugin_dir_url( __FILE__ ) . 'css/icons.css',
				[ 'elementor-editor' ],
				WC_PPP_VERSION );
		}

		public function enqueue_scripts() {
			wp_register_script(
				WC_PPP_SLUG . '_elementor_editor',
				plugin_dir_url( __FILE__ ) . 'js/editor.js',
				[ 'elementor-editor' ],
				WC_PPP_VERSION,
				true
			);

			wp_enqueue_script( WC_PPP_SLUG . '_elementor_editor' );
		}

		public function elementor_restrict_content( $data, $post_id ) {
			if ( apply_filters( 'wc_pay_per_post_force_elementor_full_page_protection', false ) ) {
				$public = new Woocommerce_Pay_Per_Post_Public();
				add_action( 'elementor/frontend/the_content', [ $public, 'restrict_content' ] );
			}

			return $data;
		}

		public function controls( Controls_Stack $element, $section_id ) {

			if ( ! $element instanceof Element_Base ) {
				return;
			}

			/**
			 * Only continue if the current section in stack is "Responsive" from the advanced tab
			 * We'll add our controls after that.
			 */
			if ( '_section_responsive' !== $section_id ) {
				return;
			}

			$products = $this->get_products();
			$options  = [];
			foreach ( $products as $product ) {
				$options[ $product['ID'] ] = __( '[' . $product['ID'] . '] ' . $product['post_title'], 'wc_pay_per_post' );
			}

			$element->start_controls_section(
			// every section/control ID needs to be unique and should not clash with any existing Elementor's or add-on's control ID
			// the ideal rule is to prefix it like functions, but avoid making them too long because all these options are loaded as a JSON string by Elementor builder during backend initialization
			// once set, changing any of the control IDs will result in the loss of access of associated setting on existing websites
				WC_PPP_SLUG . '_section',
				[
					'label' => __( 'Pay for Post (BETA V2)', 'wc_pay_per_post' ),
					'tab'   => WC_PPP_SLUG . '_protection', // add this section to our custom new tab declared above.

				]
			);

			$element->add_control(
				WC_PPP_SLUG . '_enable',
				[
					'label'        => __( 'Enable Pay for Post', 'wc_pay_per_post' ),
					'type'         => Controls_Manager::SWITCHER,
					'label_on'     => __( 'Show', 'wc_pay_per_post' ),
					'label_off'    => __( 'Hide', 'wc_pay_per_post' ),
					'return_value' => 'yes',
					'default'      => '',
					// empty string means `false`, i.e. we want it to be off by default
//					'render_type'  => 'none',
					// we don't want the editor interface to re-render all the content on every change since the settings only need to take effect on the frontend
				]
			);

			$element->add_control(
				WC_PPP_SLUG . '_select_products',
				[
					'label'       => __( 'Select Products', 'wc_pay_per_post' ),
					'type'        => Controls_Manager::SELECT2,
//					'render_type' => 'none',
					'multiple'    => true, // allow multiple products to be selected
					'label_block' => true, // full length select2 dropdown for a better experience
					'description' => __( 'This is the id of the product that is required to have been purchased before a user can view the content of this page. You can select multiple products.', 'wc_pay_per_post' ),
					'options'     => $options,
					'condition'   => [
						WC_PPP_SLUG . '_enable' => 'yes',
						// only show this control when the main "Enable Pay for Post" switcher is enabled
					],
				]
			);

			$element->add_control(
				WC_PPP_SLUG . '_should_override_message',
				[
					'label'        => __( 'Override Restricted Content Message?', 'wc_pay_per_post' ),
					'type'         => Controls_Manager::SWITCHER,
					'label_on'     => __( 'Yes', 'wc_pay_per_post' ),
					'label_off'    => __( 'No', 'wc_pay_per_post' ),
					'return_value' => 'yes',
					'default'      => '',
					'description'  => __( 'By default the Restricted Content Message is pulled from the <a href="' . get_admin_url() . 'admin.php?page=wc_pay_per_post-settings">Settings</a> page.  But you can override that on a product level by adding text below.', 'wc_pay_per_post' ),
					'separator'    => 'before', // add a divider above this to separate options
//					'render_type'  => 'none',
					'condition'    => [
						WC_PPP_SLUG . '_enable' => 'yes',
						// only show this control when the main "Enable Pay for Post" switcher is enabled
					],
				]
			);

			/**
			 * Only show the TinyMCE if the Restricted Content override switcher is enabled above
			 * to keep the options flow clean
			 */
			$element->add_control(
				WC_PPP_SLUG . '_override_message',
				[
					'label'       => __( 'Restricted Content Message', 'wc_pay_per_post' ),
					'type'        => Controls_Manager::WYSIWYG,
					'placeholder' => __( 'The token {{product_id}} will be automatically replaced...', 'wc_pay_per_post' ),
//					'render_type' => 'none',
					'description' => __( 'The token <code>{{product_id}}</code> will be automatically replaced with the product ID associated with the page the visitor is on. You can also use the token <code>{{parent_id}}</code> for when using Product Variations so you show the parent product.   You can use any WooCommerce shortcodes in the restricted content text.  Please view <a href="https://docs.woocommerce.com/document/woocommerce-shortcodes/" target="_blank">Documentation</a>. <br><br>You can also use <code>{{excerpt}}</code> which will automatically be replaced with the excerpt of the post. <br><br> If you are using WooCommerce Memberships you can use any WooCommerce Membership shortcode in the restricted content text.  Please view <a href="https://docs.woocommerce.com/document/woocommerce-memberships-restrict-content/#section-16" target="_blank">Documentation</a>.',
						'wc_pay_per_post' ),
					'condition'   => [
						WC_PPP_SLUG . '_enable'                  => 'yes',
						WC_PPP_SLUG . '_should_override_message' => 'yes',
					],
				]
			);

			/*
						// Delay Restriction
						$element->add_control(
							WC_PPP_SLUG.'_delay_restriction_enable',
							[
								'label'        => __('Enable Delay Restriction?', 'wc_pay_per_post'),
								'description'  => __('This allows you to delay the paywall from appearing for a set amount of time.', 'wc_pay_per_post'),
								'type'         => Controls_Manager::SWITCHER,
								'label_on'     => __('Yes', 'wc_pay_per_post'),
								'label_off'    => __('No', 'wc_pay_per_post'),
								'return_value' => 'yes',
								'default'      => '',
								'separator'    => 'before',
								'render_type'  => 'none',
								'condition'    => [
									WC_PPP_SLUG.'_enable' => 'yes',
								],
							]
						);

						$element->add_control(
							WC_PPP_SLUG.'_delay_restriction',
							[
								'label'       => __('Delay Restriction Number', 'wc_pay_per_post'),
								'type'        => Controls_Manager::NUMBER,
								'min'         => 1,
								'max'         => 1000,
								'step'        => 1,
								'default'     => 30,
								'render_type' => 'none',
								'condition'   => [
									WC_PPP_SLUG.'_enable'                   => 'yes',
									WC_PPP_SLUG.'_delay_restriction_enable' => 'yes',
								],
							]
						);

						$element->add_control(
							WC_PPP_SLUG.'_delay_restriction_frequency',
							[
								'label'       => __('Delay Restriction Period', 'wc_pay_per_post'),
								'type'        => Controls_Manager::SELECT,
								'default'     => 'days',
								'options'     => [
									'minute' => __('Minutes', 'wc_pay_per_post'),
									'hour'   => __('Hours', 'wc_pay_per_post'),
									'days'   => __('Days', 'wc_pay_per_post'),
									'week'   => __('Weeks', 'wc_pay_per_post'),
									'month'  => __('Months', 'wc_pay_per_post'),
									'year'   => __('Years', 'wc_pay_per_post'),
								],
								'render_type' => 'none',
								'condition'   => [
									WC_PPP_SLUG.'_enable'                   => 'yes',
									WC_PPP_SLUG.'_delay_restriction_enable' => 'yes',
								],
							]
						);


						// Page View Restriction
						$element->add_control(
							WC_PPP_SLUG.'_page_view_restriction_enable',
							[
								'label'        => __('Enable Page View Restriction?', 'wc_pay_per_post'),
								'description'  => __('This allows you to limit the number of page views the user who purchased this product has before the paywall reappears. Options to specify over a set amount of time or forever.', 'wc_pay_per_post'),
								'type'         => Controls_Manager::SWITCHER,
								'label_on'     => __('Yes', 'wc_pay_per_post'),
								'label_off'    => __('No', 'wc_pay_per_post'),
								'return_value' => 'yes',
								'default'      => '',
								'separator'    => 'before',
								'render_type'  => 'none',
								'condition'    => [
									WC_PPP_SLUG.'_enable' => 'yes',
								],
							]
						);


						$element->add_control(
							WC_PPP_SLUG.'_page_view_restriction',
							[
								'label'       => __('Page View Restriction Number', 'wc_pay_per_post'),
								'description' => __('Post can be viewed these many times before it needs to be repurchased.', 'wc_pay_per_post'),
								'type'        => Controls_Manager::NUMBER,
								'min'         => 1,
								'max'         => 1000,
								'step'        => 1,
								'default'     => 30,
								'render_type' => 'none',
								'condition'   => [
									WC_PPP_SLUG.'_enable'                       => 'yes',
									WC_PPP_SLUG.'_page_view_restriction_enable' => 'yes',
								],
							]
						);

						$element->add_control(
							WC_PPP_SLUG.'_page_view_restriction_enable_time_frame',
							[
								'label'        => __('Calculate page views over specific amount of time?', 'wc_pay_per_post'),
								'description'  => __('E.g: You only want a customer to be able to view this post no more than two times per week, otherwise they need to repurchase post.', 'wc_pay_per_post'),
								'type'         => Controls_Manager::SWITCHER,
								'label_block'  => true,
								'label_on'     => __('Yes', 'wc_pay_per_post'),
								'label_off'    => __('No', 'wc_pay_per_post'),
								'return_value' => 'yes',
								'default'      => '',
								'render_type'  => 'none',
								'condition'    => [
									WC_PPP_SLUG.'_enable'                       => 'yes',
									WC_PPP_SLUG.'_page_view_restriction_enable' => 'yes',
								],
							]
						);

						$element->add_control(
							WC_PPP_SLUG.'_page_view_restriction_time_frame',
							[
								'label'       => __('Over', 'wc_pay_per_post'),
								'type'        => Controls_Manager::NUMBER,
								'min'         => 1,
								'max'         => 1000,
								'step'        => 1,
								'default'     => 30,
								'render_type' => 'none',
								'condition'   => [
									WC_PPP_SLUG.'_enable'                                  => 'yes',
									WC_PPP_SLUG.'_page_view_restriction_enable_time_frame' => 'yes',
								],
							]
						);


						$element->add_control(
							WC_PPP_SLUG.'_page_view_restriction_frequency',
							[
								'label'       => __('Timeframe Period', 'wc_pay_per_post'),
								'type'        => Controls_Manager::SELECT,
								'default'     => 'days',
								'options'     => [
									'minute' => __('Minutes', 'wc_pay_per_post'),
									'hour'   => __('Hours', 'wc_pay_per_post'),
									'days'   => __('Days', 'wc_pay_per_post'),
									'week'   => __('Weeks', 'wc_pay_per_post'),
									'month'  => __('Months', 'wc_pay_per_post'),
									'year'   => __('Years', 'wc_pay_per_post'),
								],
								'render_type' => 'none',
								'description' => __('Since Last Purchase Date', 'wc_pay_per_post'),
								'condition'   => [
									WC_PPP_SLUG.'_enable'                                  => 'yes',
									WC_PPP_SLUG.'_page_view_restriction_enable_time_frame' => 'yes',
								],
							]
						);

						// Expiry Restriction
						$element->add_control(
							WC_PPP_SLUG.'_expire_restriction_enable',
							[
								'label'        => __('Enable Expiry Restriction?', 'wc_pay_per_post'),
								'description'  => __('This allows you to specify an expiration on this post which would require the user to repurchase the product associated with this post.', 'wc_pay_per_post'),
								'type'         => Controls_Manager::SWITCHER,
								'label_on'     => __('Yes', 'wc_pay_per_post'),
								'label_off'    => __('No', 'wc_pay_per_post'),
								'return_value' => 'yes',
								'default'      => '',
								'separator'    => 'before',
								'render_type'  => 'none',
								'condition'    => [
									WC_PPP_SLUG.'_enable' => 'yes',
								],
							]
						);

						$element->add_control(
							WC_PPP_SLUG.'_expire_restriction',
							[
								'label'       => __('Expire After', 'wc_pay_per_post'),
								'type'        => Controls_Manager::NUMBER,
								'min'         => 1,
								'max'         => 1000,
								'step'        => 1,
								'default'     => 30,
								'render_type' => 'none',
								'condition'   => [
									WC_PPP_SLUG.'_enable'                    => 'yes',
									WC_PPP_SLUG.'_expire_restriction_enable' => 'yes',
								],
							]
						);

						$element->add_control(
							WC_PPP_SLUG.'_expire_restriction_frequency',
							[
								'label'       => __('Expiry Restriction Period', 'wc_pay_per_post'),
								'type'        => Controls_Manager::SELECT,
								'default'     => 'days',
								'options'     => [
									'days'   => __('Days', 'wc_pay_per_post'),
									'weeks'  => __('Weeks', 'wc_pay_per_post'),
									'months' => __('Months', 'wc_pay_per_post'),
								],
								'render_type' => 'none',
								'condition'   => [
									WC_PPP_SLUG.'_enable'                    => 'yes',
									WC_PPP_SLUG.'_expire_restriction_enable' => 'yes',
								],
							]
						);


						// Settings from the Options tab
						$element->add_control(
							WC_PPP_SLUG.'_show_warnings',
							[
								'label'        => __('Show user remaining pageviews/time?', 'wc_pay_per_post'),
								'description'  => __('This shows to your users how much time is remaining for their purchase or how many page views they have remaining.', 'wc_pay_per_post'),
								'type'         => Controls_Manager::SWITCHER,
								'label_on'     => __('Yes', 'wc_pay_per_post'),
								'label_off'    => __('No', 'wc_pay_per_post'),
								'return_value' => 'yes',
								'default'      => '',
								'separator'    => 'before',
								'render_type'  => 'none',

								'conditions' => [
									'relation' => 'and',
									'terms'    => [
										[
											'name'     => WC_PPP_SLUG.'_enable',
											'operator' => '==',
											'value'    => 'yes',
										],
										[
											'relation' => 'or',
											'terms'    => [
												[
													'name'     => WC_PPP_SLUG.'_expire_restriction_enable',
													'operator' => '==',
													'value'    => 'yes',
												],
												[
													'name'     => WC_PPP_SLUG.'_page_view_restriction_enable',
													'operator' => '==',
													'value'    => 'yes',
												],
											],
										],
									],
								],

							]
						);
			*/
			$element->end_controls_section();

		}

		/**
		 * Determines whether Elementor's content should render on frontend or not depending on the Pay for Post Protection setting.
		 *
		 * @param bool $should_render Should Elementor's content be rendered?
		 * @param Element_Base $element The current element being rendered
		 *
		 * @return bool Should Elementor's content be rendered?
		 */
		public function should_render( bool $should_render, Element_Base $element ): bool {

			$settings = $element->get_settings_for_display();

			if ( isset( $settings[ WC_PPP_SLUG . '_enable' ] ) && 'yes' === $settings[ WC_PPP_SLUG . '_enable' ] ) {
				foreach ( (array) $settings[ WC_PPP_SLUG . '_select_products' ] as $id ) {
					if ( $this->has_access( $id ) ) {
						$this->has_access = true;
					}
				}

				if ( $this->has_access ) {
					return true;
				}

				return false;
			}

			return $should_render;
		}

		/**
		 * Injects custom output to element's frontend render
		 * Echoes HTML to be outputted in the page instead of Elementor's default content when needed
		 *
		 * @param Element_Base $element The current element being rendered
		 *
		 * @return void
		 */
		public function element( Element_Base $element ) {

			$settings        = $element->get_settings_for_display();
			$fallback_markup = null;

			// Bail if Pay for Post is not enabled.
			if ( ! isset( $settings[ WC_PPP_SLUG . '_enable' ] ) || 'yes' !== $settings[ WC_PPP_SLUG . '_enable' ] ) {
				return;
			}
			if ( ! $this->has_access ) {
				if ( isset( $settings[ WC_PPP_SLUG . '_should_override_message' ] ) && 'yes' === $settings[ WC_PPP_SLUG . '_should_override_message' ] ) {
					// Output custom Restricted Content message for this element
					$fallback_content = Woocommerce_Pay_Per_Post_Helper::replace_tokens( $settings[ WC_PPP_SLUG . '_override_message' ], $settings[ WC_PPP_SLUG . '_select_products' ] );
				} else {
					$fallback_content = Woocommerce_Pay_Per_Post_Helper::replace_tokens( get_option( WC_PPP_SLUG . '_restricted_content_default', _x( Woocommerce_Pay_Per_Post_Restrict_Content::RESTRICT_CONTENT_DEFAULT_MESSAGE, 'Default restricted content', 'wc_pay_per_post' ) ), $settings[ WC_PPP_SLUG . '_select_products' ] );
				}

				$fallback_markup = $this->fallback_markup( $fallback_content, $element->get_type() );
			}

			echo $fallback_markup;
		}

		/**
		 * Full output markup containing the default Elementor output structure
		 * todo: edit the markup as required to output whatever content the plugin is supposed to output when the content is blocked
		 *
		 * @link: https://developers.elementor.com/dom-improvements-ahead-html-wrappers-removal-from-v3-0/
		 *
		 * @param string $fallback_content The text/HTML content to be set within the markup
		 * @param string $element_type Type of the element.
		 *
		 * @return string The final markup to be outputted.
		 */
		public function fallback_markup( string $fallback_content, string $element_type ): string {

			if ( 'section' === $element_type ) {
				//section
				$markup = '
        <section class="wc-ppp-protected elementor-element elementor-section-boxed elementor-section-height-default elementor-section elementor-top-section" data-element_type="section">
            <div class="elementor-container elementor-column-gap-default">
                <div class="elementor-element elementor-column elementor-col-100 elementor-top-column" data-element_type="column">
                    <div class="elementor-widget-wrap">
                        <div class="elementor-element elementor-widget">
                            <div class="elementor-widget-container">'
				          . $fallback_content .
				          '</div>
                        </div>
                    </div>
                </div>
            </div>
        </section>';
			} elseif ( 'column' === $element_type ) {
				// column
				$markup = '
        <div class="wc-ppp-protected elementor-element elementor-column elementor-col-100 elementor-top-column" data-element_type="column">
            <div class="elementor-widget-wrap">
                <div class="elementor-element elementor-widget">
                    <div class="elementor-widget-container">'
				          . $fallback_content .
				          '</div>
                </div>
            </div>
        </div>';
			} else {
				// widget
				$markup = '
        <div class="wc-ppp-protected elementor-element elementor-widget">
            <div class="elementor-widget-container">'
				          . $fallback_content .
				          '</div>
        </div>';
			}

			return $markup;
		}

		protected function get_products() {
			if ( is_admin() ) {
				if ( is_null( $this->products ) ) {
					$this->products = Woocommerce_Pay_Per_Post_Helper::get_products();
				}

				return $this->products;
			}

			return [];
		}

		public function has_access( $id ): bool {

			$checks = [
				'check_if_logged_in',
				'check_if_admin_call',
				'check_if_purchased',
				'check_if_admin_user_have_access',
				'check_if_user_role_has_access',
			];

			if ( Woocommerce_Pay_Per_Post_Helper::can_use_woocommerce_memberships() ) {
				array_push( $checks, 'check_if_post_contains_membership_products' );
				array_push( $checks, 'check_if_is_member' );
			}
			if ( Woocommerce_Pay_Per_Post_Helper::can_use_woocommerce_subscriptions() ) {
				array_push( $checks, 'check_if_post_contains_subscription_products' );
				array_push( $checks, 'check_if_is_subscriber' );
			}

			if ( Woocommerce_Pay_Per_Post_Helper::can_use_paid_membership_pro() ) {
				array_push( $checks, 'check_if_post_contains_paid_memberships_pro_membership_products' );
				array_push( $checks, 'check_if_is_paid_memberships_pro_member' );
			}

			//Preform Checks
			$check_results = [];
			foreach ( $checks as $check ) {
				$check_results[ $check ] = Woocommerce_Pay_Per_Post_Protection_Checks::$check( $id );
			}

			if ( $check_results['check_if_admin_call'] || $check_results['check_if_admin_user_have_access'] || $check_results['check_if_user_role_has_access'] ) {
				return false;
			}

			if ( ! $check_results['check_if_purchased'] ) {
				return false;
			}


			return true;
		}


	}