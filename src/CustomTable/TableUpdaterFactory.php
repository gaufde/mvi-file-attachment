<?php

namespace MVIWebinarRegistration\CustomTable;


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
                return new \MVIWebinarRegistration\CustomTable\Versions\TableV1;
            default:
                throw new \Exception('Invalid database version');
        }
    }
}
