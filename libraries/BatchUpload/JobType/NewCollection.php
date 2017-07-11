<?php

/**
 * Job for importing into a new collection.
 * 
 * @package BatchUpload/JobType
 */
class BatchUpload_JobType_NewCollection extends BatchUploadPlugin_Application_AbstractJobType
{
    public function getNameHtml()
    {
        return html_escape(__("New Collection"));
    }
    
    public function getJobTypeName()
    {
        return __("New Collection");
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
