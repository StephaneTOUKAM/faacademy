<?php
defined( 'ABSPATH' ) || exit;

if ( ! class_exists( 'Edumall_WP_Widget_Course_Instructor_Filter' ) ) {
	class Edumall_WP_Widget_Course_Instructor_Filter extends Edumall_Course_Layered_Nav_Base {

		public function __construct() {
			$this->widget_id          = 'edumall-wp-widget-course-instructor-filter';
			$this->widget_cssclass    = 'edumall-wp-widget-course-instructor-filter edumall-wp-widget-course-filter';
			$this->widget_name        = esc_html__( '[Edumall] Course Instructor Filter', 'edumall' );
			$this->widget_description = esc_html__( 'Shows instructors in a widget which lets you narrow down the list of courses when viewing courses.', 'edumall' );
			$this->settings           = array(
				'title'        => array(
					'type'  => 'text',
					'std'   => esc_html__( 'Filter by instructor', 'edumall' ),
					'label' => esc_html__( 'Title', 'edumall' ),
				),
				
				'items_count'  => array(
					'type'    => 'select',
					'std'     => 'on',
					'label'   => esc_html__( 'Show items count', 'edumall' ),
					'options' => array(
						'on'  => esc_html__( 'ON', 'edumall' ),
						'off' => esc_html__( 'OFF', 'edumall' ),
					),
				),
			);

			parent::__construct();
		}

		public function widget( $args, $instance ) {
			global $wp_the_query;

			if ( ! $wp_the_query->post_count ) {
				return;
			}

			if ( ! Edumall_Tutor::instance()->is_course_listing() && ! Edumall_Tutor::instance()->is_taxonomy() ) {
				return;
			}

			$this->widget_start( $args, $instance );

			$this->layered_nav_list( $instance );

			$this->widget_end( $args );
		}

		protected function layered_nav_list( $instance ) {
			$items_count  = $this->get_value( $instance, 'items_count' );
			$display_type = $this->get_value( $instance, 'display_type' );

			$class = ' filter-checkbox-list';
			$class .= ' show-display-' . $display_type;
			$class .= ' show-items-count-' . $items_count;

			$instructors = tutor_utils()->get_instructors( 0, PHP_INT_MAX, '', 'approved' );

			$filter_name    = 'instructor';
			$base_link      = Edumall_Tutor::instance()->get_course_listing_page_url();
			$base_link      = remove_query_arg( $filter_name, $base_link );
			$current_values = isset( $_GET[ $filter_name ] ) ? array_map( 'intval', explode( ',', $_GET[ $filter_name ] ) ) : array();

			wp_enqueue_script( 'selectWoo' );
			wp_enqueue_style( 'select2' );
			// List display.
			echo '
			<select class="dropdown-course-instructor">
				<option value="index">Select an instructor</option>
			';
			
			foreach ( $instructors as $instructor_key => $instructor ) {
				/**
				 * @var WP_User $instructor
				 */
				$instructor_id   = intval( $instructor->ID );
				$instructor_name = $instructor->display_name;
				$count           = $this->get_filtered_course_count( $instructor_id );

				// Only show options with count > 0.
				if ( empty( $count ) ) {
					continue;
				}

				$option_is_set = in_array( $instructor_id, $current_values, true );

				$current_filter = $current_values;

				if ( ! $option_is_set ) {
					$current_filter[] = $instructor_id;
				}

				foreach ( $current_filter as $key => $value ) {
					if ( $option_is_set && $value === $instructor_id ) {
						unset( $current_filter[ $key ] );
					}
				}

				$link = $base_link;

				if ( ! empty( $current_filter ) ) {
					$link = add_query_arg( array(
						'filtering'  => '1',
						$filter_name => implode( ',', $current_filter ),
					), $link );
				}

				$item_classes = [];

				if ( $option_is_set ) {
					$item_classes [] = 'chosen';
				}

				$count_html = '';

				if ( $items_count ) {
					$count_html = '<span class="count">(' . $count . ')</span>';
				}

				if ($_GET['course-instructor'] != null && $_GET['course-instructor'] == $instructor_id) {
					$li_html = sprintf(
						
						//'<option value="index " selected >Select a instructor</option>',
						
						'<option value="'.$instructor_id.'" class="%1$s" selected="selected"><a href="%2$s">%3$s %4$s</a></option>',
						implode( '', $item_classes ),
						esc_url( $link ),
						esc_html( $instructor_name ),
						$count_html
					);
				} else {
					$li_html = sprintf(
						
						//'<option value="index " selected >Select a instructor</option>',
						
						'<option value="'.$instructor_id.'" class="%1$s" ><a href="%2$s">%3$s %4$s</a></option>',
						implode( '', $item_classes ),
						esc_url( $link ),
						esc_html( $instructor_name ),
						$count_html
					);
				}

				echo '' . $li_html;
			}

			echo '</select>';

			wc_enqueue_js(
				"
			jQuery( '.dropdown-course-instructor' ).change( function() {
				if ( jQuery(this).val() != '' ) {
					var this_page = '';
					var home_url  = '" . esc_js( tutor_utils()->course_archive_page_url() ) . "';
					if(jQuery(this).val() == 'index'){
						this_page = home_url;
					}else{
						if ( home_url.indexOf( '?' ) > 0 ) {
							this_page = home_url + '&course-instructor=' + jQuery(this).val();
						} else {
							this_page = home_url + '?filtering=1&course-instructor=' + jQuery(this).val();
						}
					}
					location.href = this_page;
				} else {
					location.href = '" . esc_js( tutor_utils()->course_archive_page_url() ) . "';
				}
			});

			if ( jQuery().selectWoo ) {
				var tutor_course_instructor_select = function() {
					jQuery( '.dropdown-course-instructor' ).selectWoo( {
						placeholder: '" . esc_js( __( 'Select an instructor', 'edumall' ) ) . "',
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
				tutor_course_instructor_select();
			}
		"
			);
		}

		/**
		 * Count courses after other filters have occurred by adjusting the main query.
		 *
		 * @param int $current_user ID of current user.
		 *
		 * @return int
		 */
		protected function get_filtered_course_count( $current_user ) {
			global $wpdb;

			// See Edumall_Course_Layered_Nav_Base::get_filtered_term_counts() for why this is cached.
			$transient_name = 'edumall_filtered_instructor_count_' . md5( $current_user . $_SERVER['QUERY_STRING'] . edumall_wpml_current_language() );
			$cached_count   = get_transient( $transient_name );

			if ( false !== $cached_count ) {
				return absint( $cached_count );
			}

			$tax_query  = Edumall_Course_Query::instance()->get_main_tax_query();
			$meta_query = Edumall_Course_Query::instance()->get_main_meta_query();

			$author_query_sql = Edumall_Course_Query::get_author_sql( [ $current_user ] );

			$tax_query  = new WP_Tax_Query( $tax_query );
			$meta_query = new WP_Meta_Query( $meta_query );

			$tax_query_sql    = $tax_query->get_sql( $wpdb->posts, 'ID' );
			$meta_query_sql   = $meta_query->get_sql( 'post', $wpdb->posts, 'ID' );
			$search_query_sql = Edumall_Course_Query::get_search_title_sql();

			$sql = "SELECT COUNT( DISTINCT {$wpdb->posts}.ID ) FROM {$wpdb->posts} ";
			$sql .= $tax_query_sql['join'] . $meta_query_sql['join'];
			$sql .= " WHERE {$wpdb->posts}.post_type = 'courses' AND {$wpdb->posts}.post_status = 'publish' ";
			$sql .= $tax_query_sql['where'] . $meta_query_sql['where'] . $author_query_sql['where'] . $search_query_sql['where'];

			$count = absint( $wpdb->get_var( $sql ) ); // WPCS: unprepared SQL ok.

			set_transient( $transient_name, $count, 15 * MINUTE_IN_SECONDS );

			return $count;
		}
	}
}
