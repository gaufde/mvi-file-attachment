<?php

namespace MVIFileAttachment;

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
    $id = \MVIFileAttachmentBase::PLUGIN_PREFIX . "submissions";
    return $id;
  }

  //Register a post type for file download tracking. This post type will store form responses and also handle serving the download requests.
  public function register_post_type()
  {
    $labels = [
      'name'                     => esc_html__('File Downloads', 'mvi-file-attachment'),
      'singular_name'            => esc_html__('File Download', 'mvi-file-attachment'),
      'add_new'                  => esc_html__('Add New', 'mvi-file-attachment'),
      'add_new_item'             => esc_html__('Add new file download', 'mvi-file-attachment'),
      'edit_item'                => esc_html__('Edit File Download', 'mvi-file-attachment'),
      'new_item'                 => esc_html__('New File Download', 'mvi-file-attachment'),
      'view_item'                => esc_html__('View File Download', 'mvi-file-attachment'),
      'view_items'               => esc_html__('View File Downloads', 'mvi-file-attachment'),
      'search_items'             => esc_html__('Search File Downloads', 'mvi-file-attachment'),
      'not_found'                => esc_html__('No file downloads found', 'mvi-file-attachment'),
      'not_found_in_trash'       => esc_html__('No file downloads found in Trash', 'mvi-file-attachment'),
      'parent_item_colon'        => esc_html__('Parent File Download:', 'mvi-file-attachment'),
      'all_items'                => esc_html__('All File Downloads', 'mvi-file-attachment'),
      'archives'                 => esc_html__('File Download Archives', 'mvi-file-attachment'),
      'attributes'               => esc_html__('File Download Attributes', 'mvi-file-attachment'),
      'insert_into_item'         => esc_html__('Insert into file download', 'mvi-file-attachment'),
      'uploaded_to_this_item'    => esc_html__('Uploaded to this file download', 'mvi-file-attachment'),
      'featured_image'           => esc_html__('Featured image', 'mvi-file-attachment'),
      'set_featured_image'       => esc_html__('Set featured image', 'mvi-file-attachment'),
      'remove_featured_image'    => esc_html__('Remove featured image', 'mvi-file-attachment'),
      'use_featured_image'       => esc_html__('Use as featured image', 'mvi-file-attachment'),
      'menu_name'                => esc_html__('File Downloads', 'mvi-file-attachment'),
      'filter_items_list'        => esc_html__('Filter file downloads list', 'mvi-file-attachment'),
      'filter_by_date'           => esc_html__('', 'mvi-file-attachment'),
      'items_list_navigation'    => esc_html__('File downloads list navigation', 'mvi-file-attachment'),
      'items_list'               => esc_html__('File Downloads list', 'mvi-file-attachment'),
      'item_published'           => esc_html__('File Download published', 'mvi-file-attachment'),
      'item_published_privately' => esc_html__('File download published privately', 'mvi-file-attachment'),
      'item_reverted_to_draft'   => esc_html__('File download reverted to draft', 'mvi-file-attachment'),
      'item_scheduled'           => esc_html__('File Download scheduled', 'mvi-file-attachment'),
      'item_updated'             => esc_html__('File Download updated', 'mvi-file-attachment'),
      'text_domain'              => esc_html__('mvi-file-attachment', 'mvi-file-attachment'),
    ];
    $args = [
      'label'               => esc_html__('File Downloads', 'mvi-file-attachment'),
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
