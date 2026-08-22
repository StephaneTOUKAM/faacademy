<?php
/*
    Template Name: Examens
*/

get_header();

$args = array(
	'post_type'      => 'exam_type_manager',
	'post_status'    => 'publish',
	'orderBy'        => 'ID',
);
$examens = new WP_Query( $args );

$args_matiere = array(
	'post_type'      => 'matiere_manager',
	'post_status'    => 'publish',
	'orderBy'        => 'ID',
	'meta_query'        => array(
        'key' => 'examen_id',
        'value' => $_GET['examen'] ? $_GET['examen'] : $_GET['sous-examen']
    ),
);
$matieres = new WP_Query( $args_matiere );

$epreuves = get_post_custom($_GET['matiere'] ? $_GET['matiere'] : 1);
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
            <?php if ( $_GET['examen'] && !$_GET['matiere'] ) : ?>
                <h3 class="heading-primary elementor-heading-title"><?php esc_html_e( 'Please choose a subject', 'edumall' ); ?></h3>
                <div style="margin-top:3rem;" class="edumall-main-post edumall-grid-wrapper edumall-courses edumall-animation-zoom-in style-grid-01">
                    <div class="row container-examens">
                        <?php if ( $matieres->have_posts()) : ?>
                            <?php foreach ( $matieres->posts as $post ) : if(get_post_meta($post->ID, 'examen_id', true) == $_GET['examen']): ?>
                                <a href="?examen=<?= get_post_meta($post->ID, 'examen_id', true); ?>&matiere=<?= $post->ID; ?>" class="tri-section matiere-selected" attr-name="">
                                    <div>
                                        <h3 style="display: flex;align-items: center;">
                                            <?php if ( ICL_LANGUAGE_CODE == "fr" ) : ?>
                                                <?= $post->post_title ?>
                                            <?php elseif ( ICL_LANGUAGE_CODE == "en" ) : ?>
                                                <?= get_post_meta($post->ID, 'matiere_en', true) ?>
                                            <?php else : ?>
                                                <?= get_post_meta($post->ID, 'matiere_de', true) ?>
                                            <?php endif ?> &nbsp;
                                            <span class="fa fa-chevron-right fleche-droite"></span>
                                        </h3>
                                    </div>
                                </a>
                            <?php endif; endforeach; ?>
                        <?php else: ?>
                                <div style="display:flex;justify-content:center;width: 100%;flex-direction: column;align-items: center;">
                                    <img src="https://img.freepik.com/free-vector/no-data-concept-illustration_114360-2506.jpg?size=626&ext=jpg" alt="" style="height: 15rem;">
                                    <p><?php esc_html_e( 'No subject recorded for the moment', 'edumall' ); ?></p>
                                </div>
                        <?php endif; ?>
                    </div>
                </div>
            <?php elseif ( $_GET['examen'] && $_GET['matiere'] ) : ?>
                <div style="display: flex;width: 100%;align-items: center;margin-top:3rem;">
                    <h3 class="heading-primary elementor-heading-title"><?php esc_html_e( 'You can download', 'edumall' ); ?></h3>
                    <select name="filter_by_year" id="filter_by_year" style="width: 20%;height:10px;margin-left:5rem;">
                        <option value="---"><?php esc_html_e( 'Select a year to filter', 'edumall' ); ?></option>
                        <?php for ($nYear = date('Y'); $nYear >= 1900; $nYear--) { ?>
                            <option value="<?= $nYear; ?>"><?= $nYear; ?></option>
                        <?php } ?>
                    </select>
                </div>
                <div style="margin-top:3rem;" class="edumall-main-post edumall-grid-wrapper edumall-courses edumall-animation-zoom-in style-grid-01">
                    <div class="row container-examens">
                        <?php if ( $epreuves ) : ?>
                            <?php for ( $i = 0; $i < count(json_decode($epreuves["epreuve_annee"][0])); $i++ ) : ?>
                                <a href="<?= json_decode($epreuves['epreuve_annee'][0])[$i]->epreuve; ?>" download class="tri-section zone_to_filter" attr-name="">
                                    <div style="display: flex;flex-direction: column;">
                                        <p style="text-align: center;margin-top: 20px;">
                                            <span class="fa fa-download telecharger-button"></span>
                                        </p>
                                        <h3 style="display: flex;align-items: center;justify-content: center;text-align:center;" attr-year="<?= json_decode($epreuves['epreuve_annee'][0])[$i]->annee; ?>">
                                            <?php esc_html_e( 'Exam', 'edumall' ); ?> <?= json_decode($epreuves["epreuve_annee"][0])[$i]->annee; ?>
                                        </h3>
                                    </div>
                                </a>

                            <?php endfor; ?>
                        <?php else: ?>
                                <div style="display:flex;justify-content:center;width: 100%;flex-direction: column;align-items: center;">
                                    <img src="https://img.freepik.com/free-vector/no-data-concept-illustration_114360-2506.jpg?size=626&ext=jpg" alt="" style="height: 15rem;">
                                    <p><?php esc_html_e( 'No exam tests recorded for the moment', 'edumall' ); ?></p>
                                </div>
                        <?php endif; ?>
                    </div>
                </div>
            <?php elseif ( $_GET['sous-examen'] && $_GET['matiere-sous-examen'] ) : $donnee = 0; ?>
                <h3 class="heading-primary elementor-heading-title"><?php esc_html_e( 'Please choose a subject', 'edumall' ); ?></h3>
                <div style="margin-top:3rem;" class="edumall-main-post edumall-grid-wrapper edumall-courses edumall-animation-zoom-in style-grid-01">
                    <div class="row container-examens">
                        <?php if ( $matieres->have_posts() ) : ?>
                            <?php foreach ( $matieres->posts as $post ) : if(get_post_meta($post->ID, 'sous_exam_matiere', true) == $_GET['matiere-sous-examen']): ?>
                                <a href="?examen=<?= get_post_meta($post->ID, 'examen_id', true); ?>&matiere=<?= $post->ID; ?>" class="tri-section matiere-selected" attr-name="">
                                    <div>
                                        <h3 style="display: flex;align-items: center;">
                                            <?= $post->post_title; ?> &nbsp;
                                            <span class="fa fa-chevron-right fleche-droite"></span>
                                        </h3>
                                    </div>
                                </a>
                            <?php else: $donne++; ?>
                            <?php endif; endforeach; ?>
                        <?php else: ?>
                                <div style="display:flex;justify-content:center;width: 100%;flex-direction: column;align-items: center;">
                                    <img src="https://img.freepik.com/free-vector/no-data-concept-illustration_114360-2506.jpg?size=626&ext=jpg" alt="" style="height: 15rem;">
                                    <p><?php esc_html_e( 'No subject recorded for the moment', 'edumall' ); ?></p>
                                </div>
                        <?php endif; ?>
                        <?php if ( $donnee == 0 ) : ?>
                                <div style="display:flex;justify-content:center;width: 100%;flex-direction: column;align-items: center;">
                                    <img src="https://img.freepik.com/free-vector/no-data-concept-illustration_114360-2506.jpg?size=626&ext=jpg" alt="" style="height: 15rem;">
                                    <p><?php esc_html_e( 'No subject recorded for the moment', 'edumall' ); ?></p>
                                </div>
                        <?php endif; ?>
                    </div>
                </div>
            <?php elseif ( $_GET['sous-examen'] ) : ?>
                <h3 class="heading-primary elementor-heading-title"><?php esc_html_e( 'Please choose a sub exam', 'edumall' ); ?></h3>
                <div style="margin-top:3rem;" class="edumall-main-post edumall-grid-wrapper edumall-courses edumall-animation-zoom-in style-grid-01">
                    <div class="row container-examens">
                        <?php if ( $examens->have_posts() ) : ?>
                            <?php foreach ( $examens->posts as $post ) : if($post->ID == $_GET['sous-examen']): for ( $i = 0; $i < count(json_decode(get_post_meta($post->ID, 'sous_type_examen_name', true))); $i++ ): ?>
                                <a href="?sous-examen=<?= $_GET['sous-examen']; ?>&matiere-sous-examen=<?= json_decode(get_post_meta($post->ID, 'sous_type_examen_name_slug', true))[$i] ?>" class="tri-section">
                                    <div>
                                        <h3 style="display: flex;align-items: center;">
                                            <?= json_decode(get_post_meta($post->ID, 'sous_type_examen_name', true))[$i] ?> &nbsp;
                                            <span class="fa fa-chevron-right fleche-droite"></span>
                                        </h3>
                                    </div>
                                </a>
                            <?php endfor; endif; endforeach; ?>
                        <?php else: ?>
                                <div style="display:flex;justify-content:center;width: 100%;flex-direction: column;align-items: center;">
                                    <img src="https://img.freepik.com/free-vector/no-data-concept-illustration_114360-2506.jpg?size=626&ext=jpg" alt="" style="height: 15rem;">
                                    <p><?php esc_html_e( 'No sub-exam recorded for the moment', 'edumall' ); ?></p>
                                </div>
                        <?php endif; ?>
                    </div>
                </div>
            <?php else: ?>
                <div style="display: flex;width: 100%;align-items: center;margin-top:3rem;">
                    <h3 class="heading-primary elementor-heading-title"><?php esc_html_e( 'Select a country to see exams', 'edumall' ); ?></h3>
                    <select name="filter_by_country" id="filter_by_country" style="width: 20%;height:10px;margin-left:2rem;background: #1470b6 url('data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAAAoAAAAFCAYAAAELY03+AAAABGdBTUEAALGPC/xhBQAAAFFJREFUCB1tTsERwCAIC9MyhnQMprUJPTw9mweGmACWmQFggGSSQCT02lLUCaayAuTubrcNeNr5TaRAZyhd6A1q2hnkQ8IPKl3G/tyPoHaMfwHyzSNHeemKDAAAAABJRU5ErkJggg==') no-repeat center right 20px;color: white;">
                        <option value="---"><?php esc_html_e( 'Select a country to filter', 'edumall' ); ?></option>
                        <?php 
                            $countries = get_posts(array('post_type' => 'pays_manager', 'suppress_filters' => 0));
                            // $args = array(
                            //     'post_type'      => 'pays_manager',
                            //     'post_status'    => 'publish',
                            //     'orderBy'        => 'ID',
                            // );
                            // $countries = new WP_Query( $args );
                            foreach ($countries as $key => $country) { ?>
                                <option value="<?= $country->guid; ?>"><?= $country->post_title; ?></option>
                        <?php } ?>
                    </select>
                </div>
            <?php endif; ?>
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
<script src="https://faacademy.de/wp-content/plugins/tutor/assets/js/vanilla-toast-main/lib/vanilla-toast.min.js"></script>
<script>
    var lat, long;
    jQuery('#filter_by_year').on('change', function(e) {
        var selected_year = jQuery(this).find(':selected').val();
        // jQuery(".zone_to_filter").fadeIn();
        console.log("annee selectionnee", selected_year);
        var empty = 0;
        jQuery( ".zone_to_filter" ).each(function() {
            var text_to_filter = jQuery(this).find("h3").attr("attr-year");
            console.log("annee a filtrer", text_to_filter);
            if (text_to_filter.trim().includes(selected_year.trim())) {
                empty = empty + 1;
                jQuery(this).fadeIn();
            }else if ("---" == selected_year.trim()) {
                empty = empty + 1;
                jQuery(".zone_to_filter").fadeIn();
            } else {
                jQuery(this).fadeOut();
            }
        });
        console.log(empty);
        if (empty == 0) {
            jQuery('.empty-div').fadeIn();
        }else{
            console.log("empty vrai", empty);
            jQuery('.empty-div').fadeOut();
        }
    })
    jQuery('#filter_by_country').on('change', function(e) {
        var selected_country = jQuery(this).find(':selected').val();
        window.location.href = selected_country;
    })
    jQuery(document).on('click', '.epreuve-selected', function(e) {
        <?php if ( !is_user_logged_in() ) : ?>
            e.preventDefault();
            vt.error("<?php esc_html_e( 'To download an exam paper you must be registered on our platform.', 'edumall' ); ?>",{
                title: "<?php esc_html_e( 'Error', 'edumall' ); ?> !",
                position: "top-center",
                duration: 5000,
                closable: true,
                focusable: true,
                callback: undefined
            });
        <?php endif; ?>
    })
</script>