<?php
/*
Plugin Name: Hotel Review Widget for AudraCherryPickings.com
Description: Hotel Review Widgetc code changes for AudraCherryPickings.com
Plugin URI: http://boklyn.net/
Version: 1.0
Author: boklyn wong
Author URI: http://boklyn.net/
*/
/* Start Adding Functions Below this Line */
// Creating the widget 
class Multi_Rating {
	
	/** Singleton *************************************************************/
	
	/**
	 * @var Multi_Rating The one true Multi_Rating
	 */
	private static $instance;
	
	/**
	 * Settings instance variable
	 */
	public $settings = null;
	
	/**
	 * Post metabox instance variable
	 */
	public $post_metabox = null;

	/**
	 * Constants
	 */
	const
	VERSION = '4.1.11',
	ID = 'multi-rating',

	// tables
	TABLE_BOKRATINGS							= 'bok_ratings',
	TABLE_BOKREVIEWS							= 'bok_reviews',
	TABLE_BOKREVIEWCATS							= 'bok_reviewcats',
	// settings
	
	/**
	 *
	 * @return Multi_Rating
	 */
	public static function instance() {
	
		if ( ! isset( self::$instance )
				&& ! ( self::$instance instanceof Multi_Rating ) ) {
				
			self::$instance = new Multi_Rating;
	
			//add_action( 'admin_enqueue_scripts', array( self::$instance, 'assets' ) );
				
			if ( is_admin() && ( ! defined( 'DOING_AJAX' ) || ! DOING_AJAX ) ) {
	
				//add_action( 'admin_menu', array(self::$instance, 'add_admin_menus') );
				//add_action( 'admin_enqueue_scripts', array( self::$instance, 'admin_assets' ) );
				//add_action( 'admin_init', array( self::$instance, 'redirect_about_page' ) );
	
			} else {
				//add_action( 'wp_enqueue_scripts', array( self::$instance, 'assets' ) );
			}
				
			//add_action( 'wp_head', array( self::$instance, 'add_custom_css') );
			//add_action( 'init', array( self::$instance, 'load_textdomain' ) );
				
			self::$instance->includes();
			//self::$instance->settings = new MR_Settings();
	
			if ( is_admin() && ( ! defined( 'DOING_AJAX' ) || ! DOING_AJAX ) ) {
				
				self::$instance->post_metabox = new MR_Post_Metabox();
				add_filter( 'hidden_meta_boxes', array( self::$instance, 'default_hidden_meta_boxes' ), 10, 2);
				
				add_action( 'delete_user', array( self::$instance, 'delete_user' ), 11, 2 );
				add_action( 'deleted_post', array( self::$instance, 'deleted_post' ) );
			}
				
			self::$instance->add_ajax_callbacks();
		}
	
		return Multi_Rating::$instance;
	}
	
	/**
	 * Delete all associated ratings by user id
	 *
	 * @param $user_id
	 * @param $reassign user id
	 */
	public function delete_user( $user_id, $reassign ) {
	
		global $wpdb;
	
		if ( $reassign == null ) { 
			// do nothing now has an invalid user id associated to it - oh well... decided not to delete the 
			// rating as the user id is not displayed or used
		} else { // reassign ratings to a user
			$wpdb->update( $wpdb->prefix . Multi_Rating::RATING_ITEM_ENTRY_TBL_NAME, array( 'user_id' => $reassign ), array( 'user_id' => $user_id ), array( '%d' ), array( '%d' ) );
		}
	}
	
	/**
	 * Checks whether the Multi Rating post meta box needs to be hidden by default
	 *
	 * @param unknown $hidden
	 * @param unknown $screen
	 * @return unknown
	 */
	public function default_hidden_meta_boxes( $hidden, $screen ) {
	
		$post_type = $screen->post_type;
	
		$general_settings = (array) get_option( Multi_Rating::GENERAL_SETTINGS );
		$post_types = $general_settings[Multi_Rating::POST_TYPES_OPTION];
		
		if ( ! is_array( $post_types ) && is_string( $post_types ) ) {
			$post_types = array( $post_types );
		}
		
		if ( $post_types != null && in_array( $post_type, $post_types ) ) {
		
			// check option if we're hiding by default
			if ( $general_settings[Multi_Rating::DEFAULT_HIDE_POST_META_BOX_OPTION] ) {
				if ( ! isset( $hidden['mr_meta_box'] ) ) {
					array_push( $hidden, 'mr_meta_box' );
				}
			}
		}
	
		return $hidden;
	}
	
