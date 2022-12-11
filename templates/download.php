<?php

//SQL query MB custom table for the download_id in the url.
//https://hookturn.io/custom-wordpress-sql-queries-for-beginners/
global $wpdb;
$table = \MVIFileAttachment\CustomTable::get_id();
$prepared_sql = $wpdb->prepare( "SELECT ID FROM $table WHERE " . \MVIFileAttatchmentBase::PLUGIN_PREFIX . "download_id = %s", $dl_id );
$ids = $wpdb->get_col( $prepared_sql );

//function to output page content with desired title and desc. Based on Blocksy theme.
function output_download_page_content( $download_page_title, $download_page_desc, $download_page_link = false){
  get_header();
  error_log("download_page_ouput");
    ?>
    <div class="ct-container" <?php echo blocksy_get_v_spacing() ?>>
    	<section class="ct-no-results">

    		<section class="hero-section" data-type="type-1">
    			<header class="entry-header">
    				<h1 class="page-title" itemprop="headline">
    					<?php esc_html_e( "$download_page_title", 'blocksy' ); ?>
    				</h1>

    				<div class="page-description">
    					<?php esc_html_e( "$download_page_desc", 'blocksy' ); ?>
    				</div>
    			</header>
    		</section>

        <?php if( $download_page_link ):?>
      		<div class="entry-content">
            <div class="wp-block-buttons" style="display: flex; justify-content: center">
              <div class="wp-block-button">
                <a class="wp-block-button__link" href="<?php echo $download_page_link ?>">Get a new link!</a>
              </div>
            </div>
      		</div>
        <?php else: get_search_form();?>
        <?php endif; ?>
    	</section>
    </div>
    <?php
    get_footer();
}


//Begin building the page
if ( !empty( $ids ) ) {
  error_log("id match:" . json_encode($ids));
  $query = new WP_Query( [
      'post_type' => \MVIFileAttachment\PostType::get_id(),
      'post__in'  => $ids,
  ] );

  if ( $query->have_posts() ) {
      while ( $query->have_posts() ) {
        $query->the_post();
        //get some useful values
        $post_id = get_the_ID(); //can only be used in the loop
        error_log("id has post");
        $fields = MVIFileAttachment\CustomTable::get_values_no_prefix( $post_id );
        $reference_post_id = $fields['reference_post_id'];
        $download_count = $fields['download_count'];
        $reference_post_url = get_permalink( $reference_post_id);
        $tstamp = $fields['tstamp'];

        $files = rwmb_meta( \MVIFileAttatchmentBase::PLUGIN_PREFIX . 'post_download_file', array( 'limit' => 1 ), $reference_post_id);



        //Handle downloading of large files
        //https://stackoverflow.com/questions/6914912/streaming-a-large-file-using-php
        //https://stackoverflow.com/questions/3176942/using-php-to-download-files-not-working-on-large-files/21354337#21354337


          //check if link is valid
          //1 day measured in seconds = 86400
          if( $_SERVER["REQUEST_TIME"] - $tstamp < 86400){
            $link_valid = true;
          } else {
            $link_valid = false;
          }



          if ($link_valid) {
            if ( $files ) {
              $file = reset( $files );
              $file_path = $file['path'];
              $mimetype = "application/pdf";
              $download_count = $download_count + 1;

              $data = [
                  'download_count' => $download_count,
              ];

              header("Content-Type: ".$mimetype );
              //header("Content-Length: ".filesize($file_path)); This line seems to cause the download to not load sometimes. First try works and then it fails after that.

              while (ob_get_level()) {
                  ob_end_clean();
              }
              readfile($file_path);

              \MVIFileAttachment\CustomTable::update_values_no_prefix( $post_id, $data );
            }

            //no file present
            add_filter( 'document_title_parts', 'change_document_title_parts' );
            function change_document_title_parts ( $title_parts ) {
                $title_parts['title'] = 'Download unavailable';
                return $title_parts;
            }

            output_download_page_content(
              "This download file is not available.",
              "Looks like there is a problem with our server. Please contact MVI to get the file you requested."
            );
          } else {
            //download link is no longer valid
            add_filter( 'document_title_parts', 'change_document_title_parts' );
            function change_document_title_parts ( $title_parts ) {
                $title_parts['title'] = 'Download expired';
                return $title_parts;
            }

            output_download_page_content(
              "This download link has expired.",
              "This link is no longer valid. Please request a new link from the original article.",
              $reference_post_url
            );
          }
      }
  }
  wp_reset_postdata();
} else {
    //no posts matching dl_id found in database
    add_filter( 'document_title_parts', 'change_document_title_parts' );
    function change_document_title_parts ( $title_parts ) {
        $title_parts['title'] = 'Download not found';
        return $title_parts;
    }

    output_download_page_content(
      "Oops! This download can&rsquo;t be found.",
      "This download link is not in our database. Please refer to your original download email to find the link to article you requested."
    );
};
