<?php if ( ! defined( 'ABSPATH' ) ) exit;


/**
 * Extend core fields
 *
 * @param $fields
 *
 * @return mixed
 */
function um_profile_completeness_add_field( $fields ) {
	$fields['completeness_bar'] = array(
		'title'             => __( 'Profile Completeness', 'um-profile-completeness' ),
		'metakey'           => 'completeness_bar',
		'type'              => 'text',
		'label'             => __( 'Profile Completeness', 'um-profile-completeness' ),
		'required'          => 0,
		'public'            => 1,
		'editable'          => 0,
		'edit_forbidden'    => 1,
		'show_anyway'       => true,
		'custom'            => true,
	);

	return $fields;
}
add_filter( 'um_predefined_fields_hook', 'um_profile_completeness_add_field', 100 );


/**
 * Display the progress bar
 *
 * @param $value
 * @param $data
 *
 * @return string
 */
function um_profile_field_filter_hook__completeness_bar( $value, $data ) {
	if ( UM()->is_ajax() ) {
		return do_shortcode('[ultimatemember_profile_progress_bar user_id="' . um_user('ID') . '" who="admin"]' );
	} else {
		if ( um_is_user_himself() || UM()->roles()->um_user_can('can_edit_everyone') ) {
			wp_enqueue_script( 'um_profile_completeness' );
			wp_enqueue_style( 'um_profile_completeness' );

			return do_shortcode('[ultimatemember_profile_progress_bar user_id="' . um_profile_id() . '" who="admin"]' );
		}
	}

	return $value;
}
add_filter( 'um_profile_field_filter_hook__completeness_bar', 'um_profile_field_filter_hook__completeness_bar', 99, 2 );


/**
 * @param $html
 * @param $field_data
 * @param $form_data
 *
 * @return string
 */
function um_completeness_fields( $html, $field_data, $form_data ) {
	wp_enqueue_script( 'um_profile_completeness' );
	wp_enqueue_style( 'um_profile_completeness' );

	$data = array();
	global $wp_roles;

	if ( ! empty( $_GET['id'] ) ) {
		$id = sanitize_key( $_GET['id'] );
		$data = get_option( "um_role_{$id}_meta" );

		if ( empty( $data['_um_is_custom'] ) ) {
			$data['name'] = $wp_roles->roles[ $id ]['name'];
		}
	}

	if ( ! empty( $_POST['role'] ) ) {
		$data = $_POST['role'];
	}

	ob_start();

	$_um_allocated_progress = 0;
	foreach ( $data as $k => $v ) {
		if ( strstr( $k, '_um_progress_' ) ) {
			$k = sanitize_key( $k ); ?>

			<input type="hidden" id="role<?php echo esc_attr( $k ) ?>" name="role[<?php echo esc_attr( $k ) ?>]" value="<?php echo esc_attr( $v ) ?>" />

			<?php $_um_allocated_progress += $v;
		}
	}

	$remaining_progress = 100 - $_um_allocated_progress; ?>
	<input type="hidden" id="role_um_allocated_progress" name="role[_um_allocated_progress]" value="<?php echo trim( $_um_allocated_progress ); ?>" />

	<div class="profilec-setup">

		<h3><?php _e( 'Setup Fields','um-profile-completeness' ); ?></h3>

		<div>
			<?php _e( 'Remaining progress:', 'um-profile-completeness'); ?>&nbsp;
			<strong><span class="profilec-ajax"><?php echo $remaining_progress; ?></span>%</strong>
		</div>

		<div class="profilec-data">

			<?php foreach ( $data as $k => $v ) {
				if ( strstr( $k, '_um_progress_') ) {
					$k = str_replace( '_um_progress_', '', $k ); ?>
					<p data-key="<?php echo esc_attr( $k ) ?>">
						<span class="profilec-key alignleft"><?php echo $k ?></span>
						<span class="profilec-progress alignright">
							<strong><ins><?php echo $v ?></ins>%</strong>&nbsp;
							<span class='profilec-edit'><i class='um-faicon-pencil'></i></span>
						</span>
					</p>
					<div class="clear"></div>
				<?php }
			} ?>

			<div class="profilec-inline">

				<p><label><?php _e( 'Edit allocated progress (%)', 'um-profile-completeness' ); ?></label>
					<input type="text" name="progress_valuei" id="progress_valuei" value=""/>
					<input type="hidden" name="progress_fieldi" id="progress_fieldi" value=""/></p>

				<p><a href="javascript:void(0);" class="profilec-update button-primary"><?php _e('Update','um-profile-completeness'); ?></a> <a href="javascript:void(0);" class="profilec-remove button"><?php _e('Remove','um-profile-completeness'); ?></a><span class="spinner" style="display:none;"></span></p>

			</div>

		</div>
		<p <?php if ( empty( $remaining_progress ) ) { ?>style="display: none"<?php } ?>>
			<a href="javascript:void(0);" class="profilec-add button"><?php _e( 'Add field', 'um-profile-completeness' ); ?></a>
		</p>
	</div>

	<div class="profilec-field" style="display: none;">
		<?php $fields = UM()->builtin()->all_user_fields( null, true ); ?>

		<p>
			<select name="progress_field" id="progress_field" class="um-forms-field um-long-field" readonly disabled>
				<?php foreach ( $fields as $key => $arr ) { ?>
					<option value="<?php echo esc_attr( $key ) ?>"><?php echo isset( $arr['title'] ) ? $arr['title'] : ''; ?></option>
				<?php } ?>
			</select>
		</p>

		<p>
			<label for="progress_value">
				<?php _e( 'How much (%) this field should attribute to profile completeness?', 'um-profile-completeness') ?>
			</label>
			<input type="text" name="progress_value" id="progress_value" value="" placeholder="<?php esc_attr_e( 'Completeness value (%)', 'um-profile-completeness' ) ?>" class="um-forms-field um-long-field" readonly disabled />
		</p>

		<p>
			<a href="javascript:void(0);" class="profilec-save button-primary"><?php _e( 'Save', 'um-profile-completeness' ) ?></a>
			<a href="javascript:void(0);" class="profilec-cancel button"><?php _e( 'Cancel', 'um-profile-completeness') ?></a>
		</p>
	</div>

	<?php return ob_get_clean();
}
add_filter( 'um_render_field_type_completeness_fields', 'um_completeness_fields', 10, 3 );


/**
 * Rewrite core id's
 *
 * @param $field_id
 * @param $data
 * @param $args
 *
 * @return string
 */
function um_completeness_field_id( $field_id, $data, $args ) {
	if ( ! empty( $args['profile_completeness'] ) ) {
		$field_id = 'um_completeness_widget_' . $field_id;
	}

	return $field_id;
}
add_filter( 'um_completeness_field_id', 'um_completeness_field_id', 0, 3 );


/**
 * Integration between "Ultimate Member - MailChimp" and "Ultimate Member - Profile Completeness"
 * @param  array  $merge_vars
 * @param  int    $user_id
 * @param  string $list_id
 * @param  array  $_um_merge
 * @return array
 */
function um_completeness_mailchimp_single_merge_fields( $merge_vars, $user_id, $list_id, $_um_merge ) {
	if ( in_array( 'completeness_bar', $_um_merge ) ) {
		$key = current( array_keys( $_um_merge, 'completeness_bar' ) );
		$merge_vars[ $key ] = intval( get_user_meta( $user_id, '_completed', true ) );
	}
	return $merge_vars;
}
add_filter( 'um_mailchimp_single_merge_fields', 'um_completeness_mailchimp_single_merge_fields', 10, 4 );