	/**
	 * Delete all associated ratings by post id
	 *
	 * @param $post_id
	 */
	public function deleted_post( $post_id ) {
	
		global $wpdb;
	
		$entry_query = 'SELECT rating_item_entry_id AS rating_entry_id '
				. 'FROM ' . $wpdb->prefix . Multi_Rating::RATING_ITEM_ENTRY_TBL_NAME
				. ' WHERE post_id = "' . $post_id . '"';
		$entries = $wpdb->get_results( $entry_query );
	
		$this->delete_entries( $entries );
	}
	
	/**
	 * Deletes entries from database including rating item values
	 * 
	 * @param $entries
	 */
	public function delete_entries( $entries ) {
		
		global $wpdb;
		
		foreach ( $entries as $entry_row ) {
			$rating_entry_id = $entry_row->rating_entry_id;
		
			$delete_entry_query = 'DELETE FROM ' . $wpdb->prefix . Multi_Rating::RATING_ITEM_ENTRY_TBL_NAME . ' WHERE rating_item_entry_id = "' . $rating_entry_id . '"';
			$results = $wpdb->query( $delete_entry_query );
		
			$delete_entry_values_query = 'DELETE FROM ' . $wpdb->prefix . Multi_Rating::RATING_ITEM_ENTRY_VALUE_TBL_NAME . ' WHERE rating_item_entry_id = "' . $rating_entry_id . '"';
			$results = $wpdb->query( $delete_entry_values_query );
		}
	}

	/**
	 * Includes files
	 */
	function includes() {
	
		require dirname( __FILE__ ) . DIRECTORY_SEPARATOR . 'includes' . DIRECTORY_SEPARATOR . 'shortcodes.php';
		require dirname( __FILE__ ) . DIRECTORY_SEPARATOR . 'includes' . DIRECTORY_SEPARATOR . 'widgets.php';
		require dirname( __FILE__ ) . DIRECTORY_SEPARATOR . 'includes' . DIRECTORY_SEPARATOR . 'class-utils.php';
		require dirname( __FILE__ ) . DIRECTORY_SEPARATOR . 'includes' . DIRECTORY_SEPARATOR . 'class-api.php';
		require dirname( __FILE__ ) . DIRECTORY_SEPARATOR . 'includes' . DIRECTORY_SEPARATOR . 'class-rating-form.php';
		require dirname( __FILE__ ) . DIRECTORY_SEPARATOR . 'includes' . DIRECTORY_SEPARATOR . 'auto-placement.php';
		require dirname( __FILE__ ) . DIRECTORY_SEPARATOR . 'includes' . DIRECTORY_SEPARATOR . 'misc-functions.php';
		require dirname( __FILE__ ) . DIRECTORY_SEPARATOR . 'includes' . DIRECTORY_SEPARATOR . 'class-settings.php';
		require dirname( __FILE__ ) . DIRECTORY_SEPARATOR . 'includes' . DIRECTORY_SEPARATOR . 'actions.php';
		require dirname( __FILE__ ) . DIRECTORY_SEPARATOR . 'includes' . DIRECTORY_SEPARATOR . 'legacy.php';
		require dirname( __FILE__ ) . DIRECTORY_SEPARATOR . 'includes' . DIRECTORY_SEPARATOR . 'template-functions.php';
	
		if ( is_admin() ) {
			
			require dirname( __FILE__ ) . DIRECTORY_SEPARATOR . 'includes' . DIRECTORY_SEPARATOR . 'admin' . DIRECTORY_SEPARATOR . 'class-rating-item-table.php';
			require dirname( __FILE__ ) . DIRECTORY_SEPARATOR . 'includes' . DIRECTORY_SEPARATOR . 'admin' . DIRECTORY_SEPARATOR . 'class-rating-entry-table.php';
			require dirname( __FILE__ ) . DIRECTORY_SEPARATOR . 'includes' . DIRECTORY_SEPARATOR . 'admin' . DIRECTORY_SEPARATOR . 'class-rating-results-table.php';
			require dirname( __FILE__ ) . DIRECTORY_SEPARATOR . 'includes' . DIRECTORY_SEPARATOR . 'admin' . DIRECTORY_SEPARATOR . 'class-post-metabox.php';
			require dirname( __FILE__ ) . DIRECTORY_SEPARATOR . 'includes' . DIRECTORY_SEPARATOR . 'admin' . DIRECTORY_SEPARATOR . 'about.php';
			require dirname( __FILE__ ) . DIRECTORY_SEPARATOR . 'includes' . DIRECTORY_SEPARATOR . 'admin' . DIRECTORY_SEPARATOR . 'rating-items.php';
			require dirname( __FILE__ ) . DIRECTORY_SEPARATOR . 'includes' . DIRECTORY_SEPARATOR . 'admin' . DIRECTORY_SEPARATOR . 'rating-results.php';
			require dirname( __FILE__ ) . DIRECTORY_SEPARATOR . 'includes' . DIRECTORY_SEPARATOR . 'admin' . DIRECTORY_SEPARATOR . 'reports.php';
			require dirname( __FILE__ ) . DIRECTORY_SEPARATOR . 'includes' . DIRECTORY_SEPARATOR . 'admin' . DIRECTORY_SEPARATOR . 'settings.php';
			require dirname( __FILE__ ) . DIRECTORY_SEPARATOR . 'includes' . DIRECTORY_SEPARATOR . 'admin' . DIRECTORY_SEPARATOR . 'tools.php';
			require dirname( __FILE__ ) . DIRECTORY_SEPARATOR . 'includes' . DIRECTORY_SEPARATOR . 'admin' . DIRECTORY_SEPARATOR . 'edit-rating.php';
		}
	}
	
