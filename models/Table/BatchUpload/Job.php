<?php

/**
 * Table for batch upload jobs.
 * @package models/Table
 */
class Table_BatchUpload_Job extends Omeka_Db_Table
{
    /**
     * Add initial conditions to selections from the database.
     * Protect against unauthorized access.
     *
     * @return Omeka_Db_Select
     */
    public function getSelect()
    {
        $select = parent::getSelect();
        $user = current_user();
        if ($user)
        {
            if ($user->role == 'researcher')
            {
                $select->where('1 = 0');
            }
            elseif ($user->role == 'contributor')
            {
                $select->where('owner_id = ?', array($user->id));
            }
        }
        return $select;
    }
}
