<?php
namespace um_ext\um_profile_completeness\core;


use um\core\Member_Directory_Meta;


if ( ! defined( 'ABSPATH' ) ) exit;


/**
 * Class Profile_Completeness_Member_Directory
 *
 * @package um_ext\um_profile_completeness\core
 */
class Profile_Completeness_Member_Directory {


	var $joined = false;

	/**
	 * Profile_Completeness_Member_Directory constructor.
	 */
	function __construct() {
		add_action( 'um_pre_directory_shortcode', array( &$this, 'directory_enqueue_scripts' ), 10, 1 );

		add_filter( 'um_admin_extend_directory_options_general', array( &$this, 'um_profile_completeness_admin_directory' ), 10, 1 );

		add_filter( 'um_members_directory_sort_fields', array( &$this, 'um_profile_completeness_members_directory_sort_dropdown_options' ), 10, 1 );
		add_filter( 'um_members_directory_filter_fields', array( &$this, 'um_profile_completeness_members_directory_filter_fields' ), 10, 1 );
		add_filter( 'um_members_directory_filter_types', array( &$this, 'um_profile_completeness_directory_filter_types' ), 10, 1 );
		add_filter( 'um_member_directory_filter_completeness_bar_slider', array( &$this, 'um_profile_completeness_directory_filter_completeness_bar' ), 10, 2 );
		add_filter( 'um_member_directory_filter_slider_range_placeholder', array( &$this, 'filter_completeness_bar_slider_range_placeholder' ), 10, 2 );

		add_filter( 'um_prepare_user_query_args', array( &$this, 'completed_add_search_to_query' ), 40, 2 );

		add_filter( 'um_modify_sortby_parameter', array( &$this, 'sortby_completeness' ), 100, 2 );

		add_filter( 'um_query_args_completeness_bar__filter',  array( $this, 'completeness_filter_query' ), 10, 4 );

		add_action( 'um_pre_users_query', array( &$this, 'completed_add_search_to_query_meta' ), 10, 2 );
		add_action( 'um_pre_users_query', array( &$this, 'add_sortby_custom' ), 10, 3 );
		add_filter( 'um_query_args_completeness_bar__filter_meta',  array( $this, 'completeness_filter_query_meta' ), 10, 6 );
	}


	/**
	 * Enqueue scripts
	 *
	 */
	function directory_enqueue_scripts() {
		wp_enqueue_script( 'um_profile_completeness' );
		wp_enqueue_style( 'um_profile_completeness' );
	}


	/**
	 * Admin options for directory filtering
	 *
	 * @param $fields
	 *
	 * @return array
	 */
	function um_profile_completeness_admin_directory( $fields ) {
		$additional_fields = array(
			array(
				'id'    => '_um_has_completed_profile',
				'type'  => 'checkbox',
				'label' => __( 'Only show members who have completed their profile', 'um-profile-completeness' ),
				'value' => UM()->query()->get_meta_value( '_um_has_completed_profile', null, 'na' ),
			),
			array(
				'id'            => '_um_has_completed_profile_pct',
				'type'          => 'text',
				'label'         => __( 'Required completeness (%)', 'um-profile-completeness' ),
				'value'         => UM()->query()->get_meta_value('_um_has_completed_profile_pct', null, 'na' ),
				'conditional'   => array( '_um_has_completed_profile', '=', '1' ),
				'size'          => 'small'
			)
		);

		return array_merge( $fields, $additional_fields );
	}


	/**
	 * @param $options
	 *
	 * @return mixed
	 */
	function um_profile_completeness_members_directory_sort_dropdown_options( $options ) {
		$options['most_completed'] = __( 'Most completed', 'um-profile-completeness' );
		$options['least_completed'] = __( 'Least completed', 'um-profile-completeness' );

		return $options;
	}


	/**
	 * @param $options
	 *
	 * @return mixed
	 */
	function um_profile_completeness_members_directory_filter_fields( $options ) {
		$options['completeness_bar'] = __( 'Profile Completeness', 'um-profile-completeness' );

		return $options;
	}


	/**
	 * @param $filters
	 *
	 * @return mixed
	 */
	function um_profile_completeness_directory_filter_types( $filters ) {
		$filters['completeness_bar'] = 'slider';

		return $filters;
	}


	/**
	 * @param $range
	 *
	 * @return array|bool
	 */
	function um_profile_completeness_directory_filter_completeness_bar( $range, $directory_data ) {
		global $wpdb;

		$meta = $wpdb->get_row(
			"SELECT MIN( meta_value ) as min_meta, 
			MAX( meta_value ) as max_meta, 
			COUNT( DISTINCT meta_value ) as amount 
			FROM {$wpdb->usermeta} 
			WHERE meta_key = '_completed'",
		ARRAY_A );

		if ( empty( $meta ) || ! isset( $meta['amount'] ) || $meta['amount'] === 1 ) {
			$range = false;
		} elseif ( isset( $meta['min_meta'] ) && isset( $meta['max_meta'] ) ) {
			if ( ! empty( $directory_data['has_completed_profile'] ) && ! empty( $directory_data['has_completed_profile_pct'] ) ) {
				$range = array( absint( $directory_data['has_completed_profile_pct'] ), $meta['max_meta'] );
			} else {
				$range = array( 0, $meta['max_meta'] );
			}
		}

		return $range;
	}


	/**
	 * @param $placeholder
	 * @param $filter
	 *
	 * @return array
	 */
	function filter_completeness_bar_slider_range_placeholder( $placeholder, $filter ) {
		if ( $filter == 'completeness_bar' ) {
			return array(
				'<strong>' . __( 'Profile Completed', 'um-profile-completeness' ) . ':</strong>&nbsp;{value}%',
				'<strong>' . __( 'Profile Completed', 'um-profile-completeness' ) . ':</strong>&nbsp;{min_range} - {max_range}%',
			);
		}

		return $placeholder;
	}


