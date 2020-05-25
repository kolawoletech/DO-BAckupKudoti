<?php if ( ! defined( 'ABSPATH' ) ) exit;


/**
 * @param $args
 */
function um_profile_content_groups_list_default( $args ) {
	$enabled_tab = UM()->options()->get( 'profile_tab_groups_list' );
	if ( ! $enabled_tab ) {
		return;
	}
	
	echo do_shortcode('[ultimatemember_groups_profile_list groups_per_page="5" groups_per_page_mobile="5" own_groups="true"]');
}
add_action( 'um_profile_content_groups_list_default', 'um_profile_content_groups_list_default', 10, 1 );