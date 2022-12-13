<?php
namespace MVIFileAttachment;

class ServeFile {

    public function __construct( ) {

    }

    /**
     * Serve the file to the user. Will increment download_count on the submission if optional $post_id parameter is provided
     * @param string $file_path
     * @param int $post_id
     *
     */
    public static function output( string $file_path, int $post_id = NULL ) {
      $mimetype = "application/pdf";
      header("Content-Type: " . $mimetype );

      while (ob_get_level()) {
          ob_end_clean();
      }
      readfile($file_path);

      if ( $post_id ){
        \MVIFileAttachment\CustomTable::increment_field ( $post_id, 'download_count' );
      }
    }
}
