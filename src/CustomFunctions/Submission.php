<?php
namespace MVIFileAttachment\CustomFunctions;

class Submission{
			private $config;
			public $post_id;

			public function __construct( $config, $post_id ) {
					$this->config = $config;
					$this->post_id = $post_id;
					$this->plugin_prefix = \MVIFileAttachmentBase::PLUGIN_PREFIX;

					$this->email = $_POST[$this->plugin_prefix . 'email'];
			    $this->email = mb_strtolower($this->email);
			    $this->first_name = $_POST[$this->plugin_prefix . 'first_name'];
			    $this->last_name = $_POST[$this->plugin_prefix . 'last_name'];
					$this->reference_post_id = (int) $_POST[$this->plugin_prefix . 'reference_post_id'];
			    $this->reference_post_url = get_permalink( $this->reference_post_id);
					$this->reference_post_name = get_the_title( $this->reference_post_id);
					$this->download_id = sha1(uniqid($this->post_id, true)); //create a unique token to use for the download link
					$this->download_url = get_site_url() . "/download/" . $this->download_id ."/";
			    $this->professional_role = $_POST[$this->plugin_prefix . 'professional_role'];
			    $this->subscribe = $_POST[$this->plugin_prefix . 'subscribe'];

					$this->files = rwmb_meta( $this->plugin_prefix . 'post_download_file', array( 'limit' => 1 ), $this->reference_post_id); //get only the first file from the array
					$this->file = reset( $this->files );
					$this->download_name = $this->file['name'];
			    $this->tstamp = $_SERVER["REQUEST_TIME"];
			}

			public function save_to_db() {
					global $wpdb;
	        $data = [
	            'download_id' => $this->download_id,
	            'download_name' => $this->download_name,
	            'tstamp' => $this->tstamp,
	        ];

					\MVIFileAttachment\CustomTable::update_values_no_prefix( $this->post_id, $data ); //save data to hidden fields
	    }

			public function email_user() {
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

		        wp_mail( $to, $subject, $message, $headers); //email user
					}
	   }

	 public function newsletter_subscribe(): bool {
       if($this->subscribe == 1) {
         return true;
       } else {
         return false;
       }
   }

	 public function get_new_mailchimp_tags() {
		 $tags = [$this->reference_post_name, "Downloaded PDF", $this->professional_role];
		 return $tags;
	 }

		public function get_update_mailchimp_tags() {
			$tags = [
	      [
	        "name" => $this->reference_post_name,
	        "status" => "active"
	      ],
	      [
	        "name" => "Downloaded PDF",
	        "status" => "active"
	      ],
	      [
	        "name" => $this->professional_role,
	        "status" => "active"
	      ]
	    ];
			return $tags;
		}

		public function get_merge_var() {
			$merge_var = [
	      'FNAME' => $this->first_name,
	      'LNAME' => $this->last_name,
	    ];
			return $merge_var;
		}

		public function professional_role_tags_to_remove (array $subscriber_tags) {

			$tags_to_remove = [];

	    $professional_role_tags = new ProfessionalRoleTagsArray;
	    $professional_role_tags = $professional_role_tags->generate_slug_array();

	    $current_professional_role_tags = array_intersect($subscriber_tags, $professional_role_tags);

	    foreach ($current_professional_role_tags as $current_professional_role_tag) {
	      $tags_to_remove[] = [
	        "name" => $current_professional_role_tag,
	        "status" => "inactive"
	      ];
	    }

	    return $tags_to_remove;
	  }

}
