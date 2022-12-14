<?php
/*
Plugin Name: MVI File Attachment
Description: Plugin to allow files to be attached to posts and automatically create email capture forms to handle download requests.
Version: 2.0.5
Author: Graceson Aufderheide
License: GPLv2 or later
Text Domain: mvi-file-attachment
*/


if ( !defined( 'ABSPATH' ) ) {
    exit;
    // Exit if accessed directly.
}

/********************
Autoload/require dependencies
********************/
require("vendor/autoload.php");

//Don't run if MetaBox isn't installed.
if ( !defined( 'RWMB_VER' ) ) {
  return;
}

/********************
Begin plugin
********************/
register_activation_hook(__FILE__, ['MVIFileAttachmentBase', 'on_activation']);
register_deactivation_hook(__FILE__, ['MVIFileAttachmentBase', 'on_deactivation']);

if ( !class_exists( 'MVIFileAttachmentBase' ) ) {

  class MVIFileAttachmentBase {
    const PLUGIN_PREFIX = 'mvi_fa_';
    const VERSION_NO = '2.0.5';

    /**
     * Registers this class with WordPress.
     */
  	public static function register() {
  		$plugin = new self();
  		add_action( 'init', [$plugin, 'on_init']);
      add_action( 'init', [$plugin, 'on_init_admin'], 5); //For some reason needs to be high-priority init hook
      add_action( self::PLUGIN_PREFIX . 'export_file', ['MVIFileAttachment\CustomFunctions\CsvExport', 'run_export_file'] ); //add the CSV export action for the event that is scheduled in on_activation()
  	}

    public function on_init() {
      MVIFileAttachment\PostType::register(); //Create the post type for storing downloads
      MVIFileAttachment\Taxonomy::register(); //Create a custom taxonomy for tracking if a file exists
      MVIFileAttachment\Fields::register(); //Create all the custom fields
      MVIFileAttachment\VirtualDownloadPage\Controller::register( new MVIFileAttachment\VirtualDownloadPage\TemplateLoader); //Create the virtual download page
      MVIFileAttachment\Shortcode::register(); //load assets, prepare shortcode for displaying the custom download form
      MVIFileAttachment\CustomFunctions\ProcessSubmission::register();
    }

    public function on_init_admin() {
      if ( !is_admin() ) {
        return;
      }
      MVIFileAttachment\CustomAdminColumn::register(); //Create the custom admin column
      MVIFileAttachment\Settings::register(); //Create the settings page
      MVIFileAttachment\CustomFunctions\ApplyTaxonomy::register(); //Apply the taxonomy every time a post is saved

    }

    public static function on_activation() {
      MVIFileAttachment\CustomTable::create_table(); //Create the custom DB table for storing fields
      MVIFileAttachment\CustomFunctions\CsvExport::activate_weekly_export(); //schedue the export function
    }

    public static function on_deactivation() {
      MVIFileAttachment\CustomFunctions\CsvExport::deactivate_weekly_export();
    }
  }

  add_action('plugins_loaded', array('MVIFileAttachmentBase', 'register'));

}
