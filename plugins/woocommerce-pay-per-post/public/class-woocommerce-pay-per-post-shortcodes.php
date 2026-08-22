<?php

use  Elementor\Plugin ;
use  PRAMADILLO\Woocommerce_Pay_Per_Post_Restrict_Content ;
/**
 * Class Woocommerce_Pay_Per_Post_Shortcodes
 */
class Woocommerce_Pay_Per_Post_Shortcodes
{
    public static function register_shortcodes()
    {
        add_shortcode( 'woocommerce-payperpost', [ __CLASS__, 'process_shortcode' ] );
        add_shortcode( 'wc-pay-for-post', [ __CLASS__, 'process_shortcode' ] );
    }
    
    public static function process_shortcode( $atts )
    {
        $post_id = get_the_ID();
        Woocommerce_Pay_Per_Post_Helper::logger( 'Post ID: ' . $post_id . ' - SHORTCODE: Woocommerce_Pay_Per_Post_Shortcodes/process_shortcode() called. - BACKTRACE - Called From - ' . print_r( debug_backtrace()[1]['function'], true ) );
        Woocommerce_Pay_Per_Post_Helper::logger( 'Post ID: ' . $post_id . ' - SHORTCODE: Woocommerce_Pay_Per_Post_Shortcodes/process_shortcode() supplied attributes ', $atts );
        global  $product_ids ;
        $template = 'purchased';
        $orderby = 'date';
        $order = 'DESC';
        $transient = '';
        $bypass_transients = false;
        if ( isset( $atts['bypass_transients'] ) && ($atts['bypass_transients'] === 'TRUE' || $atts['bypass_transients'] === true || $atts['bypass_transients'] === 'true') ) {
            $bypass_transients = true;
        }
        if ( isset( $atts['template'] ) && array_key_exists( $atts['template'], self::available_templates() ) ) {
            $template = $atts['template'];
        }
        $custom_post_types = Woocommerce_Pay_Per_Post_Helper::get_protected_post_types();
        if ( !is_array( $custom_post_types ) ) {
            $custom_post_types = explode( ',', $custom_post_types );
        }
        $args = [
            'orderby'     => $orderby,
            'order'       => $order,
            'nopaging'    => true,
            'meta_query'  => [ [
            'key'     => WC_PPP_SLUG . '_product_ids',
            'value'   => '',
            'compare' => '!=',
        ] ],
            'post_status' => 'publish',
            'post_type'   => $custom_post_types,
        ];
        Woocommerce_Pay_Per_Post_Helper::logger( 'Post ID: ' . $post_id . ' - SHORTCODE: Woocommerce_Pay_Per_Post_Shortcodes/process_shortcode() query args ', $args );
        $get_ppp_args = apply_filters( 'wc_pay_per_post_args', $args );
        Woocommerce_Pay_Per_Post_Helper::logger( 'Post ID: ' . $post_id . ' - SHORTCODE: Woocommerce_Pay_Per_Post_Shortcodes/process_shortcode() query args AFTER FILTER ', $args );
        //			echo '<pre>'.print_r($get_ppp_args, true).'</pre>';
        $ppp_posts = Woocommerce_Pay_Per_Post_Helper::get_protected_posts( $get_ppp_args, $transient, $bypass_transients );
        Woocommerce_Pay_Per_Post_Helper::logger( 'Post ID: ' . $post_id . ' - SHORTCODE: Woocommerce_Pay_Per_Post_Shortcodes/process_shortcode() $ppp_posts contains ' . count( $ppp_posts ) . ' posts.' );
        Woocommerce_Pay_Per_Post_Helper::logger( 'Post ID: ' . $post_id . ' - SHORTCODE: Woocommerce_Pay_Per_Post_Shortcodes/process_shortcode() $ppp_posts contains ' . json_encode( $ppp_posts ) . ' posts.' );
        //			echo '<pre>'.print_r($ppp_posts, true).'</pre>';
        ob_start();
        switch ( $template ) {
            case 'has_access':
                self::shortcode_has_access( $template, $ppp_posts );
                break;
            case 'purchased':
                self::shortcode_purchased( $template, $ppp_posts );
                break;
            case 'remaining':
                self::shortcode_remaining( $template, $ppp_posts );
                break;
            case 'all':
                self::shortcode_all( $template, $ppp_posts );
                break;
        }
        return ob_get_clean();
    }
    
    /**
     * @param $template
     * @param $ppp_posts
     */
    protected static function shortcode_purchased( $template, $ppp_posts )
    {
        $post_id = get_the_ID();
        Woocommerce_Pay_Per_Post_Helper::logger( 'Post ID: ' . $post_id . ' - SHORTCODE: Woocommerce_Pay_Per_Post_Shortcodes/shortcode_purchased() called. - BACKTRACE - Called From - ' . print_r( debug_backtrace()[1]['function'], true ) );
        $purchased = [];
        
        if ( is_user_logged_in() ) {
            foreach ( $ppp_posts as $post ) {
                
                if ( Woocommerce_Pay_Per_Post_Helper::has_purchased( $post->ID, false ) ) {
                    Woocommerce_Pay_Per_Post_Helper::logger( 'Post ID: ' . $post_id . ' - SHORTCODE: Woocommerce_Pay_Per_Post_Shortcodes/shortcode_purchased() checking if ' . $post->ID . ' has been purchased.  HAS BEEN PURCHASED' );
                    $purchased[] = $post;
                } else {
                    Woocommerce_Pay_Per_Post_Helper::logger( 'Post ID: ' . $post_id . ' - SHORTCODE: Woocommerce_Pay_Per_Post_Shortcodes/shortcode_purchased() checking if ' . $post->ID . ' HAS NOT BEEN PURCHASED' );
                }
            
            }
            require Woocommerce_Pay_Per_Post_Helper::locate_template( self::available_templates()[$template], '', WC_PPP_PATH . 'public/partials/' );
        }
    
    }
    
