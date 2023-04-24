<?php

namespace MVIWebinarRegistration\CustomFunctions;

class ProcessSubmission
{
	public $post_id;
	private $subscription_notice;
	private $form_url;

	/**
	 * Registers this class with WordPress.
	 */
	public static function register()
	{
		$plugin = new self();
		add_action('rwmb_frontend_after_process', [$plugin, 'on_rwmb_frontend_after_process'], 10, 2);
		add_action('rwmb_frontend_after_display_confirmation', [$plugin, 'on_rwmb_frontend_after_display_confirmation'], 10, 2);
		add_action('rwmb_frontend_validate', [$plugin, 'on_rwmb_frontend_validate'], 10, 2);
	}


	public function __construct()
	{
	}

	/**
	 * Use mailchimp events to validate the form
	 *
	 * @param array $config
	 * @param string $post_id
	 */
	public function on_rwmb_frontend_validate($validate, $config)
	{
		if (\MVIWebinarRegistration\Fields\FrontendFileDownload::get_id() !== $config['id']) {
			return; //exit if not from the right form
		}

		$submission = new Submission($config);
		//Add people to the events list
		try {
			$event_subscriber = new MailchimpSubscriber(\MVIWebinarRegistration\Settings::get_field_value('settings_webinar_mailchimp_key'), \MVIWebinarRegistration\Settings::get_field_value('settings_webinar_mailchimp_list_id'), $submission->email);
			// updated_subscriber | added_subscriber | updated_tags | null
			$event_result = $event_subscriber->add_or_update($submission->get_tag_names(), $submission->get_merge_var(), true, true);
		} catch (\Exception $e) {
			error_log("Error on line " . $e->getLine() . " in file " . $e->getFile() . ": " . $e->getMessage());
			return;
		}

		if ($event_result == "error") {
			if ($event_subscriber->get_list()["subscribe_url_short"]) {
				$form_url = $event_subscriber->get_list()["subscribe_url_short"];
				$validate = 'Uh oh, registration failed. Please try again <a href="' . $form_url . '" target="_blank">here!</a>';
			} else {
				$validate = "Uh oh, registration failed. Please contact <a href=\"mailto:info@molecularvista.com\">info@molecularvista.com</a>";
			}
		}

		return $validate;
	}

	/**
	 * Process submission after form submission
	 *
	 * @param array $config
	 * @param string $post_id
	 */
	public function on_rwmb_frontend_after_process($config, $post_id)
	{

		if (\MVIWebinarRegistration\Fields\FrontendFileDownload::get_id() !== $config['id']) {
			return; //exit if not from the right form
		}

		$submission = new Submission($config, $post_id);
		$submission->save_to_db($post_id);
		$submission->email_owner();
		$submission->email_user();

		$debug = true;

		//subscribe people to the newsletter if they choose to
		try {
			$newsletter_subscriber = new MailchimpSubscriber(\MVIWebinarRegistration\Settings::get_field_value('settings_mailchimp_key'), \MVIWebinarRegistration\Settings::get_field_value('settings_mailchimp_list_id'), $submission->email);
			// updated_subscriber | added_subscriber | updated_tags | null
			$newsletter_result = $newsletter_subscriber->add_or_update($submission->get_tag_names(), $submission->get_merge_var(), $submission->get_subscribe(), $debug);
		} catch (\Exception $e) {
			error_log("Error on line " . $e->getLine() . " in file " . $e->getFile() . ": " . $e->getMessage());
			return;
		}

		//set extra notice to user
		if ($submission->get_subscribe()) {
			if ($newsletter_result !== "error") {
				$this->subscription_notice = '<div class="rwmb-confirmation">Thanks for subscribing to our newsletter!</div>';
			} else {
				if ($newsletter_subscriber->get_list()["subscribe_url_short"]) {
					$form_url = $newsletter_subscriber->get_list()["subscribe_url_short"];
					$this->subscription_notice = '<div class="rwmb-error">Uh oh, we could not subscribe you to our newsletter. Please try again <a href="' . $form_url . '" target="_blank">here!</a></div>';
				} else {
					$this->subscription_notice = '<div class="rwmb-error">Uh oh, we could not subscribe you to our newsletter. Please try again!</div>';
				}
			}
		}
	}

	/**
	 * Insert the frontend notice
	 * 
	 * This used to be done by setting an cookie and using JS to add the notice in the browser.
	 * Since MB frontend forms 4.2.0 this can now be handled entirely on the server side
	 * because rwmb_frontend_after_display_confirmation now works when ajax="true" in the shortcode.
	 *
	 */
	public function on_rwmb_frontend_after_display_confirmation()
	{
		if ($this->subscription_notice) {
			echo $this->subscription_notice;
		}

		if ( $scripts = \MVIWebinarRegistration\Settings::get_field_value('submit_scripts') ) {
			echo "<script>" . $scripts . "</script>";
		}

		return;
	}
}
