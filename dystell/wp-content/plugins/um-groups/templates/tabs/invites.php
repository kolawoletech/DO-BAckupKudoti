<?php
/**
 * Template for the UM Groups. The group "Blocked" tab content
 *
 * Page: "Group", tab "Blocked"
 * Caller: function um_groups_single_page_content__blocked()
 * Child template: list-users.php
 *
 * This template can be overridden by copying it to yourtheme/ultimate-member/um-groups/tabs/blocked.php
 */
if ( ! defined( 'ABSPATH' ) ) exit;


$unique_hash = substr( md5( $group_id ), 10, 5 );
$current_page = ( ! empty( $_GET[ 'page_' . $unique_hash ] ) && is_numeric( $_GET[ 'page_' . $unique_hash ] ) ) ? (int) $_GET[ 'page_' . $unique_hash ] : 1;

//Search
$search = get_post_meta( $group_id, '_um_groups_invites_search', true );
$search = empty( $search ) ? false : true;
$search_from_url = '';
if ( $search ) {
	$search_from_url = ! empty( $_GET[ 'search_' . $unique_hash ] ) ? $_GET[ 'search_' . $unique_hash ] : '';
}

//Filters

$filters = get_post_meta( $group_id, '_um_groups_invites_search_fields', true );
$filters = empty( $filters ) ? false : true;

$search_filters = array();
$search_fields = get_post_meta( $group_id, '_um_groups_invites_fields', true );
if ( ! empty( $search_fields ) ) {
	$search_filters = apply_filters( 'um_groups_invites_user_search_filters', array_unique( array_filter( $search_fields ) ) );
}

if ( ! empty( $search_filters ) ) {
	$search_filters = array_filter( $search_filters, function( $item ) {
		return in_array( $item, array_keys( UM()->member_directory()->filter_fields ) );
	});

	$search_filters = array_values( $search_filters );
}

// Classes
$classes = '';
if ( $search ) {
	$classes .= ' um-member-with-search';
}

if ( $filters && count( $search_filters ) ) {
	$classes .= ' um-member-with-filters';
}

$filters_expanded = false;
$args['form_id'] = $group_id;

UM()->get_template( 'js/members-list.php', um_groups_plugin, array( 'list' => 'invites', 'group_id' => $group_id ), true );
UM()->get_template( 'members-header.php', '', array(), true );
UM()->get_template( 'members-pagination.php', '', array(), true ); ?>

<div class="um-groups-invites-users-wrapper" data-page="<?php echo esc_attr( $current_page ) ?>"
     data-hash="<?php echo esc_attr( $unique_hash ) ?>">
	<div class="um-members-overlay"><div class="um-ajax-loading"></div></div>

	<div class="um-member-directory-header">
		<?php if ( $search ) { ?>
			<div class="um-member-directory-header-row um-member-directory-search-row">
				<div class="um-member-directory-search-line">
					<label>
						<span><?php _e( 'Search:', 'um-groups' ); ?></span>
						<input type="search" class="um-search-line" placeholder="<?php esc_attr_e( 'Search', 'um-groups' ) ?>"  value="<?php echo esc_attr( $search_from_url ) ?>" aria-label="<?php esc_attr_e( 'Search', 'um-groups' ) ?>" speech />
					</label>
					<input type="button" class="um-do-search" value="<?php esc_attr_e( 'Search', 'um-groups' ); ?>" />
				</div>
			</div>
		<?php }

		if ( $filters && count( $search_filters ) ) { ?>
			<div class="um-member-directory-header-row">
				<div class="um-member-directory-nav-line">
					<span class="um-member-directory-filters">
						<span class="um-member-directory-filters-a<?php if ( $filters_expanded ) { ?> um-member-directory-filters-visible<?php } ?>">
							<a href="javascript:void(0);">
								<?php _e( 'More filters', 'um-groups' ); ?>
							</a>
							&nbsp;<i class="um-faicon-caret-down"></i><i class="um-faicon-caret-up"></i>
						</span>
					</span>
				</div>
			</div>
			<?php if ( is_array( $search_filters ) ) { ?>
				<script type="text/template" id="tmpl-um-members-filtered-line">
					<# if ( data.filters.length > 0 ) { #>
						<# _.each( data.filters, function( filter, key, list ) { #>
							<div class="um-members-filter-tag">
								<# if ( filter.type == 'slider' ) { #>
									<# if ( filter.value[0] == filter.value[1] ) { #>
										<strong>{{{filter.label}}}</strong>: {{{filter.value[0]}}}
									<# } else { #>
										{{{filter.value_label}}}
									<# } #>
								<# } else { #>
									<strong>{{{filter.label}}}</strong>: {{{filter.value_label}}}
								<# } #>
								<div class="um-members-filter-remove um-tip-n" data-name="{{{filter.name}}}"
								     data-value="{{{filter.value}}}" data-range="{{{filter.range}}}"
								     data-type="{{{filter.type}}}" title="<?php esc_attr_e( 'Remove filter', 'um-groups' ) ?>">&times;</div>
							</div>
						<# }); #>
					<# } #>
				</script>

				<div class="um-member-directory-header-row<?php if ( ! $filters_expanded ) { ?> um-header-row-invisible<?php } ?>">
					<div class="um-search um-search-<?php echo count( $search_filters ) ?><?php if ( ! $filters_expanded ) { ?> um-search-invisible<?php } ?>">
						<?php $i = 0;
						foreach ( $search_filters as $filter ) {
							$filter_content = UM()->member_directory()->show_filter( $filter, $args );
							if ( empty( $filter_content ) ) {
								continue;
							}

							$type = UM()->member_directory()->filter_types[ $filter ]; ?>

							<div class="um-search-filter um-<?php echo esc_attr( $type ) ?>-filter-type <?php echo ( $i != 0 && $i%2 !== 0 ) ? 'um-search-filter-2' : '' ?>"> <?php echo $filter_content; ?> </div>

							<?php $i++;
						} ?>
					</div>
				</div>
				<div class="um-member-directory-header-row">
					<div class="um-filtered-line">
						<div class="um-clear-filters"><a href="javascript:void(0);" class="um-clear-filters-a" title="<?php esc_attr_e( 'Remove all filters', 'um-groups' ) ?>"><?php _e( 'Clear all', 'um-groups' ); ?></a></div>
					</div>
				</div>
				<?php
			}
		} ?>
	</div>

	<div class="um-groups-members-list"></div>

	<div class="um-members-pagination-box"></div>

</div>