<?php
namespace MVIFileAttachment;

class Taxonomy {
    public function __construct() {
        add_action( 'wp_loaded', [ $this, 'file_download_register_taxonomy' ] );
    }

    /**
     * Get ID
     * Get the ID for this settings page
     *
     * @return string
     */
  	public static function get_id() {
  		$id = \MVIFileAttatchmentBase::PLUGIN_PREFIX . "file-status";
  		return $id;
  	}

    //Register Taxonomy for tracking if post has pdf download attatched
    public function file_download_register_taxonomy() {
    	$labels = [
    		'name'                       => esc_html__( 'MVI File downloads', 'mvi-file-attachment' ),
    		'singular_name'              => esc_html__( 'MVI File-Download', 'mvi-file-attachment' ),
    		'menu_name'                  => esc_html__( 'MVI File downloads', 'mvi-file-attachment' ),
    		'search_items'               => esc_html__( 'Search MVI File downloads', 'mvi-file-attachment' ),
    		'popular_items'              => esc_html__( 'Popular MVI File downloads', 'mvi-file-attachment' ),
    		'all_items'                  => esc_html__( 'All MVI File downloads', 'mvi-file-attachment' ),
    		'parent_item'                => esc_html__( 'Parent MVI File-Download', 'mvi-file-attachment' ),
    		'parent_item_colon'          => esc_html__( 'Parent MVI File-Download:', 'mvi-file-attachment' ),
    		'edit_item'                  => esc_html__( 'Edit MVI File-Download', 'mvi-file-attachment' ),
    		'view_item'                  => esc_html__( 'View MVI File-Download', 'mvi-file-attachment' ),
    		'update_item'                => esc_html__( 'Update MVI File-Download', 'mvi-file-attachment' ),
    		'add_new_item'               => esc_html__( 'Add New MVI File-Download', 'mvi-file-attachment' ),
    		'new_item_name'              => esc_html__( 'New MVI File-Download Name', 'mvi-file-attachment' ),
    		'separate_items_with_commas' => esc_html__( 'Separate mvi file downloads with commas', 'mvi-file-attachment' ),
    		'add_or_remove_items'        => esc_html__( 'Add or remove mvi file downloads', 'mvi-file-attachment' ),
    		'choose_from_most_used'      => esc_html__( 'Choose most used mvi file downloads', 'mvi-file-attachment' ),
    		'not_found'                  => esc_html__( 'No mvi file downloads found.', 'mvi-file-attachment' ),
    		'no_terms'                   => esc_html__( 'No mvi file downloads', 'mvi-file-attachment' ),
    		'filter_by_item'             => esc_html__( 'Filter by mvi file-download', 'mvi-file-attachment' ),
    		'items_list_navigation'      => esc_html__( 'MVI File downloads list pagination', 'mvi-file-attachment' ),
    		'items_list'                 => esc_html__( 'MVI File downloads list', 'mvi-file-attachment' ),
    		'most_used'                  => esc_html__( 'Most Used', 'mvi-file-attachment' ),
    		'back_to_items'              => esc_html__( '&larr; Go to MVI File downloads', 'mvi-file-attachment' ),
    		'text_domain'                => esc_html__( 'mvi-file-attachment', 'mvi-file-attachment' ),
    	];
    	$args = [
    		'label'              => esc_html__( 'MVI File downloads', 'mvi-file-attachment' ),
    		'labels'             => $labels,
    		'description'        => '',
    		'public'             => true,
    		'publicly_queryable' => false,
    		'hierarchical'       => false,
    		'show_ui'            => false,
    		'show_in_menu'       => false,
    		'show_in_nav_menus'  => false,
    		'show_in_rest'       => false,
    		'show_tagcloud'      => false,
    		'show_in_quick_edit' => false,
    		'show_admin_column'  => false,
    		'query_var'          => false,
    		'sort'               => false,
    		'meta_box_cb'        => false,
    		'rest_base'          => '',
    		'rewrite'            => [
    			'with_front'   => true,
    			'hierarchical' => false,
    		],
    	];
      //$post_types = get_post_types();
    	register_taxonomy( self::get_id(), \MVIFileAttachment\Settings::get_field_value('settings_select_pt'), $args );
    }
}
