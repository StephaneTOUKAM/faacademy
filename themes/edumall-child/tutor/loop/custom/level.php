<?php
/**
 * Course Loop Level
 *
 * @package       Edumall/TutorLMS/Templates
 * @theme-since   2.0.0
 * @theme-version 2.0.0
 */

defined( 'ABSPATH' ) || exit;

global $edumall_course;
//var_dump($edumall_course);

$level = $edumall_course->get_level();

if ( empty( $level ) ) {
	return;
}

$wrapper_class = 'course-loop-badge-level '.$level;
$wrappers_class = 'course-loop-badge-level all_levels';
?>
<div class="<?php echo esc_attr( $wrappers_class ); ?>">
	<span class="badge-text"><?php echo esc_html( Edumall_Tutor::instance()->entry_course_levels() ); ?></span>
</div>
<div class="<?php echo esc_attr( $wrapper_class ); ?>">
	
	<span class="badge-text"><?php echo esc_html( $edumall_course->get_level_label() ); ?></span>
</div>