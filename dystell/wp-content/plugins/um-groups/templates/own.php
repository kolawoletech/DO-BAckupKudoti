<?php
/**
 * Template for the UM Groups. The list of user groups
 *
 * Shortcode: [ultimatemember_my_groups]
 * Caller: method Groups_Shortcode->own_groups()
 *
 * This template can be overridden by copying it to yourtheme/ultimate-member/um-groups/own.php
 */
if( !defined( 'ABSPATH' ) ) {
	exit;
}
?>

<!-- um-groups/templates/own.php -->
<div class="um">
	<?php
	do_action( 'um_groups_directory_header', $args );
	do_action( 'um_groups_own_directory_tabs', $args );
	do_action( 'um_groups_directory', $args );
	do_action( 'um_groups_directory_footer', $args );
	?>
</div>
