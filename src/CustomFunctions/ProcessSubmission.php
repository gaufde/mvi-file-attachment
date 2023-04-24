<?php

namespace MVIFileAttachment\CustomFunctions;

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
	}


	public function __construct()
	{
	}

	/**
	 * Process submission after form submission
	 *
	 * @param array $config
	 * @param string $post_id
	 */
	public function on_rwmb_frontend_after_process($config, $post_id)
	{

		if (\MVIFileAttachment\Fields\FrontendFileDownload::get_id() !== $config['id']) {
			return; //exit if not from the right form
		}

		$submission = new Submission($config, $post_id);
		$submission->save_to_db();
		$submission->email_user();
		$submission->email_owner();

		//initialize MailchimpFunctions
		$mc_func = new MailchimpFunctions();
		$this->form_url = $mc_func->list_signup_form_url();

		//Now we need to handle mailchimp API requests
		//set $debug = true and turn on WP_DEBUG as well as WP_DEBUG_LOG in config.php
		$debug = true;

		//Run some functions once and save their values in a variable
		$subscription_status = $mc_func->subscription_status($submission->email, $debug);
		$newsletter_subscribe = $submission->newsletter_subscribe();

		//Do not run if there is a problem with MailChimp
		if ($subscription_status == "false") {
			error_log("Something is wrong with MailChimp. It could be your SSL certificate, or check the errors");
			//https://community.localwp.com/t/multiple-ssl-issues-in-v6-4-3-on-macos/33707/3
			$mc_func->subscription_status($submission->email, $debug);
			error_log("Last MailChimp Error: " . json_encode($mc_func->mailchimp->getLastError()));
			return;
		}

		//Try to update or subscribe user
		if ($subscription_status !== "404error") {
			if ($debug) {
				error_log("User is in mailchimp (status: $subscription_status)...remove old subscriber_role_tags...");
			}

			$current_subscriber_tags = $mc_func->get_subscriber_tags($submission->email, $debug);
			$remove_subscriber_role_tags = $submission->professional_role_tags_to_remove($current_subscriber_tags);

			//Remove subscriber_role_tags
			$mc_func->update_subscriber_tags($submission->email, $remove_subscriber_role_tags, $debug);

			if ($newsletter_subscribe) {
				if ($debug) {
					error_log("User wants to subscribe...update their info and show mailchimp confirmation...");
				}

				$re_add_user = $mc_func->re_subscribe_user($submission->email, $submission->get_new_mailchimp_tags(), $submission->get_merge_var(), $debug); //This function will return 1 or 0 depending on if it was successful
				$this->generate_subscription_notice($re_add_user, $debug);
			} else {
				if ($debug) {
					error_log("User does not want to subscribe...update their info quietly...");
				}
				$update_subscriber = $mc_func->update_subscriber_tags($submission->email, $submission->get_update_mailchimp_tags(), $debug); //This function returns true if successful
			}
		} elseif ($newsletter_subscribe) {
			if ($debug) {
				error_log("User is not in mailchimp (status: $subscription_status)...They want to subscribe...show confirmation...");
			}

			$add_user = $mc_func->add_subscriber($submission->email, $submission->get_new_mailchimp_tags(), $submission->get_merge_var(), $debug); //This function will return 1 or 0 depending on if the user was successfully subscribed
			$this->generate_subscription_notice($add_user, $debug);
		} else {

			if ($debug) {
				error_log("User is not in mailchimp (status: $subscription_status) and they don't want to be...do nothing...");
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
		
		return;
	}


	/**
	 * Process validation to create frontend notice
	 *
	 */
	public function generate_subscription_notice($validation, $debug = false)
	{

		if ($validation == 1) {
			$output = '<div class="rwmb-confirmation">Thanks for subscribing to our newsletter!</div>';
		} elseif ($validation == 0 && $this->form_url) {
			$output = '<div class="rwmb-error">Uh oh, we could not subscribe you to our newsletter. Please try again <a href="' . $this->form_url . '" target="_blank">here!</a></div>';
		} elseif ($validation == 0) {
			$output = '<div class="rwmb-error">Uh oh, we could not subscribe you to our newsletter. Please try again!</div>';
		}

		if ($debug) {
			error_log( $output );
		}
		
		$this->subscription_notice = $output;
	}
}
