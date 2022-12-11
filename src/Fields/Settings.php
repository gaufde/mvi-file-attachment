<?php
namespace MVIFileAttachment\Fields;

class Settings implements FieldsInterface {

	public function __construct() {
    //empty
	}

	/**
   * Get ID
   * Get the ID for this settings page
   *
   * @return string
   */
	public static function get_id() {
		$id = self::PLUGIN_PREFIX . "settings_page";
		return $id;
	}
  /**
   * Return Fields
   *
   * @return array
   */
  public static function return_fields() {
    $fields =  [
        'title'          => __( 'File Downloads Settings', 'mvi-file-attachment' ),
        'id'             => self::get_id(),
        'settings_pages' => [\MVIFileAttachment\Settings::get_id()],
        'fields'         => [
            [
                'name' => __( 'From Address', 'mvi-file-attachment' ),
                'id'   => self::PLUGIN_PREFIX . 'settings_from_email',
                'type' => 'email',
                'columns' => 6,
            ],
            [
                'name' => __( 'From Name', 'mvi-file-attachment' ),
                'id'   => self::PLUGIN_PREFIX . 'settings_from_name',
                'type' => 'text',
                'columns' => 6,
            ],
            [
                'name' => __( 'Recaptcha Key', 'mvi-file-attachment' ),
                'id'   => self::PLUGIN_PREFIX . 'settings_recaptcha_key',
                'type' => 'text',
                'columns' => 6,
            ],
            [
                'name' => __( 'Recaptcha Secret', 'mvi-file-attachment' ),
                'id'   => self::PLUGIN_PREFIX . 'settings_recaptcha_secret',
                'type' => 'text',
                'columns' => 6,
            ],
            [
                'name' => __( 'Mailchimp API Key', 'mvi-file-attachment' ),
                'id'   => self::PLUGIN_PREFIX . 'settings_mailchimp_key',
                'type' => 'text',
                'columns' => 6,
            ],
            [
                'name' => __( 'Mailchimp List ID', 'mvi-file-attachment' ),
                'id'   => self::PLUGIN_PREFIX . 'settings_mailchimp_list_id',
                'type' => 'text',
                'columns' => 6,
            ],
    				[
                'name' => __( 'Newsletter sign up checkbox text', 'mvi-file-attachment' ),
                'id'   => self::PLUGIN_PREFIX . 'settings_newsletter_desc',
                'type' => 'text',
            ],
            [
                'name' => __( 'Email CSV exports to', 'mvi-file-attachment' ),
                'id'   => self::PLUGIN_PREFIX . 'settings_export_emails',
                'type' => 'text',
            ],
        ],
    ];

    return $fields;
  }

}
