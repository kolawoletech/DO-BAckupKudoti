<?php
/**
 * Template for the UM Groups search form
 * Used on the "Groups" page
 * Called from the um_groups_directory_search_form() function
 *
 * This template can be overridden by copying it to yourtheme/ultimate-member/directory/directory_search.php.
 */
if( !defined( 'ABSPATH' ) ) {
	exit;
}
$select_style = "display: block;width: 100%;";
?>
<div class="um-groups-directory-header">
	<form class="um-group-form um-groups-search-form">
		
		<div class="um-field">
			<input type="text" name="groups_search" placeholder="<?php esc_attr_e( __( 'Search groups...', 'um-groups' ) ) ?>" value="<?php esc_attr_e( $search ) ?>"/>
		</div>

		<?php if( $arr_categories ) : ?>
			<div class="um-field um-field-select um-field-type_select" style="<?php esc_attr_e( $select_style ); ?>">
				<select name="cat" class="um-form-field um-s1" style="<?php esc_attr_e( $select_style ); ?>">
					<option value=""><?php _e( "All Categories", "um-groups" ); ?></option>
					<?php foreach( $arr_categories as $value => $title ) : ?>
						<option value="<?php esc_attr_e( $value ); ?>" <?php selected( $cat, $value, true ); ?>><?php echo esc_html( $title ); ?></option>
					<?php endforeach; ?>	
				</select>
			</div>
		<?php endif; ?>

		<?php if( $arr_tags ) : ?>
			<div class="um-field um-field-select um-field-type_select">
				<select name="tags" class="um-form-field um-s1" style="<?php esc_attr_e( $select_style ); ?>">
					<option value=""><?php _e( "All Tags", "um-groups" ); ?></option>
					<?php foreach( $arr_tags as $value => $title ) : ?>
						<option value="<?php esc_attr_e( $value ); ?>" <?php selected( $tags, $value, true ); ?>><?php echo esc_html( $title ); ?></option>
					<?php endforeach; ?>	
				</select>
			</div>
		<?php endif; ?>

		<?php if( 'own' == $filter ) : ?>
			<input type="hidden" name="filter" value="<?php esc_attr_e( $filter ) ?>" />	
		<?php endif; ?>

		<div class="um-col-alt">
			<div class="um-left um-half">
				<input type="submit" value="<?php esc_attr_e( "Search", "um-groups" ); ?>" class="um-button">
			</div>
			<div class="um-right um-half">
				<a href="<?php echo esc_url( get_the_permalink() ); ?>" class="um-button um-alt"><?php _e( 'Clear', 'um-groups' ); ?></a>
			</div>
			<div class="um-clear"></div>
		</div>
			
	</form>
	<div class="um-clear"></div>
</div>