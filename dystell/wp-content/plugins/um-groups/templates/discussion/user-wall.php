<?php
/**
 * Template for the UM Groups. Group posts
 *
 * Page: "Group", tab "Discussions"
 * Caller: method Groups_Shortcode->discussion_activity()
 * Caller: method Groups_Shortcode->discussion_wall()
 *
 * This template can be overridden by copying it to yourtheme/ultimate-member/um-groups/discussion/user-wall.php
 */
if( !defined( 'ABSPATH' ) ) {
	exit;
}

global $core_page, $um_group, $um_group_id;

if( empty( $um_group_id ) ) {
	error_log( 'UM Error: Undefined variable: um_group_id in ' . __FILE__ );
	return;
}

$args = array(
		'post_type'			 => 'um_groups_discussion',
		'posts_per_page' => ( UM()->mobile()->isMobile() ) ? UM()->options()->get( 'groups_posts_num_mob' ) : UM()->options()->get( 'groups_posts_num' ),
		'post_status'		 => array( 'publish' ),
);

if( isset( $offset ) ) {
	$args[ 'offset' ] = $offset;
}

if( isset( $user_wall ) && $user_wall ) {
	$args[ 'author' ] = sanitize_html_class( $user_id );
}

if( isset( $wall_post ) && $wall_post > 0 ) {

	$args[ 'post__in' ] = array( $wall_post );
} else if( isset( $hashtag ) && $hashtag ) {

	$args[ 'tax_query' ] = array( array( 'taxonomy' => 'um_hashtag', 'field' => 'slug', 'terms' => array( $hashtag ) ) );
} else if( UM()->Groups()->discussion()->followed_ids() ) {

	$args[ 'meta_query' ][] = array( 'key' => '_user_id', 'value' => UM()->Groups()->discussion()->followed_ids(), 'compare' => 'IN' );
} elseif( UM()->Groups()->discussion()->friends_ids() ) {

	$args[ 'meta_query' ][] = array( 'key' => '_user_id', 'value' => UM()->Groups()->discussion()->friends_ids(), 'compare' => 'IN' );
} else if( um_is_core_page( 'user' ) || ( isset( $core_page ) && $core_page == 'user' & defined( 'DOING_AJAX' ) ) ) {

	$um_current_page_tab = get_query_var( 'profiletab' );

	if( $um_current_page_tab == 'activity' && !defined( 'DOING_AJAX' ) ) {
		unset( $args[ 'author' ] );

		$args[ 'meta_query' ][] = array(
				'relation' => 'OR',
				array( 'key'			 => '_wall_id',
						'value'		 => $user_id,
						'compare'	 => '='
				),
				array( 'key'			 => '_user_id',
						'value'		 => $user_id,
						'compare'	 => '='
				)
		);
	}
}

if( isset( $user_wall ) && $user_wall && isset( $core_page ) && $core_page != 'user' ) {
	$args[ 'author' ] = sanitize_html_class( $user_id );
}

$args[ 'meta_query' ][] = array( 'key' => '_group_id', 'value' => $um_group_id, 'compare' => '=' );

$um_group_moderation = get_post_meta( $um_group_id, '_um_groups_posts_moderation', true );

if( 'require-moderation' == $um_group_moderation ) {
	$show_pending_approval = get_query_var( 'show' );

	$can_moderate_post = UM()->Groups()->api()->can_moderate_posts( $um_group_id );

	if( 'pending' == $show_pending_approval || ( isset( $show_pending ) && $show_pending == true ) ) {
		$args[ 'meta_key' ] = '_group_moderation';
		$args[ 'meta_value' ] = 'pending_review';
		if( !$can_moderate_post ) {
			$args[ 'author' ] = get_current_user_id();
		}
	} else {
		$args[ 'meta_key' ] = '_group_moderation';
		$args[ 'meta_value' ] = 'approved';
	}

	if( $can_moderate_post ) {
		if( 'pending' == $show_pending_approval || ( isset( $show_pending ) && $show_pending == true ) ) {
			$args[ 'meta_key' ] = '_group_moderation';
			$args[ 'meta_value' ] = 'pending_review';
		} else {
			$args[ 'meta_key' ] = '_group_moderation';
			$args[ 'meta_value' ] = 'approved';
		}
	}
}

$args = apply_filters( 'um_groups_discussion_wall_args', $args );

$wallposts = new WP_Query( $args );

if( $wallposts->found_posts == 0 ) return;

