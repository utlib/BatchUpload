<?php

class BatchUpload_Wizard_NewCollection extends BatchUpload_Wizard_ExistingCollection
{
    public $job_type = "new_collection";
    public $steps = 4;
    
    /**
     * Rendering step 1's form for creating the target collection.
     * @param array $args
     */
    public function step1Form($args)
    {
        $job = $args['job'];
        $partialAssigns = $args['partial_assigns'];
        $form = new BatchUpload_Form_CollectionCreate();
        $form->getElement('name')->setValue($job->target_id);
        $partialAssigns->set('form', $form);
        $partialAssigns->set('page_title', __("Create Target Collection"));
    }
    
    /**
     * Processing step 1's form for creating the target collection.
     * @param array $args
     */
    public function step1Process($args)
    {
        $job = $args['job'];
        $partialAssigns = $args['partial_assigns'];
        $form = new BatchUpload_Form_CollectionCreate();
        // If valid, record the target ID, go to the next step and save
        if ($this->validateAndCarryForm($form))
        {
            $newCollection = new Collection();
            $newCollection->addElementTextsByArray(array(
                'Dublin Core' => array(
                    'Title' => array(
                        array('text' => $form->getElement('name')->getValue(), 'html' => false),
                    )
                ),
            ));
            $newCollection->save();
            $job->target_id = $newCollection->id;
            $job->step++;
            $job->save();
        }
        // If invalid, go back to the form
        else
        {
            $partialAssigns->set('form', $form);
            $partialAssigns->set('page_title', __("Select Target"));
        }
    }
}
