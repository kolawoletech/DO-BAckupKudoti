<?php
/**
 * Uninstall UM Groups
 *
 */

// Exit if accessed directly.
if ( ! defined( 'WP_UNINSTALL_PLUGIN' ) ) {
	exit;
}


if ( ! defined( 'um_groups_path' ) ) {
	define( 'um_groups_path', plugin_dir_path( __FILE__ ) );
}

if ( ! defined( 'um_groups_url' ) ) {
	define( 'um_groups_url', plugin_dir_url( __FILE__ ) );
}

if ( ! defined( 'um_groups_plugin' ) ) {
	define( 'um_groups_plugin', plugin_basename( __FILE__ ) );
}

$options = get_option( 'um_options', array() );

if ( ! empty( $options['uninstall_on_delete'] ) ) {
	if ( ! class_exists( 'um_ext\um_groups\core\Groups_Setup' ) ) {
		require_once um_groups_path . 'includes/core/class-groups-setup.php';
	}

	$groups_setup = new um_ext\um_groups\core\Groups_Setup();

	//remove settings
	foreach ( $groups_setup->settings_defaults as $k => $v ) {
		unset( $options[ $k ] );
	}

	unset( $options['um_groups_license_key'] );

	update_option( 'um_options', $options );

	delete_option( 'ultimatemember_groups_db' );
	delete_option( 'um_groups_last_version_upgrade' );
	delete_option( 'um_groups_version' );
	delete_option( 'widget_um_my_groups' );
	delete_option( 'um_group_categories_children' );

	//remove tables
	global $wpdb;

	$wpdb->query( "DROP TABLE IF EXISTS {$wpdb->prefix}um_groups_members" );

	$groups_pages = $wpdb->get_results( "
		SELECT post_id
		FROM {$wpdb->postmeta}
		WHERE meta_key = '_um_core' AND
			  meta_value = 'groups' OR 
			  meta_value = 'create_group' OR 
			  meta_value = 'my_groups' OR 
			  meta_value = 'group_invites'",
	ARRAY_A );

	foreach ( $groups_pages as $page_id ) {
		wp_delete_post( $page_id['post_id'], 1 );
	}

	$wpdb->query( 'SET SQL_BIG_SELECTS=1' );

	$wpdb->query( "
		DELETE posts, term_rel, pmeta, terms, tax, commetns
		FROM {$wpdb->posts} posts
		LEFT JOIN {$wpdb->term_relationships} term_rel ON (posts.ID = term_rel.object_id)
		LEFT JOIN {$wpdb->postmeta} pmeta ON (posts.ID = pmeta.post_id)
		LEFT JOIN {$wpdb->terms} terms ON (term_rel.term_taxonomy_id = terms.term_id)
		LEFT JOIN {$wpdb->term_taxonomy} tax ON (term_rel.term_taxonomy_id = tax.term_taxonomy_id)
		LEFT JOIN {$wpdb->comments} commetns ON (commetns.comment_post_ID = posts.ID)
		WHERE posts.post_type = 'um_groups' OR 
		      posts.post_type = 'um_groups_discussion'"
	);
}