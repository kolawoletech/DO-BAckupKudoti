<?php if ( ! defined( 'ABSPATH' ) ) exit; ?>

<div style="float:right;">
	<?php $group_members_orders = UM()->Groups()->api()->group_members_order; ?>
	<select name="um_groups_sort_member_list" id="um_groups_sort_member_list">
		<?php  foreach( $group_members_orders as $slug => $title ): ?>
			<option value="<?php echo esc_attr( $slug ); ?>"><?php echo esc_html( $title ); ?></option>
		<?php  endforeach; ?>
	</select>

	<?php $group_member_statuses = UM()->Groups()->api()->get_member_statuses(); ?>
	<select name="um_groups_filter_status" id="um_groups_filter_status">
		<option value=""><?php _e("Show all group status","um-groups");?></option>
		<?php  foreach( $group_member_statuses as $status => $title ): ?>
			<option value="<?php echo esc_attr( $status ); ?>"><?php echo esc_html( $title ); ?></option>
		<?php  endforeach; ?>
	</select>

	<?php $group_member_roles = UM()->Groups()->api()->get_member_roles(); ?>
	<select name="um_groups_filter_role" id="um_groups_filter_role">
		<option value=""><?php _e("Show all group roles","um-groups");?></option>
		<?php foreach ( $group_member_roles as $status => $title ) { ?>
			<option value="<?php echo esc_attr( $status ); ?>"><?php echo esc_html( $title ); ?></option>
		<?php } ?>
	</select>
</div>
<div class="um-clear"></div>

<table id="um_groups_manage_members" class="display">
	<thead>
	<tr>
		<th style="width:35%"><?php _e("Name","um-groups");?></th>
		<th style="width:15%"><?php _e("Status","um-groups");?></th>
		<th style="width:15%"><?php _e("Group Role","um-groups");?></th>
		<th style="width:18%"><?php _e("Actions","um-groups");?></th>
		<th><?php _e("Hidden Status","um-groups");?></th>
		<th><?php _e("Hidden Role","um-groups");?></th>
		<th><?php _e("Hidden User Login","um-groups");?></th>
		<th><?php _e("Hidden User Email","um-groups");?></th>
		<th><?php _e("Hidden Timestamp","um-groups");?></th>
	</tr>
	</thead>
	<tbody>
        
	</tbody>
	<tfoot>
	<tr>
		<th><?php _e("Name","um-groups");?></th>
		<th><?php _e("Status","um-groups");?></th>
		<th><?php _e("Group Role","um-groups");?></th>
		<th><?php _e("Actions","um-groups");?></th>
		<th><?php _e("Hidden Status","um-groups");?></th>
		<th><?php _e("Hidden Role","um-groups");?></th>
		<th><?php _e("Hidden User Login","um-groups");?></th>
		<th><?php _e("Hidden User Email","um-groups");?></th>
		<th><?php _e("Hidden Timestamp","um-groups");?></th>
	</tr>
	</tfoot>
</table>