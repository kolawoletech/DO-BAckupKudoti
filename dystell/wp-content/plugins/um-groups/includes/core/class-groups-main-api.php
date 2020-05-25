<?php
namespace um_ext\um_groups\core;

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) exit;

/**
 * @since  1.0.0
 */
class Groups_Main_API {

	/**
	 * Group Tabs
	 * @var  array
	 */
	var $group_tabs;

	/**
	 * Group search results
	 * @var array
	 */
	var $groups_results;

	/**
	 * Invited user id
	 * @var integer
	 */
	var $invited_user_id;

	/**
	 * Own groups count
	 * @var integer
	 */
	var $own_groups_count;

	/**
	 * Curren group tab
	 * @var string
	 */
	var $current_group_tab;

	/**
	 * Single group title
	 * @var string
	 */
	var $single_group_title;

	/**
	 * __construct
	 */
	function __construct() {

		$this->privacy_options = array(
			'public' 	=> __('Public','um-groups'),
			'public_role' => __( 'Public for Role', 'um-groups' ),
			'private' 	=> __('Private','um-groups'),
			'hidden' 	=> __('Hidden','um-groups')
		);

		$this->privacy_icons = array(
			'public' 	=> '<i class="um-faicon-globe"></i> ',
			'public_role' 	=> '<i class="um-faicon-lock"></i> ',
			'private' 	=> '<i class="um-faicon-lock"></i> ',
			'hidden' 	=> '<i class="um-faicon-eye"></i> ',
		);

		$this->get_groups_button_labels();

		$this->join_status = array(
			'pending_admin_review'  => __('Pending Admin Review','um-groups'),
			'pending_member_review' => __('Pending Member Review','um-groups'),
			'approved' 				=> __('Approved','um-groups'),
			'rejected'				=> __('Rejected','um-groups'),
			'blocked' 				=> __('Blocked','um-groups'),

		);

		$this->group_roles = array(
			'admin' 	=> __('Administrator','um-groups'),
			'moderator' => __('Moderator','um-groups'),
			'member'	=> __('Member','um-groups'),
			'banned'	=> __('Banned','um-groups'),
		);

		$this->can_invite = array(
			0 => __('All Group Members','um-groups'),
			1 => __('Group Administrators & Moderators only','um-groups'),
			2 => __('Group Administrators only','um-groups')
		);

		$this->group_posts_moderation_options = array(
			'auto-published' => __('Auto Published','um-groups'),
			'require-moderation' => __('Require Mod/Admin','um-groups')
		);

		$this->group_members_order = array(
			'asc' => __('Oldest members first','um-groups'),
			'desc' => __('Newest members first','um-groups')
		);

	}


	/**
	 * Checks if user enabled email notification
	 *
	 * @param $user_id
	 *
	 * @return bool|int
	 */
	function enabled_email( $user_id, $key ) {
		$_enabled = true;
		if ( get_user_meta( $user_id, $key, true ) == 'yes' ) {
			$_enabled = 1;
		} elseif ( get_user_meta( $user_id, $key, true ) == 'no' ) {
			$_enabled = 0;
		}
		return $_enabled;
	}


	/**
	 * Get Privacy Title
	 * @param  string $slug
	 * @return string
	 */
	function get_privacy_title( $slug ){
		if( isset( $this->privacy_options[ $slug ] ) ){
			return $this->privacy_options[ $slug ];
		}

		return '';
	}


	/**
	 * Get group image
	 * @param  integer $group_id
	 * @param  string  $ratio
	 * @param  integer $width
	 * @param  integer $height
	 * @return mixed
	 */
	function get_group_image( $group_id = 0, $ratio = 'default', $width = 50, $height = 50, $raw = false ){
		if ( ! UM()->options()->get( 'groups_show_avatars' ) ) {
			return '';
		}

		wp_enqueue_script( 'um_groups' );
		wp_enqueue_style( 'um_groups' );

		$thumbnail = get_the_post_thumbnail( $group_id, array( $width, $height ) , array( 'class' => 'um-group-image' ) );

		if ( ! $thumbnail ) {
			$thumbnail = wp_get_attachment_image( get_post_thumbnail_id( $group_id ), array( $width, $height ), '', array( 'class' => 'um-group-image' ) );
		}

		if ( $raw ) {
			$thumbnail = wp_get_attachment_image_src( get_post_thumbnail_id( $group_id ) );
			return $thumbnail;
		}

		if ( ! $thumbnail ) {
			$group_data = get_post( $group_id );
			if ( $group_data ) {
				$group_title = $group_data->post_title;
			}

			return "<img src=\"//via.placeholder.com/{$width}x{$height}?text=" . ucfirst( $group_title[0] ) . "\" class=\"um-group-image um-group-id-{$group_id}\" width=\"{$width}\" height=\"{$height}\"/>";
		} else {
			return $thumbnail;
		}
	}


	/**
	 * Get Privacy Icon
	 * @param  string $slug
	 * @return string
	 */
	function get_privacy_icon( $slug ) {

		if( isset( $this->privacy_icons[ $slug ] ) ){
			return $this->privacy_icons[ $slug ];
		}

		return '';
	}


	/**
	 * Get privacy slug
	 * @param  integer $group_id
	 * @return string
	 */
	function get_privacy_slug( $group_id = 0 ){
		$slug = get_post_meta( $group_id , '_um_groups_privacy', true );

		if ( $slug === 'public_role' ) {
			$group_roles = get_post_meta( $group_id, '_um_groups_privacy_roles', true );
			$slug = ( is_array( $group_roles ) && array_intersect( $group_roles, um_user( 'roles' ) ) ) ? 'public' : 'private';
		}

		return $slug;
	}


	/**
	 * Get single group privacy
	 * @param  integer $group_id
	 * @return string
	 */
	function get_single_privacy( $group_id = 0 ){
		$slug = $this->get_privacy_slug( $group_id );

		$output = sprintf('%s %s', $this->get_privacy_icon( $slug ), $this->get_privacy_title( $slug ) );

		return $output;
	}


