<?php
/**
 * Template for displaying course tags
 *
 * @since   v.1.0.0
 *
 * @author  Themeum
 * @url https://themeum.com
 *
 * @package TutorLMS/Templates
 * @version 1.4.3
 */

defined( 'ABSPATH' ) || exit;

do_action( 'tutor_course/single/before/subjects' );

$course_subjects = get_tutor_course_subjects();
if ( is_array( $course_subjects ) && count( $course_subjects ) ) { ?>
	<div class="tutor-course-tags-wrap">
		<div class="course-tags-title">
			<span class="tutor-segment-title-icon heading-color"><i class="fal fa-tags"></i></span>
			<h4 class="tutor-segment-title"><?php esc_html_e( 'Subjects', 'edumall' ); ?></h4>
		</div>
		<div class="tutor-course-tags">
			<?php
			$separator  = esc_html( _x( ', ', 'subject separator', 'edumall' ) );
			$loop_count = 0;
			foreach ( $course_subjects as $course_subject ) {
				if ( $loop_count > 0 ) {
					echo '' . $separator;
				}

				$subject_link = get_term_link( $course_subject->term_id );
				echo "<a href='$subject_link'>$course_subject->name</a>";

				$loop_count++;
			}
			?>
		</div>
	</div>
	<?php
}

do_action( 'tutor_course/single/after/subjects' ); ?>
