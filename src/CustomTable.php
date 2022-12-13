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

    public static function get_values_no_prefix( $post_id ) {
      if ( ! class_exists( 'MB_Custom_Table_API' ) ) {
          echo "no class here";
          return;
      }

      $data_array = \MB_Custom_Table_API::get( $post_id, self::get_id() );



      $data_array_no_prefix = self::stripArrayKeyPrefix($data_array, self::PLUGIN_PREFIX);
      return $data_array_no_prefix;
    }

    private static function stripArrayKeyPrefix(array $input, string $prefix) {
      $return = array();
      foreach ($input as $key => $value) {
        if (strpos($key, $prefix) === 0) {
          $key = substr($key, strlen($prefix));
        }

        $return[$key] = $value;
      }
      return $return;
    }


    public static function update_values_no_prefix($post_id, array $data) {
      if ( ! class_exists( 'MB_Custom_Table_API' ) ) {
          echo "no class here";
          return;
      }

      $data_with_prefix = self::addArrayPrefix($data, self::PLUGIN_PREFIX);

      \MB_Custom_Table_API::update( $post_id, self::get_id(), $data_with_prefix );

    }

    private static function addArrayPrefix(array $input, string $prefix) {
      $return = array();
      foreach ($input as $key => $value) {

        if (substr( $key, 0, strlen($prefix) ) !== $prefix && $key != 'ID') {
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
    public static function get_submission_post_id( string $dl_id ) {

      $ids = NULL;
      //test if input matches expected form.
      if ( preg_match( ',[0-9a-fA-F]{40},', $dl_id ) == 1 ){
        global $wpdb;
        $table = self::get_id();
        $prepared_sql = $wpdb->prepare( "SELECT ID FROM $table WHERE " . \MVIFileAttatchmentBase::PLUGIN_PREFIX . "download_id = %s", $dl_id );
        $ids = $wpdb->get_col( $prepared_sql );
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
    public static function get_submission_file( int $post_id ) {

      $file = [];
      $data = self::get_values_no_prefix( $post_id );

      if ( $data ) {
        $reference_post_id = $data['reference_post_id'];
        $files = rwmb_meta( \MVIFileAttatchmentBase::PLUGIN_PREFIX . 'post_download_file', array( 'limit' => 1 ), $reference_post_id);

        if ( $files ) {
          $file = reset( $files );
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
    public static function get_submission_url( int $post_id ) {

      $reference_post_url = NULL;
      $data = self::get_values_no_prefix( $post_id );

      if ( $data ) {
        $reference_post_id = $data['reference_post_id'];
        $reference_post_url = get_permalink( $reference_post_id);
      }

      return $reference_post_url;
    }

    /**
     * @param int $post_id
     * Check if the submission is still valid based off the tstamp
     *
     * @return bool
     */
    public static function is_submission_valid ( int $post_id ) {

      $data = self::get_values_no_prefix( $post_id );

      if ( $data ) {
        $tstamp = $data['tstamp'];

        //1 day measured in seconds = 86400
        if( $_SERVER["REQUEST_TIME"] - $tstamp < 86400) {
          return true;
        }
      }

      return false;
    }

    /**
     * Check if the submission is still valid based off the tstamp
     * @param int $post_id
     * @param string $field_id
     *
     *
     */
    public static function increment_field ( int $post_id, string $field_id ) {
      $field_val = NULL;

      $data = self::get_values_no_prefix( $post_id );

      if ( $data[$field_id] != NULL ){
        $field_val = intval( $data[$field_id] );
        $field_val = $field_val + 1;

        $data = [
            $field_id => $field_val,
        ];

        self::update_values_no_prefix( $post_id, $data );
      }

      return;
    }

}
