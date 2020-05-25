<?php if ( ! defined( 'ABSPATH' ) ) exit; ?>

<div class="um-groups-search-member">
	<input type="text" name="um_groups_add_new_members" autocomplete="off" placeholder="<?php _e("Start typing a username or email address to add a new member..."); ?>" />

	<div class="um-groups-found-user" style="display:none">
		<div class="image-wrapper">
		<img src="<?php echo esc_attr( um_get_default_avatar_uri() ) ?>" class="func-um_user gravatar avatar" />
		</div>
		<div class="user-info">
			<span class="display-name"></span>
			<span class="role"></span>
			<span class="has-joined-current"><?php _e("Already a member","um-groups"); ?><span class="added-by" data-text="<?php _e("added by","um-groups");?>"></span></span>
			<span class="actions">
				<input type="button" name="add-member" class="button primary-button" value="<?php _e("Add Member","um-groups");?>" />
				<a href="javascript:void(0);" class="new-search"><?php _e("New search","um-groups"); ?></a>
			</span>
		</div>
		<div class="um-clear"></div>
	</div>

</div>