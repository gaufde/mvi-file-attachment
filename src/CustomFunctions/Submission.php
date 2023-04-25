<?php

namespace MVIFileAttachment\CustomFunctions;

use DateTime;
use DateTimeZone;

class Submission
{
	private $config;
	public $post_id;
	public $email;
	private $plugin_prefix;
	private $first_name;
	private $last_name;
	private $reference_post_id;
	private $reference_post_url;
	private $reference_post_name;
	private $phone;
	private $country_code;
	private $download_id;
	private $download_url;
	private $download_name;
	private $professional_role;
	private $subscribe;
	private $files;
	private $file;
	private $date_time;

	public function __construct($config, $post_id)
	{
		$this->config = $config;
		$this->post_id = $post_id;
		$this->plugin_prefix = \MVIFileAttachmentBase::PLUGIN_PREFIX;

		$this->email = $_POST[$this->plugin_prefix . 'email'];
		$this->email = mb_strtolower($this->email);
		$this->first_name = $_POST[$this->plugin_prefix . 'first_name'];
		$this->last_name = $_POST[$this->plugin_prefix . 'last_name'];
		$this->country_code = $_POST[$this->plugin_prefix . 'country_code'];
		$this->phone = $_POST[$this->plugin_prefix . 'phone'];
		$this->reference_post_id = (int) $_POST[$this->plugin_prefix . 'reference_post_id'];
		$this->reference_post_url = get_permalink($this->reference_post_id);
		$this->reference_post_name = get_the_title($this->reference_post_id);
		$this->download_id = sha1(uniqid($this->post_id, true)); //create a unique token to use for the download link
		$this->download_url = get_site_url() . "/download/" . $this->download_id . "/";
		$this->professional_role = $_POST[$this->plugin_prefix . 'professional_role'];
		$this->subscribe = $_POST[$this->plugin_prefix . 'subscribe'] ?? 0;
		$this->files = rwmb_meta($this->plugin_prefix . 'post_download_file', array('limit' => 1), $this->reference_post_id); //get only the first file from the array
		$this->file = reset($this->files);
		$this->download_name = $this->file['name'];
		$timezone = new DateTimeZone(wp_timezone_string());
		$this->date_time = new DateTime('now', $timezone);
	}

	public function save_to_db()
	{
		global $wpdb;
		$data = [
			'download_id' => $this->download_id,
			'download_name' => $this->download_name,
			'date_time' => $this->date_time->format('Y-m-d H:i:s'),
			'url_params' => $this->get_url_params(),
		];

		\MVIFileAttachment\CustomTable::update_values_no_prefix($this->post_id, $data); //save data to hidden fields
	}

	public function email_user()
	{
		$settings_from_email = \MVIFileAttachment\Settings::get_field_value('settings_from_email');

		$settings_from_name = \MVIFileAttachment\Settings::get_field_value('settings_from_name');

		if ($settings_from_name && $settings_from_email) {
			$to = $this->email;
			$from = "$settings_from_name <$settings_from_email>";
			$headers = ['Content-type: text/html', "Reply-To: $from"];
			$subject = "Download link";
			$message = "<p>Hi {$this->first_name},</p>
		                    <p>Thank you for requesting your download. Here is the download link for <a href=\"{$this->download_url}\">{$this->download_name}</a>.</p>
		                    <p>Please note that this link will expire. If you need to request a new download, please visit the <a href=\"{$this->reference_post_url}\">original page</a>.</p>
		                    <p></p>
		                    <p>Thank You,<br/>$settings_from_name</p>";

			wp_mail($to, $subject, $message, $headers); //email user
		}
	}

	public function email_owner()
	{
		$settings_from_email = \MVIFileAttachment\Settings::get_field_value('settings_from_email');
		$settings_from_name = \MVIFileAttachment\Settings::get_field_value('settings_from_name');
		$owner = \MVIFileAttachment\Settings::get_field_value('settings_owner_email');

		if ($settings_from_name && $settings_from_email && $owner) {
			$site_url = get_site_url();
			$full_phone = "+" . "$this->country_code" . " $this->phone";
			$pattern = '/^"([a-zA-Z \']*)" <(\w+([-+.\']\w+)*@\w+([-.]\w+)*\.\w+([-.]\w+)*)>$/';
			preg_match($pattern, $owner, $owner_matches);
			$to = $owner_matches[2];
			$from = "$settings_from_name <$settings_from_email>";
			$headers = ['Content-type: text/html', "Reply-To: $from"];
			$subject = "New file download on $site_url";
			$message = "
						<p>This user submission has been saved and will be included in the next CSV export. Here are the user's details:</p>

						<ul>
							<li>First Name: {$this->first_name}</li>
							<li>Last Name: {$this->last_name}</li>
							<li>Professional Role: {$this->professional_role}</li>
							<li>Email: <a href=\"mailto:{$this->email}?cc={$settings_from_email}&subject=Following up&body=Hi {$this->first_name},%0D%0A%0D%0AI noticed you downloaded {$this->download_name} from our website. I wanted to personally follow up to see if you have any further questions. %0D%0A%0D%0ABest,%0D%0A{$owner_matches[1]}\">{$this->email}</a></li>
							<li>Phone: <a href=\"tel:{$full_phone}\">{$full_phone}</a></li>
							<li>File: {$this->download_name}</li>
							<li>Submitted from: <a href=\"{$this->reference_post_url}\">$this->reference_post_url</a>.</li>
						</ul>
						";

			wp_mail($to, $subject, $message, $headers); //email owner
		}
	}

	public function get_url_params()
	{
		$return = "";
		if (class_exists("\UrlParamTrack\Session")) {
			$return = \UrlParamTrack\Session::get_params_string();
		}
		return $return;
	}

	public function get_tag_names(): array
	{
		$tags = [$this->reference_post_name, "Downloaded PDF", $this->professional_role];
		return $tags;
	}

	public function get_subscribe(): bool
	{
		$return = false;

		if ($this->subscribe == 1) {
			$return = true;
		}

		return $return;
	}

	public function get_merge_var(): array
	{
		$merge_var = [
			'FNAME' => $this->first_name,
			'LNAME' => $this->last_name,
		];
		return $merge_var;
	}
}
