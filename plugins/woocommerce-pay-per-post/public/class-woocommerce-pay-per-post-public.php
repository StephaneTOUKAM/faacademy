<?php

use  PRAMADILLO\Woocommerce_Pay_Per_Post_Restrict_Content ;
/**
 * Class Woocommerce_Pay_Per_Post_Public
 */
class Woocommerce_Pay_Per_Post_Public
{
    private  $should_track_pageview ;
    public  $product_ids ;
    public function init()
    {
        Woocommerce_Pay_Per_Post_Shortcodes::register_shortcodes();
    }
    
    /**
     * TODO: Is this function even used?
     */
    public function load_integrations()
    {
        $post_id = get_the_ID();
        if ( Woocommerce_Pay_Per_Post_Helper::can_use_elementor() ) {
            if ( is_admin() || \Elementor\Plugin::$instance->documents->get( $post_id )->is_built_with_elementor() ) {
                require_once plugin_dir_path( dirname( __FILE__ ) ) . 'integrations/elementor/Elementor.php';
            }
        }
    }
    
    public function add_locks_to_post_title( $title, $post_id )
    {
        if ( !is_admin() ) {
            if ( !Woocommerce_Pay_Per_Post_Helper::has_access( $post_id, false ) ) {
                if ( in_the_loop() ) {
                    return apply_filters( 'wc_pay_per_post_paywall_icon', '<span>&#x1F512;</span>' ) . ' ' . $title;
                }
            }
        }
        return $title;
    }
    
    /**
     * @return false|void
     */
    public function should_disable_comments()
    {
        global  $product_ids ;
        $post_id = get_the_ID();
        Woocommerce_Pay_Per_Post_Helper::logger( 'Post ID: ' . $post_id . ' - Woocommerce_Pay_Per_Post_Public/should_disable_comments() Called' );
        
        if ( !Woocommerce_Pay_Per_Post_Helper::is_an_allowed_protected_post_type() || is_admin() ) {
            Woocommerce_Pay_Per_Post_Helper::logger( 'Post ID: ' . $post_id . ' - Woocommerce_Pay_Per_Post_Public/should_disable_comments() NOT in protected post types.  Bailing.' );
            return false;
        }
        
        if ( Woocommerce_Pay_Per_Post_Helper::can_use_elementor() ) {
            
            if ( \Elementor\Plugin::$instance->editor->is_edit_mode() || \Elementor\Plugin::$instance->preview->is_preview_mode() ) {
                Woocommerce_Pay_Per_Post_Helper::logger( 'Post ID: ' . $post_id . ' - Woocommerce_Pay_Per_Post_Public/should_disable_comments() In Elementor Edit/Preview Mode.  Bailing.' );
                return false;
            }
        
        }
        $turn_off_comments_completely_to_everyone_on_protected_posts = (bool) get_option( WC_PPP_SLUG . '_turn_off_comments_when_protected', true );
        $allow_admins_access_to_protected_posts = get_option( WC_PPP_SLUG . '_allow_admins_access_to_protected_posts', false );
        //$is_protected = Woocommerce_Pay_Per_Post_Helper::is_protected(get_the_ID());
        $is_protected = isset( $product_ids['product_ids'] ) && is_array( $product_ids['product_ids'] ) && count( $product_ids['product_ids'] ) > 0 && !empty($product_ids['product_ids'][0]);
        
        if ( $turn_off_comments_completely_to_everyone_on_protected_posts && $is_protected ) {
            Woocommerce_Pay_Per_Post_Helper::logger( 'Post ID: ' . $post_id . ' - Woocommerce_Pay_Per_Post_Public/should_disable_comments() $turn_off_comments_completely_to_everyone_on_protected_posts is TRUE' );
            add_filter( 'comments_open', function () {
                return false;
            } );
            add_filter( 'get_comments_number', function () {
                return 0;
            } );
        } else {
            Woocommerce_Pay_Per_Post_Helper::logger( 'Post ID: ' . $post_id . ' - Woocommerce_Pay_Per_Post_Public/should_disable_comments() $turn_off_comments_completely_to_everyone_on_protected_posts is FALSE' );
        }
    
    }
    
