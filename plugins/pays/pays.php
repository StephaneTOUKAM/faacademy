<?php

/*
  Plugin Name: Gestion des pays d'examen
  Plugin URI: https://www.smartcodegroup.com/
  Description: Permet à l'administrateur de gérer les matières d'examen
  Version: 1.0
  Author: SMARTCODE Group
  Author URI: https://www.smartcodegroup.com/
  License: Copyright 2020 Academia, All Rights Reserved
 */

define("PAYS_MANAGER_PLUGIN_URL", plugin_dir_url(__FILE__));
define("PAYS_MANAGER_PLUGIN_DIR", dirname(__FILE__));
define("PAYS_MANAGER_PLUGIN_PATH", plugin_dir_path(__FILE__));

class PaysManager{
        
    public function __construct()
    {
        add_action("init", array($this, "pays_manager_posttype"), 0);   
        add_action("add_meta_boxes", array($this, "pays_manager_meta_box"), 1,2);
        add_action("save_post", array($this, "pays_manager_post_meta_save"), 1, 2);
        wp_enqueue_script( 'jquery' );
    } 
        
    public function pays_manager_posttype(){
        $labels = array(
            'name'                => __( 'Gestion Pays', 'Post Type General Name' ),
            'singular_name'       => __( 'Gestion Pays', 'Post Type Singular Name' ),
            'menu_name'           => __( 'Gestion Pays' ),
            'parent_item_colon'   => __( 'Parent Pays_Manager' ),
            'all_items'           => __( 'Liste des pays' ),
            'view_item'           => __( 'See all pays' ),
            'add_new_item'        => __( 'Create a new pays' ),
            'add_new'             => __( 'New' ),
            'edit_item'           => __( 'Edit' ),
            'upcode_item'         => __( 'Update' ),
            'search_items'        => __( 'Search pays' ),
            'not_found'           => __( 'No item found' ),
            'not_found_in_trash'  => __( 'No item found in trash' ),
        );
        $rewrite = array(
            'slug'                => 'gestion-pays',
            'with_front'          => true,
            'pages'               => true,
            'feeds'               => true,
        );
        $args = array(
            'label'               => __( 'Pays Manager' ),
            'rang'                => __( 'Pays Manager' ),
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
            'query_var'           => 'pays_manager',
            'rewrite'             => 'pays_manager',

        );
       
        register_post_type( 'pays_manager', $args );
    }
 
    public function pays_manager_meta_box(){
        add_meta_box( 'pays_manager_box_details', 'Ajout d\'un pays' , array($this,'pays_manager_post_meta'), 'pays_manager', 'normal', 'high' );

    }

    public function pays_manager_post_meta( $post ){
        $values = get_post_custom( $post->ID );
        wp_nonce_field( 'pays_manager_post_nonce', 'meta_box_nonce' );
        $pays_fr = isset( $values['pays_fr'] ) ? esc_attr( $values['pays_fr'][0] ) : '';
        $pays_en = isset( $values['pays_en'] ) ? esc_attr( $values['pays_en'][0] ) : '';
        $pays_de = isset( $values['pays_de'] ) ? esc_attr( $values['pays_de'][0] ) : '';
        ?>
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
                    <label>Pays en francais: <span>*</span></label>
                    <input type="text" name="pays_fr" style="width: 96%;" value="<?= $pays_fr; ?>">
                </div>
                <div class="form-group" style="width: 50%;margin-bottom:2rem;">
                    <label>Pays en anglais: <span>*</span></label>
                    <input type="text" name="pays_en" style="width: 96%;" value="<?= $pays_en; ?>">
                </div>
                <div class="form-group" style="width: 50%;margin-bottom:2rem;">
                    <label>Pays en allemand: <span>*</span></label>
                    <input type="text" name="pays_de" style="width: 96%;" value="<?= $pays_de; ?>">
                </div>
                <div class="form-group" style="width: 100%;margin-bottom:2rem;display:flex;justify-content:center;flex-direction: row;">
                    <button class="btn btn-primary components-button is-primary add_annee_exam" style="width: 10%;display: flex;justify-content: center;">Ajouter</button>
                </div>
            </div>
        </form>
              
        <?php   
    }

    public function pays_manager_post_meta_save($post_id){

        // intercepte l'evenement lors de la sauvegarde
        //  if we're doing an auto save
        if( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) return;
        
        // if our nonce isn't there, or we can't verify it, bail
        if( !isset( $_POST['meta_box_nonce'] ) || !wp_verify_nonce( $_POST['meta_box_nonce'], 'pays_manager_post_nonce' ) ) return;
        
        // if our current user can't edit this post, bail
        // if( !current_user_can( 'edit_post' ) ) return;
        
        // now we can actually save the data
        $allowed = array( 
            'a' => array( // on allow a tags
                'href' => array() // and those anchords can only have href attribute
            )
        );

        update_post_meta($post_id, 'pays_fr', $_POST['pays_fr']);
        update_post_meta($post_id, 'pays_en', $_POST['pays_en']);
        update_post_meta($post_id, 'pays_de', $_POST['pays_de']);
    }
    
        
    public static function instance()
    {
        $instance = NULL;
          
        if(is_null($instance))
            $instance = new PaysManager();
          
        return $instance;
    }
        
}
    
PaysManager::instance();


?>
