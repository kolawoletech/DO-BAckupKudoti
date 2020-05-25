<?php

/**
  ReduxFramework Sample Config File
  For full documentation, please visit: https://docs.reduxframework.com
 * */

if ( !class_exists( 'VG_Admin_to_Frontend_Redux_Setup' ) ) {
    class VG_Admin_to_Frontend_Redux_Setup
    {
        public  $args = array() ;
        public  $sections = array() ;
        public  $pts ;
        public  $ReduxFramework ;
        public function __construct()
        {
            if ( !class_exists( 'ReduxFramework' ) ) {
                return;
            }
            // This is needed. Bah WordPress bugs.  ;)
            
            if ( true == Redux_Helpers::isTheme( __FILE__ ) ) {
                $this->initSettings();
            } else {
                add_action( 'init', array( $this, 'initSettings' ), 10 );
            }
        
        }
        
        public function initSettings()
        {
            // Set the default arguments
            $this->setArguments();
            // Create the sections and fields
            $this->setSections();
            if ( !isset( $this->args['opt_name'] ) ) {
                // No errors please
                return;
            }
            // If Redux is running as a plugin, this will remove the demo notice and links
            add_action( 'redux/loaded', array( $this, 'remove_demo' ) );
            $this->ReduxFramework = new ReduxFramework( $this->sections, $this->args );
        }
        
        // Remove the demo link and the notice of integrated demo from the redux-framework plugin
        function remove_demo()
        {
            // Used to hide the demo mode link from the plugin page. Only used when Redux is a plugin.
            
            if ( class_exists( 'ReduxFrameworkPlugin' ) ) {
                remove_filter(
                    'plugin_row_meta',
                    array( ReduxFrameworkPlugin::instance(), 'plugin_metalinks' ),
                    null,
                    2
                );
                // Used to hide the activation notice informing users of the demo panel. Only used when Redux is a plugin.
                remove_action( 'admin_notices', array( ReduxFrameworkPlugin::instance(), 'admin_notices' ) );
            }
        
        }
        
        public function setSections()
        {
            global  $wp_post_types ;
            $roles = wp_roles();
            $capabilities = array();
            foreach ( $roles->roles as $role ) {
                $capabilities = array_merge( $capabilities, array_keys( $role['capabilities'] ) );
            }
            if ( is_multisite() ) {
                $capabilities = array_merge( $capabilities, array(
                    'create_sites',
                    'delete_sites',
                    'manage_network',
                    'manage_sites',
                    'manage_network_users',
                    'manage_network_plugins',
                    'manage_network_themes',
                    'manage_network_options',
                    'upgrade_network',
                    'setup_network'
                ) );
            }
            // We tried to remove the capabilities that require second parameter, but it failed because they were already removed
            // I don't know why some users have those capabilities in the dropdown
            //			foreach ($wp_post_types as $post_type_key => $post_type_object) {
            //				$remove_capabilities = array($post_type_object->cap->edit_post, $post_type_object->cap->read_post, $post_type_object->cap->delete_post);
            //				$capabilities = array_diff($capabilities, $remove_capabilities);
            //			}
            sort( $capabilities );
            $fields = array();
            
            if ( is_multisite() ) {
                $main_options = get_blog_option( 1, VG_Admin_To_Frontend::$textname, array() );
                
                if ( get_current_blog_id() === 1 ) {
                    $fields[] = array(
                        'id'      => 'enable_wpmu_mode',
                        'type'    => 'switch',
                        'title'   => __( 'Enable global settings on Multisite?', VG_Admin_To_Frontend::$textname ),
                        'desc'    => __( 'WP Multisite Detected. Activate this option and the settings from the main site will apply to the entire network of sites. Deactivate this option to control the settings on individual sites.', VG_Admin_To_Frontend::$textname ),
                        'default' => false,
                    );
                } elseif ( !empty($main_options['enable_wpmu_mode']) ) {
                    $fields[] = array(
                        'id'   => 'enable_wpmu_mode',
                        'type' => 'info',
                        'desc' => sprintf( __( 'IMPORTANT. You are using global settings, you must change the settings in the main site. Go to the <a href="%s">settings on the main site</a>.', VG_Admin_To_Frontend::$textname ), get_admin_url( 1, 'admin.php?page=vg_admin_to_frontend' ) ),
                    );
                } else {
                    $fields[] = array(
                        'id'   => 'enable_wpmu_mode',
                        'type' => 'info',
                        'desc' => sprintf( __( 'WP Multisite Detected. You can manage the settings globally, you can enable the option in the main site. Go to the <a href="%s">settings on the main site</a>.', VG_Admin_To_Frontend::$textname ), get_admin_url( 1, 'admin.php?page=vg_admin_to_frontend' ) ),
                    );
                }
            
            }
            
            $fields = array_merge( $fields, array(
                array(
                'id'       => 'login_page_url',
                'type'     => 'text',
                'validate' => 'url',
                'title'    => __( 'Login Page URL (optional)', VG_Admin_To_Frontend::$textname ),
                'desc'     => __( 'By default, when someone opens an admin page in the frontend without login, we show a login form in the same page. If you have a custom login page, you can enter the URL here and we will redirect users to your custom login page instead of showing our login form.', VG_Admin_To_Frontend::$textname ),
            ),
                array(
                'id'      => 'login_message',
                'type'    => 'editor',
                'title'   => __( 'Login message', VG_Admin_To_Frontend::$textname ),
                'default' => __( 'You need to login to view this page.', VG_Admin_To_Frontend::$textname ),
                'desc'    => __( 'This will be displayed when the current user is not logged in and tries to see an admin page through a shortcode on the frontend. We will display a login form after your message.', VG_Admin_To_Frontend::$textname ),
            ),
                array(
                'id'    => 'admin_view_css',
                'type'  => 'textarea',
                'mode'  => 'css',
                'title' => __( 'Admin CSS', VG_Admin_To_Frontend::$textname ),
                'desc'  => __( 'This css will be used to customize the admin page when it´s displayed on the frontend. For example, you can hide admin elements or tweak design. You dont need to add style tags, just add the plain css.', VG_Admin_To_Frontend::$textname ),
            ),
                array(
                'id'      => 'hide_admin_bar_frontend',
                'type'    => 'switch',
                'title'   => __( 'Hide admin bar on the frontend', VG_Admin_To_Frontend::$textname ),
                'desc'    => __( 'By default WordPress shows a black bar at the top of the page when a logged in user views a frontend page. The bar lets you access the wp-admin, log out, edit the current page, etc. If you enable this option we will hide that bar and you can use the shortcode: [vg_display_logout_link] to display the logout link.', VG_Admin_To_Frontend::$textname ),
                'default' => false,
            ),
                array(
                'id'      => 'add_post_edit_link',
                'type'    => 'switch',
                'title'   => __( 'Add "Edit" link after post content', VG_Admin_To_Frontend::$textname ),
                'desc'    => __( 'Enable this option if you want to allow your frontend users to edit posts and link to the frontend page when viewing a post. Super admins will see a link to the wp-admin dashboard.', VG_Admin_To_Frontend::$textname ),
                'default' => false,
            ),
                array(
                'id'      => 'disable_quick_settings',
                'type'    => 'switch',
                'title'   => __( 'Disable the quick settings?', VG_Admin_To_Frontend::$textname ),
                'desc'    => __( 'Enable this option if you do not want to use the quick settings bar on the frontend, you can edit everything on the normal page editor.', VG_Admin_To_Frontend::$textname ),
                'default' => false,
            ),
                array(
                'id'      => 'enable_wpadmin_access_restrictions',
                'type'    => 'switch',
                'title'   => __( 'Enable the wp-admin access restrictions?', VG_Admin_To_Frontend::$textname ),
                'desc'    => __( 'Enable this option if you want to make sure users can view specific admin pages in the frontend and restrict other admin pages.', VG_Admin_To_Frontend::$textname ),
                'default' => false,
            ),
                array(
                'id'       => 'whitelisted_admin_urls',
                'type'     => 'textarea',
                'title'    => __( 'Access restriction: What wp-admin pages can be viewed on the frontend?', VG_Admin_To_Frontend::$textname ),
                'desc'     => sprintf( __( 'Enter a list of admin URLs that can be displayed in the frontend, one URL per line. All URLs not found in this list will be redirected to the homepage. We automatically add to this list the pages that you display on the frontend. Note, You still need to be careful with the user roles. The normal users should not be adminsitrators and they should not have advanced permissions like activate_plugins or manage_options capabilities. <a href="%s">Allow pages that contain our shortcode currently</a>', VG_Admin_To_Frontend::$textname ), add_query_arg( 'wpfa_auto_whitelist_urls', 1 ) ),
                'required' => array( 'enable_wpadmin_access_restrictions', 'equals', true ),
            ),
                array(
                'id'       => 'whitelisted_user_capability',
                'type'     => 'select',
                'title'    => __( 'Access restriction: Who can access all the wp-admin pages?', VG_Admin_To_Frontend::$textname ),
                'desc'     => __( 'You can select the user capability who can view all URLs bypassing the access restrictions. For example, "manage_options" means users who can manage site options can access all the admin pages ignoring the list of allowed URLs from the option above. You can deactivate the restrictions by clearing this option.', VG_Admin_To_Frontend::$textname ),
                'options'  => array_combine( array_unique( $capabilities ), array_unique( $capabilities ) ),
                'default'  => VG_Admin_To_Frontend_Obj()->master_capability(),
                'required' => array( 'enable_wpadmin_access_restrictions', 'equals', true ),
            ),
                array(
                'id'       => 'redirect_to_frontend',
                'type'     => 'text',
                'validate' => 'url',
                'title'    => __( 'Access restriction: Frontend dashboard URL', VG_Admin_To_Frontend::$textname ),
                'desc'     => __( 'When users access a wp-admin page directly, we will automatically redirect to the equivalent frontend page (i.e. wp-admin > pages is redirected to the "pages" in the frontend, only if you created the frontend page previously), if the frontend page doesn\'t exist we redirect to this URL as the "default page". Leave empty to redirect to the homepage', VG_Admin_To_Frontend::$textname ),
                'required' => array( 'enable_wpadmin_access_restrictions', 'equals', true ),
            ),
                array(
                'id'    => 'extra_popup_selectors',
                'type'  => 'text',
                'title' => __( 'Admin Popups CSS selectors', VG_Admin_To_Frontend::$textname ),
                'desc'  => __( 'Sometimes popups from wp-admin dont open centered or are too tall, you can add the CSS selectors here and the plugin will try to fix it automatically', VG_Admin_To_Frontend::$textname ),
            ),
                array(
                'id'      => 'disable_all_admin_notices',
                'type'    => 'switch',
                'title'   => __( 'Disable the wp-admin notices when viewing on the frontend?', VG_Admin_To_Frontend::$textname ),
                'desc'    => __( 'Enable this option if you want to remove the plugin, update, and annoying notifications on the frontend pages. Keep in mind that useful notifications will be removed as well', VG_Admin_To_Frontend::$textname ),
                'default' => false,
            ),
                array(
                'id'      => 'redirect_to_new_after_publish_post',
                'type'    => 'switch',
                'title'   => __( 'Redirect users to create new post after publishing a post?', VG_Admin_To_Frontend::$textname ),
                'desc'    => __( 'Enable this option if you want to clear the post editor after publishing a post, instead of showing the editor for editing the published post.', VG_Admin_To_Frontend::$textname ),
                'default' => false,
            ),
                array(
                'id'      => 'disable_frontend_to_main_window',
                'type'    => 'switch',
                'title'   => __( 'Disable the redirection of frontend pages to the main window', VG_Admin_To_Frontend::$textname ),
                'desc'    => __( 'Use this for debugging purposes in case of issues. If activating this fixes a problem, please contact us to fix it.', VG_Admin_To_Frontend::$textname ),
                'default' => false,
            ),
                array(
                'id'    => 'frontend_urls_allowed_in_iframe',
                'type'  => 'switch',
                'title' => __( 'Which frontend URLs are allowed to load inside an iframe?', VG_Admin_To_Frontend::$textname ),
                'desc'  => __( 'We redirect frontend URLs from the iframe to the main window. But sometimes translation plugins or page builders need to load previews in iframes. You can use this option to allow those plugins to load pages inside iframes. Enter the keywords from the URL separated by commas.', VG_Admin_To_Frontend::$textname ),
            ),
                array(
                'id'      => 'disable_permissions_help_message',
                'type'    => 'switch',
                'title'   => __( 'Disable the message indicating why a page didnt load?', VG_Admin_To_Frontend::$textname ),
                'desc'    => __( 'We show a message saying: "You need higher permissions" so administrators can see why a page doesnt load on the frontend.', VG_Admin_To_Frontend::$textname ),
                'default' => false,
            ),
                array(
                'id'       => 'wrong_permissions_page_url',
                'type'     => 'text',
                'validate' => 'url',
                'title'    => __( 'Wrong Permissions Page URL (optional)', VG_Admin_To_Frontend::$textname ),
                'desc'     => __( 'By default, when someone opens an admin page in the frontend with the wrong user role, we show a message saying that they are not allowed to access that page. You can enter a URL of your pricing page or upgrade page and we will redirect users to that page when they try to do something not allowed by their role (membership plan).', VG_Admin_To_Frontend::$textname ),
            )
            ) );
            $this->sections[] = array(
                'icon'   => 'el-icon-cogs',
                'title'  => __( 'General', VG_Admin_To_Frontend::$textname ),
                'fields' => $fields,
            );
            if ( function_exists( 'dapof_fs' ) ) {
            }
        }
        
        /**
        
        		  All the possible arguments for Redux.
        		  For full documentation on arguments, please refer to: https://github.com/ReduxFramework/ReduxFramework/wiki/Arguments
        
        		 * */
        public function setArguments()
        {
            $this->args = apply_filters( 'vg_admin_to_frontend/settings_page/args', array(
                'opt_name'           => VG_Admin_To_Frontend::$textname,
                'display_name'       => __( 'Settings', VG_Admin_To_Frontend::$textname ),
                'display_version'    => VG_Admin_To_Frontend::$version,
                'page_slug'          => VG_Admin_To_Frontend::$textname,
                'page_title'         => __( 'Settings', VG_Admin_To_Frontend::$textname ),
                'update_notice'      => false,
                'admin_bar'          => false,
                'menu_type'          => 'submenu',
                'menu_title'         => __( 'Settings', VG_Admin_To_Frontend::$textname ),
                'page_parent'        => 'wpatof_welcome_page',
                'default_mark'       => '*',
                'hints'              => array(
                'icon'          => 'el-icon-question-sign',
                'icon_position' => 'right',
                'icon_color'    => 'lightgray',
                'icon_size'     => 'normal',
                'tip_style'     => array(
                'color' => 'light',
            ),
                'tip_position'  => array(
                'my' => 'top left',
                'at' => 'bottom right',
            ),
                'tip_effect'    => array(
                'show' => array(
                'duration' => '500',
                'event'    => 'mouseover',
            ),
                'hide' => array(
                'duration' => '500',
                'event'    => 'mouseleave unfocus',
            ),
            ),
            ),
                'output'             => true,
                'output_tag'         => true,
                'compiler'           => true,
                'page_icon'          => 'icon-themes',
                'dev_mode'           => false,
                'page_permissions'   => 'manage_options',
                'save_defaults'      => true,
                'show_import_export' => true,
                'transient_time'     => '3600',
                'network_sites'      => true,
            ) );
        }
    
    }
    add_action( 'plugins_loaded', 'vgca_init_options_page' );
    function vgca_init_options_page()
    {
        new VG_Admin_to_Frontend_Redux_Setup();
    }

}

/**
 * Disable dev mode. For some reason it doesnt disable when 
 * I change the dev_mode argument when constructing the options page.
 * So I took this code from the redux-developer-mode-disabler plugin
 */

if ( !function_exists( 'vg_redux_disable_dev_mode_plugin' ) ) {
    function vg_redux_disable_dev_mode_plugin( $redux )
    {
        
        if ( $redux->args['opt_name'] != 'redux_demo' ) {
            $redux->args['dev_mode'] = false;
            $redux->args['forced_dev_mode_off'] = false;
        }
    
    }
    
    add_action( 'redux/construct', 'vg_redux_disable_dev_mode_plugin' );
}
