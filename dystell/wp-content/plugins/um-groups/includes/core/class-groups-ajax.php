<?php
namespace um_ext\um_groups\core;


// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) exit;


/**
 * Class Groups_Ajax
 * @package um_ext\um_groups\core
 */
class Groups_Ajax {


	/**
	 * Groups_Enqueue constructor.
	 */
	function __construct() {
		add_action( 'wp_ajax_um_groups_members', array( UM()->Groups()->api(), 'ajax_get_members' ) );
		add_action( 'wp_ajax_um_groups_search_member_suggest', array( UM()->Groups()->api(), 'search_member_suggest' ) );
		add_action( 'wp_ajax_um_groups_search_member', array( UM()->Groups()->api(), 'search_member' ) );
		add_action( 'wp_ajax_um_groups_add_member', array( UM()->Groups()->api(), 'add_member' ) );
		add_action( 'wp_ajax_um_groups_delete_member', array( UM()->Groups()->api(), 'delete_member' ) );
		add_action( 'wp_ajax_um_groups_send_invitation_mail', array( UM()->Groups()->api(), 'send_invitation_mail' ) );
		add_action( 'wp_ajax_um_groups_change_member_status', array( UM()->Groups()->api(), 'change_member_group_status' ) );
		add_action( 'wp_ajax_um_groups_change_member_role', array( UM()->Groups()->api(), 'change_member_group_role' ) );


		add_action( 'wp_ajax_um_groups_load_more_groups', array( UM()->Groups()->api(), 'load_more_groups' ) );


		add_action( 'wp_ajax_um_groups_join_group', array( UM()->Groups()->api(), 'ajax_join_group' ) );
		add_action( 'wp_ajax_um_groups_leave_group', array( UM()->Groups()->api(), 'ajax_leave_group' ) );
		add_action( 'wp_ajax_um_groups_confirm_invite', array( UM()->Groups()->api(), 'ajax_confirm_invite' ) );
		add_action( 'wp_ajax_um_groups_ignore_invite', array( UM()->Groups()->api(), 'ajax_ignore_invite' ) );


		add_action( 'wp_ajax_um_groups_approve_user', array( UM()->Groups()->member(), 'approve' ) );
		add_action( 'wp_ajax_um_groups_block_user', array( UM()->Groups()->member(), 'block' ) );
		add_action( 'wp_ajax_um_groups_unblock_user', array( UM()->Groups()->member(), 'unblock' ) );
		add_action( 'wp_ajax_um_groups_reject_user', array( UM()->Groups()->member(), 'reject' ) );

		add_action( 'wp_ajax_um_groups_load_wall', array( UM()->Groups()->discussion(), 'ajax_load_wall' ) );
		add_action( 'wp_ajax_um_groups_get_user_suggestions', array( UM()->Groups()->discussion(), 'ajax_get_user_suggestions' ) );
		add_action( 'wp_ajax_um_groups_remove_post', array( UM()->Groups()->discussion(), 'ajax_remove_post' ) );
		add_action( 'wp_ajax_um_groups_remove_comment', array( UM()->Groups()->discussion(), 'ajax_remove_comment' ) );
		add_action( 'wp_ajax_um_groups_get_post_likes', array( UM()->Groups()->discussion(), 'ajax_get_post_likes' ) );
		add_action( 'wp_ajax_um_groups_get_comment_likes', array( UM()->Groups()->discussion(), 'ajax_get_comment_likes' ) );
		add_action( 'wp_ajax_um_groups_hide_comment', array( UM()->Groups()->discussion(), 'ajax_hide_comment' ) );
		add_action( 'wp_ajax_um_groups_unhide_comment', array( UM()->Groups()->discussion(), 'ajax_unhide_comment' ) );
		add_action( 'wp_ajax_um_groups_report_post', array( UM()->Groups()->discussion(), 'ajax_report_post' ) );
		add_action( 'wp_ajax_um_groups_unreport_post', array( UM()->Groups()->discussion(), 'ajax_unreport_post' ) );
		add_action( 'wp_ajax_um_groups_load_more_comments', array( UM()->Groups()->discussion(), 'ajax_load_more_comments' ) );
		add_action( 'wp_ajax_um_groups_load_more_replies', array( UM()->Groups()->discussion(), 'ajax_load_more_replies' ) );
		add_action( 'wp_ajax_um_groups_like_comment', array( UM()->Groups()->discussion(), 'ajax_like_comment' ) );
		add_action( 'wp_ajax_um_groups_unlike_comment', array( UM()->Groups()->discussion(), 'ajax_unlike_comment' ) );
		add_action( 'wp_ajax_um_groups_like_post', array( UM()->Groups()->discussion(), 'ajax_like_post' ) );
		add_action( 'wp_ajax_um_groups_unlike_post', array( UM()->Groups()->discussion(), 'ajax_unlike_post' ) );
		add_action( 'wp_ajax_um_groups_wall_comment', array( UM()->Groups()->discussion(), 'ajax_wall_comment' ) );
		add_action( 'wp_ajax_um_groups_publish', array( UM()->Groups()->discussion(), 'ajax_activity_publish' ) );
		add_action( 'wp_ajax_um_groups_approve_discussion_post', array( UM()->Groups()->discussion(), 'ajax_approve_discussion_post' ) );

	}
}
