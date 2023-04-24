<?php

namespace MVIWebinarRegistration\CustomTable\Versions;

/**
 * Define the function required to update from the previous version to this version.
 * 
 * @return bool $version
 */
interface TableVersionInterface
{
  public function update_table(): bool;
}
