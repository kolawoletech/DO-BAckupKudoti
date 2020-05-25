<?php if ( ! defined( 'ABSPATH' ) ) exit;


/**
 * Email notification for approved group membership
 * @param  integer $user_id
 * @param  integer $group_id
 */
function um_groups_after_member_changed_status__approved( $user_id, $group_id ) {
	um_fetch_user( $user_id  );

	$member_address = um_user('user_email');
	$group_name = get_the_title( $group_id );
	$group_url = get_the_permalink( $group_id );

	UM()->mail()->send(
		$member_address,
		'groups_approve_member',
		array(
			'plain_text' => 1,
			'path' => um_groups_path . 'templates/email/',
			'tags' => array(
				'{group_name}',
				'{group_url}',
			),
			'tags_replace' => array(
				$group_name,
				$group_url,
			)
		)
	);
}
add_action( 'um_groups_after_member_changed_status__approved', 'um_groups_after_member_changed_status__approved', 10, 2 );


/**
 * Email notification for join request to a group
 *
 * @param  integer $user_id
 * @param  integer $group_id
 */
function um_groups_after_member_changed_status__pending_admin_review( $user_id, $group_id ) {
	global $wpdb;

	$group_name = get_the_title( $group_id );
	$group_url = get_the_permalink( $group_id );
	$groups_request_tab_url = add_query_arg( 'tab','requests', $group_url );
	$moderators = UM()->Groups()->member()->get_moderators( $group_id );

	um_fetch_user( $user_id );
	$member_name = um_user('display_name');
	$profile_link = um_user_profile_url( $user_id );

	foreach( $moderators as $key => $mod ){

		// moderator
		um_fetch_user( $mod->uid );
		$moderator_name = um_user('display_name');
		$moderator_address = um_user('user_email');

		UM()->mail()->send(
			$moderator_address,
			'groups_join_request',
			array(
				'plain_text' => 1,
				'path' => um_groups_path . 'templates/email/',
				'tags' => array(
					'{moderator_name}',
					'{member_name}',
					'{group_name}',
					'{group_url}',
					'{groups_request_tab_url}',
					'{profile_link}'
				),
				'tags_replace' => array(
					$moderator_name,
					$member_name,
					$group_name,
					$group_url,
					$groups_request_tab_url,
					$profile_link
				)
			)
		);
	}


}
add_action('um_groups_after_member_changed_status__pending_admin_review','um_groups_after_member_changed_status__pending_admin_review', 10, 2 );


/**
 * Email notification for join request to a group
 * @param  integer $user_id
 * @param  integer $group_id
 */
function um_groups_after_member_changed_status__pending_member_review( $user_id, $group_id, $invited_by_user_id = null ) {

	$group = get_post( $group_id );
	$group_name = get_the_title( $group_id );
	$group_url = get_the_permalink( $group_id );
	
	$group_invitation_host_id = empty( $invited_by_user_id ) ? $group->post_author : $invited_by_user_id;
	$group_invitation_host = get_userdata( $group_invitation_host_id );
	$group_invitation_host_name = $group_invitation_host->display_name;

	$group_invitation_guest = get_userdata( $user_id );
	$group_invitation_guest_name = $group_invitation_guest->display_name;
	$group_invitation_guest_email = $group_invitation_guest->user_email;
	
	UM()->mail()->send(
		$group_invitation_guest_email,
		'groups_invite_member',
		array(
			'plain_text' => 1,
			'path' => um_groups_path . 'templates/email/',
			'tags' => array(
				'{group_name}',
				'{group_url}',
				'{group_invitation_guest_name}',
				'{group_invitation_host_name}'
			),
			'tags_replace' => array(
				$group_name,
				$group_url,
				$group_invitation_guest_name,
				$group_invitation_host_name
			)
		)
	);

}
add_action( 'um_groups_after_member_changed_status__pending_member_review','um_groups_after_member_changed_status__pending_member_review', 10, 3 );


/**
 * Email notification to group members where someone posts on group
 * @param  integer $post_id
 * @param  integer $author_id
 * @param  integer $wall_id
 */
