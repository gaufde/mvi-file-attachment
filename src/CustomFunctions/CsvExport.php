<?php

namespace MVIFileAttachment\CustomFunctions;

class CsvExport
{

  public function __construct()
  {
  }

  //syntax is TableToCSV('table-name without WPDB prefix','separator character','file name');
  public static function run_export_file()
  {
    $table = \MVIFileAttachment\CustomTable::get_id();
    $exportCSV = new TableToCSV($table, ',', 'file_downloads_user_information');
  }

  public static function activate_weekly_export()
  {
    if (!wp_next_scheduled(\MVIFileAttachmentBase::PLUGIN_PREFIX . 'export_file')) {
      wp_schedule_event(strtotime('Monday 16:00'), 'weekly', \MVIFileAttachmentBase::PLUGIN_PREFIX . 'export_file');
    }
  }

  public static function deactivate_weekly_export()
  {
    wp_clear_scheduled_hook(\MVIFileAttachmentBase::PLUGIN_PREFIX . 'export_file');
  }
}
