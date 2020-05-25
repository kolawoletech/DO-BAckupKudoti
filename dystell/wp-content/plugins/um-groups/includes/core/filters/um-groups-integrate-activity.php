<?php if ( ! defined( 'ABSPATH' ) ) exit;


/**
 * Email template placeholders
 *
 * @param $search
 *
 * @return array
 */
function um_groups_activity_search_tpl( $search ) {
	$search[ ] = "{group_author_profile}";
	$search[ ] = "{group_author_name}";
	$search[ ] = "{group_permalink}";
	$search[ ] = "{group_name}";

	return $search;
}
add_filter( "um_activity_search_tpl", "um_groups_activity_search_tpl", 10, 1 );


/**
 * Search and Replace Email template placeholders
 *
 * @param $replace
 * @param $array
 *
 * @return array
 */
function um_groups_activity_replace_tpl( $replace, $array ){
	$replace[ ] = isset( $array['group_author_profile'] ) ? $array['group_author_profile'] : '';
	$replace[ ] = isset( $array['group_author_name'] ) ? $array['group_author_name'] : '';
	$replace[ ] = isset( $array['group_permalink'] ) ? $array['group_permalink'] : '';
	$replace[ ] = isset( $array['group_name'] ) ? $array['group_name'] : '';

	return $replace;
}
add_filter( "um_activity_replace_tpl", "um_groups_activity_replace_tpl", 10, 2 );


/**
 * Activity options
 *
 * @param $actions
 *
 * @return mixed
 */
function um_groups_activity_global_actions( $actions ) {
	$actions['new-group'] = __( 'New group', 'um-groups' );
	return $actions;
}
add_filter( 'um_activity_global_actions', 'um_groups_activity_global_actions', 10, 1 );