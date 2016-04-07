<?php
echo "
			<tr>
				<td  style=\"text-align:center;vertical-align:middle;\">
				<div class=\"bokreview-total-wrapper\">
				<span class=\"bokreview-total-box\">";
				echo $rating_result['star_result'];
				echo"</span>
				</div>
				</td>
				<td>";
				$template_part_name = 'star-rating';
			if ( $use_custom_star_images ) {
				$template_part_name = 'custom-star-images';
			}
		
			bok_get_template_part( 'bokrating-result', $template_part_name, true, array( 
				'max_stars' => $max_stars, 
				'star_result' => $star_result,
				'icon_classes' => $icon_classes,
				'image_height' => $image_height,
				'image_width' => $image_width
			) );
			echo "
				</td>
			</tr>
			";
?>