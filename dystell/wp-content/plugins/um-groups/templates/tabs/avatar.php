<?php
/**
 * Template for the UM Groups. The group "Edit" tab, "Avatat" subtab content
 *
 * Page: "Group", tab "Edit"
 * Caller: function um_groups_single_page_sub_content__settings_avatar()
 *
 * This template can be overridden by copying it to yourtheme/ultimate-member/um-groups/tabs/avatar.php
 */
if( !defined( 'ABSPATH' ) ) {
	exit;
}
?>

<form action="" method="post"  enctype="multipart/form-data">

	<?php do_action( 'um_groups_upload_form_header' ); ?>

	<div class="um-group-avatar-image">
		<?php echo UM()->Groups()->api()->get_group_image( $group_id, 'default', 100, 100 ); ?>
		<input type="file" name="um_groups_avatar" />
	</div>

	<?php
	if( UM()->form()->has_error( 'um_groups_avatar' ) ) {
		UM()->Groups()->form_process()->show_error( UM()->form()->errors[ 'um_groups_avatar' ] );
	}
	?>

	<div class="um-col-alt">
		<?php if( has_post_thumbnail( $group_id ) ) { ?>
			<div class="um-left um-half">
				<input type="submit" name="um_groups_upload_avatar" value="<?php esc_attr_e( 'Upload', 'um-groups' ) ?>" class="um-button" />
			</div>
			<div class="um-right um-half">
				<input type="submit" name="um_groups_delete_avatar" value="<?php esc_attr_e( 'Delete', 'um-groups' ) ?>" class="um-button um-alt" />
			</div>
		<?php } else { ?>
			<input type="submit" name="um_groups_upload_avatar" value="<?php esc_attr_e( 'Upload', 'um-groups' ) ?>" class="um-button" />
		<?php } ?>

		<div class="um-clear"></div>
	</div>

	<?php wp_nonce_field( 'um-groups-nonce_upload_' . get_current_user_id() ); ?>

	<?php do_action( 'um_groups_upload_form_footer' ); ?>

</form>