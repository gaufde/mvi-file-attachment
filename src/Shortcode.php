<?php
namespace MVIFileAttachment;

Class Shortcode{

  public function __construct(){
    $this->settings_recaptcha_key = \MVIFileAttachment\Settings::get_field_value('settings_recaptcha_key');
    $this->settings_recaptcha_secret = \MVIFileAttachment\Settings::get_field_value('settings_recaptcha_secret');
  }

  public static function register() {
    $plugin = new self();

    add_shortcode( self::get_id(), [$plugin, 'shortcode'] ); //Register shortcode
    add_filter( self::get_id() . '_instance', [ $plugin, 'get_instance' ] ); //Allow the shortcode to be modified/removed. https://wordpress.stackexchange.com/questions/61437/php-error-with-shortcode-handler-from-a-class/61440#61440

    //Register assets in WP
    add_action(  'wp_enqueue_scripts', function () {
      //path is relative to the location of this file
      wp_register_style( 'file-form-style', plugins_url('../assets/css/mvi-file-attachment.css',__FILE__ ), [], \MVIFileAttachmentBase::VERSION_NO);
      //path is relative to the location of this file
      wp_register_script( 'file-form-script', plugins_url('../assets/js/subscription_validation.js',__FILE__ ), [],  \MVIFileAttachmentBase::VERSION_NO);

    });
  }

  public static function get_id() {
    $shortcode_id = \MVIFileAttachmentBase::PLUGIN_PREFIX . "frontend_form";
    return $shortcode_id;
  }

  public function get_instance() {
    return $this; // return the object
  }


  /**
   * Recaptcha
   * Checks for recaptcha settings and dynamically adds them to the mb_frontend_form shortcode arguments
   *
   * @return string
   */
  public function recaptcha () {
    if ( $this->settings_recaptcha_secret && $this->settings_recaptcha_key ) {
      //no prefix because these functions are for populating the MB shortcode
      add_filter( 'rwmb_frontend_field_value_recaptcha_secret', function ( $value, $args ) {
        if ( \MVIFileAttachment\Fields\FrontendFileDownload::get_id() !== $args['id'] ) {
      		return; //exit if not from the right form
      	}

        $value = $this->settings_recaptcha_secret;
        return $value;
      }, 10, 2 );

      add_filter( 'rwmb_frontend_field_value_recaptcha_key', function ( $value, $args ) {
        if ( \MVIFileAttachment\Fields\FrontendFileDownload::get_id() !== $args['id'] ) {
      		return; //exit if not from the right form
      	}

        $value = $this->settings_recaptcha_key;
        return $value;
      }, 10, 2 );
    }
    return;
  }

  /**
   * Insert file download form
   * Custom shortcode to output the download form
   *
   * @return string
   */
  public function shortcode( $atts ){
    //Don't break the REST API
    //https://generatepress.com/forums/topic/block-element-updating-failed-the-response-is-not-a-valid-json-response/
    ob_start();

    //Get the form title for this page
    $title = rwmb_meta( \MVIFileAttachmentBase::PLUGIN_PREFIX . 'form_title');
    $short_title = rwmb_meta( \MVIFileAttachmentBase::PLUGIN_PREFIX . 'short_title');

    //If the title field is blank, set the default title
    if ( !$title ) {
      $title = 'Get the pdf download to your inbox:';
    }

    //If the button title field is blank, set the default title
    if ( !$short_title ) {
      $short_title = 'Download PDF';
    }


    //set the default title as the attribute default (specifying an attribute overrides both other title options)
    $atts = shortcode_atts( [
		'title' => $title,
    'short_title' => $short_title,
    'form' => 'true',
    'use_short_title' => 'false',
    ], $atts, self::get_id() );


    $files = rwmb_meta( \MVIFileAttachmentBase::PLUGIN_PREFIX . 'post_download_file', array( 'limit' => 1 )); //get only the first file from the array

    if ( $files && $atts['form'] == 'true' ) {

      //Enqueue assets only when the form is actually inserted
      wp_enqueue_style( 'file-form-style' );
      wp_enqueue_script( 'file-form-script' );

      //prepopulate reference_post_id. This is required in order for ajax submit to work since for some reason saving the post ID after the fact doesn't work.
      add_filter( 'rwmb_' . \MVIFileAttachmentBase::PLUGIN_PREFIX . 'reference_post_id_field_meta', function ( $meta, $field, $saved ) {
        $meta = get_the_ID();
        return $meta;
      }, 10, 3 );

      //insert recaptcha keys if they exist
      $this->recaptcha();

      $form_url = new \MVIFileAttachment\CustomFunctions\MailchimpFunctions;
      $form_url = $form_url->list_signup_form_url();

      $output = '';
      //Build a simple structure with the form inside.
      $output .= "<div class=file-download-form>";
      $output .= "<h3 class=form-title>" . $atts['title'] . "</h3>";
    	$output .= do_shortcode( '[mb_frontend_form id="'.\MVIFileAttachment\Fields\FrontendFileDownload::get_id().'"
      confirmation="Thank you! Please check your email for a download link.
      <script>
      var mailChimpValidation = getCookie(\'mailchimpvalidation\');
      if ( mailChimpValidation ) {
            subscriptionMessage( mailChimpValidation, \' ' . $form_url . ' \');
          }
      </script>"

      submit_button="Get my link!" ajax="true"]');
      //close div and add paragraph for whitespace after form
      $output .= "</div><p></p>";

      return $output;
    } elseif ( $atts['form'] == 'false' && $atts['use_short_title'] == 'false' ){
      return $atts['title'];
    } else {
      return $atts['short_title'];
    }

    return ob_get_clean();
  }

}
