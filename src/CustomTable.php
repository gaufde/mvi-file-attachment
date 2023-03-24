<?php

namespace MVIFileAttachment;

use DateTime;
use DateTimeZone;
use WP_Error;

//Create a Meta Box custom table.
class CustomTable
{
  protected const PLUGIN_PREFIX = \MVIFileAttachmentBase::PLUGIN_PREFIX;

  public function __construct()
  {
  }

  public static function get_id()
  {
    global $wpdb;
    $custom_table_id = $wpdb->prefix . self::PLUGIN_PREFIX . "submissions";
    return $custom_table_id;
  }

  public static function get_db_ver()
  {
    if (get_option(self::PLUGIN_PREFIX . "db_version")) {
      $db_ver = get_option(self::PLUGIN_PREFIX . "db_version");
    } else {
      $db_ver = false;
    }

    return $db_ver;
  }

  public static function set_db_ver()
  {
    global $wpdb;
    $table_name = self::get_id();
    $current_db_ver = self::table_db_version();

    $table_exists = $wpdb->get_var("SHOW TABLES LIKE '{$table_name}'") === $table_name; //bool check if table exists

    if ($table_exists) {
      $current_columns = $wpdb->get_col("SHOW COLUMNS FROM {$table_name};", 0);
      array_shift($current_columns); //remove the ID column since we don't define that
      $intended_columns = array_keys(self::table_columns());

      if ($current_columns == $intended_columns) {

        if (self::get_db_ver() == $current_db_ver) {
          $result = true;
          //error_log ("The table db version number ({$current_db_ver}) for {$table_name} is already up-to-date.");
        } elseif (self::get_db_ver()) {
          update_option(self::PLUGIN_PREFIX . "db_version", $current_db_ver);
          $result = true;
          //error_log("The table db version number ({$current_db_ver}) for {$table_name} was updated.");
        } else {
          add_op(self::PLUGIN_PREFIX . "db_version", $current_db_ver);
          $result = true;
          //error_log("The table db version number ({$current_db_ver}) for {$table_name} was added.");
        }
      } else {
        $result = new WP_Error('table_column_error', __("The table columns for {$table_name} do not match."));
      }
    } else {
      $result = new WP_Error('table_error', __("The table {$table_name} does not exist."));
    }

    if (is_wp_error($result)) {
      $errors = $result->get_error_messages();
      foreach ($errors as $error) {
        error_log($error);
      }
    }

    return $result;
  }

