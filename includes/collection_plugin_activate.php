<?php

if ( ! defined( 'ABSPATH' ) ) {
    exit; // Exit if accessed directly
}

class IMKT_Collection_Plugin_Activate{
	public static function activate(){
		IMKT_Collection_Plugin_Activate::init_collection();
		flush_rewrite_rules();
	}

	private static function init_collection(){
		global $wpdb;
		$col_prefix = 'coldb_';
		$site_prefix = $wpdb->prefix;
		$prefix = $site_prefix . $col_prefix;
		$create_table_collection = 'CREATE TABLE IF NOT EXISTS `'. $prefix .'collection`( id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY NOT NULL, user_id BIGINT(20) UNSIGNED NOT NULL, book_id BIGINT(20) UNSIGNED NOT NULL, book_title TEXT NOT NULL, permalink varchar(255) NOT NULL, collection_name_id BIGINT UNSIGNED NOT NULL, FOREIGN KEY (book_id) REFERENCES '. $site_prefix .'posts(id) ON DELETE CASCADE, FOREIGN KEY (user_id) REFERENCES '. $site_prefix .'users(id) ON DELETE CASCADE, FOREIGN KEY (collection_name_id) REFERENCES '. $prefix .'collection_name(id) ON DELETE CASCADE );';
		$create_table_collection_name = 'CREATE TABLE IF NOT EXISTS `'. $prefix .'collection_name`( id BIGINT UNSIGNED PRIMARY KEY AUTO_INCREMENT NOT NULL, user_id BIGINT(20) UNSIGNED NOT NULL, FOREIGN KEY (user_id) REFERENCES '. $site_prefix .'users(id) ON DELETE CASCADE, collection_name_str varchar(120) NOT NULL );';
		
		$col_name_result = $wpdb->query( $create_table_collection_name );
		if( !$col_name_result ){
			exit;
		}

		$col_result = $wpdb->query( $create_table_collection );
		if( !$col_result ){
			exit;
		}

		$query = new WP_Query(
			array(
				'post_type'				=> 'page',
				'title'					=> COLLECTIONS_DISPLAY_PAGE,
				'posts_per_page'		=> 1
		    )
		);

		if( empty( $query->post ) ){
			$new_page = array(
				'post_title' => COLLECTIONS_DISPLAY_PAGE,
				'post_content' => 'collections_page_content',
				'post_status' => 'publish',
				'post_type' => 'page',
				'post_author' => 1
			);
			$page_id = wp_insert_post($new_page);
			update_option('collections_page_id', $page_id);
		}

	}
}

?>