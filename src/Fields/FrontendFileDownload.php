<?php
namespace MVIFileAttachment\Fields;
//Create metabox fields for the file downloads form with MB custom table

class FrontendFileDownload implements FieldsInterface {

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
		$id = self::PLUGIN_PREFIX . "frontend-form-fields";
		return $id;
	}
  /**
   * Return Fields
   *
   * @return array
   */
  public static function return_fields() {
    $newsletter_desc = \MVIFileAttachment\Settings::get_field_value('settings_newsletter_desc');
    //set default
    if ( empty($newsletter_desc) ) {
      $newsletter_desc = 'Subscribe to our newsletter!';
    }

    $professional_role_tags = new \MVIFileAttachment\CustomFunctions\ProfessionalRoleTagsArray;
    $professional_role_tags_array = $professional_role_tags->generate_associative_array();

    //Get the mailchimp key and ID
    $settings_mailchimp_key = \MVIFileAttachment\Settings::get_field_value('settings_mailchimp_key');
    $settings_mailchimp_list_id = \MVIFileAttachment\Settings::get_field_value('settings_mailchimp_list_id');

    $table = \MVIFileAttachment\CustomTable::get_id();

    $frontend_fields = [
        'title'      => __( 'File Download Form Fields', 'mvi-file-attachment' ),
        'id'         => self::get_id(),
        'post_types' => [\MVIFileAttachment\PostType::get_id()],
    		'storage_type' => 'custom_table',    // Important
        'table'        => $table, // Your custom table name
        'fields'     => [
            [
                'type'          => 'custom_html',
                'std'          => '<em class="spam-notice">No spam, a download link will be sent directly to you. </em>',
            ],
            [
                'name'          => __( 'First Name', 'mvi-file-attachment' ),
                'id'            => self::PLUGIN_PREFIX . 'first_name',
                'type'          => 'text',
                'required'      => true,
                'columns' => 6,
                'attributes' => [
                    'placeholder'  => 'First name*',
                ],
                'admin_columns' => [
                    'position'   => 'after title',
                    'sort'       => true,
                    'searchable' => true,
                    'filterable' => true,
                ],
            ],
            [
                'name'          => __( 'Last Name', 'mvi-file-attachment' ),
                'id'            => self::PLUGIN_PREFIX . 'last_name',
                'type'          => 'text',
                'required'      => true,
                'columns' => 6,
                'attributes' => [
                    'placeholder'  => 'Last name*',
                ],
                'admin_columns' => [
                    'position'   => 'after ' . self::PLUGIN_PREFIX . 'first_name',
                    'sort'       => true,
                    'searchable' => true,
                    'filterable' => true,
                ],
            ],
            [
                'name'          => __( 'Email', 'mvi-file-attachment' ),
                'id'            => self::PLUGIN_PREFIX . 'email',
                'type'          => 'email',
                'required'      => true,
                'columns' => 6,
                'attributes' => [
                    'placeholder'  => 'Email*',
                ],
                'admin_columns' => [
                    'position'   => 'replace title',
                    'sort'       => true,
                    'searchable' => true,
                    'filterable' => true,
                ],
            ],
            [
                'name'          => __( 'Professional Role', 'mvi-file-attachment' ),
                'id'            => self::PLUGIN_PREFIX . 'professional_role',
                'type'          => 'select',
                'multiple'      => false,
                'required'      => true,
                'columns'       => 6,
                'placeholder'   => 'Professional Role',
                'options'       => $professional_role_tags_array,
                'admin_columns' => [
                    'position'   => 'after ' . self::PLUGIN_PREFIX . 'last_name',
                    'sort'       => true,
                    'searchable' => true,
                    'filterable' => true,
                ],
            ],
            [
                'name'          => __( 'Country Code', 'mvi-file-attachment' ),
                'id'            => self::PLUGIN_PREFIX . 'country_code',
                'type'          => 'text',
                'required'      => true,
                'class' => 'country_code',
                'columns' => 4,
                'attributes' => [
                    'placeholder'  => 'Country Code*',
                    'pattern'      => '\d{1,3}',
                ],
            ],
            [
                'name'          => __( 'Phone', 'mvi-file-attachment' ),
                'id'            => self::PLUGIN_PREFIX . 'phone',
                'type'          => 'tel',
                'required'      => true,
                'class' => 'phone',
                'columns' => 8,
                'attributes' => [
                    'placeholder'  => 'Phone (digits only)*',
                    'pattern'      => '\d{1,15}',
                ],
            ],
            [
                'type'          => 'custom_html',
                'std'          => '<p class="recaptcha-notice">This site is protected by reCAPTCHA and the Google <a href="https://policies.google.com/privacy" target="_blank" rel="noopener">Privacy Policy</a> and <a href="https://policies.google.com/terms" target="_blank" rel="noopener">Terms of Service</a> apply.</p>',
            ],
            [
                'id'   => self::PLUGIN_PREFIX . 'reference_post_id', //This backend field must be pre-filled on frontend in order to use MB ajax submit.
                'type' => 'hidden',
            ],
        ],
    ];

    //If mailchimp values are put in settings, then show the signup checkbox
    if ( $settings_mailchimp_key && $settings_mailchimp_list_id ) {
        array_splice(
          $frontend_fields['fields'],
          -2, //choose where to insert this field in the array above
          0,
          [
            [
              'name'          => __( 'Subscribe', 'mvi-file-attachment' ),
              'id'            => self::PLUGIN_PREFIX . 'subscribe',
              'type'          => 'checkbox',
              'std'           => 0,
              'required'      => false,
              'desc'          => __( "<span class=\"checkmark\"><svg xmlns=\"http://www.w3.org/2000/svg\" width=\"12\" height=\"12\" viewBox=\"0 0 24 24\"><path fill=\"currentColor\" d=\"M20.285 2l-11.285 11.567-5.286-5.011-3.714 3.716 9 8.728 15-15.285z\"></path></svg></span>
                                <span>" . "$newsletter_desc" . "</span>", 'mvi-file-attachment' ),
            ]
          ]
        );
    }

    return $frontend_fields;
  }

}
