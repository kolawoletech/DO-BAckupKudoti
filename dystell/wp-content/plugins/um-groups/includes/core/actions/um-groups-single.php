<?php if ( ! defined( 'ABSPATH' ) ) exit;


/**
 * Single Page Tabs
 *
 * @param $group_id
 */
function um_groups_single_page_tabs( $group_id ) {

	$param_tab = get_query_var('tab');
	$param_sub_tab = get_query_var('sub');

	$tabs = apply_filters('um_groups_tabs', array(), $group_id, $param_tab );

	$arr_tab_keys = array();
	if ( !empty( $tabs ) ) {
		foreach ( $tabs as $key => $tab ) {
			$arr_tab_keys[] = $key;
			$tab_url = add_query_arg( 'tab', $tab[ 'slug' ], '' );
			if ( isset( $tab[ 'default_sub' ] ) ) {
				$tab_url = add_query_arg( 'sub', $tab[ 'default_sub' ], $tab_url );
			}
			$tabs[ $key ][ 'url' ] = $tab_url;
		}
	}

	if( ! empty( $tabs ) ){
		if( ! empty( $param_tab ) ){
			UM()->Groups()->api()->current_group_tab = $param_tab;
			UM()->Groups()->api()->current_group_subtab = $param_sub_tab;
		}elseif(  empty( $param_tab ) || ! in_array( $param_tab, $arr_tab_keys ) ){
			UM()->Groups()->api()->current_group_tab = 'discussion';
			UM()->Groups()->api()->current_group_subtab = '';
		}
	}else{
		UM()->Groups()->api()->current_group_tab = '';
		UM()->Groups()->api()->current_group_subtab = '';
	}

	UM()->Groups()->api()->group_tabs = $arr_tab_keys;

	$t_args = compact( 'group_id', 'param_tab', 'tabs' );
	UM()->get_template( 'tabs/single-group-tabs.php', um_groups_plugin, $t_args, true );

	wp_enqueue_script( 'um_groups' );
	wp_enqueue_style( 'um_groups' );
}
add_action( 'um_groups_single_page_tabs', 'um_groups_single_page_tabs', 10, 1 );


/**
 * Single Page Content
 *
 * @param $group_id
 * @param $current_tab
 */
function um_groups_single_page_content( $group_id, $current_tab ) {
	wp_enqueue_script( 'um_groups' );
	wp_enqueue_style( 'um_groups' );

	$param_tab = get_query_var('tab');
	$param_sub_tab = get_query_var('sub');

	$sub_tabs = apply_filters('um_groups_sub_tabs', array(), $group_id, $param_sub_tab, $param_tab );


	if( ! empty( $sub_tabs ) ){
		echo '<ul class="um-groups-single-subtabs">';
			foreach( $sub_tabs as $sub_tab ):
				UM()->Groups()->api()->group_tabs[ ] = "{$param_tab}_{$sub_tab['slug']}";
				if( in_array( $param_tab, UM()->Groups()->api()->group_tabs ) ){
					UM()->Groups()->api()->group_tabs[ ] = $sub_tab['slug'];
					echo '<li class="um-groups-subtab-slug_'.$sub_tab['slug'].' '.( ( isset( $sub_tab['default'] ) && empty( $param_tab ) )|| $param_sub_tab == $sub_tab['slug'] ? 'active':'').'"><a href="?tab='.$param_tab.'&sub='.$sub_tab['slug'].'">'.$sub_tab['name'].'</a></li>';
				}
			endforeach;
		echo '</ul>';
	}

}
add_action( 'um_groups_single_page_content','um_groups_single_page_content', 10, 2 );


/**
 * Single Page Content - Discussion Tab
 *
 * @param $group_id
 */
function um_groups_single_page_content__discussion( $group_id ) {

	$t_args = compact( 'group_id' );
	UM()->get_template( 'tabs/discussions.php', um_groups_plugin, $t_args, true );
}
add_action( 'um_groups_single_page_content__discussion', 'um_groups_single_page_content__discussion' );


/**
 * Single Page Content - Members Tab
 *
 * @param $group_id
 */
function um_groups_single_page_content__members( $group_id ) {

	$args = UM()->Groups()->api()->get_members( $group_id, 'approved' );

	if ( $args ) {
		$args['group_id'] = $group_id;
		$args = apply_filters( 'um_groups_user_lists_args', $args );
		$args = apply_filters( 'um_groups_user_lists_args__approved', $args );
	} else {
		return;
	}

	$t_args = compact( 'args', 'group_id' );
	UM()->get_template( 'tabs/members.php', um_groups_plugin, $t_args, true );
}
add_action( 'um_groups_single_page_content__members', 'um_groups_single_page_content__members' );


