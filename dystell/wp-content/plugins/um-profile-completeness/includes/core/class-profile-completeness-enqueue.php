<?php
namespace um_ext\um_profile_completeness\core;


if ( ! defined( 'ABSPATH' ) ) exit;


/**
 * Class Profile_Completeness_Enqueue
 * @package um_ext\um_profile_completeness\core
 */
class Profile_Completeness_Enqueue {


	/**
	 * Profile_Completeness_Enqueue constructor.
	 */
	function __construct() {
		add_action( 'wp_enqueue_scripts',  array( &$this, 'wp_enqueue_scripts' ), 9999 );
	}


	/**
	 *
	 */
	function wp_enqueue_scripts() {
		$suffix = ( defined( 'SCRIPT_DEBUG' ) && SCRIPT_DEBUG || defined( 'UM_SCRIPT_DEBUG' ) ) ? '' : '.min';

		wp_register_script( 'um_profile_completeness', um_profile_completeness_url . 'assets/js/um-profile-completeness' . $suffix . '.js', array( 'jquery', 'wp-util', 'select2', 'um_scripts' ), um_profile_completeness_version, true );
		wp_register_style( 'um_profile_completeness', um_profile_completeness_url . 'assets/css/um-profile-completeness' . $suffix . '.css', array(), um_profile_completeness_version );
	}
}