	/**
	 * Activates the plugin
	 */
	public static function activate_plugin() {
		
		global $wpdb;	
		require_once( ABSPATH . 'wp-admin/includes/upgrade.php' );
		//bokrating create database
		$sql_create_rating_subject_tbl = 'CREATE TABLE ' . $wpdb->prefix . Multi_Rating::TABLE_BOKRATINGS . ' (
				id int(11) NOT NULL,
				bokreviewid int(11) DEFAULT NULL,
				name varchar(45) DEFAULT NULL,
				star_level decimal(9,1) DEFAULT NULL,
				notes varchar(300) DEFAULT NULL,
				category varchar(45) DEFAULT NULL,
		) ENGINE=InnoDB AUTO_INCREMENT=1;';
		dbDelta( $sql_create_rating_subject_tbl );
		
		$sql_create_rating_subject_tbl = 'CREATE TABLE ' . $wpdb->prefix . Multi_Rating::TABLE_BOKREVIEWCATS . ' (
			  id int(11) NOT NULL,
			  name varchar(45) DEFAULT NULL,
			  reviewcat varchar(45) DEFAULT NULL,
			  position int(11) DEFAULT NULL,
			  PRIMARY KEY (id)
		) ENGINE=InnoDB AUTO_INCREMENT=1;';
		dbDelta( $sql_create_rating_subject_tbl );
		
		$sql_create_rating_subject_tbl = 'CREATE TABLE ' . $wpdb->prefix . Multi_Rating::TABLE_BOKREVIEWS . ' (
			  bokreviewid int(11) NOT NULL,
			  score decimal(3,1) DEFAULT NULL,
			  post_id bigint(20) unsigned NOT NULL,
			  overall_notes varchar(200) DEFAULT NULL,
			  reviewcat varchar(45) DEFAULT NULL,
			  PRIMARY KEY (bokreviewid),
			  KEY post_id (post_id)
		) ENGINE=InnoDB AUTO_INCREMENT=1;';
		dbDelta( $sql_create_rating_subject_tbl );
			
				
		// Adds mr_edit_ratings capability to allow Editor role to be able to edit ratings
		/*$editor_role = get_role( 'editor' );
		$administrator_role = get_role( 'administrator' );
		
		$editor_role->add_cap( 'mr_edit_ratings' );
		$administrator_role->add_cap( 'mr_edit_ratings' );
		
		 if no rating items exist, add a sample one :)
		try {
			
			$count = $wpdb->get_var( 'SELECT COUNT(rating_item_id) FROM ' . $wpdb->prefix 
					. Multi_Rating::RATING_ITEM_TBL_NAME );
			
			if ( is_numeric( $count ) && $count == 0 ) {
				$results = $wpdb->insert(  $wpdb->prefix . Multi_Rating::RATING_ITEM_TBL_NAME, array(
						'description' => __( 'Sample rating item', 'multi-rating' ),
						'max_option_value' => 5,
						'default_option_value' => 5,
						'weight' => 1,
						'type' => 'star_rating',
						'required' => true
				) );
			}
			
		} catch ( Exception $e ) {
			// do nothing
		}*/
		
	}
	
	/**
	 * Uninstalls the plugin
	 */
	public static function uninstall_plugin() {
		
		delete_option( Multi_Rating::GENERAL_SETTINGS );
		delete_option( Multi_Rating::CUSTOM_TEXT_SETTINGS );
		delete_option( Multi_Rating::POSITION_SETTINGS );
		delete_option( Multi_Rating::STYLE_SETTINGS );
		
		// Drop tables
		global $wpdb;

		$wpdb->query( 'DROP TABLE IF EXISTS ' . $wpdb->prefix . Multi_Rating::TABLE_BOKRATINGS );
		$wpdb->query( 'DROP TABLE IF EXISTS ' . $wpdb->prefix . Multi_Rating::TABLE_BOKREVIEWS );
		$wpdb->query( 'DROP TABLE IF EXISTS ' . $wpdb->prefix . Multi_Rating::TABLE_BOKREVIEWCATS );
	}
	
	/**
	 * Redirects to about page on activation
	 */
	function redirect_about_page() {
		if ( get_option( MULTI_RATING::DO_ACTIVATION_REDIRECT_OPTION, false ) ) {
			delete_option( MULTI_RATING::DO_ACTIVATION_REDIRECT_OPTION );
			wp_redirect( 'admin.php?page=' . MULTI_RATING::ABOUT_PAGE_SLUG );
		}
	}
	
	/**
	 * Loads plugin text domain
	 */
	public function load_textdomain() {
		load_plugin_textdomain( 'multi-rating', false, dirname( plugin_basename( __FILE__) ) . DIRECTORY_SEPARATOR . 'languages' . DIRECTORY_SEPARATOR );
	}
	
	/**
	 * Adds admin menus
	 */
	public function add_admin_menus() {
		
		add_menu_page( __( 'Multi Rating', 'multi-rating' ), __( 'Multi Rating', 'multi-rating' ), 'mr_edit_ratings', Multi_Rating::RATING_RESULTS_PAGE_SLUG, 'mr_rating_results_screen', 'dashicons-star-filled', null );
		add_submenu_page( Multi_Rating::RATING_RESULTS_PAGE_SLUG, '', '', 'mr_edit_ratings', Multi_Rating::RATING_RESULTS_PAGE_SLUG, 'mr_rating_results_screen' );
		add_submenu_page( Multi_Rating::RATING_RESULTS_PAGE_SLUG, __( 'Rating Results', 'multi-rating' ), __( 'Rating Results', 'multi-rating' ), 'mr_edit_ratings', Multi_Rating::RATING_RESULTS_PAGE_SLUG, 'mr_rating_results_screen' );
		add_submenu_page( Multi_Rating::RATING_RESULTS_PAGE_SLUG, __( 'Rating Items', 'multi-rating' ), __( 'Rating Items', 'multi-rating' ), 'manage_options', Multi_Rating::RATING_ITEMS_PAGE_SLUG, 'mr_rating_items_screen' );
		add_submenu_page( Multi_Rating::RATING_RESULTS_PAGE_SLUG, __( 'Add New Rating Item', 'multi-rating' ), __( 'Add New Rating Item', 'multi-rating' ), 'manage_options', Multi_Rating::ADD_NEW_RATING_ITEM_PAGE_SLUG, 'mr_add_new_rating_item_screen' );
		add_submenu_page( Multi_Rating::RATING_RESULTS_PAGE_SLUG, __( 'Settings', 'multi-rating' ), __( 'Settings', 'multi-rating' ), 'manage_options', Multi_Rating::SETTINGS_PAGE_SLUG, 'mr_settings_screen' );
		add_submenu_page( Multi_Rating::RATING_RESULTS_PAGE_SLUG, __( 'Reports', 'multi-rating' ), __( 'Reports', 'multi-rating' ), 'mr_edit_ratings', Multi_Rating::REPORTS_PAGE_SLUG, 'mr_reports_screen' );
		add_submenu_page( Multi_Rating::RATING_RESULTS_PAGE_SLUG, __( 'Tools', 'multi-rating' ), __( 'Tools', 'multi-rating' ), 'mr_edit_ratings', Multi_Rating::TOOLS_PAGE_SLUG, 'mr_tools_screen' );
		add_submenu_page( Multi_Rating::RATING_RESULTS_PAGE_SLUG, __( 'About', 'multi-rating' ), __( 'About', 'multi-rating' ), 'mr_edit_ratings', Multi_Rating::ABOUT_PAGE_SLUG, 'mr_about_screen' );
		add_submenu_page( Multi_Rating::RATING_RESULTS_PAGE_SLUG, __( 'Edit Rating', 'multi-rating' ), '', 'mr_edit_ratings', Multi_Rating::EDIT_RATING_PAGE_SLUG, 'mr_edit_rating_screen' );	
	}

	/**
	 * Javascript and CSS used by the plugin
	 *
	 * @since 0.1
	 */
	public function admin_assets() {
		
		wp_enqueue_script( 'jquery' );
		
		$config_array = array(
				'ajax_url' => admin_url( 'admin-ajax.php' ),
				'ajax_nonce' => wp_create_nonce( Multi_Rating::ID.'-nonce' ),
				'confirm_clear_db_message' => __( 'Are you sure you want to permanently delete ratings?', 'multi-rating' )
		);

		wp_enqueue_script( 'mr-admin-script', plugins_url('assets' . DIRECTORY_SEPARATOR . 'js' . DIRECTORY_SEPARATOR . 'admin.js', __FILE__), array('jquery'), Multi_Rating::VERSION, true );
		wp_localize_script( 'mr-admin-script', 'mr_admin_data', $config_array );

		wp_enqueue_script( 'mr-frontend-script', plugins_url('assets' . DIRECTORY_SEPARATOR . 'js' . DIRECTORY_SEPARATOR . 'frontend-min.js', __FILE__), array('jquery'), Multi_Rating::VERSION, true );
		wp_localize_script( 'mr-frontend-script', 'mr_frontend_data', $config_array );
		
		// Add simple table CSS for rating form
		wp_enqueue_style( 'mr-frontend-style', plugins_url( 'assets' . DIRECTORY_SEPARATOR . 'css' . DIRECTORY_SEPARATOR . 'frontend-min.css', __FILE__ ) );
		wp_enqueue_style( 'mr-admin-style', plugins_url( 'assets' . DIRECTORY_SEPARATOR . 'css' . DIRECTORY_SEPARATOR . 'admin.css', __FILE__ ) );
		
		// flot
		wp_enqueue_script( 'flot', plugins_url( 'assets' . DIRECTORY_SEPARATOR . 'js' . DIRECTORY_SEPARATOR . 'flot' . DIRECTORY_SEPARATOR . 'jquery.flot.js', __FILE__ ), array( 'jquery' ) );
		wp_enqueue_script( 'flot-categories', plugins_url( 'assets' . DIRECTORY_SEPARATOR . 'js' . DIRECTORY_SEPARATOR . 'flot' . DIRECTORY_SEPARATOR . 'jquery.flot.categories.js', __FILE__ ), array( 'jquery', 'flot' ) );
		wp_enqueue_script( 'flot-time', plugins_url( 'assets' . DIRECTORY_SEPARATOR . 'js' . DIRECTORY_SEPARATOR . 'flot' . DIRECTORY_SEPARATOR . 'jquery.flot.time.js', __FILE__ ), array( 'jquery', 'flot' ) );
		wp_enqueue_script( 'flot-selection', plugins_url( 'assets' . DIRECTORY_SEPARATOR . 'js' . DIRECTORY_SEPARATOR . 'flot' . DIRECTORY_SEPARATOR . 'jquery.flot.selection.js', __FILE__ ), array( 'jquery', 'flot', 'flot-time' ) );
		
		// color picker
		wp_enqueue_style( 'wp-color-picker' );          
    	wp_enqueue_script( 'wp-color-picker' );
		
    	// date picker
		wp_enqueue_script('jquery-ui-datepicker');
		wp_enqueue_style( 'jquery-style', '//ajax.googleapis.com/ajax/libs/jqueryui/1.8.2/themes/smoothness/jquery-ui.css' );
		
		wp_enqueue_media();
	}

	/**
	 * Javascript and CSS used by the plugin
	 *
	 * @since 0.1
	 */
	public function assets() {
		
		wp_enqueue_script('jquery');
		
		// Add simple table CSS for rating form
		wp_enqueue_style( 'mr-frontend-style', plugins_url( 'assets' . DIRECTORY_SEPARATOR . 'css' . DIRECTORY_SEPARATOR . 'frontend-min.css', __FILE__ ) );
		
		// Allow support for other versions of Font Awesome
		$style_settings = (array) get_option( Multi_Rating::STYLE_SETTINGS );
		$include_font_awesome = $style_settings[Multi_Rating::INCLUDE_FONT_AWESOME_OPTION];
		$font_awesome_version = $style_settings[Multi_Rating::FONT_AWESOME_VERSION_OPTION];
		
		$icon_classes = MR_Utils::get_icon_classes( $font_awesome_version );
		
		$protocol = ( isset( $_SERVER['HTTPS'] ) && $_SERVER['HTTPS'] == 'on' ) ? 'https' : 'http';
		
		if ( $include_font_awesome ) {
			if ( $font_awesome_version == '4.0.3' ) {
				wp_enqueue_style( 'fontawesome', $protocol . '://netdna.bootstrapcdn.com/font-awesome/4.0.3/css/font-awesome.css' );
			} else if ( $font_awesome_version == '3.2.1' ) {
				wp_enqueue_style( 'fontawesome',  $protocol . '//netdna.bootstrapcdn.com/font-awesome/3.2.1/css/font-awesome.css' );
			} else if ( $font_awesome_version == '4.1.0' ) {
				wp_enqueue_style( 'fontawesome',  $protocol . '://maxcdn.bootstrapcdn.com/font-awesome/4.1.0/css/font-awesome.min.css' );
			} else if ( $font_awesome_version == '4.2.0' ) {
				wp_enqueue_style( 'fontawesome',  $protocol . '://maxcdn.bootstrapcdn.com/font-awesome/4.2.0/css/font-awesome.min.css' );
			} else if ( $font_awesome_version == '4.3.0' ) {
				wp_enqueue_style( 'fontawesome',  $protocol . '://maxcdn.bootstrapcdn.com/font-awesome/4.3.0/css/font-awesome.min.css' );
			}
		}
		
		$config_array = array(
				'ajax_url' => admin_url( 'admin-ajax.php' ),
				'ajax_nonce' => wp_create_nonce( Multi_Rating::ID.'-nonce' ),
				'icon_classes' => json_encode( $icon_classes ),
				'use_custom_star_images' => ( $style_settings[Multi_Rating::USE_CUSTOM_STAR_IMAGES] == true ) ? "true" : "false"
		);
		
		wp_enqueue_script( 'mr-frontend-script', plugins_url('assets' . DIRECTORY_SEPARATOR . 'js' . DIRECTORY_SEPARATOR . 'frontend-min.js', __FILE__), array('jquery'), Multi_Rating::VERSION, true );
		wp_localize_script( 'mr-frontend-script', 'mr_frontend_data', $config_array );
	}
	
	
	/**
	 * Register AJAX actions
	 */
	public function add_ajax_callbacks() {
		
		add_action( 'wp_ajax_save_rating', array( 'MR_Rating_Form', 'save_rating' ) );
		add_action( 'wp_ajax_nopriv_save_rating', array( 'MR_Rating_Form', 'save_rating' ) );
		add_action( 'wp_ajax_save_rating_item_table_column', array( 'MR_Rating_Item_Table', 'save_rating_item_table_column' ) );
		
		add_action( 'wp_ajax_nopriv_get_terms_by_taxonomy', 'mr_get_terms_by_taxonomy' );
		add_action( 'wp_ajax_get_terms_by_taxonomy', 'mr_get_terms_by_taxonomy' );
		
	}
		
	function add_custom_css() {
		?>
		<style type="text/css">
			<?php 
			$style_settings = (array) get_option( Multi_Rating::STYLE_SETTINGS );
			echo $style_settings[Multi_Rating::CUSTOM_CSS_OPTION];
			
			$star_rating_colour = $style_settings[Multi_Rating::STAR_RATING_COLOUR_OPTION];
			$star_rating_hover_colour = $style_settings[Multi_Rating::STAR_RATING_HOVER_COLOUR_OPTION];
			$error_message_colour = $style_settings[Multi_Rating::ERROR_MESSAGE_COLOUR_OPTION];
			
			$this->get_custom_star_images_css();
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
	}
	
	/**
	 * Helper function to get the custom star images CSS
	 */
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
}

