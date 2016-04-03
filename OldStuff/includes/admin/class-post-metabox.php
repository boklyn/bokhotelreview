<?php 
/**
 * Post metabox class
 * 
 * @author dpowney
 */
class MR_Post_Metabox {
	
	/**
	 * Constructor
	 */
	public function __construct() {	
		/*$i = 0;		
		$post_categories = wp_get_post_categories( $post->ID );
		foreach($post_categories as $c){
			$cat = get_category( $c );
			$cats[$i] = $cat->name;
			$i++;
		}
		$key = 99;
		$key = array_search('sponsored', $cats); 
		echo $key;
		if(!empty($key)){*/
			add_action( 'add_meta_boxes', array( $this, 'add_meta_box' ) );			
			add_action( 'save_post', array( $this, 'save_post_meta' ) );
		//}
	
	}

	/**
	 * Adds the meta box container
	 */
	public function add_meta_box( $post_type ) {
		
		$general_settings = (array) get_option( Multi_Rating::GENERAL_SETTINGS );
		$post_types = $general_settings[Multi_Rating::POST_TYPES_OPTION];
		
		if ( ! is_array( $post_types ) && is_string( $post_types ) ) {
			$post_types = array($post_types);
		}
		if ( $post_types != null && in_array( $post_type, $post_types )) {
			add_meta_box( 'mr_meta_box', __('Boklyn Title', 'multi-rating'), array( $this, 'display_meta_box_content' ), $post_type, 'side', 'high');
		}
	}
	
	/**
	 * Save the meta when the post is saved.
	 *
	 * @param int $post_id The ID of the post being saved.
	 */
	public function save_post_meta( $post_id ) {
			
		if ( ! isset( $_POST['meta_box_nonce_action'] ) )
			return $post_id;
	
		if ( ! wp_verify_nonce( $_POST['meta_box_nonce_action'], 'meta_box_nonce' ) )
			return $post_id;
	
		// If this is an autosave, our form has not been submitted, so we don't want to do anything.
		if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE )
			return $post_id;
	
		// Check the user's permissions.
		if ( 'page' == $_POST['post_type'] ) {
			if ( ! current_user_can( 'edit_page', $post_id ) )
				return $post_id;
	
		} else {
			if ( ! current_user_can( 'edit_post', $post_id ) )
				return $post_id;
		}
	
		$rating_form_position = $_POST['rating-form-position'];
		$rating_results_position = $_POST['rating-results-position'];
	
