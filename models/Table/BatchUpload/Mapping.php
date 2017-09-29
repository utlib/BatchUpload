<?php

/**
 * Table for header mappings.
 * @package models/Table
 */
class Table_BatchUpload_Mapping extends Omeka_Db_Table
{
    /**
     * Add initial conditions to selections from the database.
     * Sort by the order.
     *
     * @return Omeka_Db_Select
     */
    public function getSelect()
    {
        $select = parent::getSelect();
        $select->order('order ASC');
        return $select;
    }
}
