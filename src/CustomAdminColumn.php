<?php
namespace MVIFileAttachment;

//Create a Meta Box custom admin column.
class CustomAdminColumn {
  public function __construct() {
    add_action( 'admin_init', [$this, 'initialize_admin_column'], 20 );
  }

  public function initialize_admin_column() {
    new CustomFunctions\AdminColumn( \MVIFileAttachment\PostType::get_id(), array() );
  }

}
