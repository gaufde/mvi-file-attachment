<?php
namespace MVIFileAttachment\CustomFunctions;

//https://stackoverflow.com/questions/10542012/php-namespaces-and-use
use \DrewM\MailChimp\MailChimp;

Class MailchimpFunctions{

  public function __construct(){
    $settings_mailchimp_key = \MVIFileAttachment\Settings::get_field_value('settings_mailchimp_key');
    $this->list_id = \MVIFileAttachment\Settings::get_field_value('settings_mailchimp_list_id');

    //make sure the key and list id are set. Otherwise, don't do anything.
    if ($settings_mailchimp_key && $this->list_id) {
        $this->mailchimp = new MailChimp( $settings_mailchimp_key );
    }
  }

  public function list_signup_form_url ( $debug = false ) {
    //don't try if mailchimp isn't active
    if (empty($this->mailchimp)) {
        return;
    };

    $response = $this->mailchimp->get('lists/' . $this->list_id . '/signup-forms');

    if ($debug){
           error_log( "list_signup_form response: " . json_encode( $response ) );
    }

    if ( $response ){
      $form_url = $response['signup_forms'][0]['signup_form_url'];
      return $form_url;
    }
  }


  /**
  *Check if email is subscribed in specific audience.
  */
  public function subscription_status($email, $debug = false) {
      //don't try if mailchimp isn't active
      if (empty($this->mailchimp)) {
          return;
      };

      $response = $this->mailchimp->get(sprintf(
          'lists/%s/members/%s',
          $this->list_id,
          $this->mailchimp->subscriberHash($email)
      ));

      if ($debug){
           error_log( "subscription_status response: " . json_encode( $response ) );
      }

      if ( isset($response['status']) && $response['status'] == 'subscribed' ){
        return 'subscribed';
      } elseif ( isset($response['status']) && $response['status'] !== 404 ) {
        return 'inactive';
      } elseif ( isset($response['status']) ) {
        return '404error';
      }

      return json_encode( $response );
  }



  public function update_subscriber_tags($email, $tags, $debug = false) {
    //don't try if mailchimp isn't active
    if (empty($this->mailchimp)) {
        return;
    };

    $subscriber_hash = $this->mailchimp->subscriberHash($email);

    $payload = [
      'tags'=> $tags
    ];

    $upd = $this->mailchimp->post("lists/$this->list_id/members/$subscriber_hash/tags", $payload);

    if ($debug) {
         error_log( "update_subscriber_tags response: " . json_encode( $upd ) );
    }


    return json_encode( $upd );
  }



  public function add_subscriber($email, $tags, $merge_var, $debug = false) {
    //don't try if mailchimp isn't active
    if (empty($this->mailchimp)) {
        return;
    };

    $payload = [
      'email_address' => $email,
      'status'        => 'subscribed',
      'merge_fields'  => $merge_var,
      'tags' => $tags,
    ];


    $result = $this->mailchimp->post('lists/' . $this->list_id . '/members', $payload);

    if ($debug){
           error_log( "add_subscriber response: " . json_encode( $result ) );

           error_log( "mailchimp last response: " . json_encode( $this->mailchimp->getLastResponse() ) );
    }

    if ( isset($result['status']) && $result['status'] == 'subscribed' ) {
      return 1;
    }

    return 0;
  }

  public function getArrayValuesRecursively(array $array) {
      $values = [];
      foreach ($array as $value) {
          if (is_array($value)) {
              $values = array_merge($values,
                  $this->getArrayValuesRecursively($value));
          } else {
              $values[] = $value;
          }
      }
      return $values;
  }

  public function get_subscriber_tags($email, $debug = false) {
    //don't try if mailchimp isn't active
    if (empty($this->mailchimp)) {
        return;
    };

    $subscriber_hash = $this->mailchimp->subscriberHash($email);

    $subscriber_tags = $this->mailchimp->get("lists/$this->list_id/members/$subscriber_hash/tags");
    $subscriber_tags = $this->getArrayValuesRecursively($subscriber_tags);

    return $subscriber_tags;
  }

  //This function accepts a parameter, $validation, which can be either 1 or 0.
  public function set_mailchimp_validation_cookie ( $validation, $debug = false ) {

    $cookie_options = [
      'expires' => 0,
      'path' => '/',
      'domain' => '', // leading dot for compatibility or use subdomain
      'secure' => false,     // or false
      'httponly' => false,    // or false
      'samesite' => 'Lax' // None || Lax  || Strict
    ];

    setcookie('mailchimpvalidation', $validation, $cookie_options);

    if ( $debug && $validation == 1 ) {
      error_log("Set mailchimp success cookie");
    } elseif ( $debug && $validation == 0 ) {
      error_log("Set mailchimp fail cookie");
    }
  }

  public function re_subscribe_user ($email, $tags, $merge_var, $debug ) {

    //don't try if mailchimp isn't active
    if (empty($this->mailchimp)) {
        return;
    };

    $payload = [
      'email_address' => $email,
      'status'        => 'subscribed',
      'merge_fields'  => $merge_var,
      'tags' => $tags,
    ];

    $subscriber_hash = $this->mailchimp->subscriberHash($email);

    $result = $this->mailchimp->put("lists/$this->list_id/members/$subscriber_hash", $payload);

    if ($debug){
           error_log( "add_subscriber response: " . json_encode( $result ) );

           error_log( "mailchimp last response: " . json_encode( $this->mailchimp->getLastResponse() ) );
    }

    if ( isset($result['status']) && $result['status'] == 'subscribed' ) {
      return 1;
    }

    return 0;
  }

}
