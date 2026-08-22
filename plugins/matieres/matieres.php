<?php

/*
  Plugin Name: Gestion des matières d'examen
  Plugin URI: https://www.smartcodegroup.com/
  Description: Permet à l'administrateur de gérer les matières d'examen
  Version: 1.0
  Author: SMARTCODE Group
  Author URI: https://www.smartcodegroup.com/
  License: Copyright 2020 Academia, All Rights Reserved
 */

define("MATIERE_MANAGER_PLUGIN_URL", plugin_dir_url(__FILE__));
define("MATIERE_MANAGER_PLUGIN_DIR", dirname(__FILE__));
define("MATIERE_MANAGER_PLUGIN_PATH", plugin_dir_path(__FILE__));

class MatiereManager{
        
    public function __construct()
    {
        add_action("init", array($this, "matiere_manager_posttype"), 0);   
        add_action("add_meta_boxes", array($this, "matiere_manager_meta_box"), 1,2);
        add_action("save_post", array($this, "matiere_manager_post_meta_save"), 1, 2);
        wp_enqueue_script( 'jquery' );
    } 
        
    public function matiere_manager_posttype(){
        $labels = array(
            'name'                => __( 'Gestion Matieres', 'Post Type General Name' ),
            'singular_name'       => __( 'Gestion Matieres', 'Post Type Singular Name' ),
            'menu_name'           => __( 'Gestion Matieres' ),
            'parent_item_colon'   => __( 'Parent Matiere_Manager' ),
            'all_items'           => __( 'Liste des matières' ),
            'view_item'           => __( 'See all matieres' ),
            'add_new_item'        => __( 'Create a new matieres' ),
            'add_new'             => __( 'New' ),
            'edit_item'           => __( 'Edit' ),
            'upcode_item'         => __( 'Update' ),
            'search_items'        => __( 'Search matieres' ),
            'not_found'           => __( 'No item found' ),
            'not_found_in_trash'  => __( 'No item found in trash' ),
        );
        $rewrite = array(
            'slug'                => 'gestion-matieres',
            'with_front'          => true,
            'pages'               => true,
            'feeds'               => true,
        );
        $args = array(
            'label'               => __( 'Matiere Manager' ),
            'rang'                => __( 'Matiere Manager' ),
            'labels'              => $labels,
            'supports'            => array( 'title', 'editor', 'author', 'thumbnail', 'excerpt', 'custom-fields', 'comments' ),
            'author'              =>  get_current_user_id(),
            'hierarchical'        => false,
            'public'              => true,
            'show_ui'             => true,
            'show_in_rest'        => true,
            'show_in_menu'        => true,
            'show_in_nav_menus'   => true,
            'show_in_admin_bar'   => true,
            'menu_position'       => 5,
            'menu_icon'           => 'dashicons-id',
            'can_export'          => true,
            'has_archive'         => true,
            'exclude_from_search' => false,
            'publicly_queryable'  => true,
            'query_var'           => 'matiere_manager',
            'rewrite'             => 'matiere_manager',

        );
       
        register_post_type( 'matiere_manager', $args );
    }
 
    public function matiere_manager_meta_box(){
        add_meta_box( 'matiere_manager_box_details', 'Ajout d\'une matière' , array($this,'matiere_manager_post_meta'), 'matiere_manager', 'normal', 'high' );

    }

