<?php

/**
 * An upload job and its associated data and mappings.
 * 
 * @package models
 */
class BatchUpload_Job extends Omeka_Record_AbstractRecord
{
    /**
     * The name of this batch upload job.
     * 
     * @var string
     */
    public $name;
    
    /**
     * The current step number that this batch upload job is on.
     * 
     * @var int
     */
    public $step;
    
    /**
     * The name of the job type that this batch upload job is.
     * 
     * @var string
     */
    public $job_type;
    
    /**
     * The name of the model that this batch upload job targets, if any.
     * 
     * @var string
     */
    public $target_type;
    
    /**
     * The ID of the model that this batch upload job targets, if any.
     * 
     * @var int
     */
    public $target_id;
    
    /**
     * Serialized data attached to this batch upload job.
     * 
     * @var string
     */
    public $data;
    
    /**
     * The date this batch upload job was added.
     *
     * @var string
     */
    public $added;
    
    /**
     * The date this batch upload job was modified.
     *
     * @var string
     */
    public $modified;
    
    /**
     * Return the model targeted by this job.
     * @return Record
     */
    public function getTarget()
    {
        return get_record_by_id($this->target_type, $this->target_id);
    }
    
    /**
     * Return the JSON-decoded data of this job.
     * @return array
     */
    public function getJsonData()
    {
        return json_decode($this->data, true);
    }
    
    /**
     * Set and return the JSON-encoded data of this job.
     * @param array $json
     * @return array
     */
    public function setJsonData($json)
    {
        return $this->data = json_encode($json, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);
    }
    
    /**
     * Return an array of this job's data rows.
     * @return BatchUpload_Row[]
     */
    public function getUploadRows()
    {
        return $this->_db->getTable('BatchUpload_Row')->findBy(array('job_id' => $this->id));
    }
    
    /**
     * Return the mapping set of this job.
     * @return BatchUpload_MappingSet|null
     */
    public function getMappingSet()
    {
        return $this->_db->getTable('BatchUpload_MappingSet')->findBySql('job_id = ?', array($this->id), true);
    }
    
    /**
     * Return all mappings associated with this job.
     * @return BatchUpload_Mapping[]
     */
    public function getMappings()
    {
        if ($mappingSet = $this->getMappingSet())
        {
            return $mappingSet->getMappings();
        }
        return array();
    }
    
    /**
     * Insert a row into this job and return the added row.
     * @param mixed $raw
     * @return BatchUpload_Row The inserted row.
     */
    public function addRow($raw)
    {
        $table = $this->_db->getTable('BatchUpload_Row');
        $insertedId = $this->_db->insert($this->_db->BatchUpload_Row, array(
            'job_id' => $this->id,
            'order' => $table->count(array('job_id' => $this->id)),
            'data' => $raw,
        ));
        return $table->find($insertedId);
    }
    
    /**
     * Insert a JSON row into this job and return the added row.
     * @param array $json
     * @return BatchUpload_Row The inserted row.
     */
    public function addJsonRow($json)
    {
        return $this->addRow(json_encode($json, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE));
    }
    
    /**
     * Insert a CSV row into this job and return the added row.
     * @param string[] $headers
     * @param string[] $values
     * @return BatchUpload_Row The inserted row.
     */
    public function addCsvRow($headers, $values)
    {
        return $this->addJsonRow(array_combine($headers, $values));
    }
}