class wpb_widget extends WP_Widget {
	function __construct() {
		parent::__construct(
		// Base ID of your widget
		'wpb_widget', 

		// Widget name will appear in UI
		__('Hotel Review Widget', 'wpb_widget_domain'), 

		// Widget description
		array( 'description' => __( 'Hotel Review Widget', 'wpb_widget_domain' ), ) 
		);
	}

	// Creating widget front-end
	// This is where the action happens
	public function widget( $args, $instance ) {
		$title = apply_filters( 'widget_title', $instance['title'] );
		// before and after widget arguments are defined by themes
		echo $args['before_widget'];
		if ( ! empty( $title ) )
		echo $args['before_title'] . $title . $args['after_title'];

		// This is where you run the code and display the output
		echo __( '<h1>OverAll Score</h1>', 'wpb_widget_domain' );
		echo __( '<h1>Ambiance</h1>', 'wpb_widget_domain' );
		echo __( 'Families Welcomed:'.PHP_EOL, 'wpb_widget_domain' );
		echo __( 'Design:\\r\\n', 'wpb_widget_domain' );
		echo __( 'Comments:\\r\\n', 'wpb_widget_domain' );
		echo __( '<h1>The Rooms</h1>', 'wpb_widget_domain' );
		echo __( 'Family Comfort:\\r\\n', 'wpb_widget_domain' );
		echo __( 'Comments:\\r\\n', 'wpb_widget_domain' );
		echo __( '<h1>Dining on Site</h1>', 'wpb_widget_domain' );
		echo __( 'Parents:\\r\\n', 'wpb_widget_domain' );
		echo __( 'Kids:\\r\\n', 'wpb_widget_domain' );
		echo __( 'Comments:\\r\\n', 'wpb_widget_domain' );
		echo __( '<h1>Family Fun</h1>', 'wpb_widget_domain' );
		echo __( 'Kids:\\r\\n', 'wpb_widget_domain' );
		echo __( 'Comments:\\r\\n', 'wpb_widget_domain' );
		echo __( '<h1>Spa & Fitness</h1>', 'wpb_widget_domain' );
		echo __( 'Parents:\\r\\n', 'wpb_widget_domain' );
		echo __( 'Comments:\\r\\n', 'wpb_widget_domain' );
		echo $args['after_widget'];
	}
		
