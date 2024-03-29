<?php

namespace MVIFileAttachment;

//Create settings page for the plugin
class Settings
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
		add_filter('mb_settings_pages', [$plugin, 'create_settings_page']);
	}

	/**
	 * Get ID
	 * Get the ID for this settings page
	 *
	 * @return string
	 */
	public static function get_id()
	{
		$id = \MVIFileAttachmentBase::PLUGIN_PREFIX . "settings_page";
		return $id;
	}

	/**
	 * Get field values
	 * rwmb_meta() does not work here
	 *
	 * @param string field_id
	 * 
	 * @return string|array
	 */
	public static function get_field_value($field_id)
	{
		$field_id = \MVIFileAttachmentBase::PLUGIN_PREFIX . $field_id; //add the prefix
		$settings = get_option(self::get_id());
		$value = "";
		if (isset($settings[$field_id])) {
			$value = $settings[$field_id];
		}
		return $value;
	}

	//create settings page
	public function create_settings_page($settings_pages)
	{
		$settings_pages[] = [
			'menu_title'    => __('Settings', 'mvi-file-attachment'),
			'id'            => self::get_id(),
			'position'      => 0,
			'parent'        => 'edit.php?post_type=' . \MVIFileAttachment\PostType::get_id(),
			'style'         => 'no-boxes',
			'columns'       => 1,
			'submit_button' => __('Save', 'mvi-file-attachment'),
			'icon_url'      => 'dashicons-admin-generic',
		];

		return $settings_pages;
	}
}
