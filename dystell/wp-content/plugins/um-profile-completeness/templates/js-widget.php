<?php
/**
 * Template for the UM Profile Completeness, "Complete your Profile" widget
 *
 * Call: UM()->Profile_Completeness_API()->shortcode()->ultimatemember_profile_completeness()
 * Shortcode: [ultimatemember_profile_completeness]
 *
 * This template can be overridden by copying it to yourtheme/ultimate-member/um-profile-completeness/js-widget.php
 */
if ( ! defined( 'ABSPATH' ) ) exit;
?>

<div class="um-completeness-widget" data-is_profile="<?php echo intval( um_is_core_page( 'user' ) ); ?>" data-user_id="<?php echo esc_attr( get_current_user_id() ); ?>">
	<div class="um-completeness-widget-overlay"><div class="um-ajax-loading"></div></div>
	<div class="um-completeness-widget-wrapper"></div>
</div>

<script type="text/template" id="tmpl-ultimatemember_profile_completeness">
	<div style="font-weight: bold;line-height: 22px;">
		<span>
			<?php _e('Profile:','um-profile-completeness'); ?>
			<span class="um-completeness-progress" style="color: #3BA1DA;">
				<span class="um-completeness-jx" data-user_id="<?php echo esc_attr( get_current_user_id() ); ?>">{{{data.progress}}}</span>%
			</span>
		</span>
	</div>

	<div class="um-completeness-bar-holder">{{{data.bar}}}</div>

	<# if ( typeof( data.fields ) === 'object' && Object.keys( data.fields ).length ) { #>

		<div class="um-completeness-steps">

			<# for( var name in data.fields ) { #>

				<div data-key="{{{name}}}" class="um-completeness-step {{{data.fields[ name ].class}}}<# if ( data.isProfile && ( name === 'profile_photo' || name === 'cover_photo' ) ) { #> is-core<# } #>">
					<span class="um-completeness-bullet">{{{data.fields[ name ].bullet}}}</span>
					<span class="um-completeness-desc">

						<# if ( data.isProfile && ( name === 'profile_photo' || name === 'cover_photo' ) ) { #>
							<strong><a href="{{{data.profileEditURL}}}" data-key="{{{name}}}" class="um-completeness-edit um-real-url">{{{data.fields[ name ].label}}}</a></strong>
						<# } else { #>
							<strong><a href="javascript:void(0);" data-key="{{{name}}}" class="um-completeness-edit">{{{data.fields[ name ].label}}}</a></strong>
						<# } #>

					</span>
					<span class="um-completeness-pct">{{{data.fields[name].pct}}}%</span>
				</div>

			<# } #>

		</div>

	<# } #>

	<div style="padding-top: 15px;text-align: center;">
		<a href="<?php echo esc_url( um_edit_profile_url() ); ?>"><?php _e( 'Complete your profile','um-profile-completeness' ); ?></a>
	</div>
</script>