	// Widget Backend 
	public function form( $instance ) {
		if ( isset( $instance[ 'title' ] ) ) {
			$title = $instance[ 'title' ];
		}
		else {
			$title = __( 'New title', 'wpb_widget_domain' );
		}
	
		// Widget admin form
		?>
		<p>
		<label for="<?php echo $this->get_field_id( 'title' ); ?>"><?php _e( 'Title:' ); ?></label> 
		<input class="widefat" id="<?php echo $this->get_field_id( 'title' ); ?>" name="<?php echo $this->get_field_name( 'title' ); ?>" type="text" value="<?php echo esc_attr( $title ); ?>" />
		</p>
		<?php 
	}
	
	// Updating widget replacing old instances with new
	public function update( $new_instance, $old_instance ) {
		$instance = array();
		$instance['title'] = ( ! empty( $new_instance['title'] ) ) ? strip_tags( $new_instance['title'] ) : '';
		return $instance;
	}
} // Class wpb_widget ends here

	function wpb_load_widget() {
		register_widget( 'wpb_widget' );
	}

	// Register and load the widget
	add_action( 'widgets_init', 'wpb_load_widget' );
       function mr_multi_rating() {
	return Multi_Rating::instance();
}
mr_multi_rating();
/* Stop Adding Functions Below this Line */
?>


