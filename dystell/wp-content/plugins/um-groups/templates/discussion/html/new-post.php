<?php
/**
 * Template for the UM Groups. Just created a new blog
 *
 * This template can be overridden by copying it to yourtheme/ultimate-member/um-groups/discussion/html/new-post.php
 */
if( !defined( 'ABSPATH' ) ) {
	exit;
}
?>

<a href="{author_profile}" class="um-link">{author_name}</a> <?php _e('just created a new blog','um-groups');?> <a href="{post_url}" class="um-link"><?php _e('post','um-groups');?></a>. <span class="post-meta"><a href="{post_url}">{post_image} {post_title} {post_excerpt}</a></span>