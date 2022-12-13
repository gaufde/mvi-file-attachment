<?php
namespace MVIFileAttachment\VirtualDownloadPage;


interface ControllerInterface {

  /**
   * register the controller, loads WP filters and actions
   */
  static function register( TemplateLoaderInterface $loader );


  /**
   * Run on 'do_parse_request' and if the request is for one of the registerd
   * setup global variables, fire core hooks, requires page template and exit.
   *
   * @param boolean $bool The boolean flag value passed by 'do_parse_request'
   * @param \WP $wp       The global wp object passed by 'do_parse_request'
   */
  function dispatch( $bool, \WP $wp );

}
