<?php
/**
 * @package       TutorLMS/Templates
 * @version       1.4.3
 *
 * @theme-since   1.0.0
 * @theme-version 2.6.0
 */

defined( 'ABSPATH' ) || exit;
?>

<h3><?php esc_html_e( 'Recently Viewed Courses', 'edumall' ); ?></h3>

<div class="tutor-dashboard-content-inner">
	<div class="tutor-dashboard-inline-links">
		<ul>
			<li>
				<a href="<?php echo tutor_utils()->get_tutor_dashboard_page_permalink( 'enrolled-courses' ); ?>">
					<?php esc_html_e( 'All Courses', 'edumall' ); ?>
				</a>
			</li>
			<li>
				<a href="<?php echo tutor_utils()->get_tutor_dashboard_page_permalink( 'enrolled-courses/active-courses' ); ?>">
					<?php esc_html_e( 'Active Courses', 'edumall' ); ?>
				</a>
			</li>
			<li >
				<a href="<?php echo tutor_utils()->get_tutor_dashboard_page_permalink( 'enrolled-courses/completed-courses' ); ?>">
					<?php esc_html_e( 'Completed Courses', 'edumall' ); ?>
				</a>
			</li>
            <li class="active">
				<a href="<?php echo tutor_utils()->get_tutor_dashboard_page_permalink( 'enrolled-courses/recently-viewed-courses' ); ?>">
					<?php esc_html_e( 'Recently Viewed Courses', 'edumall' ); ?>
				</a>
			</li>
		</ul>
	</div>

	<?php
	$args = array(
		'post_type' => 'cours',
	
	);
	
	$my_query = new WP_Query( $args );
	
	
	// 3. On lance la boucle !
	while( $my_query->have_posts() ) : 
		$my_query->the_post();
		$id=get_the_ID();//id de cours
		$cours_id=get_post_meta($id,'cours_post_class',true);//id de course
		$user_id=get_post_meta($id,'user_post_class',true);
		
		if ($user_id == get_current_user_id()) :
			$query_args = array(
				'p' => $cours_id,
				'post_type' => 'courses',
				'posts_per_page' => -1,
			);
			$query = new WP_Query( $query_args );
			if( $query->have_posts() ):
				while( $query->have_posts() ) : 
					$query->the_post();
				?>
					<div class="dashboard-enrolled-courses edumall-animation-zoom-in">

						<?php $avg_rating = tutor_utils()->get_course_rating()->rating_avg;
						?>
						<a href="<?php the_permalink(); ?>"
						class="edumall-box link-secret tutor-mycourse-wrap tutor-mycourse-<?php the_ID(); ?>">
							<div class="edumall-image tutor-mycourse-thumbnail">
								<?php Edumall_Image::the_post_thumbnail( [
									'size' => '480x295',
								] ); ?>
							</div>
							<div class="tutor-mycourse-content">
								<?php Edumall_Templates::render_rating( $avg_rating, [
									'style'         => '03',
									'wrapper_class' => 'tutor-mycourse-rating',
								] ); ?>

								<h3 class="course-title"><?php the_title(); ?></h3>

								<div class="tutor-meta tutor-course-metadata">
									<?php
									$total_lessons     = tutor_utils()->get_lesson_count_by_course();
									$completed_lessons = tutor_utils()->get_completed_lesson_count_by_course();
									?>
									<ul>
										<li>
											<?php
											esc_html_e( 'Total Lessons:', 'edumall' );
											echo "<span>$total_lessons</span>";
											?>
										</li>
										<li>
											<?php
											esc_html_e( 'Completed Lessons:', 'edumall' );
											echo "<span>$completed_lessons / $total_lessons</span>";
											?>
										</li>
									</ul>
								</div>
								<?php tutor_course_completing_progress_bar(); ?>
							</div>

						</a>
					
						<?php wp_reset_postdata(); ?>
					</div>
				<?php endwhile;?>
			<?php else: ?>
				<div class="dashboard-no-content-found">
					<?php esc_html_e( 'You are not review  any courses at this moment.', 'edumall' ); ?>
				</div>
			<?php endif;
		endif;
	endwhile;
	
	
	// 4. On réinitialise à la requête principale (important)
	wp_reset_postdata();
			
				
	

			


