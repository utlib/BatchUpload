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
     * The User ID of the owner.
     * 
     * @var int
     */
    public $owner_id;
    
    /**
     * The date this batch upload job was finished. Null if unfinished.
     * 
     * @var string
     */
    public $finished;
    
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
     * Listing of cacheable association method calls.
     * 
     * @var array
     */
    protected $_related = array(
        'mappingSet' => 'getMappingSet',
        'mappings' => 'getMappings',
    );
    
    /**
     * Initialize the mixins.
     */
    protected function _initializeMixins()
    {
        $this->_mixins[] = new Mixin_Owner($this);
    }
    
    /**
     * Return the model targeted by this job, if any.
     * @return Record
     */
    public function getTarget()
    {
        try
        {
            if ($this->target_type && $this->target_id)
            {
                return get_record_by_id($this->target_type, $this->target_id);
            }
        } catch (Exception $ex) {
            debug("Exception when finding batch upload job target: {$ex->getMessage()}");
        }
        return null;
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
     * Return the number of data rows on this job.
     * @return int
     */
    public function countUploadRows()
    {
        return $this->_db->getTable('BatchUpload_Row')->count(array('job_id' => $this->id));
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
     * Return whether this job is finished.
     * @return bool
     */
    public function isFinished()
    {
        return $this->finished !== null;
    }
    
    /**
     * Mark this job as finished.
     */
    public function finish()
    {
        $this->finished = date("Y-m-d H:i:s");
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
    
    /**
     * HOOK: Before-save hook.
     * - Initialize new jobs to start at step 1.
     * 
     * @param array $args
     */
    protected function beforeSave($args)
    {
        if ($args['post'])
        {
            if ($args['insert']) {
                $this->step = 1;
            }
        }
    }
    
    /**
     * HOOK: Validate hook.
     * - Name must be filled.
     * - Target type must be filled.
     */
    protected function _validate()
    {
        // Name must be filled
        if (!$this->name)
        {
            $this->addError('name', __('Name must be filled.'));
        }
        // Target type must be filled
        if (!$this->job_type)
        {
            $this->addError('job_type', __('Target type must be filled.'));
        }
    }
}
