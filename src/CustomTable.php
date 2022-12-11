<?php
namespace MVIFileAttachment;

//Create a Meta Box custom table.
class CustomTable {
    protected const PLUGIN_PREFIX = \MVIFileAttatchmentBase::PLUGIN_PREFIX;

    public function __construct() {

    }

    public static function get_id() {
      global $wpdb;
      $custom_table_id = $wpdb->prefix . self::PLUGIN_PREFIX . "submissions";
      return $custom_table_id;
    }

    //Create a MB custom table to store data in the database
    public static function create_table() {
        if ( ! class_exists( 'MB_Custom_Table_API' ) ) {
            echo "no class here";
            return;
        }

        \MB_Custom_Table_API::create( self::get_id(), array(
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
            self::PLUGIN_PREFIX . 'tstamp'   => 'INTEGER UNSIGNED NOT NULL',
            self::PLUGIN_PREFIX . 'export_count'   => 'INT(20) NOT NULL',


        ), array( self::PLUGIN_PREFIX . 'download_id', self::PLUGIN_PREFIX . 'email' ) ); //set a SQL key to make retrieval more performant.
    }

    public static function get_values_no_prefix($post_id) {
      if ( ! class_exists( 'MB_Custom_Table_API' ) ) {
          echo "no class here";
          return;
      }

      $data_array = \MB_Custom_Table_API::get( $post_id, self::get_id() );

      function stripArrayKeyPrefix(array $input, string $prefix) {
        $return = array();
        foreach ($input as $key => $value) {
          if (strpos($key, $prefix) === 0) {
            $key = substr($key, strlen($prefix));
          }

          $return[$key] = $value;
        }
        return $return;
      }

      $data_array_no_prefix = stripArrayKeyPrefix($data_array, self::PLUGIN_PREFIX);
      return $data_array_no_prefix;
    }



    public static function update_values_no_prefix($post_id, array $data) {
      if ( ! class_exists( 'MB_Custom_Table_API' ) ) {
          echo "no class here";
          return;
      }

      function addArrayPrefix(array $input, string $prefix) {
        $return = array();
        foreach ($input as $key => $value) {

          if (substr( $key, 0, strlen($prefix) ) !== $prefix && $key != 'ID') {
            $key = $prefix . $key;
          }

          $return[$key] = $value;
        }
        return $return;
      }

      $data_with_prefix = addArrayPrefix($data, self::PLUGIN_PREFIX);

      \MB_Custom_Table_API::update( $post_id, self::get_id(), $data_with_prefix );

    }



}
