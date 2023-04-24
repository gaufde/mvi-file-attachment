<?php

namespace MVIFileAttachment\Fields;

class Posts implements FieldsInterface
{

  public function __construct()
  {
    //empty
  }

  /**
   * Get ID
   * Get the ID for this settings page
   *
   * @return string
   */
  public static function get_id()
  {
    $id = self::PLUGIN_PREFIX . "attach_download_file";
    return $id;
  }
  /**
   * Return Fields
   *
   * @return array
   */
  public static function return_fields()
  {
    $fields = [
      'title'      => __('Attach Download File', 'mvi-file-attachment'),
      'id'         => self::get_id(),
      'post_types' => \MVIFileAttachment\Settings::get_field_value('settings_select_pt'),
      'style'      => 'seamless',
      'fields'     => [
        [
          'name'             => __('Custom Form title', 'mvi-file-attachment'),
          'id'               => self::PLUGIN_PREFIX . 'form_title',
          'type'             => 'text',
          'tooltip' => 'Replaces the default title shown above the form'
        ],
        [
          'name'             => __('Short Title', 'mvi-file-attachment'),
          'id'               => self::PLUGIN_PREFIX . 'short_title',
          'type'             => 'text',
          'tooltip' => 'An alternative to the longer title above. Useful for button text.'
        ],
        [
          'name'             => __('Download File', 'mvi-file-attachment'),
          'id'               => self::PLUGIN_PREFIX . 'post_download_file',
          'type'             => 'file',
          'max_file_uploads' => 1,
          'force_delete'     => true,
          'upload_dir'       => ABSPATH . 'file_downloads/secure',
        ],
      ],
    ];

    return $fields;
  }
}
