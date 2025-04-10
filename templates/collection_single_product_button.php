<?php

if ( ! defined( 'ABSPATH' ) ) {
    exit; // Exit if accessed directly
}

if( is_user_logged_in()){
	$echoed = '';
	global $product;
	$collections = $this->coldb->list_collections();

	$echoed .= '<style> .add-single-page button, .add-single-page p, .add-single-page option{ text-wrap: nowrap; font-family: "Roboto", Sans-serif; font-size: .92rem; font-weight: 400; } .collection { padding-top: 20px; padding-bottom: 10px; display: flex; justify-content: left; gap: 10px; } .collection select{ height: auto; width: 20vw;} .collection button{ padding: 15px 15px 15px 15px; } </style>';
	$echoed .= '<div class="add-single-page">';
	$echoed .= '<form class="collection" method="POST">';
	$echoed .= '<button type="submit" > Προσθήκη σε συλλογή </button>';
	$echoed .= '<select name="add-to-collection">';

	foreach( $collections as $collection_entry ){
		$echoed .= '<option value="' . $collection_entry->id . '">' . $collection_entry->collection_name_str . '</option>';
	}
	
	$echoed .= '</select>';
	$echoed .= '</form>';
	$echoed .= '</div>';

	if( !empty( $_POST ) && isset( $_POST[ 'add-to-collection' ] ) ){
		$result = $this->coldb->insert( $product->get_id(), intval( $_POST[ 'add-to-collection' ] ) );
		$echoed .= $result ? '<p>Προστέθηκε στην συλλογή</p>' : '<p>Το βιβλίο υπάρχει ήδη στη συλλογή</p>';
	}
	echo $echoed;
}
?>
