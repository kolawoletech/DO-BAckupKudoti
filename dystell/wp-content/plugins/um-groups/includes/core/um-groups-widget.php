<?php if ( ! defined( 'ABSPATH' ) ) exit;


/**
 * Class UM_Groups_Own_Group
 */
class UM_Groups_Own_Group extends WP_Widget {


	/**
	 * UM_Groups_Own_Group constructor.
	 */
	function __construct() {
		
		parent::__construct(
		
		// Base ID of your widget
		'um_my_groups', 

		// Widget name will appear in UI
		__('Ultimate Member - Groups: Show latest groups', 'um-groups'), 

		// Widget description
		array( 'description' => __( 'Shows latest groups in a widget.', 'um-groups' ), ) 
		);
	
	}


	/**
	 * Creating widget front-end
	 *
	 * @param array $args
	 * @param array $instance
	 */
	public function widget( $args, $instance ) {
		$title = apply_filters( 'widget_title', $instance['title'] );
		$max = $instance['max'];

		if ( ! is_user_logged_in() ) {
			return;
		}

		wp_enqueue_script( 'um_groups' );
		wp_enqueue_style( 'um_groups' );

		// before and after widget arguments are defined by themes
		echo $args['before_widget'];
		if ( ! empty( $title ) ) {
			echo $args['before_title'] . $title . $args['after_title'];
		}
		
		// This is where you run the code and display the output
		echo do_shortcode('[ultimatemember_groups style="avatars" own_groups="false" widget=1 avatar_size="small" groups_per_page="'.$max.'" sort="newest_groups" show_search_form="0" show_total_groups_count="0"  show_actions="0" show_pagination="0" ]');
		
		echo $args['after_widget'];
	}


	/**
	 * Widget Backend
	 *
	 * @param array $instance
	 *
	 * @return string|void
	 */
	public function form( $instance ) {
		if ( isset( $instance[ 'title' ] ) ) {
			$title = $instance[ 'title' ];
		} else {
			$title = __( 'Latest Groups', 'um-groups' );
		}
		
		if ( isset( $instance[ 'max' ] ) ) {
			$max = $instance[ 'max' ];
		} else {
			$max = 11;
		}
		
		// Widget admin form
		?>
		
		<p>
			<label for="<?php echo esc_attr( $this->get_field_id( 'title' ) ); ?>"><?php _e( 'Title:' ); ?></label>
			<input class="widefat" id="<?php echo esc_attr( $this->get_field_id( 'title' ) ); ?>" name="<?php echo esc_attr( $this->get_field_name( 'title' ) ); ?>" type="text" value="<?php echo esc_attr( $title ); ?>" />
		</p>
		
		<p>
			<label for="<?php echo esc_attr( $this->get_field_id( 'max' ) ); ?>"><?php _e( 'Maximum number of groups to display:' ); ?></label>
			<input class="widefat" id="<?php echo esc_attr( $this->get_field_id( 'max' ) ); ?>" name="<?php echo esc_attr( $this->get_field_name( 'max' ) ); ?>" type="text" value="<?php echo esc_attr( $max ); ?>" />
		</p>
		
		<?php 
	}


	/**
	 * Updating widget replacing old instances with new
	 *
	 * @param array $new_instance
	 * @param array $old_instance
	 *
	 * @return array
	 */
	public function update( $new_instance, $old_instance ) {
		$instance = array();
		$instance['title'] = ( ! empty( $new_instance['title'] ) ) ? strip_tags( $new_instance['title'] ) : '';
		$instance['max'] = ( ! empty( $new_instance['max'] ) ) ? strip_tags( $new_instance['max'] ) : 11;
		return $instance;
	}
}