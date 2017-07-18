<?php

class BatchUpload_Wizard_ExistingCollection extends BatchUpload_Application_AbstractWizard
{
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
        $partialAssigns->set('page_title', 'Select Target');
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
}
