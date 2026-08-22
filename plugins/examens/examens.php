<?php

/*
  Plugin Name: Exam Type Manager
  Plugin URI: https://www.smartcodegroup.com/
  Description: Allow admin to manage type of official exams
  Version: 1.0
  Author: SMARTCODE Group
  Author URI: https://www.smartcodegroup.com/
  License: Copyright 2020 Academia, All Rights Reserved
 */

define("EXAM_TYPE_MANAGER_PLUGIN_URL", plugin_dir_url(__FILE__));
define("EXAM_TYPE_MANAGER_PLUGIN_DIR", dirname(__FILE__));
define("EXAM_TYPE_MANAGER_PLUGIN_PATH", plugin_dir_path(__FILE__));

class ExamTypeManager{
        
    public function __construct()
    {
        add_action("init", array($this, "exam_type_manager_posttype"), 0);   
        add_action("add_meta_boxes", array($this, "exam_type_manager_meta_box"), 1,2);
        add_action("save_post", array($this, "exam_type_manager_post_meta_save"), 1, 2);
        wp_enqueue_script( 'jquery' );
    } 
        
    public function exam_type_manager_posttype(){
        $labels = array(
            'name'                => __( 'Exam Type Manager', 'Post Type General Name' ),
            'singular_name'       => __( 'Exam Type Manager', 'Post Type Singular Name' ),
            'menu_name'           => __( 'Gestion type examen' ),
            'parent_item_colon'   => __( 'Parent Exam_Type_Manager' ),
            'all_items'           => __( 'List of Exam Types' ),
            'view_item'           => __( 'See all Exam Types' ),
            'add_new_item'        => __( 'Create a new Exam Type' ),
            'add_new'             => __( 'New' ),
            'edit_item'           => __( 'Edit' ),
            'upcode_item'         => __( 'Update' ),
            'search_items'        => __( 'Search Exam Type' ),
            'not_found'           => __( 'No item found' ),
            'not_found_in_trash'  => __( 'No item found in trash' ),
        );
        $rewrite = array(
            'slug'                => 'gestion-exam-type',
            'with_front'          => true,
            'pages'               => true,
            'feeds'               => true,
        );
        $args = array(
            'label'               => __( 'Exam Type Manager' ),
            'rang'                => __( 'Exam Type Manager' ),
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
            'query_var'           => 'exam_type_manager',
            'rewrite'             => 'exam_type_manager',

        );
       
        register_post_type( 'exam_type_manager', $args );
    }
 
    public function exam_type_manager_meta_box(){
        add_meta_box( 'exam_type_manager_box_details', 'Sélection du pays du type d\'examen' , array($this,'exam_type_manager_post_meta'), 'exam_type_manager', 'normal', 'high' );

    }

