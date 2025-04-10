<?php

if ( ! defined( 'ABSPATH' ) ) {
    exit; // Exit if accessed directly
}

class IMKT_Plugin_Template_Loader{
	public function get_template( $template_name, $coldb ){
		$template_path = plugin_dir_path( __FILE__) . '../templates/' . $template_name . '.php';
		if( !is_user_logged_in() ){
			wp_redirect( home_url() . '/my-account/' );
			exit;
		} elseif( file_exists( $template_path ) ){
			include $template_path;
			exit;
		} else{
			echo '<p> Template not found </p>';
			exit;
		}
	}
}

?>