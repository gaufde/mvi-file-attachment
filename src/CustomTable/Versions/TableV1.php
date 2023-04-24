<?php

namespace MVIWebinarRegistration\CustomTable\Versions;

class TableV1 extends Table implements TableVersionInterface
{
  /**
   * Define the version number for this version of the db table
   * 
   * @return int
   */
  public static function define_table_version()
  {
    return 1; //Define the current version here.
  }

  /**
   * Define the columns for this version of the db table
   * 
   * @return array
   */
  public static function define_table_columns()
  {
    return array(
      \MVIWebinarRegistrationBase::PLUGIN_PREFIX . 'first_name' => 'TEXT NOT NULL',
      \MVIWebinarRegistrationBase::PLUGIN_PREFIX . 'last_name'   => 'TEXT NOT NULL',
      \MVIWebinarRegistrationBase::PLUGIN_PREFIX . 'email'   => 'VARCHAR(100) NOT NULL',
      \MVIWebinarRegistrationBase::PLUGIN_PREFIX . 'professional_role'   => 'VARCHAR(100) NOT NULL',
      \MVIWebinarRegistrationBase::PLUGIN_PREFIX . 'country_code'   => 'TEXT NOT NULL',
      \MVIWebinarRegistrationBase::PLUGIN_PREFIX . 'phone'   => 'TEXT NOT NULL',
      \MVIWebinarRegistrationBase::PLUGIN_PREFIX . 'subscribe' => 'INT(20) NOT NULL',
      \MVIWebinarRegistrationBase::PLUGIN_PREFIX . 'reference_post_id'   => 'BIGINT(20) NOT NULL',
      \MVIWebinarRegistrationBase::PLUGIN_PREFIX . 'url_params'   => 'JSON NOT NULL',
      \MVIWebinarRegistrationBase::PLUGIN_PREFIX . 'shortcode_atts'   => 'JSON NOT NULL',
      \MVIWebinarRegistrationBase::PLUGIN_PREFIX . 'date_time'   => 'DATETIME NOT NULL',
      \MVIWebinarRegistrationBase::PLUGIN_PREFIX . 'export_count'   => 'INT(20) NOT NULL',
    );
  }

  /**
   * Update to this table structure from the previous one.
   * 
   * 
   */
  public function update_table(): bool
  {
    return false;
  }
}
