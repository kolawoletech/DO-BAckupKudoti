<?php
/**
 * Template for the UM Profile Completeness, "Complete your profile" popup
 *
 * Called from the UM_Profile_Completeness_API->ajax_edit_popup() method
 *
 * This template can be overridden by copying it to yourtheme/ultimate-member/um-profile-completeness/completeness-popup.php
 */
if ( ! defined( 'ABSPATH' ) ) exit;
?>

<div class="um-completeness-editwrap" data-key="<?php echo esc_attr( $key ); ?>">

	<div class="um-completeness-header">
		<?php _e( 'Complete your profile', 'um-profile-completeness' ); ?>
	</div>

	<div class="um-completeness-complete">
		<?php printf( __( 'Your profile is %s complete', 'um-profile-completeness' ), '<span style="color:#3ba1da"><strong><span class="um-completeness-jx">' . $result['progress'] . '</span>%</strong></span>' ); ?>
	</div>

	<div class="um-completeness-bar-holder">
		<?php echo $result['bar']; ?>
	</div>

	<div class="um-completeness-field">
		<?php echo UM()->fields()->edit_field( $key, $data, false, $args ); ?>
	</div>

	<div class="um-completeness-save">
		<a href="javascript:void(0);" class="save"><?php _e( 'Save', 'um-profile-completeness' ); ?></a>
		<a href="javascript:void(0);" class="skip"><?php _e( 'Do this later', 'um-profile-completeness' ); ?></a>
	</div>

</div>

<div class="um-completeness-popup-overlay"><div class="um-ajax-loading"></div></div>