<?php if ( ! defined( 'ABSPATH' ) ) exit;

/**
 * Show own groups query
 *
 * @param $query_args
 * @param $args
 *
 * @return mixed
 */
function um_prepare_groups_query_args( $query_args, $args ){

	$query_args['post_type'] = 'um_groups';

	if( isset( $args['author'] ) ){
		$query_args['author'] = $args['author'];
	}

	if( isset( $args['s'] ) ){
		$query_args['s'] = $args['s'];
	}

	if( isset( $args['_um_groups_filter'] ) ){
		$query_args['post__in'] = $args['post__in'];
	}


	if( um_is_core_page('user') || um_is_core_page('my_groups') || isset( $args['own_groups'] ) ){
	
		$groups = UM()->Groups()->member()->get_groups_joined( );
		$arr_groups = array();

		if( ! empty( $groups ) ){
			foreach ( $groups as $key => $value) {
				$arr_groups[ ] = $value->group_id;
			}
			$query_args['post__in'] = $arr_groups;
		}else{
			$query_args['post__in'] = array(0);
		
		}

	}

	return $query_args;
}
add_filter( 'um_prepare_groups_query_args', 'um_prepare_groups_query_args', 10, 2 );