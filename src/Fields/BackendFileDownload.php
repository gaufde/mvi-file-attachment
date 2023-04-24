<?php

namespace MVIWebinarRegistration\Fields;

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
    //Create metabox fields for the webinar registrations CPT with MB custom table. These fields are for backend only.
    if (!is_singular()) {
      $table = \MVIWebinarRegistration\CustomTable::get_id();

      $fields = [
        'title' => __('Webinar Registrations Form Fields', 'mvi-webinar-registration'),
        'id' => self::get_id(),
        'post_types' => [\MVIWebinarRegistration\PostType::get_id()],
        'storage_type' => 'custom_table',
        // Important
        'table' => $table,
        // Your custom table name
        'fields' => [
          [
            'id' => self::PLUGIN_PREFIX . 'date_time',
            'type' => 'hidden',
          ],
          [
            'id' => self::PLUGIN_PREFIX . 'url_params',
            'name' => __('URL Tracking', 'mvi-webinar-registration'),
            'type' => 'hidden',
            'admin_columns' => [
              'position' => 'after ' . self::PLUGIN_PREFIX . 'professional_role',
              'searchable' => true,
              'filterable' => true,
            ],
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
