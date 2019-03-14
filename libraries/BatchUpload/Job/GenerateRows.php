<?php

/**
 * Background job for generating rows from submitted metadata gathered from CSV.
 *
 * @package Job
 */
class BatchUpload_Job_GenerateRows extends Omeka_Job_AbstractJob {
    /**
     * The default internal separator for column values.
     * @var string
     */
    protected $_separator = ';';
    
    /**
     * The BatchUpload_Job ID that this background job is running for.
     * @var int
     */
    private $_jobId;

    /**
     * CSV data in [[col0...col(n-1)], ...] form
     * @var array
     */
    private $_csvData;

    /**
     * Metadata in [{header: ?, property: ?, `html: `}, ...] form (backticks = optional)
     * @var array
     */
    private $_metadataPostData;

    /**
     * Initialize this background job.
     * @param array $options
     */
    public function __construct(array $options)
    {
        parent::__construct($options);
        $this->_jobId = $options['jobId']; // The ID of the batch upload job
        debug("Processing batch upload job #{$this->_jobId}}");
        debug(count($this->_csvData) . " CSV rows");
    }

    /**
     * Main runnable method
     */
    public function perform()
    {
        try {
            $this->_perform();
        } catch (Exception $ex) {
            debug($ex->getMessage());
            debug($ex->getTraceAsString());
            $job = get_record_by_id('BatchUpload_Job', $this->_jobId);
            if ($job)
            {
                $job->step--;
                $job->save();
            }
        }
    }

