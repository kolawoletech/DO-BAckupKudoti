<?php
/**
 * Template for the UM Groups. Post clone
 *
 * Page: "Group", tab "Discussions"
 * Caller: method Groups_Shortcode->discussion_activity()
 * Caller: method Groups_Shortcode->discussion_wall()
 *
 * This template can be overridden by copying it to yourtheme/ultimate-member/um-groups/discussion/clone.php
 */
if( !defined( 'ABSPATH' ) ) {
	exit;
}

global $user_ID, $um_group, $um_group_id;

um_fetch_user( $user_ID );
$show_pending_approval = get_query_var( 'show' );
?>

<?php if( !UM()->Groups()->discussion()->has_group_discussions( $um_group_id ) && !$show_pending_approval ) { ?>
	<div  class="um-groups-discussion-empty um-profile-note" style="display:block;">
		<?php _e( "No group discussions available.", "um-groups" ); ?>
	</div>
<?php } ?>

<div class="um-groups-widget um-groups-clone">

	<div class="um-groups-head">

		<div class="um-groups-left um-groups-author">
			<div class="um-groups-ava"><a href="<?php echo esc_url( um_user_profile_url() ); ?>"><?php echo get_avatar( um_user( 'ID' ), 80 ); ?></a></div>
			<div class="um-groups-author-meta">
				<div class="um-groups-author-url"><a href="<?php echo esc_url( um_user_profile_url() ); ?>" class="um-link"><?php echo esc_html( um_user( 'display_name' ) ); ?></a></div>
				<span class="um-groups-metadata">
					<a href=""><?php _e( 'Just now', 'um-groups' ); ?></a>
				</span>
			</div>
		</div>

		<div class="um-groups-right">

			<?php if( is_user_logged_in() ) { ?>

				<a href="#" class="um-groups-ticon um-groups-start-dialog" data-role="um-groups-tool-dialog"><i class="um-faicon-chevron-down"></i></a>

				<div class="um-groups-dialog um-groups-tool-dialog">

					<a href="#" class="um-groups-manage" data-cancel_text="<?php _e( 'Cancel editing', 'um-groups' ); ?>" data-update_text="<?php _e( 'Update', 'um-groups' ); ?>"><?php _e( 'Edit', 'um-groups' ); ?></a>

					<a href="#" class="um-groups-trash" data-msg="<?php _e( 'Are you sure you want to delete this post?', 'um-groups' ); ?>"><?php _e( 'Delete', 'um-groups' ); ?></a>

				</div>

			<?php } ?>

		</div>
		<div class="um-clear"></div>

	</div>

	<?php
	$has_video = null;
	$has_text_video = null;
	$has_oembed = null;
	if( isset( $post ) ) {
		$has_video = UM()->Groups()->discussion()->get_video( $post->ID );
		$has_text_video = get_post_meta( $post->ID, '_video_url', true );
		$has_oembed = get_post_meta( $post->ID, '_oembed', true );
	}
	$bodyinner_class = '';
	if( $has_video || $has_text_video ) {
		$bodyinner_class .= ' has-embeded-video';
	}
	if( $has_oembed ) {
		$bodyinner_class .= ' has-oembeded';
	}
	?>

	<div class="um-groups-body">
		<div class="um-groups-bodyinner <?php echo esc_attr( $bodyinner_class ); ?>">

			<div class="um-groups-bodyinner-edit">
				<textarea style="display:none!important"></textarea>
				<input type="hidden" name="_photo_" id="_photo_" value="" />
				<input type="hidden" name="_photo_url" id="_photo_url" value="" />
			</div>

			<div class="um-groups-bodyinner-txt"></div>

			<div class="um-groups-bodyinner-photo"></div>

			<div class="um-groups-bodyinner-video"></div>

		</div>
	</div>

	<div class="um-groups-foot status">
		<div class="um-groups-left um-groups-actions">
			<div class="um-groups-like"><a href="#"><i class="um-faicon-thumbs-up"></i><span class=""><?php _e( 'Like', 'um-groups' ); ?></span></a></div>
			<?php if( UM()->Groups()->discussion()->can_comment() ) { ?>
				<div class="um-groups-comment"><a href="#"><i class="um-faicon-comment"></i><span class=""><?php _e( 'Comment', 'um-groups' ); ?></span></a></div>
			<?php } ?>
		</div>
		<div class="um-clear"></div>
	</div>

	<?php
	$t_args = compact( 'post', 'post_id' );
	UM()->get_template( 'discussion/comments.php', um_groups_plugin, $t_args, true );
	?>

</div>