    public function exam_type_manager_post_meta( $post ){
        $values = get_post_custom( $post->ID );
        wp_nonce_field( 'exam_type_manager_post_nonce', 'meta_box_nonce' );
        $pays = isset( $values['pays'] ) ? esc_attr( $values['pays'][0] ) : '';
        $sous_type_examen_name = isset( $values['sous_type_examen_name'] ) ? json_decode( $values['sous_type_examen_name'][0] ) : '';
        $sous_type_examen = isset( $values['sous_type_examen'] ) ? esc_attr( $values['sous_type_examen'][0] ) : '';
        $args = array(
            'post_type'      => 'pays_manager',
            'post_status'    => 'publish',
            'orderBy'        => 'ID',
        );
        $countries = new WP_Query( $args );
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
                <div class="form-group" style="width: 100%;margin-bottom:2rem;">
                    <label>Pays: <span>*</span></label>
                    <select name="pays" id="pays">
					    <?php foreach ( $countries->posts as $country ) : ?>
                            <option value="<?= $country->post_title; ?>" <?= $country->post_title == $pays ? "selected" : "" ?>><?= $country->post_title; ?></option>
					    <?php endforeach; ?>
                    </select>
                </div>
                <div class="form-group" style="width: 100%;margin-bottom:2rem;">
                    <label>Y'a t-il des sous-examens ? <span>*</span></label>
                    <select name="sous_type_examen" id="sous_type_examen">
                        <option value="---">Y'a t-il des sous-examens ?</option>
                        <option value="Oui" <?= $sous_type_examen == "Oui" ? "selected" : "" ?>>Oui</option>
                        <option value="Non" <?= $sous_type_examen == "Non" ? "selected" : "" ?>>Non</option>
                    </select>
                </div>
                <section class="append_zone" style="width: 100%;display:<?= $sous_type_examen == "Oui" ? "flex" : "none" ?>;flex-direction: column;">
                    <?php 
                        if(!empty($sous_type_examen_name)):
                            for ( $i = 0; $i < count($sous_type_examen_name); $i++ ) : 
                    ?>
                        <div class="annee_exam" style="display: flex;width:100%;">
                            <div class="form-group" style="width: 90%;margin-bottom:2rem;">
                                <label>Nom du sous type examen: <span>*</span></label>
                                <input type="text" name="sous_type_examen_name[]" style="width: 100%;" value="<?= $sous_type_examen_name[$i]; ?>">
                            </div>
                            <div class="form-group" style="width: 10%;margin-bottom:2rem;display: flex;align-items: center;justify-content: center;">
                                <span class="fa fa-trash deleteAnneeEpreuve"></span>
                            </div>
                        </div>
                    <?php 
                            endfor; 
                        else : 
                    ?>
                        <div class="annee_exam" style="display: flex;">
                            <div class="form-group" style="width: 90%;margin-bottom:2rem;">
                                <label>Nom du sous type examen: <span>*</span></label>
                                <input type="text" name="sous_type_examen_name[]" style="width: 100%;">
                            </div>
                            <div class="form-group" style="width: 10%;margin-bottom:2rem;display: flex;align-items: center;justify-content: center;">
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
                        <div class="form-group" style="width: 90%;margin-bottom:2rem;">
                            <label>Nom du sous type examen: <span>*</span></label>
                            <input type="text" name="sous_type_examen_name[]" style="width: 100%;">
                        </div>
                        <div class="form-group" style="width: 10%;margin-bottom:2rem;display: flex;align-items: center;justify-content: center;">
                            <span class="fa fa-trash deleteAnneeEpreuve"></span>
                        </div>
                    </div>
                `);
            })
            jQuery(document).on('click', '.deleteAnneeEpreuve', function(e){
                e.preventDefault();
                jQuery(this).parent().parent().remove();
            })
            jQuery(document).on('change', '#sous_type_examen', function(e){
                e.preventDefault();
                if(jQuery(this).find(':selected').val() == "Oui"){
                    jQuery('.append_zone').fadeIn();
                }else{
                    jQuery('.append_zone').fadeOut();
                }
            })
        </script>
              
        <?php   
    }

    public static function slugify($text, string $divider = '-')
    {
        // replace non letter or digits by divider
        $text = preg_replace('~[^\pL\d]+~u', $divider, $text);

        // transliterate
        $text = iconv('utf-8', 'us-ascii//TRANSLIT', $text);

        // remove unwanted characters
        $text = preg_replace('~[^-\w]+~', '', $text);

        // trim
        $text = trim($text, $divider);

        // remove duplicate divider
        $text = preg_replace('~-+~', $divider, $text);

        // lowercase
        $text = strtolower($text);

        if (empty($text)) {
            return 'n-a';
        }

        return $text;
    }

    public function exam_type_manager_post_meta_save($post_id){

        // intercepte l'evenement lors de la sauvegarde
        //  if we're doing an auto save
        if( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) return;
        
        // if our nonce isn't there, or we can't verify it, bail
        if( !isset( $_POST['meta_box_nonce'] ) || !wp_verify_nonce( $_POST['meta_box_nonce'], 'exam_type_manager_post_nonce' ) ) return;
        
        // if our current user can't edit this post, bail
        // if( !current_user_can( 'edit_post' ) ) return;
        
        $sous_type_examen_name_json = [];
        $sous_type_examen_name_slug_json = [];
        foreach ($_POST['sous_type_examen_name'] as $field) {
            $sous_type_examen_name_json[] = $field;
            $sous_type_examen_name_slug_json[] = $this->slugify($field);
        }
        update_post_meta($post_id, 'sous_type_examen_name', json_encode($sous_type_examen_name_json));
        update_post_meta($post_id, 'sous_type_examen_name_slug', json_encode($sous_type_examen_name_slug_json));
        
        // now we can actually save the data
        $allowed = array( 
            'a' => array( // on allow a tags
                'href' => array() // and those anchords can only have href attribute
            )
        );
        
        // save field
        //define the table of field to save$
        $form_fields=[
            'pays',
            'sous_type_examen'
        ];
        foreach ($form_fields as $field) {
            update_post_meta($post_id, $field, wp_kses($_POST[$field], $allowed));
        }

    }
    
        
    public static function instance()
    {
        $instance = NULL;
          
        if(is_null($instance))
            $instance = new ExamTypeManager();
          
        return $instance;
    }
        
}
    
ExamTypeManager::instance();


?>