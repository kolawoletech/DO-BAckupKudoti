<?php if ( ! defined( 'ABSPATH' ) ) exit;


/**
 * Class UM_Profile_Completeness_API
 */
class UM_Profile_Completeness_API {


	/**
	 * @var
	 */
	private static $instance;


	/**
	 * @return UM_Profile_Completeness_API
	 */
	static public function instance() {
		if ( is_null( self::$instance ) ) {
			self::$instance = new self();
		}

		return self::$instance;
	}


	/**
	 * UM_Profile_Completeness_API constructor.
	 */
	function __construct() {
		// Global for backwards compatibility.
		$GLOBALS['um_profile_completeness'] = $this;
		add_filter( 'um_call_object_Profile_Completeness_API', array( &$this, 'get_this' ) );

		if ( UM()->is_request( 'admin' ) ) {
			$this->admin();
		}

		$this->enqueue();
		$this->shortcode();
		$this->restrict();
		$this->member_directory();

		add_action( 'plugins_loaded', array( &$this, 'init' ), 0 );

		require_once um_profile_completeness_path . 'includes/core/um-profile-completeness-widget.php';
		add_action( 'widgets_init', array( &$this, 'widgets_init' ) );

		add_action( 'wp_ajax_um_profile_completeness_save_popup', array( $this, 'ajax_save_popup' ) );
		add_action( 'wp_ajax_um_profile_completeness_edit_popup', array( $this, 'ajax_edit_popup' ) );
		add_action( 'wp_ajax_um_profile_completeness_get_widget', array( $this, 'ajax_get_widget' ) );
	}


	/**
	 * @return $this
	 */
	function get_this() {
		return $this;
	}


	/**
	 * @return um_ext\um_profile_completeness\core\Profile_Completeness_Enqueue()
	 */
	function enqueue() {
		if ( empty( UM()->classes['um_profile_completeness_enqueue'] ) ) {
			UM()->classes['um_profile_completeness_enqueue'] = new um_ext\um_profile_completeness\core\Profile_Completeness_Enqueue();
		}

		return UM()->classes['um_profile_completeness_enqueue'];
	}


	/**
	 * @return um_ext\um_profile_completeness\core\Profile_Completeness_Shortcode()
	 */
	function shortcode() {
		if ( empty( UM()->classes['um_profile_completeness_shortcode'] ) ) {
			UM()->classes['um_profile_completeness_shortcode'] = new um_ext\um_profile_completeness\core\Profile_Completeness_Shortcode();
		}

		return UM()->classes['um_profile_completeness_shortcode'];
	}


	/**
	 * @return um_ext\um_profile_completeness\core\Profile_Completeness_Admin()
	 */
	function admin() {
		if ( empty( UM()->classes['um_profile_completeness_admin'] ) ) {
			UM()->classes['um_profile_completeness_admin'] = new um_ext\um_profile_completeness\core\Profile_Completeness_Admin();
		}

		return UM()->classes['um_profile_completeness_admin'];
	}


	/**
	 * @return um_ext\um_profile_completeness\core\Profile_Completeness_Restrict()
	 */
	function restrict() {
		if ( empty( UM()->classes['um_profile_completeness_restrict'] ) ) {
			UM()->classes['um_profile_completeness_restrict'] = new um_ext\um_profile_completeness\core\Profile_Completeness_Restrict();
		}

		return UM()->classes['um_profile_completeness_restrict'];
	}


	/**
	 * @return um_ext\um_profile_completeness\core\Profile_Completeness_Member_Directory()
	 */
	function member_directory() {
		if ( empty( UM()->classes['um_profile_completeness_member_directory'] ) ) {
			UM()->classes['um_profile_completeness_member_directory'] = new um_ext\um_profile_completeness\core\Profile_Completeness_Member_Directory();
		}

		return UM()->classes['um_profile_completeness_member_directory'];
	}


	/**
	 * Init
	 */
	function init() {
		delete_user_meta( 1, 'birthdate' );

		require_once um_profile_completeness_path . 'includes/core/um-profile-completeness-profile.php';
		require_once um_profile_completeness_path . 'includes/core/um-profile-completeness-fields.php';
	}


