<?php
namespace um_ext\um_profile_completeness\core;


if ( ! defined( 'ABSPATH' ) ) exit;


/**
 * Class Profile_Completeness_Admin
 * @package um_ext\um_profile_completeness\core
 */
class Profile_Completeness_Admin {


	/**
	 * Profile_Completeness_Admin constructor.
	 */
	function __construct() {
		add_action( 'admin_enqueue_scripts',  array( &$this, 'admin_enqueue_scripts' ), 9 );
		add_filter( 'um_settings_structure', array( &$this, 'profile_completeness_settings' ) , 10, 1 );

		add_filter( 'um_admin_role_metaboxes', array( &$this, 'add_role_metabox' ), 10, 1 );

		add_filter( 'manage_users_columns', array( &$this, 'manage_users_columns' ) );

		add_filter( 'manage_users_custom_column', array( &$this, 'manage_users_custom_column' ), 10, 3 );

		add_filter( 'manage_users_sortable_columns', array( &$this, 'manage_users_sortable_columns' ), 10, 1 );

		add_action( 'pre_get_users', array( &$this, 'manage_users_orderby' ), 10, 1 );
	}


	/**
	 * admin styles
	 */
	function admin_enqueue_scripts() {
		if ( UM()->admin()->is_um_screen() ) {
			wp_register_script( 'um_admin_profile_completeness', um_profile_completeness_url . 'includes/admin/assets/js/um-admin-profile-completeness.js', array( 'jquery' ), um_profile_completeness_version, true );
			wp_register_style( 'um_admin_profile_completeness', um_profile_completeness_url . 'includes/admin/assets/css/um-admin-profile-completeness.css', array(), um_profile_completeness_version );

			wp_enqueue_script( 'um_admin_profile_completeness' );
			wp_enqueue_style( 'um_admin_profile_completeness' );
		}
	}


	/**
	 * @param $settings
	 *
	 * @return mixed
	 */
	function profile_completeness_settings( $settings ) {
		$settings['licenses']['fields'][] = array(
			'id'        => 'um_profile_completeness_license_key',
			'label'     => __( 'Profile Completeness License Key', 'um-profile-completeness' ),
			'item_name' => 'Profile Completeness',
			'author'    => 'Ultimate Member',
			'version'   => um_profile_completeness_version,
		);

		return $settings;
	}


	/**
	 * A widget in user role page
	 *
	 * @param $roles_metaboxes
	 *
	 * @return array
	 */
	function add_role_metabox( $roles_metaboxes ) {
		$roles_metaboxes[] = array(
			'id'        => "um-admin-form-profilecompleteness{" . um_profile_completeness_path . "}",
			'title'     => __( 'Profile Completeness', 'um-profile-completeness' ),
			'callback'  => array( UM()->metabox(), 'load_metabox_role' ),
			'screen'    => 'um_role_meta',
			'context'   => 'side',
			'priority'  => 'default'
		);

		return $roles_metaboxes;
	}


	/**
	 * Filter: Add column 'Status'
	 *
	 * @param array $columns
	 *
	 * @return array
	 */
	public function manage_users_columns( $columns ) {
		$columns['profile_completeness'] = __( 'Profile Completeness', 'um-profile-completeness' );
		return $columns;
	}


	/**
	 * Filter: Show column 'Status'
	 *
	 * @param string $val
	 * @param string $column_name
	 * @param int $user_id
	 *
	 * @return string
	 */
	public function manage_users_custom_column( $val, $column_name, $user_id ) {
		if ( $column_name == 'profile_completeness' ) {
			$progress = UM()->Profile_Completeness_API()->get_progress( $user_id );
			$val = ! isset( $progress['progress'] ) ? ' - ' : $progress['progress'] . '%';
			return $val;
		}
		return $val;
	}


	/**
	 * Sortable column
	 *
	 * @param $columns
	 *
	 * @return mixed
	 */
	function manage_users_sortable_columns( $columns ) {
		$columns['profile_completeness'] = 'progress';
		return $columns;
	}


	/**
	 * @param \WP_User_Query $query
	 */
	function manage_users_orderby( $query ) {
		if ( ! is_admin() ) {
			return;
		}

		if ( 'progress' === $query->get( 'orderby' ) ) {

			$query->set( 'meta_query', array(
				array(
					'relation' => 'OR',
					'complete_val' => array(
						'key'       => '_completed',
						'compare'   => 'EXISTS'
					),
					'no_complete' => array(
						'key'       => '_completed',
						'compare'   => 'NOT EXISTS'
					),
				),
			) );

			$query->set( 'orderby', array( 'no_complete' => $query->query_vars['order'], 'complete_val' => $query->query_vars['order'] ) );
			unset( $query->query_vars['meta_key'] );
			unset( $query->query_vars['meta_value'] );
			unset( $query->query_vars['meta_compare'] );
			unset( $query->query_vars['order'] );
		}
	}
}