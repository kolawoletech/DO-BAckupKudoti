<?php
namespace um_ext\um_groups\core;


if ( ! defined( 'ABSPATH' ) ) exit;


/**
 * Class Groups_Invites
 *
 * @package um_ext\um_groups\core
 */
class Groups_Invites extends \um\core\Member_Directory {


	/**
	 * @var int
	 */
	var $profiles_per_page = 10;


	/**
	 * Groups_Invites constructor.
	 */
	function __construct() {
		parent::__construct();

		add_action( 'wp_ajax_nopriv_um_groups_get_invites', array( $this, 'ajax_get_members' ) );
		add_action( 'wp_ajax_um_groups_get_invites', array( $this, 'ajax_get_members' ) );
	}


	/**
	 * @param $directory_data
	 */
	function predefined_no_caps( $directory_data ) {
		//predefined result for user without capabilities to see other members
		if ( is_user_logged_in() && ! UM()->roles()->um_user_can( 'can_view_all' ) ) {
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


	/**
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

		return apply_filters( 'um_get_group_users_data_invites', $data, $user_ids, $group_id );
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
	 * Exclude users from query who is 'waiting_admin_review' and 'approved'
	 *
	 * @param $query
	 */
	function only_not_invited( $query ) {
		global $wpdb;

		$group_id = $query->query_vars['um_group_id'];
		$groups_table_name = UM()->Groups()->setup()->db_groups_table;

		$group_meta = $wpdb->prepare(
			"{$wpdb->users}.ID NOT IN(
				SELECT DISTINCT tbg.user_id1 FROM {$groups_table_name} as tbg
				WHERE tbg.user_id1 = {$wpdb->users}.ID AND tbg.group_id = %d AND tbg.status != 'pending_member_review' 
			)",
			$group_id
		);

		$query->query_where = str_replace(
			'WHERE 1=1',
			"WHERE 1=1 AND (" . $group_meta . " ) ",
			$query->query_where );
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
	 * Handle general search line request
	 */
	function general_search() {
		//general search
		if ( ! empty( $_POST['search'] ) ) {
			// complex using with change_meta_sql function

			$meta_query = array(
				'relation' => 'OR',
				array(
					'value'     => trim( $_POST['search'] ),
					'compare'   => '=',
				),
				array(
					'value'     => trim( $_POST['search'] ),
					'compare'   => 'LIKE',
				),
				array(
					'value'     => trim( serialize( strval( $_POST['search'] ) ) ),
					'compare'   => 'LIKE',
				),
			);

			$meta_query = apply_filters( 'um_groups_invites_general_search_meta_query', $meta_query, $_POST['search'] );

			$this->query_args['meta_query'][] = $meta_query;
		}
	}


	/**
	 * Render member's directory
	 * filters selectboxes
	 *
	 * @param string $filter
	 * @param array $directory_data
	 * @param mixed $default_value
	 * @param bool $admin
	 *
	 * @return string $filter
	 */
	function show_filter( $filter, $directory_data = array(), $default_value = false, $admin = false ) {

		if ( empty( $this->filter_types[ $filter ] ) ) {
			return '';
		}

		$field_key = $filter;
		if ( $filter == 'last_login' ) {
			$field_key = '_um_last_login';
		}
		if ( $filter == 'role' ) {
			$field_key = 'role_select';
		}

		$fields = UM()->builtin()->all_user_fields;

		if ( isset( $fields[ $field_key ] ) ) {
			$attrs = $fields[ $field_key ];
		} else {
			/**
			 * UM hook
			 *
			 * @type filter
			 * @title um_custom_search_field_{$filter}
			 * @description Custom search settings by $filter
			 * @input_vars
			 * [{"var":"$settings","type":"array","desc":"Search Settings"}]
			 * @change_log
			 * ["Since: 2.0"]
			 * @usage
			 * <?php add_filter( 'um_custom_search_field_{$filter}', 'function_name', 10, 1 ); ?>
			 * @example
			 * <?php
			 * add_filter( 'um_custom_search_field_{$filter}', 'my_custom_search_field', 10, 1 );
			 * function my_change_email_template_file( $settings ) {
			 *     // your code here
			 *     return $settings;
			 * }
			 * ?>
			 */
			$attrs = apply_filters( "um_custom_search_field_{$filter}", array(), $field_key );
		}

		/**
		 * UM hook
		 *
		 * @type filter
		 * @title um_search_fields
		 * @description Filter all search fields
		 * @input_vars
		 * [{"var":"$settings","type":"array","desc":"Search Fields"}]
		 * @change_log
		 * ["Since: 2.0"]
		 * @usage
		 * <?php add_filter( 'um_search_fields', 'function_name', 10, 1 ); ?>
		 * @example
		 * <?php
		 * add_filter( 'um_search_fields', 'my_search_fields', 10, 1 );
		 * function my_search_fields( $settings ) {
		 *     // your code here
		 *     return $settings;
		 * }
		 * ?>
		 */
		$attrs = apply_filters( 'um_search_fields', $attrs, $field_key );

		$unique_hash = substr( md5( $directory_data['form_id'] ), 10, 5 );

		ob_start();

		switch ( $this->filter_types[ $filter ] ) {
			default: {

				do_action( "um_member_directory_filter_type_{$this->filter_types[ $filter ]}", $filter, $this->filter_types );

				break;
			}
			case 'select': {

				// getting value from GET line
				$filter_from_url = ! empty( $_GET[ 'filter_' . $filter . '_' . $unique_hash ] ) ? explode( '||', sanitize_text_field( $_GET[ 'filter_' . $filter . '_' . $unique_hash ] ) ) : array();

				if ( isset( $attrs['metakey'] ) && strstr( $attrs['metakey'], 'role_' ) ) {
					$um_roles = UM()->roles()->get_roles( false );

					$attrs['options'] = array();
					foreach ( $um_roles as $key => $value ) {
						$attrs['options'][ $key ] = $value;
					}
				}

				if ( ! empty( $attrs['custom_dropdown_options_source'] ) ) {
					$attrs['custom'] = true;
					$attrs['options'] = UM()->fields()->get_options_from_callback( $attrs, $attrs['type'] );
				}

				if ( isset( $attrs['label'] ) ) {
					$attrs['label'] = strip_tags( $attrs['label'] );
				}

				if ( isset( $attrs['options'] ) && is_array( $attrs['options'] ) ) {
					asort( $attrs['options'] );
				}

				$custom_dropdown = ! empty( $attrs['custom_dropdown_options_source'] ) ? ' data-um-ajax-source="' . $attrs['custom_dropdown_options_source'] . '"' : '';

				if ( ! empty( $attrs['options'] ) || ! empty( $custom_dropdown ) ) { ?>

					<select class="um-s1" id="<?php echo $filter; ?>" name="<?php echo $filter; ?>"
					        data-placeholder="<?php esc_attr_e( stripslashes( $attrs['label'] ), 'ultimate-member' ); ?>"
						<?php echo $custom_dropdown; ?>>

						<option></option>

						<?php if ( ! empty( $attrs['options'] ) ) {
							foreach ( $attrs['options'] as $k => $v ) {

								$v = stripslashes( $v );

								$opt = $v;

								if ( strstr( $filter, 'role_' ) ) {
									$opt = $k;
								}

								if ( isset( $attrs['custom'] ) ) {
									$opt = $k;
								} ?>

								<option value="<?php echo esc_attr( $opt ); ?>" data-value_label="<?php esc_attr_e( $v, 'ultimate-member' ); ?>"
									<?php disabled( ! empty( $filter_from_url ) && in_array( $opt, $filter_from_url ) ) ?>
									<?php selected( $opt == $default_value ) ?>>
									<?php _e( $v, 'ultimate-member' ); ?>
								</option>

							<?php }
						} ?>

					</select>

				<?php }

				break;
			}
			case 'slider': {
				$range = $this->slider_filters_range( $filter, $directory_data );
				$placeholder = $this->slider_range_placeholder( $filter, $attrs );

				if ( $range ) { ?>
					<input type="hidden" id="<?php echo $filter; ?>_min" name="<?php echo $filter; ?>[]" class="um_range_min" value="<?php echo ! empty( $default_value ) ? esc_attr( min( $default_value ) ) : '' ?>" />
					<input type="hidden" id="<?php echo $filter; ?>_max" name="<?php echo $filter; ?>[]" class="um_range_max" value="<?php echo ! empty( $default_value ) ? esc_attr( max( $default_value ) ) : '' ?>" />
					<div class="um-slider" data-field_name="<?php echo $filter; ?>" data-min="<?php echo $range[0] ?>" data-max="<?php echo $range[1] ?>"></div>
					<div class="um-slider-range" data-placeholder="<?php echo esc_attr( $placeholder ); ?>" data-label="<?php echo ( ! empty( $attrs['label'] ) ) ? esc_attr__( stripslashes( $attrs['label'] ), 'ultimate-member' ) : ''; ?>"></div>
				<?php }

				break;
			}
			case 'datepicker': {

				$range = $this->datepicker_filters_range( $filter );

				$label = ! empty( $attrs['label'] ) ? $attrs['label'] : $attrs['title'];

				if ( $range ) { ?>

					<input type="text" id="<?php echo $filter; ?>_from" name="<?php echo $filter; ?>_from" class="um-datepicker-filter"
					       placeholder="<?php esc_attr_e( sprintf( '%s From', stripslashes( $label ) ), 'ultimate-member' ); ?>"
					       data-filter-label="<?php echo esc_attr( stripslashes( $label ) ); ?>"
					       data-date_min="<?php echo $range[0] ?>" data-date_max="<?php echo $range[1] ?>"
					       data-filter_name="<?php echo $filter; ?>" data-range="from" data-value="<?php echo ! empty( $default_value ) ? esc_attr( strtotime( min( $default_value ) ) ) : '' ?>" />
					<input type="text" id="<?php echo $filter; ?>_to" name="<?php echo $filter; ?>_to" class="um-datepicker-filter"
					       placeholder="<?php esc_attr_e( sprintf( '%s To', stripslashes( $label ) ), 'ultimate-member' ); ?>"
					       data-filter-label="<?php echo esc_attr( stripslashes( $label ) ); ?>"
					       data-date_min="<?php echo $range[0] ?>" data-date_max="<?php echo $range[1] ?>"
					       data-filter_name="<?php echo $filter; ?>" data-range="to" data-value="<?php echo ! empty( $default_value ) ? esc_attr( strtotime( max( $default_value ) ) ) : '' ?>" />

				<?php }

				break;
			}
			case 'timepicker': {

				$range = $this->timepicker_filters_range( $filter );

				$label = ! empty( $attrs['label'] ) ? $attrs['label'] : $attrs['title'];

				switch ( $attrs['format'] ) {
					case 'g:i a':
						$js_format = 'h:i a';
						break;
					case 'g:i A':
						$js_format = 'h:i A';
						break;
					case 'H:i':
						$js_format = 'HH:i';
						break;
				}

				if ( $range ) { ?>

					<input type="text" id="<?php echo $filter; ?>_from" name="<?php echo $filter; ?>_from" class="um-timepicker-filter"
					       placeholder="<?php esc_attr_e( sprintf( '%s From', stripslashes( $label ) ), 'ultimate-member' ); ?>"
					       data-filter-label="<?php echo esc_attr( stripslashes( $label ) ); ?>"
					       data-min="<?php echo $range[0] ?>" data-max="<?php echo $range[1] ?>"
					       data-format="<?php echo esc_attr( $js_format ) ?>" data-intervals="<?php echo esc_attr( $attrs['intervals'] ) ?>"
					       data-filter_name="<?php echo $filter; ?>" data-range="from" />
					<input type="text" id="<?php echo $filter; ?>_to" name="<?php echo $filter; ?>_to" class="um-timepicker-filter"
					       placeholder="<?php esc_attr_e( sprintf( '%s To', stripslashes( $label ) ), 'ultimate-member' ); ?>"
					       data-filter-label="<?php echo esc_attr( stripslashes( $label ) ); ?>"
					       data-min="<?php echo $range[0] ?>" data-max="<?php echo $range[1] ?>"
					       data-format="<?php echo esc_attr( $js_format ) ?>" data-intervals="<?php echo esc_attr( $attrs['intervals'] ) ?>"
					       data-filter_name="<?php echo $filter; ?>" data-range="to" />

				<?php }

				break;
			}
		}

		$filter = ob_get_clean();
		return $filter;
	}


	/**
	 * Handle filters request
	 */
	function filters( $directory_data = array() ) {
		//filters

		$filter_query = array();
		if ( ! empty( $directory_data['groups_invites_fields'] ) ) {
			$search_filters = maybe_unserialize( $directory_data['groups_invites_fields'] );
			if ( ! empty( $search_filters ) && is_array( $search_filters ) ) {
				$filter_query = array_intersect_key( $_POST, array_flip( $search_filters ) );
			}
		}

		if ( empty( $filter_query ) ) {
			return;
		}

		foreach ( $filter_query as $field => $value ) {

			switch ( $field ) {
				default:

					$filter_type = $this->filter_types[ $field ];

					/**
					 * UM hook
					 *
					 * @type filter
					 * @title um_query_args_{$field}__filter
					 * @description Change field's query for search at Members Directory
					 * @input_vars
					 * [{"var":"$field_query","type":"array","desc":"Field query"}]
					 * @change_log
					 * ["Since: 2.0"]
					 * @usage
					 * <?php add_filter( 'um_query_args_{$field}__filter', 'function_name', 10, 1 ); ?>
					 * @example
					 * <?php
					 * add_filter( 'um_query_args_{$field}__filter', 'my_query_args_filter', 10, 1 );
					 * function my_query_args_filter( $field_query ) {
					 *     // your code here
					 *     return $field_query;
					 * }
					 * ?>
					 */
					$field_query = apply_filters( "um_query_args_{$field}__filter", false, $field, $value, $filter_type );

					if ( ! $field_query ) {

						switch ( $filter_type ) {
							default:

								$field_query = apply_filters( "um_query_args_{$field}_{$filter_type}__filter", false, $field, $value, $filter_type );

								break;
							case 'select':
								if ( is_array( $value ) ) {
									$field_query = array( 'relation' => 'OR' );

									foreach ( $value as $single_val ) {
										$arr_meta_query = array(
											array(
												'key'       => $field,
												'value'     => trim( $single_val ),
												'compare'   => '=',
											),
											array(
												'key'       => $field,
												'value'     => serialize( strval( trim( $single_val ) ) ),
												'compare'   => 'LIKE',
											),
											array(
												'key'       => $field,
												'value'     => '"' . trim( $single_val ) . '"',
												'compare'   => 'LIKE',
											)
										);

										if ( is_numeric( $single_val ) ) {

											$arr_meta_query[] = array(
												'key'       => $field,
												'value'     => serialize( intval( trim( $single_val ) ) ),
												'compare'   => 'LIKE',
											);

										}

										$field_query = array_merge( $field_query, $arr_meta_query );
									}
								}

								break;
							case 'slider':

								$field_query = array(
									'key'       => $field,
									'value'     => $value,
									'compare'   => 'BETWEEN',
									'inclusive' => true,
								);

								break;
							case 'datepicker':

								$offset = 0;
								if ( isset( $_POST['gmt_offset'] ) && is_numeric( $_POST['gmt_offset'] ) ) {
									$offset = (int) $_POST['gmt_offset'];
								}

								$from_date = (int) min( $value ) + ( $offset * HOUR_IN_SECONDS ); // client time zone offset
								$to_date   = (int) max( $value ) + ( $offset * HOUR_IN_SECONDS ) + DAY_IN_SECONDS - 1; // time 23:59
								$field_query = array(
									'key'       => $field,
									'value'     =>  array( $from_date, $to_date ),
									'compare'   => 'BETWEEN',
									'inclusive' => true,
								);

								break;
							case 'timepicker':

								if ( $value[0] == $value[1] ) {
									$field_query = array(
										'key'       => $field,
										'value'     => $value[0],
									);
								} else {
									$field_query = array(
										'key'       => $field,
										'value'     => $value,
										'compare'   => 'BETWEEN',
										'type'      => 'TIME',
										'inclusive' => true,
									);
								}

								break;
						}

					}

					if ( ! empty( $field_query ) && $field_query !== true ) {
						$this->query_args['meta_query'] = array_merge( $this->query_args['meta_query'], array( $field_query ) );
					}

					break;
				case 'role':
					$value = array_map( 'strtolower', $value );

					if ( ! empty( $this->query_args['role__in'] ) ) {
						$this->query_args['role__in'] = is_array( $this->query_args['role__in'] ) ? $this->query_args['role__in'] : array( $this->query_args['role__in'] );
						$default_role = array_intersect( $this->query_args['role__in'], $value );
						$um_role = array_diff( $value, $default_role );

						foreach ( $um_role as $key => &$val ) {
							$val = 'um_' . str_replace( ' ', '-', $val );
						}
						$this->query_args['role__in'] = array_merge( $default_role, $um_role );
					} else {
						$this->query_args['role__in'] = $value;
					};

					break;
				case 'birth_date':

					$from_date = date( 'Y/m/d', mktime( 0,0,0, date( 'm', time() ), date( 'd', time() ), date( 'Y', time() - min( $value ) * YEAR_IN_SECONDS ) ) );
					$to_date = date( 'Y/m/d', mktime( 0,0,0, date( 'm', time() ), date( 'd', time() ) + 1, date( 'Y', time() - ( max( $value ) + 1 ) * YEAR_IN_SECONDS ) ) );

					$meta_query = array(
						array(
							'key'       => 'birth_date',
							'value'     => array( $to_date, $from_date ),
							'compare'   => 'BETWEEN',
							'type'      => 'DATE',
							'inclusive' => true,
						)
					);

					$this->query_args['meta_query'] = array_merge( $this->query_args['meta_query'], array( $meta_query ) );

					break;
				case 'user_registered':

					$offset = 0;
					if ( isset( $_POST['gmt_offset'] ) && is_numeric( $_POST['gmt_offset'] ) ) {
						$offset = (int) $_POST['gmt_offset'];
					}

					$from_date = date( 'Y-m-d H:s:i', strtotime( date( 'Y-m-d H:s:i', min( $value ) ) . "+$offset hours" ) );
					$to_date = date( 'Y-m-d H:s:i', strtotime( date( 'Y-m-d H:s:i', max( $value ) ) . "+$offset hours" ) );

					$date_query = array(
						array(
							'column'    => 'user_registered',
							'before'    => $to_date,
							'after'     => $from_date,
							'inclusive' => true,
						),
					);

					if ( empty( $this->query_args['date_query'] ) ) {
						$this->query_args['date_query'] = $date_query;
					} else {
						$this->query_args['date_query'] = array_merge( $this->query_args['date_query'], array( $date_query ) );
					}

					break;
				case 'last_login':

					$offset = 0;
					if ( isset( $_POST['gmt_offset'] ) && is_numeric( $_POST['gmt_offset'] ) ) {
						$offset = (int) $_POST['gmt_offset'];
					}

					$from_date = (int) min( $value ) + ( $offset * HOUR_IN_SECONDS ); // client time zone offset
					$to_date   = (int) max( $value ) + ( $offset * HOUR_IN_SECONDS ) + DAY_IN_SECONDS - 1; // time 23:59
					$meta_query = array(
						array(
							'key'       => '_um_last_login',
							'value'     =>  array( $from_date, $to_date ),
							'compare'   => 'BETWEEN',
							'inclusive' => true,
						)
					);

					$this->query_args['meta_query'] = array_merge( $this->query_args['meta_query'], array( $meta_query ) );
					break;
			}

		}
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
	 * Main Query function for getting members via AJAX
	 */
	function ajax_get_members() {
		UM()->check_ajax_nonce();

		global $wpdb;

		$group_id = $this->get_group_by_hash( $_POST['group_id'] );
		$group_data = UM()->query()->post_data( $group_id );

		$directory_data = apply_filters( 'um_group_invites_directory_data', array(
			'header'         => '',
			'header_single'  => '',
			'show_tagline'   => false,
			'show_userinfo'  => false,
			'tagline_fields' => array(),
		), $group_id, $group_data );

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
		$this->filters( $directory_data );

		$this->query_args['um_group_id'] = $group_id;
		$this->query_args = apply_filters( 'um_groups_invites_prepare_user_query_args', $this->query_args, $directory_data );

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
	 * @param array $user_ids
	 * @param array $directory_data
	 * @param int $group_id
	 *
	 * @return array
	 */
	function prepare_users_response( $user_ids, $directory_data, $group_id ) {
		$users_data = $this->get_group_users_data( $user_ids, $group_id );

		$users = array();
		foreach ( $user_ids as $i => $user_id ) {
			$users[ $i ] = $this->build_user_card_data( $user_id, $directory_data );

			$user_data = ! empty( $users_data[ $user_id ] ) ? $users_data[ $user_id ] : false;
			if ( $user_data ) {
				$users[ $i ]['date'] = strtotime( $user_data['date_joined'] ) > 0 ? sprintf( __( 'Joined %s ago', 'um-groups' ), human_time_diff( strtotime( $user_data['date_joined'] ), current_time( 'timestamp' ) ) ) : '';
				$users[ $i ]['is_invited'] = $user_data['status'] === 'pending_member_review';
			}

			ob_start();
			do_action( 'um_groups_users_list_after_details', $user_id, $group_id, false );
			do_action( 'um_groups_users_list_after_details__invites_front', $user_id, $group_id, $user_data, false );
			$users[ $i ]['additional_content'] = ob_get_clean();
		}
		um_reset_user();
		// end of user card

		return array_values( $users );
	}
}