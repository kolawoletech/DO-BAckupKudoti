<?php
/**
 * Template for the UM Groups tabs
 * Used on the "Groups" page
 * Called from the um_groups_directory_tabs() function
 *
 * This template can be overridden by copying it to yourtheme/ultimate-member/directory/directory_tabs.php.
 */
if( !defined( 'ABSPATH' ) ) {
	exit;
}
?>

<div id="um-groups-filters" class="um-groups-found-posts">
	<ul class="filters">
		<li class="all <?php echo esc_attr( 'all' == $filter || empty( $filter ) ? 'active' : '' ); ?>">
			<a href="<?php esc_attr_e( um_get_core_page( 'groups' ) ) ?>"><?php printf( __( 'All Groups <span>%s</span>', 'um-groups' ), um_groups_get_all_groups_count() ) ?></a>
		</li>

		<?php if( is_user_logged_in() ) : ?>
			<li class="own <?php echo esc_attr( 'own' == $filter ? 'active' : '' ); ?>">
				<a href="<?php esc_attr_e( um_get_core_page( 'groups' ) ) ?>?filter=own"><?php printf( __( 'My Groups <span>%s</span>', 'um-groups' ), um_groups_get_own_groups_count() ) ?></a>
			</li>
			<li class="create">
				<a href="<?php esc_attr_e( um_get_core_page( 'create_group' ) ) ?>"><?php _e( 'Create a Group', 'um-groups' ) ?></a>
			</li>
		<?php endif; ?>
	</ul>
</div>




