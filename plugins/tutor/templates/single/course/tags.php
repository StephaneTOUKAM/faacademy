<?php
/**
 * Template for displaying course tags
 *
 * @since v.1.0.0
 *
 * @author Themeum
 * @url https://themeum.com
 *
 * @package TutorLMS/Templates
 * @version 1.4.3
 */

do_action('tutor_course/single/before/tags');

$course_tags = get_tutor_course_tags();
if(is_array($course_tags) && count($course_tags)){ ?>
    <div class="tutor-single-course-segment">
        <div class="course-benefits-title">
            <h4 class="tutor-segment-title"><?php esc_html_e('Tags', 'tutor') ?></h4>
        </div>
        <div class="tutor-course-tags">
            <?php
                foreach ($course_tags as $course_tag){
                    $tag_link = get_term_link($course_tag->term_id);
                    echo "<a href='$tag_link'> $course_tag->name </a>";
                }
            ?>
        </div>
    </div>
<?php
}

do_action('tutor_course/single/after/tags'); ?>
<?php

do_action('tutor_course/single/before/tags');

$course_tags = get_tutor_course_subjects();
if(is_array($course_subjects) && count($course_subjects)){ ?>
    <div class="tutor-single-course-segment">
        <div class="course-benefits-title">
            <h4 class="tutor-segment-title"><?php esc_html_e('Subjects', 'tutor') ?></h4>
        </div>
        <div class="tutor-course-tags">
            <?php
                foreach ($course_subjects as $course_subject){
                    $subject_link = get_term_link($course_subject->term_id);
                    echo "<a href='$subject_link'> $course_subject->name </a>";
                }
            ?>
        </div>
    </div>
<?php
}

do_action('tutor_course/single/after/tags'); ?>

<?php
do_action('tutor_course/single/before/tags');

$course_topics = get_tutor_course_topics();
if(is_array($course_topics) && count($course_topics)){ ?>
    <div class="tutor-single-course-segment">
        <div class="course-benefits-title">
            <h4 class="tutor-segment-title"><?php esc_html_e('Topics', 'tutor') ?></h4>
        </div>
        <div class="tutor-course-tags">
            <?php
                foreach ($course_topics as $course_topic){
                    $topic_link = get_term_link($course_topic->term_id);
                    echo "<a href='$topic_link'> $course_topic->name </a>";
                }
            ?>
        </div>
    </div>
<?php
}

do_action('tutor_course/single/after/tags'); ?>