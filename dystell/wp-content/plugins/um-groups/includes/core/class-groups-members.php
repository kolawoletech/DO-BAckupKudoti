<?php
namespace um_ext\um_groups\core;


if ( ! defined( 'ABSPATH' ) ) exit;


/**
 * Class Groups_Members
 *
 * @package um_ext\um_groups\core
 */
class Groups_Members extends \um\core\Member_Directory {


	var $profiles_per_page = 10;


	/**
	 * Groups_Members constructor.
	 */
	function __construct() {
		parent::__construct();

		add_action( 'wp_ajax_nopriv_um_groups_get_members', array( $this, 'ajax_get_members' ) );
		add_action( 'wp_ajax_um_groups_get_members', array( $this, 'ajax_get_members' ) );
	}


	/**
	 * @param $hash
	 *
	 * @return bool|int
	 */
	function get_group_by_hash( $hash ) {
		global $wpdb;

		$group_id = $wpdb->get_var( $wpdb->prepare( "SELECT ID FROM {$wpdb->posts} WHERE SUBSTRING( MD5( ID ), 11, 5 ) = %s", $hash ) );

		if ( empty( $group_id ) ) {
			return false;
		}

		return (int) $group_id;
	}


	/**
	 * Handle members can view restrictions
	 */
	function restriction_options() {
		$this->hide_not_approved();
		$this->hide_by_role();

		do_action( 'um_groups_invites_restrictions_handle_extend' );
	}


	/**
	 * Change where query to show only group members
	 *
	 * @param $query
	 *
	 * @return mixed
	 */
	function only_members( $query ) {
		global $wpdb;

		$group_id = $query->query_vars['um_group_id'];
		$groups_table_name = UM()->Groups()->setup()->db_groups_table;

		$group_meta = $wpdb->prepare(
			"{$wpdb->users}.ID IN(
				SELECT DISTINCT tbg.user_id1 FROM {$groups_table_name} as tbg
				WHERE tbg.user_id1 = {$wpdb->users}.ID AND tbg.group_id = %d AND tbg.status = 'approved'
			)",
			$group_id
		);

		$query->query_where = str_replace(
			'WHERE 1=1',
			"WHERE 1=1 AND (" . $group_meta . " ) ",
			$query->query_where );

