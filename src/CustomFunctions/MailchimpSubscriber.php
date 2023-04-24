<?php

namespace MVIWebinarRegistration\CustomFunctions;

use \DrewM\MailChimp\MailChimp;
use \MVIWebinarRegistration\CustomFunctions\ProfessionalRoleTagsArray;

/**
 * MailchimpSubscriber class. Used as a simplified class for making API requests using \DrewM\MailChimp\MailChimp.
 *
 * @date    04/18/2023
 * @since   3.0.0
 */
class MailchimpSubscriber
{

    private $mailchimp;
    private $list_id;
    private $subscriber;
    private $email;
    private $subscriber_hash;
    private $list;


    /**
     * Construct Object for the facade
     * 
     * @param string $settings_mailchimp_key
     * @param string $settings_mailchimp_list_id
     * @param string $email
     */
    public function __construct(string $settings_mailchimp_key, string $settings_mailchimp_list_id, string $email)
    {
        $this->list_id = $settings_mailchimp_list_id;
        $this->mailchimp = new MailChimp($settings_mailchimp_key);
        $this->email = $email;
        $this->subscriber_hash = $this->mailchimp::subscriberHash($this->email);

        if ($settings_mailchimp_key == "" || $settings_mailchimp_list_id == "" || $email == "") {
            throw new \Exception("Invalid (empty) parameter passed to MailchimpSubscriber");
        }
    }


    public function add_or_update(array $submission_tag_names, array $merge_var, bool $subscribe, bool $debug = false): string
    {
        //Make the intial API call
        $subscriber = $this->get_subscriber();

        $return = "nothing_modified";

        $professional_role_tags = new ProfessionalRoleTagsArray;
        $remove_names_tags = $professional_role_tags->generate_slug_array();

        $subscriber_tags = $subscriber["tags"] ?? [];

        //get the updated tags
        $tags = $this->build_updated_tags($this->get_tag_names($subscriber_tags), $submission_tag_names, $remove_names_tags);

        switch (true) {
            case $subscriber["status"] === 'subscribed':

                //update the user's tags
                $return = $this->update_subscriber_tags($tags, $debug);

                break;

            case $subscriber["status"] === 'unsubscribed':
                //same as below
            case $subscriber["status"] === 'archived':


                //either re-subscribe the user or update their tags
                if ($subscribe) {
                    $return = $this->re_subscribe_user($tags, $merge_var, $debug);
                } else {
                    $return = $this->update_subscriber_tags($tags, $debug);
                }

                break;

            case $subscriber["status"] === 404:


                //subscribe the user if they want to
                if ($subscribe) {
                    $return = $this->add_subscriber($submission_tag_names, $merge_var, $debug);
                }

                break;

            default:
                throw new \Exception("Unexpected status from MailChimp ({$subscriber["status"]}). Here is the last error: {$this->mailchimp->getLastError()}");
                break;
        }

        //output the log message if desired
        if ($debug) {
            error_log("add_or_update: " . $return);
        }

        return $return;
    }

    /**
     * Take a simple array of tag names, and compare to determine what the updated tags list should be. Formatted for the update and re-subscribe functions.
     * 
     * @param array $subscriber_tag_names
     * @param array $submission_tag_names
     * @param array $remove_names
     * 
     * @return array
     */
    private function build_updated_tags(array $subscriber_tag_names, array $submission_tag_names, array $remove_names = []): array
    {
        //get tags that could be multiple values for the same concept.
        $possible_duplicates = array_intersect($subscriber_tag_names, $remove_names);

        //don't remove the tag if it is in the new submission.
        $tags_to_remove = array_diff($possible_duplicates, $submission_tag_names);

        $return = array_merge($this->format_tags_array($tags_to_remove, "inactive"), $this->format_tags_array($submission_tag_names, "active"));
        return $return;
    }

