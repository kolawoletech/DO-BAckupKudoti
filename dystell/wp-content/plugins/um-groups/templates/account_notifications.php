<?php
/**
 * Template for the UM Groups.
 * Used on the "Account" page, "Notifications" tab
 *
 * Caller: method Groups_Account->account_tab()
 *
 * This template can be overridden by copying it to yourtheme/ultimate-member/um-groups/account_notifications.php
 */
if ( ! defined( 'ABSPATH' ) ) exit; ?>


<div class="um-field" data-key="">
	<div class="um-field-label"><strong><?php _e( 'Groups', 'um-messaging' ); ?></strong></div>

	<?php if ( $show_post_notification ) { ?>
		<div class="um-field-area">
			<label class="um-field-checkbox<?php if ( ! empty( $post_notification ) ) { ?> active<?php } ?>">
				<input type="checkbox" name="um_group_post_notification" value="1" <?php checked( ! empty( $post_notification ) ); ?>/>
				<span class="um-field-checkbox-state">
					<i class="um-icon-android-checkbox-<?php if ( ! empty( $post_notification ) ) { ?>outline<?php } else { ?>outline-blank<?php } ?>"></i>
				</span>
				<span class="um-field-checkbox-option"><?php _e('Notify me when someone posts on group','um-groups'); ?></span>
			</label>
			<div class="um-clear"></div>
		</div>
	<?php }

	if ( $show_comment_notification ) { ?>
		<div class="um-field-area">
			<label class="um-field-checkbox<?php if ( ! empty( $comment_notification ) ) { ?> active<?php } ?>">
				<input type="checkbox" name="um_group_comment_notification" value="1" <?php checked( ! empty( $comment_notification ) ); ?>/>
				<span class="um-field-checkbox-state">
					<i class="um-icon-android-checkbox-<?php if ( ! empty( $comment_notification ) ) { ?>outline<?php } else { ?>outline-blank<?php } ?>"></i>
				</span>
				<span class="um-field-checkbox-option"><?php _e('Notify me when someone comments on group','um-groups'); ?></span>
			</label>
			<div class="um-clear"></div>
		</div>
	<?php } ?>
</div>