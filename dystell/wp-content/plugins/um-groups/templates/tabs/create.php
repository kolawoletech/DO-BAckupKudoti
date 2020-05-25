<?php
/**
 * Template for the UM Groups, The group "Create" and "Edit" tab content
 *
 * Page: "Group", tab "Edit"
 * Page: "Groups", tab "Create a Group"
 * Caller: function um_groups_single_page_sub_content__settings_details()
 * Caller: method Groups_Shortcode->create()
 *
 * This template can be overridden by copying it to yourtheme/ultimate-member/um-groups/tabs/create.php
 */
if( !defined( 'ABSPATH' ) ) {
	exit;
}
?>

<div class="um um-groups-create">

	<?php do_action( 'um_groups_create_form', $args ); ?>

</div>
