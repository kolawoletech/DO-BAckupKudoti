<?php if ( ! defined( 'ABSPATH' ) ) exit; ?>


<script type="text/template" id="tmpl-um_groups_members">

	<# _.each( data.users, function( user, key ) { #>

		<div class="um-groups-user-wrap" data-group-uid="{{{user.id}}}" data-group-id="{{{data.group_id}}}">

			<div class="user-details">
				<div class="um-group-image-wrap">
					<a href="{{{user.profile_url}}}">
						{{{user.avatar}}}
					</a>
				</div>

				<div class="um-group-buttons">

					<?php $can_approve_requests = UM()->Groups()->api()->can_approve_requests( $group_id ) || um_groups_admin_all_access();

					if ( $list == 'members' || ( $list == 'requests' && $can_approve_requests ) ) { ?>

						<# if ( Object.keys( user.dropdown_actions ).length > 0 ) { #>
							<div class="um-member-actions">
								<a href="javascript:void(0);" class="um-member-actions-a">
									<i class="um-faicon-ellipsis-h"></i>
								</a>
								<?php UM()->member_directory()->dropdown_menu_js( '.um-member-actions', 'click', 'user' ); ?>
							</div>
						<# } #>

					<?php } elseif ( $list == 'blocked' && $can_approve_requests ) { ?>

						<a href="javascript:void(0);" class="um-group-button" data-action-key="unblock">
							<?php _e( 'Unblock', 'um-groups' ) ?>
						</a>

					<?php } elseif ( $list == 'invites' ) {

						$can_invite = UM()->Groups()->api()->can_invite_members( $group_id ) || um_groups_admin_all_access();

						if ( $can_invite ) { ?>

							<# if ( user.is_invited ) {#>
								<a href="javascript:void(0);" class="um-group-button" data-action-key="resend_invite">
									<span class="um-faicon-check"></span> <?php _e( 'Invited', 'um-groups' ) ?>
								</a>
							<# } else { #>
								<a href="javascript:void(0);" class="um-group-button" data-action-key="invite">
									<?php _e( 'Invite', 'um-groups' ) ?>
								</a>
							<# } #>

						<?php }
					} ?>

				</div>

				<div class="um-group-texts">
					<div>
						<a href="{{{user.profile_url}}}">{{{user.display_name}}}</a>
					</div>
					<div>
						<ul>
							<?php if ( $list !== 'invites' ) { ?>
								<li>{{{user.date}}}</li>
							<?php } ?>
							{{{user.additional_content}}}
						</ul>
					</div>
				</div>
			</div>
		</div>

	<# }); #>

</script>