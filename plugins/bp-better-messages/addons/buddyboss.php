<?php

defined( 'ABSPATH' ) || exit;

if ( !class_exists( 'Better_Messages_BuddyBoss' ) ) {

    class Better_Messages_BuddyBoss
    {

        public static function instance()
        {

            static $instance = null;

            if (null === $instance) {
                $instance = new Better_Messages_BuddyBoss();
            }

            return $instance;
        }

        public function __construct()
        {
            add_filter('bp_better_messages_after_format_message', array($this, 'buddyboss_group_messages'), 10, 4);
            add_filter('heartbeat_received', array($this, 'heartbeat_unread_notifications'), 12);
            add_filter( 'bb_pusher_enabled_features', array( $this, 'disable_bb_pusher') );


            add_action('wp_ajax_buddyboss_theme_get_header_unread_messages', array($this, 'buddyboss_theme_get_header_unread_messages'), 9);


            /**
             * BuddyBoss moderation
             */
            if( function_exists('bp_is_moderation_member_blocking_enable') ){
                $bb_blocking_enabled = bp_is_moderation_member_blocking_enable();
                if( $bb_blocking_enabled ){
                    add_filter( 'better_messages_can_send_message', array($this, 'buddyboss_disable_message_to_blocked'), 10, 3);
                }
            }

            if( function_exists('bb_access_control_member_can_send_message') ) {
                add_filter( 'better_messages_can_send_message', array($this, 'buddyboss_blocked_message'), 10, 3);
            }

            add_filter('bp_messages_thread_current_threads', array( $this, 'buddyboss_notifications_fix' ), 10, 1 );

            add_filter('bb_exclude_endpoints_from_restriction', array( $this, 'buddyboss_disable_rest_api_block' ), 10, 2 );
        }

        public function buddyboss_disable_rest_api_block( $default_exclude_endpoint, $current_endpoint ){
            if( strpos( $current_endpoint, 'better-messages/v1/' ) !== false ){
                $default_exclude_endpoint[] = $current_endpoint;
            }
            return $default_exclude_endpoint;
        }

        public function disable_bb_pusher( $options ){
            if( ! is_admin() ) {
                if (isset($options['live-messaging'])) {
                    $options['live-messaging'] = '0';
                }
            }
            return $options;
        }

        public function buddyboss_notifications_fix( $array ){
            if ( function_exists( 'buddyboss_theme_register_required_plugins' ) || class_exists('BuddyBoss_Theme') ) {
                if( count( $array['threads'] ) > 0 && isset( $array['total'] ) ) {
                    $new_threads = [];

                    foreach ($array['threads'] as $i => $thread) {
                        if ( ! isset($thread->last_message_date) || strtotime($thread->last_message_date) <= 0 ) {
                            unset($array['threads'][$i]);
                            $array['total']--;
                        } else {
                            $new_threads[] = $thread;
                        }
                    }


                    $array['threads'] = $new_threads;
                }
                if( $array['total'] < 0 ) $array['total'] = 0;
            }

            return $array;
        }

        public function heartbeat_unread_notifications( $response = array() ){
            if( Better_Messages()->settings['mechanism'] === 'websocket') {
                if (isset($response['total_unread_messages'])) {
                    unset($response['total_unread_messages']);
                }
            }

            return $response;
        }

        public function buddyboss_theme_get_header_unread_messages(){
            $response = array();
            ob_start();

            echo Better_Messages()->functions->get_threads_html( get_current_user_id() );
            ?>
            <script type="text/javascript">
                var notification_list = jQuery('.site-header .messages-wrap .notification-list');
                notification_list.removeClass('notification-list').addClass('bm-notification-list');

                notification_list.css({'margin' : 0, 'padding' : 0});

                jQuery(document).trigger("bp-better-messages-init-scrollers");
            </script>
            <?php
            $response['contents'] = ob_get_clean();

            wp_send_json_success( $response );
        }

        public function buddyboss_group_messages( $message, $message_id, $context, $user_id ){
            global $wpdb;
            $group_id         = Better_Messages()->functions->get_message_meta( $message_id, 'group_id', true );
            $message_deleted  = Better_Messages()->functions->get_message_meta( $message_id, 'bp_messages_deleted', true );

            if( $group_id ) {
                if ( function_exists('bp_get_group_name') ) {
                    $group_name = bp_get_group_name(groups_get_group($group_id));
                } else {
                    $bp_prefix = bp_core_get_table_prefix();
                    $table = $bp_prefix . 'bp_groups';
                    $group_name = $wpdb->get_var( "SELECT `name` FROM `{$table}` WHERE `id` = '{$group_id}';" );
                }

                $message_left     = Better_Messages()->functions->get_message_meta( $message_id, 'group_message_group_left', true );
                $message_joined   = Better_Messages()->functions->get_message_meta( $message_id, 'group_message_group_joined', true );

                if ($message_left && 'yes' === $message_left) {
                    $message = '<i>' . sprintf(__('Left "%s"', 'bp-better-messages'), ucwords($group_name)) . '</i>';
                } else if ($message_joined && 'yes' === $message_joined) {
                    $message = '<i>' . sprintf(__('Joined "%s"', 'bp-better-messages'), ucwords($group_name)) . '</i>';
                }
            }

            if ( $message_deleted && 'yes' === $message_deleted ) {
                $message =  '<i>' . __( 'This message was deleted.', 'bp-better-messages' ) . '</i>';
            }

            return $message;
        }

        public function buddyboss_disable_message_to_blocked( $allowed, $user_id, $thread_id ){
            if ( ! bp_is_active( 'moderation' ) ) return $allowed;
            if( ! class_exists( 'BP_Moderation' ) ) return $allowed;
            if( ! function_exists( 'bp_moderation_is_user_blocked' ) ) return $allowed;

            $participants = Better_Messages()->functions->get_participants($thread_id);

            if( ! isset( $participants['recipients'] ) ) {
                return $allowed;
            }

            /**
             * Not block in group thread
             */
            if( count($participants['recipients']) > 1 ){
                return $allowed;
            }

            $thread_type = Better_Messages()->functions->get_thread_type( $thread_id );
            if( $thread_type !== 'thread') return $allowed;

            foreach( $participants['recipients'] as $recipient_user_id ){
                if( bp_moderation_is_user_blocked( $recipient_user_id ) ){
                    global $bp_better_messages_restrict_send_message;
                    $bp_better_messages_restrict_send_message['bb_blocked_user'] = __( "You can't message a blocked member.", 'bp-better-messages' );
                    $allowed = false;

                    continue;
                }

                $moderation            = new BP_Moderation();
                $moderation->user_id   = $recipient_user_id;
                $moderation->item_id   = $user_id;
                $moderation->item_type = 'user';

                $id = BP_Moderation::check_moderation_exist( $user_id, 'user' );

                if ( ! empty( $id ) ) {
                    $moderation->id = (int) $id;
                    $moderation->populate();
                }

                $is_blocked = ( ! empty( $moderation->id ) && ! empty( $moderation->report_id ) );

                if( $is_blocked ){
                    global $bp_better_messages_restrict_send_message;
                    $bp_better_messages_restrict_send_message['bb_blocked_by_user'] = __("You can't message this member.", 'bp-better-messages');
                    $allowed = false;
                }
            }

            return $allowed;
        }

        public function buddyboss_blocked_message( $allowed, $user_id, $thread_id ){
            if( ! isset( $recipients ) ) return $allowed;
            if( ! is_array( $recipients ) ) return $allowed;
            if( count( $recipients ) === 0 ) return $allowed;

            $thread = new BP_Messages_Thread( $thread_id );

            $check_buddyboss_access = bb_access_control_member_can_send_message( $thread, $thread->recipients, 'wp_error' );

            if( is_wp_error($check_buddyboss_access) ){
                $allowed = false;
                global $bp_better_messages_restrict_send_message;
                $bp_better_messages_restrict_send_message['buddyboss_restricted'] = $check_buddyboss_access->get_error_message();
            }

            return $allowed;
        }

        public function bb_pushs_active(){
            if( function_exists('bb_onesignal_auth_key')
                && function_exists('bb_onesignal_account_apps')
                && function_exists('bb_onesignal_connected_app')
                && function_exists('bb_onesignal_connected_app_name')
            ) {
                if (bb_onesignal_auth_key() &&
                    bb_onesignal_account_apps() &&
                    bb_onesignal_connected_app() &&
                    bb_onesignal_connected_app_name() &&
                    !empty(bb_onesignal_connected_app_details())) {
                    return true;
                }
            }

            return false;
        }
    }
}

