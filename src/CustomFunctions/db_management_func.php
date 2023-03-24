<?php

/* //deactivated to be used manually only. Also, not up to date, this will have to be modified to work.
function delete_old_entires(){
  global $wpdb;
  $ids = $wpdb->get_col( "SELECT ID FROM {$wpdb->prefix}mvi_file_downloads" );
  if ( !empty($ids) ) {

  	// When calling WP_Query, we no longer need to worry about specifying a post type because we know exactly which post
  	// IDs we're after. We've also already ensured the correct order of our post IDs through the ORDER BY xxx ASC
  	// portion of our SQL query. So, we just tell WP_Query to return the WP_Post objects in the order of the post IDs we
  	// pass to it.
  	$query = new WP_Query( [
      'post_type' => \MVIFileAttachment\PostType::get_id(),
  		'post__in' => $ids,
      'posts_per_page'   => -1, //get all posts
  	] );

  	// The rest is exactly as you normally would handle a WP_Query object.
  	if ( $query->have_posts() ) {
  		while ( $query->have_posts() ) {
  			$query->the_post();
        $post_id = get_the_ID();
        $export_count = rwmb_meta( PLUGIN_PREFIX . 'export_count', ['storage_type' => 'custom_table', 'table' => WPDB_PREFIX . 'mvi_file_downloads'], $post_id);
        $date_time = rwmb_meta( PLUGIN_PREFIX . 'date_time', ['storage_type' => 'custom_table', 'table' => WPDB_PREFIX . 'mvi_file_downloads'], $post_id);

        //delete expired posts which have been exported
        if ($_SERVER["REQUEST_TIME"] - $date_time > EXPIRE_TIME && $export_count > 1 ){
          wp_delete_post( $post_id );
        }
  		}
  	}
    wp_reset_postdata();
  }
}
*/