    /**
     * @param $template
     * @param $ppp_posts
     */
    protected static function shortcode_has_access( $template, $ppp_posts )
    {
        $post_id = get_the_ID();
        Woocommerce_Pay_Per_Post_Helper::logger( 'Post ID: ' . $post_id . ' - SHORTCODE: Woocommerce_Pay_Per_Post_Shortcodes/shortcode_has_access() called. - BACKTRACE - Called From - ' . print_r( debug_backtrace()[1]['function'], true ) );
        $purchased = [];
        
        if ( is_user_logged_in() ) {
            foreach ( $ppp_posts as $post ) {
                
                if ( Woocommerce_Pay_Per_Post_Helper::has_access( $post->ID, false ) ) {
                    Woocommerce_Pay_Per_Post_Helper::logger( 'Post ID: ' . $post_id . ' - SHORTCODE: Woocommerce_Pay_Per_Post_Shortcodes/shortcode_has_access() checking if ' . $post->ID . ' has access.  HAS Access' );
                    $purchased[] = $post;
                } else {
                    Woocommerce_Pay_Per_Post_Helper::logger( 'Post ID: ' . $post_id . ' - SHORTCODE: Woocommerce_Pay_Per_Post_Shortcodes/shortcode_has_access() checking if ' . $post->ID . ' has access.  DOES NOT HAVE Access' );
                }
            
            }
            require Woocommerce_Pay_Per_Post_Helper::locate_template( self::available_templates()[$template], '', WC_PPP_PATH . 'public/partials/' );
        }
    
    }
    
    /**
     * @param $template
     * @param $ppp_posts
     */
    protected static function shortcode_remaining( $template, $ppp_posts )
    {
        $remaining = [];
        
        if ( is_user_logged_in() ) {
            foreach ( $ppp_posts as $post ) {
                if ( !Woocommerce_Pay_Per_Post_Helper::has_access( $post->ID, false ) ) {
                    $remaining[] = $post;
                }
            }
            require Woocommerce_Pay_Per_Post_Helper::locate_template( self::available_templates()[$template], '', WC_PPP_PATH . 'public/partials/' );
        }
    
    }
    
    /**
     * @param $template
     * @param $ppp_posts
     */
    protected static function shortcode_all( $template, $ppp_posts )
    {
        require Woocommerce_Pay_Per_Post_Helper::locate_template( self::available_templates()[$template], '', WC_PPP_PATH . 'public/partials/' );
    }
    
    /**
     * @param null $post_id
     *
     * @return bool
     */
    protected static function has_purchased_products( $post_id = null ) : bool
    {
        _deprecated_function( __FUNCTION__, '2.6.2', 'Woocommerce_Pay_Per_Post_Helper::has_purchased()' );
        global  $product_ids ;
        if ( is_null( $post_id ) ) {
            $post_id = get_the_ID();
        }
        $current_user = wp_get_current_user();
        foreach ( (array) $product_ids as $id ) {
            Woocommerce_Pay_Per_Post_Helper::logger( '_deprecated_function Looking to see if purchased product ' . trim( $id ) );
            if ( wc_customer_bought_product( $current_user->user_email, $current_user->ID, trim( $id ) ) ) {
                return true;
            }
        }
        return false;
    }
    
    /**
     * @param $post_id
     *
     * This returns the INVERSE of can_user_view_content()
     *
     * @return bool
     */
    protected static function has_access( $post_id ) : bool
    {
        $restrict = new Woocommerce_Pay_Per_Post_Restrict_Content( $post_id );
        $restrict->set_track_pageview( false );
        if ( apply_filters( 'wc_pay_per_post_hide_delay_restricted_posts_when_paywall_should_not_be_shown', false ) ) {
            /**
             * We have the following check because if you have delay protection enabled and the post is not suppose to show the paywall for
             * a year after publishing, the posts that show in the purchased content tab or purchased shortcode will output all of the ppp posts
             * that have delay protection even though they are not suppose to show paywall yet.
             */
            if ( 'delay' === Woocommerce_Pay_Per_Post_Helper::is_protected( $post_id ) ) {
                return $restrict->check_if_should_show_paywall();
            }
        }
        return !$restrict->can_user_view_content();
    }
    
    private static function available_templates() : array
    {
        return [
            'purchased'  => 'shortcode-purchased.php',
            'has_access' => 'shortcode-has_access.php',
            'all'        => 'shortcode-all.php',
            'remaining'  => 'shortcode-remaining.php',
        ];
    }

}