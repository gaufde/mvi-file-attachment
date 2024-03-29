<?php

namespace MVIFileAttachment\Fields;

class Settings implements FieldsInterface
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
    $id = self::PLUGIN_PREFIX . "settings_page";
    return $id;
  }

  /**
   * Get post types
   * Get an array of post types currently registered in WP
   *
   * @return array
   */
  public static function get_post_types()
  {
    $args = array('_builtin' => false); // only get CPTs
    $cpts = get_post_types($args);
    $post_types = ['post' => 'post', 'page' => 'page'];
    $post_types = array_merge($post_types, $cpts); //add post and pages to array
    $plugin_cpt = \MVIFileAttachment\PostType::get_id(); //remove CPT from this plugin
    unset($post_types[$plugin_cpt]);
    return $post_types;
  }

  /**
   * Return Fields
   *
   * @return array
   */
  public static function return_fields()
  {
    $fields =  [
      'title'          => __('File Downloads Settings', 'mvi-file-attachment'),
      'id'             => self::get_id(),
      'settings_pages' => [\MVIFileAttachment\Settings::get_id()],
      'fields'         => [
        [
          'name' => __('From Address', 'mvi-file-attachment'),
          'id'   => self::PLUGIN_PREFIX . 'settings_from_email',
          'type' => 'email',
          'columns' => 6,
          'tooltip' => 'From address used when emailing users their download link.'
        ],
        [
          'name' => __('From Name', 'mvi-file-attachment'),
          'id'   => self::PLUGIN_PREFIX . 'settings_from_name',
          'type' => 'text',
          'columns' => 6,
          'tooltip' => 'From name used when emailing users their download link.'
        ],
        [
          'name' => __('Recaptcha Key', 'mvi-file-attachment'),
          'id'   => self::PLUGIN_PREFIX . 'settings_recaptcha_key',
          'type' => 'text',
          'columns' => 6,
          'tooltip' => 'Add this if you want recaptcha protection for the frontend form.'
        ],
        [
          'name' => __('Recaptcha Secret', 'mvi-file-attachment'),
          'id'   => self::PLUGIN_PREFIX . 'settings_recaptcha_secret',
          'type' => 'text',
          'columns' => 6,
          'tooltip' => 'Add this if you want recaptcha protection for the frontend form.'
        ],
        [
          'name' => __('Mailchimp API Key', 'mvi-file-attachment'),
          'id'   => self::PLUGIN_PREFIX . 'settings_mailchimp_key',
          'type' => 'text',
          'columns' => 6,
          'tooltip' => 'Add this if you want a subscribe checkbox to appear.'
        ],
        [
          'name' => __('Mailchimp List ID', 'mvi-file-attachment'),
          'id'   => self::PLUGIN_PREFIX . 'settings_mailchimp_list_id',
          'type' => 'text',
          'columns' => 6,
          'tooltip' => 'Add this if you want a subscribe checkbox to appear.'
        ],
        [
          'name' => __('Newsletter sign up checkbox text', 'mvi-file-attachment'),
          'id'   => self::PLUGIN_PREFIX . 'settings_newsletter_desc',
          'type' => 'text',
          'columns' => 3,
          'tooltip' => 'Label text for the newsletter sign-up checkbox.'
        ],
        [
          'name' => __('Email CSV exports to', 'mvi-file-attachment'),
          'id'   => self::PLUGIN_PREFIX . 'settings_export_emails',
          'type' => 'text',
          'columns' => 3,
          'tooltip' => 'Comma separated email addresses that user submissions should be sent to.'
        ],
        [
          'name' => __('Email individual submissions to', 'mvi-file-attachment'),
          'id'   => self::PLUGIN_PREFIX . 'settings_owner_email',
          'type' => 'text',
          'pattern' => '^"([a-zA-Z \']*)" <(\w+([-+.\']\w+)*@\w+([-.]\w+)*\.\w+([-.]\w+)*)>$',
          'sanitize_callback' => 'none',
          'columns' => 3,
          'tooltip' => 'This email will recieve a notification each time someone downloads a file.'
        ],
        [
          'name'    => 'Select Post Types',
          'id'      => self::PLUGIN_PREFIX . 'settings_select_pt',
          'type'    => 'select_advanced',
          'multiple' => true,
          'options' => self::get_post_types(),
          'columns' => 3,
          'tooltip' => 'Which post types should files be uploaded to.'
        ],
        [
          'name'    => 'Add scripts after submission',
          'id'      => self::PLUGIN_PREFIX . 'submit_scripts',
          'type'    => 'textarea',
          'tooltip' => 'Use this to add tracking scripts for conversion events',
          'sanitize_callback' => 'none',
        ],
        [
          'name'    => 'Allow scroll after ajax submit?',
          'id'      => self::PLUGIN_PREFIX . 'allow_scroll',
          'type'    => 'checkbox',
          'std' => 0,
          'tooltip' => 'Turn this off if you want to display the form outside of the main page content. For example, inside a modal popup.'
        ],
      ],
      'validation' => [
        'messages' => [
            self::PLUGIN_PREFIX . 'settings_owner_email' => [
                'pattern' => "Format as: \"First Last\" &ltemail@address.com>",
            ],
            // Error messages for other fields
        ],
    ],
    ];

    return $fields;
  }
}
