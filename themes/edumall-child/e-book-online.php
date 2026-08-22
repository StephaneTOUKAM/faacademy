<?php
/*
    Template Name: E-Book Online
*/

get_header();

// echo do_shortcode("[products category='404']");

$subjects       = tutor_utils()->get_course_subjects();
$levels       = tutor_utils()->get_course_levels();
$image_base     = tutor()->url . '/assets/images/';
$args = array(
	'post_type'      => 'product',
	'post_status'    => 'publish',
	'orderBy'        => 'ID',
);
$rehearsal_courses = new WP_Query( $args );
$total = 0;
$total_rehearsal = 0;
$total_ebook = 0;
if ( $rehearsal_courses->have_posts() ){
	foreach ( $rehearsal_courses->posts as $post ){
		if ( strpos($post->post_excerpt, 'rehearsal-course-') !== false ){
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
</style>

<div id="rehearsal-ancre" class="row" style="padding-left: 50px;padding-right: 50px;">
    <div id="ebook-ancre" class="col-md-12" style="padding: 27px 20px 30px;">
        <h3 class="heading-primary elementor-heading-title"><?php esc_html_e( 'Our ', 'edumall' ); ?> <mark><?php esc_html_e( 'E-Books', 'edumall' ); ?></mark></h3>
        <div style="margin-top:3rem;" class="edumall-main-post edumall-grid-wrapper edumall-courses edumall-animation-zoom-in style-grid-01"
            data-grid="{&quot;type&quot;:&quot;grid&quot;,&quot;columns&quot;:4,&quot;columnsTablet&quot;:2,&quot;columnsMobile&quot;:1,&quot;gutter&quot;:30}"
            data-power-tip="{&quot;placement&quot;:&quot;e&quot;,&quot;popupClass&quot;:&quot;course-quick-view-popup&quot;}"
            data-active-columns="3">
            <div class="edumall-grid grid-lg-4 grid-md-2 grid-sm-1 loaded row"
                style="position: relative;">
                <!-- <div class="grid-sizer" style="width: 330px;"></div> -->

                <?php if ( $rehearsal_courses->have_posts() && $total > 0 && $total_ebook > 0 ) : ?>
                    <?php foreach ( $rehearsal_courses->posts as $post ) : ?>
                        <?php if ( strpos($post->post_excerpt, 'rehearsal-course') !== false && get_post_meta($post->ID, "tutor_rehearsal_type_data", true) == "E-Book" ) : ?>
                            <div class="grid-item post-<?php echo $post->ID; ?> courses type-courses status-publish has-post-thumbnail hentry course-language-french course-category-art-design course-tag-achat course-subject-nothng course-free animate col-md-3"
                                style="margin-top: 0px; width: 330px; margin-bottom: 30px; height: 555.391px;">
                                <div class="course-loop-wrapper edumall-box edumall-tooltip"
                                    data-tooltip="quick-view-course-4097-60e85d4baf800">


                                    <div class="tutor-course-header">

                                        <div class="course-thumbnail edumall-image">
                                            <a href="<?php echo $post->guid; ?>">
                                                <img src="<?php echo get_post_meta($post->ID, "tutor_rehearsal_image", true) ? get_post_meta($post->ID, "tutor_rehearsal_image", true) : 'https://myviewboard.com/blog/wp-content/uploads/2019/04/Blog-4-body-20190426-classroom-02.jpg'; ?>"
                                                    alt="couleur-du-web-2017-340x200" width="340" style="height: 300px; object-fit:cover;"> </a>
                                        </div>

                                        <div class="course-loop-badges">

                                            <div class="tutor-course-badge free"><?php echo get_post_meta($post->ID, "tutor_rehearsal_type", true); ?></div>

                                        </div>
                                    </div>

                                    <div class="course-loop-info">
                                        <h2 class="course-loop-title course-loop-title-collapse-2-rows"><a
                                                href="<?php echo $post->guid; ?>"><?php echo $post->post_title; ?></a></h2>
                                        <div class="course-loop-excerpt course-loop-excerpt-collapse-2-rows">
                                            <p><?php echo substr(get_post_meta($post->ID, "tutor_rehearsal_description", true), 0, 50) . '...'; ?></p>
                                        </div>
                                        <div class="course-loop-price">
                                            <div class="tutor-price course-paid" style="font-weight:bold;color:black;">
                                                <?php echo get_woocommerce_currency_symbol(); echo get_post_meta($post->ID, "tutor_rehearsal_price", true) == 0 ? 0 : get_post_meta($post->ID, "tutor_rehearsal_price", true); ?> </div>
                                        </div>

                                    </div>

                                </div>
                            </div>
                        <?php endif; ?>
                    <?php endforeach; ?>
                <?php else : ?>
                    <p style="text-align: center;">
                        <?php esc_html_e( 'E-Books not found', 'edumall' ); ?>
                    </p>
                <?php endif; ?>

            </div>
        </div>
    </div>
</div>

<?php
    get_footer();
?>