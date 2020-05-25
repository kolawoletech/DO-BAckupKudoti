<?php
/**
 * Template for the UM Groups. The list of groups
 *
 * Shortcode: [ultimatemember_groups_profile_list]
 * Caller: method Groups_Shortcode->profile_list()
 *
 * This template can be overridden by copying it to yourtheme/ultimate-member/um-groups/groups-list.php
 */
if( !defined( 'ABSPATH' ) ) {
	exit;
}
?>

<!-- um-groups/templates/groups-list.php -->
<div class="um um-groups-list">
	<?php
	do_action( 'um_groups_directory_tabs', $args );
	do_action( 'um_groups_directory', $args );
	do_action( 'um_groups_directory_footer', $args );
	?>
</div>
