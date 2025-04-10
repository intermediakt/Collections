<?php
/**
 * Plugin Name: Collection plugin
 * Description: Adds a wishlist type feature in WooCommerce.
 * Version: 1.0.0
 * Author: Charalambos Rentoumis
 *
 * WC requires at least: 6.0
 * WC tested up to: 6.6.2
 *
 * License: GNU General Public License v3.0
 * License URI: http://www.gnu.org/licenses/gpl-3.0.html
**/


/**
 * Collection Global Variables
 * 
 * @var PLUGIN_DIR_PATH				string full path to plugin directory
 * @var DEFAULT_COLLECTION_NAME 	string default name used for the default collection
 * @var COLLECTIONS_DISPLAY_PAGE	string collection page name
 * @var DROP_COLLECTIONS_TABLES 	bool to drop or not the plugins database tables on deactivation
**/


/**
 * Collections Class Variables
 * 
 * @var $coldb 						object used as an api for database communication (IMKT_Collection_Plugin_DB_API)
 * @var $template_loader 			object used to load the collections page template (IMKT_Plugin_Template_Loader)
 * 
**/

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

define( 'PLUGIN_DIR_PATH', plugin_dir_path( __FILE__ ) );
define( 'DEFAULT_COLLECTION_NAME', 'Αγαπημένα' );
define( 'COLLECTIONS_DISPLAY_PAGE', 'Συλλογές' );
define( 'DROP_COLLECTIONS_TABLES', false );
//https://make.wordpress.org/docs/plugin-developer-hand/hooks/creating-custom-hooks/
//TODO: debug.log output on $wpdb, WP_Errors
//TODO: Error:Handling/Reporting

register_activation_hook( __FILE__, array( 'IMKT_Collection_Plugin', 'activate' ) );
register_deactivation_hook( __FILE__, array( 'IMKT_Collection_Plugin', 'deactivate' ) );

//plugins_loaded
if ( !has_action( 'init', 'imkt_initialize_collection_plugin' ) ){
	add_action( 'init', 'imkt_initialize_collection_plugin' );
}

function imkt_initialize_collection_plugin() {
	if (class_exists( 'IMKT_Collection_Plugin' ) ){
    	$collection = new IMKT_Collection_Plugin();
	}
}


class IMKT_Collection_Plugin{

	private $coldb = null;
	private $template_loader = null;

	function __construct(){

		require_once PLUGIN_DIR_PATH . 'includes/collection_plugin_db_api.php';
		$this->coldb = new IMKT_Collection_Plugin_DB_API();

		require_once PLUGIN_DIR_PATH . 'includes/collection_plugin_template_loader.php';
		$this->template_loader = new IMKT_Plugin_Template_Loader();


		//TODO: Move to activate() (static) run on activate hook
		if( !has_action( 'admin_menu', array( $this, 'add_menu_item' ) ) ){
			add_action( 'admin_menu', array( $this, 'add_menu_item' ) );
		}
		if( !has_action( 'woocommerce_after_shop_loop_item', array( $this, 'add_custom_button_on_product_preview' ) ) ){
			add_action( 'woocommerce_after_shop_loop_item', array( $this, 'add_custom_button_on_product_preview' ), 15 );
		}
		//action hook for next to the image: woocommerce_product_meta_end, 
		//action hook for under the image: woocommerce_simple_add_to_cart
		if( !has_action( 'woocommerce_product_meta_end', array( $this, 'add_custom_button_on_single_product' ) ) ){
			add_action( 'woocommerce_product_meta_end', array( $this, 'add_custom_button_on_single_product' ), 25 );
		}			

		add_action( 'wp', function(){
			if ( is_page( COLLECTIONS_DISPLAY_PAGE ) ) {
				$this->template_loader->get_template( 'collection_page_template', $this->coldb );
			}
		});
	}

	public static function activate(){
		require_once PLUGIN_DIR_PATH . 'includes/collection_plugin_activate.php';
		IMKT_Collection_Plugin_Activate::activate();
	}

	public static function deactivate(){
		require_once PLUGIN_DIR_PATH . 'includes/collection_plugin_deactivate.php';
		IMKT_Collection_Plugin_Deactivate::deactivate();	
	}

	public function add_custom_button_on_product_preview(){
		require PLUGIN_DIR_PATH . 'templates/collection_product_preview_button.php';
	}

	public function add_custom_button_on_single_product(){
		require_once PLUGIN_DIR_PATH . 'templates/collection_single_product_button.php';
	}
	
	public function add_menu_item(){
		add_menu_page( 'CollectionSettings', 'Collection', 'manage_options', 'collection_plug', array( $this, 'render_settings') );
	}

	public function render_settings(){
		require_once PLUGIN_DIR_PATH . 'templates/admin/settings_page.php';
	}
}

?>
