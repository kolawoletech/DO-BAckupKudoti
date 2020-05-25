<?php
namespace um_ext\um_groups\core;


// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) exit;


/**
 * Class Groups_Setup
 * @package um_ext\um_groups\core
 */
class Groups_Setup {


	/**
	 * @var array
	 */
	var $settings_defaults;


	/**
	 * @var
	 */
	var $global_actions;


	/**
	 * Groups_Setup constructor.
	 */
	function __construct() {
		global $wpdb;

		$this->global_actions['status']                 = __('New wall post','um-activity');
		$this->global_actions['new-user']               = __('New user','um-activity');
		$this->global_actions['new-post']               = __('New blog post','um-activity');
		$this->global_actions['new-product']            = __('New product','um-activity');
		$this->global_actions['new-gform']              = __('New Gravity From','um-activity');
		$this->global_actions['new-gform-submission']   = __('New Gravity From Answer','um-activity');
		$this->global_actions['new-follow']             = __('New follow','um-activity');
		$this->global_actions['new-topic']              = __('New forum topic','um-activity');

		$this->db_groups_table = $wpdb->prefix . 'um_groups_members';

		 //settings defaults
		$this->settings_defaults = array(

			// Join Request - Email template
			'groups_join_request_on'        => 1,
			'groups_join_request_sub'       => '{site_name} - Join Request',
			'groups_join_request'           => 'Hi {display_name},<br /><br />' .
					'{display_name} has requested to join {group_name}. You can view their profile here: {profile_link}<br /><br />' .
					'To approve/reject this request please click the following link: {groups_request_tab_url}<br /><br />',

			// Request Approved - Email Template
			'groups_approve_member_on'      => 1,
			'groups_approve_member_sub'     => '{site_name} - Your request to join {group_name} has been approved.',
			'groups_approve_member'         => 'Your request to join {group_name} has been approved.<br /><br />{group_url}',

			// New post - Email Template
			'groups_new_post_on'      => 1,
			'groups_new_post_sub'     => '{site_name} - {author_name} added a new post on group {group_name}',

			// New comment - Email Template
			'groups_new_comment_on'      => 1,
			'groups_new_comment_sub'     => '{site_name} - {author_name} added a new comment on post on group "{group_name}"',

			// Invited - Email Template
			'groups_invite_member_on'       => 1,
			'groups_invite_member_sub'      => '{site_name} - You have been invited to join {group_name}',
			'groups_invite_member'          => 'Hi {group_invitation_host_name},<br /><br />'.
					'{group_invitation_guest_name} has invited you to join {group_name}.<br /><br />'.
					'To confirm/reject this invitation please click the following link: {group_url}',

			'groups_invite_people'          => 'everyone',
			'groups_show_avatars'           => 1,

			// Discussion settings
			'groups_posts_num'              => 10,
			'groups_posts_num_mob'          => 5,
			'groups_init_comments_count'    => 2,
			'groups_load_comments_count'    => 10,
			'groups_order_comment'          => 'asc',
			'groups_post_truncate'          => 25,
			'groups_enable_privacy'         => 1,
			'groups_trending_days'          => 7,
			'groups_require_login'          => 0,
			'groups_need_to_login'          => __( 'Please <a href="{register_page}" class="um-link">sign up</a> or <a href="{login_page}" class="um-link">sign in</a> to see group activity.','um-activity'),
			'groups_highlight_color'        => '#0085ba',
			
			// Notification settings
			'um_group_post_notification'		=> 1,
			'um_group_comment_notification'		=> 1,

		);


		// Real-time notification integration's default logs
		foreach ( $this->get_log_types() as $k => $desc ) {
			$this->settings_defaults[ 'log_' . $k ] = 1;
			$this->settings_defaults[ 'log_' . $k . '_template' ] = $desc['template'];
		}

		foreach ( apply_filters( 'um_groups_discussion_global_actions', $this->global_actions ) as $k => $v ) {
			if ( $k == 'status' ) {
				continue;
			}

			$this->settings_defaults[ 'groups-discussion-' . $k ] = 1;
		}

	}


	/**
	 * Get default notification log templates
	 * @return array
	 */
	function get_log_types() {
		$array = array();

		$array['groups_approve_member'] = array(
			'title'         => __( 'Groups - Approve Member', 'um-groups' ),
			'template'      => __( 'Your request to join {group_name} have been approved.', 'um-groups' ),
			'account_desc'  => __( 'When my group requests have been approved', 'um-groups' ),
		);


		$array['groups_join_request'] = array(
			'title'         => __( 'Groups - Join Request', 'um-groups' ),
			'template'      => __( '{member_name} has requested to join {group_name}.', 'um-groups' ),
			'account_desc'  => __( 'When a user requested to join their group', 'um-groups' ),
		);


		$array['groups_invite_member'] = array(
			'title'         => __( 'Groups - Invite Member', 'um-groups' ),
			'template'      => __( '{group_invitation_host_name} has invited you to join {group_name}.', 'um-groups' ),
			'account_desc'  => __( 'When a member has invited to join a group', 'um-groups' ),
		);

		$array['groups_change_role'] = array(
			'title'         => __( 'Groups - Change Group Role', 'um-groups' ),
			'template'      => __( 'Your group role {group_role_old} has been changed to {group_role_new} in {group_name}.', 'um-groups' ),
			'account_desc'  => __( 'When my group roles have been changed', 'um-groups' ),
		);

		return $array;
	}


