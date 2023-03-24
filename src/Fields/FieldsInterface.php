<?php

namespace MVIFileAttachment\Fields;

interface FieldsInterface
{

  const PLUGIN_PREFIX = \MVIFileAttachmentBase::PLUGIN_PREFIX;


  /**
   * Return the fields to register as an array
   *
   * @return string
   */
  static function get_id();

  /**
   * Return the fields to register as an array
   *
   * @return array
   */
  static function return_fields();
}
