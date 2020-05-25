<?php
namespace um_ext\um_groups\core;


if ( ! defined( 'ABSPATH' ) ) exit;


/**
 * Class Groups_Taxonomies
 * @package um_ext\um_groups\core
 */
class Groups_Taxonomies {


	/**
	 * Groups_Taxonomies constructor.
	 */
	function __construct() {
		add_action( 'init',  array( &$this, 'create_taxonomies'), 9999 );
		add_action( 'do_meta_boxes', array( &$this, 'change_featured_image_metabox_title' ) );
		add_action( 'admin_menu', array( &$this,'remove_post_meta_box' ),  999999 );

		add_filter( 'post_row_actions', array( &$this,'remove_bulk_actions' ) );
		add_filter( 'admin_post_thumbnail_html', array( &$this,'change_group_featured_image_text' ) );
	}


	/**
	 * @param $actions
	 *
	 * @return mixed
	 */
	function remove_bulk_actions( $actions ) {
		global $current_screen;
		if ( $current_screen->post_type != 'um_groups' ) {
			return $actions;
		}
		unset( $actions['inline hide-if-no-js'] );

		return $actions;
	}


	/**
	 * Create post types
	 */
	function create_taxonomies() {
		register_post_type( 'um_groups', array(
				'labels' => array(
					'name' 					=> __( 'Groups' ),
					'singular_name' 		=> __( 'Group' ),
					'add_new' 				=> __( 'Add New' ),
					'add_new_item' 			=> __('Add New Group' ),
					'edit_item' 			=> __('Edit Group'),
					'not_found' 			=> __('You did not create any groups yet'),
					'not_found_in_trash'	=> __('Nothing found in Trash'),
					'search_items' 			=> __('Search groups')
				),
				'show_ui' 				=> true,
				'show_in_menu' 			=> true,
				'public' 				=> true,
				'publicly_queryable' 	=> true,
				'hierarchical'       	=> false,
				'menu_position'      	=> null,
				'menu_icon'      	=> 'dashicons-groups',
				'supports' 				=> array('title','editor','thumbnail'),
				'taxonomies' 			=> array('um_group_categories'),
				'rewrite'            	=> array( 'slug' => 'groups' ),
				'capability_type'    	=> 'page',
			)
		);

		// Group Categories

		$labels = array(
			'name'                       => _x( 'Group Categories', 'taxonomy general name' ),
			'singular_name'              => _x( 'Group Category', 'taxonomy singular name' ),
			'search_items'               => __( 'Search Group Categories' ),
			'popular_items'              => __( 'Popular Group Categories' ),
			'all_items'                  => __( 'All Group Categories' ),
			'parent_item'                => null,
			'parent_item_colon'          => null,
			'edit_item'                  => __( 'Edit Group Category' ),
			'update_item'                => __( 'Update Group Category' ),
			'add_new_item'               => __( 'Add New Group Category' ),
			'new_item_name'              => __( 'New Group Category Name' ),
			'separate_items_with_commas' => __( 'Separate group categories with commas' ),
			'add_or_remove_items'        => __( 'Add or remove group categories' ),
			'choose_from_most_used'      => __( 'Choose from the most used group categories' ),
			'not_found'                  => __( 'No group categories found.' ),
			'menu_name'                  => __( 'Group Categories' ),
		);

		$args = array(
				'hierarchical'					 => true,
				'labels'								 => $labels,
				'show_ui'								 => true,
				'show_admin_column'			 => false,
				'show_in_menu'					 => true,
				'update_count_callback'	 => '_update_post_term_count',
				'query_var'							 => false,
				'rewrite'								 => array(
						'slug' => 'um-group-categories' 
				),
		);

		register_taxonomy( 'um_group_categories', 'um_groups', $args );

		// Group Tags
		$labels = array(
				'name'											 => _x( 'Group Tags', 'taxonomy general name' ),
				'singular_name'							 => _x( 'Tag', 'taxonomy singular name' ),
				'search_items'							 => __( 'Search Group Tags' ),
				'popular_items'							 => __( 'Popular Group Tags' ),
				'all_items'									 => __( 'All Group Tags' ),
				'parent_item'								 => null,
				'parent_item_colon'					 => null,
				'edit_item'									 => __( 'Edit Group Tag' ),
				'update_item'								 => __( 'Update Group Tag' ),
				'add_new_item'							 => __( 'Add New Group Tag' ),
				'new_item_name'							 => __( 'New Group Tag Name' ),
				'separate_items_with_commas' => __( 'Separate group tags with commas' ),
				'add_or_remove_items'				 => __( 'Add or remove group tags' ),
				'choose_from_most_used'			 => __( 'Choose from the most used tags' ),
				'not_found'									 => __( 'No group tags found.' ),
				'menu_name'									 => __( 'Group Tags' ),
		);

		register_taxonomy( 'um_group_tags', 'um_groups', array(
				'hierarchical'					 => false,
				'labels'								 => $labels,
				'show_ui'								 => true,
				'update_count_callback'	 => '_update_post_term_count',
				'query_var'							 => true,
				'rewrite'								 => array(
						'slug' => 'um-group-tags'
				)
		) );
	}


	/**
	 * Change the featured image metabox link text
	 * @param  string $content Featured image link text
	 * @return string $content Featured image link text, filtered
	 */
	function change_group_featured_image_text( $content ) {
		if ( 'um_groups' === get_post_type() ) {
			$content = str_replace( 'Set featured image', __( 'Set Group Image', 'um-groups' ), $content );
			$content = str_replace( 'Remove featured image', __( 'Remove Group Image', 'um-groups' ), $content );
		}

		return $content;
	}


	/**
	 *
	 */
	function remove_post_meta_box() {
		remove_meta_box( 'slugdiv', 'um_groups', 'normal' );
	}


	/*
	 * Change the featured image metabox title text
	 */
	function change_featured_image_metabox_title() {
		remove_meta_box( 'postimagediv', 'um_groups', 'side' );
		add_meta_box( 'postimagediv', __( 'Group Image', 'um-groups' ), 'post_thumbnail_meta_box', 'um_groups', 'side' );
	}
}