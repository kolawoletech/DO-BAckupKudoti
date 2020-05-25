<?php if ( ! defined( 'ABSPATH' ) ) exit;


/**
 * Invite Members
 *
 * @param $invited_user_id
 * @param $group_id
 * @param $invited_by_user_id
 */
function um_groups_notify_invite_member( $invited_user_id, $group_id, $invited_by_user_id ) {
	if( ! class_exists('UM_Notifications_API') ) {
		return;
	}

	um_fetch_user( $invited_by_user_id );

	$vars = array();
	$vars['photo'] = um_get_avatar_url( get_avatar( $invited_by_user_id, 40 ) );
	$vars['group_name'] = ucwords( get_the_title( $group_id ) );
	$vars['notification_uri'] = get_the_permalink( $group_id );
	$vars['group_invitation_host_name'] = um_user('display_name');

	UM()->Notifications_API()->api()->store_notification( $invited_user_id, 'groups_invite_member', $vars );
}
add_action( 'um_groups_after_member_changed_status__pending_member_review', 'um_groups_notify_invite_member', 1, 3 );


/**
 * Join Request
 *
 * @param $user_id
 * @param $group_id
 * @param $invited_by_user_id
 */
function um_groups_notify_join_request( $user_id, $group_id, $invited_by_user_id ) {
	if ( !class_exists( 'UM_Notifications_API' ) ) {
		return;
	}

	if( $user_id == $invited_by_user_id ) {

		um_fetch_user( $user_id );
		$vars = array();
		$vars['member_name'] = um_user('display_name');
		$vars['group_name'] = ucwords( get_the_title( $group_id ) );
		$vars['notification_uri'] = get_the_permalink( $group_id )."?tab=requests";

		$moderators = UM()->Groups()->member()->get_moderators( $group_id );
		foreach( $moderators as $key => $mod ){

			um_fetch_user( $mod->uid );

			$vars['photo'] = um_get_avatar_url( get_avatar( $user_id, 40 ) );
			$vars['group_invitation_host_name'] = um_user('display_name');

			UM()->Notifications_API()->api()->store_notification( $mod->uid, 'groups_join_request', $vars );
		}
	}

}
add_action( 'um_groups_after_member_changed_status__pending_admin_review', 'um_groups_notify_join_request', 1, 3 );


/**
 * Approve Member
 *
 * @param $user_id
 * @param $group_id
 * @param $invited_by_user_id
 * @param $group_role
 * @param $new_group
 */
function um_groups_notify_approve_member( $user_id, $group_id, $invited_by_user_id, $group_role, $new_group ) {
	if ( ! class_exists('UM_Notifications_API') ) {
		return;
	}
	if ( $new_group ) {
		return;
	}

	um_fetch_user( $user_id );

	$vars = array();
	$vars['photo'] = UM()->Groups()->api()->get_group_image( $group_id, 'default', 50, 50, true );
	$vars['group_name'] = ucwords( get_the_title( $group_id ) );
	$vars['notification_uri'] = get_the_permalink( $group_id );
	$vars['group_invitation_host_name'] = um_user('display_name');

	foreach ( $vars as $key => $value ) {
		if( is_array( $value ) ){
			$vars[$key] = current( $value );
		}
	}

	UM()->Notifications_API()->api()->store_notification( $user_id, 'groups_approve_member', $vars );
}
add_action( 'um_groups_after_member_changed_status__approved', 'um_groups_notify_approve_member', 1, 5 );
add_action( 'um_groups_after_member_changed_status__hidden_approved', 'um_groups_notify_approve_member', 1, 5 );


/**
 * Member Changed Role
 *
 * @param $user_id
 * @param $group_id
 * @param $new_role
 * @param $old_role
 */
function um_groups_notify_member_changed_role( $user_id, $group_id, $new_role, $old_role ) {

	if ( ! class_exists('UM_Notifications_API') ) {
		return;
	}

	um_fetch_user( $user_id );

	$group_member_roles = UM()->Groups()->api()->get_member_roles();

	$vars = array();
	$vars['photo'] = UM()->Groups()->api()->get_group_image( $group_id, 'default', 50, 50, true );
	$vars['group_name'] = ucwords( get_the_title( $group_id ) );
	$vars['group_role_new'] = $group_member_roles[ $new_role ];
	$vars['group_role_old'] = $group_member_roles[ $old_role ];
	$vars['notification_uri'] = get_the_permalink( $group_id );

	foreach ( $vars as $key => $value ) {
		if( is_array( $value ) ){
			$vars[$key] = current( $value );
		}
	}

	UM()->Notifications_API()->api()->store_notification( $user_id, 'groups_change_role', $vars );
}
add_action( 'um_groups_after_member_changed_role', 'um_groups_notify_member_changed_role', 1, 4 );