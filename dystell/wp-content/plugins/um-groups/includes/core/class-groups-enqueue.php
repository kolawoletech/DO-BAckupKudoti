<?php
namespace um_ext\um_groups\core;


// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) exit;


/**
 * Class Groups_Enqueue
 * @package um_ext\um_groups\core
 */
class Groups_Enqueue {


	/**
	 * Groups_Enqueue constructor.
	 */
	function __construct() {
		add_action( 'admin_enqueue_scripts', array( &$this, 'admin_enqueue_scripts' ), 10, 1 );
		add_action( 'wp_enqueue_scripts', array( &$this, 'public_enqueue_scripts' ), 10, 1 );

		add_filter( 'um_admin_enqueue_localize_data', array( &$this, 'localize_data' ), 10, 1 );
		add_filter( 'um_enqueue_localize_data', array( &$this, 'localize_data' ), 10, 1 );
	}


	/**
	 * @param $hook
	 */
	function admin_enqueue_scripts( $hook ) {
		global $post;

		if ( 'post-new.php' == $hook || 'post.php' == $hook || 'edit.php' == $hook ) {
			if ( isset( $post->post_type ) && 'um_groups' === $post->post_type ) {

				wp_register_script('jquery-datatables', um_groups_url . 'assets/js/jquery.dataTables.js', array( 'jquery' ), um_groups_version, true );
				wp_register_script('um_groups_admin', um_groups_url . 'admin/assets/js/um-groups-admin.js', array( 'jquery', 'wp-util', 'suggest', 'jquery-datatables', 'um_admin_global' ), um_groups_version, true );

				wp_register_style('jquery-datatables', um_groups_url . 'assets/css/jquery.dataTables.css', array(), um_groups_version );
				wp_register_style('um_groups_admin', um_groups_url . 'admin/assets/css/um-groups-admin.css', array( 'jquery-datatables' ), um_groups_version );

				wp_enqueue_script( 'um_groups_admin' );
				wp_enqueue_style( 'um_groups_admin' );
			}
		}
	}


	/**
	 *
	 */
	function public_enqueue_scripts() {
		$suffix = ( defined( 'SCRIPT_DEBUG' ) && SCRIPT_DEBUG || defined( 'UM_SCRIPT_DEBUG' ) ) ? '' : '.min';

		// public
		wp_register_script( 'um_groups', um_groups_url . 'assets/js/um-groups' . $suffix . '.js', array( 'jquery', 'wp-util', 'um_scripts', 'wp-i18n', 'jquery-ui-slider', 'wp-hooks', 'select2', 'um_members' ), um_groups_version, true );

		$dependencies = array( 'um_groups_members' );
		wp_register_style( 'um_groups_members', um_groups_url . 'assets/css/um-members' . $suffix . '.css', array(), um_groups_version );
		if ( is_rtl() ) {
			wp_register_style( 'um_groups_members_rtl', um_groups_url . 'assets/css/um-members-rtl' . $suffix . '.css', array( 'um_groups_members' ), um_groups_version );
			$dependencies = array( 'um_groups_members_rtl' );
		}

		$dependencies[] = 'um_ui';

		wp_register_style( 'um_groups', um_groups_url . 'assets/css/um-groups' . $suffix . '.css', $dependencies, um_groups_version );

		// discussion
		wp_register_script( 'um_autosize', um_groups_url . 'assets/js/autoresize-mod.jquery.js', array( 'jquery' ), um_groups_version, true );
		wp_register_script( 'um_scrollto', um_groups_url . 'assets/js/um-scrollto' . $suffix . '.js', array( 'jquery' ), um_groups_version, true );

		wp_register_script( 'um_groups_discussion', um_groups_url . 'assets/js/um-groups-discussion' . $suffix . '.js', array( 'jquery', 'wp-util', 'um_scripts', 'jquery-ui-autocomplete', 'um_autosize' ), um_groups_version, true );
		wp_register_style( 'um_groups_discussion', um_groups_url . 'assets/css/um-groups-discussion' . $suffix . '.css', array(), um_groups_version );

		if ( 'um_groups' == get_post_type() ) {
			wp_enqueue_script( 'um_groups' );
			wp_enqueue_style( 'um_groups' );
			wp_enqueue_script( 'um_groups_discussion' );
			wp_enqueue_style( 'um_groups_discussion' );
		}
	}


	/**
	 * @param $data
	 *
	 * @return mixed
	 */
	function localize_data( $data ) {
		$data['groups_settings'] = array(
			'statuses' 			=> UM()->Groups()->api()->get_member_statuses(),
			'roles'				=> UM()->Groups()->api()->get_member_roles(),
			'privacy_options' 	=> UM()->Groups()->api()->privacy_options,
			'privacy_icons'		=> UM()->Groups()->api()->privacy_icons,
			'can_invite' 		=> UM()->Groups()->api()->can_invite,
			'posts_moderation' 	=> UM()->Groups()->api()->group_posts_moderation_options,
			'labels' => array(
				'all'				=> __('All','um-groups'),
				'approve'			=> __('Approve','um-groups'),
				'block'				=> __('Block','um-groups'),
				'display'			=> __('Display','um-groups'),
				'expel'				=> __('Expel','um-groups'),
				'invite' 			=> __('Invite','um-groups'),
				'invited' 			=> __('Invited','um-groups'),
				'length_menu' 		=> __('Display _MENU_ profiles per page','um-groups'),
				'members'			=> __('members','um-groups'),
				'no_members_found'	=> __('No members found','um-groups'),
				'no_members_yet'	=> __('No members yet','um-groups'),
				'pagination_info'	=> __('Showing _START_ to _END_ of _TOTAL_ members','um-groups'),
				'pagination_info_empty'		=> __('Showing 0 to 0 of 0 members','um-groups'),
				'pagination_info_filtered'	=> __('(filtered from _MAX_ total members)','um-groups'),
				'processing'		=> __('Loading members...','um-groups'),
				'resend'			=> __('Resend','um-groups'),
				'reject'			=> __('Reject','um-groups'),
				'search_name'		=> __('Search name:','um-groups'),
				'resent_invite'		=> __('Sent','um-groups'),
				'unblock'			=> __('Unblock','um-groups'),
				'went_wrong'		=> __('Something went wrong. Please try again','um-groups'),
			)
		);

		return $data;
	}
}
