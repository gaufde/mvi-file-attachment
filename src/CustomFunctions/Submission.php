<?php

namespace MVIWebinarRegistration\CustomFunctions;

use DateTime;
use DateTimeZone;
use \MVIWebinarRegistration\Shortcode;

class Submission
{
	private $config;
	public $post_id;
	public $email;
	private $plugin_prefix;
	private $first_name;
	private $last_name;
	private $phone;
	private $country_code;
	private $professional_role;
	private $subscribe;
	private $date_time;
	private $shortcode_atts;

	public function __construct($config)
	{
		$this->config = $config;
		$this->plugin_prefix = \MVIWebinarRegistrationBase::PLUGIN_PREFIX;
		$this->email = $_POST[$this->plugin_prefix . 'email'];
		$this->email = mb_strtolower($this->email);
		$this->first_name = $_POST[$this->plugin_prefix . 'first_name'];
		$this->last_name = $_POST[$this->plugin_prefix . 'last_name'];
		$this->country_code = $_POST[$this->plugin_prefix . 'country_code'];
		$this->phone = $_POST[$this->plugin_prefix . 'phone'];
		$this->professional_role = $_POST[$this->plugin_prefix . 'professional_role'];
		$this->subscribe = $_POST[$this->plugin_prefix . 'subscribe'] ?? 0;
		$this->shortcode_atts = $_POST[$this->plugin_prefix . 'shortcode_atts'];
		$timezone = new DateTimeZone(wp_timezone_string());
		$this->date_time = new DateTime('now', $timezone);
	}



	public function save_to_db($post_id)
	{
		global $wpdb;
		$data = [
			'date_time' => $this->date_time->format('Y-m-d H:i:s'),
			'url_params' => $this->get_url_params(),
			'shortcode_atts' => json_encode($this->get_shortcode_atts()),
		];

		\MVIWebinarRegistration\CustomTable::update_values_no_prefix($post_id, $data); //save data to hidden fields
	}


	public function email_user()
	{
		$settings_from_email = \MVIFileAttachment\Settings::get_field_value('settings_from_email');

		$settings_from_name = \MVIFileAttachment\Settings::get_field_value('settings_from_name');
		$event_name = $this->get_shortcode_atts()["event_title"] ?? "";
		$event_type = $this->get_shortcode_atts()["event_type"] ?? "Webinar";

		if ($settings_from_name && $settings_from_email) {
			$to = $this->email;
			$from = "$settings_from_name <$settings_from_email>";
			$headers = ['Content-type: text/html', "Reply-To: $from"];
			$subject = "$event_type Registration Confirmation";
			$link = "";
			$message = "<p>Hi {$this->first_name},</p>
		                    <p>You are successfully registered for \"$event_name\". Details and updates will be included in separate emails.</p>
							<p>We look forward to seeing you!</p>
							<p></p>
		                    <p>Thank You,<br/>$settings_from_name</p>";
			$ics = [];
			wp_mail($to, $subject, $message, $headers, $ics); //email user
		}
	}

	public function email_owner()
	{
		$settings_from_email = \MVIWebinarRegistration\Settings::get_field_value('settings_from_email');
		$settings_from_name = \MVIWebinarRegistration\Settings::get_field_value('settings_from_name');
		$owner = \MVIWebinarRegistration\Settings::get_field_value('settings_owner_email');

		if ($settings_from_name && $settings_from_email && $owner) {
			$site_url = get_site_url();
			$full_phone = "+" . "$this->country_code" . " $this->phone";
			$pattern = '/^"([a-zA-Z \']*)" <(\w+([-+.\']\w+)*@\w+([-.]\w+)*\.\w+([-.]\w+)*)>$/';
			preg_match($pattern, $owner, $owner_matches);
			$to = $owner_matches[2];
			$from = "$settings_from_name <$settings_from_email>";
			$headers = ['Content-type: text/html', "Reply-To: $from"];
			$subject = "New webinar registration on $site_url";
			$message = "
						<p>This user submission has been saved and will be included in the next CSV export. Here are the user's details:</p>

						<ul>
							<li>First Name: {$this->first_name}</li>
							<li>Last Name: {$this->last_name}</li>
							<li>Professional Role: {$this->professional_role}</li>
							<li>Email: <a href=\"mailto:{$this->email}\">{$this->email}</a></li>
							<li>Phone: <a href=\"tel:{$full_phone}\">{$full_phone}</a></li>
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

	public function get_shortcode_atts()
	{
		if ( !empty($this->shortcode_atts) ) {
			$atts = json_decode(stripslashes($this->shortcode_atts), true);

			if (is_null($atts) || !is_array($atts)) {
				error_log("Invalid urlParams cookie data.");
				return "";
			}

			foreach ($atts as $key => $value) {
				$filteredParams[$key] = htmlspecialchars($value);
			}
			$return = $filteredParams;
		}
		return $return ?? [];
	}

	public function get_tag_names(): array
	{	
		$event_id = $this->get_shortcode_atts()["event_id"] ?? "";

		$tags = [$event_id, $this->professional_role];
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
