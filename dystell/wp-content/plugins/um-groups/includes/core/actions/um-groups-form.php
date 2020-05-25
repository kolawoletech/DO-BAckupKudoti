<?php if ( !defined( 'ABSPATH' ) ) exit;


/**
 * Build settings
 *
 * @global WP_Post $post
 * @param array $args
 */
function um_groups_create_form( $args ) {
	global $um_group, $um_group_id;

	if ( isset( $args['group_id'] ) ) {
		$um_group_id = $args['group_id'];
		$um_group = $args['group'] = get_post( $um_group_id );
	}

	$categories = get_categories( array(
		'taxonomy'      => 'um_group_categories',
		'hide_empty'    => 0
	) );

	$tags = get_categories( array(
		'taxonomy'      => 'um_group_tags',
		'hide_empty'    => 0
	) );

	if ( isset( $um_group ) && is_a( $um_group, 'WP_Post' ) && $um_group->post_type === 'um_groups' ) {

		$group_categories = wp_get_object_terms( $um_group_id, 'um_group_categories' );
		$group_categories_ids = array_map( function( $term ) {
			return $term->term_id;
		}, $group_categories );

		$group_tags = wp_get_object_terms( $um_group_id, 'um_group_tags' );
		$group_tags_ids = array_map( function( $term ) {
			return $term->term_id;
		}, $group_tags );

		$group = $um_group;
	} else {
		$group = ( object ) array(
			'post_title'                    => '',
			'post_content'                  => '',
			'post_type'                     => 'um_groups',
			'_um_groups_privacy'            => 'public',
			'_um_groups_posts_moderation'   => 'auto-published',
			'_um_groups_can_invite'         => '0',
			'_um_groups_invites_settings'   => '1',
		);
		$group_categories_ids = array();
		$group_tags_ids = array();
	}

	wp_enqueue_script( 'um_groups' );
	wp_enqueue_style( 'um_groups' );

	$t_args = compact( 'categories', 'group', 'group_categories_ids', 'tags', 'group_tags_ids' );
	UM()->get_template( 'create-group-form.php', um_groups_plugin, $t_args, true );
}

add_action( 'um_groups_create_form', 'um_groups_create_form', 10, 1 );
