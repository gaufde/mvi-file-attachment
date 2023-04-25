<?php

namespace MVIFileAttachment;

use DateTime;
use DateTimeZone;

//Create a Meta Box custom table.
class CustomTable
{
  public function __construct()
  {
  }

  public static function get_id()
  {
    $table = self::get_table();
    $id = $table->id;
    return $id;
  }

  /**
   * Get the table version saved in WP.
   * 
   * @return int
   */
  public static function get_db_ver()
  {
    $db_ver = get_option(\MVIFileAttachmentBase::PLUGIN_PREFIX . "db_version") ? get_option(\MVIFileAttachmentBase::PLUGIN_PREFIX . "db_version") : 0; //return zero if there is no pre-defined version
    return $db_ver;
  }

  /**
   * Update the db table
   * 
   * @return bool
   */
  public static function update_table()
  {
    $table = self::get_table();
    $result = false;
    if (!$table->exists){
      return; //return if the table doesn't exist
    }
    while (self::get_db_ver() != $table->version) {
      try {
        $updater = \MVIFileAttachment\CustomTable\TableUpdaterFactory::create_table_updater(self::get_db_ver());
        $result = $updater->update_table();
        
        //make sure the loop doesn't run forever
        if ( $result === false ) {
          throw new \Exception("The table wasn't updated properly");
        }
      } catch (\Exception $e) {
        error_log("Error on line " . $e->getLine() . " in file " . $e->getFile() . ": " . $e->getMessage());
        return;
      }
    }

    return $result;
  }

  /**
   * Create a MB custom table to store data in the database
   * 
   * @return bool
   */
  public static function create_table()
  {
    if (!class_exists('MB_Custom_Table_API')) {
      echo "no class here";
      return;
    }

    $table = self::get_table();
    $id = $table->id;
    $table_exists = $table->exists;
    $columns = $table->columns;
    $keys = $table->keys;

    if (!$table_exists) {
      \MB_Custom_Table_API::create($id, $columns, $keys); //set a SQL key to make retrieval more performant.
      $table::set_db_ver($table->version);
    }
  }

  /**
   * Get the options for the current version of the db table.
   * 
   * @return  $options
   */
  public static function get_table()
  {
    $options = [];
    $number = self::get_table_version();

    $class_name = "\MVIFileAttachment\CustomTable\Versions\TableV$number";
    if (class_exists($class_name)) {
      $table = new $class_name;
    }

    return $table;
  }

  /**
   * Look in the table versions folder to find the latest version
   * 
   * @return int
   */
  private static function get_table_version()
  {
    $directory = dirname(__FILE__) . '/CustomTable/Versions/'; //relative to this file
    $largest_num = 0;
    $largest_file = '';

    // Open the directory
    if ($handle = opendir($directory)) {

      // Loop through the files
      while (false !== ($file = readdir($handle))) {

        // Only process files
        if ($file != "." && $file != ".." && !is_dir($directory . $file)) {

          // Get the integer from the file name
          $num = intval(preg_replace('/[^0-9]+/', '', $file));

          // Check if this is the largest integer so far
          if ($num > $largest_num) {
            $largest_num = $num;
            $largest_file = $file;
          }
        }
      }

      // Close the directory
      closedir($handle);
    }

    // Output the file name with the largest integer
    return $largest_num;
  }

  /**
   * Get values for a post without the prefix
   * 
   * @return array
   */
  public static function get_values_no_prefix($post_id)
  {
    if (!class_exists('MB_Custom_Table_API')) {
      echo "no class here";
      return;
    }

    $data_array = \MB_Custom_Table_API::get($post_id, self::get_id());



    $data_array_no_prefix = self::stripArrayKeyPrefix($data_array, \MVIFileAttachmentBase::PLUGIN_PREFIX);
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

    $data_with_prefix = self::addArrayPrefix($data, \MVIFileAttachmentBase::PLUGIN_PREFIX);

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
