<?php

namespace MVIFileAttachment;

class Fields
{
  /**
   * Registers this class with WordPress.
   */
  public static function register()
  {
    $plugin = new self();
    add_filter('rwmb_meta_boxes', [$plugin, 'register_meta_boxes']);
  }

  public function __construct()
  {
  }

  /**
   * Register Metaboxes
   * Reference: https://wordpress.stackexchange.com/questions/61437/php-error-with-shortcode-handler-from-a-class/61440#61440
   *
   * @return array
   */
  public function register_meta_boxes($meta_boxes)
  {
    $meta_boxes[] = \MVIFileAttachment\Fields\Settings::return_fields();
    $meta_boxes[] = \MVIFileAttachment\Fields\Posts::return_fields();
    $meta_boxes[] = \MVIFileAttachment\Fields\FrontendFileDownload::return_fields();
    $meta_boxes[] = \MVIFileAttachment\Fields\BackendFileDownload::return_fields();

    return $meta_boxes;
  }
}