		return $query;
	}


	/**
	 * Handle "Pagination Options" metabox settings
	 *
	 * @param array $directory_data
	 */
	function pagination_options( $directory_data ) {
		$this->query_args['number'] = apply_filters( 'um_groups_users_per_page', 0 );
		$this->query_args['paged'] = ! empty( $_POST['page'] ) ? $_POST['page'] : 1;
	}


	/**
	 * Get data array for pagination
	 *
	 *
	 * @param array $directory_data
	 * @param \WP_User_Query $result
	 *
	 * @return array
	 */
	function calculate_pagination( $directory_data, $result ) {

		$current_page = ! empty( $_POST['page'] ) ? $_POST['page'] : 1;
		$total_users = $result->total_users;
		$total_pages = ceil( $total_users / $this->profiles_per_page );

		if ( ! empty( $total_pages ) ) {
			$index1 = 0 - ( $current_page - 2 ) + 1;
			$to = $current_page + 2;
			if ( $index1 > 0 ) {
				$to += $index1;
			}

			$index2 = $total_pages - ( $current_page + 2 );
			$from = $current_page - 2;
			if ( $index2 < 0 ) {
				$from += $index2;
			}

			$pages_to_show = range(
				( $from > 0 ) ? $from : 1,
				( $to <= $total_pages ) ? $to : $total_pages
			);
		}


		$pagination_data = array(
			'pages_to_show' => ( ! empty( $pages_to_show ) && count( $pages_to_show ) > 1 ) ? array_values( $pages_to_show ) : array(),
			'current_page'  => $current_page,
			'total_pages'   => $total_pages,
			'total_users'   => $total_users,
		);

		$pagination_data['header'] = $this->convert_tags( $directory_data['header'], $pagination_data );
		$pagination_data['header_single'] = $this->convert_tags( $directory_data['header_single'], $pagination_data );

		return $pagination_data;
	}


	/**
	 * Send empty members for users who haven't capabilities
	 * @param $group_id
	 */
	function predefined_no_caps( $group_id ) {
		//predefined result for user without capabilities to see other members
		if ( is_user_logged_in() && ! UM()->roles()->um_user_can( 'can_view_all' ) ) {
			UM()->Groups()->member()->set_group( $group_id, get_current_user_id() );

			$member_role = UM()->Groups()->member()->get_role();
			$member_status = UM()->Groups()->member()->get_status();

			if ( ! in_array( $member_role, array( 'admin', 'moderator' ) ) || 'approved' !== $member_status ) {
				$pagination_data = array(
					'pages_to_show' => array(),
					'current_page'  => 1,
					'total_pages'   => 0,
					'total_users'   => 0,
				);

				$pagination_data['header'] = $this->convert_tags( __( '{total_users} Members', 'um-groups' ), $pagination_data );
				$pagination_data['header_single'] = $this->convert_tags( __( '{total_users} Member', 'um-groups' ), $pagination_data );

				wp_send_json_success( array( 'users' => array(), 'pagination' => $pagination_data ) );
			}
		}
	}


	/**
	 * Main Query function for getting members via AJAX
	 */
	function ajax_get_members() {
		UM()->check_ajax_nonce();

		global $wpdb;

		$group_id = $this->get_group_by_hash( $_POST['group_id'] );
		$group_data = UM()->query()->post_data( $group_id );

		$directory_data = apply_filters( 'um_group_members_directory_data', array(
			'header'         => '',
			'header_single'  => '',
			'show_tagline'   => false,
			'show_userinfo'  => false,
			'tagline_fields' => array(),
		), $group_id, $group_data );

		$this->predefined_no_caps( $group_id );

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

		$this->query_args['um_group_id'] = $group_id;

		//unset empty meta_query attribute
		if ( isset( $this->query_args['meta_query']['relation'] ) && count( $this->query_args['meta_query'] ) == 1 ) {
			unset( $this->query_args['meta_query'] );
		}

		add_action( 'pre_user_query', array( &$this, 'only_members' ) );

		$user_query = new \WP_User_Query( $this->query_args );

		remove_action( 'pre_user_query', array( &$this, 'only_members' ) );

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

		$return = array(
			'pagination'   => $this->calculate_pagination( $directory_data, $user_query ),
			'users'        => $this->prepare_users_response( $user_ids, $directory_data, $group_id ),
		);

		wp_send_json_success( $return );
	}


	/**
	 * Get users data from group
	 *
	 * @param array $user_ids
	 * @param int $group_id
	 *
	 * @return array
	 */
	function get_group_users_data( $user_ids, $group_id ) {
		global $wpdb;
		$users_data = $wpdb->get_results( $wpdb->prepare(
			"SELECT `role`, `date_joined`, `status`, `user_id1` AS ID
			FROM {$wpdb->prefix}um_groups_members
			WHERE user_id1 IN('" . implode( "','", $user_ids ) . "') AND 
				  group_id = %d",
			$group_id
		), ARRAY_A );

		$data = array();
		foreach ( $users_data as $row ) {
			$data[ $row['ID'] ] = $row;
			unset( $data[ $row['ID'] ]['ID'] );
		}

		return apply_filters( 'um_get_group_users_data', $data, $user_ids, $group_id );
	}


	/**
	 * @param int $user_id
	 * @param array $user_data
	 * @param int $group_id
	 *
	 * @return array
	 */
	function build_user_actions( $user_id, $user_data, $group_id ) {

		$actions = array();
		if ( ! is_user_logged_in() ) {
			return $actions;
		}

		$privacy = UM()->Groups()->api()->get_privacy_slug( $group_id );
		$can_manage_group = UM()->Groups()->api()->can_manage_group( $group_id, null, $privacy ) || um_groups_admin_all_access();

		if ( $can_manage_group ) {
			$actions['make-admin'] = array(
				'title' => __( 'Make Admin', 'um-groups' ),
			);
			$actions['make-member'] = array(
				'title' => __( 'Make Member', 'um-groups' ),
			);
			$actions['make-moderator'] = array(
				'title' => __( 'Make Moderator', 'um-groups' ),
			);

			if ( 'admin' == $user_data['role'] ) {
				unset( $actions['make-admin'] );
			} elseif ( 'member' == $user_data['role'] ) {
				unset( $actions['make-member'] );
			} elseif ( 'moderator' == $user_data['role'] ) {
				unset( $actions['make-moderator'] );
			}
		}

		if ( $user_id == get_current_user_id() ) {
			$actions['remove-self-from-group'] = array(
				'title' => __( 'Leave Group', 'um-group' ),
			);
		} elseif ( $can_manage_group ) {
			$actions['remove-from-group'] = array(
				'title' => __( 'Remove From Group', 'um-groups' ),
			);
		}

		$actions = apply_filters( 'um_groups_members_user_card_actions', $actions, $user_id );
		return $actions;
	}


	/**
	 * @param array $user_ids
	 * @param array $directory_data
	 * @param int $group_id
	 *
	 * @return array
	 */
	function prepare_users_response( $user_ids, $directory_data, $group_id ) {
		$users = array();

		$users_data = $this->get_group_users_data( $user_ids, $group_id );

		foreach ( $user_ids as $i => $user_id ) {
			$user_data = ! empty( $users_data[ $user_id ] ) ? $users_data[ $user_id ] : false;
			if ( ! $user_data ) {
				continue;
			}

			$users[ $i ] = $this->build_user_card_data( $user_id, $directory_data );
			$users[ $i ]['dropdown_actions'] = $this->build_user_actions( $user_id, $user_data, $group_id );
			$users[ $i ]['date'] = strtotime( $user_data['date_joined'] ) > 0 ? sprintf( __( 'Joined %s ago', 'um-groups' ), human_time_diff( strtotime( $user_data['date_joined'] ), current_time( 'timestamp' ) ) ) : '';

			ob_start();
			do_action( 'um_groups_users_list_after_details', $user_id, $group_id, true );
			do_action( 'um_groups_users_list_after_details__approved', $user_id, $group_id, $user_data, true );
			$users[ $i ]['additional_content'] = ob_get_clean();
		}

		um_reset_user();

		return array_values( $users );
	}
}