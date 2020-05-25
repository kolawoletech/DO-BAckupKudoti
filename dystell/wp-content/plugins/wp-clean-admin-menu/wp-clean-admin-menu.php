<?php
/**
* Plugin Name: WP Clean Admin Menu
* Plugin URI:  http://wordpress.org/plugins/wp-clean-admin-menu
* Description: Simplify WordPress admin-menu by hiding the rarely used admin-menu items/links.
* Tags: wp clean admin menu, wordpress clean admin menu, wp hide admin menu, clean admin menu
* Author: P. Roy
* Author URI: https://www.proy.info
* Version: 1.0.1
* License: GPLv2 or later
* License URI: https://www.gnu.org/licenses/gpl-2.0.html
* Text Domain: wp-clean-admin-menu
**/

// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {   die; }

class WP_Clean_Admin_Menu {

    //protected $loader;
    protected $plugin_name;
    protected $version;

    public $toggleItemSlug = 'toggle_wpcleanadminmenu';
    public $toggleItemOrder = '98.1';
    public $hiddenItemsOptionName = 'toggle_wpcleanadminmenu_items';
    public $nonceName = 'toggle_wpcleanadminmenu_options';

    public function __construct() {

        $this->plugin_name = 'wp-clean-admin-menu';
        $this->version = '1.0.0';

        add_action( 'admin_init', array( $this, 'admin_init' ) );

        //add_action( 'admin_enqueue_scripts', array( $this, 'enqueue_styles' ) );
        //add_action( 'admin_enqueue_scripts', array( $this, 'enqueue_scripts' ) );

        //action to add menu pages on admin
        add_action( 'admin_menu', array( $this, 'addMenuPages' ) );

        //action for adding classes to admin menu items
        add_action( 'admin_menu', array( $this, 'adminMenuAction' ), 1000000 );


    }

    public function admin_init() {
        if ( is_admin() ) {

            ob_start();// this is require to resolve redirect issue

            add_action( 'admin_head', array( $this, 'toggle_menu_items' ) );
        }

    }

    public function toggle_menu_items() {
        ?>
        <script>
        (function($) {
            var menusAreHidden = true;
            $(function() {
                /**
                 * When the toggle extra item clicked show/hide menu items
                 * Also trigger the wp-window-resized event for left menu
                 */
                $('#toplevel_page_toggle_wpcleanadminmenu a').click(function(e){
                    e.preventDefault();
                    $('.menu-top.clean-wp-admin-menu__valid-item').toggleClass('hidden');
                    $(document).trigger('wp-window-resized');
                });

                /**
                 * Little hack for some of the submenus declared after the admin_menu hook
                 * If it should be open but hidden, remove the hidden class
                 */
                $('#adminmenu .wp-menu-open.hidden').removeClass('hidden');
            });
        })(jQuery);
        </script>
        <?php
    }

    /**
     * Add menu pages in admin
     */
    public function addMenuPages()  {

        add_menu_page(
            __('Toggle Menu', $this->plugin_name),
            __('Toggle Menu', $this->plugin_name),
            'manage_options',
            $this->toggleItemSlug,
            function () {  return false;  },
            "dashicons-hidden",
            $this->toggleItemOrder
        );

        add_options_page(
            __('WP Clean Admin Menu', $this->plugin_name),
            __('WP Clean Admin Menu', $this->plugin_name),
            'manage_options',
            $this->plugin_name . '_options',
            array(
                $this,
                'settingsPage'
            )
        );



        add_filter('plugin_action_links_' . plugin_basename(__FILE__), array(&$this, 'plugin_settings_link'), 10, 2 );

    }


    public function plugin_settings_link($links, $file) {
        $settings_link = '<a href="options-general.php?page=wp-clean-admin-menu_options">' . __('Settings', $this->plugin_name) . '</a>';
        array_unshift($links, $settings_link); // before other links
        return $links;
    }

    /**
     * Add necessary items
     */
    public function adminMenuAction()  {

        global $_registered_pages, $_parent_pages, $menu, $admin_page_hooks, $submenu;
        global $self, $parent_file, $submenu_file, $plugin_page, $typenow, $_wp_real_parent_file;

        //list of items selected from settings page
        $selectedItems = $this->selectedItems();
        $menuItems     = wp_list_pluck($menu, 2);


        foreach ($menu as $k => $item) {
            // Reminder for parent menu items array
            // 0 = menu_title, 1 = capability, 2 = menu_slug, 3 = page_title, 4 = classes, 5 = hookname, 6 = icon_url

            $isSelected      = in_array($item[2], $selectedItems);
            $isCurrentItem   = false;
            $isCurrentParent = false;


            //check if item is parent of current item
            //if not both of them, it deserves to be hidden if it is selected
            if ($parent_file) {
                $isCurrentItem = ($item[2] == $parent_file);

                if (isset($_parent_pages[$parent_file])) {
                    $isCurrentParent = ($_parent_pages[$parent_file] === $item[2]);
                }
            }

            $isHidden = ($isSelected && false === ($isCurrentParent OR $isCurrentItem));

            if ($isHidden) {
                $menu[$k][4] = $item[4] . ' hidden clean-wp-admin-menu__valid-item';
            }
        }
    }

