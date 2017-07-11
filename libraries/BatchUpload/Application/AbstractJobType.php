<?php

/**
 * Abstract template for job types.
 * 
 * @package BatchUpload
 */
abstract class BatchUpload_Application_AbstractJobType
{
    /**
     * Number of the current step.
     * @var int
     */
    protected $_steps;
    /**
     * Name of the job type.
     * @var string
     */
    protected $_name;
    
    /**
     * Render the form for the given step.
     * 
     * @param int $step
     * @param Zend_Request $request
     * @param Omeka_View $view
     */
    public function renderStep($step, $request, $view)
    {
    }
    
    /**
     * 
     * @param int $step
     * @param Zend_Request $request
     */
    public function updateStep($step, $request, $view)
    {
    }
    
    /**
     * 
     * @param int $step
     * @param Zend_Request $request
     */
    public function processStep($step, $request, $view)
    {
    }
    
    
}
