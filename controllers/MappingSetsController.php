<?php

/**
 * Controller for mapping sets.
 * 
 * @package controllers
 */
class BatchUpload_MappingSetsController extends BatchUpload_Application_AbstractActionController
{
    private $_ajaxRequiredActions = array(
    );

    private $_methodRequired = array(
    );
    
    public function init()
    {
        $this->_helper->db->setDefaultModelName('BatchUpload_MappingSet');
    }
    
    public function preDispatch()
    {
        $action = $this->getRequest()->getActionName();
        if (in_array($action, $this->_ajaxRequiredActions))
        {
            if (!$this->getRequest()->isXmlHttpRequest())
            {
                return $this->_forward('not-found', 'error');
            }
        }
        if (array_key_exists($action, $this->_methodRequired))
        {
            if (!in_array($this->getRequest()->getMethod(), $this->_methodRequired[$action]))
            {
                return $this->_forward('method-not-allowed', 'error');
            }
        }
    }
    
    public function browseAction()
    {
        $table = $this->_helper->db->getTable();
        $select = $table->getSelect();
        $select->where('job_id IS NULL');
        $this->view->batch_upload_mapping_sets = $table->fetchObjects($select);
    }
    
    protected function _getBrowseDefaultSort()
    {
        return array('added', 'd');
    }
    
    public function addAction()
    {
        parent::addAction();
        $this->_fillMappingsArray();
        $this->_fillAvailableProperties();
    }
    
    protected function _redirectAfterAdd()
    {
        $this->_helper->redirector('browse', null, null, array());
    }
    
    protected function _getAddSuccessMessage($record)
    {
        return __('The mapping set "%s" was successfully added!', $record->name);
    }
    
    public function editAction()
    {
        parent::editAction();
        $this->_fillMappingsArray();
        $this->_fillAvailableProperties();
    }
    
    protected function _getEditSuccessMessage($record)
    {
        return __('The mapping set "%s" was successfully changed!', $record->name);
    }
    
    protected function _redirectAfterEdit()
    {
        $this->_helper->redirector('browse', null, null, array());
    }
    
    protected function _getDeleteSuccessMessage($record)
    {
        return __('The mapping set "%s" was successfully deleted!', $record->name);
    }
    
    protected function _fillMappingsArray()
    {
        if ($this->getRequest()->isPost())
        {
            $this->view->mappings_array = $this->getParam('mappings');
        }
        else
        {
            $this->view->mappings_array = $this->view->batch_upload_mapping_set->getMappingsArray();
        }
    }
    
    protected function _fillAvailableProperties()
    {
        $properties = array(
            __("Special Properties") => array(
                0 => __("<Unmapped>"),
                -1 => __("Tags"),
                -2 => __("File"),
                -3 => __("Item Type"),
                -4 => __("Collection"),
                -5 => __("Public"),
                -6 => __("Featured"),
            )
        );
        $elementSets = $this->_helper->db->getTable('ElementSet')->findAll();
        foreach ($elementSets as $elementSet)
        {
            $idNamePairs = array();
            $elementTexts = $elementSet->getElements();
            foreach ($elementTexts as $elementText)
            {
                $idNamePairs[$elementText->id] = $elementText->name;
            }
            $properties[$elementSet->name] = $idNamePairs;
        }
        $this->view->available_properties = $properties;
    }
    
    public function templateAction()
    {
        // Find the mapping set template
        $mappingSet = $this->_helper->db->getTable('BatchUpload_MappingSet')->find($this->getParam('id'));
        // Find all fields with a File special property
        $fileMappings = array();
        foreach ($mappingSet->getMappings() as $mapping)
        {
            if ($mapping->property === BatchUpload_Wizard_ExistingCollection::SPECIAL_TYPE_FILE)
            {
                $fileMappings[] = $mapping;
            }
        } 
        // GET: Render file spec form
        $request = $this->getRequest();
        if ($request->isGet())
        {
            //? If there is no field with a File special property (-2), render CSV right away
            if (empty($fileMappings))
            {
                $this->getResponse()->setHeader('Content-Disposition', 'attachment; filename=' . rawurlencode($mappingSet->name) . '.csv');
                $this->respondWithRaw($mappingSet->getCsvTemplate(), 200, 'text/csv');
            }
            // Otherwise, proceed to form for file scanning
            queue_js_file('CsvTemplateForm', 'js');
            $this->view->file_mappings = $fileMappings;
        }
        // POST: Getting file mapping submissions
        elseif ($request->isPost())
        {
            // Render CSV
            $fileSpecs = $this->getParam('filespecs');
            $csv = $mappingSet->getCsvTemplate($fileSpecs);
            $this->getResponse()->setHeader('Content-Disposition', 'attachment; filename=' . rawurlencode($mappingSet->name) . '.csv');
            $this->respondWithRaw($csv, 200, 'text/csv; charset=utf-8');
            
        }
        // Other verbs: Don't handle
        else
        {
            throw new Omeka_Controller_Exception_404;
        }
    }
}
