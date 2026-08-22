<?php
defined( 'ABSPATH' ) || exit;

if ( ! class_exists( 'Edumall_WP_Widget_Course_Themes' ) ) {
	class Edumall_WP_Widget_Course_Themes extends Edumall_WP_Widget_Base {

		/**
		 * Category ancestors.
		 *
		 * @var array
		 */
		public $cat_ancestors;

		/**
		 * Current Theme.
		 *
		 * @var bool
		 */
		public $current_theme;

		public function __construct() {
			$this->widget_id          = 'edumall-wp-widget-course-theme';
			$this->widget_cssclass    = 'edumall-wp-widget-course-theme';
			$this->widget_name        = esc_html__( '[Edumall] Course Themes', 'edumall' );
			$this->widget_description = esc_html__( 'A list or dropdown of course themes.', 'edumall' );
			$this->settings           = array(
				'title'              => array(
					'type'  => 'text',
					'std'   => esc_html__( 'Course Themes', 'edumall' ),
					'label' => esc_html__( 'Title', 'edumall' ),
				),
				'orderby'            => array(
					'type'    => 'select',
					'std'     => 'name',
					'label'   => esc_html__( 'Order by', 'edumall' ),
					'options' => array(
						'order' => esc_html__( 'Theme order', 'edumall' ),
						'name'  => esc_html__( 'Name', 'edumall' ),
					),
				),
				'dropdown'           => array(
					'type'  => 'checkbox',
					'std'   => 0,
					'label' => esc_html__( 'Show as dropdown', 'edumall' ),
				),
				'count'              => array(
					'type'  => 'checkbox',
					'std'   => 0,
					'label' => esc_html__( 'Show course counts', 'edumall' ),
				),
				'hierarchical'       => array(
					'type'  => 'checkbox',
					'std'   => 1,
					'label' => esc_html__( 'Show hierarchy', 'edumall' ),
				),
				'show_children_only' => array(
					'type'  => 'checkbox',
					'std'   => 0,
					'label' => esc_html__( 'Only show children of the current theme', 'edumall' ),
				),
				'hide_empty'         => array(
					'type'  => 'checkbox',
					'std'   => 0,
					'label' => esc_html__( 'Hide empty themes', 'edumall' ),
				),
				'max_depth'          => array(
					'type'  => 'text',
					'std'   => '',
					'label' => esc_html__( 'Maximum depth', 'edumall' ),
					'desc'  => esc_html__( 'For eg: Input 1 to show only top themes.', 'edumall' ),
				),
			);

			parent::__construct();
		}

		public function widget( $args, $instance ) {
			global $wp_query, $post;

			$count              = $this->get_value( $instance, 'count' );
			$hierarchical       = $this->get_value( $instance, 'hierarchical' );
			$show_children_only = $this->get_value( $instance, 'show_children_only' );
			$dropdown           = $this->get_value( $instance, 'dropdown' );
			$orderby            = $this->get_value( $instance, 'orderby' );
			$hide_empty         = $this->get_value( $instance, 'hide_empty' );
			$max_depth          = absint( $this->get_value( $instance, 'max_depth' ) );
			$dropdown_args = array(
				'hide_empty' => $hide_empty,
			);
			$list_args     = array(
				'show_count'   => $count,
				'hierarchical' => $hierarchical,
				'taxonomy'     => 'course-topic',
				'hide_empty'   => $hide_empty,
			);

			$list_args['menu_order'] = false;
			$dropdown_args['depth']  = $max_depth;
			$list_args['depth']      = $max_depth;

			if ( 'order' === $orderby ) {
				$list_args['orderby']      = 'meta_value_num';
				$dropdown_args['orderby']  = 'meta_value_num';
				$list_args['meta_key']     = 'order';
				$dropdown_args['meta_key'] = 'order';
			}

			$this->current_theme   = false;
			$this->sub_ancestors = array();

			if ( is_tax( 'course-topic' ) ) {
				$this->current_theme   = $wp_query->queried_object;
				$this->sub_ancestors = get_ancestors( $this->current_theme->term_id, 'course-topic' );

			} elseif ( is_singular( 'courses' ) ) {
				$terms = wp_get_post_terms( $post->ID, 'course-topic', [
					'orderby' => 'parent',
					'order'   => 'DESC',
				] );

				if ( $terms ) {
					$main_term           = apply_filters( 'edumall_course_theme_widget_main_term', $terms[0], $terms );
					$this->current_theme   = $main_term;
					$this->sub_ancestors = get_ancestors( $main_term->term_id, 'course-topic' );
				}
			}

			// Show Siblings and Children Only.
			if ( $show_children_only && $this->current_theme ) {
				if ( $hierarchical ) {
					$include = array_merge(
						$this->sub_ancestors,
						array( $this->current_theme->term_id ),
						get_terms( [
							'taxonomy'     => 'course-topic',
							'fields'       => 'ids',
							'parent'       => 0,
							'hierarchical' => true,
							'hide_empty'   => false,
						] ),
						get_terms( [
							'taxonomy'     => 'course-topic',
							'fields'       => 'ids',
							'parent'       => $this->current_theme->term_id,
							'hierarchical' => true,
							'hide_empty'   => false,
						] )
					);
					// Gather siblings of ancestors.
					if ( $this->sub_ancestors ) {
						foreach ( $this->sub_ancestors as $ancestor ) {
							$include = array_merge(
								$include,
								get_terms( [
									'taxonomy'     => 'course-topic',
									'fields'       => 'ids',
									'parent'       => $ancestor,
									'hierarchical' => false,
									'hide_empty'   => false,
								] )
							);
						}
					}
				} else {
					// Direct children.
					$include = get_terms( [
						'taxonomy'     => 'course-topic',
						'fields'       => 'ids',
						'parent'       => $this->current_theme->term_id,
						'hierarchical' => true,
						'hide_empty'   => false,
					] );
				}

				$list_args['include']     = implode( ',', $include );
				$dropdown_args['include'] = $list_args['include'];

				if ( empty( $include ) ) {
					return;
				}
			} elseif ( $show_children_only ) {
				$dropdown_args['depth']        = 1;
				$dropdown_args['child_of']     = 0;
				$dropdown_args['hierarchical'] = 1;
				$list_args['depth']            = 1;
				$list_args['child_of']         = 0;
				$list_args['hierarchical']     = 1;
			}

			$this->widget_start( $args, $instance );
			
			if ( $dropdown ) {
				Edumall_Tutor::instance()->course_dropdown_topics( wp_parse_args(
					$dropdown_args,
					array(
						'show_count'         => $count,
						'hierarchical'       => $hierarchical,
						'show_uncategorized' => 0,
						'selected'           => $_GET['course-topic'] != null ? $_GET['course-topic'] : '',
					)
				) );

				wp_enqueue_script( 'selectWoo' );
				wp_enqueue_style( 'select2' );

				wc_enqueue_js(
					"
				jQuery( '.edumall-wp-widget-course-theme .dropdown-course-topic' ).change( function() {
					if ( jQuery(this).val() != '' ) {
						var this_page = '';
						var home_url  = '" . esc_js( tutor_utils()->course_archive_page_url() ) . "';
						if ( home_url.indexOf( '?' ) > 0 ) {
							this_page = home_url + '&course-topic=' + jQuery(this).val();
						} else {
							this_page = home_url + '?filtering=1&course-topic=' + jQuery(this).val();
						}
						location.href = this_page;
					} else {
						location.href = '" . esc_js( tutor_utils()->course_archive_page_url() ) . "';
					}
				});

				if ( jQuery().selectWoo ) {
					var tutor_course_theme_select = function() {
						jQuery( '.edumall-wp-widget-course-theme .dropdown-course-topic' ).selectWoo( {
							placeholder: '" . esc_js( __( 'Select a theme', 'edumall' ) ) . "',
							minimumResultsForSearch: 6,
							width: '90%',
							allowClear: true,
							language: {
								noResults: function() {
									return '" . esc_js( _x( 'No matches found', 'enhanced select', 'edumall' ) ) . "';
								}
							}
						} );
					};
					tutor_course_theme_select();
				}
			"
				);
			} else {
				require_once EDUMALL_FRAMEWORK_DIR . '/tutor/class-course-category-list-walker.php';

				$list_args['walker']                     = new Edumall_Course_Category_List_Walker();
				$list_args['title_li']                   = '';
				$list_args['pad_counts']                 = 1;
				$list_args['show_option_none']           = esc_html__( 'No themes exist.', 'edumall' );
				$list_args['current_theme']           = ( $this->current_theme ) ? $this->current_theme->term_id : '';
				$list_args['current_theme_ancestors'] = $this->sub_ancestors;
				$list_args['max_depth']                  = $max_depth;


				echo '<ul class="course-categories">';

				//wp_subject_cloud( apply_filters( 'edumall_course_categories_widget_args', $list_args ) );
				wp_list_categories( apply_filters( 'edumall_course_categories_widget_args', $list_args ) );

				echo '</ul>';
			}

			$this->widget_end( $args );
		}
	}
}
