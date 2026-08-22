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

do_action( 'tutor_course/single/before/topics' );

$course_subjects = get_tutor_course_topics();
if ( is_array( $course_subjects ) && count( $course_subjects ) ) { ?>
	<div class="tutor-course-tags-wrap">
		<div class="course-tags-title">
			<span class="tutor-segment-title-icon heading-color"><i class="fal fa-tags"></i></span>
			<h4 class="tutor-segment-title"><?php esc_html_e( 'Themes', 'edumall' ); ?></h4>
		</div>
		<div class="tutor-course-tags">
			<?php
			$separator  = esc_html( _x( ', ', 'theme separator', 'edumall' ) );
			$loop_count = 0;
			foreach ( $course_topics as $course_topic ) {
				if ( $loop_count > 0 ) {
					echo '' . $separator;
				}

				$topic_link = get_term_link( $course_topic->term_id );
				echo "<a href='$topic_link'>$course_topic->name</a>";

				$loop_count++;
			}
			?>
		</div>
	</div>
	<?php
}

do_action( 'tutor_course/single/after/topics' ); ?>
