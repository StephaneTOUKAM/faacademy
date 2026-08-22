<?php
defined( 'ABSPATH' ) || exit;
require( get_stylesheet_directory(). '/widgets/tutor/class-course-layered-nav-base.php' ); 

if ( ! class_exists( 'Edumall_WP_Widget_Popular_Courses' ) ) {
	class Edumall_WP_Widget_Popular_Courses  extends Edumall_Course_Layered_Nav_Base {


		public function __construct() {
			$this->widget_id          = 'edumall-wp-widget-courses-popular';
			$this->widget_cssclass    = 'edumall-wp-widget-courses';
			$this->widget_name        = esc_html__( ' Popular Courses', 'edumall' );
			$this->widget_description = esc_html__( 'A list of popular courses.', 'edumall' );
			$this->settings           = array(
				'title'              => array(
					'type'  => 'text',
					'std'   => esc_html__( 'Popular Courses', 'edumall' ),
					'label' => esc_html__( 'Title', 'edumall' ),
				),
				'num'             => array(
					'type'  => 'number',
					'step'  => 1,
					'min'   => 1,
					'max'   => 40,
					'std'   => 5,
					'label' => esc_html__( 'Number', 'edumall' ),
				),
				'show_thumbnail'  => array(
					'type'  => 'checkbox',
					'std'   => 1,
					'label' => esc_html__( 'Show Thumbnail', 'edumall' ),
				),
				'show_price'      => array(
					'type'  => 'checkbox',
					'std'   => 1,
					'label' => esc_html__( 'Show Price', 'edumall' ),
				),
				'show_categories' => array(
					'type'  => 'checkbox',
					'std'   => 0,
					'label' => esc_html__( 'Show Categories', 'edumall' ),
				),
				'show_badge'      => array(
					'type'  => 'checkbox',
					'std'   => 0,
					'label' => esc_html__( 'Show Badge', 'edumall' ),
				),
			);
	
			parent::__construct();
		}
			
		function track_custom_post_watch ($post_ID) {
				//you can use is_single here, to track all your posts. Here, we're traking custom post 'watch'
			if ( !is_singular( 'watch') ) return; 
		
			if ( empty ( $post_ID) ) {
		
					//gets the global post
					global $post; 
		
					//extracts the ID
					$post_ID = $post->ID;    
			}
		
			
		}
			//adds the tracker to wp_head.
		


		public function widget( $args, $instance ) {
			
			$num             = $this->get_value( $instance, 'num' );
			$show_thumbnail  = $this->get_value( $instance, 'show_thumbnail' );
			$show_price      = $this->get_value( $instance, 'show_price' );
			$show_categories = $this->get_value( $instance, 'show_categories' );
			$show_badge      = $this->get_value( $instance, 'show_badge' );
	
			$query_args = [
					'post_type'      => 'courses',
					'posts_per_page' => $num,
					'no_found_rows'  => true,
					'post_status'    => 'publish',
					'meta_key'      => 'views', //the metakey previously defined
					'orderby'       => 'meta_value_num',
					'order'         => 'DESC',
			];
	
					// $query_args = wp_parse_args( $query_args, [
					// 	'orderby' => 'date',
					// 	'order'   => 'DESC',
					// ] );
	
			$query = new WP_Query( $query_args );
			if ( $query->have_posts() ) {
				$this->widget_start( $args, $instance );
	
				?>
				<div class="edumall-courses edumall-animation-zoom-in">
					<?php while ( $query->have_posts() ) :$query->the_post(); ?>
						<?php
							$classes = array( 'course-item edumall-box' );
						?>
						<div <?php post_class( implode( ' ', $classes ) ); ?> >
								<?php if ( $show_thumbnail ) : ?>
									<div class="course-thumbnail edumall-image">
										<?php if ( $show_badge ): ?>
											<?php
												$badge_text = Edumall_Tutor::instance()->get_course_price_badge_text();
											?>
											<?php if ( ! empty( $badge_text ) ): ?>
												<?php echo '<div class="tutor-course-badge onsale">' . $badge_text . '</div>'; ?>
											<?php endif; ?>
										<?php endif; ?>

										<a href="<?php the_permalink(); ?>">
											<?php if ( has_post_thumbnail() ) { ?>
												<?php Edumall_Image::the_post_thumbnail( array( 'size' => '120x72' ) ); ?>
												<?php
											} else {
												Edumall_Templates::image_placeholder( 120, 72 );
											}
											?>
										</a>
									</div>
								<?php endif; ?>
								<div class="course-info">

									<?php if ( $show_categories ) : ?>
										<?php Edumall_Tutor::instance()->course_loop_category(); ?>
									<?php endif; ?>

									<h5 class="course-title course-loop-title-collapse-2-rows">
										<a href="<?php the_permalink(); ?>"><?php the_title(); ?></a>
									</h5>
										
									<?php if ( $show_price ): ?>
										<?php Edumall_Tutor::instance()->course_loop_price(); ?>
									<?php endif; ?>
										
								</div>
						</div>
					<?php endwhile; ?>
				</div>
				<?php
					wp_reset_postdata();
	
					$this->widget_end( $args );
			}
		}
	}
}
	

	//add_action( 'tutor_course/single/before/content', 'track_custom_post_watch');