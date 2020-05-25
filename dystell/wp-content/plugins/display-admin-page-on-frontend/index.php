<?php

/*
  Plugin Name: WP Frontend Admin
  Plugin URI: https://wpfrontendadmin.com/?utm_source=wp-admin&utm_medium=plugins-list
  Description: Display wp-admin pages on the frontend using a shortcode.
  Version: 1.9.1
  Author: WP Frontend Admin
  Author Email: josevega@wpfrontendadmin.com
  Author URI: https://wpfrontendadmin.com/?utm_source=wp-admin&utm_medium=plugins-list
    License:
 Copyright 2018 JoseVega (josevega@vegacorp.me)
 This program is free software; you can redistribute it and/or modify
  it under the terms of the GNU General Public License, version 2, as
  published by the Free Software Foundation.
 This program is distributed in the hope that it will be useful,
  but WITHOUT ANY WARRANTY; without even the implied warranty of
  MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
  GNU General Public License for more details.
 You should have received a copy of the GNU General Public License
  along with this program; if not, write to the Free Software
  Foundation, Inc., 51 Franklin St, Fifth Floor, Boston, MA  02110-1301  USA
*/
if ( !defined( 'WPFA_IS_EMBED_MODE' ) ) {
    define( 'WPFA_IS_EMBED_MODE', strpos( wp_normalize_path( __FILE__ ), 'plugins/display-admin-page' ) === false );
}
if ( !WPFA_IS_EMBED_MODE ) {
    require 'inc/freemius-init.php';
}
require 'vendor/vg-plugin-sdk/index.php';
require_once __DIR__ . '/inc/table-columns-manager.php';
require_once __DIR__ . '/inc/change-texts.php';
require_once __DIR__ . '/inc/show-own-posts.php';

