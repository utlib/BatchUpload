<?php

class BatchUpload_Wizard_ExistingCollection extends BatchUpload_Application_AbstractWizard
{
    const SPECIAL_TYPE_UNMAPPED = 0;
    const SPECIAL_TYPE_TAGS = -1;
    const SPECIAL_TYPE_FILE = -2;
    const SPECIAL_TYPE_ITEMTYPE = -3;
    const SPECIAL_TYPE_COLLECTION = -4;
    const SPECIAL_TYPE_PUBLIC = -5;
    const SPECIAL_TYPE_FEATURED = -6;
    
    public $job_type = "existing_collection";
    public $steps = 3;
    
    public function newJob($job)
    {
        $job->target_type = "Collection";
    }
    
    public function step1Form($args)
    {
        $job = $args['job'];
        $partialAssigns = $args['partial_assigns'];
        $form = new BatchUpload_Form_CollectionSelect();
        $form->getElement('target_id')->setValue($job->target_id);
        $partialAssigns->set('form', $form);
        $partialAssigns->set('page_title', __("Select Target"));
    }
    
    public function step1Process($args)
    {
        $job = $args['job'];
        $partialAssigns = $args['partial_assigns'];
        $form = new BatchUpload_Form_CollectionSelect();
        if ($this->validateAndCarryForm($form))
        {
            $job->step++;
            $job->save();
        }
        else
        {
            $partialAssigns->set('form', $form);
        }
    }
    
    public function step2Form($args)
    {
        $partialAssigns = $args['partial_assigns'];
        queue_js_file('papaparse.min', 'lib/papaparse');
        queue_js_file('CsvImportMappings', 'js');
        $partialAssigns->set('page_title', __("Input Data"));
        $availablePropertiesArray = $this->__getAvailablePropertiesArray();
        $partialAssigns->set('available_properties', $this->__getAvailablePropertiesJson($availablePropertiesArray));
        $partialAssigns->set('available_properties_options', $this->__getAvailablePropertiesOptions($availablePropertiesArray));
    }
    
    public function step2Process($args)
    {
        $job = $args['job'];
        $partialAssigns = $args['partial_assigns'];
        $post = $args['post'];
        $valid = true;
        if (!isset($post['metadata']) || empty($post['metadata']))
        {
            
        }
        if ($valid)
        {
            $job->step++;
            $job->save();
        }
        else
        {
            queue_js_file('papaparse.min', 'lib/papaparse');
            queue_js_file('CsvImportMappings', 'js');
            $partialAssigns->set('page_title', __("Input Data"));
            $partialAssigns->set('available_properties', $this->_getAvailableProperties());
        }
    }
    
    private function __getAvailablePropertiesArray()
    {
        $properties = array(
            __("Special Properties") => array(
                self::SPECIAL_TYPE_UNMAPPED => __("[Unmapped]"),
                self::SPECIAL_TYPE_TAGS => __("Tags"),
                self::SPECIAL_TYPE_FILE => __("File"),
                self::SPECIAL_TYPE_ITEMTYPE => __("Item Type"),
                self::SPECIAL_TYPE_COLLECTION => __("Collection"),
                self::SPECIAL_TYPE_PUBLIC => __("Public"),
                self::SPECIAL_TYPE_FEATURED => __("Featured"),
            )
        );
        $elementSets = get_db()->getTable('ElementSet')->findAll();
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
        return $properties;
    }
    
    private function __getAvailablePropertiesJson($availableProperties=null)
    {
        if ($availableProperties === null)
        {
            $availableProperties = $this->__getAvailablePropertiesArray();
        }
        $json = array();
        // Enumerate default SET:ELEMENT combinations
        foreach ($availableProperties as $elementSetName => $elements)
        {
            foreach ($elements as $elementId => $elementName)
            {
                $json["{$elementSetName}:{$elementName}"] = $elementId;
                $json[$elementName] = $elementId;
            }
        }
        // Add a few specials
        $json['tags'] = self::SPECIAL_TYPE_TAGS;
        $json['file'] = self::SPECIAL_TYPE_FILE;
        $json['itemType'] = self::SPECIAL_TYPE_ITEMTYPE;
        $json['collection'] = self::SPECIAL_TYPE_COLLECTION;
        $json['public'] = self::SPECIAL_TYPE_PUBLIC;
        $json['featured'] = self::SPECIAL_TYPE_FEATURED;
        // Done
        return $json;
    }
    
    private function __getAvailablePropertiesOptions($availableProperties=null)
    {
        if ($availableProperties === null)
        {
            $availableProperties = $this->__getAvailablePropertiesArray();
        }
        $selectContent = '';
        foreach ($availableProperties as $elementSetName => $elements)
        {
            $selectContent .= '<optgroup label="' . html_escape($elementSetName) . '">';
            foreach ($elements as $elementId => $elementName)
            {
                $selectContent .= '<option value="' . $elementId . '">' . html_escape($elementName) . '</option>';
            }
            $selectContent .= '</optgroup>';
        }
        return $selectContent;
    }
}
