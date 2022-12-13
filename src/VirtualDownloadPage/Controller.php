<?php
namespace MVIFileAttachment\VirtualDownloadPage;

/**
 * Heavily based off GM Virtual Pages by Giuseppe Mazzapica <giuseppe.mazzapica@gmail.com>
 * https://wordpress.stackexchange.com/questions/162240/custom-pages-with-plugin
 * Modified so that user virtual pages can-not be created, and virtual page is created dynamically based off DB SQL queries.
 *
 */
class Controller implements ControllerInterface {

  private $pages;
  private $loader;

  /**
   * Register this plugin with WP
   */
  public static function register( TemplateLoaderInterface $loader ) {
    $controller = new self( $loader );
    add_filter( 'do_parse_request', array( $controller, 'dispatch' ), PHP_INT_MAX, 2 );

    add_action( 'loop_end', function( \WP_Query $query ) {
      if ( isset( $query->virtual_page ) && ! empty( $query->virtual_page ) ) {
        $query->virtual_page = NULL;
      }
    } );

    add_filter( 'the_permalink', function( $plink ) {
      global $post, $wp_query;
      if (
        $wp_query->is_page
        && isset( $wp_query->virtual_page )
        && $wp_query->virtual_page instanceof Page
        && isset( $post->is_virtual )
        && $post->is_virtual
      ) {
        $plink = home_url( $wp_query->virtual_page->getUrl() );
      }
      return $plink;
    } );
  }

  function __construct( TemplateLoaderInterface $loader ) {
    $this->loader = $loader;
  }

  function dispatch( $bool, \WP $wp ) {
    $dl_id = $this->checkRequest();
    $path = $this->getPathInfo();

    if ( $dl_id ) {
      $post_id = \MVIFileAttachment\CustomTable::get_submission_post_id ($dl_id);

      $this->page = new Page( $path ); //set up a page object to populate if needed
      $output_page = true;

      if ( $post_id != 0 ){
        $file = \MVIFileAttachment\CustomTable::get_submission_file ( $post_id );
        $valid = \MVIFileAttachment\CustomTable::is_submission_valid ( $post_id );

        if ( $valid && $file ) {
          //serve the file to the user
          $output_page = false;
          \MVIFileAttachment\ServeFile::output ( $file['path'], $post_id );
        } elseif ( $file ) {
          //Your link has expired
          $reference_post_url = \MVIFileAttachment\CustomTable::get_submission_url ( $post_id );

          $this->page->setTitle( 'This download link has expired' )
          ->setContent( '<p>This link is no longer valid. Please request a new link from the <a href="' . $reference_post_url . '">original article.</a></p>' );

        } elseif ( !$file ) {
          //looks like there is a problem with our server.
          $this->page->setTitle( 'Download unavailable' )
          ->setContent( '<p>Looks like there is a problem with our server. Please contact us to get the file you requested.</p>' );
        }
      } else {
        //no posts found in db.

        $this->page->setTitle( 'Oops! This download can&rsquo;t be found' )
        ->setTemplate( 'custom.php' )
        ->setContent( '<p>This download link is not in our database. Please refer to the original email to find the link to the article you requested.</p>' );
      }

      if ( $output_page ){
        $this->loader->init( $this->page );
        $wp->virtual_page = $this->page;
        do_action( 'parse_request', $wp );
        $this->setupQuery();
        do_action( 'wp', $wp );
        $this->loader->load();
        $this->handleExit();
      }
    }
    return $bool;
  }

  private function checkRequest() {
    preg_match( ',download/([0-9a-fA-F]{40})/?$,', $this->getPathInfo(), $matches );
    if ( $matches ) {
      $dl_id = $matches[1];
      return $dl_id;
    }
    return false;
  }

  private function getPathInfo() {
    $home_path = parse_url( home_url(), PHP_URL_PATH );
    return preg_replace( "#^/?{$home_path}/#", '/', add_query_arg( array() ) );
  }

  private function setupQuery() {
    global $wp_query;
    $wp_query->init();
    $wp_query->is_page       = TRUE;
    $wp_query->is_singular   = TRUE;
    $wp_query->is_home       = FALSE;
    $wp_query->found_posts   = 1;
    $wp_query->post_count    = 1;
    $wp_query->max_num_pages = 1;
    $wp_query->query_vars = [];
    $posts = (array) apply_filters(
      'the_posts', array( $this->page->asWpPost() ), $wp_query
    );
    $post = $posts[0];
    $wp_query->posts          = $posts;
    $wp_query->post           = $post;
    $wp_query->queried_object = $post;
    $GLOBALS['post']          = $post;
    $wp_query->virtual_page   = $post instanceof \WP_Post && isset( $post->is_virtual )
      ? $this->page
      : NULL;
  }

  public function handleExit() {
    exit();
  }
}
