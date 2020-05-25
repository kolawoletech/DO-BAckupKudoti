<?php
/**
 * Template for the UM Groups. The group "Edit" tab, "Delete" subtab content
 *
 * Page: "Group", tab "Edit"
 * Caller: function um_groups_single_page_sub_content__settings_delete()
 *
 * This template can be overridden by copying it to yourtheme/ultimate-member/um-groups/tabs/delete.php
 */
if( !defined( 'ABSPATH' ) ) {
	exit;
}
?>

<form action="" method="post">

	<?php do_action( 'um_group_delete_form_header' ); ?>

	<p><strong><?php _e( "This action cannot be undone.", "um-groups" ); ?></strong></p>
	
	<div class="um-col-alt">
		<input type="submit" name="um_groups_delete_group" value="<?php _e( "Delete group permanently", "um-groups" ); ?>" class="um-button" />
	</div>

	<?php wp_nonce_field( 'um-groups-nonce_delete_group_' . get_current_user_id() ); ?>

	<?php do_action( 'um_group_delete_form_footer' ); ?>

</form>