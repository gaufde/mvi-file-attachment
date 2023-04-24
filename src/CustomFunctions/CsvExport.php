<?php

namespace MVIWebinarRegistration\CustomFunctions;

class CsvExport
{

  public function __construct()
  {
  }

  //syntax is TableToCSV('table-name without WPDB prefix','separator character','file name');
  public static function run_export_file()
  {
    $table = \MVIWebinarRegistration\CustomTable::get_id();
    $exportCSV = new TableToCSV($table, ',', 'webinar_registration_user_information');
  }

  public static function activate_weekly_export()
  {
    if (!wp_next_scheduled(\MVIWebinarRegistrationBase::PLUGIN_PREFIX . 'export_file')) {
      wp_schedule_event(strtotime('Monday 16:00'), 'weekly', \MVIWebinarRegistrationBase::PLUGIN_PREFIX . 'export_file');
    }
  }

  public static function deactivate_weekly_export()
  {
    wp_clear_scheduled_hook(\MVIWebinarRegistrationBase::PLUGIN_PREFIX . 'export_file');
  }
}
