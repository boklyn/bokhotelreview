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
				'classname' => 'sponsoredcard',
				'description' => __('Displays a list of rating results.', 'sponsored-system' )
		);
		$control_ops = array( 'width' => 400, 'height' => 350 );
	
		parent::__construct( $id_base, $name, $widget_opts, $control_ops );	
	}

	function widget( $args, $instance ) {

		// https://codex.wordpress.org/Function_Reference/url_to_postid
		// FIXME may not work with attachments. See here: https://pippinsplugins.com/retrieve-attachment-id-from-image-url/
		$post_id = url_to_postid( Bok_Core::get_current_url() );

		if ( $post_id == 0 || $post_id == null ) {
			return; // Nothing to do.
		}
		
		$i = 0;		
		$post_categories = wp_get_post_categories( $post_id );
		foreach($post_categories as $c){
			$cat = get_category( $c );
			$cats[$i] = $cat->name;
			$i++;
		}
		$key = 99;
		$key = array_search('sponsored', $cats); 
		if ($key !== false) {
			$title = apply_filters( 'widget_title', $instance['title'] );
			extract( $args );
			$srpcard_results_html = Bok_Review_API::display_sponsored_rpcard( array(
				'post_id' => $post_id,
				'echo' => false,
				'show_date' => false,
				'show_rich_snippets' => true,
				'class' => 'rating_form_position mr-filter'
			) );
			echo $before_widget;
				echo $args['before_title'] . $title . $args['after_title'];
				echo __( $srpcard_results_html, 'sponsored-system' );
			echo $after_widget;
		}
		else{
			return;
		}
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
	
	public function update( $new_instance, $old_instance ) {
		$instance = array();
		$instance['title'] = ( ! empty( $new_instance['title'] ) ) ? strip_tags( $new_instance['title'] ) : '';
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