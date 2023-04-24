<?php

namespace MVIWebinarRegistration;

class Shortcode
{
  private $settings_recaptcha_key;
  private $settings_recaptcha_secret;
  private $allow_scroll;
  private $atts;

  public function __construct()
  {
    $this->settings_recaptcha_key = \MVIWebinarRegistration\Settings::get_field_value('settings_recaptcha_key');
    $this->settings_recaptcha_secret = \MVIWebinarRegistration\Settings::get_field_value('settings_recaptcha_secret');
    $this->allow_scroll = \MVIWebinarRegistration\Settings::get_field_value('allow_scroll');
  }

  public static function register()
  {
    $plugin = new self();

    add_shortcode(self::get_id(), [$plugin, 'shortcode']); //Register shortcode
    add_filter(self::get_id() . '_instance', [$plugin, 'get_instance']); //Allow the shortcode to be modified/removed. https://wordpress.stackexchange.com/questions/61437/php-error-with-shortcode-handler-from-a-class/61440#61440

    //Register assets in WP
    add_action('wp_enqueue_scripts', function () {
      //path is relative to the location of this file
      wp_register_style('webinar-form-style', plugins_url('../assets/css/mvi-webinar-registration.css', __FILE__), [], \MVIWebinarRegistrationBase::VERSION_NO);
    });
  }

  /**
   * Get the id of the shortcode
   *
   * @return string
   */
  public static function get_id()
  {
    $shortcode_id = \MVIWebinarRegistrationBase::PLUGIN_PREFIX . "frontend_form";
    return $shortcode_id;
  }

  /**
   * Get instance
   * Reference: https://wordpress.stackexchange.com/questions/61437/php-error-with-shortcode-handler-from-a-class/61440#61440
   *
   * @return object
   */
  public function get_instance()
  {
    return $this; // return the object
  }


  /**
   * optional_shortcode_attributes
   * Checks for recaptcha settings and dynamically adds them to the mb_frontend_form shortcode arguments. Also does this with allow_scroll.
   *
   * @return string
   */
  private function optional_shortcode_attributes()
  {
    if ($this->settings_recaptcha_secret && $this->settings_recaptcha_key) {
      //no prefix because these functions are for populating the MB shortcode. Format is: rwmb_frontend_field_value_{$attribute}.
      add_filter('rwmb_frontend_field_value_recaptcha_secret', function ($value, $args) {
        if (\MVIWebinarRegistration\Fields\FrontendFileDownload::get_id() !== $args['id']) {
          return; //exit if not from the right form
        }

        $value = $this->settings_recaptcha_secret;
        return $value;
      }, 10, 2);

      add_filter('rwmb_frontend_field_value_recaptcha_key', function ($value, $args) {
        if (\MVIWebinarRegistration\Fields\FrontendFileDownload::get_id() !== $args['id']) {
          return; //exit if not from the right form
        }

        $value = $this->settings_recaptcha_key;
        return $value;
      }, 10, 2);
    }

    if ($this->allow_scroll == 0) {
      //no prefix because these functions are for populating the MB shortcode. Format is: rwmb_frontend_field_value_{$attribute}.
      add_filter('rwmb_frontend_field_value_allow_scroll', function ($value, $args) {
        if (\MVIWebinarRegistration\Fields\FrontendFileDownload::get_id() !== $args['id']) {
          return; //exit if not from the right form
        }

        $value = $this->allow_scroll;
        return $value;
      }, 10, 2);

      return;
    }
  }

  /**
   * Shortcode
   * Custom shortcode to output the download form
   *
   * @return string
   */
  public function shortcode($atts)
  {
    //Don't break the REST API
    //https://generatepress.com/forums/topic/block-element-updating-failed-the-response-is-not-a-valid-json-response/
    ob_start();

    //Set the default title
    $form_title = 'Register for the webinar!';

    //set the default title as the attribute default (specifying an attribute overrides both other title options)
    $atts = shortcode_atts([
      'form_title' => $form_title,
      'event_title' => '',
      'event_id' => '',
      'event_type' => 'Webinar',
    ], $atts, self::get_id());

    $this->atts = $atts;

    //only show form if Webinar mailchimp is configured
    if (\MVIWebinarRegistration\Settings::get_field_value('settings_webinar_mailchimp_key') && \MVIWebinarRegistration\Settings::get_field_value('settings_webinar_mailchimp_list_id')) {
      //prepopulate event_title. This is required in order for ajax submit to work since for some reason saving the post ID after the fact doesn't work.
      add_filter('rwmb_' . \MVIWebinarRegistrationBase::PLUGIN_PREFIX . 'shortcode_atts_field_meta', function ($meta, $field, $saved) {
        $meta = $this->atts;
        return $meta;
      }, 10, 3);

      //Enqueue assets only when the form is actually inserted
      wp_enqueue_style('webinar-form-style');

      //insert recaptcha keys if they exist
      $this->optional_shortcode_attributes();

      $output = '';
      //Build a simple structure with the form inside.
      $output .= "<div class=event-registration-form>";
      $output .= "<h3 class=form-title>" . $atts['form_title'] . "</h3>";
      $output .= do_shortcode('[mb_frontend_form id="' . \MVIWebinarRegistration\Fields\FrontendFileDownload::get_id() . '"
  confirmation="Thank you! Please check your email for the registration details." submit_button="Sign me up!" ajax="true"]');
      //close div and add paragraph for whitespace after form
      $output .= "</div><p></p>";

      return $output;
    }

    return ob_get_clean();
  }
}
