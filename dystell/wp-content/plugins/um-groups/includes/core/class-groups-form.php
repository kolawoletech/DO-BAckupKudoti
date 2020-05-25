<?php
namespace um_ext\um_groups\core;

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) exit;

class Groups_Form {

	var $form_process_successful;

	function __construct() {

		$this->post_form = array();
		$this->fields = array();
		$this->errors = array();
		$this->setup_fields();
		
		add_action('template_redirect', array( &$this, 'form_init' ), 1.33 );
		add_action('um_groups_form_process', array( $this, 'check_form' ) );
		
		
	}

	public function check_form(){

		if( ! is_user_logged_in() ) {
			wp_die( __("You are not allowed to do this action.") );
		}
	
	}

	function form_init(){


		if( isset( $_POST['um_groups_submit'] ) ){

			$this->post_form = $_POST;
			do_action('um_groups_form_process', $this->post_form );
			do_action('um_groups_publisher_errors_hook', $this->post_form );
			do_action('um_groups_publisher_process_form', $this->post_form );
		}

		if( isset( $_POST['um_groups_update'] ) ){

			$this->post_form = $_POST;
			do_action('um_groups_form_process', $this->post_form );
			do_action('um_groups_publisher_errors_hook', $this->post_form );
			do_action('um_groups_updater_process_form', $this->post_form );
		}

		if( isset( $_POST['um_groups_upload_avatar'] ) ){

			$this->post_form = $_POST;
			do_action('um_groups_form_process', $this->post_form );
			do_action('um_groups_upload_file_errors_hook', $this->post_form );
			do_action('um_groups_upload_file_process_form', $this->post_form );
		}

		if( isset( $_POST['um_groups_delete_avatar'] ) ){

			$this->post_form = $_POST;
			do_action('um_groups_form_process', $this->post_form );
			do_action('um_groups_delete_file_errors_hook', $this->post_form );
			do_action('um_groups_delete_file_process_form', $this->post_form );
		}

		if( isset( $_POST['um_groups_delete_group'] ) ){

			$this->post_form = $_POST;
			do_action('um_groups_form_process', $this->post_form );
			do_action('um_groups_delete_group_errors_hook', $this->post_form );
			do_action('um_groups_delete_group_process_form', $this->post_form );
		}
			
	}

	function setup_fields(){

		$this->fields[ ] = array(
			'label' => __('Title','um-groups'),
			'id' => 'um-groups-title',
			'meta_key' => 'title',
			'type' => 'text',
			'placeholder' => 'Enter title here',
			'class' => 'um-form-field valid',
			'required' => true,
		);

		$this->fields[ ] = array(
			'label' => __('Description','um-groups'),
			'id' => 'um-groups-content',
			'meta_key' => 'um-groups-content',
			'type' => 'textarea',
			'class' => 'um-groups-content',
			'wysiwyg' => true,
			'required' => false,
			'height' => 500,
		);
		
		$this->fields[ ] = array(
			'label' => __('Featured Image','um-groups'),
			'id' => 'um-groups-featured-image',
			'meta_key' => 'featured_image',
			'type' => 'image-picker',
			'class' => 'um-form-field valid',
			'required' => false,
		);

		$this->fields[ ] = array(
			'label' => __('Categories','um-groups'),
			'id' => 'um-groups-categories',
			'meta_key' => 'categories',
			'type' => 'select',
			'class' => 'um-form-field valid',
			'required' => false,
			'multiple' => true,
			'choices' => $this->choices('categories'),
		);

		$this->fields[ ] = array(
			'label' => __('Tags','um-groups'),
			'id' => 'um-groups-tags',
			'meta_key' => 'tags',
			'type' => 'select',
			'class' => 'um-form-field valid',
			'required' => false,
			'multiple' => true,
			'choices' => $this->choices('tags'),
		);

		$this->fields[ ] = array(
			'label' => __('Status','um-groups'),
			'id' => 'um-groups-status',
			'meta_key' => 'status',
			'type' => 'select',
			'class' => 'um-form-field valid',
			'required' => true,
			'choices' => $this->choices('status'),
		);

		

		$this->fields = apply_filters('um_groups_fields', $this->fields );

	}

	function choices( $type ){

		$options = array();

		switch ( $type ) {
			case 'categories':

				$taxonomy = apply_filters('um_groups_fields_categories_taxonomy', 'category' );

				$args = array(
					'hide_empty' => false,
				);

				$categories = get_terms( $taxonomy, $args );
 
				if ( is_wp_error( $categories ) ) {
					$categories = array();
				} else {
					$categories = (array) $categories;
					foreach ( array_keys( $categories ) as $k ) {
					    _make_cat_compat( $categories[ $k ] );
					}
				}

				$options = $this->prepare_options( $categories );
				
			
			break;

			case 'tags':
					
				$tags = get_tags();
				foreach ( $tags as $tag ) {
					$options[ $tag->term_id ] = $tag->name;
				}
				
				break;
			
		}

		return $options;

	}

	function prepare_options( $arr = array() ){
		
		$options = array();

		foreach ( $arr as $key => $option ) {
			$options[ $option->term_id ] = $option->name;
		}

		return $options;
	}

	function show_error( $text ){
		echo '<div class="um-field-error"><span class="um-field-arrow"><i class="um-faicon-caret-up"></i></span>'.$text.'</div>';
	}
}

