<?php if ( ! defined( 'ABSPATH' ) ) exit;

/**
 * Create new group post type on group creation
 *
 * @param $args
 * @param $group_id
 */
function um_activity_groups_after_front_insert( $args, $group_id ){

	if ( ! class_exists('UM_Activity_API') ) {
		return;
	}
	if ( ! UM()->options()->get('activity-new-group') ) {
		return;
	}

	$user_id = get_current_user_id();
	um_fetch_user( $user_id );
	$author_name = um_user('display_name');
	$author_profile = um_user_profile_url();
		
	UM()->Activity_API()->api()->save(
		array(
			'template' => 'new-group',
			'wall_id' => 0,
			'group_id' => $group_id,
			'author' => $user_id,
			'group_name' => ucwords( get_the_title( $group_id ) ),
			'group_permalink' => get_the_permalink( $group_id ),
			'group_author_name' => $author_name,
			'group_author_profile' => $author_profile,
		)
	);
}
add_action( 'um_groups_after_front_insert', 'um_activity_groups_after_front_insert', 10, 2 );