	/**
	 * @param $query_args
	 * @param $directory_data
	 *
	 * @return mixed
	 */
	function completed_add_search_to_query( $query_args, $directory_data ) {
		if ( ! empty( $directory_data['has_completed_profile'] ) && ! empty( $directory_data['has_completed_profile_pct'] ) ) {

			$completed = absint( $directory_data['has_completed_profile_pct'] ) > 100 ? 100 : absint( $directory_data['has_completed_profile_pct'] );

			if ( empty( $query_args['meta_query'] ) ) {
				$query_args['meta_query'] = array();
			}

			$query_args['meta_query'][] = array(
				'key'       => '_completed',
				'value'     => $completed,
				'compare'   => '>=',
				'type'      =>'NUMERIC'
			);
		}

		return $query_args;
	}


	/**
	 * @param $query_args
	 * @param $sortby
	 *
	 * @return mixed
	 */
	function sortby_completeness( $query_args, $sortby ) {
		if ( $sortby != 'most_completed' && $sortby != 'least_completed' ) {
			return $query_args;
		}

		if ( empty( $query_args['meta_query'] ) ) {
			$query_args['meta_query'] = array();
		}

		$query_args['meta_query'][] = array(
			'relation'      => 'OR',
			array(
				'key'       => '_completed',
				'compare'   => 'EXISTS',
				'type'      => 'NUMERIC',
			),
			'no_complete' => array(
				'key'       => '_completed',
				'compare'   => 'NOT EXISTS',
				'type'      => 'NUMERIC',
			),
		);

		if ( $sortby == 'most_completed' ) {

			$query_args['orderby'] = array( 'no_complete' => 'DESC', 'user_registered' => 'DESC' );
			unset( $query_args['order'] );

		} elseif ( $sortby == 'least_completed' ) {

			$query_args['orderby'] = array( 'no_complete' => 'ASC', 'user_registered' => 'DESC' );
			unset( $query_args['order'] );

		}

		return $query_args;
	}


	/**
	 * @param $query
	 * @param $field
	 * @param $value
	 * @param $filter_type
	 *
	 * @return array|bool
	 */
	function completeness_filter_query( $query, $field, $value, $filter_type ) {
		$min = min( $value );
		$max = max( $value );

		if ( $min == $max ) {
			$query = array(
				'key'       => '_completed',
				'value'     => $min,
			);
		} else {
			$query = array(
				'key'       => '_completed',
				'value'     => array_map( 'absint', $value ),
				'compare'   => 'BETWEEN',
				'type'      => 'NUMERIC',
				'inclusive' => true,
			);
		}

		UM()->member_directory()->custom_filters_in_query[ $field ] = $value;

		return $query;
	}


	/**
	 * @param $query
	 * @param $directory_data
	 */
	function completed_add_search_to_query_meta( $query, $directory_data ) {
		if ( ! empty( $directory_data['has_completed_profile'] ) && ! empty( $directory_data['has_completed_profile_pct'] ) ) {
			$completed = absint( $directory_data['has_completed_profile_pct'] ) > 100 ? 100 : absint( $directory_data['has_completed_profile_pct'] );

			global $wpdb;

			if ( ! $this->joined ) {
				$query->joins[] = "LEFT JOIN {$wpdb->prefix}um_metadata umm_completed ON ( umm_completed.user_id = u.ID AND umm_completed.um_key = '_completed' )";
				$this->joined = true;
			}

			$query->where_clauses[] = $wpdb->prepare( "CAST( umm_completed.um_value AS SIGNED ) >= %d", $completed );
		}
	}


	/**
	 * @param $query
	 * @param $directory_data
	 * @param $sortby
	 */
	function add_sortby_custom( $query, $directory_data, $sortby ) {
		if ( $sortby != 'most_completed' && $sortby != 'least_completed' ) {
			return;
		}

		$order = $sortby == 'most_completed' ? 'DESC' : 'ASC';

		global $wpdb;
		if ( ! $this->joined ) {
			$query->joins[] = "LEFT JOIN {$wpdb->prefix}um_metadata umm_completed ON ( umm_completed.user_id = u.ID AND umm_completed.um_key = '_completed' )";
			$this->joined = true;
		}
		$query->sql_order = " ORDER BY CAST( umm_completed.um_value AS SIGNED ) {$order}, u.user_registered DESC";
	}


	/**
	 * @param bool $skip
	 * @param Member_Directory_Meta $query
	 * @param $field
	 * @param $value
	 * @param $filter_type
	 * @param bool $is_default
	 *
	 * @return bool
	 */
	function completeness_filter_query_meta( $skip, $query, $field, $value, $filter_type, $is_default ) {
		global $wpdb;

		$skip = true;

		$min = min( $value );
		$max = max( $value );

		if ( ! $this->joined ) {
			$query->joins[] = "LEFT JOIN {$wpdb->prefix}um_metadata umm_completed ON ( umm_completed.user_id = u.ID AND umm_completed.um_key = '_completed' )";
			$this->joined = true;
		}

		if ( $min == $max ) {
			$query->where_clauses[] = $wpdb->prepare( "CAST( umm_completed.um_value AS SIGNED ) = %d", $min );
			$query = array(
				'key'       => '_completed',
				'value'     => $max,
			);
		} else {
			$query->where_clauses[] = $wpdb->prepare( "CAST( umm_completed.um_value AS SIGNED ) BETWEEN %d AND %d", $min, $max );
		}

		if ( ! $is_default ) {
			$query->custom_filters_in_query[ $field ] = $value;
		}

		return $skip;
	}
}