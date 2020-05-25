<?php if ( ! defined( 'ABSPATH' ) ) exit;


/**
 * Debug with js dump
 * @param  array  $args 
 */
function um_groups_js_dump( $args = array() ){
	echo "<script>console.log(".json_encode( $args ).");</script>";
}


/**
 * Get privacy icon
 *
 * @param  int $group_id
 * @return string
 */
function um_groups_get_privacy_icon( $group_id = 0 ){
	$privacy = UM()->Groups()->api()->get_privacy_slug( $group_id );
	return UM()->Groups()->api()->get_privacy_icon( $privacy );
}


/**
 * Get privacy title
 *
 * @param  integer $group_id 
 * @return string            
 */
function um_groups_get_privacy_title( $group_id = 0 ){
	$privacy = UM()->Groups()->api()->get_privacy_slug( $group_id );
	return UM()->Groups()->api()->get_privacy_title( $privacy );
}


/**
 * Get group total member
 *
 * @param  integer $group_id 
 * @return integer       
 */
function um_groups_get_member_count( $group_id = 0, $cache = false ){
	$total_members = UM()->Groups()->api()->count_members( $group_id, $cache, 'approved' );
	return $total_members;
}


/**
 * Get group total pending admin review members
 * @param  integer $group_id 
 * @return integer           
 */
function um_groups_get_join_request_count_by_admin( $group_id = 0, $cache = false  ){
	$total_members = UM()->Groups()->api()->count_members( $group_id, $cache, 'pending_admin_review' );
	return $total_members;
}


/**
 * Get group total pending member review members
 * @param  integer $group_id 
 * @return integer           
 */
function um_groups_get_join_request_count_by_member( $group_id = 0, $cache = false  ){
	$total_members = UM()->Groups()->api()->count_members( $group_id, $cache, 'pending_member_review' );
	return $total_members;
}


/**
 * Get banned member count
 * @param  integer $group_id 
 * @param  boolean $cache    
 * @return integer
 */
function um_groups_get_banned_member_count( $group_id = 0, $cache = false  ){
	$total_members = UM()->Groups()->api()->count_members( $group_id, $cache, 'blocked' );
	return $total_members;
}


/**
 * Get group directory results
 *
 * @param $key
 *
 * @return mixed
 */
function um_groups( $key ) {
	return UM()->Groups()->api()->results[ $key ];
}


/**
 * Allow admin to access all groups and tabs
 * @return boolean
 */
function um_groups_admin_all_access() {
	if ( is_admin() && current_user_can( 'manage_options' ) ) {
		return true;
	}

	return false;
}


/**
 * Upload group avatar
 * @param  array  $file 
 * @return boolean  
 */
function um_groups_upload_user_file( $file = array() ) {

	require_once( ABSPATH . 'wp-admin/includes/admin.php' );

	$file_return = wp_handle_upload( $file, array('test_form' => false ) );

	if( isset( $file_return['error'] ) || isset( $file_return['upload_error_handler'] ) ) {
		return false;
	} else {

		$filename = $file_return['file'];

		$wp_filetype = wp_check_filetype( basename( $filename ), null );

		$arr_image_mime_types = array('image/jpeg','image/png', 'image/jpg');

		$arr_accepted_filetype = apply_filters('um_groups_accepted_upload_file_types', $arr_image_mime_types );

		if( !in_array( $wp_filetype['type'], $arr_accepted_filetype ) ){

			UM()->form()->add_error('um_groups_avatar', __('You must upload an image file.','um-groups') );

			return false;
		}

		$attachment = array(
			'post_mime_type' => $wp_filetype['type'],
			'post_title' => preg_replace( '/\.[^.]+$/', '', basename( $filename ) ),
			'post_content' => '',
			'post_status' => 'inherit',
			'guid' => $file_return['url']
		);

		$attachment_id = wp_insert_attachment( $attachment, $filename );

		require_once(ABSPATH . 'wp-admin/includes/image.php');

		$attachment_data = wp_generate_attachment_metadata( $attachment_id, $filename );

		update_post_meta( $attachment_id, '_um_groups_avatar', $filename );

		wp_update_attachment_metadata( $attachment_id, $attachment_data );

		if ( 0 < intval( $attachment_id ) ) {
			return $attachment_id;
		}
	}
	return false;
}


