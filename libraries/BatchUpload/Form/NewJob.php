<?php

/**
 * Form helper for adding a new job.
 * 
 * @package Form
 */
class BatchUpload_Form_NewJob extends Omeka_Form
{
    /**
     * Set up elements in the form.
     */
    public function init()
    {
        // Top-level parent
        parent::init();
        $this->applyOmekaStyles();
        $this->setAutoApplyOmekaStyles(false);
        $this->setAttrib('id', 'new_batch_upload_job_form');
        $this->setAttrib('method', 'POST');
        // Name
        $this->addElement('text', 'name', array(
            'label' => __("Job Name"),
            'description' => __("The name for this batch upload job."),
            'required' => true,
        ));
        // Source
        $availableJobTypes = apply_filters('batch_upload_register_job_type', array());
        $this->addElement('radio', 'job_type', array(
            'label' => __("Target"),
            'description' => __("Select where or what to start the upload job for."),
            'multiOptions' => $availableJobTypes,
            'required' => true,
        ));
    }
}
