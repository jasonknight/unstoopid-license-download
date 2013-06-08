<?php
/**
 * @package UnstoopidLicenseDownload
 * @version 0.1
 */
/*
Plugin Name: Unstoopid License Download
Plugin URI: http://wordpress.org/extend/plugins/unstoopid-license-download/
Description: A simple, hassle free way to issue licenses for downloaded content
Author: Jason Martin
Version: 0.1
Author URI: http://red-e.eu
*/
define( 'REDE_PLUGIN_BASE_PATH', plugin_dir_path(__FILE__) );
require_once( plugin_dir_path(__FILE__) . 'classes/class-rede-helpers.php' );

function unstoopid_license_download_init() {
  
  add_action( "wp_enqueue_scripts", "unstoopid_license_download_enqueue_scripts" );
  add_action( "wp_footer", "unstoopid_license_download_footer" );  
  
}

function unstoopid_license_download_enqueue_scripts() {
		wp_enqueue_style('unstoopid-css',  plugins_url('assets/css/unstoopid-license-download.css',               __FILE__) );
}
function unstoopid_license_download_footer() {

}

add_action("init","unstoopid_license_download_init",10);


?>
