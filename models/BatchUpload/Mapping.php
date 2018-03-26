<?php

/**
 * A mapping from a header/ordering to a specific metadata ID or property code.
 *
 * @package models
 */
class BatchUpload_Mapping extends Omeka_Record_AbstractRecord
{
    /**
     * The source header name of the mapping.
     *
     * @var string
     */
    public $header;

    /**
     * The relative order of this mapping among other mappings of the same set.
     *
     * @var int
     */
    public $order;

    /**
     * The target element ID of the mapping.
     *
     * @var int
     */
    public $property;

    /**
     * Whether the element should contain HTML content.
     *
     * @var int
     */
    public $html;

    /**
     * The ID of the mapping set that this mapping belongs to.
     *
     * @var int
     */
    public $mapping_set_id;

    /**
     * Listing of cacheable association method calls.
     *
     * @var array
     */
    protected $_related = array(
        'mappingSet' => 'getMappingSet',
        'job' => 'getJob',
    );

    /**
     * Return the mapping set that this mapping belongs to.
     *
     * @return BatchUpload_MappingSet|null
     */
    public function getMappingSet()
    {
        return $this->_db->getTable('BatchUpload_MappingSet')->find($this->mapping_set_id);
    }

    /**
     * Return the job that this mapping corresponds to.
     *
     * @return BatchUpload_Job|null
     */
    public function getJob()
    {
        if ($mappingSet = $this->getMappingSet())
        {
            return $mappingSet->getJob();
        }
        return null;
    }
}
