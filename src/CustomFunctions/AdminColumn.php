<?php

namespace MVIWebinarRegistration\CustomFunctions;

//Create a Meta Box custom admin column.
class AdminColumn extends \MBAC\Post
{
    public function columns($columns)
    {
        $columns = parent::columns($columns);
        $position = '';
        $target = '';
        $this->add($columns, 'full_phone', 'Phone', 'after', \MVIWebinarRegistrationBase::PLUGIN_PREFIX . 'professional_role');
        // Add more if you want
        return $columns;
    }
    public function show($column, $post_id)
    {
        switch ($column) {
            case 'full_phone':
                $table = \MVIWebinarRegistration\CustomTable::get_id();
                $rwmb_meta_args = [
                    'storage_type' => 'custom_table',
                    'table' => $table
                ];
                $country_code = rwmb_meta(\MVIWebinarRegistrationBase::PLUGIN_PREFIX . 'country_code', $rwmb_meta_args, $post_id);
                $phone = rwmb_meta(\MVIWebinarRegistrationBase::PLUGIN_PREFIX . 'phone', $rwmb_meta_args, $post_id);
                $full_phone = "+" . "$country_code" . " $phone";
                echo "<a href='tel:$full_phone'>$full_phone</a>";
                break;
                // More columns
        }
    }
}
