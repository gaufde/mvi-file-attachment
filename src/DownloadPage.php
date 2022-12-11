<?php
namespace MVIFileAttachment;

//Create virtual download page
//https://metabox.io/how-to-create-a-virtual-page-in-wordpress/
class DownloadPage{

    public function __construct() {
    		add_filter( 'generate_rewrite_rules', [ $this, 'download_rewrite_rules' ] );
        add_filter( 'query_vars', [ $this, 'get_download_id' ] );
        add_action( 'template_redirect', [ $this, 'download_template' ] ); //use template_redirect so that we can use WP query in templates/download.
  	}


    public function download_rewrite_rules( $wp_rewrite ) {
        $wp_rewrite->rules = array_merge(
            ['download/([0-9a-fA-F]{40})/?$' => 'index.php?dl_id=$matches[1]'],
            $wp_rewrite->rules
            /*
            The filter generate_rewrite_rules is used to add a custom rewrite rule
            to the list of available WordPress rules (stored in $wp_rewrite->rules).
            This list is an array, and to add a rule, we simply add an element to that array
            */
        );
    }

    public function get_download_id( $query_vars ) {
        $query_vars[] = 'dl_id';
        return $query_vars;
        /*
        Add dl_id to the list of Wordpress query_vars.
        */
    }

    //load a template for the download page
    public function download_template() {
        $dl_id = strval( get_query_var( 'dl_id' ) );

        if ( $dl_id ) {
            include plugin_dir_path(__DIR__) . 'templates/download.php';
            die; //prevent wordpress from continuing to load more things.
        }
    }
}
