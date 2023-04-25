<?php

namespace MVIFileAttachment\CustomTable;


class TableUpdaterFactory
{
    /**
     * Create an updater depending on the version numbers
     * 
     * @return TableVersionInterface
     */
    public static function create_table_updater($old_db_ver)
    {

        switch ($old_db_ver) {
            case 0:
                return new \MVIFileAttachment\CustomTable\Versions\TableV1;
            case 1:
                return new \MVIFileAttachment\CustomTable\Versions\TableV2;
            default:
                throw new \Exception('Invalid database version');
        }
    }
}