foreach( $wallposts->posts as $post ) {
	setup_postdata( $post );

	$post_id = $post->ID;
	$author_id = UM()->Groups()->discussion()->get_author( $post->ID );
	$wall_id = UM()->Groups()->discussion()->get_wall( $post->ID );
	$post_link = UM()->Groups()->discussion()->get_permalink( $post->ID );


	um_fetch_user( $author_id );

	$pending_approval = false;
	$can_view = apply_filters( 'um_groups_wall_can_view', -1, $author_id );

	// exclude private walls
	if( $can_view >= 0 ) {
		continue;
	}

	if( (isset( $show_pending_approval ) && 'pending' == $show_pending_approval) || ( isset( $show_pending ) && $show_pending == true ) ) {
		$post_link = add_query_arg( 'show', 'pending', $post_link );
		$pending_approval = true;
	}

	$has_video = UM()->Groups()->discussion()->get_video( $post->ID );
	$has_text_video = get_post_meta( $post->ID, '_video_url', true );
	$has_oembed = get_post_meta( $post->ID, '_oembed', true );
	?>

	<div class="um-groups-widget" id="postid-<?php echo esc_attr( $post->ID ); ?>">

		<div class="um-groups-head">

			<div class="um-groups-left um-groups-author">
				<div class="um-groups-ava"><a href="<?php echo esc_url( um_user_profile_url() ); ?>"><?php echo get_avatar( $author_id, 80 ); ?></a></div>
				<div class="um-groups-author-meta">
					<div class="um-groups-author-url">
						<a href="<?php echo esc_url( um_user_profile_url() ); ?>" class="um-link"><?php echo um_user( 'display_name', 'html' ); ?></a>
						<?php
						if( $wall_id && $wall_id != $author_id ) {
							um_fetch_user( $wall_id );
							echo '<i class="um-icon-forward"></i>';
							echo '<a href="' . um_user_profile_url() . '" class="um-link">' . um_user( 'display_name', 'html' ) . '</a>';
						}
						?>
					</div>
					<span class="um-groups-metadata">
						<a href="<?php echo esc_url( $post_link ); ?>"><?php echo esc_html( UM()->Groups()->discussion()->get_post_time( $post->ID ) ); ?></a>
					</span>
				</div>
			</div>

			<div class="um-groups-right">

				<?php if( isset( $can_moderate_post ) && $can_moderate_post && $pending_approval ) { ?>
					<a href="#" class="um-groups-ticon um-groups-post-approval-tool um-groups-start-dialog um-tip-n" title="<?php _e( "Approve", "um-groups" ); ?>" original-title="<?php _e( "Approve", "um-groups" ); ?>" data-discussion-id="<?php echo esc_attr( $post->ID ); ?>" data-uid="<?php echo esc_attr( $author_id ); ?>" data-role="approve">
						<i class="um-faicon-check"></i>
					</a>
					<?php if( $author_id != get_current_user_id() ) { ?>
						<a href="#" class="um-groups-ticon um-groups-post-approval-tool um-groups-start-dialog  um-tip-n" title="<?php _e( "Delete", "um-groups" ); ?>" data-msg="<?php _e( "Are you sure you want to delete this post?", "um-groups" ); ?>"   original-title="<?php _e( "Delete", "um-groups" ); ?>"  data-discussion-id="<?php echo esc_attr( $post->ID ); ?>"  data-uid="<?php echo esc_attr( $author_id ); ?>" data-role="delete">
							<i class="um-faicon-remove"></i>
						</a>
					<?php } ?>


				<?php } ?>

				<?php if( is_user_logged_in() && (!$pending_approval || $author_id == get_current_user_id() ) ) { ?>

					<a href="#" class="um-groups-ticon um-groups-start-dialog" data-role="um-groups-tool-dialog"><i class="um-faicon-chevron-down"></i></a>

					<div class="um-groups-dialog um-groups-tool-dialog">

						<?php if( ( current_user_can( 'edit_users' ) || $author_id == get_current_user_id() ) || ( UM()->Groups()->discussion()->get_action_type( $post->ID ) == 'status' ) && !$pending_approval ) { ?>
							<a href="#" class="um-groups-manage" data-cancel_text="<?php _e( 'Cancel editing', 'um-groups' ); ?>" data-update_text="<?php _e( 'Update', 'um-groups' ); ?>"><?php _e( 'Edit', 'um-groups' ); ?></a>
						<?php } ?>

						<?php if( current_user_can( 'edit_users' ) || $author_id == get_current_user_id() ) { ?>
							<a href="#" class="um-groups-trash" data-msg="<?php _e( 'Are you sure you want to delete this post?', 'um-groups' ); ?>"><?php _e( 'Delete', 'um-groups' ); ?></a>
						<?php } ?>

						<?php if( $author_id != get_current_user_id() ) { ?>
							<span class="sep"></span>
							<a href="#" class="um-groups-report <?php if( UM()->Groups()->discussion()->reported( $post->ID ) ) echo 'flagged'; ?>" data-report="<?php _e( 'Report', 'um-groups' ); ?>" data-cancel_report="<?php _e( 'Cancel report', 'um-groups' ); ?>"><?php echo esc_html( ( UM()->Groups()->discussion()->reported( $post->ID ) ) ? __( 'Cancel report', 'um-groups' ) : __( 'Report', 'um-groups' ) ); ?></a>
						<?php } ?>

					</div>

				<?php } ?>

			</div>
			<div class="um-clear"></div>

		</div>

		<div class="um-groups-body">
			<div class="um-groups-bodyinner<?php
			if( $has_video || $has_text_video ) {
				echo ' has-embeded-video';
			}
			?> <?php
			if( $has_oembed ) {
				echo ' has-oembeded';
			}
			?>">
				<div class="um-groups-bodyinner-edit">
					<textarea style="display: none;"><?php echo esc_attr( get_post_meta( $post->ID, '_original_content', true ) ); ?></textarea>

					<?php $photo_base = get_post_meta( $post->ID, '_photo', true ); ?>
					<input type="hidden" name="_photo_" id="_photo_" value="<?php echo esc_attr( $photo_base ); ?>" />

					<?php $photo_base = wp_basename( $photo_base ); ?>
					<?php $photo_url = UM()->uploader()->get_upload_user_base_url( $author_id ) . "/{$photo_base}"; ?>
					<input type="hidden" name="_photo_url" id="_photo_url" value="<?php echo esc_attr( $photo_url ); ?>" />


				</div>

				<?php $um_groups_discussion_post = UM()->Groups()->discussion()->get_content( $post->ID, $has_video ); ?>
				<?php $um_shared_link = get_post_meta( $post->ID, '_shared_link', true ); ?>
				<?php if( $um_groups_discussion_post || $um_shared_link ) { ?>
					<div class="um-groups-bodyinner-txt">
						<?php echo $um_groups_discussion_post; ?>
						<?php echo $um_shared_link; ?>
					</div>
				<?php } ?>

				<div class="um-groups-bodyinner-photo">
					<?php echo UM()->Groups()->discussion()->get_photo( $post->ID, '', $author_id ); ?>
				</div>

				<?php if( empty( $um_shared_link ) ) { ?>
					<div class="um-groups-bodyinner-video">
						<?php echo $has_video; ?>
					</div>
				<?php } ?>

			</div>

			<?php
			$likes = UM()->Groups()->discussion()->get_likes_number( $post->ID );
			$comments = UM()->Groups()->discussion()->get_comments_number( $post->ID );

			if( $likes > 0 || $comments > 0 ) {
				?>

				<div class="um-groups-disp">
					<div class="um-groups-left">
						<div class="um-groups-disp-likes">
							<a href="#" class="um-groups-show-likes um-link" data-post_id="<?php echo esc_attr( $post->ID ); ?>">
								<span class="um-groups-post-likes"><?php echo esc_html( $likes ); ?></span>
								<span class="um-groups-disp-span"><?php _e( 'likes', 'um-groups' ); ?></span>
							</a>
						</div>
						<div class="um-groups-disp-comments">
							<a href="#" class="um-link">
								<span class="um-groups-post-comments"><?php echo esc_html( $comments ); ?></span>
								<span class="um-groups-disp-span"><?php _e( 'comments', 'um-groups' ); ?></span>
							</a>
						</div>
					</div>
					<div class="um-groups-faces um-groups-right">
						<?php echo UM()->Groups()->discussion()->get_faces( $post->ID ); ?>
					</div>
					<div class="um-clear"></div>
				</div>
				<div class="um-clear"></div>

			<?php } ?>

		</div>

		<?php if( !$pending_approval ) { ?>
			<div class="um-groups-foot status" id="wallcomments-<?php echo esc_attr( $post->ID ); ?>">

				<?php if( is_user_logged_in() && UM()->Groups()->api()->has_joined_group() ) { ?>

					<div class="um-groups-left um-groups-actions">
						<?php if( UM()->Groups()->discussion()->user_liked( $post->ID ) ) { ?>
							<div class="um-groups-like active" data-like_text="<?php _e( 'Like', 'um-groups' ); ?>" data-unlike_text="<?php _e( 'Unlike', 'um-groups' ); ?>"><a href="#"><i class="um-faicon-thumbs-up um-active-color"></i><span class=""><?php _e( 'Unlike', 'um-groups' ); ?></span></a></div>
						<?php } else { ?>
							<div class="um-groups-like" data-like_text="<?php _e( 'Like', 'um-groups' ); ?>" data-unlike_text="<?php _e( 'Unlike', 'um-groups' ); ?>"><a href="#"><i class="um-faicon-thumbs-up"></i><span class=""><?php _e( 'Like', 'um-groups' ); ?></span></a></div>
						<?php } ?>
						<?php if( UM()->Groups()->discussion()->can_comment() ) { ?>
							<div class="um-groups-comment"><a href="#"><i class="um-faicon-comment"></i><span class=""><?php _e( 'Comment', 'um-groups' ); ?></span></a></div>
						<?php } ?>
					</div>

				<?php } else { ?>
					<div class="um-groups-left um-groups-join"><?php _e( 'Please join group to like or comment on this post.', 'um-activity' ); ?></div>
				<?php } ?>

				<div class="um-clear"></div>
			</div>

			<?php
			$t_args = compact( 'post', 'post_id' );
			UM()->get_template( 'discussion/comments.php', um_groups_plugin, $t_args, true );
			?>

		<?php } ?>
	</div>

	<?php
}

wp_reset_postdata();
?>

<div class="um-groups-load"></div>
