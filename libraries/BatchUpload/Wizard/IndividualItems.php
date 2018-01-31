<?php

/**
 * Wizard for importing individual items.
 * @package Wizard
 */
class BatchUpload_Wizard_IndividualItems extends BatchUpload_Wizard_ExistingCollection
{
    public $job_type = "individual_items";
    public $job_type_description = "Individual Items";
    public $steps = 3;
    
    /**
     * Hook for what to do when a new job is created.
     * Set the target type to nothing.
     * 
     * @param BatchUpload_Job $job
     */
    public function newJob($job)
    {
        // Initialize new job target type to Collection
        $job->target_type = null;
    }
    
    /**
     * Rendering step 1's form for mapping metadata
     * @param array $args
     */
    public function step1Form($args)
    {
        parent::step2Form($args);
    }
    
    /**
     * Process step 1's form for mapping metadata
     * @param array $args
     */
    public function step1Process($args)
    {
        parent::step2Process($args);
    }
    
    /**
     * Process step 1's AJAX requests.
     * @param array $args
     */
    public function step1Ajax($args)
    {
        parent::step2Ajax($args);
    }
    
    /**
     * Rendering step 2's form for creating rows
     * @param array $args
     */
    public function step2Form($args)
    {
        parent::step3Form($args);
    }
    
    /**
     * Process step 2's form for creating rows
     * @param array $args
     */
    public function step2Process($args)
    {
        parent::step3Process($args);
    }
    
    /**
     * Process step 2's AJAX requests.
     * @param array $args
     */
    public function step2Ajax($args)
    {
        parent::step3Ajax($args);
    }
    
    /**
     * Rendering step 3's form for uploading files
     * @param array $args
     */
    public function step3Form($args)
    {
        parent::step4Form($args);
    }
    
    /**
     * Process step 3's form for uploading files
     * @param array $args
     */
    public function step3Process($args)
    {
        parent::step4Process($args);
    }
    
    /**
     * Process step 3's AJAX endpoint for uploading files
     * @param array $args
     */
    public function step3Ajax($args)
    {
        parent::step4Ajax($args);
    }
}
