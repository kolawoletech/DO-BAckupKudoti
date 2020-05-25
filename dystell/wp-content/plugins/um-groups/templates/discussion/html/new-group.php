<?php
/**
 * Template for the UM Groups. Created the group
 *
 * This template can be overridden by copying it to yourtheme/ultimate-member/um-groups/discussion/html/new-group.php
 */
if ( !defined( 'ABSPATH' ) ) {
	exit;
}
?>

<span><a href="{group_author_profile}" class="um-link">{group_author_name}</a> <?php _e( 'created the group', 'um-groups' ); ?> <a href="{group_permalink}" class="um-link">{group_name}</a></span>