  public static function update_table()
  {
    global $wpdb;
    $table_name = self::get_id();
    $old_db_ver = self::get_db_ver();
    $current_db_ver = self::table_db_version();

    $table_exists = $wpdb->get_var("SHOW TABLES LIKE '{$table_name}'") === $table_name; //bool check if table exists
    if ($table_exists) {
      if ($old_db_ver != $current_db_ver) {

        $tstamp_column = self::PLUGIN_PREFIX . 'tstamp';
        $date_time_column = self::PLUGIN_PREFIX . 'date_time';
        $column_location = self::PLUGIN_PREFIX . 'download_count';

        $tstamp_column_exists = $wpdb->query("SHOW COLUMNS FROM {$table_name} LIKE '{$tstamp_column}'");

        if ($tstamp_column_exists) {
          $date_time_column_exists = $wpdb->query("SHOW COLUMNS FROM {$table_name} LIKE '{$date_time_column}'");

          if (!$date_time_column_exists) {
            // Define the SQL statement to add the new column
            $sql = "ALTER TABLE {$table_name} ADD COLUMN {$date_time_column} DATETIME AFTER {$column_location}";

            // Run dbDelta to add the new column
            $wpdb->query($sql);
          }

          // Get the data from the old column and process it
          $rows = $wpdb->get_results("SELECT id, $tstamp_column FROM {$table_name}");
          // Check if the query was successful
          if (false == $rows) {
            $result = new WP_Error('database_error', __('Database error: Unable to get results from old column.'));
          } else {
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
                $result = new WP_Error('database_error', __('Database error: Unable to update new column with processed data.'));
                break;
              }
            }

            // Drop the old column
            $drop_result = $wpdb->query("ALTER TABLE $table_name DROP COLUMN {$tstamp_column}");

            // Check if the query was successful
            if (false === $drop_result) {
              $result = new WP_Error('database_error', __('Database error: Unable to drop old column.'));
            } else {
              $result = true;
            }
          }
        } else {
          $result = new WP_Error('column_not_found_error', __('The old column was not found.'));
        }
      } else {
        //$result = new WP_Error('table_version_error', __('The table is already up to date.'));
        $result = true;
      }
    } else {
      $result = new WP_Error('table_not_found_error', __('The table was not found.'));
    }

    if (is_wp_error($result)) {
      $errors = $result->get_error_messages();
      foreach ($errors as $error) {
        error_log($error);
      }
    }

    self::set_db_ver();
    return $result;
  }

  public static function table_db_version()
  {
    return 1; //Define the current version here.
  }

  public static function table_columns()
  {
    return array(
      self::PLUGIN_PREFIX . 'first_name' => 'TEXT NOT NULL',
      self::PLUGIN_PREFIX . 'last_name'   => 'TEXT NOT NULL',
      self::PLUGIN_PREFIX . 'email'   => 'VARCHAR(100) NOT NULL',
      self::PLUGIN_PREFIX . 'professional_role'   => 'VARCHAR(100) NOT NULL',
      self::PLUGIN_PREFIX . 'country_code'   => 'TEXT NOT NULL',
      self::PLUGIN_PREFIX . 'phone'   => 'TEXT NOT NULL',
      self::PLUGIN_PREFIX . 'subscribe' => 'INT(20) NOT NULL',
      self::PLUGIN_PREFIX . 'reference_post_id'   => 'BIGINT(20) NOT NULL',
      self::PLUGIN_PREFIX . 'download_name'   => 'TEXT NOT NULL',
      self::PLUGIN_PREFIX . 'download_id'   => 'CHAR(40) NOT NULL',
      self::PLUGIN_PREFIX . 'download_count'   => 'INT(20) NOT NULL',
      self::PLUGIN_PREFIX . 'date_time'   => 'DATETIME',
      self::PLUGIN_PREFIX . 'export_count'   => 'INT(20) NOT NULL',


    );
  }

  //Create a MB custom table to store data in the database
  public static function create_table()
  {
    global $wpdb;
    if (!class_exists('MB_Custom_Table_API')) {
      echo "no class here";
      return;
    }

    $table_name = self::get_id();
    $table_exists = $wpdb->get_var("SHOW TABLES LIKE '{$table_name}'") === $table_name; //bool check if table exists

    if (!$table_exists) {
      \MB_Custom_Table_API::create(self::get_id(), self::table_columns(), array(self::PLUGIN_PREFIX . 'download_id', self::PLUGIN_PREFIX . 'email')); //set a SQL key to make retrieval more performant.
    }
  }

  public static function get_values_no_prefix($post_id)
  {
    if (!class_exists('MB_Custom_Table_API')) {
      echo "no class here";
      return;
    }

    $data_array = \MB_Custom_Table_API::get($post_id, self::get_id());



    $data_array_no_prefix = self::stripArrayKeyPrefix($data_array, self::PLUGIN_PREFIX);
    return $data_array_no_prefix;
  }

  private static function stripArrayKeyPrefix(array $input, string $prefix)
  {
    $return = array();
    foreach ($input as $key => $value) {
      if (strpos($key, $prefix) === 0) {
        $key = substr($key, strlen($prefix));
      }

      $return[$key] = $value;
    }
    return $return;
  }


  public static function update_values_no_prefix($post_id, array $data)
  {
    if (!class_exists('MB_Custom_Table_API')) {
      echo "no class here";
      return;
    }

    $data_with_prefix = self::addArrayPrefix($data, self::PLUGIN_PREFIX);

    \MB_Custom_Table_API::update($post_id, self::get_id(), $data_with_prefix);
  }

  private static function addArrayPrefix(array $input, string $prefix)
  {
    $return = array();
    foreach ($input as $key => $value) {

      if (substr($key, 0, strlen($prefix)) !== $prefix && $key != 'ID') {
        $key = $prefix . $key;
      }

      $return[$key] = $value;
    }
    return $return;
  }

  /**
   * @param string $dl_id
   * Get the post id of a post associated with a certain dl_id.
   *
   * @return int $post_id
   */
  public static function get_submission_post_id(string $dl_id)
  {

    $ids = NULL;
    //test if input matches expected form.
    if (preg_match(',[0-9a-fA-F]{40},', $dl_id) == 1) {
      global $wpdb;
      $table = self::get_id();
      $prepared_sql = $wpdb->prepare("SELECT ID FROM $table WHERE " . \MVIFileAttachmentBase::PLUGIN_PREFIX . "download_id = %s", $dl_id);
      $ids = $wpdb->get_col($prepared_sql);
    }

    //return post_id or 0
    if ($ids) {
      return intval($ids[0]);
    }
    return intval(0);
  }


  /**
   * @param int $post_id
   * Get the file associated with a submission.
   *
   * @return array $file
   */
  public static function get_submission_file(int $post_id)
  {

    $file = [];
    $data = self::get_values_no_prefix($post_id);

    if ($data) {
      $reference_post_id = $data['reference_post_id'];
      $files = rwmb_meta(\MVIFileAttachmentBase::PLUGIN_PREFIX . 'post_download_file', array('limit' => 1), $reference_post_id);

      if ($files) {
        $file = reset($files);
      }
    }

    return $file;
  }

  /**
   * Get the reference_post_url associated with a submission.
   * @param int $post_id
   *
   *
   * @return string $url
   */
  public static function get_submission_url(int $post_id)
  {

    $reference_post_url = NULL;
    $data = self::get_values_no_prefix($post_id);

    if ($data) {
      $reference_post_id = $data['reference_post_id'];
      $reference_post_url = get_permalink($reference_post_id);
    }

    return $reference_post_url;
  }

  /**
   * @param int $post_id
   * Check if the submission is still valid based off the date_time
   *
   * @return bool
   */
  public static function is_submission_valid(int $post_id)
  {

    $data = self::get_values_no_prefix($post_id);

    if ($data) {
      $timezone = new DateTimeZone(wp_timezone_string());
      $submission_time = DateTime::createFromFormat('Y-m-d H:i:s', $data['date_time'], $timezone);
      $current_time = new DateTime('now', $timezone);

      if ($current_time->diff($submission_time)->format('%d') < 1) {
        return true;
      }
    }

    return false;
  }

  /**
   * Increment counters in database
   * @param int $post_id
   * @param string $field_id
   *
   *
   */
  public static function increment_field(int $post_id, string $field_id)
  {
    $field_val = NULL;

    $data = self::get_values_no_prefix($post_id);

    if ($data[$field_id] != NULL) {
      $field_val = intval($data[$field_id]);
      $field_val = $field_val + 1;

      $data = [
        $field_id => $field_val,
      ];

      self::update_values_no_prefix($post_id, $data);
    }

    return;
  }
}
