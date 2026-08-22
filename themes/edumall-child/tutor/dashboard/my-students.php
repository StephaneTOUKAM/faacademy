<?php
/**
 * Show all students of the instructor.
 *
 * @author  ThemeMove
 * @package Edumall/TutorLMS/Templates
 * @since   2.7.4
 * @version 2.7.4
 */

defined( 'ABSPATH' ) || exit;

$limit        = 20;
$current_page = max( 1, tutils()->array_get( 'current_page', $_GET ) );
$offset       = ( $current_page - 1 ) * $limit;

$my_students    = Edumall_Tutor::instance()->get_students_by_instructor( 0, $offset, $limit );
$total_students = Edumall_Tutor::instance()->get_total_students_by_instructor( get_current_user_id() );

$total_pages = ceil( $total_students / $limit );
?>
<h3><?php esc_html_e( 'My Students', 'edumall' ); ?></h3>

<?php if ( ! empty( $my_students ) ) : ?>
	<div class="dashboard-my-students-table dashboard-table-wrapper dashboard-table-responsive">
		<div class="dashboard-table-container">
			<table class="dashboard-table">
				<tr>
					<th class="col-student-info"><?php esc_html_e( 'Student', 'edumall' ); ?></th>
					<th class="col-student-actions"><?php esc_html_e( 'Profile', 'edumall' ); ?></th>
					<th class="col-student-actions"><?php esc_html_e( 'Enrolled Courses', 'edumall' ); ?></th>
					<th class="col-student-actions"><?php esc_html_e( 'Chat', 'edumall' ); ?></th>
				</tr>

				<?php foreach ( $my_students as $student ): ?>
					<?php
					$profile_url             = tutor_utils()->profile_url( $student->ID );
					$enrolled_courses_action = tutor_utils()->get_tutor_dashboard_page_permalink( 'my-students/enrolled-courses/?student_id=' . $student->ID );
					?>
					<tr>
						<td class="td-student-info">
							<div class="student-info">
								<div class="student-avatar">
									<?php echo edumall_get_avatar( $student->ID, 70 ); ?>
								</div>
							
								<h6 class="student-name"><?php echo esc_html( $student->display_name ); ?></h6>
							</div>
						</td>
						<td class="td-student-actions">
							<a href="<?php echo esc_url( $profile_url ); ?>" class="student-profile-link"><i
									class="fal fa-eye"></i><?php esc_html_e( 'Profile', 'edumall' ) ?></a>
						</td>
						<td class="td-student-actions">
							<a href="<?php echo esc_url( $enrolled_courses_action ); ?>"
							   class="student-enrolled-courses-link"><i
									class="fal fa-book-open"></i><?php esc_html_e( 'Enrolled Courses', 'edumall' ) ?>
						</td>
						
						<td class="td-student-actions">
									<span clas="button-text">
										<?php

											function displayed_user_id() {
														$limit        = 20;
														$current_page = max( 1, tutils()->array_get( 'current_page', $_GET ) );
														$offset       = ( $current_page - 1 ) * $limit;
														$my_students    = Edumall_Tutor::instance()->get_students_by_instructor( 0, $offset, $limit );
													foreach ( $my_students as $student ): 
														$student_id = $student->ID;
													endforeach;
												return (int) apply_filters( 'bp_displayed_user_id', $student_id );
											}

											function send_private_message_link() {

												if ( bp_is_my_profile() || ! is_user_logged_in() ) {
													return false;
												}

												return apply_filters( 'bp_get_send_private_message_link', wp_nonce_url( bp_loggedin_user_domain() . bp_get_messages_slug() . '/compose/?r=' . bp_core_get_username( displayed_user_id() ) ) );
											}
									
										function send_message_button( $args = '' ) {

											$r = bp_parse_args( $args, array(
												'id'                => 'private_message',
												'component'         => 'messages',
												'must_be_logged_in' => true,
												'block_self'        => true,
												'wrapper_id'        => 'send-private-message',
												'link_href'         => send_private_message_link(),
												'link_text'         => __( ' Messages', 'buddypress' ),
												'link_class'        => 'tutor-btn bordered-btn',
											) );
											return apply_filters( 'bp_get_send_message_button',
												bp_get_button( apply_filters( 'bp_get_send_message_button_args', $r ) )
											);
										}
										echo send_message_button($args = '' );
										
										?>
									</span>
						</td>
						
					</tr>
				<?php endforeach; ?>
			</table>
		</div>
	</div>

	<?php if ( $total_pages > 1 ) : ?>
		<div class="edumall-grid-pagination">
			<?php
			Edumall_Templates::render_paginate_links( [
				'format'  => '?current_page=%#%',
				'current' => $current_page,
				'total'   => $total_pages,
			] );
			?>
		</div>
	<?php endif; ?>
<?php else : ?>
	<div class="dashboard-no-content-found">
		<?php esc_html_e( 'You do not have any students yet.', 'edumall' ); ?>
	</div>
<?php endif; ?>
