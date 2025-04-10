<?php

if ( ! defined( 'ABSPATH' ) ) {
    exit; // Exit if accessed directly
}

class IMKT_Collection_Plugin_Deactivate{
    
    public static function deactivate(){
        IMKT_Collection_Plugin_Deactivate::remove_collections_page();
        IMKT_Collection_Plugin_Deactivate::drop_tables();
        flush_rewrite_rules();
    }

    public static function remove_collections_page(){
        global $wpdb;
        $page_id = get_option('collections_page_id');
        if( $page_id ){
            wp_delete_post( $page_id, true );
            delete_option('collections_page_id');
        }
        wp_reset_postdata();
    }

    public static function drop_tables(){
        $to_drop_or_not = get_option( 'to-drop-or-not' );
        if ( $to_drop_or_not === 'on' ) {
            delete_option( 'to-drop-or-not' );
            global $wpdb;
            $wpdb->query( 'DROP TABLE IF EXISTS `'. $wpdb->prefix . 'coldb_collection`' );
            $wpdb->query( 'DROP TABLE IF EXISTS `'. $wpdb->prefix . 'coldb_collection_name`' );
        }
    }
}


?>