if ( !class_exists( 'VG_Admin_To_Frontend' ) ) {
    class VG_Admin_To_Frontend
    {
        private static  $instance = false ;
        static  $textname = 'vg_admin_to_frontend' ;
        static  $dir = __DIR__ ;
        static  $file = __FILE__ ;
        static  $version = '1.9.1' ;
        static  $name = 'Frontend Admin' ;
        static  $main_admin_id_key = 'vgfa_admin_main_admin_id' ;
        var  $allowed_urls = array() ;
        var  $base_page_id = 'vgca_base_page_id' ;
        private function __construct()
        {
        }
        
        function get_upgrade_url()
        {
            $url = ( function_exists( 'dapof_fs' ) ? dapof_fs()->checkout_url( WP_FS__PERIOD_ANNUALLY, true, array(
                'licenses'      => 1,
                'billing_cycle' => ( is_multisite() ? 'monthly' : WP_FS__PERIOD_ANNUALLY ),
            ) ) : 'https://wpfrontendadmin.com/go/start-free-trial-wpadmin' );
            return $url;
        }
        
        function get_main_admin_id()
        {
            $admin_id = (int) get_option( VG_Admin_To_Frontend::$main_admin_id_key, null );
            if ( !empty($_GET['vgfa_is_main_admin']) && current_user_can( 'manage_options' ) ) {
                $admin_id = (int) $_GET['vgfa_is_main_admin'];
            }
            
            if ( !empty($_GET['vgfa_im_main_admin']) && current_user_can( 'manage_options' ) ) {
                $admin_id = (int) get_current_user_id();
                $this->set_main_admin_id( $admin_id, true );
            }
            
            return $admin_id;
        }
        
        function set_main_admin_id( $user_id = null, $overwrite = false )
        {
            if ( !$user_id ) {
                $user_id = get_current_user_id();
            }
            if ( $this->get_main_admin_id() && !$overwrite ) {
                return;
            }
            update_option( VG_Admin_To_Frontend::$main_admin_id_key, $user_id );
        }
        
        function get_settings( $key = null, $default = null )
        {
            
            if ( is_multisite() ) {
                $main_options = get_blog_option( 1, VG_Admin_To_Frontend::$textname, array() );
                if ( !empty($main_options['enable_wpmu_mode']) ) {
                    $options = get_blog_option( 1, VG_Admin_To_Frontend::$textname, array() );
                }
            }
            
            if ( empty($options) ) {
                $options = get_option( VG_Admin_To_Frontend::$textname, array() );
            }
            $out = $options;
            if ( !empty($key) ) {
                $out = ( isset( $options[$key] ) ? $options[$key] : null );
            }
            if ( empty($out) ) {
                $out = $default;
            }
            return apply_filters(
                'vg_frontend_admin/settings',
                $out,
                $key,
                $default
            );
        }
        
        function get_plugin_install_url( $plugin_slug )
        {
            $install_plugin_base_url = ( is_multisite() ? network_admin_url() : admin_url() );
            $install_plugin_url = add_query_arg( array(
                's'    => $plugin_slug,
                'tab'  => 'search',
                'type' => 'term',
            ), $install_plugin_base_url . 'plugin-install.php' );
            return $install_plugin_url;
        }
        
        function init()
        {
            $this->args = array(
                'main_plugin_file'  => __FILE__,
                'show_welcome_page' => true,
                'welcome_page_file' => VG_Admin_To_Frontend::$dir . '/views/welcome-page-content.php',
                'logo'              => plugins_url( '/assets/imgs/logo.png', __FILE__ ),
                'plugin_name'       => VG_Admin_To_Frontend::$name,
                'plugin_prefix'     => 'wpatof_',
                'plugin_version'    => VG_Admin_To_Frontend::$version,
                'plugin_options'    => get_option( VG_Admin_To_Frontend::$textname, false ),
                'buy_link'          => $this->get_upgrade_url(),
                'buy_link_text'     => __( 'Try premium plugin for FREE - 7 Days', VG_Admin_To_Frontend::$textname ),
            );
            $this->vg_plugin_sdk = new VG_Freemium_Plugin_SDK( apply_filters( 'vg_admin_to_frontend/plugin_sdk_args', $this->args ) );
            add_shortcode( 'vg_display_admin_page', array( $this, 'get_admin_page_for_frontend' ) );
            add_shortcode( 'vg_display_logout_link', array( $this, 'get_logout_link' ) );
            add_shortcode( 'vg_display_edit_link', array( $this, 'get_edit_link' ) );
            add_shortcode( 'wp_frontend_admin_login_form', array( $this, 'get_login_form' ) );
            add_action( 'admin_init', array( $this, 'identify_source_id' ) );
            add_action( 'wp', array( $this, 'identify_source_id' ) );
            add_action( 'wp', array( $this, 'maybe_redirect_to_login_page' ) );
            add_action( 'admin_head', array( $this, 'enforce_frontend_dashboard' ), 1 );
            add_action( 'admin_head', array( $this, 'cleanup_admin_page_for_frontend' ), 5 );
            // The customizer page doesn't run the admin_head hook, so we use customize_controls_print_scripts
            // with priority 99 to make sure jquery already loaded
            add_action( 'customize_controls_print_scripts', array( $this, 'cleanup_admin_page_for_frontend' ), 99 );
            add_action( 'wp_head', array( $this, 'cleanup_admin_page_for_frontend' ) );
            add_action( 'wp_head', array( $this, 'frontend_cleanup_admin_page_for_frontend' ) );
            add_action( 'wp_head', array( $this, 'redirect_to_main_window_from_iframe' ), 1 );
            if ( !WPFA_IS_EMBED_MODE ) {
                add_action( 'admin_menu', array( $this, 'register_menu_page' ) );
            }
            add_filter( 'wp_die_handler', array( $this, 'register_custom_die_handler' ) );
            add_action( 'admin_init', array( $this, 'maybe_redirect_direct_link' ) );
            add_action( 'after_setup_theme', array( $this, 'late_init' ) );
            add_action( 'admin_bar_menu', array( $this, 'add_direct_link_menu' ), 999 );
            // Priority 99 to override the "edit" link from other frontend dashboards
            add_action( 'get_edit_post_link', array( $this, 'add_post_edit_link' ), 99 );
            // Override the edit link from the "wp user frontend" plugin
            add_action( 'wpuf_edit_post_link', array( $this, 'add_post_edit_link' ), 99 );
            add_action( 'wp_ajax_vg_frontend_admin_save_quick_settings', array( $this, 'save_quick_settings' ) );
            $compatibility_files = $this->get_files_list( __DIR__ . '/inc/compatibility/' );
            foreach ( $compatibility_files as $file ) {
                require_once $file;
            }
            if ( !empty($_GET['wpfa_auto_whitelist_urls']) ) {
                add_action(
                    'admin_init',
                    array( $this, 'whitelist_existing_urls' ),
                    10,
                    0
                );
            }
            add_action(
                'transition_post_status',
                array( $this, 'redirect_to_new_after_publish' ),
                10,
                3
            );
            
            if ( !empty($_POST) && !wp_doing_ajax() ) {
                add_action(
                    'wp_authenticate',
                    array( $this, 'redirect_to_login_page_after_empty_credentials' ),
                    10,
                    2
                );
                add_action( 'wp_login_failed', array( $this, 'redirect_to_login_page_after_wrong_credentials' ) );
            }
            
            add_action( 'admin_init', array( $this, 'apply_page_blacklist' ) );
            add_action( 'wp_logout', array( $this, 'redirect_after_log_out' ) );
            add_action( 'admin_page_access_denied', array( $this, 'catch_missing_reduxframework_error' ) );
        }
        
        function catch_missing_reduxframework_error()
        {
            
            if ( !empty($_GET['page']) && $_GET['page'] === VG_Admin_To_Frontend::$textname && !class_exists( 'ReduxFramework' ) ) {
                echo  '<p>' . sprintf( __( 'WP Frontend Admin: Please install the Redux Framework plugin. <a href="%s" target="_blank" class="button">Click here</a>.<br/>It´s required for the settings page, you can remove redux framework after changing the settings.', VG_Admin_To_Frontend::$textname ), VG_Admin_To_Frontend_Obj()->get_plugin_install_url( 'redux-framework' ) ) . '</p>' ;
                die;
            }
        
        }
        
        function redirect_after_log_out()
        {
            $login_page_url = $this->get_login_url( home_url() );
            if ( empty($login_page_url) ) {
                return;
            }
            wp_redirect( esc_url( $login_page_url ) );
            exit;
        }
        
        function redirect_to_login_page_after_empty_credentials( $user, $password )
        {
            if ( empty($_GET['loggedout']) && (empty($user) || empty($password)) ) {
                $this->redirect_to_login_page_after_wrong_credentials();
            }
        }
        
        function redirect_to_login_page_after_wrong_credentials( $username = null )
        {
            if ( defined( 'THEME_MY_LOGIN_VERSION' ) ) {
                return;
            }
            $referrer = wp_get_referer();
            // where did the post submission come from?
            // if there's a valid referrer, and it's not the default log-in screen
            
            if ( !empty($referrer) && !strstr( $referrer, 'wp-login' ) && !strstr( $referrer, 'wp-admin' ) ) {
                wp_redirect( esc_url( add_query_arg( 'vgfa_login_failed', '1', $referrer ) ) );
                exit;
            }
        
        }
        
        function redirect_to_new_after_publish( $new_status, $old_status, $post )
        {
            
            if ( strpos( $_SERVER['REQUEST_URI'], 'wp-admin/post' ) !== false && $new_status === 'publish' && $new_status != $old_status && $this->get_settings( 'redirect_to_new_after_publish_post' ) ) {
                wp_redirect( admin_url( 'post-new.php?post_type=' . $post->post_type ) );
                exit;
            }
        
        }
        
        function maybe_redirect_to_login_page()
        {
            $login_page_url = $this->get_login_url();
            if ( is_user_logged_in() || !is_singular() || empty($login_page_url) ) {
                return;
            }
            $post = get_queried_object();
            if ( strpos( $post->post_content, '[vg_display_admin_page' ) === false ) {
                return;
            }
            wp_redirect( esc_url( $login_page_url ) );
            exit;
        }
        
        function master_capability()
        {
            return ( is_multisite() ? 'manage_network' : 'manage_options' );
        }
        
        function is_master_user()
        {
            return ( is_multisite() ? current_user_can( $this->master_capability() ) : get_current_user_id() === $this->get_main_admin_id() );
        }
        
        function whitelist_existing_urls( $redirect = true, $only_master_user = true )
        {
            global  $wpdb ;
            // Only administrators can whitelist urls
            if ( !current_user_can( 'manage_options' ) ) {
                return;
            }
            if ( $only_master_user && !$this->is_master_user() ) {
                return;
            }
            $pages = implode( '<br>', $wpdb->get_col( "SELECT post_content FROM {$wpdb->posts} WHERE post_content LIKE '%[vg_display_admin_page%' " ) );
            preg_match_all( '/\\[vg_display_admin_page page_url="([^"]+)"/', $pages, $matches );
            if ( !empty($matches[1]) ) {
                $this->whitelist_urls( $matches[1] );
            }
            
            if ( $redirect ) {
                $redirect_to = remove_query_arg( 'wpfa_auto_whitelist_urls' );
                wp_redirect( $redirect_to );
                exit;
            }
        
        }
        
        function whitelist_urls( $urls )
        {
            $whitelisted_urls = array_map( 'trim', explode( PHP_EOL, $this->get_settings( 'whitelisted_admin_urls', '' ) ) );
            $whitelisted_urls = array_unique( array_merge( $whitelisted_urls, $urls ) );
            $all_urls = serialize( $whitelisted_urls );
            
            if ( preg_match( '/(edit\\.php|post-new\\.php)/', $all_urls ) ) {
                if ( strpos( $all_urls, 'post.php?action=edit' ) === false ) {
                    $whitelisted_urls[] = admin_url( 'post.php?action=edit' );
                }
                if ( strpos( $all_urls, 'post.php"' ) === false ) {
                    $whitelisted_urls[] = admin_url( 'post.php' );
                }
            }
            
            $this->update_option( 'whitelisted_admin_urls', implode( PHP_EOL, $whitelisted_urls ) );
        }
        
        function update_option( $key, $value )
        {
            
            if ( is_multisite() ) {
                $main_options = get_blog_option( 1, VG_Admin_To_Frontend::$textname, array() );
                
                if ( !empty($main_options['enable_wpmu_mode']) ) {
                    $use_main_site = true;
                    $options = $main_options;
                }
            
            }
            
            
            if ( empty($options) ) {
                $use_main_site = false;
                $options = get_option( VG_Admin_To_Frontend::$textname, array() );
            }
            
            $options[$key] = $value;
            
            if ( $use_main_site ) {
                update_blog_option( 1, VG_Admin_To_Frontend::$textname, $options );
            } else {
                update_option( VG_Admin_To_Frontend::$textname, $options );
            }
        
        }
        
        function redirect_to_main_window_from_iframe()
        {
            $is_disabled = apply_filters( 'vg_admin_to_frontend/open_frontend_pages_in_main_window', $this->get_settings( 'disable_frontend_to_main_window', false ) );
            if ( $is_disabled ) {
                return;
            }
            $allowed_keywords = $this->get_settings( 'frontend_urls_allowed_in_iframe', array() );
            $allowed_keywords = array_merge( $allowed_keywords, array( 'elementor', 'microthemer', 'trp-edit-translation' ) );
            ?>
			<script>
				var adminBaseUrl = <?php 
            echo  json_encode( admin_url() ) ;
            ?>;
				// If the iframe loaded a frontend page (not wp-admin), open it in the main window
				var keywordsAllowed = <?php 
            echo  json_encode( $allowed_keywords ) ;
            ?>;
				var keywordFound = false;
				if (window.parent.location.href.indexOf(adminBaseUrl) < 0 && window.parent.location.href !== window.location.href) {
					//					console.log('window.parent.location.href1', window.parent.location.href, window.location.href);
					keywordsAllowed.forEach(function (keyword) {
						if (window.location.href.indexOf(keyword) > -1 || window.parent.location.href.indexOf(keyword) > -1) {
							keywordFound = true;
						}
					});
					// Redirect if it is an url
					if (!keywordFound && window.location.href.indexOf('http') > -1) {
						window.parent.location.href = window.location.href;
					}
				}
			</script>
			<?php 
        }
        
        function get_current_url()
        {
            $pageURL = 'http';
            if ( isset( $_SERVER["HTTPS"] ) ) {
                if ( $_SERVER["HTTPS"] == "on" ) {
                    $pageURL .= "s";
                }
            }
            $pageURL .= "://";
            
            if ( $_SERVER["SERVER_PORT"] != "80" && $_SERVER["SERVER_PORT"] != "443" ) {
                $pageURL .= $_SERVER["HTTP_HOST"] . ":" . $_SERVER["SERVER_PORT"] . $_SERVER["REQUEST_URI"];
            } else {
                $pageURL .= $_SERVER["HTTP_HOST"] . $_SERVER["REQUEST_URI"];
            }
            
            return $pageURL;
        }
        
        function get_admin_url_without_base( $page_url )
        {
            $page_path_only = str_replace( array( '{{user_id}}' ), array( get_current_user_id() ), $page_url );
            
            if ( is_multisite() ) {
                foreach ( get_sites() as $site ) {
                    $page_path_only = str_replace( get_admin_url( $site->blog_id ), '', $page_path_only );
                }
            } else {
                $page_path_only = str_replace( admin_url(), '', $page_path_only );
            }
            
            // This is required for the quick-settings > remove elements tool,
            // Note. The upload.php breaks when the url has any query string
            if ( strpos( $page_path_only, 'upload.php' ) === false ) {
                $page_path_only = add_query_arg( 'vgfa_source', get_the_ID(), $page_path_only );
            }
            if ( empty($page_path_only) ) {
                $page_path_only = 'index.php';
            }
            return $page_path_only;
        }
        
        function get_login_url( $default = null )
        {
            $login_url = $this->get_settings( 'login_page_url' );
            if ( empty($login_url) ) {
                return $default;
            }
            
            if ( is_multisite() ) {
                foreach ( get_sites( array(
                    'order' => 'DESC',
                ) ) as $site ) {
                    $login_url = str_replace( get_site_url( $site->blog_id ), '', $login_url );
                }
                $login_url = home_url( $login_url );
            }
            
            return $login_url;
        }
        
        function is_page_blacklisted()
        {
            $whitelisted = $this->get_settings( 'whitelisted_admin_urls' );
            $whitelisted_capability = $this->get_settings( 'whitelisted_user_capability' );
            $restrictions_are_enabled = $this->get_settings( 'enable_wpadmin_access_restrictions' );
            if ( !is_user_logged_in() || empty($restrictions_are_enabled) || empty($whitelisted) || empty($whitelisted_capability) || !empty($whitelisted_capability) && current_user_can( $whitelisted_capability ) || $this->is_master_user() || strpos( $_SERVER['REQUEST_URI'], 'wp-login.php' ) !== false ) {
                return apply_filters(
                    'vg_admin_to_frontend/is_page_blacklisted',
                    false,
                    $whitelisted,
                    $whitelisted_capability,
                    $restrictions_are_enabled
                );
            }
            // Don't apply the access restrictions if the user is administrator
            // and we're viewing the settings page, so the admin can change settings
            // and not be locked out.
            if ( is_admin() && current_user_can( 'manage_options' ) && (!empty($_GET['page']) && $_GET['page'] === 'vg_admin_to_frontend') ) {
                return apply_filters(
                    'vg_admin_to_frontend/is_page_blacklisted',
                    false,
                    $whitelisted,
                    $whitelisted_capability,
                    $restrictions_are_enabled
                );
            }
            
            if ( wp_doing_ajax() ) {
                // Allow ajax requests from users with low capabilities because it's secure by default
                // We apply the blacklist on ajax requests from users with administrator capabilities only
                // as an extra layer of security
                if ( !current_user_can( 'manage_options' ) ) {
                    return apply_filters(
                        'vg_admin_to_frontend/is_page_blacklisted',
                        false,
                        $whitelisted,
                        $whitelisted_capability,
                        $restrictions_are_enabled
                    );
                }
                $url = wp_get_referer();
            }
            
            if ( empty($url) ) {
                $url = $this->get_current_url();
            }
            $url_path = $this->prepare_loose_url( $this->get_current_url() );
            $whitelisted_urls = array_map( 'trim', explode( PHP_EOL, $whitelisted ) );
            $allowed = false;
            if ( !empty($url_path) ) {
                foreach ( $whitelisted_urls as $whitelisted_url ) {
                    
                    if ( strpos( $whitelisted_url, '/' . $url_path ) !== false ) {
                        $allowed = true;
                        break;
                    }
                
                }
            }
            if ( $allowed ) {
                return apply_filters(
                    'vg_admin_to_frontend/is_page_blacklisted',
                    false,
                    $whitelisted,
                    $whitelisted_capability,
                    $restrictions_are_enabled
                );
            }
            return apply_filters(
                'vg_admin_to_frontend/is_page_blacklisted',
                true,
                $whitelisted,
                $whitelisted_capability,
                $restrictions_are_enabled
            );
        }
        
        function prepare_loose_url( $url )
        {
            $url = remove_query_arg( array(
                'post',
                'token',
                '_wpnonce',
                'user_id',
                'wp_http_referer',
                's'
            ), $url );
            $url_path = $this->get_admin_url_without_base( $url );
            // We only use the first query string that indicates the page or
            // post type and remove all the other query strings
            $url_path = current( explode( '&', $url_path ) );
            return $url_path;
        }
        
        /**
         * Redirect whitelisted pages to the frontend dashboard only if they're 
         * viewed outside the iframe (frontend dashboard)
         * @return null
         */
        function enforce_frontend_dashboard()
        {
            $is_blacklisted = $this->is_page_blacklisted();
            if ( $is_blacklisted || !is_user_logged_in() || !is_admin() || current_user_can( $this->master_capability() ) || wp_doing_ajax() || wp_doing_cron() ) {
                return;
            }
            $whitelisted_capability = $this->get_settings( 'whitelisted_user_capability' );
            if ( current_user_can( $whitelisted_capability ) ) {
                return;
            }
            $url = remove_query_arg( array( 'post' ), $this->get_current_url() );
            $url_path = $this->get_admin_url_without_base( $url );
            $frontend_page_id = $this->get_page_id( esc_url( admin_url( $url_path ) ), '' );
            
            if ( $frontend_page_id ) {
                $redirect_to = get_permalink( $frontend_page_id );
            } else {
                $redirect_to = $this->get_settings( 'redirect_to_frontend', ( !empty($this->get_settings( 'enable_wpadmin_access_restrictions' )) ? home_url() : null ) );
            }
            
            if ( empty($redirect_to) ) {
                return;
            }
            $redirect_to = add_query_arg( array(
                'wpfa_frontend_url' => 1,
            ), $redirect_to );
            ?>
			<script>
				// If it's not an iframe and it is an URL, redirect the parent window to the frontend url
				if (window.parent.location.href === window.location.href && window.location.href.indexOf('http') > -1) {
					window.parent.location.href = <?php 
            echo  json_encode( esc_url( $redirect_to ) ) ;
            ?>;
				}
			</script>
			<?php 
        }
        
        function apply_page_blacklist()
        {
            $is_blacklisted = $this->is_page_blacklisted();
            if ( !$is_blacklisted ) {
                return;
            }
            if ( wp_doing_ajax() ) {
                die( '0' );
            }
            $redirect_to = $this->get_settings( 'redirect_to_frontend', home_url() );
            wp_redirect( add_query_arg( array(
                'wpfa_blacklisted_url' => 1,
            ), $redirect_to ) );
            exit;
        }
        
        function register_custom_die_handler()
        {
            return array( $this, 'show_message_if_page_not_allowed' );
        }
        
        function show_message_if_page_not_allowed( $message, $title, $args )
        {
            if ( !is_string( $message ) ) {
                return _default_wp_die_handler( $message, $title, $args );
            }
            $text_not_found = strpos( $message, __( 'Sorry, you are not allowed to access this page.' ) ) === false && strpos( $message, __( 'You need a higher level of permission.' ) ) === false;
            if ( $text_not_found ) {
                return _default_wp_die_handler( $message, $title, $args );
            }
            $url = $this->get_settings( 'wrong_permissions_page_url', false );
            
            if ( filter_var( $url, FILTER_VALIDATE_URL ) ) {
                wp_safe_redirect( esc_url( $url ) );
                exit;
            }
            
            if ( empty($_GET['vgfa_source']) || $this->get_settings( 'disable_permissions_help_message', false ) ) {
                return _default_wp_die_handler( $message, $title, $args );
            }
            ob_start();
            include __DIR__ . '/views/frontend/wrong-permissions.php';
            $custom_message = ob_get_clean();
            return _default_wp_die_handler( $custom_message, $title, $args );
        }
        
        function save_quick_settings()
        {
            global  $wpdb ;
            if ( !$this->is_master_user() || empty($_REQUEST['ID']) || empty($_REQUEST['_wpnonce']) || !wp_verify_nonce( $_REQUEST['_wpnonce'], 'vg_frontend_admin_save_quick_settings' ) ) {
                wp_send_json_error( __( 'Settings couldn\'t be saved', VG_Admin_To_Frontend::$textname ) );
            }
            $post_id = (int) $_REQUEST['ID'];
            $update = array();
            update_post_meta( $post_id, 'is_wpfa_page', 1 );
            if ( !empty($_REQUEST['post_title']) ) {
                $update['post_title'] = sanitize_text_field( $_REQUEST['post_title'] );
            }
            if ( !empty($_REQUEST['page_template']) ) {
                update_post_meta( $post_id, '_wp_page_template', sanitize_text_field( $_REQUEST['page_template'] ) );
            }
            if ( isset( $_REQUEST['vgfa_hidden_elements'] ) ) {
                update_post_meta( $post_id, 'vgfa_hidden_elements', implode( ', ', array_filter( explode( ',', sanitize_text_field( $_REQUEST['vgfa_hidden_elements'] ) ) ) ) );
            }
            if ( !empty($_REQUEST['post_name']) ) {
                $update['post_name'] = sanitize_title( $_REQUEST['post_name'] );
            }
            if ( !empty($update) ) {
                $wpdb->update(
                    $wpdb->posts,
                    $update,
                    array(
                    'ID' => $post_id,
                ),
                    '%s',
                    '%d'
                );
            }
            
            if ( !empty($_REQUEST['menu']) ) {
                update_post_meta( $post_id, 'vgfa_menu', sanitize_text_field( $_REQUEST['menu'] ) );
                $this->maybe_add_to_menu( $post_id, sanitize_text_field( $_REQUEST['menu'] ) );
            }
            
            $this->update_option( 'disable_all_admin_notices', (bool) $_REQUEST['vgfa_hide_notices'] );
            clean_post_cache( $post_id );
            do_action( 'wp_frontend_admin/quick_settings/after_save', $post_id, get_post( $post_id ) );
            wp_send_json_success( array(
                'message' => __( 'Settings saved successfully. We will reload the page to show the new changes', VG_Admin_To_Frontend::$textname ),
                'new_url' => get_permalink( $post_id ),
            ) );
        }
        
        function maybe_add_to_menu( $post_id, $menu )
        {
            $already_in_menu = new WP_Query( array(
                'post_type'      => 'nav_menu_item',
                'posts_per_page' => 1,
                'meta_query'     => array( array(
                'key'   => '_menu_item_object_id',
                'value' => $post_id,
            ) ),
                'tax_query'      => array( array(
                'taxonomy' => 'nav_menu',
                'field'    => 'term_id',
                'terms'    => (int) $menu,
            ) ),
            ) );
            if ( !$already_in_menu->posts ) {
                wp_update_nav_menu_item( (int) $menu, 0, array(
                    'menu-item-title'     => get_the_title( $post_id ),
                    'menu-item-object-id' => $post_id,
                    'menu-item-object'    => 'page',
                    'menu-item-status'    => 'publish',
                    'menu-item-type'      => 'post_type',
                ) );
            }
        }
        
        /**
         * Get all files in the folder
         * @return array
         */
        function get_files_list( $directory_path, $file_format = '.php' )
        {
            $files = glob( trailingslashit( $directory_path ) . '*' . $file_format );
            return $files;
        }
        
        function add_post_edit_link( $link )
        {
            if ( $this->is_master_user() || is_admin() && !$this->get_settings( 'elementor_default_editor' ) ) {
                return $link;
            }
            $add_post_edit_link = $this->get_settings( 'add_post_edit_link' );
            
            if ( $add_post_edit_link ) {
                $post_id = get_the_ID();
                $is_elementor = get_post_meta( $post_id, '_elementor_data', true );
                $page_id = $this->get_page_id( admin_url( 'post.php?action=edit' ), __( 'Edit' ) );
                $url_parameters = array(
                    'post' => $post_id,
                );
                if ( !empty($is_elementor) || $this->get_settings( 'elementor_default_editor' ) ) {
                    $url_parameters['action'] = 'elementor';
                }
                $link = esc_url( add_query_arg( $url_parameters, get_permalink( $page_id ) ) );
            }
            
            return $link;
        }
        
        function get_shortcode_for_slug( $slug, $only_beginning = false )
        {
            $out = '[vg_display_admin_page page_url="' . $slug . '"';
            if ( !$only_beginning ) {
                $out .= ']';
            }
            return $out;
        }
        
        function get_page_id( $admin_url, $title )
        {
            global  $wpdb ;
            if ( strpos( $admin_url, '/edit.php' ) !== false && strpos( $admin_url, 'post_type' ) === false ) {
                $admin_url = add_query_arg( 'post_type', 'post', $admin_url );
            }
            $admin_url = remove_query_arg( 'vgfa_source', $admin_url );
            $url_path = remove_query_arg( 'vgfa_source', $this->get_admin_url_without_base( $admin_url ) );
            $full_shortcode = $this->get_shortcode_for_slug( $admin_url );
            $page_id = (int) $wpdb->get_var( "SELECT ID FROM {$wpdb->posts} WHERE post_type = 'page' AND post_status = 'publish' AND post_content LIKE '%[vg_display_admin_page%' AND post_content LIKE '%" . esc_url_raw( $url_path ) . "%' " );
            
            if ( !$page_id && $this->is_master_user() ) {
                $page_id = wp_insert_post( array(
                    'post_type'    => 'page',
                    'post_status'  => 'publish',
                    'post_title'   => trim( preg_replace( '/\\d+/', '', $title ) ),
                    'post_content' => $full_shortcode,
                ) );
                $this->whitelist_existing_urls( false, false );
            }
            
            return $page_id;
        }
        
        /**
         * Redirect from wp-admin url to the real frontend url.
         * We could link to the frontend url directly on the toolbar, but we´re doing this
         * redirect so we create the base page only when needed.
         * 
         * @return null
         */
        function maybe_redirect_direct_link()
        {
            if ( empty($_GET['vgca_direct']) || !$this->is_master_user() ) {
                return;
            }
            $page_id = $this->get_page_id( esc_url( admin_url( $_GET['vgca_slug'] ) ), sanitize_text_field( $_GET['title'] ) );
            $page_url = get_permalink( $page_id );
            wp_redirect( $page_url );
            exit;
        }
        
        function add_direct_link_menu( $wp_admin_bar )
        {
            if ( !is_admin() || !$this->is_master_user() ) {
                return;
            }
            $args = array(
                'id'    => 'vgca-direct-frontend-link',
                'title' => __( 'View on the frontend', VG_Admin_To_Frontend::$textname ),
                'href'  => add_query_arg( array(
                'vgca_direct' => 1,
            ), admin_url() ),
                'meta'  => array(
                'class' => 'vgca-direct-frontend-link',
            ),
            );
            $wp_admin_bar->add_node( $args );
        }
        
        function late_init()
        {
            $urls = array();
            if ( function_exists( 'dapof_fs' ) && !dapof_fs()->can_use_premium_code__premium_only() ) {
                $urls = array(
                    admin_url( 'edit.php' ),
                    admin_url( 'post-new.php' ),
                    admin_url( 'post.php?action=edit' ),
                    admin_url( 'edit-tags.php?taxonomy=post_tag' ),
                    admin_url( 'edit-tags.php?taxonomy=category' )
                );
            }
            $this->allowed_urls = apply_filters( 'vg_admin_to_frontend/allowed_urls', $urls );
            if ( $this->get_settings( 'hide_admin_bar_frontend' ) && !is_admin() && !$this->is_master_user() ) {
                add_filter( 'show_admin_bar', '__return_false' );
            }
        }
        
        function register_menu_page()
        {
            add_menu_page(
                VG_Admin_To_Frontend::$name,
                VG_Admin_To_Frontend::$name,
                'manage_options',
                'wpatof_welcome_page',
                array( $this->vg_plugin_sdk, 'render_welcome_page' ),
                plugins_url( '/assets/imgs/wp-admin-icon.png', __FILE__ )
            );
        }
        
        function required_capability_by_current_page()
        {
            global  $pagenow, $menu, $submenu ;
            if ( !is_admin() || !current_user_can( 'manage_options' ) ) {
                return;
            }
            
            if ( !empty($_GET['page']) ) {
                $page_slug = sanitize_text_field( $_GET['page'] );
            } elseif ( !empty($_GET['post_type']) ) {
                $page_slug = $pagenow . '?post_type=' . sanitize_text_field( $_GET['post_type'] );
            } elseif ( !empty($_GET['taxonomy']) ) {
                $page_slug = $pagenow . '?taxonomy=' . sanitize_text_field( $_GET['taxonomy'] );
            } else {
                $page_slug = $pagenow;
            }
            
            $capability = null;
            foreach ( $menu as $menu_page ) {
                
                if ( !empty($menu_page[2]) && $menu_page[2] === $page_slug ) {
                    $capability = $menu_page[1];
                    break;
                }
            
            }
            if ( !$capability ) {
                foreach ( $submenu as $submenu_items ) {
                    foreach ( $submenu_items as $menu_page ) {
                        
                        if ( !empty($menu_page[2]) && $menu_page[2] === $page_slug ) {
                            $capability = $menu_page[1];
                            break;
                        }
                    
                    }
                }
            }
            return $capability;
        }
        
        function identify_source_id()
        {
            // If it's a frontend page, allow it only for ?elementor-preview pages
            if ( wp_doing_ajax() || !is_admin() && empty($_GET['elementor-preview']) ) {
                return;
            }
            $referer = wp_get_referer();
            
            if ( strpos( $referer, 'vgfa_source=' ) !== false && empty($_GET['vgfa_source']) ) {
                $_GET['vgfa_source'] = (int) preg_replace( '/.+vgfa_source=(\\d+).*/', '$1', $referer );
                setcookie(
                    'vgfa_source',
                    $_GET['vgfa_source'],
                    null,
                    '/'
                );
            } elseif ( empty($_GET['vgfa_source']) && !empty($_COOKIE['vgfa_source']) ) {
                $_GET['vgfa_source'] = (int) $_COOKIE['vgfa_source'];
            }
        
        }
        
        function render_admin_css( $source_id = null )
        {
            if ( !$source_id && !empty($_GET['vgfa_source']) ) {
                $source_id = (int) $_GET['vgfa_source'];
            }
            $custom_css = preg_replace( '/(\\r|\\n)/', '', $this->get_settings( 'admin_view_css', '' ) );
            $hidden_elements = ( !empty($source_id) ? get_post_meta( $source_id, 'vgfa_hidden_elements', true ) : '' );
            ?>
			<style class="vgfa-admin-css">
			<?php 
            if ( !empty($this->get_settings( 'disable_all_admin_notices' )) ) {
                ?>
					.vgca-only-admin-content body.wp-admin .update-nag, 
					.vgca-only-admin-content body.wp-admin .updated, 
					.vgca-only-admin-content body.wp-admin .notice.error, 
					.vgca-only-admin-content body.wp-admin .is-dismissible, 
					.vgca-only-admin-content body.wp-admin .notice{
						display: none !important;
					}

			<?php 
            }
            ?>	

				.vgca-only-admin-content div#wpwrap {
					min-height: initial !important;
				}

				.vgca-only-admin-content #wpbody-content {
					padding-bottom: 0;
				}
				.vgca-only-admin-content body[class*="post-type-"] #wpbody-content {
					padding-bottom: 100px;
				}

				.vgca-only-admin-content body, 
				html.vgca-only-admin-content {
					height: auto;
					overflow: auto;
					min-height: 300px;
					-webkit-overflow-scrolling: touch !important;
				}

				.vgca-only-admin-content body {
					background: transparent;
					min-width: 100%;
				}
				.vgca-only-admin-content .postbox {
					min-width: 100%;
				}

			<?php 
            // Hide elements selected on quick-settings
            
            if ( !empty($hidden_elements) ) {
                $cleaned_selectors = '.vgca-only-admin-content ' . implode( ', .vgca-only-admin-content ', array_filter( explode( ',', $hidden_elements ) ) );
                echo  $cleaned_selectors . ',' ;
            }
            
            ?>				
				.vgca-only-admin-content #wpadminbar,
				.vgca-only-admin-content #adminmenumain,
				.vgca-only-admin-content #update-nag, 
				.vgca-only-admin-content body > record, 
				.vgca-only-admin-content .woocommerce-embed-page .woocommerce-layout__header,
				.vgca-only-admin-content .update-nag,
				.vgca-only-admin-content #screen-meta-links,
				.vgca-only-admin-content #wpfooter{
					display: none !important;
				}
				.vgca-only-admin-content .folded #wpcontent, 
				.vgca-only-admin-content .folded #wpfooter,
				.vgca-only-admin-content #wpcontent,
				.vgca-only-admin-content #wpfooter {
					margin-left: 0px !important;
					padding-left: 0px !important;
				}
				html.wp-toolbar.vgca-only-admin-content  {
					padding-top: 0px !important;
				}
				/*Limit media popups height*/
				.vgca-only-admin-content .thickbox-loading .media-modal.wp-core-ui,
				.vgca-only-admin-content .media-modal {
					max-height: 600px;
				}
				.vgca-only-admin-content #TB_ajaxContent,
				.vgca-only-admin-content #TB_window {
					max-height: 580px;
				}

				.vgca-only-admin-content .DomOutline_label {
					display: none;
				}
			</style>
			<script>
				var vgfaCustomCss = <?php 
            echo  json_encode( $custom_css ) ;
            ?>;
				var vgfaWpAdminBase = <?php 
            echo  json_encode( admin_url() ) ;
            ?>;
			</script>
			<?php 
        }
        
        function frontend_cleanup_admin_page_for_frontend()
        {
            if ( !is_singular() ) {
                return;
            }
            $post = get_queried_object();
            if ( empty($post->post_content) || strpos( $post->post_content, '[vg_display_admin_page' ) === false ) {
                return;
            }
            $this->render_admin_css( $post->ID );
        }
        
        function cleanup_admin_page_for_frontend()
        {
            // If it's a frontend page, allow it only for ?elementor-preview pages
            if ( wp_doing_ajax() || !is_admin() && empty($_GET['elementor-preview']) ) {
                return;
            }
            $this->render_admin_css( ( !empty($_GET['vgfa_source']) ? (int) $_GET['vgfa_source'] : '' ) );
            $required_capability_html = '';
            
            if ( !empty($_GET['vgfa_source']) ) {
                $required_capability = $this->required_capability_by_current_page();
                
                if ( $required_capability ) {
                    if ( !function_exists( 'get_editable_roles' ) ) {
                        require_once ABSPATH . 'wp-admin/includes/user.php';
                    }
                    $required_capability_html = '<ul>';
                    foreach ( get_editable_roles() as $role_name => $role_info ) {
                        if ( !empty($role_info['capabilities']) && isset( $role_info['capabilities'][$required_capability] ) ) {
                            $required_capability_html .= '<li>' . $role_name . '</li>';
                        }
                    }
                    $required_capability_html .= '</ul>';
                    $required_capability_html .= sprintf( __( '<div><b>Give permission to other user roles</b>. You can use the <a href="%s" target="_blank">User Role Editor</a> plugin to assign the capability to the user role required by the page: "%s". Careful, assign advanced capabilities only if you trust the users.</div>', VG_Admin_To_Frontend::$textname ), VG_Admin_To_Frontend_Obj()->get_plugin_install_url( 'user-role-editor' ), esc_html( $required_capability ) );
                }
            
            }
            
            ?>
			<script>
				var vgfaRequiredRoles = <?php 
            echo  json_encode( $required_capability_html ) ;
            ?>;

				// We add the class here, inline, to hide the admin menu and bar quicker
				// if we load it on the backend.js file it appears for a second while the file is downloaded
				if (window.parent.location.href !== window.location.href) {
					jQuery('html').addClass('vgca-only-admin-content');

			<?php 
            ?>
				}
			</script>
			<?php 
            wp_enqueue_script( 'vg-frontend-admin-outline', plugins_url( '/assets/vendor/jQuery.DomOutline.js', VG_Admin_To_Frontend::$file ), array( 'jquery' ) );
            wp_enqueue_script(
                'vg-frontend-admin-init',
                plugins_url( '/assets/js/backend.js', VG_Admin_To_Frontend::$file ),
                array( 'jquery' ),
                filemtime( VG_Admin_To_Frontend::$dir . '/assets/js/backend.js' )
            );
        }
        
        function get_login_form( $atts = array(), $content = '' )
        {
            extract( shortcode_atts( array(
                'redirect_to' => ( isset( $_REQUEST['redirect_to'] ) ? esc_url( $_REQUEST['redirect_to'] ) : '' ),
            ), $atts ) );
            
            if ( is_user_logged_in() ) {
                ob_start();
                
                if ( $redirect_to ) {
                    ?>
					<script>window.location.href = <?php 
                    echo  json_encode( esc_url( $redirect_to ) ) ;
                    ?>;</script>
					<?php 
                }
                
                return ob_get_clean();
            }
            
            $login_page_url = $this->get_login_url();
            $login_message = '';
            if ( empty($redirect_to) ) {
                $redirect_to = home_url();
            }
            $login_form = wp_login_form( array(
                'echo'     => false,
                'redirect' => $redirect_to,
            ) );
            ob_start();
            include 'views/frontend/log-in-message.php';
            return ob_get_clean();
        }
        
        function get_edit_link( $atts = array(), $content = '' )
        {
            extract( shortcode_atts( array(
                'post_id' => '',
            ), $atts ) );
            if ( !is_user_logged_in() ) {
                return;
            }
            if ( !$post_id ) {
                $post_id = get_the_ID();
            }
            ob_start();
            edit_post_link(
                __( 'Edit', VG_Admin_To_Frontend::$textname ),
                '',
                '',
                $post_id
            );
            $out = ob_get_clean();
            return $out;
        }
        
        function get_logout_link( $atts = array(), $content = '' )
        {
            extract( shortcode_atts( array(
                'page_url' => '',
            ), $atts ) );
            if ( !is_user_logged_in() ) {
                return;
            }
            $logout_link = str_replace( '<a ', '<a class="vg-logout-link" ', wp_loginout( $_SERVER['REQUEST_URI'], false ) );
            $out = '<style>.vg-logout-link{padding:5px;background:#000;color:#fff;text-decoration:none}</style>' . $logout_link;
            return $out;
        }
        
        function get_admin_page_for_frontend( $atts = array(), $content = '' )
        {
            extract( shortcode_atts( array(
                'page_url'               => '',
                'forward_parameters'     => true,
                'allowed_roles'          => null,
                'required_capabilities'  => null,
                'allowed_user_ids'       => null,
                'allow_single_post_edit' => null,
                'use_desktop_in_mobile'  => false,
                'allow_any_url'          => false,
                'lazy_load'              => true,
            ), $atts ) );
            
            if ( !is_user_logged_in() ) {
                $login_page_url = $this->get_login_url();
                $login_message = wp_kses_post( wpautop( $this->get_settings( 'login_message' ) ) );
                $login_form = wp_login_form( array(
                    'echo'     => false,
                    'redirect' => $_SERVER['REQUEST_URI'],
                ) );
                ob_start();
                include 'views/frontend/log-in-message.php';
                return ob_get_clean();
            }
            
            if ( !$page_url ) {
                return;
            }
            // Prevent errors. Sometimes user add forward/backward quotes to the shortcode
            $page_url = str_replace( array(
                '‘',
                '’',
                '“',
                '”'
            ), '', $page_url );
            $allowed_to_view = true;
            
            if ( !empty($allowed_roles) ) {
                $allowed_roles = explode( ',', $allowed_roles );
                $user_data = get_userdata( get_current_user_id() );
                $allowed_to_view = false;
                foreach ( $allowed_roles as $allowed_role ) {
                    
                    if ( in_array( $allowed_role, $user_data->roles ) ) {
                        $allowed_to_view = true;
                        break;
                    }
                
                }
            }
            
            
            if ( !empty($required_capabilities) ) {
                $required_capabilities = explode( ',', $required_capabilities );
                $user_data = get_userdata( get_current_user_id() );
                $allowed_to_view = false;
                foreach ( $required_capabilities as $required_capability ) {
                    
                    if ( current_user_can( $required_capability ) ) {
                        $allowed_to_view = true;
                        break;
                    }
                
                }
            }
            
            
            if ( !empty($allowed_user_ids) ) {
                $allowed_user_ids = array_map( 'intval', explode( ',', $allowed_user_ids ) );
                $allowed_to_view = in_array( get_current_user_id(), $allowed_user_ids );
            }
            
            
            if ( !$allowed_to_view ) {
                ob_start();
                include 'views/frontend/logged-in-user-not-allowed.php';
                return ob_get_clean();
            }
            
            $full_url = ( strpos( $page_url, '//' ) !== false ? $page_url : admin_url( $page_url ) );
            
            if ( empty($allow_single_post_edit) ) {
                $full_url = remove_query_arg( 'post', $full_url );
                $full_url = remove_query_arg( 'classic-editor', $full_url );
            }
            
            $allowed_urls = $this->get_admin_url_without_base( implode( ',', $this->allowed_urls ) );
            $path_to_check = remove_query_arg( 'vgfa_source', remove_query_arg( 'post', html_entity_decode( $this->get_admin_url_without_base( $full_url ) ) ) );
            if ( !empty($allowed_urls) && strpos( $allowed_urls, $path_to_check ) === false && strpos( $full_url, 'post_type=post' ) === false ) {
                
                if ( is_super_admin() ) {
                    ob_start();
                    include 'views/frontend/wrong-plan.php';
                    return ob_get_clean();
                } else {
                    return;
                }
            
            }
            ob_start();
            // customize_theme is used by the customizer, we don't want to show the quick settings inside the customizer preview
            
            if ( $this->is_master_user() && !$this->get_settings( 'disable_quick_settings' ) && empty($_GET['customize_theme']) && strpos( $_SERVER['REQUEST_URI'], 'post.php' ) === false ) {
                $post = get_post( get_the_ID() );
                $templates = wp_get_theme()->get_page_templates();
                $current_template = get_post_meta( get_the_ID(), '_wp_page_template', true );
                $menus = get_terms( 'nav_menu', array(
                    'hide_empty' => false,
                ) );
                $url_parts = explode( 'wp-admin/', $page_url );
                $help_url = 'https://wpfrontendadmin.com/contact/?utm_source=wp-admin&utm_term=' . esc_url( end( $url_parts ) ) . '&utm_campaign=quick-settings-help&utm_medium=' . (( empty(VG_Admin_To_Frontend_Obj()->allowed_urls) ? 'free' : 'pro' )) . '-plugin';
                wp_enqueue_style( 'vg-frontend-admin-quick-settings', plugins_url( '/assets/css/quick-settings.css', VG_Admin_To_Frontend::$file ) );
                include 'views/frontend/quick-settings.php';
            }
            
            
            if ( $allow_any_url ) {
                $final_url = $page_url;
            } else {
                $page_path_only = str_replace( '#038;', '&', $this->get_admin_url_without_base( html_entity_decode( $page_url ) ) );
                if ( $forward_parameters ) {
                    $page_path_only = add_query_arg( $_GET, $page_path_only );
                }
                // Sometimes tinymce urlencodes the url, so we use urldecode to prevent that
                $final_url = admin_url( $page_path_only );
            }
            
            include 'views/frontend/page.php';
            wp_enqueue_script(
                'vg-frontend-admin-init',
                plugins_url( '/assets/js/frontend.js', VG_Admin_To_Frontend::$file ),
                array( 'jquery' ),
                filemtime( VG_Admin_To_Frontend::$dir . '/assets/js/frontend.js' )
            );
            $popup_selectors = ( !empty($this->get_settings( 'extra_popup_selectors' )) ? $this->get_settings( 'extra_popup_selectors' ) : '' );
            $full_screen_keywords = array_map( 'trim', explode( ',', $this->get_settings( 'fullscreen_pages_keywords', '' ) ) );
            $disable_fullscreen_pages_keywords = array_map( 'trim', explode( ',', $this->get_settings( 'disable_fullscreen_pages_keywords', '' ) ) );
            wp_localize_script( 'vg-frontend-admin-init', 'vgfa_data', apply_filters( 'vg_admin_to_frontend/frontend/js_data', array(
                'wp_ajax_url'                       => admin_url( 'admin-ajax.php' ),
                'extra_popup_selectors'             => $popup_selectors,
                'backend_js_urls'                   => array( plugins_url( '/assets/vendor/jQuery.DomOutline.js', VG_Admin_To_Frontend::$file ), plugins_url( '/assets/js/backend.js', VG_Admin_To_Frontend::$file ) ),
                'fullscreen_pages_keywords'         => array_values( array_filter( array_merge( $full_screen_keywords, array(
                'action=elementor',
                'page=formidable-settings',
                'page=formidable&fr',
                'page=formidable-styles',
                'page=wpforms-builder',
                'customize.php'
            ) ) ) ),
                'disable_fullscreen_pages_keywords' => $disable_fullscreen_pages_keywords,
            ) ) );
            return ob_get_clean();
        }
        
        /**
         * Creates or returns an instance of this class.
         */
        static function get_instance()
        {
            
            if ( null == VG_Admin_To_Frontend::$instance ) {
                VG_Admin_To_Frontend::$instance = new VG_Admin_To_Frontend();
                VG_Admin_To_Frontend::$instance->init();
            }
            
            return VG_Admin_To_Frontend::$instance;
        }
        
        function __set( $name, $value )
        {
            $this->{$name} = $value;
        }
        
        function __get( $name )
        {
            return $this->{$name};
        }
    
    }
    if ( !function_exists( 'VG_Admin_To_Frontend_Obj' ) ) {
        function VG_Admin_To_Frontend_Obj()
        {
            return VG_Admin_To_Frontend::get_instance();
        }
    
    }
    VG_Admin_To_Frontend_Obj();
    require 'inc/options-init.php';
}
