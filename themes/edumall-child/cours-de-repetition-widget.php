<?php
/*
    Template Name: Rehearsals courses
*/

// get_header();

// echo do_shortcode("[products category='404']");

$subjects       = tutor_utils()->get_course_subjects();
$levels       = tutor_utils()->get_course_levels();
$image_base     = tutor()->url . '/assets/images/';
$args = array(
    'post_type'      => 'product',
    'post_status'    => 'publish',
    'orderBy'        => 'ID',
);
$rehearsal_courses = new WP_Query($args);
$total = 0;
$total_rehearsal = 0;
$total_ebook = 0;
if ($rehearsal_courses->have_posts()) {
    foreach ($rehearsal_courses->posts as $post) {
        if (strpos($post->post_excerpt, 'rehearsal-course-') !== false) {
            $total = $total + 1;
        }
        if (get_post_meta($post->ID, "tutor_rehearsal_type_data", true) == "Rehearsal Course") {
            $total_rehearsal = $total_rehearsal + 1;
        }
        if (get_post_meta($post->ID, "tutor_rehearsal_type_data", true) == "E-Book") {
            $total_ebook = $total_ebook + 1;
        }
    }
}
?>
<style>
    .edumall-wp-widget-course-level-filter {
        background: var(--edumall-color-box-light-grey-background);
        border-radius: 5px;
        padding: 27px 20px 30px;
    }

    .img-course-list {
        height: 15rem !important;
        object-fit: contain;
    }
</style>