    public function settingsPage() {
        global $_registered_pages, $_parent_pages, $menu, $admin_page_hooks, $submenu;

        $this->saveSettings();
        $pluginName = $this->plugin_name;
        $selectedItems = $this->selectedItems();

        ?>
        <style>
            .wrap td, .wrap th { text-align: left; }
            .table-menulist{ background-color: #fff; padding: 10px; margin-bottom: 20px; }
            .table-menulist th { padding: 5px; border-bottom: 1px solid #DFDFDF; }
            .table-menulist td  { padding: 5px; border-bottom: 1px solid #DFDFDF; }
            .table-menulist tr:last-child td  { border-bottom: 0;}
        </style>
        <div class="wrap">
            <h1><?php esc_html_e('WP Clean Admin Menu', $pluginName); ?></h1>
            <?php
            echo (($_GET['saved']==1)?'<div class="updated"><p>' . __('Success! Admin menu cleaned successfully', $this->plugin_name) . '</p></div>':'');
            ?>
            <p>
                This plugin helps to simplify WordPress admin-menu by hiding the rarely used admin-menu items/links.<br/>
                <h3>Selected menu items will be HIDDEN by default. Use The toggle Menu item to show/hide items.</h3>
            </p>
            <form action="<?php echo esc_attr(admin_url('options-general.php?page=wp-clean-admin-menu_options')); ?>" method="post">
                <?php wp_nonce_field($this->nonceName, $this->nonceName, true, true); ?>
                <table class="table-menulist">
                    <tr>
                        <th></th>
                        <th></th>
                        <th style="width:300px;">Menu Items</th>
                    </tr>
                    <?php

                    $separator = 0;
                    foreach ($menu as $key => $menuItem){
                        $isSeparator = strpos($menuItem[4], 'wp-menu-separator');
                        $isSelected  = in_array($menuItem[2], $selectedItems);

                        //if ($isSeparator !== false OR $menuItem[2] === 'toggle_wpcleanadminmenu') {
                        if ($isSeparator !== false) {
                            $menuItem[0] = '――――――separator――――――';
                            $separator++;
                        }

                        // Hiding the Separator before the "toggle menu" link
                        if($separator > 1) { $separator = 0; continue; }

                        ?>
                        <tr>
                            <td>
                                <input type="checkbox" name="toggle_wpcleanadminmenu_items[]" value="<?php echo $menuItem[2]; ?>"
                                   id="toggle_wpcleanadminmenu_item_<?php echo $key; ?>"
                                <?php echo ($isSelected) ? 'checked' : ''; ?>
                                <?php //echo ($menuItem[2] === 'index.php') ? 'disabled' : ''; ?> />
                            </td>
                            <td>
                                <?php if ($isSelected){ ?>
                                    <span style="color:#CA4A1F;" class="dashicons-before dashicons-hidden"></span>
                                <?php }else{?>
                                    <span style="color:#DFDFDF;" class="dashicons-before dashicons-visibility"></span>
                                <?php } ?>
                            </td>
                            <td>
                                <label for="toggle_wpcleanadminmenu_item_<?php echo $key; ?>">
                                    <strong <?php echo ($isSeparator !== false?'style="color:#B7B7B7;"':'')?>>
                                        <?php
                                        if ($menuItem[2] === 'toggle_wpcleanadminmenu')
                                            echo '―― '.strtoupper($menuItem[0]).' ――<br><sub style="color:#616A74;">Used to toggle menu items</sub>';
                                        else
                                            echo $menuItem[0];
                                        ?>
                                    </strong>
                                </label>
                            </td>
                        </tr>
                    <?php } ?>
                </table>
                <input type="submit" class="button-primary" value="<?php esc_html_e('SAVE CHANGES', $pluginName); ?>"/>
            </form>
            <hr>
            <?php echo esc_html_e('This Plugin Developed by ',$pluginName);?><a href="https://www.proy.info" target="_blank">P. Roy</a>
        </div>
        <?php
    }

    public function selectedItems() {
        $items = get_option($this->hiddenItemsOptionName);
        if (!$items) {
            $items = array();
            return $items;
        }
        return $items;
    }

    private function saveSettings() {
        global $menu;

        if (!isset($_POST[$this->nonceName])) {
            return false;
        }

        $verify = check_admin_referer($this->nonceName, $this->nonceName);

        //TODO if empty but has post delete items

        if (!isset($_POST['toggle_wpcleanadminmenu_items'])) {
            $itemsToSave = array();
            $savedSuccess = 0;
        } else {

            $menuItems = wp_list_pluck($menu, 2);

            $items = $_POST['toggle_wpcleanadminmenu_items'];

            //save them after a check if they really exists on menu
            $itemsToSave = array();

            if ($items) {
                foreach ($items as $item) {
                    if (in_array($item, $menuItems)) {
                        $itemsToSave[] = $item;
                    }
                }
            }
            $savedSuccess = 1;
        }

        //update the option and set as autoloading option
        update_option($this->hiddenItemsOptionName, $itemsToSave, true);

        // we'll redirect to same page when saved to see results.
        // redirection will be done with js, due to headers error when done with wp_redirect
        $adminPageUrl = admin_url('options-general.php?page=wp-clean-admin-menu_options&saved='.$savedSuccess);
        wp_safe_redirect( $adminPageUrl ); exit;
    }
}

new WP_Clean_Admin_Menu();
