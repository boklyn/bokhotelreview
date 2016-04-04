<?php

/*

Plugin Name: Sponsored Systems for AudraCherryPickings.com
Description: Sponsored Systems code changes for AudraCherryPickings.com
Plugin URI: http://boklyn.net/
Version: 1.0
Author: boklyn wong
Author URI: http://boklyn.net/
Text Domain: sponsored-system
*/
/* Start Adding Functions Below this Line */
// Creating the widget 
class Bok_Review {
	
	/** Singleton *************************************************************/
	
	private static $instance;
	/**
	 * Constants
	 */
	const
	VERSION = '1.0',
	ID = 'bok-review',

	// tables
	TABLE_BOKRATINGS							= 'bok_ratings',
	TABLE_BOKREVIEWS							= 'bok_reviews',
	TABLE_WORDPRESS								= 'wp_posts',
	RATING_ITEM_TBL_NAME 						= 'mr_rating_item',
	
	TABLE_BOKREVIEWCATS							= 'bok_reviewcats';
	// settings

	public static function instance() {
	
		if ( ! isset( self::$instance )
				&& ! ( self::$instance instanceof Bok_Review ) ) {					
			self::$instance = new Bok_Review;		
			self::$instance->includes();	
			add_action( 'init', array( self::$instance, 'load_textdomain' ) );
		}	
		return Bok_Review::$instance;
	}

	
	/**
	 * Activates the plugin
	 */
	public static function activate_plugin() {
		
		global $wpdb;	
		//bokrating create database
		$sql_create_bok_ratings_tbl = 'CREATE TABLE ' . $wpdb->prefix . Bok_Review::TABLE_BOKRATINGS . ' (
				id int(11) NOT NULL AUTO_INCREMENT,
				bokreviewid int(11) DEFAULT NULL,
				name varchar(45) DEFAULT NULL,
				star_level decimal(9,1) DEFAULT NULL,
				notes varchar(300) DEFAULT NULL,
				category varchar(45) DEFAULT NULL,
				PRIMARY KEY (id),
				KEY bokreviewid (bokreviewid)
		) ENGINE=InnoDB AUTO_INCREMENT=1;';
		
		$sql_create_bok_reviewcat_tbl = 'CREATE TABLE ' . $wpdb->prefix . Bok_Review::TABLE_BOKREVIEWCATS . ' (
			  id int(11) NOT NULL AUTO_INCREMENT,
			  name varchar(45) DEFAULT NULL,
			  reviewcat varchar(45) DEFAULT NULL,
			  position int(11) DEFAULT NULL,
			  PRIMARY KEY (id)
		) ENGINE=InnoDB AUTO_INCREMENT=1;';

		$sql_create_bok_reviews_tbl = 'CREATE TABLE ' . $wpdb->prefix . Bok_Review::TABLE_BOKREVIEWS . ' (
			  id int(11) NOT NULL AUTO_INCREMENT,
			  score decimal(3,1) DEFAULT NULL,
			  post_id bigint(20) unsigned NOT NULL,
			  overall_notes varchar(200) DEFAULT NULL,
			  reviewcat varchar(45) DEFAULT NULL,
			  PRIMARY KEY (id),
			  KEY post_id (post_id)
		) ENGINE=InnoDB AUTO_INCREMENT=1;';
		
		require_once( ABSPATH . 'wp-admin/includes/upgrade.php' );
		dbDelta( $sql_create_bok_ratings_tbl );
		dbDelta( $sql_create_bok_reviewcat_tbl );
		dbDelta( $sql_create_bok_reviews_tbl );
		//Alter table link need to fix this later. work on this later to build foreign key constraint.  
		//$sql = "ALTER TABLE " . Bok_Review::TABLE_BOKRATINGS . " ADD CONSTRAINT theconstraint FOREIGN KEY (bokreviewid) REFERENCES " . Bok_Review::TABLE_WORDPRESS . "(id) ON DELETE CASCADE ON UPDATE CASCADE;";
		//$wpdb->query($sql);
	}
	
	/**
	 * Uninstalls the plugin
	 */
	public static function uninstall_plugin() {
		
		delete_option( Bok_Review::GENERAL_SETTINGS );
		delete_option( Bok_Review::CUSTOM_TEXT_SETTINGS );
		delete_option( Bok_Review::POSITION_SETTINGS );
		delete_option( Bok_Review::STYLE_SETTINGS );
		
		// Drop tables
		global $wpdb;

		$wpdb->query( 'DROP TABLE IF EXISTS ' . $wpdb->prefix . Bok_Review::TABLE_BOKRATINGS );
		$wpdb->query( 'DROP TABLE IF EXISTS ' . $wpdb->prefix . Bok_Review::TABLE_BOKREVIEWS );
		$wpdb->query( 'DROP TABLE IF EXISTS ' . $wpdb->prefix . Bok_Review::TABLE_BOKREVIEWCATS );
	}
	

	/**
	 * Includes files
	 */
	function includes() {
		require dirname( __FILE__ ) . DIRECTORY_SEPARATOR . 'includes' . DIRECTORY_SEPARATOR . 'widgets.php';	
		require dirname( __FILE__ ) . DIRECTORY_SEPARATOR . 'includes' . DIRECTORY_SEPARATOR . 'class-utils.php';	
	}
	
	/**
	 * Loads plugin text domain
	 */
	public function load_textdomain() {
		load_plugin_textdomain( 'sponsored-system', false, dirname( plugin_basename( __FILE__) ) . DIRECTORY_SEPARATOR . 'languages' . DIRECTORY_SEPARATOR );
	}

}

/**
 * Activate plugin
 */
function br_activate_plugin() {
	
	if ( is_admin() && ( ! defined( 'DOING_AJAX' ) || ! DOING_AJAX ) ) {
		Bok_Review::activate_plugin();
	}
	
}
register_activation_hook( __FILE__, 'br_activate_plugin' );


/**
 * Uninstall plugin
 */
function br_uninstall_plugin() {
	
	if ( is_admin() && ( ! defined( 'DOING_AJAX' ) || ! DOING_AJAX ) ) {
		Bok_Review::uninstall_plugin();
	}
}
register_uninstall_hook( __FILE__, 'br_uninstall_plugin' );

/*
 * Instantiate plugin main class
 */
function mr_Bok_Review() {
	return Bok_Review::instance();
}
mr_Bok_Review();
/* Stop Adding Functions Below this Line */
?>