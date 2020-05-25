<?php if ( ! defined( 'ABSPATH' ) ) exit; ?>


<div class="um-admin-metabox">

	<?php $role = $object['data'];

	$fields = array(
		array(
			'id'        => '_um_profilec',
			'type'      => 'select',
			'label'     => __( 'Enable profile completeness', 'um-profile-completeness' ),
			'tooltip'   => __( 'Turn on / off profile completeness features for this role', 'um-profile-completeness' ),
			'value'     => ! empty( $role['_um_profilec'] ) ? $role['_um_profilec'] : 0,
			'options'   => array(
				0   => __( 'No', 'um-profile-completeness' ),
				1   => __( 'Yes', 'um-profile-completeness' ),
			),
		),
		array(
			'id'            => '_um_profilec_pct',
			'type'          => 'text',
			'label'         => __( 'Percentage (%) required for completion', 'um-profile-completeness' ),
			'tooltip'       => __( 'Consider the profile complete when the user completes (%) by filling profile information.', 'um-profile-completeness' ),
			'value'         => ! empty( $role['_um_profilec_pct'] ) ? $role['_um_profilec_pct'] : 100,
			'conditional'   => array( '_um_profilec', '=', '1' )
		),
		array(
			'id'            => 'profilec-setup',
			'type'          => 'completeness_fields',
			'value'         => ! empty( $role['_um_profilec_pct'] ) ? $role['_um_profilec_pct'] : 100,
			'conditional'   => array( '_um_profilec', '=', '1' )
		),
		array(
			'id'            => '_um_profilec_upgrade_role',
			'type'          => 'select',
			'label'         => __( 'Upgrade to role automatically when profile is 100% complete', 'um-profile-completeness' ),
			'tooltip'       => __( 'Prevent user from browsing site If their profile completion is below the completion threshold set up above?', 'um-profile-completeness' ),
			'value'         => ! empty( $role['_um_profilec_upgrade_role'] ) ? $role['_um_profilec_upgrade_role'] : 0,
			'options'       => UM()->roles()->get_roles( __( 'Do not upgrade', 'um-profile-completeness' ) ),
			'conditional'   => array( '_um_profilec', '=', '1' )
		),
		array(
			'id'            => '_um_profilec_prevent_browse',
			'type'          => 'select',
			'label'         => __( 'Require profile to be complete to browse the site?', 'um-profile-completeness' ),
			'tooltip'       => __( 'Prevent user from browsing site If their profile completion is below the completion threshold set up above?', 'um-profile-completeness' ),
			'value'         => ! empty( $role['_um_profilec_prevent_browse'] ) ? $role['_um_profilec_prevent_browse'] : 0,
			'conditional'   => array( '_um_profilec', '=', '1' ),
			'options'       => array(
				0   => __( 'No', 'um-profile-completeness' ),
				1   => __( 'Yes', 'um-profile-completeness' ),
			),
		),
		array(
			'id'            => '_um_profilec_prevent_browse_exclude_pages',
			'type'          => 'text',
			'label'         => __( 'Allowed pages', 'um-profile-completeness' ),
			'tooltip'       => __( 'Comma separated list of pages (use page ID), that don\'t depends on "Require profile to be complete to browse the site" option.', 'um-profile-completeness' ),
			'value'         => ! empty( $role['_um_profilec_prevent_browse_exclude_pages'] ) ? $role['_um_profilec_prevent_browse_exclude_pages'] : '',
			'conditional'   => array( '_um_profilec_prevent_browse', '=', '1' ),
		),
		array(
			'id'            => '_um_profilec_prevent_browse_redirect',
			'type'          => 'select',
			'label'         => __( 'Redirect no-completed user', 'um-profile-completeness' ),
			'tooltip'       => __( 'If profile isn\'t completed redirect a user to this page', 'um-profile-completeness' ),
			'value'         => ! empty( $role['_um_profilec_prevent_browse_redirect'] ) ? $role['_um_profilec_prevent_browse_redirect'] : '',
			'options'       => array(
				0   => __( 'User Profile', 'um-profile-completeness' ),
				1   => __( 'Custom URL', 'um-profile-completeness' ),
			),
			'conditional'   => array( '_um_profilec_prevent_browse', '=', '1' ),
		),
		array(
			'id'            => '_um_profilec_prevent_browse_redirect_url',
			'type'          => 'text',
			'label'         => __( 'Redirect URL no-completed user', 'um-profile-completeness' ),
			'tooltip'       => __( 'If profile isn\'t completed redirect a user to this custom URL', 'um-profile-completeness' ),
			'value'         => ! empty( $role['_um_profilec_prevent_browse_redirect_url'] ) ? $role['_um_profilec_prevent_browse_redirect_url'] : '',
			'conditional'   => array( '_um_profilec_prevent_browse_redirect', '=', '1' ),
		),
		array(
			'id'		    => '_um_profilec_prevent_profileview',
			'type'		    => 'select',
			'label'		    => __( 'Require profile to be complete to browse user profiles?', 'um-profile-completeness' ),
			'tooltip'	=> __( 'Prevent user from browsing other profiles If their profile completion is below the completion threshold set up above?', 'um-profile-completeness' ),
			'value'		    => ! empty( $role['_um_profilec_prevent_profileview'] ) ? $role['_um_profilec_prevent_profileview'] : 0,
			'conditional'	=> array( '_um_profilec', '=', '1' ),
			'options'		=> array(
				0	=> __( 'No', 'um-profile-completeness' ),
				1	=> __( 'Yes', 'um-profile-completeness' ),
			),
		),
		array(
			'id'		    => '_um_profilec_prevent_comment',
			'type'		    => 'select',
			'label'		    => __( 'Require profile to be complete to leave a comment?', 'um-profile-completeness' ),
			'tooltip'	=> __( 'Prevent user from leaving comments If their profile completion is below the completion threshold set up above?', 'um-profile-completeness' ),
			'value'		    => ! empty( $role['_um_profilec_prevent_comment'] ) ? $role['_um_profilec_prevent_comment'] : 0,
			'conditional'	=> array( '_um_profilec', '=', '1' ),
			'options'		=> array(
				0	=> __( 'No', 'um-profile-completeness' ),
				1	=> __( 'Yes', 'um-profile-completeness' ),
			),
		),
		array(
			'id'		    => '_um_profilec_prevent_bb',
			'type'		    => 'select',
			'label'		    => __( 'Require profile to be complete to create new bbPress topics/replies?', 'um-profile-completeness' ),
			'tooltip'	=> __( 'Prevent user from adding participating in forum If their profile completion is below the completion threshold set up above?', 'um-profile-completeness' ),
			'value'		    => ! empty( $role['_um_profilec_prevent_bb'] ) ? $role['_um_profilec_prevent_bb'] : 0,
			'conditional'	=> array( '_um_profilec', '=', '1' ),
			'options'		=> array(
				0	=> __( 'No', 'um-profile-completeness' ),
				1	=> __( 'Yes', 'um-profile-completeness' ),
			),
		),
	);

	$fields = apply_filters( 'um_profile_completeness_roles_metabox_fields', $fields, $role );

	UM()->admin_forms( array(
		'class'     => 'um-role-profile-completeness um-top-label',
		'prefix_id' => 'role',
		'fields'    => $fields
	) )->render_form(); ?>

	<div class="um-admin-clear"></div>
</div>