	/**
	 * Get factors that increase completion
	 *
	 * @param $role_data
	 *
	 * @return array|bool
	 */
	function get_metrics( $role_data ) {
		$array = array();
		$meta = $role_data;
		foreach ( $meta as $k => $v ) {
			if ( strstr( $k, 'progress_' ) ) {
				$k = str_replace( 'progress_', '', $k );
				if ( $k == 'profile_photo' ) {

					if ( um_user( 'profile_photo' ) ) {
						$array['profile_photo'] = $v;
					} elseif ( um_user( 'synced_profile_photo' ) ) {
						$array['synced_profile_photo'] = $v;
					}

					if ( UM()->options()->get( 'use_gravatars' ) ) {
						$array['synced_gravatar_hashed_id'] = $v;
						continue;
					}

				}
				$array[ $k ] = $v;
			}
		}

		return ! empty( $array ) ? $array : false;
	}


	/**
	 * Get user profile progress
	 *
	 * @param $user_id
	 *
	 * @return array|int
	 */
	function get_progress( $user_id ) {
		um_fetch_user( $user_id );

		//get priority role here
		$role_data = UM()->roles()->role_data( um_user( 'role' ) );
		if ( empty( $role_data['profilec'] ) ) {
			return -1;
		}

		// get factors
		$array = $this->get_metrics( $role_data );
		if ( ! $array ) {
			$result = array(
				'req_progress'                  => $role_data['profilec_pct'],
				'progress'                      => 100,
				'steps'                         => '',
				'prevent_browse'                => $role_data['profilec_prevent_browse'],
				'prevent_browse_exclude_pages'  => empty( $role_data['profilec_prevent_browse_exclude_pages'] ) ? '' : $role_data['profilec_prevent_browse_exclude_pages'],
				'prevent_browse_redirect'       => empty( $role_data['profilec_prevent_browse_redirect'] ) ? 0 : $role_data['profilec_prevent_browse_redirect'],
				'prevent_browse_redirect_url'   => empty( $role_data['profilec_prevent_browse_redirect_url'] ) ? '' : $role_data['profilec_prevent_browse_redirect_url'],
				'prevent_profileview'           => $role_data['profilec_prevent_profileview'],
				'prevent_comment'               => $role_data['profilec_prevent_comment'],
				'prevent_bb'                    => $role_data['profilec_prevent_bb'],
			);

			$result = apply_filters( 'um_profile_completeness_get_progress_result', $result, $role_data );

			$result['raw'] = $result;

			update_user_meta( $user_id, '_profile_progress', $result );
			update_user_meta( $user_id, '_completed', 100 );

			return $result;
		}

		// see what user has completed
		$profile_progress = 0;
		$completed = array();
		foreach ( $array as $key => $value ) {
			$custom = apply_filters( 'um_profile_completeness_get_field_progress', false, $key, $user_id );
			if ( $custom ) {
				$profile_progress = $profile_progress + (int)$value;
				$completed[] = $key;
			} else {

				$field_type = UM()->fields()->get_field_type( $key );

				$user_meta = get_user_meta( $user_id, $key, true );
				if ( $field_type == 'multiselect' ) {
					if ( ! empty( $user_meta ) ) {
						$profile_progress = $profile_progress + (int)$value;
						$completed[] = $key;
					}
				} elseif ( $user_meta != '' ) {
					$profile_progress = $profile_progress + (int)$value;
					$completed[] = $key;
				} elseif ( in_array( $key, array( 'user_email' ) ) ) {
					$user = get_user_by( 'ID', $user_id );
					if ( ! empty( $user ) && ! empty( $user->user_email ) ) {
						$profile_progress = $profile_progress + (int)$value;
						$completed[] = $key;
					}
				} elseif ( in_array( $key, array( 'user_url' ) ) ) {
					$user = get_user_by( 'ID', $user_id );
					if ( ! empty( $user ) && ! empty( $user->user_url ) ) {
						$profile_progress = $profile_progress + (int)$value;
						$completed[] = $key;
					}
				} elseif ( in_array( $key, array( 'profile_photo' ) ) ) {
					$user_photo = get_user_meta( $user_id, 'profile_photo', true );
					if ( ! $user_photo ) {
						$user_photo = get_user_meta( $user_id, '_save_synced_profile_photo', true );
					}
					if ( $user_photo ) {
						$profile_progress = $profile_progress + (int) $value;
						$completed[] = $key;
					}
				}
			}
		}

		$result = array(
			'req_progress'                  => $role_data['profilec_pct'],
			'progress'                      => $profile_progress,
			'steps'                         => $array,
			'completed'                     => $completed,
			'prevent_browse'                => ( empty( $role_data['profilec_prevent_browse'] ) ? 0 : 1 ),
			'prevent_browse_exclude_pages'  => empty( $role_data['profilec_prevent_browse_exclude_pages'] ) ? '' : $role_data['profilec_prevent_browse_exclude_pages'],
			'prevent_browse_redirect'       => empty( $role_data['profilec_prevent_browse_redirect'] ) ? 0 : $role_data['profilec_prevent_browse_redirect'],
			'prevent_browse_redirect_url'   => empty( $role_data['profilec_prevent_browse_redirect_url'] ) ? '' : $role_data['profilec_prevent_browse_redirect_url'],
			'prevent_profileview'           => ( empty( $role_data['profilec_prevent_profileview'] ) ? 0 : 1 ),
			'prevent_comment'               => ( empty( $role_data['profilec_prevent_comment'] ) ? 0 : 1 ),
			'prevent_bb'                    => ( empty( $role_data['profilec_prevent_bb'] ) ? 0 : 1 ),
		);

		$result = apply_filters( 'um_profile_completeness_get_progress_result', $result, $role_data );

		update_user_meta( $user_id, '_profile_progress', $result );
		update_user_meta( $user_id, '_completed', $profile_progress );

		$profile_percentage = $role_data['profilec_pct'];

		if ( empty( $profile_percentage ) ) {
			$profile_percentage = 100;
		}

		if ( $profile_progress >= $profile_percentage && $role_data['profilec_upgrade_role'] ) {
			$new_role = $role_data['profilec_upgrade_role'];
			um_fetch_user( $user_id );

			$userdata = get_userdata( $user_id );
			$old_roles = $userdata->roles;
			UM()->roles()->set_role( $user_id, $new_role );

			foreach ( $old_roles as $_role ) {
				UM()->roles()->remove_role( $user_id, $_role );
			}

			do_action( 'um_after_member_role_upgrade', array( $new_role ), $old_roles, $user_id );
		}

		$result['raw'] = $result;
		return $result;
	}


