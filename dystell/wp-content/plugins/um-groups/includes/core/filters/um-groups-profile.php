<?php if ( ! defined( 'ABSPATH' ) ) exit;


/**
 * Change title of the comment at the user's profile
 *
 * @param string $title
 * @param \WP_Comment $comment
 *
 * @return string
 */
function um_groups_comment_title( $title, $comment ) {
	$post_type = get_post_type( $comment->comment_post_ID );
	if ( $post_type == 'um_groups_discussion' ) {
		$comment_id = $comment->comment_post_ID;
		$group_id = get_post_meta( $comment_id, '_group_id', true );
		$title = get_the_title( $group_id );
	}

	return $title;
}
add_filter( 'um_user_profile_comment_title', 'um_groups_comment_title', 10, 2 );


/**
 * Change URL of the comment at the user's profile
 *
 * @param string $url
 * @param \WP_Comment $comment
 *
 * @return string
 */
function um_groups_comment_url( $url, $comment ) {
	$post_type = get_post_type( $comment->comment_post_ID );
	if ( $post_type == 'um_groups_discussion' ) {
		$comment_id = $comment->comment_post_ID;
		$group_id = get_post_meta( $comment_id, '_group_id', true );
		$url = get_permalink( $group_id ) . '/?tab=discussion#commentid-' . $comment_id;
	}

	return $url;
}
add_filter( 'um_user_profile_comment_url', 'um_groups_comment_url', 10, 2 );


/**
 * @param array $tabs
 *
 * @return array
 */
function um_groups_add_tabs( $tabs ) {

	$joined_groups = UM()->Groups()->api()->get_joined_groups( um_user( 'ID' ), 'pending_member_review' );

	$tabs['groups_list'] = array(
		'name'     => __( 'Groups', 'um-groups' ),
		'icon'     => 'um-faicon-users',
		'notifier' => count( $joined_groups )
	);

	return $tabs;
}
add_filter( 'um_profile_tabs', 'um_groups_add_tabs', 2000, 1 );


/**
 * Adds user-condition tab
 * @param array $tabs
 * @return array
 */
function um_groups_user_profile_tabs( $tabs ) {
	if ( um_user( 'groups_wall_off' ) ) {
		unset( $tabs['activity'] );
	}

	return $tabs;
}
add_filter( 'um_user_profile_tabs', 'um_groups_user_profile_tabs', 5, 1 );


/**
 * Add info about restrict content to group's wall posting
 *
 * @param array $output
 * @param $progress
 *
 * @return array
 */
function um_groups_profile_completeness_progress_output( $output, $progress ) {
	$output['prevent_group_post'] = $progress['prevent_group_post'];
	return $output;
}
add_filter( 'um_profile_completeness_progress_output', 'um_groups_profile_completeness_progress_output', 10, 2 );


/**
 * Add info about restrict content to group's wall posting
 *
 * @param array $output
 * @param $progress
 *
 * @return array
 */
function um_groups_profile_completeness_get_progress_result( $result, $role_data ) {
	$result['prevent_group_post'] = ! empty( $role_data['profilec_prevent_group_post'] ) ? $role_data['profilec_prevent_group_post'] : 0;
	return $result;
}
add_filter( 'um_profile_completeness_get_progress_result', 'um_groups_profile_completeness_get_progress_result', 10, 2 );