function um_groups_send_notification_to_group_members( $post_id, $author_id, $wall_id ) {
	global $wpdb;

	$option = um_get_option( 'groups_new_post_on' );
	if ( $option ):
		$table_name = UM()->Groups()->setup()->db_groups_table;
		$group_id = get_post_meta( $post_id, '_group_id', true );
		$sql = "SELECT user_id1 FROM $table_name WHERE group_id = $group_id AND status = 'approved'";
		$members = $wpdb->get_results( $sql );

		$members_ids = [];
		for ( $i = 0; $i < count( $members ); $i++ ) {
			if ( $author_id == $members[ $i ]->user_id1 ) { continue; }
			$members_ids[] = $members[ $i ]->user_id1;
		}

		um_fetch_user( $author_id );
		$author_name = um_user( 'display_name' );
		$author_photo = um_get_avatar_url( get_avatar( $author_id, 40 ) );
		$group_name = ucwords( get_the_title( $group_id ) );
		$group_url = get_the_permalink( $group_id );

		for ( $i = 0; $i < count( $members_ids ); $i++ ) {
			
			$post_notification_enabled = get_user_meta($members_ids[$i],'um_group_post_notification',true );
			if( ! $post_notification_enabled){ continue; }

			um_fetch_user( $members_ids[ $i ] );
			$member_address = um_user( 'user_email' );

			// email notification
			UM()->mail()->send( $member_address, 'groups_new_post', array(
				'plain_text'	 => 1,
				'path'				 => um_groups_path . 'templates/email/',
				'tags'				 => array(
					'{group_name}',
					'{group_url}',
					'{author_name}',
					'{author_photo}'
				),
				'tags_replace' => array(
					$group_name,
					$group_url,
					$author_name,
					$author_photo
				)
			) );
		} // end loop

	endif; // if option
}
add_action( 'um_groups_after_wall_post_published', 'um_groups_send_notification_to_group_members', 50, 3 );
add_action( 'um_groups_after_wall_post_updated', 'um_groups_send_notification_to_group_members', 50, 3 );

/**
 * Email notification to group members where someone comments on group
 * @param  integer $commentid
 * @param  integer $comment_parent
 * @param  integer $post_id
 * @param  integer $user_id
 */
function um_groups_after_user_comments( $commentid, $comment_parent, $post_id, $user_id ) {
	global $wpdb;

	$option = um_get_option( 'groups_new_comment_on' );
	if ( $option ):
		$group_id = get_post_meta( $post_id, '_group_id', true );
		$table_name = UM()->Groups()->setup()->db_groups_table;
		$sql = "SELECT user_id1 FROM $table_name WHERE group_id = $group_id AND status = 'approved'";
		$members = $wpdb->get_results( $sql );

		$members_ids = [];
		for ( $i = 0; $i < count( $members ); $i++ ) {
			if ( $user_id == $members[ $i ]->user_id1 ) {
				continue;
			}
			$members_ids[] = $members[ $i ]->user_id1;
		}

		um_fetch_user( $user_id );
		$author_name = um_user( 'display_name' );
		$author_photo = um_get_avatar_url( get_avatar( $user_id, 40 ) );
		$group_name = ucwords( get_the_title( $group_id ) );
		$group_url = get_the_permalink( $group_id );


		for ( $i = 0; $i < count( $members_ids ); $i++ ) {
			
			$comment_notification_enabled = get_user_meta($members_ids[$i],'um_group_comment_notification',true );
			if( ! $comment_notification_enabled){ continue; }

			um_fetch_user( $members_ids[ $i ] );
			$member_address = um_user( 'user_email' );

			// email notification
			UM()->mail()->send( $member_address, 'groups_new_comment', array(
				'plain_text'	 => 1,
				'path'				 => um_groups_path . 'templates/email/',
				'tags'				 => array(
					'{group_name}',
					'{group_url}',
					'{author_name}',
					'{author_photo}'
				),
				'tags_replace' => array(
					$group_name,
					$group_url,
					$author_name,
					$author_photo
				)
			) );
		} // end loop

	endif; // if option
}
add_action( 'um_groups_after_wall_comment_published', 'um_groups_after_user_comments', 10, 4 );