	/**
	 *
	 */
	function widgets_init() {
		register_widget( 'um_profile_completeness' );
		register_widget( 'um_profile_progress_bar' );
	}


	/**
	 * @param string $key
	 *
	 * @return string
	 */
	function get_field_title( $key = '' ) {
		$fields_without_metakey = UM()->builtin()->get_fields_without_metakey();
		$fields_without_metakey = apply_filters( 'um_profile_completeness_fields_without_metakey', $fields_without_metakey );

		UM()->builtin()->fields_dropdown = array( 'image', 'file', 'password', 'rating' );
		UM()->builtin()->fields_dropdown = array_merge( UM()->builtin()->fields_dropdown, $fields_without_metakey );

		$custom = UM()->builtin()->custom_fields;
		$predefined = UM()->builtin()->predefined_fields;

		$all = array( 0 => '' );

		if ( is_array( $custom ) ) {
			$all = $all + array_merge( $predefined, $custom );
		} else {
			$all = $all + $predefined;
		}

		$fields = array( 0 => '' ) + $all;

		if ( ! empty( $fields[ $key ]['label'] ) ) {
			return sprintf( __( '%s', 'um-profile-completeness' ), $fields[ $key ]['label'] );
		}

		if ( ! empty( $fields[ $key ]['title'] ) ) {
			return sprintf( __( '%s', 'um-profile-completeness' ), $fields[ $key ]['title'] );
		}

		return __( 'Custom Field', 'um-profile-completeness' );
	}


