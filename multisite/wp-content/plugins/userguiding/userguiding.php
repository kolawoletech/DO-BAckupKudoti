<?php
/*
Plugin Name: UserGuiding
Description: Create quick, hassle free, and interactive guides for an easier product journey.
Version: 1.0.1
Author: UserGuiding
Author URI: https://userguiding.com
Developer: UserGuiding
Developer URI: https://userguiding.com
Text Domain: userguiding
*/

defined( 'ABSPATH' ) or die( 'No script kiddies please!' );

class UserGuiding
{

	public function __construct()
    {
        $this->constants();

        $this->init();
	}

    public function init()
    {
        global $wp_customize;

        if (get_option('userguiding_admin')) {
            add_action('admin_enqueue_scripts', array($this, 'addScript'));
        }
        
        if (get_option('userguiding_site')) {
            add_action('wp_enqueue_scripts', array($this, 'addScript'));
        }
        
        if (get_option('userguiding_customizer')) {
            add_action('customize_controls_enqueue_scripts', array($this, 'addScript'));
        }
        
        if (!is_admin()) {
            // to do
        } elseif (!isset($wp_customize)) {
            add_action('admin_menu', array($this, 'menu'));
            add_action('admin_init', array($this, 'register_options'));
            add_action('plugin_action_links_' . USERGUIDING_NAME, array($this, 'action_links'));
        }
    }
    
    public function addScript()
    {
        wp_enqueue_script('userguiding', USERGUIDING_URL.'assets/js/userguiding.js', array(), '1.0');
        wp_add_inline_script('userguiding', get_option('userguiding_code'));
    }

    public function constants()
    {
        if (!defined('USERGUIDING_NAME')) {
            define('USERGUIDING_NAME', plugin_basename(__FILE__));
        }

        if (!defined('USERGUIDING_PATH')) {
            define('USERGUIDING_PATH', plugin_dir_path(__FILE__));
        }

        if (!defined('USERGUIDING_ADMIN_URL')) {
            define('USERGUIDING_ADMIN_URL', admin_url());
        }

        if (!defined('USERGUIDING_URL')) {
            $current_directory_name = basename(dirname(__FILE__));
            define('USERGUIDING_URL', plugins_url($current_directory_name) . '/');
        }
    }

    public function menu()
    {
        add_options_page('UserGuiding Settings', 'UserGuiding', 'manage_options', 'userguiding', array($this, 'display_options'));
    }

    public function action_links($links)
    {
        array_unshift($links, '<a href="'. esc_url(USERGUIDING_ADMIN_URL . 'options-general.php?page=userguiding') .'">Settings</a>');

        return $links;
    }

    public function display_options()
    {
        if (!current_user_can('manage_options'))  {
            wp_die(__('You do not have sufficient permissions to access this page.'));
        }

        // check if the user have submitted the settings
        // wordpress will add the "settings-updated" $_GET parameter to the url
        if (isset($_GET['settings-updated'])) {
            settings_errors('userguiding-options');
        }

        require_once(USERGUIDING_PATH . 'options.php');
    }

    public function register_options()
    {
        register_setting('userguiding-options', 'userguiding_code');
        register_setting('userguiding-options', 'userguiding_admin');
        register_setting('userguiding-options', 'userguiding_site');
        register_setting('userguiding-options', 'userguiding_customizer');
    }
}

function UserGuidingInit() {
    new UserGuiding();
}
add_action('init', 'UserGuidingInit');