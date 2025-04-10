<?php

if ( ! defined( 'ABSPATH' ) ) {
    exit; // Exit if accessed directly
}

if ( is_user_logged_in() ){
	$echoed = '';
	$echoed = '<style> .add-preview-button p, .add-preview-button input{ text-wrap: nowrap; font-family: "Roboto", Sans-serif; font-size: 17px; font-weight: 400; } </style>';
	global $wpdb;
	global $product;
	$product_id = $product->get_id();
	$tmp_id = $this->coldb->find_default_users_collection_id();
	
	
	if( isset($_POST[ 'add-to-col-button' . $product_id ] ) ){
		$res = $this->coldb->insert( $product_id, $tmp_id );
	}
	$exists = $wpdb->get_results( 'SELECT * FROM `Q48ZbU_coldb_collection` WHERE `user_id` = ' . get_current_user_id() . ' AND `collection_name_id` = ' . $tmp_id . ' AND `book_id` = ' . $product_id );
	$echoed .= '<div class="add-preview-button">';
	if( $exists ){
		$echoed .= '	<p> Ήδη στα Αγαπημένα </p>';
	} else{
		$echoed .= '<form method="POST">';
		$echoed .= '	<input type="submit" name="add-to-col-button' . $product_id . '" value="Προσθήκη στα Αγαπημένα">';
		$echoed .= '</form>';
	}
	$echoed .= '</div>';
	echo $echoed;
}
?>