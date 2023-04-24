<?php

namespace MVIFileAttachment\CustomFunctions;
use \MetaBox\CustomTable\API;

//https://stackoverflow.com/questions/14160511/export-to-csv-wordpress
//https://stackoverflow.com/questions/3523551/how-to-create-an-instance-of-a-class-in-another-class
//https://stackoverflow.com/questions/1468616/call-a-class-inside-another-class-in-php
//https://stackoverflow.com/questions/53186970/running-wp-query-in-a-class-causes-unnamed-wp-query-object
//https://stackoverflow.com/questions/35597600/wp-query-doesnt-work-inside-a-custom-class

class TableToCSV
{

  private $db;
  private $table;
  private $separator;
  private $internal_query;
  private $filename;
  private $file_path;
  private $plugin_prefix;


  function __construct($table, $sep, $file_n)
  {

    global $wpdb; //We gonna work with database aren't we?
    $this->db = $wpdb; //Can't use global on it's own within a class so lets assign it to local object.
    $this->table = $table;
    $this->separator = $sep;
    $this->plugin_prefix = \MVIFileAttachmentBase::PLUGIN_PREFIX;

    $generatedDate = date('d-m-Y His'); //Date will be part of file name. I dont like to see ...(23).csv downloaded
    $this->filename = "$file_n" . "$generatedDate" . ".csv";
    $this->file_path = dirname(ABSPATH, 1) . "/csvoutput/$this->filename"; //Define path outside root folder

    $csvFile = $this->generate_csv(); //save a csv file
    $this->email_admin(); //calling directly works for wp_cron events
    $this->update_export_counts();
    //add_action( 'init', [ $this, 'email_admin' ] );        //email admin a copy of the csv for triggering the event myself
    //add_action( 'init', [ $this, 'update_export_counts' ] );  //update export count number after everything else has run for triggering the event myself
  }

  //loop through posts and update export count for posts that haven't been exported yet.
  function update_export_counts()
  {
    $ids = $this->db->get_col("SELECT ID FROM " . $this->table . " WHERE " . $this->plugin_prefix . "export_count < 1");
    if (!empty($ids)) {

      $this->internal_query = new \WP_Query([
        'post_type' => \MVIFileAttachment\PostType::get_id(),
        'post__in' => $ids,
        'posts_per_page' => -1, //get all posts
      ]);

      // The rest is exactly as you normally would handle a WP_Query object.
      if ($this->internal_query->have_posts()) {
        while ($this->internal_query->have_posts()) {
          $this->internal_query->the_post();
          $post_id = get_the_ID();
          $export_count = rwmb_meta($this->plugin_prefix . 'export_count', ['storage_type' => 'custom_table', 'table' => $this->table], $post_id);

          $export_count = $export_count + 1;

          $data = [
            $this->plugin_prefix . 'export_count' => $export_count,
          ];

          \MetaBox\CustomTable\API::update($post_id, $this->table, $data); //save data to hidden fields
        }
      }
      wp_reset_postdata();
    }
  }


  function generate_csv()
  {
    $csv_output = ''; //Assigning the variable to store all future CSV file's data

    $result = $this->db->get_results("SHOW COLUMNS FROM " . $this->table . ""); //Displays all COLUMN NAMES under 'Field' column in records returned

    if (count($result) > 0) {

      foreach ($result as $row) {

        if (strpos($row->Field, $this->plugin_prefix) === 0) {
          $row->Field = substr($row->Field, strlen($this->plugin_prefix)); //Remove plugin prefix if it exists
        }

        $csv_output = $csv_output . $row->Field . $this->separator;
      }
      $csv_output = substr($csv_output, 0, -1); //Removing the last separator, because thats how CSVs work

    }
    $csv_output .= "\n";

    $values = $this->db->get_results("SELECT * FROM " . $this->table . " WHERE " . $this->plugin_prefix . "export_count < 1"); //This here

    foreach ($values as $rowr) {
      $fields = array_values((array) $rowr); //Getting rid of the keys and using numeric array to get values
      $csv_output .= implode($this->separator, $fields); //Generating string with field separator
      $csv_output .= "\n"; //Yeah...
    }

    $dirname = dirname($this->file_path); //Check if the directory for saving exists, and create it if needed.
    if (!is_dir($dirname)) {
      mkdir($dirname, 0755, true);
    }

    $Myfile = fopen("$this->file_path", "w") or die("Unable to open the file ");
    fwrite($Myfile, $csv_output);
    fclose($Myfile);

    //return $csv_output; //Back to constructor

  }

  function email_admin()
  {
    $settings_export_emails = \MVIFileAttachment\Settings::get_field_value('settings_export_emails');

    if ($settings_export_emails) {
      $to = "$settings_export_emails";
      $site_url = get_site_url();
      $headers = ['Content-type: text/html', "Reply-To: $to"];
      $subject = "User Download Information";
      $message = "<p>Here are the emails from people who downloaded files from your website $site_url. Only the new entries since the previous export have been included.</p>";

      wp_mail($to, $subject, $message, $headers, $this->file_path);
    }
  }
}

// Also include nonce check here - https://codex.wordpress.org/WordPress_Nonces
if (isset($_POST['processed_values']) && $_POST['processed_values'] == 'download_csv') { //When we must do this
  print_r(wp_get_schedules());
  //add_action( 'init', 'delete_old_entires' );
}
