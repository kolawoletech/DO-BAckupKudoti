<?php
/**
 * Template for the UM Groups. Single post comment
 *
 * Page: "Group", tab "Discussions"
 * Parent template: discussion/comments.php
 *
 * This template can be overridden by copying it to yourtheme/ultimate-member/um-groups/discussion/comment.php
 */
if( !defined( 'ABSPATH' ) ) {
	exit;
}

foreach( $comments as $comment ) {
	um_fetch_user( $comment->user_id );

	$avatar = get_avatar( um_user( 'ID' ), 80 );
	$likes = ( int ) get_comment_meta( $comment->comment_ID, '_likes', true );
	$user_hidden = UM()->Groups()->discussion()->user_hidden_comment( $comment->comment_ID );
	?>

	<div class="um-groups-commentwrap" data-comment_id="<?php echo esc_attr( $comment->comment_ID ); ?>">

		<div class="um-groups-commentl" id="commentid-<?php echo esc_attr( $comment->comment_ID ); ?>">

			<?php if( !$user_hidden ) { ?>
				<a href="#" class="um-groups-comment-hide um-tip-s"><i class="um-icon-close-round"></i></a>
			<?php } ?>

			<div class="um-groups-comment-avatar hidden-<?php echo esc_attr( $user_hidden ); ?>"><a href="<?php echo esc_url( um_user_profile_url() ); ?>"><?php echo $avatar; ?></a></div>

			<div class="um-groups-comment-hidden hidden-<?php echo esc_attr( $user_hidden ); ?>"><?php _e( 'Comment hidden. <a href="#" class="um-link">Show this comment</a>', 'um-groups' ); ?></div>

			<div class="um-groups-comment-info hidden-<?php echo esc_attr( $user_hidden ); ?>">

				<div class="um-groups-comment-data">
					<span class="um-groups-comment-author-link"><a href="<?php echo esc_url( um_user_profile_url() ); ?>" class="um-link"><?php echo esc_html( um_user( 'display_name' ) ); ?></a></span>
					<span class="um-groups-comment-text"><?php echo str_replace( "\'", "'", UM()->Groups()->discussion()->commentcontent( $comment->comment_content ) ); ?></span>
					<textarea id="um-groups-reply-<?php echo esc_attr( $comment->comment_ID ); ?>" class="original-content" style="display:none!important"><?php echo $comment->comment_content; ?></textarea>
				</div>

				<div class="um-groups-comment-meta">
					<?php if( is_user_logged_in() && UM()->Groups()->api()->has_joined_group() ) { ?>
						<?php if( UM()->Groups()->discussion()->user_liked_comment( $comment->comment_ID ) ) { ?>
							<span><a href="#" class="um-link um-groups-comment-like active" data-like_text="<?php _e( 'Like', 'um-groups' ); ?>" data-unlike_text="<?php _e( 'Unlike', 'um-groups' ); ?>"><?php _e( 'Unlike', 'um-groups' ); ?></a></span>
						<?php } else { ?>
							<span><a href="#" class="um-link um-groups-comment-like" data-like_text="<?php _e( 'Like', 'um-groups' ); ?>" data-unlike_text="<?php _e( 'Unlike', 'um-groups' ); ?>"><?php _e( 'Like', 'um-groups' ); ?></a></span>
						<?php } ?>

						<span class="um-groups-comment-likes count-<?php echo esc_attr( $likes ); ?>"><a href="#"><i class="um-faicon-thumbs-up"></i><ins class="um-groups-ajaxdata-commentlikes"><?php echo esc_html( $likes ); ?></ins></a></span>

						<?php if( UM()->Groups()->discussion()->can_comment() ) { ?>
							<span><a href="#" class="um-link um-groups-comment-reply" data-commentid="<?php echo esc_attr( $comment->comment_ID ); ?>"><?php _e( 'Reply', 'um-groups' ); ?></a></span>
						<?php } ?>
					<?php } ?>

					<span><a href="<?php echo esc_url( UM()->Groups()->discussion()->get_comment_link( UM()->Groups()->discussion()->get_permalink( absint( $comment->comment_post_ID ) ), $comment->comment_ID ) ); ?>" class="um-groups-comment-permalink"><?php echo esc_html( UM()->Groups()->discussion()->get_comment_time( $comment->comment_date ) ); ?></a></span>

					<?php if( UM()->Groups()->discussion()->can_edit_comment( $comment->comment_ID, get_current_user_id() ) ) { ?>
						<span class="um-groups-editc"><a href="#"><i class="um-icon-edit"></i></a>
							<span class="um-groups-editc-d">
								<a href="#" class="edit" data-commentid="<?php echo esc_attr( $comment->comment_ID ); ?>"><?php _e( 'Edit', 'um-groups' ); ?></a>
								<a href="#" class="delete" data-msg="<?php _e( 'Are you sure you want to delete this comment?', 'um-groups' ); ?>"><?php _e( 'Delete', 'um-groups' ); ?></a>
							</span>
						</span>
					<?php } ?>

				</div>
			</div>
		</div>

		<?php UM()->Groups()->discussion()->the_comment_child( $comment ); ?>

	</div>

	<?php
}

// reset um user
um_reset_user();
