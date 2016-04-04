<?php 

/**
 * BEGIN:sponsored reprotcard widget
 */
class sponsored_reportcard extends WP_Widget {
	/**
	 * Constructor
	 */
	function __construct( ) {
	
		$id_base = 'sponsored_reportcard';
		$name = __( 'Sponsored Report Card', 'sponsored-system' );
		$widget_opts = array(
				'classname' => 'rating-results-list-widget',
				'description' => __('Displays a list of rating results.', 'sponsored-system' )
		);
		$control_ops = array( 'width' => 400, 'height' => 350 );
	
		parent::__construct( $id_base, $name, $widget_opts, $control_ops );	
	}

	function widget( $args, $instance ) {

		// https://codex.wordpress.org/Function_Reference/url_to_postid
		// FIXME may not work with attachments. See here: https://pippinsplugins.com/retrieve-attachment-id-from-image-url/
		/*$post_id = url_to_postid( MR_Utils::get_current_url() );

		if ( $post_id == 0 || $post_id == null ) {
			return; // Nothing to do.
		}*/
		$title = apply_filters( 'widget_title', $instance['title'] );

		extract( $args );

		echo $before_widget;
			if ( ! empty( $title ) )
			echo $args['before_title'] . $title . $args['after_title'];
			echo __( 'Hello, World!Moving on up', 'sponsored-system' );
		echo $after_widget;
	}
	
	function form( $instance ) {
		if ( isset( $instance[ 'title' ] ) ) {
			$title = $instance[ 'title' ];
		}
		else {
			$title = __( 'New title', 'sponsored-system' );
		}
		// Widget admin form
		?>
		<p>
		<label for="<?php echo $this->get_field_id( 'title' ); ?>"><?php _e( 'Title:' ); ?></label> 
		<input class="widefat" id="<?php echo $this->get_field_id( 'title' ); ?>" name="<?php echo $this->get_field_name( 'title' ); ?>" type="text" value="<?php echo esc_attr( $title ); ?>" />
		</p>
		<?php 	
	}
	
	function update( $new_instance, $old_instance ) {

		$instance = $old_instance;

		return $instance;
	}

}
/**
 * END:sponsored reprotcard widget
 */
 
function register_widgets() {
	register_widget( 'sponsored_reportcard' );
}

add_action( 'widgets_init', 'register_widgets' );
?>