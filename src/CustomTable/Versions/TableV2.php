<?php

namespace MVIFileAttachment\CustomTable\Versions;





class TableV2 extends Table implements TableVersionInterface
{
  /**
   * Define the version number for this version of the db table
   * 
   * @return int
   */
  public static function define_table_version()
  {
    return 2; //Define the current version here.
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
      \MVIFileAttachmentBase::PLUGIN_PREFIX . 'url_params'   => 'JSON NOT NULL',
      \MVIFileAttachmentBase::PLUGIN_PREFIX . 'export_count'   => 'INT(20) NOT NULL',
    );
  }

  public function update_table(): bool
  {
    global $wpdb;
    $new_col = \MVIFileAttachmentBase::PLUGIN_PREFIX . 'url_params';
    $column_location = \MVIFileAttachmentBase::PLUGIN_PREFIX . 'date_time';
    $result = false;

    if (\MVIFileAttachment\CustomTable::get_db_ver() == $this->version - 1) {
      
      try {
        $url_params = self::column_exists($new_col);
        if (!$url_params){
          // Define the SQL statement to add the new column
          $sql = "ALTER TABLE {$this->id} ADD COLUMN {$new_col} JSON AFTER {$column_location}";
          // Run query to add the new column
          $query = $wpdb->query($sql);

          if($query) {
            $sql = "UPDATE {$this->id} SET {$new_col} = '{}' WHERE {$new_col} IS NULL";
            $query = $wpdb->query($sql);
            if ($query){
              $sql = "ALTER TABLE {$this->id} MODIFY COLUMN {$new_col} JSON NOT NULL";
              $query = $wpdb->query($sql);
            }
          }

          $result = self::set_db_ver( self::define_table_version() );
        }
      } catch (\Exception $e) {
        error_log( "Error on line " . $e->getLine() . " in file " . $e->getFile() . ": " . $e->getMessage());
        exit;
      }
    }
    return $result;
  }
}
