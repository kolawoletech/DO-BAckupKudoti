<?php
/**
 * Template for the UM Groups Single tabs
 * Used on the "Single Group" page
 * Called from the um_groups_single_page_tabs() function
 *
 * This template can be overridden by copying it to yourtheme/ultimate-member/tabs/single-group-tabs.php.
 */
if ( !defined( 'ABSPATH' ) ) {
	exit;
}

if ( empty( $tabs ) ) {
	return;
}
?>

<div class="um-group-tabs-wrap">
	<ul class="um-groups-single-tabs">
		<?php foreach ( $tabs as $tab ): ?>
			<li class="um-groups-tab-slug_<?php echo esc_attr( $tab[ 'slug' ] ); ?> <?php echo esc_attr( ( isset( $tab[ 'default' ] ) && empty( $param_tab ) ) || $param_tab == $tab[ 'slug' ] ? 'active' : '' ); ?>"><a href="<?php echo esc_url( $tab[ 'url' ] ); ?>"><?php echo $tab[ 'name' ]; ?></a></li>
		<?php endforeach; ?>
	</ul>
	<input type="hidden" name="group_id" value="<?php echo esc_attr( $group_id ); ?>"/>
	<input type="hidden" name="group_current_tab" value="<?php echo esc_attr( UM()->Groups()->api()->current_group_tab ); ?>"/>
	<div class="um-clear"></div>
</div>