		// Update the meta field.
		update_post_meta( $post_id, Multi_Rating::RATING_FORM_POSITION_POST_META, $rating_form_position );
		update_post_meta( $post_id, Multi_Rating::RATING_RESULTS_POSITION_POST_META, $rating_results_position );
	}
	
	function get_custom_star_images_css() {
	
		$style_settings = (array) get_option( Multi_Rating::STYLE_SETTINGS );
		echo $style_settings[Multi_Rating::CUSTOM_CSS_OPTION];
	
		$image_width = $style_settings[Multi_Rating::CUSTOM_STAR_IMAGE_WIDTH];
		$image_height = $style_settings[Multi_Rating::CUSTOM_STAR_IMAGE_HEIGHT];
	
		?>
		.mr-custom-full-star {
			background: url(<?php echo $style_settings[Multi_Rating::CUSTOM_FULL_STAR_IMAGE]; ?>) no-repeat;
			width: <?php echo $image_width; ?>px;
			height: <?php echo $image_height; ?>px;
			background-size: <?php echo $image_width; ?>px <?php echo $image_height; ?>px;
			image-rendering: -moz-crisp-edges;
			display: inline-block;
		}
		.mr-custom-half-star {
			background: url(<?php echo $style_settings[Multi_Rating::CUSTOM_HALF_STAR_IMAGE]; ?>) no-repeat;
			width: <?php echo $image_width; ?>px;
			height: <?php echo $image_height; ?>px;
			background-size: <?php echo $image_width; ?>px <?php echo $image_height; ?>px;
			image-rendering: -moz-crisp-edges;
			display: inline-block;
		}
		.mr-custom-empty-star {
			background: url(<?php echo $style_settings[Multi_Rating::CUSTOM_EMPTY_STAR_IMAGE]; ?>) no-repeat;
			width: <?php echo $image_width; ?>px;
			height: <?php echo $image_height; ?>px;
			background-size: <?php echo $image_width; ?>px <?php echo $image_height; ?>px;
			image-rendering: -moz-crisp-edges;
			display: inline-block;
		}
		.mr-custom-hover-star {
			background: url(<?php echo $style_settings[Multi_Rating::CUSTOM_HOVER_STAR_IMAGE]; ?>) no-repeat;
			width: <?php echo $image_width; ?>px;
			height: <?php echo $image_height; ?>px;
			background-size: <?php echo $image_width; ?>px <?php echo $image_height; ?>px;
			image-rendering: -moz-crisp-edges;		
			display: inline-block;		
		}
		<?php 
	}
	
	/**
	 * Displays the meta box content
	 *
	 * @param WP_Post $post The post object.
	 */
	public function display_meta_box_content( $post ) {
	
		wp_nonce_field( 'meta_box_nonce', 'meta_box_nonce_action' );
	
		$rating_form_position = get_post_meta( $post->ID, Multi_Rating::RATING_FORM_POSITION_POST_META, true );
		$rating_results_position = get_post_meta( $post->ID, Multi_Rating::RATING_RESULTS_POSITION_POST_META, true );
	
		?>
		<p>
		
		<?php
		
	    $rating_results_html = Multi_Rating_API::display_bokrating_items( array(
				'post_id' => $post_id,
				'echo' => false,
				'show_date' => false,
				'show_rich_snippets' => true,
				'class' => $rating_results_position . ' mr-filter'
		) );
		?>
		<style type="text/css">
			<?php 
			
			$style_settings = (array) get_option( Multi_Rating::STYLE_SETTINGS );
			//echo $style_settings[Multi_Rating::CUSTOM_CSS_OPTION];
			
			$star_rating_colour = $style_settings[Multi_Rating::STAR_RATING_COLOUR_OPTION];
			$star_rating_hover_colour = $style_settings[Multi_Rating::STAR_RATING_HOVER_COLOUR_OPTION];
			$error_message_colour = $style_settings[Multi_Rating::ERROR_MESSAGE_COLOUR_OPTION];
			
			//$this->get_custom_star_images_css();
			?>
			
			.mr-star-hover {
				color: <?php echo $star_rating_hover_colour; ?> !important;
			}
			.mr-star-full, .mr-star-half, .mr-star-empty {
				color: <?php echo $star_rating_colour; ?>;
			}
			.mr-error {
				color: <?php echo $error_message_colour; ?>;
			}
		</style>
		<?php 
		$i = 0;		
		$post_categories = wp_get_post_categories( $post->ID );
		foreach($post_categories as $c){
			$cat = get_category( $c );
			$cats[$i] = $cat->name;
			$i++;
		}
		$key = 99;
		$key = array_search('sponsored', $cats); 
		if($key != 99){
			?>
			<table align>
				<?php
				echo $rating_results_html;
				?>
			</table>
		<?php
		}
		/*
		<p>
			<label for="rating-form-position"><?php _e( 'Rating form position', 'multi-rating' ); ?></label>
			<select class="widefat" name="rating-form-position">
				<option value="<?php echo Multi_Rating::DO_NOT_SHOW; ?>" <?php selected('do_not_show', $rating_form_position, true );?>><?php _e( 'Do not show', 'multi-rating' ); ?></option>
				<option value="" <?php selected('', $rating_form_position, true );?>><?php _e( 'Use default settings', 'multi-rating' ); ?></option>
				<option value="before_content" <?php selected('before_content', $rating_form_position, true );?>><?php _e( 'Before content', 'multi-rating' ); ?></option>
				<option value="after_content" <?php selected('after_content', $rating_form_position, true );?>><?php _e( 'After content', 'multi-rating' ); ?></option>
			</select>
		</p>
		
		<p>
			<label for="rating-results-position"><?php _e( 'Rating result position', 'multi-rating' ); ?></label>
			<select class="widefat" name="rating-results-position">
				<option value="<?php echo Multi_Rating::DO_NOT_SHOW; ?>" <?php selected('do_not_show', $rating_results_position, true );?>><?php _e('Do not show', 'multi-rating' ); ?></option>
				<option value="" <?php selected('', $rating_results_position, true );?>><?php _e( 'Use default settings', 'multi-rating' ); ?></option>
				<option value="before_title" <?php selected('before_title', $rating_results_position, true );?>><?php _e( 'Before title', 'multi-rating' ); ?></option>
				<option value="after_title" <?php selected('after_title', $rating_results_position, true );?>><?php _e( 'After title', 'multi-rating' ); ?></option>
				<option value="before_content" <?php selected('before_content', $rating_results_position, true );?>><?php _e( 'Before content', 'multi-rating' ); ?></option>
				<option value="after_content" <?php selected('after_content', $rating_results_position, true );?>><?php _e( 'After content', 'multi-rating' ); ?></option>
			</select>
		</p>*/
		//<?php
	}
}
?>