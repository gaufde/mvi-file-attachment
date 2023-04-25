<?php
/*
Plugin Name: MVI File Attachment
Description: Plugin to allow files to be attached to posts and automatically create email capture forms to handle download requests.
Version: 2.2.0
Author: Graceson Aufderheide
License: GPLv2 or later
Text Domain: mvi-file-attachment
*/


if (!defined('ABSPATH')) {
  exit;
  // Exit if accessed directly.
}

/********************
Autoload/require dependencies
 ********************/
require("vendor/autoload.php");

// Throw a admin notice and don't run if MetaBox and the required extenions are not active. 
require_once ABSPATH . 'wp-admin/includes/plugin.php';
$required_plugins = [
  'Meta Box' => is_plugin_active('meta-box/meta-box.php'),
  'MB Admin Columns' => is_plugin_active('mb-admin-columns/mb-admin-columns.php'),
  'MB Custom Table' => is_plugin_active('mb-frontend-submission/mb-frontend-submission.php'),
  'MB Frontend Submission' => is_plugin_active('mb-settings-page/mb-settings-page.php'),
  'MB Settings Page' => is_plugin_active('mb-settings-page/mb-settings-page.php'),
  'Meta Box Columns ' => is_plugin_active('meta-box-columns/meta-box-columns.php'),
  'Meta Box Tooltip ' => is_plugin_active('meta-box-tooltip/meta-box-tooltip.php'),
];

$required_plugins_alt = [
  'Meta Box' => is_plugin_active('meta-box/meta-box.php'),
  'Meta Box AIO' => is_plugin_active('meta-box-aio/meta-box-aio.php'),
];
if (is_plugin_active('meta-box-aio/meta-box-aio.php')) {
  $required_plugins = $required_plugins_alt;
}

$is_plugin_missing = (in_array(false, $required_plugins));

if ($is_plugin_missing) {
  add_action(
    'admin_notices',
    function () {
      global $required_plugins;
      global $required_plugins_alt;
      $plugin_list = '<p>';
      foreach ($required_plugins as $plugin => $status) {
        $status_string = $status ? __('(active)', 'mvi-file-attachment') : __('(missing or deactivated)', 'mvi-file-attachment');
        $plugin_list .= "$plugin <code>$status_string</code><br>";
      }
      $plugin_list .= '</p><p><strong>' . __('or as a alternate setup', 'mvi-file-attachment') . '</strong></p><p>';

      foreach ($required_plugins_alt as $plugin => $status) {
        $status_string = $status ? __('(active)', 'mvi-file-attachment') : __('(missing or deactivated)', 'mvi-file-attachment');
        $plugin_list .= "$plugin <code>$status_string</code><br>";
      }
      $plugin_list .= '</p>';
      echo "<div class='notice notice-error'>
        <p><strong>" . __('MVI File Attachment requires the following plugins', 'mvi-file-attachment') . "</strong></p>
        $plugin_list
        </div>";
    }
  );
  return;
}

if (!defined('RWMB_VER')) {
  return;
}

/********************
Begin plugin
 ********************/
register_activation_hook(__FILE__, ['MVIFileAttachmentBase', 'on_activation']);
register_deactivation_hook(__FILE__, ['MVIFileAttachmentBase', 'on_deactivation']);

if (!class_exists('MVIFileAttachmentBase')) {

  class MVIFileAttachmentBase
  {
    const PLUGIN_PREFIX = 'mvi_fa_';
    const VERSION_NO = '2.2.0';

    /**
     * Registers this class with WordPress.
     */
    public static function register()
    {
      $plugin = new self();
      add_action('init', [$plugin, 'on_init']);
      add_action('init', [$plugin, 'on_init_admin'], 5); //For some reason needs to be high-priority init hook
      add_action(self::PLUGIN_PREFIX . 'export_file', ['MVIFileAttachment\CustomFunctions\CsvExport', 'run_export_file']); //add the CSV export action for the event that is scheduled in on_activation()
      self::on_plugins_loaded();
    }

    public function on_init()
    {
      MVIFileAttachment\PostType::register(); //Create the post type for storing downloads
      MVIFileAttachment\Taxonomy::register(); //Create a custom taxonomy for tracking if a file exists
      MVIFileAttachment\Fields::register(); //Create all the custom fields
      MVIFileAttachment\VirtualDownloadPage\Controller::register(new MVIFileAttachment\VirtualDownloadPage\TemplateLoader); //Create the virtual download page
      MVIFileAttachment\Shortcode::register(); //load assets, prepare shortcode for displaying the custom download form
      MVIFileAttachment\CustomFunctions\ProcessSubmission::register();
    }

    public function on_init_admin()
    {
      if (!is_admin()) {
        return;
      }
      MVIFileAttachment\CustomAdminColumn::register(); //Create the custom admin column
      MVIFileAttachment\Settings::register(); //Create the settings page
      MVIFileAttachment\CustomFunctions\ApplyTaxonomy::register(); //Apply the taxonomy every time a post is saved

    }

    public static function on_activation()
    {
      MVIFileAttachment\CustomTable::create_table(); //Create the custom DB table for storing fields
      MVIFileAttachment\CustomFunctions\CsvExport::activate_weekly_export(); //schedue the export function
    }

    public static function on_deactivation()
    {
      MVIFileAttachment\CustomFunctions\CsvExport::deactivate_weekly_export();
    }

    public static function on_plugins_loaded()
    {
      MVIFileAttachment\CustomTable::update_table();
    }
  }

  add_action('plugins_loaded', array('MVIFileAttachmentBase', 'register'));
}
