<?php

if ( ! defined( 'ABSPATH' ) ) {
    exit; // Exit if accessed directly
}


class IMKT_Collection_Plugin_DB_API{
	private $coldb_prefix = 'coldb_';
	private $site_prefix;
	private $prefix;
	private $queries;

	public function __construct(){
		global $wpdb;
		$this->site_prefix = $wpdb->prefix;
		$this->prefix = $this->site_prefix . $this->coldb_prefix;
		$this->queries = array(
			'list-collections' => 'SELECT * FROM `'. $this->prefix .'collection_name` WHERE `user_id` = %d',
			'get-collection' => 'SELECT * FROM `'. $this->prefix .'collection` WHERE `user_id` = %d AND collection_name_id = %d',
			'is-owner' => 'SELECT `id` FROM `'. $this->prefix .'collection_name` WHERE `user_id` = %d AND `id` = %d;',
			'entry-exists' => 'SELECT * FROM `'. $this->prefix .'collection` WHERE `book_id` = %d AND `collection_name_id` = %d AND `user_id` = %d;',
			'insert' => 'INSERT INTO `'. $this->prefix .'collection` ( `user_id`, `book_id`, `book_title`, `permalink`, `collection_name_id` ) SELECT %d, %d, `post_title`, `guid`, %d FROM `'. $this->site_prefix .'posts` WHERE `id` = %d;',
			'remove' => 'DELETE FROM `'. $this->prefix .'collection` WHERE `user_id` = %d AND `id` = %d',
			'get-product' => 'SELECT * FROM `'. $this->site_prefix .'posts` WHERE `post_type` = \'product\' AND `id` = %d',
			'create-default-name' => 'INSERT INTO `'. $this->prefix .'collection_name` ( `user_id`, `collection_name_str` ) VALUES ( %d, "%s" )',
			'name-exists' => 'SELECT * FROM `'. $this->prefix .'collection_name` WHERE `user_id` = %d AND `collection_name_str` = "%s"',
			'name-exists-id' => 'SELECT * FROM `'. $this->prefix .'collection_name` WHERE `user_id` = %d AND `id` = %d',
			'get-collection-name' => 'SELECT collection_name_str FROM `' . $this->prefix . 'collection_name` WHERE `user_id` = %d AND `id` = %d',
			'insert-name' => 'INSERT INTO `'. $this->prefix .'collection_name` ( `user_id`, `collection_name_str` ) VALUES ( %d, "%s" )',
			'update-name' => 'UPDATE `'. $this->prefix .'collection_name` SET `collection_name_str` = "%s" WHERE `id` = %d AND `user_id` = %d',
			'remove-name' => 'DELETE FROM `'. $this->prefix .'collection_name` WHERE `user_id` = %d AND `id` = "%s"'
			);
	}

	//Will not use, every query also uses the use_id in a where close
	private function is_collection_owner( $collection_id ){
		global $wpdb;
		$wpdb->query( sprintf( $this->queries['is-owner'], get_current_user_id(), $collection_id ) );
		if ( $wpdb->num_rows == 0 ){
			return false;
		}
		return true;
	}

	public function list_collections(){
		$this->find_default_users_collection_id();
		global $wpdb;
		$results = $wpdb->get_results( sprintf( $this->queries['list-collections'], get_current_user_id() ) );
		if( count( $results ) == 0 ){
			return false;
		}
		return $results;
	}


	public function get_collection( $collection_id ){ 	
		global $wpdb;
		$results = $wpdb->get_results( sprintf( $this->queries[ 'get-collection' ], get_current_user_id(), $collection_id ) );
		if( count( $results ) > 0 ){
			return $results;	
		}
		return false;
	}

	public function find_default_users_collection_id(){
		global $wpdb;
		$result = $wpdb->get_results( sprintf( $this->queries['name-exists'], get_current_user_id(), DEFAULT_COLLECTION_NAME ) );
		if( $wpdb->last_error ){
			return $wpdb->last_error;
		}
		if( !$result ){
			$wpdb->query( sprintf( $this->queries['create-default-name'], get_current_user_id(), DEFAULT_COLLECTION_NAME ) );
			return false;
		}
		return (int)$result[0]->id; //Typecasting cause was returning as string even while being BIGINT 
	}


	public function collection_entry_exists( $book_id, $collection_name_id ){
		//If it exists returns true
		global $wpdb;
		$results = $wpdb->query( sprintf( $this->queries[ 'entry-exists' ], $book_id, $collection_name_id, get_current_user_id() ) );
		if( !$results ){
			return false;
		}
		return true;
	}


	public function insert( $book_id, $collection_name_id ){
		if ( $this->collection_entry_exists( $book_id, $collection_name_id ) ){
			return false;
		}

		global $wpdb;
		$wpdb->query( sprintf( $this->queries[ 'insert' ], get_current_user_id(), $book_id, $collection_name_id , $book_id ) );

		if ( $wpdb->last_error ){
			return false; 
		}
		if( $wpdb->rows_affected == 0 ){
			return false;
		}
		return true;
		
	}

	//Do i even have to make an update?
	public function update(){}
	
	public function remove( $entry_id ){ 
		global $wpdb;
		$wpdb->query( sprintf( $this->queries['remove'], get_current_user_id(), $entry_id ) );
		if( $wpdb->rows_affected == 0 ){
			return false;
		}  
		return true;
	}

	private function collection_name_exists( $collection_name_id, $collection_name ){
		global $wpdb;
		if ($collection_name_id){
			$result = $wpdb->query( sprintf( $this->queries['name-exists-id'], get_current_user_id(), $collection_name_id ) );
		}
		else{
			$result = $wpdb->query( sprintf( $this->queries['name-exists'], get_current_user_id(), $collection_name ) );
		}
		if( !$result ){
			return false;
		}
		return true;
	}

	public function get_name( $collection_id ){
		if( !$collection_id ){
			return false;
		}

		global $wpdb;
		$result = $wpdb->get_results( sprintf( $this->queries[ 'get-collection-name' ], get_current_user_id(), $collection_id ) );
		if( !$result ){
			return false;
		}

		return $result;
	}

	public function insert_name( $collection_name ){
		if( !$collection_name ){
			return false;
		}

		//Cannot check databases response for conflict or set the collection_name to be unique, since many instances of the same collection_name_str may exist but for different users
		if ( $this->collection_name_exists( null, $collection_name ) ){  
			return false;
		}

		global $wpdb;
		$wpdb->query( sprintf( $this->queries['insert-name'], get_current_user_id(), esc_html( $collection_name ) ) );

		if( !( $wpdb->rows_affected > 0 ) ){
			return false;
		}
		return true;
	}

	public function update_name( $entry_id, $new_name ){
		global $wpdb;
		$wpdb->query( sprintf( $this->queries[ 'update-name' ], $new_name, $entry_id, get_current_user_id() ) );
		if( $wpdb->rows_affected == 0 ){
			return false;
		}  
		return true;
	}
	
	public function remove_name( $collection_id ){
		global $wpdb;
		$wpdb->query( sprintf( $this->queries['remove-name'], get_current_user_id(), $collection_id ) );
		if( !( $wpdb->rows_affected > 0 ) ){
			return false;
		}
		return true;
	}

}


?>