	/**
	 * Set default settings
	 */
	function set_default_settings() {
		$options = get_option( 'um_options', array() );

		foreach ( $this->settings_defaults as $key => $value ) {
			//set new options to default
			if ( ! isset( $options[ $key ] ) ) {
				$options[ $key ] = $value;
			}

		}

		update_option( 'um_options', $options );
	}


	/**
	 * Page Setup
	 */
	public function page_setup() {
		$version = get_option( 'um_groups_version' );

		if ( ! $version ) {
			$options = get_option( 'um_options' );
			$options = empty( $options ) ? array() : $options;

			 //only on first install
			$create_group_exists = UM()->query()->find_post_id( 'page', '_um_core', 'create_group' );
			$my_group_exists = UM()->query()->find_post_id( 'page', '_um_core', 'my_groups' );
			$groups_exists = UM()->query()->find_post_id( 'page', '_um_core', 'groups' );
			$invites_exists = UM()->query()->find_post_id( 'page', '_um_core', 'group_invites' );


			if ( ! $groups_exists ) {

				// All Groups
				$all_groups = array(
					'post_title'    => 'Groups',
					'post_content'  => '[ultimatemember_groups]',
					'post_status'   => 'publish',
					'post_author'   => get_current_user_id(),
					'post_type'     => 'page'
				);

				$post_id = wp_insert_post( $all_groups );

				if ( $post_id ){
					update_post_meta( $post_id, '_um_core', 'groups');
					$key = UM()->options()->get_core_page_id( 'groups' );
					$options[ $key ] = $post_id;
				}

			}


			if ( ! $create_group_exists ) {

				// Create New Group
				$new_groups  = array(
					'post_title'    => 'Create New Group',
					'post_content'  => '[ultimatemember_group_new]',
					'post_status'   => 'publish',
					'post_author'   => get_current_user_id(),
					'post_type'     => 'page'
				);

				$post_id = wp_insert_post( $new_groups  );

				if ( $post_id ){
					update_post_meta( $post_id, '_um_core', 'create_group');
					$key = UM()->options()->get_core_page_id( 'create_group' );
					$options[ $key ] = $post_id;
				}
			}


			if ( ! $my_group_exists ) {

				// My Groups
				$my_groups  = array(
					'post_title'    => 'My Groups',
					'post_content'  => '[ultimatemember_my_groups]',
					'post_status'   => 'publish',
					'post_author'   => get_current_user_id(),
					'post_type'     => 'page'
				);

				$post_id = wp_insert_post( $my_groups );

				if ( $post_id ) {
					update_post_meta( $post_id, '_um_core', 'my_groups' );
					$key = UM()->options()->get_core_page_id( 'my_groups' );
					$options[ $key ] = $post_id;
				}

			}


			if ( ! $invites_exists ) {
				// Invites List
				$my_groups  = array(
					'post_title'    => 'My Invites',
					'post_content'  => '[ultimatemember_group_invite_list]',
					'post_status'   => 'publish',
					'post_author'   => get_current_user_id(),
					'post_type'     => 'page'
				);

				$post_id = wp_insert_post( $my_groups );

				if ( $post_id ) {
					update_post_meta( $post_id, '_um_core', 'group_invites' );
					$key = UM()->options()->get_core_page_id( 'group_invites' );
					$options[ $key ] = $post_id;
				}
			}


			update_option( 'um_options', $options );

		}

		// reset rewrite rules after re-save pages
		UM()->rewrite()->reset_rules();
	}


	/**
	 * SQL Setup
	 */
	public function sql_setup() {
		global $wpdb;

		if ( get_option( 'ultimatemember_groups_db' ) == um_groups_version ) {
			return;
		}

		$charset_collate = $wpdb->get_charset_collate();

		$sql = "CREATE TABLE {$this->db_groups_table} (
id int(11) unsigned NOT NULL auto_increment,
group_id int(11) unsigned NOT NULL,
user_id1 int(11) unsigned NOT NULL,
user_id2 int(11) unsigned NOT NULL,
status enum('pending_admin_review','pending_member_review','approved','rejected','blocked') NOT NULL,
role varchar(30) NOT NULL,
invites tinyint(1) unsigned NOT NULL DEFAULT 0,
time_stamp timestamp DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
date_joined timestamp DEFAULT 0 ON UPDATE CURRENT_TIMESTAMP,
PRIMARY KEY  (id)
) $charset_collate;";

		require_once( ABSPATH . 'wp-admin/includes/upgrade.php' );
		dbDelta( $sql );


		/* >> Multisite todo */
//		if ( is_multisite() && empty( $this->current_blog ) ) {
//			$blogs = get_sites();
//			foreach ( $blogs as $blog ) {
//				if( $blog->deleted ){
//					continue;
//				}
//				switch_to_blog( $blog->blog_id );
//				$this->current_blog = $blog;
//				$this->db_groups_table = $wpdb->prefix . 'um_groups_members';
//				$this->sql_setup();
//			}
//			restore_current_blog();
//			$this->current_blog = null;
//		} elseif ( isset( $this->current_blog ) ) {
//			return;
//		}
		/* << Multisite */


		update_option('ultimatemember_groups_db', um_groups_version );
	}


	/**
	 *
	 */
	function run_setup() {
		$this->sql_setup();
		$this->page_setup();
		$this->set_default_settings();
	}
}