<?php 

/**
 * API functions for multi rating
 * 
 * @author dpowney
 */
class Bok_Review_API {
	
	public static function display_sponsored_rpcard( $params = array() ) {
		
		$rating_item_ids = isset( $params['rating_item_ids'] ) ? $params['rating_item_ids'] : null;
		$rating_entry_id = isset( $params['rating_item_entry_id'] ) ? esc_sql( $params['rating_item_entry_id'] ) : null;
		$post_id = isset( $params['post_id'] ) ? esc_sql( $params['post_id'] ) : null;

		global $wpdb;
		
		// construct rating items array
		$rating_items = array();
		
		$custom_text_settings = (array) get_option( Bok_Review::CUSTOM_TEXT_SETTINGS );
		$style_settings = (array) get_option( Bok_Review::STYLE_SETTINGS );
		
		$font_awesome_version = $style_settings[Bok_Review::FONT_AWESOME_VERSION_OPTION];
		$icon_classes = Bok_Core::get_icon_classes( $font_awesome_version );
		$use_custom_star_images = $style_settings[Bok_Review::USE_CUSTOM_STAR_IMAGES];
		$image_width = $style_settings[Bok_Review::CUSTOM_STAR_IMAGE_WIDTH];
		$image_height = $style_settings[Bok_Review::CUSTOM_STAR_IMAGE_HEIGHT];
		
		
		extract( wp_parse_args( $params, array(
				'post_id' => null,
				'no_rating_results_text' => $custom_text_settings[Bok_Review::NO_RATING_RESULTS_TEXT_OPTION],
				'show_rich_snippets' => false,
				'show_title' => false,
				'show_count' => true,
				'echo' => true,
				'result_type' => Bok_Review::STAR_RATING_RESULT_TYPE,
				'class' => '',
				'before_count' => '(',
				'after_count' => ')'
		) ) );
		
		if ( is_string( $show_rich_snippets ) ) {
			$show_rich_snippets = $show_rich_snippets == 'true' ? true : false;
		}
		if ( is_string( $show_title ) ) {
			$show_title = $show_title == 'true' ? true : false;
		}
		if ( is_string( $show_count ) ) {
			$show_count = $show_count == 'true' ? true : false;
		}
		if ( is_string( $echo ) ) {
			$echo = $echo == 'true' ? true : false;
		}
		
		// get the post id
		global $post;
		
		if ( ! isset( $post_id ) && isset( $post ) ) {
			$post_id = $post->ID;
		} else if ( ! isset( $post ) && ! isset( $post_id ) ) {
			return; // No post Id available to display rating form
		}
		
		
		// WPML get original post id for default language to get rating results
		$temp_post_id = $post_id;
		if ( function_exists( 'icl_object_id' ) ) {
			global $sitepress;
			$temp_post_id = icl_object_id( $post_id , get_post_type( $post_id ), true, $sitepress->get_default_language() );
		}
		// base query to get the banner data. 
		$rating_items_query = 'SELECT ri.score, ri.overall_notes,ri.reviewcat FROM '. $wpdb->prefix . Bok_Review::TABLE_BOKREVIEWS . ' as ri where ri.post_id = '.$post_id;
		$rating_item_rows = $wpdb->get_results( $rating_items_query );
		foreach ( $rating_item_rows as $rating_item_row ) {
			$rating_result = array(				
					'post_id' => $post_id,
					'adjusted_star_result' => $rating_item_row->score,
					'star_result' => $rating_item_row->score,
					'total_max_option_value' => 5,
					'adjusted_score_result' => $rating_item_row->score,
					'score_result' => null,
					'percentage_result' => 85,
					'adjusted_percentage_result' => 85,
					'count' => 1,
					'post_id' => $post_id,
					'overall_notes' => $rating_item_row->overall_notes,
					'reviewcat' => $rating_item_row->reviewcat);
		}
		$rating_result['post_id'] = $post_id; // set back to adjusted for WPML
		
		
		//Get all topics and within each topic the ratings and the star for each rating. 
		

		ob_start();
		echo "<table>";
		bok_get_template_part( 'bokrating-banner', null, true, array(
				'no_rating_results_text' => $no_rating_results_text,
				'show_rich_snippets' => $show_rich_snippets,
				'show_title' => $show_title,
				'show_date' => false,
				'show_count' => $show_count,
				'no_rating_results_text' => $no_rating_results_text,
				'result_type' => $result_type,
				'class' => $class . ' ratingl-result-' . $post_id,
				'rating_result' => $rating_result,
				'before_count' => $before_count,
				'after_count' => $after_count,
				'post_id' => $post_id,
				'ignore_count' => false,
				'preserve_max_option' => false, 
				'icon_classes' => $icon_classes,
				'use_custom_star_images' => $use_custom_star_images,
				'image_width' => $image_width,
				'image_height' => $image_height,
				'max_stars' => 5,
				'star_result' => $rating_item_rows[0]->score
				) );
		//for loop here to get each individual star data set. and leverage the other sequence. 
		$review_topics_query = 'SELECT ri.name,ri.position FROM '. $wpdb->prefix . Bok_Review::TABLE_BOKREVIEWCATS . ' as ri where ri.reviewcat = \''.$rating_result['reviewcat'].'\'';

		$review_topics_rows = $wpdb->get_results( $review_topics_query );
		//echo count($review_topics_rows);
		foreach ( $review_topics_rows as $review_topics_row ) {
			$review_ratings_query = 'SELECT ri.name,ri.star_level,ri.notes FROM '. $wpdb->prefix . Bok_Review::TABLE_BOKRATINGS . ' as ri where ri.category = \''.$review_topics_row->name.'\'';
			$review_ratings_rows = $wpdb->get_results( $review_ratings_query );			
				echo "<tr><td><b>";
				echo $review_topics_row->name;
				echo "</b></td></tr>";
				
			foreach ( $review_ratings_rows as $review_ratings_row ) {
				$template_part_name = 'star-rating';
				if ( $use_custom_star_images ) {
					$template_part_name = 'custom-star-images';
				}
				echo "<tr><td>";
				echo $review_ratings_row->name;
				echo ":</td><td>";
				echo $review_ratings_row->star_level;
				/*mr_get_template_part( 'bokrating-result', $template_part_name, true, array( 
					'max_stars' => 5, 
					'star_result' => $review_ratings_row->star_level,
					'icon_classes' => $icon_classes,
					'image_height' => $image_height,
					'image_width' => $image_width
				) );*/
				echo "</td></tr>";
				if(!is_null($review_ratings_row->notes)){
					echo "<tr><td>Notes: ";
					echo $review_ratings_row->notes;
					echo "</td></tr>";
				}
			}			
		}
		echo "</table>";
		$html = ob_get_contents();
		ob_end_clean();
		
		$html = apply_filters( 'mr_template_html', $html );
		
		if ( $echo == true ) {
			echo $html;
		}
		return $html;
	}
	
	
	
	
	
	
}