<?php

namespace MVIWebinarRegistration;

class PostType
{

  /**
   * Registers this class with WordPress.
   */
  public static function register()
  {
    $plugin = new self();
    add_action('wp_loaded', [$plugin, 'register_post_type']);
  }

  public function __construct()
  {
  }

  /**
   * Get ID
   * Get the ID for this settings page
   *
   * @return string
   */
  public static function get_id()
  {
    $id = \MVIWebinarRegistrationBase::PLUGIN_PREFIX . "submissions";
    return $id;
  }

  //Register a post type for webinar registration tracking. This post type will store form responses and also handle serving the registration requests.
  public function register_post_type()
  {
    $labels = [
      'name'                     => esc_html__('webinar registrations', 'mvi-webinar-registration'),
      'singular_name'            => esc_html__('Webinar Registration', 'mvi-webinar-registration'),
      'add_new'                  => esc_html__('Add New', 'mvi-webinar-registration'),
      'add_new_item'             => esc_html__('Add new webinar registration', 'mvi-webinar-registration'),
      'edit_item'                => esc_html__('Edit Webinar Registration', 'mvi-webinar-registration'),
      'new_item'                 => esc_html__('New Webinar Registration', 'mvi-webinar-registration'),
      'view_item'                => esc_html__('View Webinar Registration', 'mvi-webinar-registration'),
      'view_items'               => esc_html__('View Webinar Registrations', 'mvi-webinar-registration'),
      'search_items'             => esc_html__('Search Webinar Registrations', 'mvi-webinar-registration'),
      'not_found'                => esc_html__('No webinar registrations found', 'mvi-webinar-registration'),
      'not_found_in_trash'       => esc_html__('No webinar registrations found in Trash', 'mvi-webinar-registration'),
      'parent_item_colon'        => esc_html__('Parent Webinar Registration:', 'mvi-webinar-registration'),
      'all_items'                => esc_html__('All Webinar Registrations', 'mvi-webinar-registration'),
      'archives'                 => esc_html__('Webinar Registration Archives', 'mvi-webinar-registration'),
      'attributes'               => esc_html__('Webinar Registration Attributes', 'mvi-webinar-registration'),
      'insert_into_item'         => esc_html__('Insert into webinar registration', 'mvi-webinar-registration'),
      'uploaded_to_this_item'    => esc_html__('Uploaded to this webinar registration', 'mvi-webinar-registration'),
      'featured_image'           => esc_html__('Featured image', 'mvi-webinar-registration'),
      'set_featured_image'       => esc_html__('Set featured image', 'mvi-webinar-registration'),
      'remove_featured_image'    => esc_html__('Remove featured image', 'mvi-webinar-registration'),
      'use_featured_image'       => esc_html__('Use as featured image', 'mvi-webinar-registration'),
      'menu_name'                => esc_html__('Webinar Registrations', 'mvi-webinar-registration'),
      'filter_items_list'        => esc_html__('Filter webinar registrations list', 'mvi-webinar-registration'),
      'filter_by_date'           => esc_html__('', 'mvi-webinar-registration'),
      'items_list_navigation'    => esc_html__('Webinar registrations list navigation', 'mvi-webinar-registration'),
      'items_list'               => esc_html__('Webinar Registrations list', 'mvi-webinar-registration'),
      'item_published'           => esc_html__('Webinar Registration published', 'mvi-webinar-registration'),
      'item_published_privately' => esc_html__('Webinar registration published privately', 'mvi-webinar-registration'),
      'item_reverted_to_draft'   => esc_html__('Webinar registration reverted to draft', 'mvi-webinar-registration'),
      'item_scheduled'           => esc_html__('Webinar Registration scheduled', 'mvi-webinar-registration'),
      'item_updated'             => esc_html__('Webinar Registration updated', 'mvi-webinar-registration'),
      'text_domain'              => esc_html__('mvi-webinar-registration', 'mvi-webinar-registration'),
    ];
    $args = [
      'label'               => esc_html__('Webinar Registrations', 'mvi-webinar-registration'),
      'labels'              => $labels,
      'description'         => '',
      'public'              => false,
      'hierarchical'        => false,
      'exclude_from_search' => false,
      'publicly_queryable'  => false,
      'show_ui'             => true,
      'show_in_nav_menus'   => false,
      'show_in_admin_bar'   => false,
      'show_in_rest'        => true,
      'query_var'           => true,
      'can_export'          => true,
      'delete_with_user'    => false,
      'has_archive'         => false,
      'rest_base'           => '',
      'show_in_menu'        => true,
      'menu_position'       => '',
      'menu_icon'           => 'dashicons-admin-generic',
      'capability_type'     => 'post',
      'capabilities'        => ['create_posts' => 'do_not_allow'], // Removes support for the "Add New" function ( use 'do_not_allow' instead of false for multisite set ups )
      'map_meta_cap'        => true, // Set to `false`, if users are not allowed to edit/delete existing posts
      'supports'            => false,
      'taxonomies'          => [],
      'rewrite'             => [
        'with_front' => false,
      ],
    ];

    register_post_type(self::get_id(), $args);
  }
}