    /**
     * Take a simple array of tag names, and convert them into the proper format for the Mailchimp update function.
     * 
     * @param array $tag_names
     * 
     * @return array
     */
    public function format_tags_array(array $tag_names, string $status): array
    {
        $output = [];
        foreach ($tag_names as $name) {
            $output[] = [
                "name" => $name,
                "status" => $status
            ];
        }
        return $output;
    }

    /**
     * Get the tag names from the subscriber.
     * 
     * @param array|null $full_tags
     * 
     * @return array
     */
    private function get_tag_names(?array $full_tags): array
    {
        $tags = $full_tags ?? [];

        foreach ($tags as $tag) {
            if ($tag["name"]) {
                $tag_names[] = $tag["name"];
            }
        }
        return $tag_names ?? [];
    }

    /**
     * Get the list (audience) details. Makes an API request.
     * 
     * @return array
     */
    public function get_list(): array
    {
        $this->list = $this->mailchimp->get("lists/$this->list_id");

        return $this->list;
    }


    /**
     * Get the subscriber's details. Makes an API request only if needed.
     * 
     * @return array|false $this->subscriber
     */
    public function get_subscriber()
    {
        if (!$this->subscriber) {
            $this->subscriber = $this->mailchimp->get("lists/$this->list_id/members/$this->subscriber_hash");
        }

        if ($this->subscriber == false) {
            throw new \Exception("Something is wrong with MailChimp. It could be your SSL certificate, or see: {$this->mailchimp->getLastError()}");
        }

        return $this->subscriber;
    }

    /**
     * Get the subscriber's tags. Makes an API request only if needed.
     * 
     * @return array $this->subscriber
     */
    public function get_subscriber_tags(): array
    {
        $subscriber = $this->get_subscriber();

        return $subscriber["tags"] ?? [];
    }

    /**
     * Update the subscriber's tags without changing contact details. Makes an API request.
     * 
     * @param array $tags
     * 
     * @return string
     */
    public function update_subscriber_tags(array $tags, bool $debug = false): string
    {
        $payload = [
            'tags' => $tags //must have names and status
        ];

        //must use post for this. See: https://mailchimp.com/developer/marketing/api/list-member-tags/
        $result = $this->mailchimp->post("lists/$this->list_id/members/$this->subscriber_hash/tags", $payload); //This action returns true on success

        $this->subscriber = json_decode($this->mailchimp->getLastResponse()["body"], true); //$result returns true, so this gets the actual body of the response

        $return = $this->mailchimp->success() ? "updated_tags" : "error";

        if ($debug && $return === "error") {
            error_log("update_subscriber_tags response: " . json_encode($this->subscriber));
        }

        return $return;
    }

    /**
     * Adds a new subscriber. Makes an API request.
     * 
     * @param array $tags
     * @param array $merge_var
     * 
     * @return string
     */
    public function add_subscriber(array $tags, array $merge_var, bool $debug = false): string
    {
        $payload = [
            'email_address' => $this->email,
            'status' => 'subscribed',
            'merge_fields' => $merge_var,
            'tags' => $tags, //can only be tag names
        ];

        $this->subscriber = $this->mailchimp->post("lists/$this->list_id/members", $payload);

        $return = $this->mailchimp->success() ? "added_subscriber" : "error";

        if ($debug && $return === "error") {
            error_log("add_subscriber response: " . json_encode($this->subscriber));
        }

        return $return;
    }

    /**
     * Re-subscribe the subscriber, update contact info, update tags. Makes an API request.
     * 
     * @return string
     */
    public function re_subscribe_user($tags, $merge_var, bool $debug = false): string
    {
        $payload = [
            'email_address' => $this->email,
            'status' => 'subscribed',
            'merge_fields' => $merge_var,
            'tags' => $tags, //must have names and status
        ];

        $this->subscriber = $this->mailchimp->put("lists/$this->list_id/members/$this->subscriber_hash", $payload);

        $return = $this->mailchimp->success() ? "updated_subscriber" : "error";

        if ($debug && $return === "error") {
            error_log("re_subscribe_user response: " . json_encode($this->subscriber));
        }

        return $return;
    }
}
