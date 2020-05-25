<?php
if( !defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Clear groups title and content in single page template
 *
 * @param WP_Post $the_post
 *
 * @return mixed
 */
function um_groups_single_page_template( $the_post ) {
	if( isset( $the_post ) && 'um_groups' == $the_post->post_type && is_single() ) {
		UM()->Groups()->api()->single_group_title = $the_post->post_title;
	}

	return $the_post;
}
add_action( 'the_post', 'um_groups_single_page_template' );


/**
 * Pre query list in shortcode
 *
 * @param $args
 */
function pre_groups_shortcode_query_list( $args ) {

	$search = get_query_var( 'groups_search' );
	$cat = get_query_var( 'cat' );
	$tags = get_query_var( 'tags' );
	$filter = get_query_var( 'filter' );

	if( 1 == $args[ 'show_search_form' ] ) {

		if( !empty( $search ) ) {
			$args[ 's' ] = $search;
		}

		if( !empty( $cat ) ) {
			$args[ 'cat' ] = $cat;
		}

		if( !empty( $tags ) ) {
			$args[ 'tags' ] = $tags;
		}
	}

	if( 'own' == $filter ) {
		$array_groups = array(
				0 );
		$groups = UM()->Groups()->member()->get_groups_joined();
		foreach( $groups as $data ) {
			$array_groups[] = $data->group_id;
		}
		$args[ '_um_groups_filter' ] = $filter;
		$args[ 'post__in' ] = $array_groups;
	}

	UM()->Groups()->api()->results = UM()->Groups()->api()->get_groups( $args );
}
add_action( 'pre_groups_shortcode_query_list', 'pre_groups_shortcode_query_list' );


/**
 * Group directory search form
 *
 * @param $args
 */
function um_groups_directory_search_form( $args ) {
	if( 0 == $args[ 'show_search_form' ] ) {
		return;
	}

	$cat = get_query_var( 'cat' );
	$filter = get_query_var( 'filter' );
	$search = get_query_var( 'groups_search' );
	$tags = get_query_var( 'tags' );

	$arr_categories = null;
	if( 1 == $args[ 'show_search_categories' ] ) {
		$arr_categories = um_groups_get_categories();
	}

	$arr_tags = null;
	if( 1 == $args[ 'show_search_tags' ] ) {
		$arr_tags = um_groups_get_tags();
	}

	$t_args = compact( 'args', 'cat', 'arr_categories', 'filter', 'search', 'tags', 'arr_tags' );
	UM()->get_template( 'directory/directory_search.php', um_groups_plugin, $t_args, true );

	wp_enqueue_script( 'um_groups' );
	wp_enqueue_style( 'um_groups' );
}
add_action( 'um_groups_directory_search_form', 'um_groups_directory_search_form' );


/**
 * Display groups directory
 *
 * @param $args
 */
function um_groups_directory( $args ) {

	$total_groups = um_groups( 'total_groups' );

	if( $total_groups ) {

		$groups = um_groups( 'groups' );

		$user_id = um_user( 'ID' );
		$joined_groups = UM()->Groups()->api()->get_joined_groups( $user_id, 'pending_member_review' );
		$joined_groups_ids = array_map( function($data) {
			return $data->group_id;
		}, $joined_groups );

		foreach( $groups as $i => $group ) {
			if( in_array( $group->ID, $joined_groups_ids ) ) {
				unset( $groups[ $i ] );
			}
		}

		if( $groups ) {
			$t_args = compact( 'args', 'groups', 'user_id' );
			UM()->get_template( 'directory/directory.php', um_groups_plugin, $t_args, true );

			wp_enqueue_script( 'um_groups' );
			wp_enqueue_style( 'um_groups' );
		}
	}

	if( !$total_groups ) {
		_e( 'No groups found.', 'um-groups' );
	}
}
add_action( 'um_groups_directory', 'um_groups_directory' );


/**
 * Display groups directory where user invited
 * @param array $args
 */
function um_groups_directory_confirm( $args ) {

	$user_id = um_user( 'ID' );
	$joined_groups = UM()->Groups()->api()->get_joined_groups( $user_id, 'pending_member_review' );

	$groups = array();
	foreach( $joined_groups as $data ) {
		$group = get_post( $data->group_id );
		$group->group_id = $data->group_id;
		$group->user_id2 = $data->user_id2;
		$groups[] = $group;
	}

	if( $groups ) {
		$t_args = compact( 'args', 'groups', 'user_id' );
		UM()->get_template( 'directory/directory_confirm.php', um_groups_plugin, $t_args, true );

		wp_enqueue_script( 'um_groups' );
		wp_enqueue_style( 'um_groups' );
	}
}
add_action( 'um_groups_directory_footer', 'um_groups_directory_confirm', 20 );


/**
 * Pagination
 *
 * @param $args
 */
function um_groups_directory_lazy_load( $args ) {

	if( um_groups( 'total_groups' ) > $args[ 'groups_per_page' ] && 1 == $args[ 'show_pagination' ] ) {

		$search = get_query_var( 'groups_search' );
		$cat = get_query_var( 'cat' );
		$tags = get_query_var( 'tags' );
		$filter = get_query_var( 'filter' );

		if( 1 == $args[ 'show_search_form' ] ) {

			if( !empty( $search ) ) {
				$args[ 's' ] = $search;
			}

			if( !empty( $cat ) ) {
				$args[ 'cat' ] = $cat;
			}

			if( !empty( $tags ) ) {
				$args[ 'tags' ] = $tags;
			}
		}

		if( 'own' == $filter ) {
			$args[ 'own_groups' ] = true;
		}

		echo "<div class='um-groups-list-pagination'>";
		echo "<a href='#' class='um-groups-lazy-load' data-groups-page='1' data-groups-pagi-settings='" . htmlspecialchars( json_encode( $args ) ) . "' data-load-more-text='" . __( "load more...", "um-groups" ) . "'  data-no-more-groups-text='" . __( "No more groups to show", "um-groups" ) . "' >";
		_e( "load more...", "um-groups" );
		echo "</a>";
		echo "</div>";

		wp_enqueue_script( 'um_groups' );
		wp_enqueue_style( 'um_groups' );
	}
}
add_action( 'um_groups_directory_footer', 'um_groups_directory_lazy_load' );


/**
 * Groups directory tabs
 *
 * @param $args
 */
function um_groups_directory_tabs( $args ) {

	if( false == $args[ 'show_total_groups_count' ] || um_is_core_page( 'my_groups' ) ) {
		return;
	}

	$filter = get_query_var( 'filter' );

	$t_args = compact( 'filter' );
	UM()->get_template( 'directory/directory_tabs.php', um_groups_plugin, $t_args, true );

	wp_enqueue_script( 'um_groups' );
	wp_enqueue_style( 'um_groups' );
}
add_action( 'um_groups_directory_tabs', 'um_groups_directory_tabs' );


/**
 * Own groups directory tabs
 *
 * @param $args
 */
function um_groups_own_directory_tabs( $args ) {

	if( um_is_core_page( 'my_groups' ) ) {
		return;
	}

	echo '<div class="um-groups-found-own-posts">' . sprintf( __( 'All Groups <span>%s</span>', 'um-groups' ), UM()->Groups()->api()->get_own_groups_count() ) . '</div>';

	wp_enqueue_script( 'um_groups' );
	wp_enqueue_style( 'um_groups' );
}
add_action( 'um_groups_own_directory_tabs', 'um_groups_own_directory_tabs' );