<div class="row">
    <?php if ($rehearsal_courses->have_posts() && $total > 0 && $total_rehearsal > 0) : ?>
        <div id="rehearsal-ancre" class="col-md-12 edumall-modern-heading-style-01" style="padding: 27px 20px 30px;">
            <h3 class="heading-primary elementor-heading-title"><?php esc_html_e('Our rehearsals courses', 'edumall'); ?></h3>
            <div style="margin-top:3rem;" class="edumall-main-post edumall-grid-wrapper edumall-courses edumall-animation-zoom-in style-grid-01"
                data-grid="{&quot;type&quot;:&quot;grid&quot;,&quot;columns&quot;:4,&quot;columnsTablet&quot;:2,&quot;columnsMobile&quot;:1,&quot;gutter&quot;:30}"
                data-power-tip="{&quot;placement&quot;:&quot;e&quot;,&quot;popupClass&quot;:&quot;course-quick-view-popup&quot;}"
                data-active-columns="3">
                <div class="edumall-grid grid-lg-4 grid-md-2 grid-sm-1 loaded row"
                    style="position: relative;">
                    <!-- <div class="grid-sizer" style="width: 330px;"></div> -->

                    <?php $i = 0; ?>
                    <?php foreach ($rehearsal_courses->posts as $post) : ?>
                        <?php if (strpos($post->post_excerpt, 'rehearsal-course') !== false && get_post_meta($post->ID, "tutor_rehearsal_type_data", true) == "Rehearsal Course") : $i++;
                            if ($i < 5):
                        ?>
                                <div class="grid-item post-<?php echo $post->ID; ?> courses type-courses status-publish has-post-thumbnail hentry course-language-french course-category-art-design course-tag-achat course-subject-nothng course-free animate col-md-3"
                                    style="margin-top: 0px; width: 330px; margin-bottom: 30px; height: 555.391px;">
                                    <div class="course-loop-wrapper edumall-box edumall-tooltip"
                                        data-tooltip="quick-view-course-4097-60e85d4baf800">


                                        <div class="tutor-course-header">

                                            <div class="course-thumbnail edumall-image">
                                                <a href="<?php echo $post->guid; ?>">
                                                    <img src="<?php echo get_post_meta($post->ID, "tutor_rehearsal_image", true) ? get_post_meta($post->ID, "tutor_rehearsal_image", true) : 'https://myviewboard.com/blog/wp-content/uploads/2019/04/Blog-4-body-20190426-classroom-02.jpg'; ?>"
                                                        alt="couleur-du-web-2017-340x200" width="340" class="img-course-list"> </a>
                                            </div>

                                            <div class="course-loop-badges">

                                                <div class="tutor-course-badge free"><?php echo get_post_meta($post->ID, "tutor_rehearsal_type", true); ?></div>
                                                <div class="tutor-course-badge free" style="background:#0071dc;"><?php
                                                                                                                    $languages       = tutor_utils()->get_course_languages();
                                                                                                                    $language_selected = "";
                                                                                                                    foreach ($languages as $key => $language) {
                                                                                                                        if ($language->term_id == get_post_meta($post->ID, "tutor_rehearsal_language", true)) {
                                                                                                                            $language_selected = $language->name;
                                                                                                                        }
                                                                                                                    }
                                                                                                                    echo $language_selected;
                                                                                                                    // echo get_post_meta($post->ID, "tutor_rehearsal_language", true);
                                                                                                                    ?></div>

                                            </div>
                                        </div>

                                        <div class="course-loop-info">
                                            <div class="course-loop-badge-level intermediate">
                                                <span class="badge-text"><?php
                                                                            $level_selected = "";
                                                                            $i = 0;
                                                                            foreach ($levels as $key => $level) {
                                                                                foreach (json_decode(get_post_meta($post->ID, "tutor_rehearsal_level", true)) as $keys => $item) {
                                                                                    $test = $i < count($levels) ? ", " : "";
                                                                                    if ($level->term_id == $item) {
                                                                                        $level_selected .= $level->name . $test;
                                                                                    }
                                                                                    $i++;
                                                                                }
                                                                            }
                                                                            echo $level_selected;
                                                                            ?></span>
                                            </div>
                                            <div class="course-loop-category">
                                                <a href="#"><?php
                                                            $subject_selected = "";
                                                            foreach ($subjects as $key => $subject) {
                                                                if ($subject->term_id == get_post_meta($post->ID, "tutor_rehearsal_subject", true)) {
                                                                    $subject_selected = $subject->name;
                                                                }
                                                            }
                                                            echo $subject_selected;
                                                            ?></a>
                                            </div>
                                            <h2 class="course-loop-title course-loop-title-collapse-2-rows"><a
                                                    href="<?php echo $post->guid; ?>"><?php echo $post->post_title; ?></a></h2>
                                            <div class="course-loop-excerpt course-loop-excerpt-collapse-2-rows">
                                                <p><?php echo substr(get_post_meta($post->ID, "tutor_rehearsal_description", true), 0, 50) . '...'; ?></p>
                                            </div>
                                            <div class="course-loop-price">
                                                <div class="tutor-price course-paid" style="font-weight:bold;color:black;">
                                                    <?php echo get_woocommerce_currency_symbol();
                                                    echo get_post_meta($post->ID, "tutor_rehearsal_price", true) == 0 ? 0 : get_post_meta($post->ID, "tutor_rehearsal_price", true); ?> </div>
                                            </div>

                                        </div>

                                    </div>
                                </div>
                        <?php endif;
                        endif; ?>
                    <?php endforeach; ?>

                </div>
                <div style="display: flex;justify-content: center;">
                    <button type="button" class="tutor-btn tutor-announcement-add-new" onclick="window.location.href='rehearsals-courses'">
                        <?php esc_html_e('View all', 'edumall'); ?>
                    </button>
                </div>
            </div>
        </div>
    <?php endif; ?>

    <?php if ($rehearsal_courses->have_posts() && $total > 0 && $total_ebook > 0) : ?>
        <div id="ebook-ancre" class="col-md-12 edumall-modern-heading-style-01" style="padding: 27px 20px 30px;">
            <h3 class="heading-primary elementor-heading-title"><?php esc_html_e('Our ', 'edumall'); ?> <mark><?php esc_html_e('E-Books', 'edumall'); ?></mark></h3>
            <div style="margin-top:3rem;" class="edumall-main-post edumall-grid-wrapper edumall-courses edumall-animation-zoom-in style-grid-01"
                data-grid="{&quot;type&quot;:&quot;grid&quot;,&quot;columns&quot;:4,&quot;columnsTablet&quot;:2,&quot;columnsMobile&quot;:1,&quot;gutter&quot;:30}"
                data-power-tip="{&quot;placement&quot;:&quot;e&quot;,&quot;popupClass&quot;:&quot;course-quick-view-popup&quot;}"
                data-active-columns="3">
                <div class="edumall-grid grid-lg-4 grid-md-2 grid-sm-1 loaded row"
                    style="position: relative;">
                    <!-- <div class="grid-sizer" style="width: 330px;"></div> -->

                    <?php $j = 0; ?>
                    <?php foreach ($rehearsal_courses->posts as $post) : ?>
                        <?php if (strpos($post->post_excerpt, 'rehearsal-course') !== false && get_post_meta($post->ID, "tutor_rehearsal_type_data", true) == "E-Book") :  $j++;
                            if ($j < 5):
                        ?>
                                <div class="grid-item post-<?php echo $post->ID; ?> courses type-courses status-publish has-post-thumbnail hentry course-language-french course-category-art-design course-tag-achat course-subject-nothng course-free animate col-md-3"
                                    style="margin-top: 0px; width: 330px; margin-bottom: 30px; height: 555.391px;">
                                    <div class="course-loop-wrapper edumall-box edumall-tooltip"
                                        data-tooltip="quick-view-course-4097-60e85d4baf800">


                                        <div class="tutor-course-header">

                                            <div class="course-thumbnail edumall-image">
                                                <a href="<?php echo $post->guid; ?>">
                                                    <img src="<?php echo get_post_meta($post->ID, "tutor_rehearsal_image", true) ? get_post_meta($post->ID, "tutor_rehearsal_image", true) : 'https://myviewboard.com/blog/wp-content/uploads/2019/04/Blog-4-body-20190426-classroom-02.jpg'; ?>"
                                                        alt="couleur-du-web-2017-340x200" width="340" class="img-course-list"> </a>
                                            </div>

                                            <div class="course-loop-badges">

                                                <div class="tutor-course-badge free"><?php echo get_post_meta($post->ID, "tutor_rehearsal_type", true); ?></div>

                                            </div>
                                        </div>

                                        <div class="course-loop-info">
                                            <div class="course-loop-badge-level intermediate">
                                                <span class="badge-text"><?php
                                                                            $level_selected = "";
                                                                            foreach ($levels as $key => $level) {
                                                                                if ($level->term_id == get_post_meta($post->ID, "tutor_rehearsal_level", true)) {
                                                                                    $level_selected = $level->name;
                                                                                }
                                                                            }
                                                                            echo $level_selected;
                                                                            ?></span>
                                            </div>
                                            <div class="course-loop-category">
                                                <a href="#"><?php
                                                            $subject_selected = "";
                                                            foreach ($subjects as $key => $subject) {
                                                                if ($subject->term_id == get_post_meta($post->ID, "tutor_rehearsal_subject", true)) {
                                                                    $subject_selected = $subject->name;
                                                                }
                                                            }
                                                            echo $subject_selected;
                                                            ?></a>
                                            </div>
                                            <h2 class="course-loop-title course-loop-title-collapse-2-rows"><a
                                                    href="<?php echo $post->guid; ?>"><?php echo $post->post_title; ?></a></h2>
                                            <div class="course-loop-excerpt course-loop-excerpt-collapse-2-rows">
                                                <p><?php echo substr(get_post_meta($post->ID, "tutor_rehearsal_description", true), 0, 50) . '...'; ?></p>
                                            </div>
                                            <div class="course-loop-price">
                                                <div class="tutor-price course-paid" style="font-weight:bold;color:black;">
                                                    <?php echo get_woocommerce_currency_symbol();
                                                    echo get_post_meta($post->ID, "tutor_rehearsal_price", true) == 0 ? 0 : get_post_meta($post->ID, "tutor_rehearsal_price", true); ?> </div>
                                            </div>

                                        </div>

                                    </div>
                                </div>
                        <?php endif;
                        endif; ?>
                    <?php endforeach; ?>

                </div>
                <div style="display: flex;justify-content: center;">
                    <button type="button" class="tutor-btn tutor-announcement-add-new" onclick="window.location.href='i-am-learning'">
                        <?php esc_html_e('View all', 'edumall'); ?>
                    </button>
                </div>
            </div>
        </div>
    <?php endif; ?>
</div>

<?php
// get_footer();
?>