<?php

/**
 * Template for the UM Groups, The group "Discussions" tab content
 *
 * Page: "Group", tab "Discussions"
 * Caller: function um_groups_single_page_content__discussion()
 *
 * This template can be overridden by copying it to yourtheme/ultimate-member/um-groups/tabs/discussions.php
 */
if( !defined( 'ABSPATH' ) ) {
	exit;
}

echo do_shortcode( '[ultimatemember_group_discussion_activity group_id="' . $group_id . '"]' );
