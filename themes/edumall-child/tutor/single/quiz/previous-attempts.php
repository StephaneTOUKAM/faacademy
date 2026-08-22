<?php
$passing_grade = tutor_utils()->get_quiz_option( $quiz_id, 'passing_grade', 0 );
?>

<h4 class="tutor-quiz-attempt-history-title"><?php esc_html_e( 'Previous attempts', 'edumall' ); ?></h4>
<div class="tutor-quiz-attempt-history single-quiz-page">
	<table>
		<thead>
		<tr>
			<th>&nbsp;</th>
			<th><?php esc_html_e( 'Time', 'edumall' ); ?></th>
			<th><?php esc_html_e( 'Questions', 'edumall' ); ?></th>
			<th><?php esc_html_e( 'Total Marks', 'edumall' ); ?></th>
			<th><?php esc_html_e( 'Earned Marks', 'edumall' ); ?></th>
			<th><?php esc_html_e( 'Pass Mark', 'edumall' ); ?></th>
			<?php if ( class_exists( '\TUTOR_GB\GradeBook' ) ): ?>
				<th><?php esc_html_e( 'Grade', 'edumall' ); ?></th>
			<?php endif; ?>
			<th><?php esc_html_e( 'Result', 'edumall' ); ?></th>
			<?php do_action( 'tutor_quiz/previous_attempts/table/thead/col' ); ?>
		</tr>
		</thead>
		<tbody>
		<?php foreach ( $previous_attempts as $attempt ) : ?>
			<tr>
				<td><span class="attempt-number"><?php echo esc_html( $attempt->attempt_id ); ?></span></td>
				<td title="<?php esc_attr_e( 'Time', 'edumall' ); ?>">
					<?php
					echo wp_date( get_option( 'date_format' ), strtotime( $attempt->attempt_started_at ) ) . ' ' . wp_date( get_option( 'time_format' ), strtotime( $attempt->attempt_started_at ) );

					if ( $attempt->is_manually_reviewed ) {
						?>
						<p class="attempt-reviewed-text">
							<?php
							echo __( 'Manually reviewed at', 'edumall' ) . wp_date( get_option( 'date_format', strtotime( $attempt->manually_reviewed_at ) ) ) . ' ' . wp_date( get_option( 'time_format', strtotime( $attempt->manually_reviewed_at ) ) );
							?>
						</p>
						<?php
					}
					?>
				</td>
				<td title="<?php esc_attr_e( 'Questions', 'edumall' ); ?>">
					<span class="attempt-questions"><?php echo esc_html( $attempt->total_questions ); ?></span>
				</td>

				<td title="<?php esc_attr_e( 'Total Marks', 'edumall' ); ?>">
					<span class="attempt-total-marks"><?php echo esc_html( $attempt->total_marks ); ?></span>
				</td>

				<td title="<?php esc_attr_e( 'Earned Marks', 'edumall' ); ?>">
					<?php
					$earned_percentage = $attempt->earned_marks > 0 ? ( number_format( ( $attempt->earned_marks * 100 ) / $attempt->total_marks ) ) : 0;
					?>
					<span
						class="attempt-earned-marks"><?php echo esc_html( $attempt->earned_marks . "({$earned_percentage}%)" ); ?></span>
				</td>

				<td title="<?php esc_attr_e( 'Pass Mark', 'edumall' ); ?>">
					<span class="attempt-pass-mark">
					<?php
					if ( $passing_grade > 0 ) {
						$pass_marks = ( $attempt->total_marks * $passing_grade ) / 100;
					} else {
						$pass_marks = 0;
					}
					if ( $pass_marks > 0 ) {
						echo number_format_i18n( $pass_marks, 2 );
					} else {
						echo 0;
					}
					if ( $passing_grade > 0 ) {
						echo "({$passing_grade}%)";
					} else {
						echo "(0%)";
					}
					?>
					</span>
				</td>

				<?php if ( class_exists( '\TUTOR_GB\GradeBook' ) ): ?>
					<td>
						<span class="attempt-grade">
							<?php
							$grade = get_gradebook_by_percent( $earned_percentage );
							echo tutor_generate_grade_html( $grade );
							?>
						</span>
					</td>
				<?php endif; ?>

				<td title="<?php esc_attr_e( 'Result', 'edumall' ); ?>">
					<?php
					if ( $attempt->attempt_status === 'review_required' ) {
						echo '<span class="attempt-result attempt-result-review-required">' . esc_html__( 'Under Review', 'edumall' ) . '</span>';
					} else {
						if ( $earned_percentage >= $passing_grade ) {
							echo '<span class="attempt-result attempt-result-pass">' . esc_html__( 'Pass', 'edumall' ) . '</span>';
						} else {
							echo '<span class="attempt-result attempt-result-fail">' . esc_html__( 'Fail', 'edumall' ) . '</span>';
						}
					}
					?>
				</td>

				<?php do_action( 'tutor_quiz/previous_attempts/table/tbody/col', $attempt ); ?>
			</tr>
		<?php endforeach; ?>
		</tbody>
	</table>
</div>
