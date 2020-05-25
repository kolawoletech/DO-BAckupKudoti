<?php
/*
  Plugin Name: WP Clean Admin Theme - Simple admin design
  Description: Make wp-admin look cleaner.
  Version: 1.0.2
  Author: WP Frontend Admin
  Author URI: https://wpfrontendadmin.com/?utm_source=wp-admin&utm_medium=plugins-list&utm_campaign=clean-admin-theme
  Plugin URI: https://wpfrontendadmin.com/?utm_source=wp-admin&utm_medium=plugins-list&utm_campaign=clean-admin-theme
  License:

  Copyright 2011 JoseVega (josevega@vegacorp.me)

  This program is free software; you can redistribute it and/or modify
  it under the terms of the GNU General Public License, version 2, as
  published by the Free Software Foundation.

  This program is distributed in the hope that it will be useful,
  but WITHOUT ANY WARRANTY; without even the implied warranty of
  MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
  GNU General Public License for more details.

  You should have received a copy of the GNU General Public License
  along with this program; if not, write to the Free Software
  Foundation, Inc., 51 Franklin St, Fifth Floor, Boston, MA  02110-1301  USA

 */


add_action('admin_enqueue_scripts', 'wpat_enqueue_assets', 10);
if (!function_exists('wpat_enqueue_assets')) {

	function wpat_enqueue_assets() {

		// Register stylesheets
		wp_enqueue_style('wp-color-picker');
		wp_enqueue_script('wp-color-picker');
		wp_enqueue_style('vg-styles', plugins_url('/styles.css', __FILE__), array(), '1.0', 'all');
	}

}

add_action('admin_head', 'wpat_dynamic_css', 999);

if (!function_exists('wpat_dynamic_css')) {

	function wpat_dynamic_css() {

		$option_key = 'wpat_main_color';
		$main_color = get_option($option_key, '#1a74bb');

		if (defined('WPCAT_MAIN_COLOR') && WPCAT_MAIN_COLOR) {
			$main_color = WPCAT_MAIN_COLOR;
		}
		if (empty($main_color)) {
			return;
		}
		?><style>
			#adminmenu li.current a.menu-top, #adminmenu li.wp-has-current-submenu a.wp-has-current-submenu, #adminmenu li.wp-has-current-submenu .wp-submenu .wp-submenu-head, .folded #adminmenu li.current.menu-top, #adminmenu .wp-has-current-submenu .wp-submenu .wp-submenu-head, #adminmenu .wp-menu-arrow, #adminmenu .wp-menu-arrow div, #adminmenu li.current a.menu-top, #adminmenu li.wp-has-current-submenu a.wp-has-current-submenu, .folded #adminmenu li.current.menu-top, .folded #adminmenu li.wp-has-current-submenu, #adminmenu a:hover, #adminmenu li.menu-top:hover, #adminmenu li.opensub > a.menu-top, #adminmenu li > a.menu-top:focus, .woocommerce-message .button-primary, p.woocommerce-actions .button-primary, .jp-connect-full__button-container .dops-button.is-primary, .dops-button.is-primary,
			.woocommerce-BlankState a.button-primary, .woocommerce-BlankState button.button-primary, .woocommerce-message a.button-primary, .woocommerce-message button.button-primary, .wp-core-ui .button-primary,
			.components-button.is-primary,
			.wp-core-ui .button-primary.active,
			.wp-core-ui .button-primary:focus,
			.wp-core-ui .button-primary:hover {
				background: <?php echo sanitize_text_field($main_color); ?>;
				box-shadow: none;
				text-shadow: none;
			}

			#adminmenu div.wp-menu-image:before {
				color: <?php echo sanitize_text_field($main_color); ?>;
				font-weight: 500;
			}

			.jp-connect-full__button-container .dops-button.is-primary,
			.woocommerce-BlankState a.button-primary, .woocommerce-BlankState button.button-primary, .woocommerce-message a.button-primary, .woocommerce-message button.button-primary,
			.components-button.is-primary,
			.dops-button.is-primary,
			.wp-core-ui .button-primary,
			.wp-core-ui .button-primary.active,
			.wp-core-ui .button-primary:focus,
			.wp-core-ui .button-primary:hover {
				font-weight: 500;
				color: white;
				border: 1px solid <?php echo sanitize_text_field($main_color); ?>;
			}
			div.woocommerce-message {
				border-left-color: <?php echo sanitize_text_field($main_color); ?> !important;
			}
		</style><?php
	}

}

/**
 * Class for adding a new field to the options-general.php page
 */
if (!class_exists('wpat_Add_Settings_Field')) {

	class wpat_Add_Settings_Field {

		var $option_key = 'wpat_main_color';

		/**
		 * Class constructor
		 */
		public function __construct() {
			add_action('admin_init', array($this, 'register_fields'));
		}

		/**
		 * Add new fields to wp-admin/options-general.php page
		 */
		public function register_fields() {
			register_setting('general', $this->option_key, 'esc_attr');
			add_settings_field(
					$this->option_key . '_id', '<label for="' . $this->option_key . '_id">' . __('Admin theme: Main color', $this->option_key) . '</label>', array($this, 'fields_html'), 'general'
			);
		}

		/**
		 * HTML for extra settings
		 */
		public function fields_html() {
			$value = get_option($this->option_key, '');
			echo '<input type="text" id="' . esc_attr($this->option_key) . '_id" name="' . esc_attr($this->option_key) . '" value="' . esc_attr($value) . '" />';
			?>

			<script>
				jQuery(document).ready(function () {
					jQuery('#<?php echo esc_attr($this->option_key); ?>_id').wpColorPicker();
				});
			</script>
			<?php
		}

	}

}

// If constant is defined in wp-config, dont load the settings option
if (!defined('WPCAT_MAIN_COLOR')) {
	new wpat_Add_Settings_Field();
}
