<?php
/**
 * Template for displaying courses
 *
 * @since   v.1.0.0
 *
 * @author  Themeum
 * @url https://themeum.com
 *
 * @package TutorLMS/Templates
 * @version 1.5.8
 */

defined( 'ABSPATH' ) || exit;

get_header();
?>
<div class="page-content">

	<?php
			/**
			 * @hook edumall_before_main_content
			 */
			do_action( 'edumall_before_main_content' );
			?>

	<div class="container">

		<div class="row">

			<?php Edumall_Sidebar::instance()->render( 'left' ); ?>
			<div class="page-main-content">
				<?php 
					$default_per_page = tutils()->get_option('courses_per_page', 6);
					$courses_per_page = (int)sanitize_text_field(tutils()->array_get('course_per_page', $_POST, $default_per_page));
					$page = (isset($_POST['page']) && is_numeric($_POST['page']) && $_POST['page']>0) ? $_POST['page'] : 1;

					if($_GET['course-instructor'] != null){
						$args = array(
							'post_status' => 'publish',
							'author'        =>  $_GET['course-instructor'],
							'post_type' => 'courses',
							'posts_per_page' => $courses_per_page,
							'paged' => $page,
							'tax_query'=> array(
								'relation' => 'AND',
							)
						);
					}else{
						$args = array(
							'post_status' => 'publish',
							'post_type' => 'courses',
							'posts_per_page' => $courses_per_page,
							'paged' => $page,
							'tax_query'=> array(
								'relation' => 'AND',
							)
						);
					}
					$terms_array = [];
					foreach(array('course-category', 'course-tag','course-subject','course-language', 'course_level') as $taxonomy) {
						$terms = $_GET['filter_'.$taxonomy];
						if($terms != null) {
							array_push($terms_array, $terms);
							$tax_query =array(
								'taxonomy' => $taxonomy,
								'field' => 'term_id',
								'terms' => $terms,
								'operator' => 'IN'
							);
							array_push($args['tax_query'], $tax_query);
						}
					}
					if(Edumall_Tutor::instance()->is_category()){
						if ( Edumall_Tutor::instance()->is_taxonomy() ) {
							$queried_object = get_queried_object();
			
							$text = $queried_object->name;
							$terms = $queried_object->term_id;
							if($terms != null) {
								array_push($terms_array, $terms);
								$tax_query =array(
									'taxonomy' => 'course-category',
									'field' => 'term_id',
									'terms' => $terms,
									'operator' => 'IN'
								);
								array_push($args['tax_query'], $tax_query);
							}
							// var_dump($queried_object);
							// die();
						}
					}
					// Prepare level and price type
					if($_GET['level'] != null){
						$level_price[] = array(
							'key'      => '_tutor_course_level',
							'value'    => $_GET['level'],
							'compare'  => 'IN'
						);
						count($level_price) ? $args['meta_query'] = $level_price : 0;
					}
					if($_GET['course-topic'] != null){
						$tax_query_topic = array(
							'taxonomy' => 'course-topic',
							'field' => 'slug',
							'terms' => $_GET['course-topic'],
						);
						array_push($args['tax_query'], $tax_query_topic);
					}

					$courses = query_posts( $args );
					if ( have_posts() ) : 
				?>

				<?php
					global $edumall_course;
					$edumall_course_clone = $edumall_course;
				?>

				<?php tutor_course_loop_start(); ?>

				<?php while ( have_posts() ) : the_post(); ?>
					<?php
						/***
						* Setup course object.
						*/
						$edumall_course_clone = new Edumall_Course();
					?>
					<?php
						/**
						* Usage Idea, you may keep a loop within a wrap, such as bootstrap col
						*
						* @hook   tutor_course/archive/before_loop_course
						*
						* @hooked tutor_course_loop_before_content
						* @see    tutor_course_loop_before_content()
						*/
						do_action( 'tutor_course/archive/before_loop_course' );
					?>

					<?php 
						tutor_load_template( 'loop.course' ); 
					?>

					<?php
						/**
						* Usage Idea, If you start any div before course loop, you can end it here, such as </div>
						*
						* @hook   tutor_course/archive/after_loop_course
						*
						* @hooked tutor_course_loop_after_content
						* @see    tutor_course_loop_after_content()
						*/
						do_action( 'tutor_course/archive/after_loop_course' );
					?>
				<?php endwhile; ?>

				<?php tutor_course_loop_end(); ?>

				<?php
					/**
					* Reset course object.
					*/
					$edumall_course = $edumall_course_clone;
				?>

				<?php //tutor_course_archive_pagination(); ?>
				<?php else : ?>
					<div class="container">
						<div class="row">
							<div class="page-main-content">
								<?php
										/**
										 * No course found
										 */
										tutor_load_template( 'course-none' );
									?>
							</div>
						</div>
					</div>
				<?php endif; ?>
			</div>

		</div>
	</div>
</div>
<?php get_footer();