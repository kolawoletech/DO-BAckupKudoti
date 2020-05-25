<?php if ( ! defined( 'ABSPATH' ) ) exit;


/**
 * Hide attachment files from the Media Library's overlay (modal) view
 * if they have a certain meta key set.
 *
 * @param array $query An array of query variables.
 *
 * @return array
 */
function um_groups_media_overlay_view( $query ) {
	// Bail if this is not the admin area.
	if ( ! is_admin() ) {
		return $query;
	}

	// Modify the query.
	$query['meta_query'] = [
		[
			'key'     => '_um_groups_avatar',
			'compare' => 'NOT EXISTS',
		]
	];

	return $query;
}
add_filter( 'ajax_query_attachments_args', 'um_groups_media_overlay_view' );


/**
 * Hide attachment files from the Media Library's list view
 * if they have a certain meta key set.
 * 
 * @param WP_Query $query The WP_Query instance (passed by reference).
 */
function um_groups_media_list_view( $query ) {
	// Bail if this is not the admin area.
	if ( ! is_admin() ) {
		return;
	}

	// Bail if this is not the main query.
	if ( ! $query->is_main_query() ) {
		return;
	}

	// Only proceed if this the attachment upload screen.
	$screen = get_current_screen();
	if ( ! $screen || 'upload' !== $screen->id || 'attachment' !== $screen->post_type ) {
		return;
	}

	// Modify the query.
	$query->set( 'meta_query', [
		[
			'key'     => '_um_groups_avatar',
			'compare' => 'NOT EXISTS',
		]
	]   );

	return;
}
add_action( 'pre_get_posts', 'um_groups_media_list_view', 10, 1 );