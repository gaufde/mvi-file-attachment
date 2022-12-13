<?php
namespace MVIFileAttachment\CustomFunctions;

//This class updates the has_file term each time a pdf is added to a post
Class ApplyTaxonomy{
  
  public function __construct() {

  }

  /**
   * Registers this class with WordPress.
   */
  public static function register() {
    $plugin = new self();
    //run when the Posts field group is saved
    add_action( 'rwmb_' . \MVIFileAttatchmentBase::PLUGIN_PREFIX . 'attach_download_file_after_save_post', [$plugin, 'save_term'] );
  }

  //https://tommcfarlin.com/save_post-in-wordpress/
  function save_term( $post_id ) {


    $taxonomy_slug = \MVIFileAttachment\Taxonomy::get_id();
    $files = rwmb_meta( \MVIFileAttatchmentBase::PLUGIN_PREFIX . 'post_download_file', array( 'limit' => 1 ), $post_id);
    $terms = get_the_terms( $post_id, $taxonomy_slug);

    //set taxonomy if there is a file. If there is no file and a term exists, then remove all terms
    if ( $files ) {
      wp_set_object_terms ($post_id, 'has_file', $taxonomy_slug);
    } elseif ( $terms ) {
      wp_set_object_terms ($post_id, '', $taxonomy_slug);
    }
    clean_post_cache( $post_id ); //idk what I'm doing. Just following: https://stackoverflow.com/questions/69192575/wordpress-saving-a-taxonomy-based-on-the-value-of-a-different-taxonomy

  }
}
