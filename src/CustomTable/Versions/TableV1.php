<?php

namespace MVIFileAttachment\CustomTable\Versions;

use DateTime;
use DateTimeZone;
use WP_Error;

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
      \MVIFileAttachmentBase::PLUGIN_PREFIX . 'first_name' => 'TEXT NOT NULL',
      \MVIFileAttachmentBase::PLUGIN_PREFIX . 'last_name'   => 'TEXT NOT NULL',
      \MVIFileAttachmentBase::PLUGIN_PREFIX . 'email'   => 'VARCHAR(100) NOT NULL',
      \MVIFileAttachmentBase::PLUGIN_PREFIX . 'professional_role'   => 'VARCHAR(100) NOT NULL',
      \MVIFileAttachmentBase::PLUGIN_PREFIX . 'country_code'   => 'TEXT NOT NULL',
      \MVIFileAttachmentBase::PLUGIN_PREFIX . 'phone'   => 'TEXT NOT NULL',
      \MVIFileAttachmentBase::PLUGIN_PREFIX . 'subscribe' => 'INT(20) NOT NULL',
      \MVIFileAttachmentBase::PLUGIN_PREFIX . 'reference_post_id'   => 'BIGINT(20) NOT NULL',
      \MVIFileAttachmentBase::PLUGIN_PREFIX . 'download_name'   => 'TEXT NOT NULL',
      \MVIFileAttachmentBase::PLUGIN_PREFIX . 'download_id'   => 'CHAR(40) NOT NULL',
      \MVIFileAttachmentBase::PLUGIN_PREFIX . 'download_count'   => 'INT(20) NOT NULL',
      \MVIFileAttachmentBase::PLUGIN_PREFIX . 'date_time'   => 'DATETIME NOT NULL',
      \MVIFileAttachmentBase::PLUGIN_PREFIX . 'export_count'   => 'INT(20) NOT NULL',
    );
  }

  /**
   * Update to this table structure from the previous one.
   * 
   * 
   */
  public function update_table(): bool
  {
    global $wpdb;
    $table_name = $this->id;
    $tstamp_column = \MVIFileAttachmentBase::PLUGIN_PREFIX . 'tstamp';
    $date_time_column = \MVIFileAttachmentBase::PLUGIN_PREFIX . 'date_time';
    $column_location = \MVIFileAttachmentBase::PLUGIN_PREFIX . 'download_count';
    $result = false;

    if (\MVIFileAttachment\CustomTable::get_db_ver() == $this->version - 1) {
      try {
        $tstamp_column_exists = self::column_exists($tstamp_column);
        $date_time_column_exists = self::column_exists($date_time_column);

        // create the new column if it doesn't exist
        if (!$date_time_column_exists) {
          // Define the SQL statement to add the new column
          $sql = "ALTER TABLE {$table_name} ADD COLUMN {$date_time_column} DATETIME AFTER {$column_location}";

          // Run query to add the new column
          $wpdb->query($sql);
        }

        if ($tstamp_column_exists && $date_time_column_exists) {
          // Get the data from the old column and process it
          $rows = $wpdb->get_results("SELECT id, $tstamp_column FROM {$table_name}");
          // Check if the query was successful
          try {
            if (false == $rows) {
              throw new \Exception('Database error: Unable to get results from old column.');
            }

            foreach ($rows as $row) {
              $timezone = new DateTimeZone(wp_timezone_string());
              $new_value = new DateTime('@' . $row->$tstamp_column);
              $new_value->setTimezone($timezone);
              // Update the new column with the processed data
              $update_result = $wpdb->update(
                $table_name,
                array($date_time_column => $new_value->format('Y-m-d H:i:s')),
                array('id' => $row->id)
              );

              // Check if the query was successful
              if (false === $update_result) {
                throw new \Exception('Database error: Unable to update new column with processed data.');
              }
            }


            $sql = "ALTER TABLE {$this->id} MODIFY COLUMN {$date_time_column} DATETIME NOT NULL";
            $query = $wpdb->query($sql);

            if (false === $query) {
              throw new \Exception('Database error: Unable to make column NOT NULL');
            }

          } catch (\Exception $e) {
            error_log("Error on line " . $e->getLine() . " in file " . $e->getFile() . ": " . $e->getMessage());
            die;
          }

          // Drop the old column
          $drop_result = $wpdb->query("ALTER TABLE $table_name DROP COLUMN {$tstamp_column}");

          // Check if the query was successful
          if (false === $drop_result) {
            throw new \Exception('Database error: Unable to drop old column.');
          } else {
            $result = self::set_db_ver(self::define_table_version());
          }
        }
      } catch (\Exception $e) {
        error_log("Error on line " . $e->getLine() . " in file " . $e->getFile() . ": " . $e->getMessage());
      }
    }
    return $result;
  }
}
