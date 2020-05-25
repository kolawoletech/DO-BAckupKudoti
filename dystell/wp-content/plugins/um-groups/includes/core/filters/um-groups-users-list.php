<?php if ( ! defined( 'ABSPATH' ) ) exit;


/**
 * Global users per page filter
 *
 * @param $limit
 *
 * @return int
 */
function um_groups_users_per_page( $limit ) {
	return 10;
}
add_filter( 'um_groups_users_per_page', 'um_groups_users_per_page', 10, 1 );


/**
 * Users lists offset query for pagination
 *
 * @param $args
 *
 * @return mixed
 */
function um_groups_user_lists_args( $args ) {
	$limit = apply_filters('um_groups_users_per_page', 0 );
	$args['offset'] = isset( $_REQUEST['offset'] ) ?  sanitize_key( $_REQUEST['offset'] ) + $limit : $limit ;
	return $args;
}
add_filter( 'um_groups_user_lists_args', 'um_groups_user_lists_args', 10, 1 );


/**
 * User Menus for Members tab
 *
 * @param $args
 *
 * @return mixed
 */
function um_groups_user_lists_args__approved( $args ) {

	$args['load_more'] = 'approved';

	$privacy = UM()->Groups()->api()->get_privacy_slug( $args['group_id'] );

	$args['privacy'] = $privacy;

	if ( UM()->Groups()->api()->can_manage_group( $args['group_id'], null, $privacy ) || um_groups_admin_all_access() ) {

		$args['menus']= array(
			'make-admin'        => __( 'Make Admin', 'um-groups' ),
			'make-moderator'    => __( 'Make Moderator', 'um-groups' ),
			'make-member'       => __( 'Make Member', 'um-groups' ),
			'remove-from-group' => __( 'Remove From Group', 'um-groups' ),
		);

	}

	return $args;
}
add_filter( 'um_groups_user_lists_args__approved', 'um_groups_user_lists_args__approved' );


/**
 * User Menus for Invite tab
 *
 * @param $args
 *
 * @return mixed
 */
function um_groups_user_lists_args__invite_front( $args ) {
	$args['load_more'] = 'invite_front';

	$privacy = UM()->Groups()->api()->get_privacy_slug( $args['group_id'] );

	$args['privacy'] = $privacy;

	if ( UM()->Groups()->api()->can_invite_members( $args['group_id'], get_current_user_id() ) || um_groups_admin_all_access() ) {
		$args['menus'] = array(
			'invite' => __( 'Invite', 'um-groups' )
		);
	}

	return $args;
}
add_filter( 'um_groups_user_lists_args__invite_front', 'um_groups_user_lists_args__invite_front' );


/**
 * Remove invite user menu for existing member from Invite tab
 *
 * @param $menus
 * @param $user_id
 * @param $group_id
 *
 * @return mixed
 */
function um_groups_list_users_menu__invite_front( $menus, $user_id, $group_id ) {
	$has_joined = UM()->Groups()->api()->has_joined_group( $user_id, $group_id );
	if( $has_joined ){
		unset( $menus['invite'] );
	}

	if( 'pending_member_review' == $has_joined ){
		$menus['resend_invite'] = __('<span class="um-faicon-check"></span> Invited','um-groups');
	}

	return $menus;
}
add_filter( 'um_groups_list_users_menu__invite_front','um_groups_list_users_menu__invite_front', 10, 4 );


/**
 * Modify User menus for Members tab by group role
 *
 * @param $menus
 * @param $user_id
 * @param $group_id
 * @param $has_joined
 * @param $args
 * @param $member
 *
 * @return mixed
 */
function um_groups_list_users_menu__approved( $menus, $user_id, $group_id, $has_joined, $args, $member ) {
	if ( UM()->Groups()->api()->has_joined_group( $user_id, $group_id ) ) {

		if( 'admin' == $member['group_role']['slug'] ){
			unset( $menus['make-admin'] );
			if( $user_id == get_current_user_id() ){
				unset( $menus['remove-from-group'] );
				$menus['remove-self-from-group'] = __("Leave Group","um-groups");
			}
		}else if( 'member' == $member['group_role']['slug'] ){
			unset( $menus['make-member'] );
		}else if( 'moderator' == $member['group_role']['slug'] ){
			unset( $menus['make-moderator'] );
		}
	}

	return $menus;
}
add_filter( 'um_groups_list_users_menu__approved', 'um_groups_list_users_menu__approved', 10, 6 );


/**
 * User Menus for Join Requests tab
 *
 * @param $args
 *
 * @return mixed
 */
function um_groups_user_lists_args__requests( $args ) {

	$args['load_more'] = 'requests';

	$privacy = UM()->Groups()->api()->get_privacy_slug( $args['group_id'] );

	$args['privacy'] = $privacy;

	if(  UM()->Groups()->api()->can_approve_requests( $args['group_id'], null, $privacy ) || um_groups_admin_all_access()  ){
  
		$args['menus'] = array(
			'approve' => __('Approve','um-groups'),
			'reject' => __('Reject','um-groups'),
			'block' => __('Block','um-groups'),

		);

	}


	return $args;
}
add_filter( 'um_groups_user_lists_args__requests', 'um_groups_user_lists_args__requests' );


/**
 * User Menus for Blocked Users tab
 *
 * @param $args
 *
 * @return array
 */
function um_groups_user_lists_args__blocked( $args ) {

	$args['load_more'] = 'blocked';

	$privacy = UM()->Groups()->api()->get_privacy_slug( get_the_ID() );

	$args = UM()->Groups()->api()->get_members( get_the_ID(), 'blocked' );

	if(  UM()->Groups()->api()->can_approve_requests( get_the_ID(), null, $privacy ) || um_groups_admin_all_access() ){

			$args['menus'] = array(
				'unblock' => __('Unblock','um-groups'),
			);
	}

	return $args;
}
add_filter( 'um_groups_user_lists_args__blocked', 'um_groups_user_lists_args__blocked' );