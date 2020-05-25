<p><?php _e('Thank you for installing our plugin.', $this->textname); ?></p>

<?php
VG_Admin_To_Frontend_Obj()->set_main_admin_id(null, true);

$steps = array();

if (!class_exists('ReduxFramework')) {
	$install_plugin_url = VG_Admin_To_Frontend_Obj()->get_plugin_install_url('redux-framework');

	$steps['required_plugin_reduxframework'] = '<p>' . sprintf(__('Please install the free plugin <b>"ReduxFramework"</b>. It´s required for the settings page. <a href="%s" target="_blank" class="button install-plugin-trigger">Click here</a>', $this->textname), esc_url($install_plugin_url)) . '</p>';
}

$admin_url = admin_url();
$home_url = home_url();
if (strpos($admin_url, 'https://') !== false && strpos($home_url, 'https://') === false) {
	$steps['http_protocol_mismatch'] = '<p>' . sprintf(__('IMPORTANT. You are using https for wp-admin and http for the public website. Both need to use the same protocol (https) for security reasons. Please change the public URL to use https. <a href="%s" target="_blank" class="button">Fix it</a>', $this->textname), esc_url(admin_url('options-general.php'))) . '</p>';
}

$steps['open_settings_page'] = '<p>' . sprintf(__('Check the plugin settings. <a href="%s" target="_blank" class="button">Open settings page</a>', $this->textname), esc_url(admin_url('admin.php?page=' . VG_Admin_To_Frontend::$textname))) . '</p>';


$steps['create_page'] = '<p>' . sprintf(__('Go to the admin page that you want to display on the frontend, and click the "View on the frontend" option in the top toolbar: <img src="%s"/>', $this->textname), esc_url(plugins_url('/assets/imgs/toolbar-item-screenshot.png', dirname(__FILE__)))) . '</p>';

if (empty(VG_Admin_To_Frontend_Obj()->allowed_urls)) {
	$allowed_urls_message = '<p>' . __('You can view any admin URL in the frontend. For example, the settings page, the widgets page, the WooCommerce settings page, the WooCommerce Sales Stats page, etc.', $this->textname) . '</p>';
} else {
	$allowed_urls_message = '<p>' . sprintf(__('You are using the Free plugin. You can view these pages in the frontend: blog posts, post editor, blog categories, and blog tags.', $this->textname)) . '</p>';

	$allowed_urls_message .= sprintf(__('<h3>Go Premium</h3><p>View ANY admin page in the frontend<br/>View settings pages from the frontend<br/>View WooCommerce settings on the frontend<br/>View WooCommerce stats from the frontend<br/>Install plugins from the frontend<br/>View plugin settings from the frontend<br/>View any page from wp-admin on the frontend<br/>And more.</p><a href="%s" class="button button-primary">%s</a> - <a href="#tutorial" class="button">Watch a demo video</a> - <a href="%s" class="button" target="_blank">Need help? Contact us</a></p><p>Try the plugin without worries.</p><p>Check this video of the premium features.</p><iframe id="tutorial" width="560" height="315" src="https://www.youtube.com/embed/EG1NE3X5yNs?rel=0&amp;controls=0&amp;showinfo=0" frameborder="0" allow="autoplay; encrypted-media" allowfullscreen></iframe>.', $this->textname), VG_Admin_To_Frontend_Obj()->args['buy_link'], VG_Admin_To_Frontend_Obj()->args['buy_link_text'], 'https://wpfrontendadmin.com/contact/?utm_source=wp-admin&utm_campaign=welcome-page-help&utm_medium=' . (empty(VG_Admin_To_Frontend_Obj()->allowed_urls) ? 'free' : 'pro') . '-plugin');
}
$steps['allowed_urls'] = $allowed_urls_message;

$steps['note'] = '<p>' . sprintf(__('You can read more about advanced settings and configuration in <a href="%s" target="_blank">our website</a>.', $this->textname), 'https://wpfrontendadmin.com/advanced-settings/?utm_source=wp-admin&utm_campaign=welcome-page-advanced-settings&utm_medium=' . (empty(VG_Admin_To_Frontend_Obj()->allowed_urls) ? 'free' : 'pro') . '-plugin') . '</p>';


$steps = apply_filters('vg_admin_to_frontend/welcome_steps', $steps);

if (!empty($steps)) {
	echo '<ol class="steps">';
	foreach ($steps as $key => $step_content) {
		?>
		<li><?php echo $step_content; ?></li>		
		<?php
	}

	echo '</ol>';
}

