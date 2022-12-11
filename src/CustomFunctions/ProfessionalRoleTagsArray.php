<?php
namespace MVIFileAttachment\CustomFunctions;

//ProfessionalRoleTagsArray is responsible for creating an array of job titles that are used by both Mailchimp and MB.
//The class ensures the arrays always agree with eachother for updating the Mailchimp database.
Class ProfessionalRoleTagsArray{
  public function __construct() {
    $this->professional_role_tags = ["Professor", "Industry Professional", "Post Doc", "Graduate Student", "Job Seeker", "Test"];
  }

  public function generate_associative_array() {

    foreach ($this->professional_role_tags as $professional_role_tag){
      //Create and associative array that can be fed to MB to make the field work properly.
      $tags[$this->slug($professional_role_tag)] = "$professional_role_tag";
    }
    return $tags;
  }


  public function generate_slug_array() {

    foreach ($this->professional_role_tags as $professional_role_tag){
      //generate an array of slugs for Mailchimp functions.
      $tags[] = $this->slug($professional_role_tag);
    }
    return $tags;
  }

  //Get rid of spaces and other characters, replace with underscrore.
  public function slug($value) {
    $value = strtolower(trim(preg_replace('/[^A-Za-z0-9-]+/', '_', $value)));
    return $value;
  }

}
