<?php

/**
 * Controller for mapping sets.
 *
 * @package controllers
 */
class BatchUpload_MappingSetsController extends BatchUpload_Application_AbstractActionController
{
    /**
     * Actions requiring an AJAX response.
     * @var string[]
     */
    private $_ajaxRequiredActions = array(
    );

    /**
     * Associative array from action to array of allowed verbs.
     * @var array
     */
    private $_methodRequired = array(
    );

    /**
     * Set up the controller.
     */
    public function init()
    {
        $this->_helper->db->setDefaultModelName('BatchUpload_MappingSet');
    }

    /**
     * HOOK: Pre-dispatch.
     */
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

    /**
     * Main browse action for reusable mapping sets (i.e. not attached to jobs).
     * GET /batch-upload/mapping-sets/browse
     */
    public function browseAction()
    {
        $table = $this->_helper->db->getTable();
        $select = $table->getSelect();
        $select->where('job_id IS NULL');
        $this->view->batch_upload_mapping_sets = $table->fetchObjects($select);
    }

    /**
     * Override the default browse order to descending by added date.
     * @return array
     */
    protected function _getBrowseDefaultSort()
    {
        return array('added', 'd');
    }

    /**
     * Action for creating mapping templates.
     * GET/POST /batch-upload/mapping-sets/add
     */
    public function addAction()
    {
        parent::addAction();
        $this->_fillMappingsArray();
        $this->_fillAvailableProperties();
    }

    /**
     * Override the redirect after creating a mapping template back to browse.
     */
    protected function _redirectAfterAdd()
    {
        $this->_helper->redirector('browse', null, null, array());
    }

    /**
     * Override the success message after creating a mapping template.
     * @param Record $record The record being created.
     * @return string
     */
    protected function _getAddSuccessMessage($record)
    {
        return __('The mapping set "%s" was successfully added!', $record->name);
    }

    /**
     * Action for editing mapping templates.
     * GET/POST /batch-upload/mapping-sets/edit
     */
    public function editAction()
    {
        parent::editAction();
        $this->_fillMappingsArray();
        $this->_fillAvailableProperties();
    }

    /**
     * Override the success message after editing a mapping template.
     * @param Record $record The record being edited.
     * @return string
     */
    protected function _getEditSuccessMessage($record)
    {
        return __('The mapping set "%s" was successfully changed!', $record->name);
    }

    /**
     * Override the redirect after editing a mapping template back to browse.
     */
    protected function _redirectAfterEdit($record)
    {
        $this->_helper->redirector('browse', null, null, array());
    }

    /**
     * Override the success message after deleting a mapping template.
     * @param Record $record The record being deleted.
     * @return string
     */
    protected function _getDeleteSuccessMessage($record)
    {
        return __('The mapping set "%s" was successfully deleted!', $record->name);
    }

    /**
     * Populate the view's list of mappings, in array form.
     */
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

    /**
     * Populate the view's list of available properties.
     */
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

    /**
     * Action for downloading CSV files from a mapping template.
     * GET/POST /batch-upload/mapping-sets/template/:id
     * @throws Omeka_Controller_Exception_404
     */
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