    /**
     * Core running part.
     */
    protected function _perform()
    {
        debug('Starting job');
        // Start up
        $db = get_db();
        // Get the batch upload job
        $job = $db->getTable('BatchUpload_Job')->find($this->_jobId);
        // Grab metadata from it
        $jobMetadata = $job->getJsonData();
        $this->_csvData = $jobMetadata['csvData']; // CSV data in [[col0...col(n-1)], ...] form
        $this->_metadataPostData = $jobMetadata['metadata']; // Metadata in [{header: ?, property: ?, `html: `}, ...] (backticks = optional)
        // Remember where any files need to be uploaded
        $uploadRowOrder = 1;
        $needsUpload = false;
        // For each row of data:
        foreach ($this->_csvData as $dataRow)
        {
            // Create buckets for different kinds of metadata
            $urls = array();
            $uploads = array();
            $metadata = array();
            $specialProperties = array(
                'collection_id' => $job->target_id,
                'tags' => '',
                'item_type_id' => null,
                'public' => false,
                'featured' => false,
            );
            // For each metadata mapping
            foreach ($dataRow as $i => $v)
            {
                // Find corresponding metadata key
                $k = $this->_metadataPostData[$i]['property'];
                $internalSeparator = empty($this->_metadataPostData[$i]['separator']) ? $this->_separator : $this->_metadataPostData[$i]['separator'];
                // Discard if it is property 0 (unmapped) or its value is empty:
                if (empty($k) || $k == BatchUpload_Wizard_ExistingCollection::SPECIAL_TYPE_UNMAPPED || empty(trim($v)))
                {
                    continue;
                }
                // Otherwise, if the property is -2 (file):
                elseif ($k == BatchUpload_Wizard_ExistingCollection::SPECIAL_TYPE_FILE)
                {
                    // If it starts with http: or https:, queue it as a file
                    if (substr($v, 0, 7) == 'http://' || substr($v, 0, 8) == 'https://')
                    {
                        $vurls = explode($internalSeparator, $v);
                        foreach ($vurls as $vurl)
                        {
                            $urls[] = trim($vurl);
                        }
                    }
                    // Otherwise, record it as an upload
                    else
                    {
                        $vuploads = explode($internalSeparator, $v);
                        foreach ($vuploads as $vupload)
                        {
                            $uploads[] = trim($vupload);
                        }
                        // This job stills needs uploading
                        $needsUpload = true;
                    }
                }
                // Otherwise, if the property is any "special" property (<0):
                elseif ($k < 0)
                {
                    switch ($k)
                    {
                        case BatchUpload_Wizard_ExistingCollection::SPECIAL_TYPE_TAGS:
                            if (empty($specialProperties['tags']))
                            {
                                $specialProperties['tags'] = $v;
                            }
                            else
                            {
                                $specialProperties['tags'] .= ",{$v}";
                            }
                            break;
                        case BatchUpload_Wizard_ExistingCollection::SPECIAL_TYPE_ITEMTYPE:
                            $tentativeItemType = $db->getTable('ItemType')->findByName($v);
                            if (!empty($tentativeItemType))
                            {
                                $specialProperties['item_type_id'] = $tentativeItemType->id;
                            }
                            break;
                        case BatchUpload_Wizard_ExistingCollection::SPECIAL_TYPE_COLLECTION:
                            $tentativeCollection = $this->__getCollectionByName($db, $v);
                            if (!empty($tentativeCollection))
                            {
                                $specialProperties['collection_id'] = $tentativeCollection->id;
                            }
                            break;
                        case BatchUpload_Wizard_ExistingCollection::SPECIAL_TYPE_PUBLIC:
                            $specialProperties['public'] = !!$v;
                            break;
                        case BatchUpload_Wizard_ExistingCollection::SPECIAL_TYPE_FEATURED:
                            $specialProperties['featured'] = !!$v;
                            break;
                    }
                }
                // Otherwise, set as a regular element ID (>0)
                else
                {
                    $tentativeElement = get_record_by_id('Element', $k);
                    if (empty($tentativeElement))
                    {
                        continue;
                    }
                    $tentativeElementSet = $tentativeElement->getElementSet();
                    if (empty($tentativeElementSet))
                    {
                        continue;
                    }
                    if (!isset($metadata[$tentativeElementSet->name]))
                    {
                        $metadata[$tentativeElementSet->name] = array();
                    }
                    if (!isset($metadata[$tentativeElementSet->name][$tentativeElement->name]))
                    {
                        $metadata[$tentativeElementSet->name][$tentativeElement->name] = array();
                    }
                    $texts = explode($internalSeparator, $v);
                    foreach ($texts as $text)
                    {
                        $metadata[$tentativeElementSet->name][$tentativeElement->name][] = array(
                            'text' => trim($text),
                            'html' => isset($this->_metadataPostData[$i]['html']),
                        );
                    }
                }
            }
            // Create the item with as much information as possible
            $newItem = insert_item($specialProperties, $metadata, array('file_transfer_type' => 'Url', 'files' => $urls));
            debug("Created new item " . $newItem->id);
            // If there are uploads, create a job data row with { "file": "str", "fileid": null, "order": int, "item": int }
            if (!empty($uploads))
            {
                $uploads = array_filter($uploads); //filter out empty file uploads created through separation
                foreach ($uploads as $uploadNum => $upload)
                {
                    $newJobRow = new BatchUpload_Row();
                    $newJobRow->order = $uploadRowOrder+1;
                    $newJobRow->job_id = $this->_jobId;
                    $newJobRow->setJsonData(array(
                        'file' => $upload,
                        'fileOrder' => $uploadNum,
                        'fileId' => null,
                        'item' => $newItem->id,
                    ));
                    $newJobRow->save();
                }
            }
        }
        // If any row has been generated:
        if ($needsUpload)
        {
            // Go to step 4 (Upload Items)
            $job->step++;
            $job->save();
        }
        // Otherwise:
        else
        {
            // Finish the job
            $job->finish();
            $job->save();
        }
    }

    /**
     * Utility for finding a collection by name.
     * @param Omeka_Db $db
     * @param string $name Name of the collection.
     * @return Collection
     */
    private function __getCollectionByName($db, $name)
    {
        $element = $db->getTable('Element')->findByElementSetNameAndElementName('Dublin Core', 'Title')->id;
        $collectionTable = $db->getTable('Collection');
        $select = $collectionTable->getSelect();
        $select->joinInner(array('s' => $db->ElementText), 's.record_id = collections.id', array());
        $select->where("s.record_type = 'Collection'");
        $select->where("s.element_id = ?", $element->id);
        $select->where("s.text = ?", $name);
        return $collectionTable->fetchObject($select);
    }
}
