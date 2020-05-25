<?php if ( ! defined( 'ABSPATH' ) ) exit;


/**
 * Show a notice on profile
 *
 * @param $args
 */
function um_profile_completeness_show_notice( $args ) {
	wp_enqueue_script( 'um_profile_completeness' );
	wp_enqueue_style( 'um_profile_completeness' );

	if ( ! isset( $_GET['notice'] ) ) {
		return;
	}

	$notice = sanitize_key( $_GET['notice'] );
	switch ( $notice ) {
		case 'incomplete_access':
		case 'incomplete_view':
			$message = __( 'You need to complete your profile before you can view that page.', 'um-profile-completeness' );
			break;
		case 'incomplete_comment':
			$message = __( 'You need to complete your profile before you can leave comments.', 'um-profile-completeness' );
			break;
		case 'incomplete_forum':
			$message = __( 'You need to complete your profile before you can participate in forums.', 'um-profile-completeness' );
			break;
	}

	if ( isset( $message ) ) {
		echo '<p class="um-notice warning" style="margin: 0 0 12px 0 !important;">' . $message . '</p>';
	}
}
add_action( 'um_profile_before_header', 'um_profile_completeness_show_notice' );