<?php

namespace MVIFileAttachment\CustomTable\Versions;

use DateTime;
use DateTimeZone;
use WP_Error;

abstract class Table implements TableVersionInterface
{
    public $columns;
    public $version;
    public $id;
    public $exists;
    public $keys;

    public function __construct()
    {
        $this->columns = static::define_table_columns();
        $this->version = static::define_table_version();
        $this->id = static::get_id();
        $this->exists = static::table_exists();
        $this->keys = static::define_keys();
    }

    /**
     * Define the version number for this version of the db table
     * 
     * @return int
     */
    protected static function define_table_version()
    {
        return 0;
    }

    /**
     * Define the columns for this version of the db table
     * 
     * @return array
     */
    protected static function define_table_columns()
    {
        return [];
    }

    /**
     * Define the name of this table
     * 
     * @return string
     */
    protected static function get_id()
    {
        global $wpdb;
        $custom_table_id = $wpdb->prefix . \MVIFileAttachmentBase::PLUGIN_PREFIX . "submissions";
        return $custom_table_id;
    }

    /**
     * Check if the table exists
     * 
     * @return bool
     */
    protected static function table_exists()
    {
        global $wpdb;
        $table_name = static::get_id();
        return $wpdb->get_var("SHOW TABLES LIKE '{$table_name}'") === $table_name;
    }

    /**
     * Check column exists and check that the table exists
     * 
     * @param string $column
     * @return bool
     */
    protected static function column_exists(string $column)
    {
        global $wpdb;

        if (static::table_exists()) {
            $table_name = static::get_id();
            $query = $wpdb->query("SHOW COLUMNS FROM {$table_name} LIKE '{$column}'");
            return boolval($query);
        }
        throw new \Exception('Table not found');
    }

    /**
     * Define the keys for the table
     * 
     * @return array
     */
    protected static function define_keys()
    {
        return array(\MVIFileAttachmentBase::PLUGIN_PREFIX . 'download_id', \MVIFileAttachmentBase::PLUGIN_PREFIX . 'email');
    }

    /**
     * Check if the table structures match and then set the table version in WP options table.
     * 
     * @param int $new_db_version
     * @return bool
     */
    public static function set_db_ver($new_db_version): bool
    {
        $columns = static::define_table_columns();
        $table_name = static::get_id();
        $old_db_version = \MVIFileAttachment\CustomTable::get_db_ver();
        $result = false;
        try {
            $columns_match = static::check_columns_structure($columns);
            if ($columns_match) {

                if ($old_db_version == $new_db_version) {
                    $result = true;
                    throw new \Exception("The table db version number ({$new_db_version}) for {$table_name} is already up-to-date.");
                } elseif ($old_db_version) {
                    update_option(\MVIFileAttachmentBase::PLUGIN_PREFIX . "db_version", $new_db_version);
                    $result = true;
                    throw new \Exception("The table db version number for {$table_name} was updated from ({$old_db_version}) to ({$new_db_version}).");
                } else {
                    add_option(\MVIFileAttachmentBase::PLUGIN_PREFIX . "db_version", $new_db_version);
                    $result = true;
                    throw new \Exception("The table db version number for {$table_name} was added with value ({$new_db_version}).");
                }
            } else {
                throw new \Exception("The table columns for {$table_name} do not match.");
            }
        } catch (\Exception $e) {
            error_log("Error on line " . $e->getLine() . " in file " . $e->getFile() . ": " . $e->getMessage());
            return $result;
        }
        return $result;
    }

    /**
     * Check to see if the current table structure matches the desired table structure.
     * 
     * @param array $columns
     * 
     * @return bool
     */
    protected static function check_columns_structure($columns)
    {
        global $wpdb;
        $table_name = static::get_id();

        if (static::table_exists()) {
            $current_columns = $wpdb->get_col("SHOW COLUMNS FROM {$table_name}", 0);
            array_shift($current_columns); //remove the ID column since we don't define that
            $intended_columns = array_keys($columns);
            return $current_columns === $intended_columns;
        }
        throw new \Exception('Table not found');
    }

    /**
     * Update to this table structure from the previous one.
     * 
     * 
     */
    abstract public function update_table(): bool;
}
