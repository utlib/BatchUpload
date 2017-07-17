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
     * The ID of the user who owns this mapping set.
     * 
     * @var int
     */
    public $owner_id;
    
    /**
     * Listing of cacheable association method calls.
     * 
     * @var array
     */
    protected $_related = array(
        'job' => 'getJob',
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
     * Return the job that this mapping belongs to (null for no job, i.e. it is a template).
     * @return BatchUpload_Job|null
     */
    public function getJob()
    {
        return ($this->job_id === null || $this->id === null) ? null : get_record_by_id('BatchUpload_Job', $this->job_id);
    }
    
    /**
     * Return an array of this mapping set's mappings.
     * @return BatchUpload_Mapping[]
     */
    public function getMappings()
    {
        return ($this->id === null) ? array() : $this->_db->getTable('BatchUpload_Mapping')->findBy(array('mapping_set_id' => $this->id));
    }
    
    /**
     * Return the number of mappings contained in this mapping set.
     * @return int
     */
    public function countMappings()
    {
        return ($this->id === null) ? 0 : $this->_db->getTable('BatchUpload_Mapping')->count(array('mapping_set_id' => $this->id));
    }
    
    /**
     * Return an array-only representation of the mappings in this mapping set. For use with forms.
     * Format: [{header, order, property, html}, {header, order, property, html}, ...]
     * @return array
     */
    public function getMappingsArray()
    {
        $mappingsArray = array();
        foreach ($this->getMappings() as $mapping)
        {
            $mappingsArray[] = array(
                '_id' => $mapping->id,
                'header' => $mapping->header,
                'order' => $mapping->order,
                'property' => $mapping->property,
                'html' => $mapping->html,
            );
        }
        if (empty($mappingsArray))
        {
            $mappingsArray[] = array(
                'header' => '',
                'order' => 1,
                'property' => '',
                'html' => false,
            );
        }
        return $mappingsArray;
    }
    
    /**
     * Before-save hook.
     * @param array $args
     */
    protected function beforeSave($args)
    {
        $mappingTable = get_db()->getTable('BatchUpload_Mapping');
        if ($args['post'])
        {
            $post = $args['post'];
            foreach ($post['mappings'] as $mappingRow)
            {
                $rowId = isset($mappingRow['_id']) ? $mappingRow['_id'] : null;
                $rowDelete = isset($mappingRow['_delete']);
                // Row to be deleted: Check that it exists if it is pre-existing
                // Deleting non-preexisting rows = Do nothing
                if ($rowDelete)
                {
                    if ($rowId !== null)
                    {
                        $mapping = $mappingTable->find($rowId);
                        if (!$mapping || ($this->id && $mapping->mapping_set_id != $this->id))
                        {
                            $this->addError('', __("Trying to delete non-member mapping %d", $rowId));
                        }
                    }
                }
                // Existing row: Check that it is a member and it exists
                elseif ($rowId !== null)
                {
                    $mapping = $mappingTable->find($rowId);
                    if (!$mapping || ($this->id && $mapping->mapping_set_id != $this->id))
                    {
                        $this->addError('', __("Trying to edit non-member mapping %d", $rowId));
                    }
                }
                // Check that it has all the right things
                if (empty($mappingRow['header']) && empty($mappingRow['_delete']))
                {
                    $this->addError('', __("Header on mapping row %d must be filled", $mappingRow['order']));
                }
            }
        }
    }
    
    /**
     * After-save hook.
     * @param array $args
     */
    protected function afterSave($args)
    {
        // Save mappings
        $mappingTable = get_db()->getTable('BatchUpload_Mapping');
        if ($args['post'])
        {
            $post = $args['post'];
            // Go through all rows
            foreach ($post['mappings'] as $mappingRow)
            {
                $rowId = isset($mappingRow['_id']) ? $mappingRow['_id'] : null;
                $rowDelete = isset($mappingRow['_delete']);
                // Row to be deleted: Delete it
                if ($rowDelete)
                {
                    if ($rowId !== null) {
                        $mapping = $mappingTable->find($rowId)->delete();
                    }
                }
                // Existing or new row: Set properties and save
                else
                {
                    $mapping = ($rowId !== null) ? $mappingTable->find($rowId) : new BatchUpload_Mapping();
                    $filteredPost = array_intersect_key($mappingRow, array(
                        'header' => true, 
                        'order' => true, 
                        'property' => true, 
                        'html' => true
                    ));
                    $mapping->setPostData(array_merge($filteredPost, array(
                        'mapping_set_id' => $this->id
                    )));
                    $mapping->save();
                }
            }
        }
    }
    
    /**
     * Validation hook.
     */
    protected function _validate()
    {
        // Name must be filled in
        if ($this->name == '')
        {
            $this->addError('name', __('Name must be filled in.'));
        }
    }
}