    public function matiere_manager_post_meta( $post ){
        $values = get_post_custom( $post->ID );
        wp_nonce_field( 'matiere_manager_post_nonce', 'meta_box_nonce' );
        $examen_id = isset( $values['examen_id'] ) ? esc_attr( $values['examen_id'][0] ) : '';
        $matiere_en = isset( $values['matiere_en'] ) ? esc_attr( $values['matiere_en'][0] ) : '';
        $matiere_de = isset( $values['matiere_de'] ) ? esc_attr( $values['matiere_de'][0] ) : '';
        $sous_exam_matiere = isset( $values['sous_exam_matiere'] ) ? esc_attr( $values['sous_exam_matiere'][0] ) : '';
        $annee = isset( $values['annee'] ) ? json_decode($values['annee'][0]) : '';
        $epreuve = isset( $values['epreuve'] ) ? json_decode($values['epreuve'][0]) : '';
        $epreuve_annee = isset( $values['epreuve_annee'] ) ? json_decode($values['epreuve_annee'][0]) : '';
        // var_dump($epreuve_annee);
        // die();
        $args = array(
            'post_type'      => 'exam_type_manager',
            'post_status'    => 'publish',
            'orderBy'        => 'ID',
        );
        $exam_type_managers = new WP_Query( $args );
        ?>
        <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.13.0/css/all.min.css" rel="stylesheet">
        <style>
                .rowcus{
                    display:flex;
                    width:100%;
                    justify-content: space-around;
                    flex-wrap: wrap;
                }
                .rowcus:first-child{
                    margin-top: 2rem;
                }
                .rowcus>div{
                    /* width:48%; */
                    display: flex;
                    flex-direction: column;
                    /* margin-top: 1.2rem; */
                }
                .deleteAnneeEpreuve{
                    cursor: pointer;
                    color: red;
                    position: relative;
                    top: 10px;
                }
                .delete_file{
                    color: red;
                }
        </style>

        <form class="border border-light p-5 mt-5">
            <div class="rowcus">
                <div class="form-group" style="width: 50%;margin-bottom:2rem;">
                    <label>Matière en anglais: <span>*</span></label>
                    <input type="text" name="matiere_en" style="width: 96%;" value="<?= $matiere_en; ?>">
                </div>
                <div class="form-group" style="width: 50%;margin-bottom:2rem;">
                    <label>Matière en allemand: <span>*</span></label>
                    <input type="text" name="matiere_de" style="width: 96%;" value="<?= $matiere_de; ?>">
                </div>
                <div class="form-group" style="width: 100%;margin-bottom:2rem;">
                    <label>Examen: <span>*</span></label>
                    <select name="examen_id" id="examen_id">
					    <?php $selected_exams; foreach ( $exam_type_managers->posts as $post ) : 
					        if($post->ID == $examen_id)
					            $selected_exams = $post->ID;
					    ?>
                            <option attr-sous-exam="<?= get_post_meta($post->ID, 'sous_type_examen', true); ?>" value="<?= $post->ID; ?>" <?= $post->ID == $examen_id ? "selected" : "" ?>><?= $post->post_title; ?></option>
					    <?php endforeach; ?>
                    </select>
                </div>
                <div class="form-group display_sous_exam" style="width: 100%;margin-bottom:2rem;display:<?= get_post_meta($selected_exams, 'sous_type_examen', true) == "Oui" ? "flex" : "none" ?>;">
                    <label>Sous-type Examen: <span>*</span></label>
                    <select name="sous_exam_matiere" id="sous_type_examen_id">
                        <option value="---">Choisissez un sous examen</option>
					    <?php foreach ( $exam_type_managers->posts as $post ) : for ( $i = 0; $i < count(json_decode(get_post_meta($post->ID, 'sous_type_examen_name', true))); $i++ ): ?>
                            <option attr-exam-id="<?= $post->ID; ?>" value="<?= json_decode(get_post_meta($post->ID, 'sous_type_examen_name_slug', true))[$i]; ?>" <?= json_decode(get_post_meta($post->ID, 'sous_type_examen_name_slug', true))[$i] == $sous_exam_matiere ? "selected" : "" ?> style="display: <?= $post->ID == $examen_id ? "block" : "none" ?>;"><?= json_decode(get_post_meta($post->ID, 'sous_type_examen_name', true))[$i]; ?> - <?= $post->post_title; ?></option>
					    <?php endfor; endforeach; ?>
                    </select>
                </div>
                <section class="append_zone" style="width: 100%;">
                    <?php 
                        if(!empty($epreuve_annee)):
                            for ( $i = 0; $i < count($epreuve_annee); $i++ ) : 
                    ?>
                        <div class="annee_exam" style="display: flex;">
                            <div class="form-group" style="width: 45%;margin-bottom:2rem;">
                                <label>Année: <span>*</span></label>
                                <input type="text" name="annee[]" style="width: 96%;" value="<?= $epreuve_annee[$i]->annee; ?>">
                            </div>
                            <div class="form-group" style="width: 45%;margin-bottom:2rem;">
                                <label>Fichier Epreuve: <span>*</span></label>
                                <input type="file" name="epreuve[]" style="width: 96%;">
                                <div>
                                    <a href="<?= $epreuve_annee[$i]->epreuve; ?>" target="_blank">Voir fichier</a>
                                    <!-- <a href="javascript;" class="delete_file">Supprimer le fichier</a>
                                    <input type="hidden" class="hidden_file" name="epreuve[]" value="<?= $epreuve_annee[$i]->epreuve; ?>"> -->
                                </div>
                            </div>
                            <div class="form-group" style="width: 10%;margin-bottom:2rem;display: flex;align-items: center;">
                                <span class="fa fa-trash deleteAnneeEpreuve"></span>
                            </div>
                        </div>
                    <?php 
                            endfor; 
                        else : 
                    ?>
                        <div class="annee_exam" style="display: flex;">
                            <div class="form-group" style="width: 45%;margin-bottom:2rem;">
                                <label>Année: <span>*</span></label>
                                <input type="text" name="annee[]" style="width: 96%;">
                            </div>
                            <div class="form-group" style="width: 45%;margin-bottom:2rem;">
                                <label>Fichier Epreuve: <span>*</span></label>
                                <input type="file" name="epreuve[]" style="width: 96%;">
                            </div>
                            <div class="form-group" style="width: 10%;margin-bottom:2rem;display: flex;align-items: center;">
                                <span class="fa fa-trash deleteAnneeEpreuve"></span>
                            </div>
                        </div>
                    <?php 
                        endif; 
                    ?>
                </section>
                <div class="form-group" style="width: 100%;margin-bottom:2rem;display:flex;justify-content:center;flex-direction: row;">
                    <button class="btn btn-primary components-button is-primary add_annee_exam" style="width: 10%;display: flex;justify-content: center;">Ajouter</button>
                </div>
            </div>
        </form>
        <script>
            jQuery(document).on('click', '.add_annee_exam', function(e){
                e.preventDefault();
                jQuery(".append_zone").append(`
                    <div class="annee_exam" style="display: flex;">
                        <div class="form-group" style="width: 45%;margin-bottom:2rem;">
                            <label>Année: <span>*</span></label>
                            <input type="text" name="annee[]" style="width: 96%;">
                        </div>
                        <div class="form-group" style="width: 45%;margin-bottom:2rem;">
                            <label>Fichier Epreuve: <span>*</span></label>
                            <input type="file" name="epreuve[]" style="width: 96%;">
                        </div>
                        <div class="form-group" style="width: 10%;margin-bottom:2rem;display: flex;align-items: center;">
                            <span class="fa fa-trash deleteAnneeEpreuve"></span>
                        </div>
                    </div>
                `);
            })
            jQuery(document).on('click', '.deleteAnneeEpreuve', function(e){
                e.preventDefault();
                jQuery(this).parent().parent().remove();
            })
            jQuery(document).on('click', '.delete_file', function(e){
                e.preventDefault();
                jQuery(this).parent().find(".hidden_file").remove(); 
                jQuery(this).css("color",'green');
                jQuery(this).html("Fichier supprimé avec succès !");
                // jQuery(this).removeClass("delete_file");
            })
            jQuery(document).on('change', '#examen_id', function(e){
                console.log(jQuery(this).find('option:selected'));
                if(jQuery(this).find('option:selected').attr("attr-sous-exam") == "Oui"){
                    var id = jQuery(this).find(':selected').val();
                    jQuery('.display_sous_exam').fadeIn();
                    jQuery("#sous_type_examen_id").find('option').fadeOut();
                    jQuery("#sous_type_examen_id").find('option').each(function() {
                        if (jQuery(this).attr("attr-exam-id") == id) {
                            jQuery(this).fadeIn();
                        }
                    })
                }else{
                    jQuery('.display_sous_exam').fadeOut();
                }
            })
        </script>
              
        <?php   
    }

