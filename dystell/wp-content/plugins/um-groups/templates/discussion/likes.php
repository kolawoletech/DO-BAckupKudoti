<?php
/**
 * Template for the UM Groups, The group "Discussion" likes popup
 *
 * Page: "Group", tab "Discussions"
 * Caller: method Groups_Discussion->ajax_get_post_likes()
 * Caller: method Groups_Discussion->ajax_get_comment_likes()
 *
 * This template can be overridden by copying it to yourtheme/ultimate-member/um-groups/discussion/likes.php
 */
if ( !defined( 'ABSPATH' ) ) {
	exit;
}
?>

<div class="um-groups-modal-head um-popup-header">
	<?php _e( 'People who like this', 'um-groups' ); ?>
	<a href="#" class="um-groups-modal-hide"><i class="um-icon-close"></i></a>
</div>

<div class="um-groups-modal-body um-popup-autogrow2" data-simplebar>

	<?php
	foreach ( $users as $user ) {
		um_fetch_user( $user );
		?>

		<div class="um-groups-modal-item">
			<div class="um-groups-modal-user">
				<div class="um-groups-modal-pic"><a href="<?php echo esc_url( um_user_profile_url() ); ?>"><?php echo get_avatar( $user, 80 ); ?></a></div>
				<div class="um-groups-modal-info">
					<div class="um-groups-modal-name"><a href="<?php echo esc_url( um_user_profile_url() ); ?>"><?php echo esc_html( um_user( 'display_name' ) ); ?></a></div>
					<?php do_action( 'um_activity_likes_below_name', $item_id ); ?>
				</div>
			</div>
			<div class="um-groups-modal-hook">
				<?php do_action( 'um_activity_likes_beside_name', $item_id ); ?>
			</div><div class="um-clear"></div>
		</div>

	<?php } ?>

</div>

<div class="um-popup-footer" style="height:30px"></div>