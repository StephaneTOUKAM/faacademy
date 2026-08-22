<?php
/**
 * The template for displaying exam type single posts.
 *
 * @link    https://developer.wordpress.org/themes/basics/template-hierarchy/#single-post
 *
 * @package Edumall
 * @since   1.0
 */
get_header();

$args = array(
	'post_type'      => 'exam_type_manager',
	'post_status'    => 'publish',
	'orderBy'        => 'ID',
	'meta_query'        => array(
        'key' => 'pays',
        'value' => $post->post_title
    ),
);
$examens = new WP_Query( $args );
$no_exams = 0;
?>
    
  
    <style>
        .edumall-wp-widget-course-level-filter {
            background: var(--edumall-color-box-light-grey-background);
            border-radius: 5px;
            padding: 27px 20px 30px;
        }
        .container-examens{
            display: flex;
            flex-wrap: wrap;
            margin: 0px;
            width: 100%;
        }
        .tri-section{
            width: 32%;
            margin-right: 1%;
            padding: 10px 20px;
            box-shadow: 0px 0px 3px 4px #0000000f;
            border-radius: 5px;
            color: #000;
            text-decoration: none !important;
            box-sizing: border-box;
            margin-bottom: 2rem;
        }
        .fleche-droite{
            display: none;
            font-size: 15px;
        }
        .tri-section:hover{
            background: #031f42;
        }
        .tri-section:hover h3{
            color: white;
        }
        .tri-section:hover .fleche-droite{
            /*display: flex;*/
            /* transition: 2ms; */
        }
        @media (max-width: 768px) {
            #rehearsal-ancre{
                padding-left: 20px !important;
                padding-right: 20px !important;
            }
            .tri-section{
                width: 98%;
                min-width: 100%;
                box-sizing: border-box;
            }
            #rehearsal-ancre>div>div{
                display: flex !important;
                width: 100%;
                align-items: center;
                margin-top: 3rem;
                flex-direction: column !important;
                justify-content: center;
            }
            #rehearsal-ancre>div>div>select{
                width: 100% !important;
                height: 10px !important;
                margin-top: 2rem !important;
                margin-left: 0rem !important;
            }
        }
        .telecharger-button{
            background: #031f42;
            font-size: 40px;
            padding: 20px;
            border-radius: 50%;
            color: white;
        }
        .tri-section:hover .telecharger-button{
            background: #fff;
            color: #031f42;
        }
        .tri-section h3{
            display: inline !important;
            overflow-wrap: break-word;
        }
    </style>
    <div id="rehearsal-ancre" class="row" style="padding-left: 50px;padding-right: 50px;margin-bottom: 5rem;">
        <div class="col-md-12" style="padding: 27px 20px 30px;">
        <div style="margin-top:3rem;" class="edumall-main-post edumall-grid-wrapper edumall-courses edumall-animation-zoom-in style-grid-01">
            <div class="row container-examens">
                <?php if ( $examens->have_posts() ) : ?>
                    <?php foreach ( $examens->posts as $post_examen ) : if($post->post_title == get_post_meta($post_examen->ID, 'pays', true)): ?>
                        <a href="/<?= ICL_LANGUAGE_CODE; ?>/examen/?<?= get_post_meta($post_examen->ID, 'sous_type_examen', true) == 'Oui' ? 'sous-examen='.$post_examen->ID : 'examen='.$post_examen->ID ?>" class="tri-section filter-exam-by-country" attr-pays="<?= get_post_meta($post_examen->ID, 'pays', true); ?>">
                            <div>
                                <h3 style="display: flex;align-items: center;">
                                    <?= $post_examen->post_title; ?> &nbsp;
                                    <span class="fa fa-chevron-right fleche-droite"></span>
                                </h3>
                            </div>
                        </a>
                    <?php $no_exams++; endif; endforeach; ?>
                <?php else: ?>
                        <div style="display:flex;justify-content:center;width: 100%;flex-direction: column;align-items: center;">
                            <img src="https://img.freepik.com/free-vector/no-data-concept-illustration_114360-2506.jpg?size=626&ext=jpg" alt="" style="height: 15rem;">
                            <p><?php esc_html_e( 'No exam recorded for the moment', 'edumall' ); ?></p>
                        </div>
                <?php endif; ?>
                <?php if($no_exams == 0): ?>
                        <div style="display:flex;justify-content:center;width: 100%;flex-direction: column;align-items: center;">
                            <img src="https://img.freepik.com/free-vector/no-data-concept-illustration_114360-2506.jpg?size=626&ext=jpg" alt="" style="height: 15rem;">
                            <p><?php esc_html_e( 'No exam recorded for the moment', 'edumall' ); ?></p>
                        </div>
                <?php endif; ?>
            </div>
        </div>
            <div class="empty-div" style="display: none !important;width:100%;">
                <div style="display:flex;justify-content:center;">
                    <img src="https://img.freepik.com/free-vector/no-data-concept-illustration_114360-2506.jpg?size=626&ext=jpg" alt="" style="height: 15rem;">
                </div>
                <p style="text-align:center;"><?php esc_html_e( 'No exam recorded for the moment', 'edumall' ); ?></p>
            </div>
        </div>
    </div>

<?php
    get_footer();
?>
<script src="https://mapane.smartcodegroup.com/front/assets/js/vanilla-toast-main/lib/vanilla-toast.min.js"></script>
<script>
    window.onload = replaceText();
    function replaceText() {
        jQuery('.page-title-bar-01 .heading').html();
    }
</script>