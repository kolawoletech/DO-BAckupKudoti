<?php
/**
 * Template for the UM Groups list
 * Used on the "Groups" page, and "Profile" page "Groups" tab
 * Called from the um_groups_directory() function
 *
 * This template can be overridden by copying it to yourtheme/ultimate-member/directory/directory.php.
 */
if ( ! defined( 'ABSPATH' ) ) exit; ?>

<div class="um-groups-directory">

	<?php foreach( $groups as $group ) {
		$count = um_groups_get_member_count( $group->ID );
	
		if ( 'small' == $args['avatar_size'] ) {
			$image = UM()->Groups()->api()->get_group_image( $group->ID, 'default', 50, 50 );
		} else {
			$image = UM()->Groups()->api()->get_group_image( $group->ID, 'default', 100, 100 );
		}
		?>

		<div class="um-group-item">

			<?php if ( true == $args['show_actions'] ) { ?>
				<div class="actions">
					<ul>
						<li><?php do_action( 'um_groups_join_button', $group->ID ); ?></li>
						<li class="last-active"><?php echo esc_html( __( 'Last active: ', 'um-groups' ) . human_time_diff( UM()->Groups()->api()->get_group_last_activity( $group->ID, true ), current_time( 'timestamp' ) ) . __( ' ago', 'um-groups' ) ); ?></li>
						<li class="count-members"><?php echo sprintf( _n( '<span>%s</span> member', '<span>%s</span> members', $count, 'um-groups' ), number_format_i18n( $count ) ); ?></li>
					</ul>
				</div>
			<?php } ?>

			<a href="<?php echo esc_url( get_permalink( $group->ID ) ); ?>">
				<?php echo $image; ?>
				<div class="um-group-name"><strong><?php echo esc_html( $group->post_title ); ?></strong></div>
			</a>

			<div class="um-group-meta">
				<ul>
					<li class="privacy">
						<?php echo um_groups_get_privacy_icon( $group->ID ); ?>
						<?php printf( __( '%s Group', 'um-groups' ), um_groups_get_privacy_title( $group->ID ) ); ?>
					</li>
					<li class="description"><?php echo $group->post_content; ?></li>
				</ul>
			</div>
			<div class="um-clear"></div>

		</div>
		<div class="um-clear"></div>

	<?php } ?>

</div>