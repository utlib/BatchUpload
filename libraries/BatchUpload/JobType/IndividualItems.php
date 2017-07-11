<?php

/**
 * Job for importing into individual items.
 * 
 * @package BatchUpload/JobType
 */
class BatchUpload_JobType_IndividualItems extends BatchUploadPlugin_Application_AbstractJobType
{
    public function getNameHtml()
    {
        return html_escape(__("Individual Items"));
    }
    
    public function getJobTypeName()
    {
        return __("Individual Items");
    }
    
    public function getProgress()
    {
        return null;
    }
    
    public function getTargetHtml()
    {
        return html_escape(__(""));
    }
    
    public function printStepHtml($stepNum, $view)
    {
        
    }
    
    public function processStep($stepNum, $formData, $fileData)
    {
        
    }
    
    public function processUpload($step, $formData, $fileData)
    {
        
    }
}
