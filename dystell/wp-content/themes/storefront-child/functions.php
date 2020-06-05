<?php
/*This file is part of storefront-child, storefront child theme.

All functions of this file will be loaded before of parent theme functions.
Learn more at https://codex.wordpress.org/Child_Themes.

Note: this function loads the parent stylesheet before, then child theme stylesheet
(leave it in place unless you know what you are doing.)
 */

if (!function_exists('suffice_child_enqueue_child_styles')) {
    function storefront_child_enqueue_child_styles()
    {
        // loading parent style
        wp_register_style(
            'parente2-style',
            get_template_directory_uri() . '/style.css'
        );

        wp_enqueue_style('parente2-style');
        // loading child style
        wp_register_style(
            'childe2-style',
            get_stylesheet_directory_uri() . '/style.css'
        );
        wp_enqueue_style('childe2-style');
    }
}
add_action('wp_enqueue_scripts', 'storefront_child_enqueue_child_styles');

/*Write here your own functions */
add_action('um_submit_form_errors_hook_', 'my_submit_form_errors_hook', 10, 1);

if (!function_exists('write_log')) {
    function write_log($log)
    {
        if (is_array($log) || is_object($log)) {
            error_log(print_r($log, true));
        } else {
            error_log($log);
        }
    }
}
function remove_sf_actions()
{

    remove_action('storefront_header', 'storefront_product_search', 40);

}
add_action('init', 'remove_sf_actions');

remove_action('woocommerce_proceed_to_checkout', 'woocommerce_button_proceed_to_checkout', 20);

function hide_update_notice()
{
    remove_action('admin_notices', 'update_nag', 3);
}
add_action('admin_notices', 'hide_update_notice', 1);

function favicon4admin()
{
    echo '<link rel="Shortcut Icon" type="image/x-icon" href="' . get_bloginfo('wpurl') . '/wp-content/favicon.ico" />';
}
add_action('admin_head', 'favicon4admin');

function example_admin_bar_remove_logo()
{
    global $wp_admin_bar;
    $wp_admin_bar->remove_menu('wp-logo');
}
add_action('wp_before_admin_bar_render', 'example_admin_bar_remove_logo', 0);

remove_action('storefront_footer', 'storefront_handheld_footer_bar', 999);

add_action('init', 'remove_footer_checkout');
function remove_footer_checkout()
{
    if (is_checkout()) {
        remove_action('storefront_footer', 'storefront_handheld_footer_bar', 999);
    }
}

add_action('init', 'jk_remove_storefront_handheld_footer_bar');

function jk_remove_storefront_handheld_footer_bar()
{
    remove_action('storefront_footer', 'storefront_handheld_footer_bar', 999);
}

add_action('rest_api_init', function () {
    register_rest_field('user', 'roles', array(
        'get_callback' => 'get_user_roles',
        'update_callback' => null,
        'schema' => array(
            'type' => 'array',
        ),
    ));
});

function get_user_roles($object, $field_name, $request)
{
    return get_userdata($object['id'])->roles;
}

add_action('rest_api_init', 'add_custom_fields');
function add_custom_fields()
{
    register_rest_field(
        'post',
        'custom_fields', //New Field Name in JSON RESPONSEs
        array(
            'get_callback' => 'get_custom_fields', // custom function name
            'update_callback' => null,
            'schema' => null,
        )
    );
}


function get_custom_fields( $object, $field_name, $request ) {
  //your code goes here
  return $customfieldvalue;
}


function wpdocs_register_my_custom_menu_page(){
  add_menu_page( 
      'Custom Menu Title',
      'custom menu',
      'manage_options',
      'custompage',
      'my_custom_menu_page',
      '',
      6
  ); 
}

/* 
function get_user_list($request) {
    //below you can change to a WQ_Query and customized it to ensure the list is exactly what you need
    $results = get_users();
 
    //Using the default controller to ensure the response follows the same structure as the default route
    $users = array();
    $controller = new WP_REST_Users_Controller();
    foreach ( $results as $user ) {
         $data    = $controller->prepare_item_for_response( $user, $request );
         $users[] = $controller->prepare_response_for_collection( $data );
     }
 
    return rest_ensure_response( $users );
}

add_action( 'rest_api_init', 'get_users' );


function get_users(){
    register_rest_route($namespace, '/customusers', array(
        'methods'             => WP_REST_Server::READABLE,
        'callback'            => 'get_user_list',
        'show_in_rest' => true
    ));
}
 */

add_filter('rest_user_query', 'remove_has_published_posts_from_api_user_query', 10, 2);
function remove_has_published_posts_from_api_user_query($prepared_args, $request)
{
    unset($prepared_args['has_published_posts']);

    return $prepared_args;
}

add_filter('rest_user_query', __NAMESPACE__ . 'remove_has_published_posts_from_api_user_query_two', 10, 2);
function remove_has_published_posts_from_api_user_query_two($prepared_args, $request)
{
    unset($prepared_args['has_published_posts']);

    return $prepared_args;
}