/**
 * Single Page content - Settings > Details Tab
 *
 * @param $group_id
 */
function um_groups_single_page_sub_content__settings_details( $group_id ) {

	$args = array(
			'group_id' => $group_id
	);

	do_action( 'um_groups_create_form', $args );
}
add_action( 'um_groups_single_page_sub_content__settings_details', 'um_groups_single_page_sub_content__settings_details' );


/**
 * Single Page content - Settings > Avatar Tab
 *
 * @param $group_id
 */
function um_groups_single_page_sub_content__settings_avatar( $group_id ){

	$t_args = compact( 'group_id' );
	UM()->get_template( 'tabs/avatar.php', um_groups_plugin, $t_args, true );
}
add_action('um_groups_single_page_sub_content__settings_avatar','um_groups_single_page_sub_content__settings_avatar');


/**
 * Single Page content - Settings >Delete Tab
 *
 * @param $group_id
 */
function um_groups_single_page_sub_content__settings_delete( $group_id ){

	$t_args = compact( 'group_id' );
	UM()->get_template( 'tabs/delete.php', um_groups_plugin, $t_args, true );
}
add_action('um_groups_single_page_sub_content__settings_delete','um_groups_single_page_sub_content__settings_delete');


/**
 * Single Page content - Requests Tab
 *
 * @param $group_id
 */
function um_groups_single_page_content__requests( $group_id ){

	$args = UM()->Groups()->api()->get_members( $group_id, 'requests' );

	if( $args ){
		$args['group_id'] = $group_id;
		$args = apply_filters('um_groups_user_lists_args', $args );
		$args = apply_filters('um_groups_user_lists_args__requests', $args );
	}else{
		return;
	}

	$t_args = compact( 'args', 'group_id' );
	UM()->get_template( 'tabs/requests.php', um_groups_plugin, $t_args, true );
}
add_action('um_groups_single_page_content__requests','um_groups_single_page_content__requests');


/**
 * Single Page content - Blocked Tab
 *
 * @param $group_id
 */
function um_groups_single_page_content__blocked( $group_id ){

	$args = UM()->Groups()->api()->get_members( $group_id, 'blocked','', '', 0 );

	if( $args ){
		$args['group_id'] = $group_id;
		$args = apply_filters('um_groups_user_lists_args', $args );
		$args = apply_filters('um_groups_user_lists_args__blocked', $args );
	}else{
		return;
	}

	$t_args = compact( 'args', 'group_id' );
	UM()->get_template( 'tabs/blocked.php', um_groups_plugin, $t_args, true );
}
add_action('um_groups_single_page_content__blocked','um_groups_single_page_content__blocked');


/**
 * Single Page content - Send Invites Tab
 *
 * @param $group_id
 */
function um_groups_single_page_content__invites( $group_id ) {
	$args = UM()->Groups()->api()->get_members( $group_id, 'invite_front' );

	if ( $args ) {
		$args['group_id'] = $group_id;
		$args = apply_filters( 'um_groups_user_lists_args', $args );
		$args = apply_filters( 'um_groups_user_lists_args__invite_front', $args );
	} else {
		return;
	}

	$t_args = compact( 'args', 'group_id' );
	UM()->get_template( 'tabs/invites.php', um_groups_plugin, $t_args, true );
}
add_action('um_groups_single_page_content__invites','um_groups_single_page_content__invites');


/**
 * Remove content of non-existent tabs
 */
function um_groups_single_remove_tab_content() {
	$param_tab = get_query_var('tab');
	$param_sub_tab = get_query_var('sub');
	$tabs = UM()->Groups()->api()->group_tabs;

	if ( ! in_array( $param_tab, $tabs ) && has_action("um_groups_single_page_content__{$param_tab}","um_groups_single_page_content__{$param_tab}") ){
		add_action("um_groups_single_page_content__{$param_tab}","um_groups_single_page_content__discussion");
		remove_action("um_groups_single_page_content__{$param_tab}","um_groups_single_page_content__{$param_tab}");
	}

	if ( ! in_array( $param_sub_tab, $tabs ) && has_action("um_groups_single_page_sub_content__{$param_tab}_{$param_sub_tab}","um_groups_single_page_sub_content__{$param_tab}_{$param_sub_tab}")  ){
		add_action("um_groups_single_page_sub_content__{$param_tab}_{$param_sub_tab}","um_groups_single_page_content__discussion");
		remove_action("um_groups_single_page_sub_content__{$param_tab}_{$param_sub_tab}","um_groups_single_page_sub_content__{$param_tab}_{$param_sub_tab}");
	}
}
add_action( 'um_groups_single_page_content', 'um_groups_single_remove_tab_content' );