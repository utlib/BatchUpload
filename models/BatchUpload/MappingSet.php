<?php

/**
 * A collection of property mappings in a set.
 * 
 * @package models
 */
class BatchUpload_MappingSet extends Omeka_Record_AbstractRecord
{
    /**
     * The name of this mapping set.
     * 
     * @var string
     */
    public $name;
    
    /**
     * The ID of the batch upload job that this mapping set is associated with.
     * If left null, it is a template.
     * 
     * @var int|null
     */
    public $job_id;
    
    /**
     * The date this mapping set was added.
     *
     * @var string
     */
    public $added;
    
    /**
     * The date this mapping set was modified.
     *
     * @var string
     */
    public $modified;
    
    /**
     * Return the job that this mapping belongs to (null for no job, i.e. it is a template).
     * @return BatchUpload_Job|null
     */
    public function getJob()
    {
        return ($this->job_id === null) ? null : get_record_by_id('BatchUpload_Job', $this->job_id);
    }
    
    /**
     * Return an array of this mapping set's mappings.
     * @return BatchUpload_Mapping
     */
    public function getMappings()
    {
        return $this->_db->getTable('BatchUpload_Mapping')->findBy(array('mapping_set_id' => $this->id));
    }
}
