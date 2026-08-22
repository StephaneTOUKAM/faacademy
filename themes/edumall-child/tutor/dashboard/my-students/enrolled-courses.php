<?php
/**
 * Show all enrolled courses of my student.
 *
 * @author  ThemeMove
 * @package Edumall/TutorLMS/Templates
 * @since   2.7.4
 * @version 2.7.4
 */

defined( 'ABSPATH' ) || exit;

$student_id = (int) sanitize_text_field( $_GET['student_id'] );
$student    = get_user_by( 'ID', $student_id );

if ( ! $student instanceof WP_User ) {
	return;
}

$enrolled_courses = Edumall_Tutor::instance()->get_enrolled_courses_by_my_student( $student->ID );
?>
<div>
	<?php $my_student_page = tutor_utils()->get_tutor_dashboard_page_permalink( 'my-students' ); ?>
	<a class="prev-btn"
	   href="<?php echo esc_url( $my_student_page ); ?>"><span>&leftarrow;</span><?php esc_html_e( 'Back to My Student List', 'edumall' ); ?>
	</a>
</div>
<h3><?php echo esc_html( sprintf( __( 'My courses get enrolled by %s', 'edumall' ), $student->display_name ) ); ?></h3>

<?php if ( ! empty( $enrolled_courses ) ) : ?>
	<div class="dashboard-my-student-courses-table dashboard-table-wrapper dashboard-table-responsive">
		<div class="dashboard-table-container">
			<table class="dashboard-table">
				<thead>
				<tr>
					<th class="col-course-info"><?php esc_html_e( 'Course', 'edumall' ); ?></th>
					<th class="col-total-lessons"><?php esc_html_e( 'Total Lessons', 'edumall' ); ?></th>
					<th class="col-completed-lessons"><?php esc_html_e( 'Completed Lessons', 'edumall' ); ?></th>
					<th class="col-completed-lessons"><?php esc_html_e( 'Chat ', 'edumall' ); ?></th>
					
				</tr>
				</thead>

				<?php
				global $post;
				?>
				<?php foreach ( $enrolled_courses as $post ): ?>
					<?php
					setup_postdata( $post );

					$total_lessons     = tutor_utils()->get_lesson_count_by_course();
					$completed_lessons = tutor_utils()->get_completed_lesson_count_by_course( 0, $student->ID );
					?>
					<tr>
						<td class="td-course-info">
							<a class="course-info" href="<?php the_permalink(); ?>">
								<div class="course-thumbnail">
									<?php Edumall_Image::the_post_thumbnail( [
										'size' => '80x80',
									] ); ?>
								</div>
								<h6 class="course-title"><?php the_title(); ?></h6>
							</a>
						</td>
						<td class="td-total-lessons">
							<div
								class="heading col-heading-mobile"><?php esc_html_e( 'Total Lessons', 'edumall' ); ?></div>
							<span><?php echo esc_html( $total_lessons ); ?></span>
						</td>
						<td class="td-completed-lessons">
							<div
								class="heading col-heading-mobile"><?php esc_html_e( 'Completed Lessons', 'edumall' ); ?></div>
							<span><?php echo esc_html( $completed_lessons ); ?></span>
						</td>
						<td class="td-student-actions">
							<span>
									<span clas="button-text">
										<?php
											if (!function_exists('test_displayed_user_id')) {
												function test_displayed_user_id() {
													
													$student_id = (int) sanitize_text_field( $_GET['student_id'] );
													return (int) apply_filters( 'bp_displayed_user_id', $student_id );
												}
											}

											if (!function_exists('test_send_private_message_link')) {
												function test_send_private_message_link() {

													if ( bp_is_my_profile() || ! is_user_logged_in() ) {
														return false;
													}

													return apply_filters( 'bp_get_send_private_message_link', wp_nonce_url( bp_loggedin_user_domain() . bp_get_messages_slug() . '/compose/?r=' . bp_core_get_username( test_displayed_user_id() ) ) );
												}
											}
									
											if (!function_exists('test_send_message_button')) {
												function test_send_message_button( $args = '' ) {

													$r = bp_parse_args( $args, array(
														'id'                => 'private_message',
														'component'         => 'messages',
														'must_be_logged_in' => true,
														'block_self'        => true,
														'wrapper_id'        => 'send-private-message',
														'link_href'         => test_send_private_message_link(),
														'link_text'         => __( ' Messages', 'buddypress' ),
														'link_class'        => 'tutor-btn bordered-btn',
													) );
													return apply_filters( 'bp_get_send_message_button',
														bp_get_button( apply_filters( 'bp_get_send_message_button_args', $r ) )
													);
												}
											}
											echo test_send_message_button($args = '' );
										
										?>
									</span>
							</span>
						</td>
						
					</tr>
				
				<?php endforeach; ?>
				<?php wp_reset_postdata(); ?>
			</table>
		</div>
	</div>
<?php else : ?>
	<div class="dashboard-no-content-found">
		<?php esc_html_e( 'This student have not get enroll any your courses yet.', 'edumall' ); ?>
	</div>
<?php endif; ?>
