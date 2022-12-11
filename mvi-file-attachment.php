<?php
/*
Plugin Name: MVI File Attachment
Description: Plugin to allow files to be attatched to posts and automatically create email capture forms to handle download requests.
Version: 2.0
Author: Graceson Aufderheide
License: GPLv2 or later
Text Domain: mvi-file-attachment
*/


if ( !defined( 'ABSPATH' ) ) {
    exit;
    // Exit if accessed directly.
}

/********************
Autoload dependencies
********************/
require("vendor/autoload.php");


register_activation_hook(__FILE__, ['MVIFileAttatchmentBase', 'on_activation']);
register_deactivation_hook(__FILE__, ['MVIFileAttatchmentBase', 'on_deactivation']);

if ( !class_exists( 'MVIFileAttatchmentBase' ) ) {

  class MVIFileAttatchmentBase {
    const PLUGIN_PREFIX = 'mvi_fa_';
    const VERSION_NO = '2.0';

    /**
     * Registers this class with WordPress.
     */
  	public static function register() {
  		$plugin = new self();
  		add_action( 'init', [$plugin, 'on_loaded']);
      add_action( 'init', [$plugin, 'on_admin_loaded'], 5); //For some reason needs to be high-priority init hook
      add_action( self::PLUGIN_PREFIX . 'export_file', ['MVIFileAttachment\CustomFunctions\CsvExport', 'run_export_file'] ); //add the CSV export action for the event that is scheduled in on_activation()
  		add_filter( 'get_mvi_file_attatchment_base_instance', [ $plugin, 'get_instance' ] ); //save the instance of this object so that the process can be removed or modified later.
  	}

  	/**
  	 * Get instance
  	 * Reference: https://wordpress.stackexchange.com/questions/61437/php-error-with-shortcode-handler-from-a-class/61440#61440
     *
     * @return object
     */
  	public function get_instance() {
    	return $this; // return the object
    }

    public function on_loaded() {
      new MVIFileAttachment\PostType; //Create the post type for storing downloads
      new MVIFileAttachment\Taxonomy; //Create a custom taxonomy for tracking if a file exists
      MVIFileAttachment\Fields::register(); //Create all the custom fields
      new MVIFileAttachment\DownloadPage; //Create the virtual download page
      new MVIFileAttachment\Shortcode; //load assets, prepare shortcode for displaying the custom download form
      MVIFileAttachment\CustomFunctions\ProcessSubmission::register();
    }

    public function on_admin_loaded() {
      if ( !is_admin() ) {
        return;
      }
      new MVIFileAttachment\CustomAdminColumn(); //Create the custom admin column
      new MVIFileAttachment\Settings; //Create the settings page
      new MVIFileAttachment\CustomFunctions\ApplyTaxonomy; //Apply the taxonomy every time a post is saved

    }

    public static function on_activation() {
      MVIFileAttachment\CustomTable::create_table(); //Create the custom DB table for storing fields
      MVIFileAttachment\CustomFunctions\CsvExport::activate_weekly_export(); //schedue the export function
    }

    public static function on_deactivation() {
      MVIFileAttachment\CustomFunctions\CsvExport::deactivate_weekly_export();
    }
  }

  add_action('plugins_loaded', array('MVIFileAttatchmentBase', 'register'));

}

new MVIFileAttachment\MyVirtualPage;


/********************
Add form and content to theme.
********************/

//Add PDF badge to application cards using Blocksy theme hook
function applications_pdf_badge( $query ) {
	$pdf_file = rwmb_meta( \MVIFileAttatchmentBase::PLUGIN_PREFIX . 'post_download_file' );
if ( 'applications' == get_post_type() && !is_search() && !empty($pdf_file) ) {
	echo '<a class=\'pdf-badge\' href=" ' . get_post_permalink() . ' ">PDF</a>';
	}
}
add_action( 'blocksy:loop:card:start', 'applications_pdf_badge');
