<?php

namespace MVIFileAttachment\Fields;

class BackendFileDownload implements FieldsInterface
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
    $id = self::PLUGIN_PREFIX . "admin-form-fields";
    return $id;
  }
  /**
   * Return Fields
   *
   * @return array
   */
  public static function return_fields()
  {
    //Create metabox fields for the file downloads CPT with MB custom table. These fields are for backend only.
    if (!is_singular()) {
      $table = \MVIFileAttachment\CustomTable::get_id();

      $fields = [
        'title' => __('File Download Form Fields', 'mvi-file-attachment'),
        'id' => self::get_id(),
        'post_types' => [\MVIFileAttachment\PostType::get_id()],
        'storage_type' => 'custom_table',
        // Important
        'table' => $table,
        // Your custom table name
        'fields' => [
          [
            'name' => __('Download Name', 'mvi-file-attachment'),
            'id' => self::PLUGIN_PREFIX . 'download_name',
            'type' => 'hidden',
            'admin_columns' => [
              'position' => 'after ' . self::PLUGIN_PREFIX . 'professional_role',
              'sort' => true,
              'searchable' => true,
              'filterable' => true,
            ],
          ],
          [
            'id' => self::PLUGIN_PREFIX . 'download_id',
            'type' => 'hidden',
          ],
          [
            'name' => __('Download Count', 'mvi-file-attachment'),
            'id' => self::PLUGIN_PREFIX . 'download_count',
            'type' => 'hidden',
            'admin_columns' => [
              'position' => 'after ' . self::PLUGIN_PREFIX . 'download_name',
              'sort' => true,
              'searchable' => true,
              'filterable' => true,
            ],
          ],
          [
            'id' => self::PLUGIN_PREFIX . 'date_time',
            'type' => 'hidden',
          ],
          [
            'id' => self::PLUGIN_PREFIX . 'url_params',
            'type' => 'hidden',
          ],
          [
            'id' => self::PLUGIN_PREFIX . 'export_count',
            'type' => 'hidden',
          ],
        ],
      ];
    }

    return $fields;
  }
}