/**
 * Get all group categories
 * @return array
 */
function um_groups_get_categories() {
	$arr = array();

	$cats = get_terms( array(
		'taxonomy' => 'um_group_categories',
		'hide_empty' => false,
	) );

	if ( ! empty( $cats ) && ! is_wp_error( $cats ) ) {
		foreach( $cats as $cat ){
			$arr[ $cat->term_id ] = $cat->name;
		}
	}

	return $arr;
}


/**
 * Get all group tags
 * @return array
 */
function um_groups_get_tags() {
	$arr = array( );

	$tags = get_terms( array(
		'taxonomy' => 'um_group_tags',
		'hide_empty' => false,
	) );

	if ( ! empty( $tags ) && ! is_wp_error( $tags ) ) {
		foreach ( $tags as $tag ) {
			$arr[ $tag->term_id ] = $tag->name;
		}
	}

	return $arr;
}


/**
 * Get create group URL
 * @return string
 */
function um_groups_get_create_group_url(){
	$create_group_page_id = UM()->options()->get( 'core_create_group' );
	$create_group_url = get_permalink( $create_group_page_id );
	return $create_group_url;
}


/**
 * Get own groups count
 * @param  integer $user_id 
 * @return integer   
 */
function um_groups_get_own_groups_count( $user_id = null ){
	if ( ! $user_id ) {
		$user_id = get_current_user_id();
	}

	$groups = UM()->Groups()->member()->get_groups_joined( $user_id );
	return count( $groups );
}


/**
 * Get all groups count
 *
 * @return integer
 */
function um_groups_get_all_groups_count() {
	$count_groups = wp_count_posts( 'um_groups' );

	$private_groups = get_posts( array(
		'post_type'     => 'um_groups',
		'post_status'   => 'publish',
		'numberposts'   => -1,
		'meta_query'    => array(
			array(
				'key'       => '_um_groups_privacy',
				'value'     => 'hidden',
				'compare'   => '='
			)
		)
	) );


	if ( ! is_user_logged_in() ) {
		$private_groups = get_posts( array(
			'post_type'     => 'um_groups',
			'post_status'   => 'publish',
			'numberposts'   => -1,
			'meta_query'    => array(
				array(
					'key'       => '_um_groups_privacy',
					'value'     => 'hidden',
					'compare'   => '='
				)
			),
			'fields' => 'ids'
		) );

		$private_groups = ! empty( $private_groups ) ? count( $private_groups ) : 0;
	} else {
		$groups_joined = UM()->Groups()->member()->get_groups_joined( get_current_user_id() );
		$groups_joined = array_map( function( $item ) {
			return (int) $item->group_id;
		}, $groups_joined );

		$private_groups = get_posts( array(
			'post_type'     => 'um_groups',
			'post_status'   => 'publish',
			'numberposts'   => -1,
			'meta_query'    => array(
				array(
					'key'       => '_um_groups_privacy',
					'value'     => 'hidden',
					'compare'   => '='
				)
			),
			'fields' => 'ids'
		) );
		$private_groups = ! empty( $private_groups ) ? $private_groups : array();

		$private_groups = array_diff( $private_groups, $groups_joined );
		$private_groups = ! empty( $private_groups ) ? count( $private_groups ) : 0;
	}

	return $count_groups->publish - $private_groups;
}


if ( !function_exists( 'um_compress_html' ) ) {

	/**
	 * Compress HTML string. 
	 * Use to reduce response size from AJAX requests
	 * @param string $html
	 * @return string
	 */
	function um_compress_html( $html ) {
		return preg_replace( array( '/^\s+/im', '/\s+$/im', '/(\\r\\n)+/im', '/(\\n)+/im', '/(\\t)+/im' ), array( '', '', ' ', ' ', ' ' ), $html );
	}

}