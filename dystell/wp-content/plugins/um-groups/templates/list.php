<?php
/**
 * Template for the UM Groups. Groups page
 *
 * Page: "Groups"
 * Shortcode: [ultimatemember_groups]
 * Caller: method Groups_Shortcode->list_shortcode()
 *
 * This template can be overridden by copying it to yourtheme/ultimate-member/um-groups/list.php
 */
if( !defined( 'ABSPATH' ) ) {
	exit;
}
?>

<!-- um-groups/templates/list.php -->
<div class="um">
	<?php
	do_action( 'um_groups_directory_header', $args );
	do_action( 'um_groups_directory_search_form', $args );
	do_action( 'um_groups_directory_tabs', $args );
	do_action( 'um_groups_directory', $args );
	do_action( 'um_groups_directory_footer', $args );
	?>
</div>
