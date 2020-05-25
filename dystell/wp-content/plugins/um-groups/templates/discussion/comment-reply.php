<?php
/**
 * Template for the UM Groups. Reply to comment
 *
 * Page: "Group", tab "Discussions"
 * Caller: Groups_Discussion->ajax_load_more_replies()
 * Parent template: discussion/comment.php
 *
 * This template can be overridden by copying it to yourtheme/ultimate-member/um-groups/discussion/comment-reply.php
 */
if( !defined( 'ABSPATH' ) ) {
	exit;
}
if( empty( $commentc ) ) {
	return;
}
?>

<div class="um-groups-commentl is-child" id="commentid-<?php echo esc_attr( $commentc->comment_ID ); ?>">

	<?php if( !$user_hidden ) { ?>
		<a href="#" class="um-groups-comment-hide um-tip-s"><i class="um-icon-close-round"></i></a>
	<?php } ?>

	<div class="um-groups-comment-avatar hidden-<?php echo esc_attr( $user_hidden ); ?>"><a href="<?php echo esc_url( um_user_profile_url() ); ?>"><?php echo $avatar; ?></a></div>

	<div class="um-groups-comment-hidden hidden-<?php echo esc_attr( $user_hidden ); ?>"><?php _e( 'Reply hidden. <a href="#" class="um-link">Show this reply</a>', 'um-groups' ); ?></div>

	<div class="um-groups-comment-info hidden-<?php echo esc_attr( $user_hidden ); ?>">
		<div class="um-groups-comment-data">
			<span class="um-groups-comment-author-link"><a href="<?php echo esc_url( um_user_profile_url() ); ?>" class="um-link"><?php echo esc_html( um_user( 'display_name' ) ); ?></a></span> <span class="um-groups-comment-text"><?php echo UM()->Groups()->discussion()->commentcontent( $commentc->comment_content ); ?></span>
			<textarea id="um-groups-reply-<?php echo esc_attr( $commentc->comment_ID ); ?>" class="original-content" style="display:none!important"><?php
				if( isset( $commentc->comment_content ) ) {
					echo $commentc->comment_content;
				}
				?></textarea>
		</div>

		<div class="um-groups-comment-meta">
			<?php if( is_user_logged_in() ) { ?>

				<?php if( UM()->Groups()->discussion()->user_liked_comment( $commentc->comment_ID ) ) { ?>
					<span><a href="#" class="um-link um-groups-comment-like active" data-like_text="<?php _e( 'Like', 'um-groups' ); ?>" data-unlike_text="<?php _e( 'Unlike', 'um-groups' ); ?>"><?php _e( 'Unlike', 'um-groups' ); ?></a></span>
				<?php } else { ?>
					<span><a href="#" class="um-link um-groups-comment-like" data-like_text="<?php _e( 'Like', 'um-groups' ); ?>" data-unlike_text="<?php _e( 'Unlike', 'um-groups' ); ?>"><?php _e( 'Like', 'um-groups' ); ?></a></span>
				<?php } ?>

				<span class="um-groups-comment-likes count-<?php echo esc_attr( ( int ) $likes ); ?>"><a href="#"><i class="um-faicon-thumbs-up"></i><ins class="um-groups-ajaxdata-commentlikes"><?php echo esc_html( ( int ) $likes ); ?></ins></a></span>

			<?php } ?>

			<span><a href="<?php echo esc_url( UM()->Groups()->discussion()->get_comment_link( UM()->Groups()->discussion()->get_permalink( absint( $commentc->comment_post_ID ) ), $commentc->comment_ID ) ); ?>" class="um-groups-comment-permalink"><?php echo esc_html( UM()->Groups()->discussion()->get_comment_time( $commentc->comment_date ) ); ?></a></span>

			<?php if( UM()->Groups()->discussion()->can_edit_comment( $commentc->comment_ID, get_current_user_id() ) ) { ?>
				<span class="um-groups-editc"><a href="#" ><i class="um-icon-edit"></i></a>
					<span class="um-groups-editc-d">
						<a href="#" class="edit" data-commentid="<?php echo esc_attr( $commentc->comment_ID ); ?>"><?php _e( 'Edit', 'um-groups' ); ?></a>
						<a href="#" class="delete" data-msg="<?php _e( 'Are you sure you want to delete this comment?', 'um-groups' ); ?>"><?php _e( 'Delete', 'um-groups' ); ?></a>
					</span>
				</span>
			<?php } ?>

		</div>
	</div>
</div>