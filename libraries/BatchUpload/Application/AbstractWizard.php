<?php

/**
 * Abstract template for import wizards.
 * 
 * @package BatchUpload
 */
abstract class BatchUpload_Application_AbstractWizard
{
    /**
     * Name slug for the job type. Must be alphanumeric+underscore.
     * @var string
     */
    public $job_type = "abstract_wizard";
    
    /**
     * Human-readable name of the job type.
     * @var string 
     */
    public $job_type_description = "Abstract Wizard";
    
    /**
     * Number of steps in the wizard's workflow.
     * @var int
     */
    public $steps = 1;
    
    /**
     * Attach processing properties of this wizard to hooks and filters.
     */
    public final function integrate()
    {
        $jobTypeSlug = Inflector::underscore($this->job_type);
        add_plugin_hook("batch_upload_{$jobTypeSlug}_job_new", array($this, '_newJob'));
        add_plugin_hook("batch_upload_{$jobTypeSlug}_step_form", array($this, '_stepForm'));
        add_plugin_hook("batch_upload_{$jobTypeSlug}_step_process", array($this, '_stepProcess'));
        add_plugin_hook("batch_upload_{$jobTypeSlug}_step_ajax", array($this, '_stepAjax'));
        add_filter("batch_upload_{$jobTypeSlug}_job_row", array($this, "jobRow"));
    }
    
    /**
     * Delegate new-job hook to overridden newJob() method.
     * @param array $args
     */
    public final function _newJob($args)
    {
        $this->newJob($args["job"]);
    }
    
    /**
     * Delegate form hook to stepNForm methods (N is the current step).
     * @param array $args
     */
    public final function _stepForm($args)
    {
        $methodName = "step{$args['job']->step}Form";
        if (method_exists($this, $methodName))
        {
            $this->$methodName($args);
        }
    }
    
    /**
     * Delegate process hook to stepNProcess methods (N is the current step).
     * If the delegated method is not implemented, automatically advance step.
     * @param array $args
     */
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
    
    /**
     * Delegate ajax hook to stepNAjax methods (N is the current step).
     * @param array $args
     */
    public final function _stepAjax($args)
    {
        $methodName = "step{$args['job']->step}Ajax";
        if (method_exists($this, $methodName))
        {
            $this->$methodName($args);
        }
    }
    
    /**
     * Get the default wizard type description.
     * @return string
     */
    public function getTypeDescription()
    {
        return Inflector::humanize($this->job_type);
    }
    
    /**
     * Placeholder for action after creating a new job of this kind.
     * @param BatchUpload_Job $job
     */
    public function newJob($job)
    {
        debug("Starting job: {$job->name}");
    }
    
    /**
     * Placeholder for filling a table row in browse for a job of this kind.
     * @param array $row
     * @return array
     */
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
