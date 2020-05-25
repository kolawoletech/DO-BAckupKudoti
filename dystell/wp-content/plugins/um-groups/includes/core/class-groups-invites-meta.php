<?php
namespace um_ext\um_groups\core;


if ( ! defined( 'ABSPATH' ) ) exit;


/**
 * Class Groups_Invites_Meta
 *
 * @package um_ext\um_groups\core
 */
class Groups_Invites_Meta extends Groups_Invites {


	var $profiles_per_page = 10;


	/**
	 * Groups_Invites_Meta constructor.
	 */
	function __construct() {
		parent::__construct();
	}


	/**
	 * Main Query function for getting members via AJAX
	 */
	function ajax_get_members() {
		UM()->check_ajax_nonce();

		global $wpdb;

		$group_id = $this->get_group_by_hash( $_POST['group_id'] );
		$group_data = UM()->query()->post_data( $group_id );

		$directory_data = apply_filters( 'um_group_invites_get_members_directory_data', array(
			'header'         => '',
			'header_single'  => '',
			'show_tagline'   => false,
			'show_userinfo'  => false,
			'tagline_fields' => array(),
		), $group_id, $group_data );

		$can_invite = UM()->Groups()->api()->can_invite_members( $group_id, null ) || um_groups_admin_all_access();

		$this->predefined_no_caps( $directory_data );

		// Prepare for BIG SELECT query
		$wpdb->query( 'SET SQL_BIG_SELECTS=1' );

		// Prepare default user query values
		$this->query_args = array(
			'fields'        => 'ids',
			'number'        => 0,
			'meta_query'    => array(
				'relation' => 'AND'
			),
		);


		// handle different restrictions
		$this->restriction_options();

		// handle pagination options
		$this->pagination_options( $directory_data );

		// handle general search line
		$this->general_search();

		// handle filters
		$this->filters( $group_data );

		$this->query_args['um_group_id'] = $group_id;
		$this->query_args = apply_filters( 'um_groups_invites_prepare_user_query_args', $this->query_args, $group_data );

		//unset empty meta_query attribute
		if ( isset( $this->query_args['meta_query']['relation'] ) && count( $this->query_args['meta_query'] ) == 1 ) {
			unset( $this->query_args['meta_query'] );
		}

		do_action( 'um_groups_invites_before_query', $this->query_args );

		add_filter( 'get_meta_sql', array( &$this, 'change_meta_sql' ), 10, 6 );

		add_action( 'pre_user_query', array( &$this, 'only_not_invited' ) );

		$user_query = new \WP_User_Query( $this->query_args );

		remove_action( 'pre_user_query', array( &$this, 'only_not_invited' ) );

		remove_filter( 'get_meta_sql', array( &$this, 'change_meta_sql' ), 10 );

		do_action( 'um_groups_invites_user_after_query', $this->query_args, $user_query );

		$pagination_data = $this->calculate_pagination( $directory_data, $user_query );

		$user_ids = ! empty( $user_query->results ) ? array_unique( $user_query->results ) : array();

		/**
		 * UM hook
		 *
		 * @type filter
		 * @title um_prepare_user_results_array
		 * @description Extend member directory query result
		 * @input_vars
		 * [{"var":"$result","type":"array","desc":"Members Query Result"}]
		 * @change_log
		 * ["Since: 2.0"]
		 * @usage
		 * <?php add_filter( 'um_prepare_user_results_array', 'function_name', 10, 1 ); ?>
		 * @example
		 * <?php
		 * add_filter( 'um_prepare_user_results_array', 'my_prepare_user_results', 10, 1 );
		 * function my_prepare_user_results( $user_ids ) {
		 *     // your code here
		 *     return $user_ids;
		 * }
		 * ?>
		 */
		$user_ids = apply_filters( 'um_prepare_user_results_array', $user_ids );

		$users = array();
		foreach ( $user_ids as $i => $user_id ) {
			$users[$i] = $this->build_user_card_data( $user_id, $directory_data );

			$users[$i]['date'] = '';
			$users[$i]['is_invited'] = '';
			$users[$i]['menus'] = array();

			$user_data = $this->get_group_user_data( $user_id, $group_id );
			if ( $user_data ) {
				$users[$i]['date'] = strtotime( $user_data['date_joined'] ) > 0 ? sprintf( __( 'Joined %s ago', 'um-groups' ), human_time_diff( strtotime( $user_data['date_joined'] ), current_time( 'timestamp' ) ) ) : '';
				$users[$i]['is_invited'] = $user_data['status'] === 'pending_member_review';
			}

			if ( $can_invite ) {
				$users[$i]['menus'] = array(
						'invite' => __( 'Invite', 'um-groups' )
				);
			}

			ob_start();
			do_action( 'um_groups_users_list_after_details', $user_id, $group_id, false );
			do_action( 'um_groups_users_list_after_details__invites_front', $user_id, $group_id, $user_data, false );
			$users[$i]['additional_content'] = ob_get_clean();
		}
		um_reset_user();
		// end of user card

		$return = array(
			'pagination'   => $pagination_data,
			'users'        => $users
		);

		wp_send_json_success( $return );
	}
}