<?php
/*This file is part of storefront-child, storefront child theme.

All functions of this file will be loaded before of parent theme functions.
Learn more at https://codex.wordpress.org/Child_Themes.

Note: this function loads the parent stylesheet before, then child theme stylesheet
(leave it in place unless you know what you are doing.)
*/

if ( ! function_exists( 'suffice_child_enqueue_child_styles' ) ) {
	function storefront_child_enqueue_child_styles() {
	    // loading parent style
	    wp_register_style(
	      'parente2-style',
	      get_template_directory_uri() . '/style.css'
	    );

	    wp_enqueue_style( 'parente2-style' );
	    // loading child style
	    wp_register_style(
	      'childe2-style',
	      get_stylesheet_directory_uri() . '/style.css'
	    );
	    wp_enqueue_style( 'childe2-style');
	 }
}
add_action( 'wp_enqueue_scripts', 'storefront_child_enqueue_child_styles' );

/*Write here your own functions */
add_action( 'um_submit_form_errors_hook_', 'my_submit_form_errors_hook', 10, 1 );

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
function remove_sf_actions() {

    remove_action('storefront_header', 'storefront_product_search', 40);

}
add_action( 'init', 'remove_sf_actions' );

remove_action( 'woocommerce_proceed_to_checkout', 'woocommerce_button_proceed_to_checkout', 20 );



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


function example_admin_bar_remove_logo() {
    global $wp_admin_bar;
    $wp_admin_bar->remove_menu( 'wp-logo' );
}
add_action( 'wp_before_admin_bar_render', 'example_admin_bar_remove_logo', 0 );

remove_action( 'storefront_footer', 'storefront_handheld_footer_bar', 999 );

add_action( 'init', 'remove_footer_checkout' );
function remove_footer_checkout() {
  if ( is_checkout() ) {
    remove_action( 'storefront_footer', 'storefront_handheld_footer_bar', 999 );
  }
}

add_action( 'init', 'jk_remove_storefront_handheld_footer_bar' );

function jk_remove_storefront_handheld_footer_bar() {
  remove_action( 'storefront_footer', 'storefront_handheld_footer_bar', 999 );
}