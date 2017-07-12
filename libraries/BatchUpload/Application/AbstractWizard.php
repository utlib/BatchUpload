<?php

abstract class BatchUpload_Application_AbstractWizard
{
    public $job_type = "abstract_wizard";
    public $job_type_description = "Abstract Wizard";
    public $steps = 1;
    
    public final function integrate()
    {
        $jobTypeSlug = Inflector::underscore($this->job_type);
        add_plugin_hook("batch_upload_{$jobTypeSlug}_job_new", array($this, '_newJob'));
        add_plugin_hook("batch_upload_{$jobTypeSlug}_step_form", array($this, '_stepForm'));
        add_plugin_hook("batch_upload_{$jobTypeSlug}_step_process", array($this, '_stepProcess'));
        add_plugin_hook("batch_upload_{$jobTypeSlug}_step_ajax", array($this, '_stepAjax'));
        add_filter("batch_upload_{$jobTypeSlug}_job_row", array($this, "jobRow"));
    }
    
    public final function _newJob($args)
    {
        $this->newJob($args["job"]);
    }
    
    public final function _stepForm($args)
    {
        $methodName = "step{$args['job']->step}Form";
        if (method_exists($this, $methodName))
        {
            $this->$methodName($args);
        }
    }
    
    public final function _stepProcess($args)
    {
        $methodName = "step{$args['job']->step}Process";
        if (method_exists($this, $methodName))
        {
            $this->$methodName($args);
        }
        else
        {
            if (++$args['job']->step > $this->steps)
            {
                $args['job']->finish();
            }
            $args['job']->save();
        }
    }
    
    public final function _stepAjax($args)
    {
        $methodName = "step{$args['job']->step}Ajax";
        if (method_exists($this, $methodName))
        {
            $this->$methodName($args);
        }
    }
    
    public function getTypeDescription()
    {
        return Inflector::humanize($this->job_type);
    }
    
    public function newJob($job)
    {
        debug("Starting job: {$job->name}");
    }
    
    public function jobRow($row)
    {
        $row['target'] = Inflector::humanize($this->job_type);
        return $row;
    }
    
    /**
     * Run validation on a given form with passed parameters and carry over data if invalid.
     * 
     * @param Omeka_Form $form The form to validate.
     * @param bool $validOnly Whether to carry only valid values.
     * @param array $data The submitted data. Defaults to $_POST if unspecified.
     * @return bool Whether the submitted data was valid. If no data submitted, return true.
     */
    protected function validateAndCarryForm($form, $validOnly=false, $data=null)
    {
        // Default to $_POST
        if ($data === null)
        {
            $data = $_POST;
        }
        // Carry data only 
        if ($validity = $form->isValid($data))
        {
            $options = $validOnly ? $form->getValidValues($data) : $form->getValues();
            unset($options['settings_csrf']);
            foreach ($options as $key => $value) {
                $form->getElement($key)->setValue($value);
            }
        }
        // Return whether valid
        return $validity;
    }
}
