<?php
/*
Plugin Name: Ultimate Member - Groups
Plugin URI: http://ultimatemember.com/extension/groups
Description: Add a group system for your community users easily.
Version: 2.2.0
Author: Ultimate Member
Author URI: http://ultimatemember.com/
Text Domain: um-groups
Domain Path: /languages
UM version: 2.1.0
*/

if ( ! defined( 'ABSPATH' ) ) exit;

require_once( ABSPATH . 'wp-admin/includes/plugin.php' );

$plugin_data = get_plugin_data( __FILE__ );

define( 'um_groups_url', plugin_dir_url( __FILE__ ) );
define( 'um_groups_path', plugin_dir_path( __FILE__ ) );
define( 'um_groups_plugin', plugin_basename( __FILE__ ) );
define( 'um_groups_extension', $plugin_data['Name'] );
define( 'um_groups_version', $plugin_data['Version'] );
define( 'um_groups_textdomain', 'um-groups' );

define( 'um_groups_requires', '2.1.0' );

function um_groups_plugins_loaded() {
	$locale = ( get_locale() != '' ) ? get_locale() : 'en_US';
	load_textdomain( um_groups_textdomain, WP_LANG_DIR . '/plugins/um-groups-' . $locale . '.mo' );
	load_plugin_textdomain( um_groups_textdomain, false, dirname( plugin_basename( __FILE__ ) ) . '/languages/' );
}
add_action( 'plugins_loaded', 'um_groups_plugins_loaded', -20 );

add_action( 'plugins_loaded', 'um_groups_check_dependencies', -20 );

if ( ! function_exists( 'um_groups_check_dependencies' ) ) {
	function um_groups_check_dependencies() {
		if ( ! defined( 'um_path' ) || ! file_exists( um_path  . 'includes/class-dependencies.php' ) ) {
			//UM is not installed
			function um_groups_dependencies() {
				echo '<div class="error"><p>' . sprintf( __( 'The <strong>%s</strong> extension requires the Ultimate Member plugin to be activated to work properly. You can download it <a href="https://wordpress.org/plugins/ultimate-member">here</a>', 'um-groups' ), um_groups_extension ) . '</p></div>';
			}

			add_action( 'admin_notices', 'um_groups_dependencies' );
		} else {

			if ( ! function_exists( 'UM' ) ) {
				require_once um_path . 'includes/class-dependencies.php';
				$is_um_active = um\is_um_active();
			} else {
				$is_um_active = UM()->dependencies()->ultimatemember_active_check();
			}

			if ( ! $is_um_active ) {
				//UM is not active
				function um_groups_dependencies() {
					echo '<div class="error"><p>' . sprintf( __( 'The <strong>%s</strong> extension requires the Ultimate Member plugin to be activated to work properly. You can download it <a href="https://wordpress.org/plugins/ultimate-member">here</a>', 'um-groups' ), um_groups_extension ) . '</p></div>';
				}

				add_action( 'admin_notices', 'um_groups_dependencies' );

			} elseif ( true !== UM()->dependencies()->compare_versions( um_groups_requires, um_groups_version, 'groups', um_groups_extension ) ) {
				//UM old version is active
				function um_groups_dependencies() {
					echo '<div class="error"><p>' . UM()->dependencies()->compare_versions( um_groups_requires, um_groups_version, 'groups', um_groups_extension ) . '</p></div>';
				}

				add_action( 'admin_notices', 'um_groups_dependencies' );

			} else {
				require_once um_groups_path . 'includes/core/um-groups-init.php';
			}
		}
	}
}

if ( ! function_exists( 'um_groups_activation_hook' ) ) {
	function um_groups_activation_hook() {
		//run setup
		if ( ! class_exists( 'um_ext\um_groups\core\Groups_Setup' ) ) {
			require_once um_groups_path . 'includes/core/class-groups-setup.php';
		}

		$groups_setup = new um_ext\um_groups\core\Groups_Setup();
		$groups_setup->run_setup();

		//first install
		$version = get_option( 'um_groups_version' );
		if ( ! $version ) {
			update_option( 'um_groups_last_version_upgrade', um_groups_version );
		}

		if ( $version != um_groups_version ) {
			update_option( 'um_groups_version', um_groups_version );
		}
	}
}
register_activation_hook( um_groups_plugin, 'um_groups_activation_hook' );