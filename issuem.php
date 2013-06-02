<?php
/**
 * Main PHP file used to for initial calls to IssueM's classes and functions.
 *
 * @package IssueM
 */
 
/*
Plugin Name: IssueM
Plugin URI: http://issuem.com/
Description: A feature rich magazine and newspaper issue manager plugin for WordPress.
Author: IssueM Development Team
Version: 1.2.0
Author URI: http://issuem.com/
Tags: 
*/

define( 'ISSUEM_PLUGIN_SLUG', 		'issuem' );
define( 'ISSUEM_VERSION', 			'1.2.0' );
define( 'ISSUEM_DB_VERSION', 		'1.0.0' );
define( 'ISSUEM_API_URL', 			'http://api.issuem.com' );
define( 'ISSUEM_PLUGIN_URL', 		plugin_dir_url( __FILE__ ) );
define( 'ISSUEM_PLUGIN_PATH', 		plugin_dir_path( __FILE__ ) );
define( 'ISSUEM_PLUGIN_BASENAME', 	plugin_basename( __FILE__ ) );
define( 'ISSUEM_PLUGIN_REL_DIR', 	dirname( ISSUEM_PLUGIN_BASENAME ) );

/**
 * Instantiate IssueM class, require helper files
 *
 * @since 1.2.0
 */
function issuem_plugins_loaded() {

	require_once( 'issuem-class.php' );

	// Instantiate the Pigeon Pack class
	if ( class_exists( 'IssueM' ) ) {
		
		global $dl_plugin_issuem;
		
		$dl_plugin_issuem = new IssueM();
		$issuem_settings = $dl_plugin_issuem->get_settings();
		
		require_once( 'issuem-post-type.php' );
		require_once( 'issuem-taxonomy.php' );
		require_once( 'issuem-functions.php' );
		
		if ( !isset( $issuem_settings['use_wp_taxonomies'] ) || empty( $issuem_settings['use_wp_taxonomies'] ) ) { 
			// Don't load these if we don't have too
			require_once( 'issuem-cats-taxonomy.php' );
			require_once( 'issuem-tags-taxonomy.php' );
		
		}
		
		require_once( 'issuem-shortcodes.php' );
		require_once( 'issuem-widgets.php' );
		require_once( 'issuem-feeds.php' );
			
		//Internationalization
		load_plugin_textdomain( 'issuem', false, ISSUEM_PLUGIN_REL_DIR . '/i18n/' );
		
		do_action( 'issuem_loaded' );
			
	}

}
add_action( 'plugins_loaded', 'issuem_plugins_loaded', 4815162342 ); //wait for the plugins to be loaded before init