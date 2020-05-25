<?php if ( ! defined( 'ABSPATH' ) ) exit;

global $post; ?>

<div class="um-admin-metabox">
	<?php
	$_um_groups_invites_fields = get_post_meta( $post->ID, '_um_groups_invites_fields', true );

	UM()->admin_forms( array(
		'class'     => 'um-member-directory-search um-half-column',
		'prefix_id' => 'um_metadata',
		'fields'    => array(
			array(
				'id'    => '_um_groups_invites_settings',
				'type'  => 'checkbox',
				'label' => __( 'Enable Invites feature', 'um-groups' ),
				'value' => UM()->query()->get_meta_value( '_um_groups_invites_settings' ),
			),
			array(
				'id'            => '_um_groups_can_invite',
				'type'          => 'select',
				'label'         => __( 'Who can invite members to the group?', 'um-groups' ),
				'options'       => UM()->Groups()->api()->can_invite,
				'value'         => UM()->query()->get_meta_value( '_um_groups_can_invite' ),
				'conditional'   => array( '_um_groups_invites_settings', '=', 1 ),
			),
			array(
				'id'            => '_um_groups_invites_search',
				'type'          => 'checkbox',
				'label'         => __( 'Enable Invites search', 'um-groups' ),
				'value'         => UM()->query()->get_meta_value( '_um_groups_invites_search' ),
				'conditional'   => array( '_um_groups_invites_settings', '=', 1 ),
			),
			array(
				'id'            => '_um_groups_invites_search_fields',
				'type'          => 'checkbox',
				'label'         => __( 'Enable Invites filters', 'um-groups' ),
				'value'         => UM()->query()->get_meta_value( '_um_groups_invites_search_fields' ),
				'conditional'   => array( '_um_groups_invites_settings', '=', 1 ),
			),
			array(
				'id'                    => '_um_groups_invites_fields',
				'type'                  => 'multi_selects',
				'label'                 => __( 'Choose field(s) to enable in search', 'um-groups' ),
				'value'                 => $_um_groups_invites_fields,
				'options'               => UM()->Groups()->invites()->filter_fields,
				'add_text'              => __( 'Add New Custom Field', 'um-groups' ),
				'conditional'           => array( '_um_groups_invites_search_fields', '=', 1 ),
				'show_default_number'   => 3,
				'sorting'               => true,
			),
		)
	) )->render_form(); ?>
</div>