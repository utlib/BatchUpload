<?php

/**
 * Controller for batch upload jobs.
 * 
 * @package controllers
 */
class BatchUpload_JobsController extends BatchUpload_Application_AbstractActionController
{
    /**
     * The directory in shared to find the form partials.
     */
    const VIEW_STEM = 'batch_upload_forms';
    
    protected $_browseRecordsPerPage = self::RECORDS_PER_PAGE_SETTING;
    
    private $_ajaxRequiredActions = array(
        'ajax',
    );

    private $_methodRequired = array(
    );
    
    public function init()
    {
        $this->_helper->db->setDefaultModelName('BatchUpload_Job');
    }
    
    public function preDispatch()
    {
        $action = $this->getRequest()->getActionName();
        if (in_array($action, $this->_ajaxRequiredActions))
        {
            if (!$this->getRequest()->isXmlHttpRequest())
            {
                return $this->_forward('not-found', 'error');
            }
        }
        if (array_key_exists($action, $this->_methodRequired))
        {
            if (!in_array($this->getRequest()->getMethod(), $this->_methodRequired[$action]))
            {
                return $this->_forward('method-not-allowed', 'error');
            }
        }
    }
    
    public function indexAction()
    {
        $this->_helper->redirector('browse', null, null, array());
    }
    
    public function browseAction()
    {
        $this->view->availableJobTypes = apply_filters('batch_upload_register_job_type', array());
        parent::browseAction();
    }
    
    protected function _getBrowseDefaultSort()
    {
        return array('added', 'd');
    }
    
    public function addAction()
    {
        $form = new BatchUpload_Form_NewJob;
        $this->view->form = $form;
        if (!$this->validateAndCarryForm($form)) {
            $this->_helper->flashMessenger(__('There were errors found in your form. Please edit and resubmit.'), 'error');
            return;
        }
        parent::addAction();
    }
    
    protected function _redirectAfterAdd($record)
    {
        $this->_helper->redirector($record->isFinished() ? 'browse' : 'wizard', null, null, array('id' => $record->id));
    }
    
    public function wizardAction()
    {
        $batch_upload_job = $this->_helper->db->findById();
        $jobTypeSlug = Inflector::underscore($batch_upload_job->job_type);
        $partialAssigns = new BatchUpload_Application_DataContainer(array(
            'batch_upload_job' => $batch_upload_job,
        ));
        $oldStep = $batch_upload_job->step;
        $request = $this->getRequest();
        if ($request->isPost())
        {
            fire_plugin_hook("batch_upload_{$jobTypeSlug}_step_process", array(
                'job' => $batch_upload_job,
                'view' => $this->view,
                'request' => $request,
                'partial_assigns' => $partialAssigns,
                'get' => $_GET,
                'post' => $_POST,
                'files' => $_FILES,
            ));
        }
        if ($request->isGet() || $batch_upload_job->step != $oldStep)
        {
            fire_plugin_hook('batch_upload_' . $jobTypeSlug . '_step_form', array(
                'job' => $batch_upload_job,
                'view' => $this->view,
                'request' => $request,
                'partial_assigns' => $partialAssigns,
            ));
        }
        if ($batch_upload_job->isFinished())
        {
            if ($batch_upload_job->step != $oldStep)
            {
                $this->_helper->flashMessenger(__('The job "%s" has been completed!', $batch_upload_job->name), 'success');
            }
            else
            {
                $this->_helper->flashMessenger(__('The job "%s" is already complete.', $batch_upload_job->name), 'error');
            }
            $this->_helper->redirector('browse', null, null, array());
        }
        else
        {
            $partial = self::VIEW_STEM . '/' . $jobTypeSlug . '/' . $batch_upload_job->step . '.php';
            $this->view->partial = $this->view->partial($partial, $partialAssigns->getData());
        }
    }
    
    public function ajaxAction()
    {
        $batch_upload_job = $this->_helper->db->findById();
        if (!$batch_upload_job)
        {
            return $this->_forward('not-found', 'error');
        }
        $jobType = Inflector::underscore($batch_upload_job->job_type);
        $response = BatchUpload_Application_DataContainer(array(
            'step' => $batch_upload_job->step,
            'success' => true,
        ));
        $http = BatchUpload_Application_DataContainer(array(
            'status' => 200,
            'headers' => array(),
        ));
        fire_plugin_hook('batch_upload' . $jobType . '_step_ajax', array(
            'job' => $batch_upload_job,
            'view' => $this->view,
            'request' => $this->getRequest(),
            'response' => $response,
            'http' => $http,
            'get' => $_GET,
            'post' => $_POST,
            'files' => $_FILES,
        ));
        $r = $this->getResponse();
        foreach ($http->get('headers') as $header => $headerBody)
        {
            $r->setHeader($header, $headerBody);
        }
        $this->respondWithJson($response, $http->get('status'));
    }
}
