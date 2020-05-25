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

function my_submit_form_errors_hook( $args ) {
    // your code here
}


function remove_sf_actions() {

	echo($_SERVER['SERVER_NAME']);
    remove_action('storefront_header', 'storefront_product_search', 40);
    //write_log($_SERVER['SERVER_NAME']);

}
add_action( 'init', 'remove_sf_actions' );


remove_action( 'woocommerce_proceed_to_checkout', 'woocommerce_button_proceed_to_checkout', 20 );
