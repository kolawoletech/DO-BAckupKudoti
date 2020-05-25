<?php
if (!class_exists('WPFA_Change_Texts')) {

	class WPFA_Change_Texts {

		static private $instance = false;
		var $current_page_edits = false;

		private function __construct() {
			
		}

		function init() {

			if (is_admin()) {
				add_action('wp_frontend_admin/quick_settings/after_save', array($this, 'save_meta_box'), 10, 2);
				add_action('admin_init', array($this, 'start_buffer_for_replacement'), 1);
				add_action('shutdown', array($this, 'buffer_end'));
			} else {
				add_action('wp_frontend_admin/quick_settings/after_fields', array($this, 'render_meta_box'));
			}
		}

		function get_text_edits_for_current_page() {
			global $wpdb;

			$vgfa = VG_Admin_To_Frontend_Obj();
			$url_path = $vgfa->prepare_loose_url($vgfa->get_current_url());
			$all_text_edits = $wpdb->get_col("SELECT meta_value FROM $wpdb->postmeta pm LEFT JOIN $wpdb->posts p ON p.ID = pm.post_id WHERE p.post_status = 'publish' AND pm.meta_key = 'vgfa_text_changes' AND pm.meta_value LIKE '%" . esc_sql($url_path) . "%' ");
			$out = array();

			if (empty($all_text_edits)) {
				return $out;
			}

			foreach ($all_text_edits as $raw_text_edits) {
				$text_edits = json_decode($raw_text_edits, true);

				if (!is_array($text_edits)) {
					continue;
				}

				foreach ($text_edits as $url => $edits) {
					if (empty($url) || empty($url_path) || strpos($url, $url_path) === false) {
						continue;
					}

					$out = array_merge($out, $edits);
				}
			}

			return $out;
		}

		function start_buffer_for_replacement() {
			// We apply the replacement for all users, including the master user
			// to be able to see the preview when editing


			$text_edits = $this->get_text_edits_for_current_page();
			if (empty($text_edits)) {
				return;
			}

			$this->current_page_edits = $text_edits;
			ob_start(array($this, 'replace'));
		}

		function replace($buffer) {
			if (empty($this->current_page_edits)) {
				return $buffer;
			}

			foreach ($this->current_page_edits as $search => $replace) {
				if (empty($search) || strlen($search) < 4) {
					continue;
				}
				$buffer = str_ireplace($search, $replace, $buffer);
			}

			return $buffer;
		}

		function buffer_end() {
			if (!$this->current_page_edits) {
				return;
			}
			if (!ob_get_contents()) {
				return;
			}
			ob_end_flush();
		}

		/**
		 * Meta box display callback.
		 *
		 * @param WP_Post $post Current post object.
		 */
		function render_meta_box($post) {
			?>
			<div class="field">
				<button class="edit-text-trigger"><?php _e('Edit texts', VG_Admin_To_Frontend::$textname); ?></button>
				<button class="stop-edit-text-trigger"><?php _e('Stop editing texts', VG_Admin_To_Frontend::$textname); ?></button><br/>
				<a class="revert-all-text-edits-trigger" href="#"><?php _e('Revert text changes', VG_Admin_To_Frontend::$textname); ?></a>		
				<input type="hidden" class="text-changes-input" name="vgfa_text_changes" value="<?php echo esc_attr(get_post_meta(get_the_ID(), 'vgfa_text_changes', true)); ?>">
			</div>
			<hr>
			<?php
		}

		function save_meta_box($post_id, $post) {

			if (!isset($_REQUEST['vgfa_text_changes'])) {
				return;
			}
			$changes = wp_unslash(sanitize_text_field($_REQUEST['vgfa_text_changes']));
			if (!empty($changes)) {
				$existing_changes = get_post_meta($post_id, 'vgfa_text_changes', true);
				if (!empty($existing_changes) && is_string($existing_changes)) {
					$existing_changes = json_decode($existing_changes, true);
				}
				$vgfa = VG_Admin_To_Frontend_Obj();
				$prepared_changes = json_decode($changes, true);
				foreach ($prepared_changes as $url => $change) {
					$url_path = $vgfa->prepare_loose_url($url);
					if (!isset($prepared_changes[$url_path])) {
						$prepared_changes[$url_path] = array();
					}
					$existing_changes_for_path = isset($existing_changes[$url_path]) ? $existing_changes[$url_path] : array();
					$prepared_changes[$url_path] = array_merge($existing_changes_for_path, $prepared_changes[$url_path], $change);
					if ($url !== $url_path) {
						unset($prepared_changes[$url]);
					}
				}
				// Add the existing text changes, that apply to pages that weren't edited in this session
				foreach ($existing_changes as $url => $change) {
					if (!isset($prepared_changes[$url])) {
						$prepared_changes[$url] = $change;
					}
				}
				foreach ($prepared_changes as $url => $changes) {
					foreach ($changes as $search => $replace) {
						if (empty($search) || strlen($search) < 4) {
							unset($prepared_changes[$url][$search]);
						}
					}
				}
				$changes = json_encode($prepared_changes);
			}
			update_post_meta($post_id, 'vgfa_text_changes', $changes);
		}

		/**
		 * Creates or returns an instance of this class.
		 */
		static function get_instance() {
			if (null == WPFA_Change_Texts::$instance) {
				WPFA_Change_Texts::$instance = new WPFA_Change_Texts();
				WPFA_Change_Texts::$instance->init();
			}
			return WPFA_Change_Texts::$instance;
		}

		function __set($name, $value) {
			$this->$name = $value;
		}

		function __get($name) {
			return $this->$name;
		}

	}

}

if (!function_exists('WPFA_Change_Texts_Obj')) {

	function WPFA_Change_Texts_Obj() {
		return WPFA_Change_Texts::get_instance();
	}

}
WPFA_Change_Texts_Obj();