_e('<h3>Tutorials</h3>', $this->textname);
_e('<ul role="menu" class=" dropdown-menu">
	<li >Allow Post Submissions from the Frontend <a target="_blank" href="https://wpfrontendadmin.com/allow-post-submissions-from-the-frontend/?utm_source=wp-admin&utm_campaign=tutorials-list&utm_medium=welcome-page">View tutorial</a></li>
	<li >Change Permalink Settings from the Frontend in WordPress <a target="_blank" href="https://wpfrontendadmin.com/change-permalink-settings-from-the-frontend-in-wordpress/?utm_source=wp-admin&utm_campaign=tutorials-list&utm_medium=welcome-page">View tutorial</a></li>
	<li >Change Site Settings from the Frontend in WordPress <a target="_blank" href="https://wpfrontendadmin.com/change-site-settings-from-the-frontend-in-wordpress/?utm_source=wp-admin&utm_campaign=tutorials-list&utm_medium=welcome-page">View tutorial</a></li>
	<li >Create and Manage Users from the Frontend <a target="_blank" href="https://wpfrontendadmin.com/create-and-manage-users-from-the-frontend/?utm_source=wp-admin&utm_campaign=tutorials-list&utm_medium=welcome-page">View tutorial</a></li>
	<li >Create WooCommerce Coupons from the Frontend <a target="_blank" href="https://wpfrontendadmin.com/create-woocommerce-coupons-from-the-frontend/?utm_source=wp-admin&utm_campaign=tutorials-list&utm_medium=welcome-page">View tutorial</a></li>
	<li >Create WooCommerce Products from the Frontend <a target="_blank" href="https://wpfrontendadmin.com/create-woocommerce-products-from-the-frontend/?utm_source=wp-admin&utm_campaign=tutorials-list&utm_medium=welcome-page">View tutorial</a></li>
	<li >Install Themes from the Frontend in WordPress <a target="_blank" href="https://wpfrontendadmin.com/install-themes-from-the-frontend-in-wordpress/?utm_source=wp-admin&utm_campaign=tutorials-list&utm_medium=welcome-page">View tutorial</a></li>
	<li >Install Updates from the Frontend in WordPress <a target="_blank" href="https://wpfrontendadmin.com/install-updates-from-the-frontend-in-wordpress/?utm_source=wp-admin&utm_campaign=tutorials-list&utm_medium=welcome-page">View tutorial</a></li>
	<li >Install WordPress Plugins from the Frontend <a target="_blank" href="https://wpfrontendadmin.com/install-wordpress-plugins-from-the-frontend/?utm_source=wp-admin&utm_campaign=tutorials-list&utm_medium=welcome-page">View tutorial</a></li>
	<li >Manage Nav Menus from the Frontend <a target="_blank" href="https://wpfrontendadmin.com/manage-nav-menus-from-the-frontend/?utm_source=wp-admin&utm_campaign=tutorials-list&utm_medium=welcome-page">View tutorial</a></li>
	<li >Manage User Comments from the Frontend in WordPress <a target="_blank" href="https://wpfrontendadmin.com/manage-user-comments-from-the-frontend-in-wordpress/?utm_source=wp-admin&utm_campaign=tutorials-list&utm_medium=welcome-page">View tutorial</a></li>
	<li >Manage Widgets from the Frontend in WordPress <a target="_blank" href="https://wpfrontendadmin.com/manage-widgets-from-the-frontend-in-wordpress/?utm_source=wp-admin&utm_campaign=tutorials-list&utm_medium=welcome-page">View tutorial</a></li>
	<li >Manage WooCommerce Settings from the Frontend <a target="_blank" href="https://wpfrontendadmin.com/manage-woocommerce-settings-from-the-frontend/?utm_source=wp-admin&utm_campaign=tutorials-list&utm_medium=welcome-page">View tutorial</a></li>
	<li >Setup a Theme from the Frontend in WordPress <a target="_blank" href="https://wpfrontendadmin.com/setup-a-theme-from-the-frontend-in-wordpress/?utm_source=wp-admin&utm_campaign=tutorials-list&utm_medium=welcome-page">View tutorial</a></li>
	<li >View and Dispatch WooCommerce Orders from the Frontend <a target="_blank" href="https://wpfrontendadmin.com/view-and-dispatch-woocommerce-orders-from-the-frontend/?utm_source=wp-admin&utm_campaign=tutorials-list&utm_medium=welcome-page">View tutorial</a></li>
	<li >View WooCommerce Sales Reports from the Frontend <a target="_blank" href="https://wpfrontendadmin.com/view-woocommerce-sales-reports-from-the-frontend/?utm_source=wp-admin&utm_campaign=tutorials-list&utm_medium=welcome-page">View tutorial</a></li>
</ul>', $this->textname);
?>
<script>
	jQuery('.vg-logo').parent().attr('href', 'https://wpfrontendadmin.com/?utm_source=wp-admin&utm_campaign=logo&utm_medium=welcome-page')
	jQuery('.install-plugin-trigger').click(function (e) {
		return !window.open(this.href, 'Install plugin', 'width=500,height=500');
	});
</script>