    /**
     * @param $unfiltered_content
     *
     * @return string
     */
    public function restrict_content( $unfiltered_content ) : string
    {
        $post_id = get_the_ID();
        Woocommerce_Pay_Per_Post_Helper::logger( 'Post ID: ' . $post_id . ' - Woocommerce_Pay_Per_Post_Public/restrict_content() called' );
        $show_paywall_in_archives = apply_filters( 'wc_pay_for_post_show_paywall_in_archives', true );
        
        if ( $show_paywall_in_archives ) {
            $check_archive = !is_archive();
        } else {
            $check_archive = is_archive();
        }
        
        //ensure that our filter only runs one time
        
        if ( $check_archive && !in_the_loop() && !is_singular() && !is_main_query() && Woocommerce_Pay_Per_Post_Helper::is_an_allowed_protected_post_type() ) {
            Woocommerce_Pay_Per_Post_Helper::logger( 'Post ID: ' . $post_id . ' - Woocommerce_Pay_Per_Post_Public/restrict_content() NOT in allowed post types/etc.  Bailing.' );
            return $unfiltered_content;
        }
        
        Woocommerce_Pay_Per_Post_Helper::logger( 'Post ID: ' . $post_id . ' - Woocommerce_Pay_Per_Post_Public/$is_protected start' );
        $is_protected = Woocommerce_Pay_Per_Post_Helper::is_protected();
        Woocommerce_Pay_Per_Post_Helper::logger( 'Post ID: ' . $post_id . ' - Woocommerce_Pay_Per_Post_Public/$is_protected after' );
        
        if ( !$is_protected['is_protected'] ) {
            Woocommerce_Pay_Per_Post_Helper::logger( 'Post ID: ' . $post_id . ' - Woocommerce_Pay_Per_Post_Public/restrict_content() - skipping - Is not protected.' );
            return $unfiltered_content;
        }
        
        //check and see if inline shortcode exists, if it does skip
        
        if ( strpos( $unfiltered_content, '[/wc-pay-for-post-inline]' ) ) {
            Woocommerce_Pay_Per_Post_Helper::logger( 'Post ID: ' . $post_id . ' - Woocommerce_Pay_Per_Post_Public/restrict_content() - skipping - Inline Shortcode Present' );
            // Handle shortcode access
            return $unfiltered_content;
        } else {
            $restrict = new Woocommerce_Pay_Per_Post_Restrict_Content( $post_id );
            $show_paywall = apply_filters( 'wc_pay_per_post_force_bypass_paywall', $restrict->can_user_view_content() );
            
            if ( $show_paywall == false ) {
                Woocommerce_Pay_Per_Post_Helper::logger( 'Post ID: ' . $post_id . ' - Woocommerce_Pay_Per_Post_Public/restrict_content() - $show_paywall is FALSE displaying full content' );
                return $restrict->show_content( $unfiltered_content );
            }
            
            remove_filter( current_filter(), __FUNCTION__ );
            Woocommerce_Pay_Per_Post_Helper::logger( 'Post ID: ' . $post_id . ' - Woocommerce_Pay_Per_Post_Public/restrict_content() - $show_paywall is TRUE displaying PayWall' );
            return $restrict->show_paywall( $unfiltered_content );
        }
    
    }
    
    public function set_product_ids_old()
    {
        global  $product_ids ;
        $post_id = get_the_ID();
        Woocommerce_Pay_Per_Post_Helper::logger( 'Post ID: ' . $post_id . ' - Woocommerce_Pay_Per_Post_Public/set_product_ids() Called' );
        $product_ids = get_post_meta( $post_id, WC_PPP_SLUG . '_product_ids', true );
        Woocommerce_Pay_Per_Post_Helper::logger( 'Post ID: ' . $post_id . ' - Woocommerce_Pay_Per_Post_Public/set_product_ids() Standard Post Product IDs ' . var_export( $product_ids, true ) );
        Woocommerce_Pay_Per_Post_Helper::logger( 'Post ID: ' . $post_id . ' - Woocommerce_Pay_Per_Post_Public/set_product_ids() Returned Combined Product IDs are ' . print_r( $product_ids, true ) );
        $this->product_ids = $product_ids;
    }
    
    public function set_product_ids()
    {
        global  $product_ids ;
        $post_id = get_the_ID();
        Woocommerce_Pay_Per_Post_Helper::logger( 'Post ID: ' . $post_id . ' - Woocommerce_Pay_Per_Post_Public/set_product_ids() Called' );
        $this->product_ids = Woocommerce_Pay_Per_Post_Helper::get_product_ids_by_post_id( $post_id );
        $product_ids = $this->product_ids;
        //Set the Global $product_ids variable
        Woocommerce_Pay_Per_Post_Helper::logger( 'Post ID: ' . $post_id . ' - Woocommerce_Pay_Per_Post_Public/set_product_ids() Returned Product IDs array ' . print_r( $product_ids, true ) );
    }
    
    protected function show_comments( $has_purchased = false )
    {
        $turn_off_comments_when_protected = get_option( WC_PPP_SLUG . '_turn_off_comments_when_protected', true );
        $allow_admins_access_to_protected_posts = get_option( WC_PPP_SLUG . '_allow_admins_access_to_protected_posts', false );
        if ( $turn_off_comments_when_protected ) {
            add_filter( 'comments_open', [ $this, 'comments_closed' ] );
        }
    }
    
    /**
     * @return bool
     */
    protected function is_admin() : bool
    {
        $current_user = wp_get_current_user();
        if ( user_can( $current_user, 'administrator' ) ) {
            return true;
        }
        return false;
    }

}