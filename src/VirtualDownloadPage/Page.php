<?php
namespace MVIFileAttachment\VirtualDownloadPage;


class Page implements PageInterface {

  private $url;
  private $title;
  private $content;
  private $template;
  private $wp_post;

  function __construct( $url, $title = 'Untitled', $template = 'page.php' ) {
    $this->url = filter_var( $url, FILTER_SANITIZE_URL );
    $this->setTitle( $title );
    $this->setTemplate( $template);
    return $this;
  }

  function getUrl() {
    return $this->url;
  }

  function getTemplate() {
    return $this->template;
  }

  function getTitle() {
    return $this->title;
  }

  function setTitle( $title ) {
    $this->title = filter_var( $title, FILTER_SANITIZE_STRING );
    return $this; //required to chain methods
  }

  function setContent( $content ) {
    $this->content = $content;
    return $this; //required to chain methods
  }

  function setTemplate( $template ) {
    $this->template = $template;
    return $this; //required to chain methods
  }

  function asWpPost() {
    if ( is_null( $this->wp_post ) ) {
      $post = array(
        'ID'             => 0,
        'post_title'     => $this->title,
        'post_name'      => sanitize_title( $this->title ),
        'post_content'   => $this->content ? : '',
        'post_excerpt'   => '',
        'post_parent'    => 0,
        'menu_order'     => 0,
        'post_type'      => 'page',
        'post_status'    => 'publish',
        'comment_status' => 'closed',
        'ping_status'    => 'closed',
        'comment_count'  => 0,
        'post_password'  => '',
        'to_ping'        => '',
        'pinged'         => '',
        'guid'           => home_url( $this->getUrl() ),
        'post_date'      => current_time( 'mysql' ),
        'post_date_gmt'  => current_time( 'mysql', 1 ),
        'post_author'    => is_user_logged_in() ? get_current_user_id() : 0,
        'is_virtual'     => TRUE,
        'filter'         => 'raw'
      );
      $this->wp_post = new \WP_Post( (object) $post );
    }
    return $this->wp_post;
  }
}