	/**
	 * Join group
	 * @param  int $user_id
	 * @param  int $invited_by_user_id
	 * @param  int $group_id
	 * @param  string $group_role
	 * @param  bool $new_group
	 *
	 * @return array
	 */
	function join_group( $user_id = null, $invited_by_user_id = null, $group_id = null, $group_role = 'member', $new_group = false ) {
		global $wpdb;

		$table_name = UM()->Groups()->setup()->db_groups_table;

		$has_joined = $this->has_joined_group( $user_id, $group_id );
		$message = '';

		if ( ! in_array( $has_joined, array( 'rejected', '' ) ) ) {
			return array(
				'status' => false,
				'message' => __( 'You\'re already a member of this group.', 'um-groups' )
			);
		} else {
			$group_privacy = $this->get_privacy_slug( $group_id );

			switch( $group_privacy ) {
				case 'public':
				case '':

					$inserted = $wpdb->insert(
						$table_name,
						array(
							'group_id'      => $group_id,
							'user_id1'      => $user_id,
							'user_id2'      => $user_id,
							'status'        => 'approved',
							'role'          => $group_role,
							'date_joined'   => date('Y-m-d H:i:s',  current_time( 'timestamp' ) )
						),
						array(
							'%d',
							'%d',
							'%d',
							'%s',
							'%s',
							'%s'
						)
					);

					do_action('um_groups_after_member_changed_status__approved', $user_id, $group_id, $invited_by_user_id, $group_role, $new_group );

					break;

				case 'hidden':

					if ( ! $has_joined ) {

						if ( 'admin' == $group_role ) {

							$inserted = $wpdb->insert(
								$table_name,
								array(
									'group_id'      => $group_id,
									'user_id1'      => $user_id,
									'user_id2'      => $user_id,
									'status'        => 'approved',
									'role'          => $group_role,
									'date_joined'   => date('Y-m-d H:i:s',  current_time( 'timestamp' ) )
								),
								array(
									'%d',
									'%d',
									'%d',
									'%s',
									'%s',
									'%s'
								)
							);
						}

						do_action('um_groups_after_admin_changed_status__hidden_approved', $user_id, $group_id, $invited_by_user_id, $group_role, $new_group );

					}

					$message = __('Only members can add you to this group.','um-groups');

					break;

				case 'private':

					if ( ! $has_joined ) {

						if ( 'member' == $group_role ) {

							$inserted = $wpdb->insert(
								$table_name,
								array(
									'group_id'      => $group_id,
									'user_id1'      => $user_id,
									'user_id2'      => $user_id,
									'status'        => 'pending_admin_review',
									'role'          => $group_role,
									'date_joined'   => date('Y-m-d H:i:s',  current_time( 'timestamp' ) )
								),
								array(
									'%d',
									'%d',
									'%d',
									'%s',
									'%s',
									'%s'
								)
							);

						} elseif ( 'admin' == $group_role ) {

							$inserted = $wpdb->insert(
								$table_name,
								array(
									'group_id'      => $group_id,
									'user_id1'      => $user_id,
									'user_id2'      => $user_id,
									'status'        => 'approved',
									'role'          => $group_role,
									'date_joined'   => date('Y-m-d H:i:s',  current_time( 'timestamp' ) )
								),
								array(
									'%d',
									'%d',
									'%d',
									'%s',
									'%s',
									'%s'
								)
							);
						}

						do_action('um_groups_after_member_changed_status__pending_admin_review', $user_id, $group_id, $invited_by_user_id );

					} else {

						$wpdb->update(
							$table_name,
							array(
								'status'    => 'pending_admin_review',
							),
							array(
								'group_id'  => $group_id,
								'user_id1'  => $user_id,
							),
							array(
								'%s'
							),
							array(
								'%d',
								'%d'
							)
						);

						do_action('um_groups_after_member_changed_status__pending_admin_review', $user_id, $group_id, $invited_by_user_id );

					}

					break;
			}

			if( $inserted && $wpdb->insert_id ) {
				$wpdb->query( "
					UPDATE `$table_name` SET
						`user_id1` = $user_id,
						`user_id2` = $user_id
					WHERE	`id` = $wpdb->insert_id;" );
			}

			return array(
				'status' => true,
				'privacy' => $group_privacy,
				'labels' => $this->privacy_groups_button_labels[ $group_privacy ],
				'has_joined' => $has_joined,
				'message' => $message
			);
		}
	}


	/**
	 * Leave group
	 *
	 * @param  int $user_id
	 * @param  int $group_id
	 *
	 * @return array
	 */
	function leave_group( $user_id = null, $group_id = null ) {
		global $wpdb;

		$table_name = UM()->Groups()->setup()->db_groups_table;

		$group_privacy = $this->get_privacy_slug( $group_id );

		$wpdb->delete(
			$table_name,
			array(
				'group_id' => $group_id,
				'user_id1' => $user_id
			),
			array(
				'%d',
				'%d'
			)
		);

		return array(
			'status' => true,
			'privacy' => $group_privacy,
			'labels' => $this->privacy_groups_button_labels[ $group_privacy ]
		);
	}


	/**
	 * Invite User
	 * @global \um_ext\um_groups\core\type $wpdb
	 * @param  integer $invited_user_id
	 * @param  integer $invited_by_user_id
	 * @param  integer $group_id
	 * @return boolean
	 */
	function invite_user( $invited_user_id = null, $invited_by_user_id = null, $group_id = null ) {
		global $wpdb;

		$table_name = UM()->Groups()->setup()->db_groups_table;
		$has_joined = $this->has_joined_group( $invited_user_id, $group_id );

		if ( in_array( $has_joined, array( '' ) ) ) {

			$inserted = $wpdb->insert(
				$table_name,
				array(
					'group_id'  => $group_id,
					'user_id1'  => $invited_user_id,
					'user_id2'  => $invited_by_user_id,
					'status'    => 'pending_member_review',
					'role'      => 'member',
					'invites'   => 1
				),
				array(
					'%d',
					'%d',
					'%d',
					'%s',
					'%s',
					'%d'
				)
			);

			if( $inserted && $wpdb->insert_id ) {
				$wpdb->query( "
					UPDATE `$table_name` SET
						`user_id1` = $invited_user_id,
						`user_id2` = $invited_by_user_id
					WHERE	`id` = $wpdb->insert_id;" );
			}

			do_action( 'um_groups_after_member_changed_status__pending_member_review', $invited_user_id, $group_id, $invited_by_user_id );

		} elseif ( in_array( $has_joined, array( 'rejected' ) ) ) {

			$wpdb->update(
				$table_name,
				array(
					'status'    => 'pending_member_review',
				),
				array(
					'group_id'  => $group_id,
					'user_id1'  => $invited_user_id,
				),
				array(
					'%s'
				),
				array(
					'%d',
					'%d'
				)
			);

			do_action( 'um_groups_after_member_changed_status__pending_member_review', $invited_user_id, $group_id, $invited_by_user_id );
		}

		do_action( "um_groups_after_member_changed_status__{$has_joined}", $invited_user_id, $group_id, $invited_by_user_id, false, false );

		return $has_joined;
	}


	/**
	 * Get groups that user join or was invited
	 * Usage: UM()->Groups()->api()->get_joined_groups( $user_id );
	 * @global type $wpdb
	 * @staticvar array $results
	 * @param type $user_id
	 * @param type $status
	 * @return array
	 */
	public function get_joined_groups( $user_id = null, $status = '' ) {
		global $wpdb;

		if ( empty( $user_id ) ) {
			$user_id = get_current_user_id();
		}

		static $results = array();
		if ( isset( $results["$user_id.$status"] ) ) {
			return $results["$user_id.$status"];
		}

		$table_name = UM()->Groups()->setup()->db_groups_table;

		$where_and = array(
				"user_id1 = $user_id",
		);
		if ( $status ) {
			$where_and[] = "status = '$status'";
		}
		$where = implode( ' AND ', $where_and );

		$group_members = $wpdb->get_results( "
			SELECT *
			FROM {$table_name}
			WHERE $where;" );

		return $results["$user_id.$status"] = $group_members;
	}


	/**
	 * Check if user has joined the group
	 * @global type $wpdb
	 * @staticvar array $results
	 * @param  integer  $user_id
	 * @param  integer  $group_id
	 * @return boolean|string  status
	 */
	public function has_joined_group( $user_id = null, $group_id = null ) {
		global $wpdb;

		if( empty($user_id) ){
			$user_id = get_current_user_id();
		}

		if ( empty( $group_id ) && isset( $_REQUEST['group_id'] ) ) {
			$group_id = sanitize_key( $_REQUEST['group_id'] );
		}
		if ( empty( $group_id ) && isset( $GLOBALS['um_group_id'] ) ) {
			$group_id = $GLOBALS['um_group_id'];
		}

		if ( ! $user_id || ! $group_id ) {
			return false;
		}

		static $results = array();
		if ( isset( $results["$user_id.$group_id"] ) ) {
			return $results["$user_id.$group_id"];
		}

		$table_name = UM()->Groups()->setup()->db_groups_table;

		$user_id2 = $wpdb->get_row( $wpdb->prepare(
			"SELECT user_id2 AS ID,
			status,
			user_id2 AS invited_by_user_id
			FROM {$table_name}
			WHERE group_id = %d AND
			      user_id1 = %d",
			$group_id,
			$user_id
		) );

 		if ( isset( $user_id2->invited_by_user_id ) ) {
 			$this->invited_by_user_id = $user_id2->invited_by_user_id;
 		}

		$joined = is_null( $user_id2 ) ? false : $user_id2->status;

		$results["$user_id.$group_id"] = $joined;

		return $joined;
	}


	/**
	 * Get total group members
	 *
	 * @param  integer  $group_id
	 * @param  boolean $update_cache
	 * @param  string $status
	 * @return integer
	 */
	function count_members( $group_id = null, $update_cache = false, $status = 'approved' ) {
		global $wpdb;
		$total_members = 0;

		$group_privacy = $this->get_privacy_slug( $group_id );

		$cache_total_members = get_post_meta( $group_id, "um_groups_members_count_{$status}", true );

		if ( $update_cache || empty( $cache_total_members ) ) {
			$table_name = UM()->Groups()->setup()->db_groups_table;

			if ( 'private' == $group_privacy ) {
				$total_members = $wpdb->get_var( $wpdb->prepare(
					"SELECT COUNT( user_id1 )
					FROM {$table_name}
					WHERE group_id = %d AND
					      status = %s",
					$group_id,
					$status
				) );
			} else {
				if ( 'pending_admin_review' == $status ) {
					$total_members = $wpdb->get_var( $wpdb->prepare(
						"SELECT COUNT( user_id1 )
						FROM {$table_name}
						WHERE group_id = %d AND
						      status = 'pending_admin_review'",
						$group_id
					) );
				} elseif ( 'pending_member_review' == $status ) {
					$total_members = $wpdb->get_var( $wpdb->prepare(
						"SELECT COUNT( user_id1 )
						FROM {$table_name}
						WHERE group_id = %d AND
						      status IN( 'pending_admin_review', 'pending_member_review' )",
						$group_id
					) );
				} elseif ( 'blocked' == $status ) {
					$total_members = $wpdb->get_var( $wpdb->prepare(
						"SELECT COUNT( user_id1 )
						FROM {$table_name}
						WHERE group_id = %d AND
						      status = %s",
						$group_id,
						$status
					) );
				} elseif ( 'rejected' == $status ) {
					$total_members = $wpdb->get_var( $wpdb->prepare(
						"SELECT COUNT( user_id1 )
						FROM {$table_name}
						WHERE group_id = %d AND
						      status = %s",
						$group_id,
						$status
					) );
				} elseif ( 'approved' == $status ) {
					$total_members = $wpdb->get_var( $wpdb->prepare(
						"SELECT COUNT( user_id1 )
						FROM {$table_name}
						WHERE group_id = %d AND
						      status = %s AND
						      user_id1 IN ( SELECT ID FROM {$wpdb->users} WHERE ID = user_id1 )",
						$group_id,
						$status
					) );
				} else {
					$total_members = $wpdb->get_var( $wpdb->prepare(
						"SELECT COUNT( user_id1 )
						FROM {$table_name}
						WHERE group_id = %d",
						$group_id
					) );
				}
			}

			update_post_meta( $group_id,"um_groups_members_count_{$status}", $total_members );
		} else {
			$total_members = get_post_meta( $group_id, "um_groups_members_count_{$status}", true );
		}

		return ! empty( $total_members ) ? $total_members : 0;
	}


	/**
	 * Only for wp-admin
	 *
	 * todo
	 *
	 * @return array|void
	 */
	function ajax_get_members() {
		if ( defined( 'DOING_AJAX' ) && DOING_AJAX ) {
			UM()->admin()->check_ajax_nonce();
		}

		$data = $this->get_members();

		wp_send_json( $data );
	}


	/**
	 * Get Members
	 *
	 * @param int $group_id
	 * @param string $status
	 * @param array $atts
	 * @param int $limit
	 * @param int $offset
	 * @param string $search_keyword
	 * @param null $user_id
	 * @param null $custom_filter
	 *
	 * @return array
	 */
	function get_members( $group_id = 0, $status = '', $atts = array(), $limit = -1, $offset = 0, $search_keyword = '', $custom_filter = null ) {
		global $wpdb;

		$group_id = isset( $_REQUEST['group_id'] ) ? sanitize_key( $_REQUEST['group_id'] ) : $group_id ;

		$limit = apply_filters('um_groups_users_per_page', $limit );

		$search_query_results = array();

		$search_query_args = array();

		$search_query_ids = array();

		$search_query = array();

		$doing_search = false;

		$members = array();

		$data = array();

		$paginate = "";
		if ( empty( $_REQUEST['wp_admin_referer'] ) ) {
			$paginate = " LIMIT {$offset},{$limit} ";
		}

		$args = shortcode_atts( array(
			'fields'   			=> array('ID'),
			'search_columns'  	=> array('user_login','user_email','display_name'),
			'meta_query'   		=> array(),
			'search' 			=> array(),
		), $atts );

		$table_name = UM()->Groups()->setup()->db_groups_table;

		/**
		 * jquery datatable's ajax user query
		 */
		if( 'invite' == $status ){
			$args = array( 'um_groups_get_members' => true,'um_group_id' => $group_id );
			$args['fields'] = array('ID');
			$args['search_columns'] = array('user_login','user_email','display_name');
			$args['meta_query'] = array(
				array(
					'key' => 'account_status',
					'value' => 'approved',
					'compare' => '='
				)
			);

			if( isset( $_REQUEST['search']['value'] ) && ! empty( $_REQUEST['search']['value'] ) ){
				unset( $args['um_groups_get_members'] );
				$args['search'] = '*'.$_REQUEST['search']['value'];
			}


			$query = new \WP_User_Query( $args );

			if ( $query->get_total() <= 0 ) {
				$args = array( 'um_groups_get_members' => true,'um_group_id' => $group_id );

				if ( isset( $_REQUEST['search']['value'] ) && ! empty( $_REQUEST['search']['value'] ) ) {

					unset( $args['um_groups_get_members'] );

					$args['meta_query'][] = array(
						'relation' => 'OR',
						array(
							'relation' => 'OR',
							array(
								'key' => 'first_name',
								'value' => $_REQUEST['search']['value'],
								'compare' => 'LIKE'
							),
							array(
								'key' => 'last_name',
								'value' => $_REQUEST['search']['value'],
								'compare' => 'LIKE'
							)
						)
					);
				}

			}

			if ( isset( $_REQUEST['length'] ) && ! empty( $_REQUEST['length'] ) ) {
				$args['number'] = sanitize_key( $_REQUEST['length'] );
			}

			if ( isset( $_REQUEST['start'] ) && ! empty( $_REQUEST['start'] ) ) {
				$args['offset'] = sanitize_key( $_REQUEST['start'] );
			}

			$query = new \WP_User_Query( $args );
			$members = $query->get_results();

			$data['data'] = array();

			if ( $query->get_total() > 0 ) {
				foreach ( $members as $key => $member ) {
					um_fetch_user( $member->ID );
					$avatar = um_user('profile_photo', 40);
					$status = $this->has_joined_group( $member->ID, $group_id );
					$profile_url = um_user_profile_url( $member->ID );
					$display_name = "<a href='".esc_url( $profile_url )."'>".um_user('display_name')."</a>";

					if ( 'approved' == $status ) {
						$data['data'][] =  array( 'user' => $avatar .$display_name .'<span class="um-groups-already-member um-right">' .__('Already a member','um-groups') .'</span>'
						);
					}
					elseif ( in_array( $status ,  array('pending_member_review','pending_admin_review') ) ) {
						$data['data'][] =  array( 'user' => $avatar .$display_name .'<a href="javascript:void(0);" data-user_id="'.$member->ID.'" class="um-button um-groups-send-invite um-right um-groups-has-invited disabled"><span class="um-faicon-check"></span> '.__("Invited","um-groups").'</a>'
						);
					}
					else {
						$data['data'][] =  array( 'user' => $avatar .$display_name .'<a href="javascript:void(0);" data-user_id="'.$member->ID.'" class="um-button um-groups-send-invite um-right"><span class="um-faicon-paper-plane-o"></span> '.__("Invite","um-groups").'</a>'
						);
					}
				}
			}

			$data['recordsTotal'] = $query->get_total();
			$data['recordsFiltered'] = $query->get_total();
			$data['draw'] = 0;
			$data['options'] = '';
			$data['files'] = '';
			$data['debug'] = array();
			$data['debug']['request'] = $_REQUEST;
			$data['debug']['members'] = $members;
			$data['debug']['query'] = $query;

			return $data;
		} else {

			$main_query = "";
			$search_in = "";
			$search_query_ids = array();

			if( !empty( $search_keyword ) || !empty( $custom_filter ) ) {

				$doing_search = true;
				$search_query_ids = array( 0 );

				$search_query_args = array(
						'fields'		 => array( 'ID' ),
						'meta_query' => array(
								'relation'			 => 'AND',
								'account_status' => array(
										'key'			 => 'account_status',
										'value'		 => 'approved',
										'compare'	 => '='
								) )
				);

				if( !empty( $search_keyword ) ) {
					$search_query_args = array_merge( $search_query_args, array(
							'search'				 => "*{$search_keyword}*",
							'search_columns' => array( 'login', 'nicename', 'email' )
							) );
				}

				if( !empty( $custom_filter ) ) {
					$filter_query = array(
							'relation' => 'OR',
					);
					foreach( $custom_filter as $filter ) {
						if( !empty( $filter[ 'value' ] ) ) {
							$filter_query[] = array(
									'key'			 => $filter[ 'key' ],
									'value'		 => $filter[ 'value' ],
									'compare'	 => 'LIKE'
							);
						}
					}
					$search_query_args[ 'meta_query' ][] = $filter_query;
				}

				$users = get_users( $search_query_args );

				foreach( $users as $user ) {
					$search_query_ids[] = $user->ID;
				}

				if( !empty( $search_query_ids ) ) {
					$search_in = " AND ID IN( " . implode( ",", $search_query_ids ) . " ) ";
				}
			}

			if ( 'invite_front' == $status ) {

				$invite_people = UM()->options()->get( 'groups_invite_people' );

				// Search Query
				if ( empty( $search_in ) && empty( $search_keyword ) ) {

					if ( 'everyone' == $invite_people ) {

						$main_query = $wpdb->prepare( "
							SELECT DISTINCT ID AS invite_user_id
							FROM {$wpdb->users}
							WHERE ID NOT IN ( SELECT user_id1 FROM {$table_name} WHERE group_id = %d {$search_in} )
							ORDER BY ID DESC {$paginate};", $group_id );

					} else {

						$main_query = apply_filters( "um_groups_invite_front__search_query", $main_query, $group_id, $invite_people, $search_in, $search_keyword, $paginate, $search_query_ids );

					}
				} else {

					if ( 'everyone' == $invite_people ) {

						$main_query = "
							SELECT DISTINCT ID AS invite_user_id
							FROM {$wpdb->users}
							WHERE 1 = 1 {$search_in}
							ORDER BY ID ASC {$paginate};";

					} else {

						$main_query = apply_filters( "um_groups_invite_front__main_query", $main_query, $group_id, $invite_people, $search_in, $search_keyword, $paginate, $search_query_ids );

					}
				}

			} elseif( 'requests' == $status ) {

				if ( um_groups_admin_all_access() ) {
					$main_query = $wpdb->prepare(
						"SELECT *
						FROM {$table_name}
						WHERE group_id = %d AND
						      status IN( 'pending_admin_review' )
						ORDER BY time_stamp DESC
						{$paginate}",
						$group_id
					);
				} else {
					$main_query = $wpdb->prepare(
						"SELECT *
						FROM {$table_name}
						WHERE group_id = %d AND
						      status IN( 'pending_admin_review' )
						ORDER BY time_stamp DESC
						{$paginate}",
						$group_id
					);
				}
			} else {

				if ( um_groups_admin_all_access() ) {
					$main_query = $wpdb->prepare( "
						SELECT *
						FROM {$table_name}
						WHERE group_id = %d
						ORDER BY time_stamp DESC
						{$paginate}",
						$group_id
					);
				} else {
					if ( is_array( $status ) ) {
						$main_query = $wpdb->prepare(
							"SELECT *
							FROM {$table_name}
							WHERE group_id = %d AND
							      status IN('".implode("','",$status)."')
							ORDER BY time_stamp DESC
							{$paginate}",
							$group_id
						);
					} else {
						$main_query = $wpdb->prepare(
							"SELECT *
							FROM {$table_name}
							WHERE group_id = %d AND
							      status = %s
							ORDER BY time_stamp DESC
							{$paginate}",
							$group_id,
							$status
						);
					}

				}

			}

			$members = $wpdb->get_results( $main_query );

			$um_groups_last_query = $wpdb->last_query;

			$arr_members = array();

			if ( ! empty( $members ) && ! empty( $main_query ) ) {
				foreach ( $members as $key ) {
					// Invite Users
					if ( isset( $key->invite_user_id ) ) {
						$has_joined = $this->has_joined_group( $key->invite_user_id, $group_id );

						um_fetch_user( $key->invite_user_id );
						$avatar = um_user('profile_photo', 60);

						$user = get_userdata($key->invite_user_id );
						if ( $user === false ) {
							continue;
						}

						$arr_member = array(
							"group_id"	=> $group_id,
							"user"  => array(
								"id"		=> $key->invite_user_id,
								"avatar" 	=> $avatar,
								"name" 		=> um_user('display_name'),
								"status"	=> $status,
								"url"		=> um_user_profile_url($key->invite_user_id  ) ,
								"description" 	=> um_user('description'),
								"user_login"	=> um_user('user_login'),
								"user_email"	=> um_user('user_email'),
								"has_joined" => $has_joined
							)
						);
					}
					else{

						$has_joined = $this->has_joined_group( $key->user_id1, $group_id );

						um_fetch_user( $key->user_id1 );
						$avatar = um_user('profile_photo', 60);

						$user = get_userdata( $key->user_id1 );
						if ( $user === false ) {
							continue;
						}

						$date_joined = strtotime( $key->date_joined );
						$time_stamp = strtotime( $key->time_stamp );
						if( $date_joined < 0 ){
							$date_joined = $time_stamp;
						}

						$arr_member = array(
								"group_id"		 => $group_id,
								"id"					 => $key->id,
								"user"				 => array(
									"id"		      => $key->user_id1,
									"avatar" 	    => $avatar,
									"name" 		    => um_user('display_name'),
									"status"	    => $status,
									"url"		      => um_user_profile_url( $key->user_id1 ),
									"description" => um_user('description'),
									"user_login"	=> um_user('user_login'),
									"user_email"	=> um_user('user_email'),
									"has_joined"  => $has_joined,
									"joined"      => strtotime( $key->date_joined ) < 0 ? '' : human_time_diff( strtotime( $key->date_joined ), current_time('timestamp') ) . __(' ago','um_groups'),
									"joined_raw"	=> $key->date_joined,
								),
								"group_status" => array( 'slug' => $key->status, 'title' => $this->join_status[$key->status] ),
								"group_role"	 => array( 'slug' => $key->role, 'title' => $this->group_roles[$key->role] ),
								"actions"			 => array(
										'user_id' => $key->user_id1,
								),
								"user_login"	 => um_user( 'user_login' ),
								"user_email"	 => um_user( 'user_email' ),
								"timestamp"		 => $time_stamp,
						);
					}

					array_push( $arr_members ,  $arr_member );
				}
			}


			$raw_data = array(
				'data' => $data,
				'members' => $arr_members,
				'status' => $status,
				'query' => $um_groups_last_query,
				'found_members' => count( $arr_members ),
				'paginate' => $paginate,
				'search_query_results' => $search_query_ids,
				'search_query' => $search_query,
				'search_in' => $search_in,
				'user_search_query_args' => $search_query_args,
				'search_keyword' => $search_keyword,
				'doing_search' => $doing_search
			);

			return $raw_data;
		}
	}


	/**
	 * Load more groups
	 * @return array
	 */
	function load_more_groups() {
		UM()->check_ajax_nonce();

		$args = array(
			'avatar_size'	 => '',
			'show_actions' => false
		);

		if ( isset( $_REQUEST['settings'] ) && is_array( $_REQUEST['settings'] ) ) {
			$args = array_merge( $args, $_REQUEST['settings'] );
		}

		ob_start();
		do_action('pre_groups_shortcode_query_list', $args );
		do_action('um_groups_directory', $args );
		$html = ob_get_clean();

		return wp_send_json( array(
			'html' => $html,
			'args' => $args,
			'found' => UM()->Groups()->api()->results
		) );
	}


	/**
	 * Add a member of a group
	 * @return json
	 */
	function add_member() {
		UM()->admin()->check_ajax_nonce();

		global $wpdb;

		$group_id = sanitize_key( $_REQUEST['group_id'] );
		$user_id = sanitize_key( $_REQUEST['user_id'] );
		$user_id2 = um_user( 'ID' );

		if ( $this->has_joined_group( $user_id, $group_id ) ) {
			return wp_send_json( array('found' => false,  'request' => array( $user_id, $group_id ) ) );
		}

		$arr_member = array(
			'user_id' => $user_id,
			'group_id' => $group_id,
		);

		$table_name = UM()->Groups()->setup()->db_groups_table;

		$inserted = $wpdb->insert(
			$table_name,
			array(
				'group_id'      => $group_id,
				'user_id1'      => $user_id,
				'user_id2'      => $user_id2,
				'status'        => 'approved',
				'role'          => 'member',
				'date_joined'   => date('Y-m-d H:i:s',  current_time( 'timestamp' ) )
			),
			array(
				'%d',
				'%d',
				'%d',
				'%s',
				'%s',
				'%s'
			)
		);

		if( $inserted && $wpdb->insert_id ) {
			$wpdb->query( "
				UPDATE `$table_name` SET
					`user_id1` = $user_id,
					`user_id2` = $user_id2
				WHERE	`id` = $wpdb->insert_id;" );
		}

		$this->count_members( $group_id, true );

		return wp_send_json( array('found' => true, 'user' => $arr_member ) );
	}


	/**
	 * Delete member
	 */
	function delete_member() {
		if ( ! empty( $_POST['admin'] ) ) {
			UM()->admin()->check_ajax_nonce();

			global $wpdb;
			$user_id = sanitize_key( $_REQUEST['user_id'] );
			$group_id = sanitize_key( $_REQUEST['group_id'] );

			$table_name = UM()->Groups()->setup()->db_groups_table;

			$wpdb->delete(
				$table_name,
				array(
					'user_id1' => $user_id,
					'group_id' => $group_id
				),
				array(
					'%d',
					'%d'
				)
			);

			$this->count_members( $group_id, true );

			wp_send_json( array('found' => true ) );

		} else {
			UM()->check_ajax_nonce();

			global $wpdb;

			if ( empty( $_REQUEST['user_id'] ) || empty( $_POST['group'] ) ) {
				wp_send_json_error( __( 'Wrong request', 'um-groups' ) );
			}

			$user_id = sanitize_key( $_REQUEST['user_id'] );
			$group_id = UM()->Groups()->members()->get_group_by_hash( sanitize_key( $_POST['group'] ) );

			if ( empty( $group_id ) || empty( $user_id ) ) {
				wp_send_json_error( __( 'Wrong request', 'um-groups' ) );
			}

			$table_name = UM()->Groups()->setup()->db_groups_table;

			$wpdb->delete(
				$table_name,
				array(
					'user_id1' => $user_id,
					'group_id' => $group_id
				),
				array(
					'%d',
					'%d'
				)
			);

			$this->count_members( $group_id, true );

			wp_send_json_success();
		}
	}


	/**
	 * Change a member's group role
	 */
	function change_member_group_role() {
		$roles_swap_menus = array(
			'admin'     => __( 'Make Admin', 'um-groups' ),
			'member'    => __( 'Make Member', 'um-groups' ),
			'moderator' => __( 'Make Moderator', 'um-groups' ),
		);

		if ( ! empty( $_POST['admin'] ) ) {
			UM()->admin()->check_ajax_nonce();

			global $wpdb;
			$user_id = sanitize_key( $_REQUEST['user_id'] );
			$group_id = sanitize_key( $_REQUEST['group_id'] );
			$role = sanitize_text_field( $_REQUEST['role'] );
			$table_name = UM()->Groups()->setup()->db_groups_table;

			$current_group_role = $wpdb->get_var( $wpdb->prepare(
				"SELECT role
				FROM {$table_name}
				WHERE user_id1 = %d AND
				      group_id = %d",
				$user_id,
				$group_id
			) );

			$wpdb->update(
				$table_name,
				array(
					'role' => $role
				),
				array(
					'user_id1' => $user_id,
					'group_id' => $group_id
				),
				array(
					'%s'
				),
				array(
					'%d',
					'%d'
				)
			);

			do_action( 'um_groups_after_member_changed_role', $user_id, $group_id, $role, $current_group_role );
			do_action( "um_groups_after_member_changed_role__{$role}", $user_id, $group_id, $role, $current_group_role );

			wp_send_json( array(
				'found'             => true,
				'role'              => $this->group_roles[ $role ],
				'role_slug'         => $role,
				'success_message'   => sprintf( __( 'Role changed to %s', 'um-groups' ), $this->group_roles[ $role ] ),
				'menus'             => $roles_swap_menus,
				'previous_role'     => $current_group_role
			) );
		} else {
			UM()->check_ajax_nonce();

			global $wpdb;
			$user_id = sanitize_key( $_REQUEST['user_id'] );
			$group_id = UM()->Groups()->members()->get_group_by_hash( sanitize_key( $_POST['group'] ) );

			$role = sanitize_text_field( $_REQUEST['role'] );
			$table_name = UM()->Groups()->setup()->db_groups_table;

			$current_group_role = $wpdb->get_var( $wpdb->prepare(
				"SELECT role
				FROM {$table_name}
				WHERE user_id1 = %d AND
				      group_id = %d",
				$user_id,
				$group_id
			) );

			$wpdb->update(
				$table_name,
				array(
					'role' => $role
				),
				array(
					'user_id1' => $user_id,
					'group_id' => $group_id
				),
				array(
					'%s'
				),
				array(
					'%d',
					'%d'
				)
			);

			do_action( 'um_groups_after_member_changed_role', $user_id, $group_id, $role, $current_group_role );
			do_action( "um_groups_after_member_changed_role__{$role}", $user_id, $group_id, $role, $current_group_role );

			$user_data = $this->get_group_user_data( $user_id, $group_id );
			$dropdown_actions = UM()->Groups()->members()->build_user_actions( $user_id, $user_data, $group_id );

			wp_send_json_success( array(
				'role'              => $this->group_roles[ $role ],
				'role_slug'         => $role,
				'success_message'   => sprintf( __( 'Role changed to %s', 'um-groups' ), $this->group_roles[ $role ] ),
				'dropdown_actions'  => $dropdown_actions,
			) );
		}
	}



	/**
	 * Get user data from group
	 *
	 * @param int $user_id
	 * @param int $group_id
	 *
	 * @return array
	 */
	function get_group_user_data( $user_id, $group_id ) {
		global $wpdb;

		$user_data = $wpdb->get_row( $wpdb->prepare(
			"SELECT `role`, `date_joined`, `status` AS ID
			FROM {$wpdb->prefix}um_groups_members
			WHERE user_id1 = %d AND 
				  group_id = %d",
			$user_id,
			$group_id
		), ARRAY_A );

		return apply_filters( 'um_get_group_user_data', $user_data, $user_id, $group_id );
	}


	/**
	 * Change a member's group status
	 * @return string
	 */
	function change_member_group_status() {
		UM()->admin()->check_ajax_nonce();

		global $wpdb;
		$user_id = sanitize_key( $_REQUEST['user_id'] );
		$group_id = sanitize_key( $_REQUEST['group_id'] );
		$status = sanitize_text_field( $_REQUEST['status'] );
		$table_name = UM()->Groups()->setup()->db_groups_table;

		$wpdb->update(
			$table_name,
			array(
				'status' => $status
			),
			array(
				'user_id1' => $user_id,
				'group_id' => $group_id
			),
			array(
				'%s'
			),
			array(
				'%d',
				'%d'
			)
		);

		do_action("um_groups_after_member_changed_status", $user_id, $group_id, $status );
		do_action("um_groups_after_member_changed_status__{$status}", $user_id, $group_id, false, false, false );

		return wp_send_json( array('found' => true, 'status' => $this->join_status[ $status ], 'status_slug' => $status ) );
	}


	/**
	 * Send invitation mail
	 */
	function send_invitation_mail() {
		if ( ! empty( $_POST['admin'] ) ) {
			UM()->admin()->check_ajax_nonce();

			global $wpdb;

			$user_id = sanitize_key( $_REQUEST['user_id'] );
			$group_id = sanitize_key( $_REQUEST['group_id'] );

			$data = $this->invite_user( $user_id, get_current_user_id(), $group_id );

			wp_send_json( array('found' => true, 'data' => $data ) );
		} else {
			UM()->check_ajax_nonce();

			global $wpdb;

			$user_id = sanitize_key( $_REQUEST['user_id'] );
			$group_id = UM()->Groups()->members()->get_group_by_hash( sanitize_key( $_REQUEST['group'] ) );

			$data = $this->invite_user( $user_id, get_current_user_id(), $group_id );

			wp_send_json_success( $data );
		}
	}


	/**
	 * Get member group statuses
	 * @return array
	 */
	public function get_member_statuses() {
		return $this->join_status;
	}


	/**
	 * Get member group roles
	 * @return array
	 */
	public function get_member_roles() {
		return $this->group_roles;
	}


	/**
	 * Search members
	 * @return json
	 */
	function search_member() {
		$args = array(
			'search'         => sanitize_text_field( $_REQUEST['search'] ),
			'search_columns' => array( 'user_login', 'user_email' )
		);
		$user_query = new \WP_User_Query( $args );
		$group_id = sanitize_key( $_REQUEST['group_id'] );

		$user = $user_query->get_results();
		$arr_user = array();

		if ( $user ) {

			$user_id = $user[0]->data->ID;

			um_fetch_user( $user_id );
			$arr_user['ID'] = um_user('ID');
			$arr_user['name'] = um_user('display_name');
			$arr_user['avatar'] = um_user( 'profile_photo', 'original' );
			$arr_user['role'] = UM()->roles()->get_role_name( um_user( 'role' ) )?:'';

			$user_id2 = $this->has_joined_group( $user_id, $group_id );
			$arr_user['has_joined'] = $user_id2;

			um_fetch_user( $user_id2 );
			$arr_user['added_by'] = um_user('display_name');

			return wp_send_json( array('found' => true , 'user' => $arr_user ) );

		}

		return wp_send_json( array( 'found' => false, 'user' => $arr_user ) );
	}


	/**
	 * Search member suggestions
	 * @return json string
	 */
	function search_member_suggest() {
		UM()->admin()->check_ajax_nonce();

		$search = '*' . sanitize_text_field( $_REQUEST['q'] ) . '*';
		$args = array(
				'search'				 => $search,
				'search_columns' => array( 'user_login', 'user_email' )
		);
		$user_query = new \WP_User_Query( $args );
		$users = $user_query->get_results();

		if ( count( $users ) ) {
			$group_id = sanitize_key( $_REQUEST['group_id'] );

			$arr_users = array();
			foreach ( $users as $user ) {
				$has_joined = $this->has_joined_group( $user->ID, $group_id );
				if ( $has_joined ) {
					$user_found = sprintf( __( '<strong>%s - %s</strong> - already a member', 'um-groups' ), $user->user_email, $user->user_login );
				} else {
					$user_found = sprintf( __( '<strong>%s - %s</strong>', 'um-groups' ), $user->user_email, $user->user_login );
				}
				$arr_users[] = $user_found;
			}
			return wp_send_json( implode( "\n", $arr_users ) );
		}
		return wp_send_json( __( 'Nothing found', 'um-groups' ) );
	}


	/**
	 * Join group
	 * @return json
	 */
	function ajax_join_group() {
		UM()->check_ajax_nonce();

		$group_id = sanitize_key( $_REQUEST['group_id'] );
		$user_id = get_current_user_id();

		$join_status = $this->join_group( $user_id, $user_id, $group_id );
		$join_status['group_id'] = $group_id;
		$join_status['members'] = $this->count_members( $group_id, true );

		return wp_send_json( $join_status );
	}


	/**
	 * Leave group
	 * @return json
	 */
	function ajax_leave_group() {
		UM()->check_ajax_nonce();

		$group_id = sanitize_key( $_REQUEST['group_id'] );
		$user_id = get_current_user_id();

		$leave_status = $this->leave_group( $user_id, $group_id );
		$leave_status['group_id'] = $group_id;
		$leave_status['members'] = $this->count_members( $group_id, true );

		return wp_send_json( $leave_status );
	}


	/**
	 * Confirm group invite
	 * @return json
	 */
	function ajax_confirm_invite() {
		UM()->check_ajax_nonce();

		if ( empty( $_REQUEST[ 'group_id' ] ) ) {
			$group_id = get_the_ID();
		}
		else {
			$group_id = sanitize_key( $_REQUEST[ 'group_id' ] );
		}
		if ( get_post_type( $group_id ) !== 'um_groups' ) {
			return wp_send_json_error( 'Wrong `group_id` parameter.' );
		}

		$user_id = get_current_user_id();

		$join_status = UM()->Groups()->member()->confirm_invitation( $group_id, $user_id );
		$join_status['group_id'] = $group_id;
		$join_status['members'] = $this->count_members( $group_id, true );

		return wp_send_json( array( $join_status, 'group_id' => $group_id, 'user_id' => $user_id ) );
	}


	/**
	 * Confirm group invite
	 * @return json
	 */
	function ajax_ignore_invite() {
		UM()->check_ajax_nonce();

		$group_id = sanitize_key( $_REQUEST['group_id'] );
		$user_id = get_current_user_id();

		$join_status = UM()->Groups()->member()->reject_invitation( $group_id, $user_id );
		$join_status['group_id'] = $group_id;
		$join_status['members'] = $this->count_members( $group_id, true );

		return wp_send_json( array( $join_status, 'group_id' => $group_id, 'user_id' => $user_id )  );
	}


	/**
	 * Get own groups
	 * @param  integer $user_id
	 * @return $arr_groups
	 */
	function get_own_groups( $user_id = null ) {

		if( ! is_user_logged_in() ) return false;

		if( is_null( $user_id ) ){
			$user_id = get_current_user_id();
		}

		$args = array(
			'post_type' => 'um_groups',
			'author' => $user_id,
		);

		$results = new \WP_Query( $args );

		UM()->Groups()->api()->own_groups_count = $results->found_posts;

		return $results->posts;
	}


	/**
	 * Get owned groups total
	 * @param  integer $user_id
	 * @return integer
	 */
	function get_own_groups_count( $user_id = null ) {
		return UM()->Groups()->api()->own_groups_count;
	}


	/**
	 * Get invite list groups
	 *
	 * @param int $user_id
	 *
	 * @return array
	 */
	function get_groups_invites_list( $user_id = null ) {
		global $wpdb;
		$table_name = UM()->Groups()->setup()->db_groups_table;

		$groups_array = $wpdb->get_col( $wpdb->prepare(
			"SELECT DISTINCT group_id
			FROM {$table_name}
			WHERE invites = 1 AND
				  status = %s AND
				  user_id1 = %d",
			'pending_member_review',
			$user_id
		) );

		if ( ! empty( $groups_array ) ) {
			$groups = new \WP_Query( array(
				'post_type'      => 'um_groups',
				'posts_per_page' => -1,
				'post__in'       => $groups_array
			) );

			return $groups->posts;
		}

		return array();
	}


	/**
	 * Get groups
	 *
	 * @param  array $args
	 * @return array
	 */
	function get_groups( $args ) {
		global $wpdb, $post;

		extract( $args );

		$query_args = array();

		// Prepare for BIG SELECT query
		$wpdb->query( 'SET SQL_BIG_SELECTS=1' );


		// Filter by category
		if ( ! empty( $args['category'] ) ) {
			$query_args['tax_query'][] = array(
				'taxonomy'  => 'um_group_categories',
				'field'     => 'id',
				'terms'     => $args['category'],
			);
		}

		// Filter by tags
		if ( ! empty( $args['tags'] ) ) {
			$query_args['tax_query'][] = array(
				'taxonomy'  => 'um_group_tags',
				'field'     => 'id',
				'terms'     => $args['tags'],
			);
		}

		// sort
		if ( ! empty( $args['sort'] ) ) {
			$sort = explode( '_', $args['sort'] );
			$order = strtoupper( $sort[1] );
			if ( $sort[0] === 'members' ) {
				$query_args['orderby'] = 'meta_value_num';
				$query_args['meta_key'] = 'um_groups_members_count_approved';
			} else {
				$query_args['orderby'] = $sort[0];
			}
			$query_args['order'] = $order;
		}

		$query_args = apply_filters( 'um_prepare_groups_query_args', $query_args , $args );

		if ( isset( $args['page'] ) ) {
			$groups_page = $args['page'];
		} else {
			$groups_page = isset( $_REQUEST['groups_page'] ) ? sanitize_key( $_REQUEST['groups_page'] ) : 1;
		}

		$query_args['paged'] = $groups_page;

		// number of profiles for mobile
		if ( UM()->mobile()->isMobile() && isset( $groups_per_page_mobile ) ) {
			$groups_per_page = $groups_per_page_mobile;
		}

		$query_args['posts_per_page'] = $groups_per_page;

		if ( isset( $args['posts_per_page'] ) ) {
			$query_args['posts_per_page'] = $args['posts_per_page'];
		}

		if ( ! is_user_logged_in() ) {
			$query_args['meta_query'] = array(
				array(
					'key'       => '_um_groups_privacy',
					'value'     => 'hidden',
					'compare'   => '!='
				)
			);
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

			$private_not_joined_groups = array_diff( (array) $private_groups, $groups_joined );

			if ( ! empty( $private_not_joined_groups ) ) {
				$query_args['post__not_in'] = $private_not_joined_groups;
			}
		}

		do_action('um_groups_before_query', $query_args );

		$groups = new \WP_Query( $query_args );

		do_action('um_groups_after_query', $query_args, $groups );

		$array['raw'] = $groups;

		$array['groups'] = isset( $groups->posts ) && ! empty( $groups->posts ) ? $groups->posts : array();

		$array['total_groups'] = (isset( $max_groups ) && $max_groups && $max_groups <= $groups->found_posts ) ? $max_groups : $groups->found_posts;

		$array['page'] = $groups_page;

		$array['total_pages'] = ceil( $array['total_groups'] / $groups_per_page );

		$array['groups_per_page'] = $groups_per_page;

		for( $i = $array['page']; $i <= $array['page'] + 2; $i++ ) {
			if ( $i <= $array['total_pages'] &&  $i > 0 ) {
				$pages_to_show[] = $i;
			}
		}

		if ( isset( $pages_to_show ) && count( $pages_to_show ) < 5 ) {
			$pages_needed = 5 - count( $pages_to_show );

			for ( $c = $array['page']; $c >= $array['page'] - 2; $c-- ) {
				if ( !in_array( $c, $pages_to_show )  ) {
					$pages_to_add[] = $c;
				}
			}
		}

		if ( isset( $pages_to_add ) ) {

			asort( $pages_to_add );
			$pages_to_show = array_merge( (array)$pages_to_add, $pages_to_show );

			if ( count( $pages_to_show ) < 5 ) {
				if ( max($pages_to_show) - $array['page'] >= 2 ) {
					$pages_to_show[] = max($pages_to_show) + 1;
					if ( count( $pages_to_show ) < 5 ) {
						$pages_to_show[] = max($pages_to_show) + 1;
					}
				} else if ( $array['page'] - min($pages_to_show) >= 2 ) {
					$pages_to_show[] = min($pages_to_show) - 1;
					if ( count( $pages_to_show ) < 5 ) {
						$pages_to_show[] = min($pages_to_show) - 1;
					}
				}
			}

			asort( $pages_to_show );

			$array['groups_to_show'] = $pages_to_show;

		} else {

			if ( isset( $pages_to_show ) && count( $pages_to_show ) < 5 ) {
				if ( max($pages_to_show) - $array['page'] >= 2 ) {
					$pages_to_show[] = max($pages_to_show) + 1;
					if ( count( $pages_to_show ) < 5 ) {
						$pages_to_show[] = max($pages_to_show) + 1;
					}
				} else if ( $array['page'] - min($pages_to_show) >= 2 ) {
					$pages_to_show[] = min($pages_to_show) - 1;
					if ( count( $pages_to_show ) < 5 ) {
						$pages_to_show[] = min($pages_to_show) - 1;
					}
				}
			}

			if ( isset( $pages_to_show ) && is_array( $pages_to_show ) ) {

				asort( $pages_to_show );

				$array['groups_to_show'] = $pages_to_show;

			}

		}

		if ( isset( $array['pages_to_show'] ) ) {

			if ( $array['total_pages'] < count( $array['pages_to_show'] ) ) {
				foreach( $array['pages_to_show'] as $k => $v ) {
					if ( $v > $array['total_groups'] ) unset( $array['pages_to_show'][$k] );
				}
			}

			foreach( $array['pages_to_show'] as $k => $v ) {
				if ( (int)$v <= 0 ) {
					unset( $array['pages_to_show'][$k] );
				}
			}

		}

		$array = apply_filters('um_groups_prepare_results_array', $array );

		return $array;
	}


	/**
	 * Get button labels
	 * @param null|string $privacy
	 * @return array
	 */
	public function get_groups_button_labels( $privacy = null ) {

		if ( empty( $this->privacy_groups_button_labels ) || $privacy ) {
			$this->privacy_groups_button_labels = array(
					'public'	 => array(
							'join'	 => __( 'Join Group', 'um-groups' ),
							'leave'	 => __( 'Leave Group', 'um-groups' ),
							'hover'	 => '',
					),
					'public_role'	 => array(
							'join'	 => __( 'Join Group', 'um-groups' ),
							'leave'	 => __( 'Request Sent', 'um-groups' ),
							'_leave' => __( 'Leave Group', 'um-groups' ),
							'hover'	 => __( 'Cancel', 'um-groups' ),
					),
					'private'	 => array(
							'join'	 => __( 'Join Group', 'um-groups' ),
							'leave'	 => __( 'Request Sent', 'um-groups' ),
							'_leave' => __( 'Leave Group', 'um-groups' ),
							'hover'	 => __( 'Cancel', 'um-groups' ),
					),
					'hidden'	 => array(
							'join'	 => __( 'Join Group', 'um-groups' ),
							'leave'	 => __( 'Leave Group', 'um-groups' ),
							'hover'	 => '',
					)
			);
		}

		if ( $privacy && isset( $this->privacy_groups_button_labels[$privacy] ) ) {
			return $this->privacy_groups_button_labels[$privacy];
		}

		return $this->privacy_groups_button_labels;
	}


	/**
	 * Get group category
	 * @param  integer $group_id
	 * @return  $id
	 */
	function get_category( $group_id ) {

	}

	/**
	 * Checks the current group is owned by author_id
	 * @param  integer  $group_id
	 * @param  integer  $author_id
	 * @return boolean
	 */
	function is_own_group( $group_id , $author_id = null ) {

		if( ! $author_id ){
			$author_id = get_current_user_id();
		}

		if( current_user_can('manage_options') ){
			return true;
		}

		$group_author_id = get_post_field('post_author',  $group_id );

		if( $author_id == $group_author_id ){
			return true;
		}

		return false;
	}


	/**
	 * Check if user can invite members in specific group
	 * @param  integer $group_id
	 * @param  integer $user_id
	 * @return boolean
	 */
	function can_invite_members( $group_id = null, $user_id = null ) {
		if( ! $user_id ){
			$user_id = get_current_user_id();
		}

		if( ! $group_id ){
			$group_id = get_the_ID();
		}

		UM()->Groups()->member()->set_group( $group_id, $user_id );

		$can_invite_members = get_post_meta($group_id, '_um_groups_can_invite', true );

		$member_role = UM()->Groups()->member()->get_role();
		$member_status = UM()->Groups()->member()->get_status();

		if( 2 == $can_invite_members && in_array( $member_role , array('admin') ) ){
			return true;
		}

		if( 1 == $can_invite_members && in_array( $member_role , array('admin','moderator') ) ){
			return true;
		}

		if( 0 == $can_invite_members && in_array( $member_role , array('admin','moderator','member') ) ){

			if( in_array( $member_status , array('pending_member_review','rejected') ) ){
				return false;
			}

			return true;

		}

		return false;

	}


	/**
	 * Check if user can manage specific group
	 *
	 * @param $group_id
	 * @param null $user_id
	 * @param string $privacy
	 *
	 * @return bool
	 */
	function can_manage_group( $group_id, $user_id = null, $privacy = 'public' ) {
		if ( ! $user_id ) {
			$user_id = get_current_user_id();
		}

		if ( user_can( $user_id, 'manage_options' ) ) {
			return true;
		}

		UM()->Groups()->member()->set_group( $group_id );

		$member_role = UM()->Groups()->member()->get_role();
		$member_stat = UM()->Groups()->member()->get_status();

		// Public - Moderator Only
		if ( in_array( $member_role , array( 'moderator' ) ) &&  'public' == $privacy ) {
			return false;
		}

		// Public - Admin Only
		if ( in_array( $member_role , array( 'admin' ) ) &&  'public' == $privacy ) {
			return true;
		}

		// Private - Admin Only
		if ( in_array( $member_role , array( 'admin' ) ) && 'approved' == $member_stat && 'private' == $privacy ) {
			return true;
		}

		// Private - Moderator Only
		if ( in_array( $member_role , array( 'moderator' ) ) && 'approved' == $member_stat && 'private' == $privacy ) {
			return false;
		}

		// Private - Admin and Moderator
		if ( in_array( $member_role , array( 'admin', 'moderator' ) ) && 'approved' == $member_stat && 'private' != $privacy ) {
			return true;
		}

		return false;
	}


	/**
	 * Check if user can manage specific group
	 *
	 * @param integer $group_id
	 * @param integer $user_id
	 * @param string $privacy
	 *
	 * @return bool
	 */
	function can_approve_requests( $group_id, $user_id = null, $privacy = 'public' ) {
		if ( ! $user_id ) {
			$user_id = get_current_user_id();
		}

		UM()->Groups()->member()->set_group( $group_id );

		$member_role = UM()->Groups()->member()->get_role();
		$member_stat = UM()->Groups()->member()->get_status();

		if ( in_array( $member_role , array( 'moderator', 'admin' ) ) ) {
			return true;
		}

		return false;
	}


	/**
	 * Check if user can moderate group posts
	 * @param  integer $group_id
	 * @param  integer $user_id
	 * @return bool
	 */
	function can_moderate_posts( $group_id, $user_id = null ) {

		if( ! $user_id ){
			$user_id = get_current_user_id();
		}

		UM()->Groups()->member()->set_group( $group_id );

		$member_role = UM()->Groups()->member()->get_role();
		$member_stat = UM()->Groups()->member()->get_status();

		// Public - Moderator Only
		if( in_array( $member_role , array('moderator','admin') ) ){
			return true;
		}

		if( in_array( $member_stat , array('pending_member_review') ) ){
			return false;
		}

		return false;
	}


	/**
	 * Delete group members
	 * @global \um_ext\um_groups\core\type $wpdb
	 * @param  integer $group_id
	 * @return bool
	 */
	function delete_group_members( $group_id = 0 ) {
		global $wpdb;

		$table_name = UM()->Groups()->setup()->db_groups_table;

		$wpdb->delete(
			$table_name,
			array(
				'group_id' => $group_id
			),
			array(
				'%d'
			)
		);

		return true;
	}


	/**
	 * Get template
	 *
	 * @deprecated since version 2.1.5, use UM()->get_template() instead.
	 *
	 * @param  string $template_name
	 * @param  array  $args
	 */
	function get_template( $template_name = '' , $args = array() ) {
		if( $template_name && file_exists( um_groups_path."templates/".$template_name.".php" ) ){
			include um_groups_path."templates/".$template_name.".php";
		}else{
			echo "Template not found. <br/>".um_groups_path."templates/".$template_name.".php";
		}
	}


	/**
	 * Set last group activity
	 * @param string $group_id
	 */
	function set_group_last_activity( $group_id = null ) {
		update_post_meta( $group_id, 'um_groups_last_active', current_time( 'mysql' ) );
	}


	/**
	 * Get group last activity
	 * @param  integer $group_id
	 * @return string
	 */
	function get_group_last_activity( $group_id = null, $strtotime = false ) {

		$last_active = get_post_meta( $group_id, 'um_groups_last_active', true );

		if( empty( $last_active ) ){
			$post = get_post( $group_id );

			$last_active = $post->post_date;
		}

		if( $strtotime ){
			return strtotime( $last_active );
		}

		return $last_active;
	}


	/**
	 * Show tab count notification
	 * @param  integer  $user_id
	 * @param  string  $tab_key
	 * @param  integer  $group_id
	 * @param  integer $count
	 * @param  string  $active_tab_key
	 * @return boolean
	 */
	function show_tab_count_notification( $user_id, $tab_key, $group_id, $count = 0, $active_tab_key = '' ) {
		if( ! $user_id ){
			$user_id = get_current_user_id();
		}

		$tab_prefs = get_user_meta( $user_id, "um_groups_tab_notif_preferences__{$tab_key}", true );

		if( ! isset( $tab_prefs[ $group_id ] ) ){
			$tab_prefs[ $group_id ] = 0;
		}

		$saved_count = (int)$tab_prefs[ $group_id ];

		if( $count > $saved_count && $count > 0 ){

			if( $active_tab_key == $tab_key ){

					$tab_prefs[ $group_id ] = $count;

					update_user_meta( $user_id, "um_groups_tab_notif_preferences__{$tab_key}", $tab_prefs );
			}

			return true;
		}

		if( $saved_count > $count ){

			$tab_prefs[ $group_id ] = $saved_count - 1;

			update_user_meta( $user_id, "um_groups_tab_notif_preferences__{$tab_key}", $tab_prefs );

		}

		return false;

	}
}