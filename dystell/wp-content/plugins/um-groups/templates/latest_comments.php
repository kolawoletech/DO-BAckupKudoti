<?php
/**
 * Template for the UM Groups. Display group posts with the latest comments
 *
 * Shortcode: [ultimatemember_group_comments]
 * Caller: Groups_Shortcode->comments() method
 *
 * This template can be overridden by copying it to:
 * yourtheme/ultimate-member/um-groups/latest_comments.php
 */
if( !defined( 'ABSPATH' ) ) {
	exit;
}

foreach( $posts as $post ) {

	$author_id = UM()->Groups()->discussion()->get_author( $post->ID );
	if( empty( $author_id ) ) {
		$author_id = $post->post_author;
	}
	$author_img = get_avatar( $author_id, 80 );
	$author_url = um_user_profile_url( $author_id );
	$author = get_userdata( $author_id );

	$group_id = $post->_group_id;
	$group_img = UM()->Groups()->api()->get_group_image( $group_id, 'default', 80, 80 );
	$group_url = get_permalink( $group_id );
	$group = get_post( $group_id );

	$post_id = $post->ID;
	$post_link = UM()->Groups()->discussion()->get_permalink( $post_id );
	$post_time = UM()->Groups()->discussion()->get_post_time( $post_id );
	$post_video = UM()->Groups()->discussion()->get_video( $post_id );
	$post_comments = UM()->Groups()->discussion()->get_comments_number( $post_id );
	$post_content = UM()->Groups()->discussion()->get_content( $post_id, $post_video );
	$post_photo = UM()->Groups()->discussion()->get_photo( $post_id, '', $author_id );

	$post_attr_id = "postid-$post_id";
	$post_body_classes = '';
	if( $post_video || $post->_video_url ) {
		$post_body_classes .= ' has-embeded-video';
	}
	if( $post->_oembed ) {
		$post_body_classes .= ' has-oembeded';
	}
	?>

	<div class="um-groups-widget" id="<?php echo esc_attr( $post_attr_id ); ?>">
		<div class="um-groups-head">

			<div class="um-groups-left um-groups-author">
				<div class="um-groups-ava"><a href="<?php echo esc_url( $author_url ); ?>"><?php echo $author_img; ?></a></div>
				<div class="um-groups-author-meta">
					<div class="um-groups-author-url">
						<a href="<?php echo esc_url( $author_url ); ?>" class="um-link"><?php echo esc_html( $author->display_name ); ?></a>
					</div>
					<span class="um-groups-metadata">
						<a href="<?php echo esc_url( $post_link ); ?>"><?php echo esc_html( $post_time ); ?></a>
					</span>
				</div>
			</div>

			<div class="um-groups-right">
				<div class="um-groups-ava"><a href="<?php echo esc_url( $group_url ); ?>"><?php echo $group_img; ?></a></div>
				<div class="um-groups-author-meta">
					<div class="um-groups-author-url">
						<a href="<?php echo esc_url( $group_url ); ?>" class="um-link"><?php echo esc_html( $group->post_title ); ?></a>
					</div>
					<span class="um-groups-metadata">
						<?php printf( __( '%s Group', 'um-groups' ), um_groups_get_privacy_title( $group_id ) ); ?> <?php echo um_groups_get_privacy_icon( $group_id ); ?>
					</span>
				</div>
			</div>

			<div class="um-clear"></div>
		</div>

		<div class="um-groups-body">
			<div class="um-groups-bodyinner <?php echo esc_attr( $post_body_classes ); ?>">

				<?php if( $post_content ) { ?>
					<div class="um-groups-bodyinner-txt"><?php echo $post_content; ?></div>
				<?php } ?>

				<?php if( $post_photo ) { ?>
					<div class="um-groups-bodyinner-photo"><?php echo $post_photo; ?></div>
				<?php } ?>

				<?php if( empty( $post->_shared_link ) ) { ?>
					<div class="um-groups-bodyinner-video"><?php echo $post_video; ?></div>
				<?php } ?>

			</div>
			<div class="um-clear"></div>
		</div>

		<?php
		if( $post_comments ) {
			$t_args = compact( 'post', 'post_id' );
			UM()->get_template( 'discussion/comments.php', um_groups_plugin, $t_args, true );
		}
		?>
	</div>

	<?php
}
?>
<style type="text/css">
	.ultimatemember_group_comments .um-groups-head{
		padding: 8px 15px;
	}
	.ultimatemember_group_comments .um-groups-right {
		direction: rtl;
		min-height: 40px;
		padding-right: 50px;
	}
	.ultimatemember_group_comments .um-groups-right .um-groups-ava{
		left: auto;
		right: 0px;
	}
</style>