<?php if ( ! defined( 'ABSPATH' ) ) exit;


/**
 * Class um_profile_completeness
 */
class um_profile_completeness extends WP_Widget {

	function __construct() {

		parent::__construct(

			// Base ID of your widget
			'um_profile_completeness',

			// Widget name will appear in UI
			__( 'Ultimate Member - Complete your Profile', 'um-profile-completeness' ),

			// Widget description
			array( 'description' => __( 'Display the user profile completeness widget.', 'um-profile-completeness' ), )
		);

	}


	/**
	 * Creating widget front-end
	 *
	 * @param array $args
	 * @param array $instance
	 */
	public function widget( $args, $instance ) {
		$title = apply_filters( 'widget_title', isset( $instance['title'] ) ? $instance['title']: '' );

		if ( ! is_user_logged_in() ) {
			return;
		}

		$result = UM()->Profile_Completeness_API()->shortcode()->profile_progress( um_profile_id() );
		if ( ! $result || $result['progress'] >= 100 ) {
			return;
		}

		wp_enqueue_script( 'um_profile_completeness' );
		wp_enqueue_style( 'um_profile_completeness' );

		// before and after widget arguments are defined by themes
		echo $args['before_widget'];
		if ( ! empty( $title ) ) {
			echo $args['before_title'] . $title . $args['after_title'];
		}

		// This is where you run the code and display the output
		echo do_shortcode('[ultimatemember_profile_completeness]');

		echo $args['after_widget'];
	}


	/**
	 * Widget Backend
	 *
	 * @param array $instance
	 */
	public function form( $instance ) {
		if ( isset( $instance[ 'title' ] ) ) {
			$title = $instance[ 'title' ];
		} else {
			$title = __( 'Complete your Profile', 'um-profile-completeness' );
		}

		// Widget admin form
		?>

		<p>
			<label for="<?php echo esc_attr( $this->get_field_id( 'title' ) ); ?>"><?php _e( 'Title:', 'um-profile-completeness' ); ?></label>
			<input class="widefat" id="<?php echo esc_attr( $this->get_field_id( 'title' ) ); ?>" name="<?php echo esc_attr( $this->get_field_name( 'title' ) ); ?>" type="text" value="<?php echo esc_attr( $title ); ?>" />
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

		return $instance;
	}

}


/**
 * Class um_profile_progress_bar
 */
class um_profile_progress_bar extends WP_Widget {

	function __construct() {

		parent::__construct(

			// Base ID of your widget
			'um_profile_progress_bar',

			// Widget name will appear in UI
			__( 'Ultimate Member - Profile Progress Bar', 'um-profile-completeness' ),

			// Widget description
			array( 'description' => __( 'Display the user profile progress bar.', 'um-profile-completeness' ), )
		);

	}


	// Creating widget front-end
	public function widget( $args, $instance ) {
		$title = apply_filters( 'widget_title', $instance['title'] );

		if ( ! is_user_logged_in() ) {
			return;
		}
		$result = UM()->Profile_Completeness_API()->shortcode()->profile_progress( um_profile_id() );
		if ( ! $result || $result['progress'] >= 100 ) {
			return;
		}

		wp_enqueue_script( 'um_profile_completeness' );
		wp_enqueue_style( 'um_profile_completeness' );

		// before and after widget arguments are defined by themes
		echo $args['before_widget'];
		if ( ! empty( $title ) ) {
			echo $args['before_title'] . $title . $args['after_title'];
		}

		$shortcode_attrs = apply_filters( 'um_profile_completeness_progress_bar_args', 'user_id="' . get_current_user_id() . '"' );

		// This is where you run the code and display the output
		echo do_shortcode('[ultimatemember_profile_progress_bar ' . $shortcode_attrs . '/]');

		echo $args['after_widget'];
	}


	// Widget Backend
	public function form( $instance ) {
		if ( isset( $instance[ 'title' ] ) ) {
			$title = $instance[ 'title' ];
		} else {
			$title = __( 'Profile Progress', 'um-profile-completeness' );
		}

		// Widget admin form
		?>

		<p>
			<label for="<?php echo esc_attr( $this->get_field_id( 'title' ) ); ?>"><?php _e( 'Title:', 'um-profile-completeness' ); ?></label>
			<input class="widefat" id="<?php echo esc_attr( $this->get_field_id( 'title' ) ); ?>" name="<?php echo esc_attr( $this->get_field_name( 'title' ) ); ?>" type="text" value="<?php echo esc_attr( $title ); ?>" />
		</p>

		<?php
	}


	// Updating widget replacing old instances with new
	public function update( $new_instance, $old_instance ) {
		$instance = array();
		$instance['title'] = ( ! empty( $new_instance['title'] ) ) ? strip_tags( $new_instance['title'] ) : '';

		return $instance;
	}

}