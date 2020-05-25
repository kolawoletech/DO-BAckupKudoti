<?php
/**
 * Template for the UM Groups. Just created a new forum
 *
 * This template can be overridden by copying it to yourtheme/ultimate-member/um-groups/discussion/html/new-topic.php
 */
if( !defined( 'ABSPATH' ) ) {
	exit;
}
?>

<a href="{author_profile}" class="um-link">{author_name}</a> <?php _e('just created a new forum','um-groups');?> <a href="{post_url}" class="um-link"><?php _e('topic','um-groups');?></a>. <span class="post-meta"><a href="{post_url}">{post_title} {post_excerpt}</a></span>