	/**
	 * Save field over popup
	 */
	function ajax_save_popup() {
		UM()->check_ajax_nonce();

		if ( ! isset( $_POST['key'] ) || ! isset( $_POST['value'] ) || ! is_user_logged_in() ) {
			wp_send_json_error();
		}

		$user_id = get_current_user_id();
		$key = sanitize_key( $_POST['key'] );
		$value = sanitize_text_field( $_POST['value'] );

		if ( get_user_meta( $user_id, $key, true ) &&
			! in_array( $key, array( 'profile_photo', 'cover_photo', 'synced_profile_photo' ) ) ) {
			wp_send_json_error();
		}

		$field_type = UM()->fields()->get_field_type( $key );
		if ( in_array( $field_type, array( 'checkbox', 'radio', 'multiselect' ) ) && strstr( $value, ', ' ) ) {
			$value = explode( ', ', $value );
		}

		update_user_meta( $user_id, $key, $value );

		delete_option( "um_cache_userdata_{$user_id}" );

		$result = UM()->Profile_Completeness_API()->shortcode()->profile_progress( $user_id );
		$output['percent'] = $result['progress'];
		$output['raw'] = $result['raw'];
		$output['user_id'] = $user_id;
		$output['redirect'] = apply_filters( 'um_profile_completeness_complete_profile_redirect', '', $user_id, $result );

		wp_send_json_success( $output );
	}


	/**
	 * Edit field over popup
	 *
	 * @throws Exception
	 */
	function ajax_edit_popup() {
		UM()->check_ajax_nonce();

		if ( ! isset( $_POST['key'] ) || ! is_user_logged_in() ) {
			wp_send_json_error();
		}

		$key = sanitize_key( $_POST['key'] );

		um_fetch_user( get_current_user_id() );

		if ( get_user_meta( get_current_user_id(), $key, true ) ) {
			wp_send_json_error();
		}

		$result = UM()->Profile_Completeness_API()->shortcode()->profile_progress( get_current_user_id() );

		$data = UM()->builtin()->get_a_field( $key );

		UM()->fields()->disable_tooltips = true;

		$args['profile_completeness'] = true;

		$t_args = compact( 'args', 'data', 'result', 'key' );
		$output = UM()->get_template( 'completeness-popup.php', um_profile_completeness_plugin, $t_args );

		wp_send_json_success( $output );
	}


	/**
	 * Get widget data
	 */
	function ajax_get_widget() {
		UM()->check_ajax_nonce();

		if ( empty( $_POST['user_id'] ) ) {
			wp_send_json_error( __( 'Wrong User ID', 'um-profile-completeness' ) );
		}

		$user_id = absint( $_POST['user_id'] );

		$is_profile = ! empty( $_POST['is_profile'] );

		$result = $this->shortcode()->profile_progress( $user_id );

		if ( is_array( $result['steps'] ) ) {
			$result['steps'] = $this->shortcode()->reorder( $result['steps'] );
		}

		$result['isProfile'] = intval( $is_profile || um_is_core_page( 'user' ) );
		$result['profileEditURL'] = um_edit_profile_url();

		$bullet = 0;
		$result['fields'] = array();
		foreach ( $result['steps'] as $key => $pct ) {
			if ( $key == 'synced_profile_photo' || $key == 'synced_gravatar_hashed_id' ) {
				continue;
			}
			if ( in_array( $key, $result['completed'] ) ) {
				continue;
			}
			if ( apply_filters( 'um_profile_completeness_skip_field', false, $key, $result ) ) {
				continue;
			}
			if ( $key == 'profile_photo' && um_user( 'synced_gravatar_hashed_id' ) && UM()->options()->get( 'use_gravatars' ) ) {
				continue;
			}

			$result['fields'][ $key ] = array(
				'bullet'    => ++$bullet,
				'class'     => in_array( $key, $result['completed'] ) ? 'completed' : '',
				'label'     => UM()->Profile_Completeness_API()->get_field_title( $key ),
				'pct'       => $pct,
			);
		}

		$output = array_intersect_key( $result, array(
			'bar'               => '',
			'fields'            => '',
			'isProfile'         => '',
			'profileEditURL'    => '',
			'progress'          => '',
		) );

		wp_send_json_success( apply_filters( 'um_profile_completeness_ajax_get_widget', $output, $result, $user_id ) );
	}
}

//create class var
add_action( 'plugins_loaded', 'um_init_profile_completeness', -10, 1 );
function um_init_profile_completeness() {
	if ( function_exists( 'UM' ) ) {
		UM()->set_class( 'Profile_Completeness_API', true );
	}
}