    public function matiere_manager_post_meta_save($post_id){

        // intercepte l'evenement lors de la sauvegarde
        //  if we're doing an auto save
        if( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) return;
        
        // if our nonce isn't there, or we can't verify it, bail
        if( !isset( $_POST['meta_box_nonce'] ) || !wp_verify_nonce( $_POST['meta_box_nonce'], 'matiere_manager_post_nonce' ) ) return;
        
        // now we can actually save the data
        $allowed = array( 
            'a' => array( // on allow a tags
                'href' => array() // and those anchords can only have href attribute
            )
        );

        $old_data_annee = !empty(get_post_custom( $post->ID )['annee']) ? get_post_custom( $post->ID )['annee'] : [];
        $old_data_examen = !empty(get_post_custom( $post->ID )['epreuve']) ? get_post_custom( $post->ID )['epreuve'] : [];
        $old_data_epreuve_annee = !empty(get_post_custom( $post->ID )['epreuve_annee']) ? get_post_custom( $post->ID )['epreuve_annee'] : [];

        update_post_meta($post_id, 'examen_id', $_POST['examen_id']);
        update_post_meta($post_id, 'matiere_en', $_POST['matiere_en']);
        update_post_meta($post_id, 'matiere_de', $_POST['matiere_de']);
        update_post_meta($post_id, 'sous_exam_matiere', $_POST['sous_exam_matiere']);
        $annee_json = [];
        $epreuve_json = [];
        foreach ($_POST['annee'] as $field) {
            $annee_json[] = $field;
        }
        foreach (json_decode($old_data_examen[0]) as $key => $value) {
            $epreuve_json[] = $value;
        }
        foreach (json_decode($old_data_epreuve_annee[0]) as $key => $value) {
            $epreuve_annee_json[] = $value;
        }
        var_dump($annee_json);
        var_dump($epreuve_json);
        if (count($epreuve_json) > count($annee_json)) {
            array_splice($epreuve_json, count($annee_json));
        }
        var_dump($epreuve_annee_json);
        // die();
        $length = count($_FILES['epreuve']['name']);
        $new_tab_key = [];
        for ($i=0; $i < $length; $i++) { 
            foreach ($epreuve_annee_json as $key => $value) {
                if ($value->annee == $annee_json[$i]) {
                    $epreuve_current = $value->epreuve;
                }
            }
            if ($_FILES['epreuve']['size'][$i] > 0) {
                $my_custom_filename_diplome = time() . $i . strtolower($_FILES['epreuve']['name'][$i]);
                $upload_diplome = wp_upload_bits($my_custom_filename_diplome, null, file_get_contents($_FILES['epreuve']['tmp_name'][$i]));
                // if ((count($epreuve_json)-1) < $i) {
                //     $epreuve_json[] = $upload_diplome["url"];
                    $new_tab_key[] = array('annee' => $annee_json[$i], 'epreuve' => $upload_diplome["url"], 'index' => $i);
                // } else {
                //     $epreuve_json[$i] = $upload_diplome["url"];
                // }
            }else{
                $epreuve_json[$i] = $epreuve_json[$i];
                $new_tab_key[] = array('annee' => $annee_json[$i], 'epreuve' => $epreuve_current, 'index' => $i);
            }
        }

        update_post_meta($post_id, 'annee', json_encode($annee_json));
        update_post_meta($post_id, 'epreuve', json_encode($epreuve_json));
        update_post_meta($post_id, 'epreuve_annee', json_encode($new_tab_key));
        // [
        //     "https://faacademy.de/wp-content/uploads/2021/11/16365212520fhsr-2021_pflichtteil.pdf",
        //     "https://faacademy.de/wp-content/uploads/2021/11/16365212521fhsr-2021_wahlteil.pdf",
        //     "https://faacademy.de/wp-content/uploads/2021/11/16365212522fhsr-2020_pflichtteil.pdf",
        //     "https://faacademy.de/wp-content/uploads/2021/11/16365212523fhsr-2020_wahlteil.pdf",
        //     "https://faacademy.de/wp-content/uploads/2021/11/163643380742020-teil_i_hilfsmittelfrei.pdf",
        //     "https://faacademy.de/wp-content/uploads/2021/11/163643380752020-teil_ii_mit-hilfsmittel.pdf",
        //     "https://faacademy.de/wp-content/uploads/2021/11/16364338076matheabi-2021-aufgaben.pdf"
        // ]
        // [
        //     {"annee":"2021","epreuve":"http://localhost/fa_academy/wp-content/uploads/2021/10/16348185840nouveau-document-10-21-2021-11.37.pdf","index":0},
        //     {"annee":"2020","epreuve":"http://localhost/fa_academy/wp-content/uploads/2021/10/16348185841expediteur.pdf","index":1},{"annee":"2018","epreuve":"http://localhost/fa_academy/wp-content/uploads/2021/11/1636543157216348314620admin.pdf","index":2},{"annee":"2017","epreuve":"http://localhost/fa_academy/wp-content/uploads/2021/11/16365431573cahier-des-charges-psc-v2-front-end-v2-1.pdf","index":3}
        // ]
        // [
        //     {"annee":"2021","epreuve":"http://localhost/fa_academy/wp-content/uploads/2021/10/16348185840nouveau-document-10-21-2021-11.37.pdf","index":0},
        //     {"annee":"2020","epreuve":"http://localhost/fa_academy/wp-content/uploads/2021/10/16348185841expediteur.pdf","index":1},{"annee":"2017","epreuve":"http://localhost/fa_academy/wp-content/uploads/2021/11/16365416862cahier-de-charge-transnet.pdf","index":2},
        //     {"annee":"2016","epreuve":"http://localhost/fa_academy/wp-content/uploads/2021/11/16365434663attestation-stage-ebobisse.pdf","index":3}
        // ]
    }
    
        
    public static function instance()
    {
        $instance = NULL;
          
        if(is_null($instance))
            $instance = new MatiereManager();
          
        return $instance;
    }
        
}
    
MatiereManager::instance();


?>
