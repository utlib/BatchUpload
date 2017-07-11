<?php

/**
 * A single row of uploaded data.
 * 
 * @package models
 */
class BatchUpload_Row extends Omeka_Record_AbstractRecord
{
    /**
     * The ID of the job that this row is associated with.
     * 
     * @var int
     */
    public $job_id;
    
    /**
     * The relative ordering of this row among rows of the job.
     * 
     * @var int
     */
    public $order;
    
    /**
     * Serialized data attached to this row.
     * 
     * @var string
     */
    public $data;
    
    /**
     * Return the job that this row belongs to.
     * @return BatchUpload_Job
     */
    public function getJob()
    {
        return get_record_by_id('BatchUpload_Job', $this->job_id);
    }
    
    /**
     * Return the JSON-decoded data of this row.
     * @return array
     */
    public function getJsonData()
    {
        return json_decode($this->data, true);
    }
    
    /**
     * Set and return the JSON-encoded data of this row.
     * @param array $json
     * @return array
     */
    public function setJsonData($json)
    {
        return $this->data = json_encode($json, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);
    }
    
    /**
     * Set and return the JSON-encoded value of this job based on given CSV headers and row values
     * @param string[] $headers
     * @param string[] $values
     * @return array
     */
    public function setJsonDataFromCsv($headers, $values)
    {
        return $this->setJsonData(array_combine($headers, $values));
    }
}
