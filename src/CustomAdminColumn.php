<?php

namespace MVIWebinarRegistration;

//Create a Meta Box custom admin column.
class CustomAdminColumn
{
  public function __construct()
  {
  }

  /**
   * Registers this class with WordPress.
   */
  public static function register()
  {
    $plugin = new self();
    add_action('admin_init', [$plugin, 'initialize_admin_column'], 20);
  }

  public function initialize_admin_column()
  {
    new CustomFunctions\AdminColumn(\MVIWebinarRegistration\PostType::get_id(), array());
  }
}
