<?php
/**
 * Template for the UM Groups. Just followed
 *
 * This template can be overridden by copying it to yourtheme/ultimate-member/um-groups/discussion/html/new-follow.php
 */
if( !defined( 'ABSPATH' ) ) {
	exit;
}
?>

<a href="{author_profile}" class="um-link">{author_name}</a> <?php _e('has just followed','um-groups');?> {user_photo} <a href="{user_profile}" class="um-link">{user_name}</a>