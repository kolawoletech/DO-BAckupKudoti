<?php if ( ! defined( 'ABSPATH' ) ) exit;


/**
 * Class UM_Groups_Admin_Init
 */
class UM_Groups_Admin_Init {


	/**
	 * UM_Groups_Admin_Init constructor.
	 */
	function __construct() {

		/**
		 * Add admin submenu 'Groups'
		 * @deprecated since 2.1.4
		 */
		//add_action('admin_menu',array( $this,'um_groups_admin_menu'), 1000);

		add_action('add_meta_boxes_um_groups', array( $this,'um_groups_add_meta_boxes') );
		add_action('save_post_um_groups', array( $this, 'save_metabox_groups'), 10, 3 );
		add_action('manage_um_groups_posts_custom_column', array( $this, 'manage_um_groups_posts_custom_column'), 10, 3);
	}


	/**
	 * Add admin submenu 'Groups'
	 */
	public function um_groups_admin_menu() {
		add_submenu_page('ultimatemember', 'Groups', 'Groups', 'manage_options', 'edit.php?post_type=um_groups' );
	}


	/**
	 * @param $post
	 */
	public function um_groups_add_meta_boxes( $post ) {
		add_meta_box( 'um_groups_settings_meta_box', __( 'Settings', 'um-groups' ), array($this,'um_groups_build_meta_box'), 'um_groups', 'normal', 'high' );
		add_meta_box( 'um_groups_invites_meta_box', __( 'Invites settings', 'um-groups' ), array($this,'um_groups_build_invites_meta_box'), 'um_groups', 'normal', 'high' );
		remove_meta_box('submitdiv', 'um_groups', 'core'); // $item represents post_type
        add_meta_box('submitdiv', sprintf( isset( $_GET['action'] ) ? __('Update %s'): __('Save %s'), 'Group' ), array($this,'um_groups_cpt_submit_meta_box'), 'um_groups', 'side', 'high'); // $value will be the output title in the box
		add_meta_box( 'um_groups_add_members_meta_box', __( 'Add New Members', 'um-groups' ), array($this,'um_groups_add_members_meta_box'), 'um_groups', 'normal', 'high' );
		add_meta_box( 'um_groups_manage_members_meta_box', __( 'Manage Members', 'um-groups' ), array($this,'um_groups_manage_members_meta_box'), 'um_groups', 'normal', 'high' );
	}


	/**
	 *
	 */
	public function um_groups_add_members_meta_box() {
		$metabox = UM()->Admin_Metabox();
		include_once um_groups_path.'admin/templates/add-members.php';
	}


	/**
	 *
	 */
	public function um_groups_build_meta_box() {
		$metabox = UM()->Admin_Metabox();
		include_once um_groups_path.'admin/templates/groups.php';
	}

	/**
	 *
	 */
	public function um_groups_build_invites_meta_box() {
		$metabox = UM()->Admin_Metabox();
		include_once um_groups_path.'admin/templates/invites.php';
	}

	/**
	 *
	 */
	public function um_groups_manage_members_meta_box() {
		$metabox = UM()->Admin_Metabox();
		include_once um_groups_path.'admin/templates/manage-members.php';
	}


	/**
	 * @param $post_id
	 * @param $post
	 * @param $update
	 */
	public function save_metabox_groups( $post_id, $post, $update ) {
		global $wpdb, $post;

		// validate nonce
		$nonce = filter_input( INPUT_POST, 'um_admin_save_metabox_groups_nonce', FILTER_SANITIZE_STRING );
		if ( !$nonce || ! wp_verify_nonce( $nonce, 'groups.php') ) {
			return;
		}

		// validate user
		$post_type = get_post_type_object( $post->post_type );
		if ( ! current_user_can( $post_type->cap->edit_post, $post_id ) ) {
			return;
		}

		if ( empty( $_POST['post_title'] ) ) {
			$_POST['post_title'] = $post_title = __( 'Group #', 'um-groups' ) . $post_id;

			$arr_post_update = array(
				'post_title' => $post_title,
				'post_name'  => sanitize_title( $post_title )
			);
			$wpdb->update( $wpdb->posts, $arr_post_update, array( 'ID' => $post_id ) );
		}

		// save
		do_action('um_admin_before_saving_group_meta', $post_id );
		do_action('um_admin_before_save_group', $post_id, $post );

		foreach ( $_POST as $k => $v ) {
			if ( strstr( $k, '_um_groups' ) ) {
				update_post_meta( $post_id, $k, $v );
			}
			elseif( is_array( $v ) ){
				foreach ( $v as $k => $v ){
					if ( strstr( $k, '_um_groups' ) ) {
						update_post_meta( $post_id, $k, $v );
					}
				}
			}
		}

		do_action( 'um_admin_after_editing_group', $post_id, $post );
		do_action( 'um_admin_after_save_group', $post_id, $post );
		do_action( 'um_groups_after_backend_insert', $post, $post_id, $update );
	}


	/**
	 * @param $column_name
	 * @param $id
	 */
	public function manage_um_groups_posts_custom_column( $column_name, $id ) {
		switch ( $column_name ) {
			case 'members':
				echo UM()->Groups()->api()->count_members( $id, true );
				break;
			case 'privacy':
				$privacy_slug = UM()->Query()->get_meta_value('_um_groups_privacy', null, 'public');
				echo UM()->Groups()->api()->get_privacy_icon( $privacy_slug );
				echo UM()->Groups()->api()->get_privacy_title( $privacy_slug );
				break;
		}
	}


	/**
	 *
	 */
	public function um_groups_cpt_submit_meta_box() {
		global $post;

		$post_type = $post->post_type; // get current post_type
		$post_type_object = get_post_type_object( $post_type );
		$can_publish = current_user_can( $post_type_object->cap->publish_posts ); ?>

		<div class="submitbox" id="submitpost">
			<div id="major-publishing-actions">
				<?php do_action( 'post_submitbox_start' ); ?>
				<div id="delete-action">
					<?php if ( current_user_can( "delete_post", $post->ID ) ) {
						if ( ! EMPTY_TRASH_DAYS ) {
							$delete_text = __( 'Delete Group Permanently', 'um-groups' );
						} else {
							$delete_text = __( 'Delete Group', 'um-groups' );
						} ?>
						<a class="submitdelete deletion" href="<?php echo esc_url( get_delete_post_link( $post->ID ) ); ?>"> <?php echo esc_html( $delete_text ); ?> </a>
					<?php } ?>
				</div>
				<div id="publishing-action">
					<span class="spinner"></span>
					<?php if ( ! in_array( $post->post_status, array('publish', 'future', 'private') ) || 0 == $post->ID ) {
						if ( $can_publish ) { ?>
							<input name="original_publish" type="hidden" id="original_publish" value="<?php esc_attr_e( 'Add Tab', 'um-groups' ) ?>" />
							<?php submit_button( __( 'Add Group' ), 'primary button-large', 'publish', false, array( 'accesskey' => 'p' ) ); ?>
						<?php }
					} else { ?>
						<input name="original_publish" type="hidden" id="original_publish" value="<?php esc_attr_e('Update Group', 'um-groups' ); ?>" />
						<input name="save" type="submit" class="button button-primary button-large" id="publish" accesskey="p" value="<?php esc_attr_e('Update Group', 'um-groups'); ?>" />
					<?php } ?>
				</div>
				<div class="clear"></div>
			</div>
		</div>
		<?php
	}

}

new UM_Groups_Admin_Init();