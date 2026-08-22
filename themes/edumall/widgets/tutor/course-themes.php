<?php
defined( 'ABSPATH' ) || exit;

if ( ! class_exists( 'Edumall_WP_Widget_Course_Themes' ) ) {
	class Edumall_WP_Widget_Course_Theme extends Edumall_WP_Widget_Base {

		/**
		 * Category ancestors.
		 *
		 * @var array
		 */
		public $cat_ancestors;

		/**
		 * Current Subject.
		 *
		 * @var bool
		 */
		public $current_cat;

		public function __construct() {
			$this->widget_id          = 'edumall-wp-widget-course-topic';
			$this->widget_cssclass    = 'edumall-wp-widget-course-topics';
			$this->widget_name        = esc_html__( '[Edumall] Course Themes', 'edumall' );
			$this->widget_description = esc_html__( 'A list or dropdown of course Themes.', 'edumall' );
			$this->settings           = array(
				'title'              => array(
					'type'  => 'text',
					'std'   => esc_html__( ' Course Themes', 'edumall' ),
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

			$this->current_top   = false;
			$this->top_ancestors = array();

			if ( is_tax( 'course-topic' ) ) {
				$this->current_top   = $wp_query->queried_object;
				$this->top_ancestors = get_ancestors( $this->current_top->term_id, 'course-topic' );

			} elseif ( is_singular( 'courses' ) ) {
				$terms = wp_get_post_terms( $post->ID, 'course-topic', [
					'orderby' => 'parent',
					'order'   => 'DESC',
				] );

				if ( $terms ) {
					$main_term           = apply_filters( 'edumall_course_categories_widget_main_term', $terms[0], $terms );
					$this->current_top   = $main_term;
					$this->top_ancestors = get_ancestors( $main_term->term_id, 'course-topic' );
				}
			}

			// Show Siblings and Children Only.
			if ( $show_children_only && $this->current_top ) {
				if ( $hierarchical ) {
					$include = array_merge(
						$this->top_ancestors,
						array( $this->current_top->term_id ),
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
							'parent'       => $this->current_top->term_id,
							'hierarchical' => true,
							'hide_empty'   => false,
						] )
					);
					// Gather siblings of ancestors.
					if ( $this->top_ancestors ) {
						foreach ( $this->top_ancestors as $ancestor ) {
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
						'parent'       => $this->current_top->term_id,
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
						'selected'           => $this->current_top ? $this->current_top->slug : '',
					)
				) );

				wp_enqueue_script( 'selectWoo' );
				wp_enqueue_style( 'select2' );

				wc_enqueue_js(
					"
				jQuery( '.dropdown-course-topic' ).change( function() {
					if ( jQuery(this).val() != '' ) {
						var this_page = '';
						var home_url  = '" . esc_js( home_url( '/' ) ) . "';
						if ( home_url.indexOf( '?' ) > 0 ) {
							this_page = home_url + '&course-topic=' + jQuery(this).val();
						} else {
							this_page = home_url + '?course-topic=' + jQuery(this).val();
						}
						location.href = this_page;
					} else {
						location.href = '" . esc_js( tutor_utils()->course_archive_page_url() ) . "';
					}
				});

				if ( jQuery().selectWoo ) {
					var tutor_course_topic_select = function() {
						jQuery( '.dropdown-course-topic' ).selectWoo( {
							placeholder: '" . esc_js( __( 'Select a theme', 'edumall' ) ) . "',
							minimumResultsForSearch: 5,
							width: '100%',
							allowClear: true,
							language: {
								noResults: function() {
									return '" . esc_js( _x( 'No matches found', 'enhanced select', 'edumall' ) ) . "';
								}
							}
						} );
					};
					tutor_course_topic_select();
				}
			"
				);
			} else {
				require_once EDUMALL_FRAMEWORK_DIR . '/tutor/class-course-category-list-walker.php';

				//$list_args['walker']                     = new Edumall_Course_Subject_List_Walker();
				$list_args['title_li']                   = '';
				$list_args['pad_counts']                 = 1;
				$list_args['show_option_none']           = esc_html__( 'No Topics exist.', 'edumall' );
				$list_args['current_topic']           = ( $this->current_top ) ? $this->current_top->term_id : '';
				$list_args['current_topic_ancestors'] = $this->top_ancestors;
				$list_args['max_depth']                  = $max_depth;

				echo '<ul class="course-categories">';

				wp_topic_cloud( apply_filters( 'edumall_course_categories_widget_args', $list_args ) );

				echo '</ul>';
			}

			$this->widget_end( $args );
		}
	}
}
