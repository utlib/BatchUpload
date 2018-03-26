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
     * @var string
     */
    const VIEW_STEM = 'batch_upload_forms';

    /**
     * The number of records to display per page.
     * @var int
     */
    protected $_browseRecordsPerPage = self::RECORDS_PER_PAGE_SETTING;

    /**
     * Actions requiring an AJAX response.
     * @var string[]
     */
    private $_ajaxRequiredActions = array(
        'ajax',
    );

    /**
     * Associative array from action to array of allowed verbs.
     * @var array
     */
    private $_methodRequired = array(
    );

    /**
     * Set up the controller.
     */
    public function init()
    {
        $this->_helper->db->setDefaultModelName('BatchUpload_Job');
    }

    /**
     * HOOK: Pre-dispatch.
     */
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

    /**
     * Main index action.
     * GET /batch-upload/jobs
     */
    public function indexAction()
    {
        $this->_helper->redirector('browse', null, null, array());
    }

    /**
     * Job listings action.
     * GET /batch-upload/jobs/browse
     */
    public function browseAction()
    {
        $this->view->availableJobTypes = apply_filters('batch_upload_register_job_type', array());
        parent::browseAction();
    }

    /**
     * Override the default sorting order to descending by added date.
     * @return array
     */
    protected function _getBrowseDefaultSort()
    {
        return array('added', 'd');
    }

    /**
     * Adding a new job.
     * GET/POST /batch-upload/jobs/add
     */
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

    /**
     * Override the post-add redirection.
     * Go to browse if the job finishes right away, otherwise show the wizard.
     * @param Record $record The currently added record.
     */
    protected function _redirectAfterAdd($record)
    {
        $this->_helper->redirector($record->isFinished() ? 'browse' : 'wizard', null, null, array('id' => $record->id));
    }

    /**
     * Display and process the wizard interface.
     * GET shows the form; POST processes form submissions.
     * GET/POST /batch-upload/jobs/wizard/:id
     */
    public function wizardAction()
    {
        // Find the working job
        $batch_upload_job = $this->_helper->db->findById();
        // Go back to browse if the job is already done
        if ($batch_upload_job->isFinished())
        {
            $this->_helper->flashMessenger(__('The job "%s" has been completed!', $batch_upload_job->name), 'success');
            $this->_helper->redirector('browse', null, null, array());
        }
        // Keep working on the job
        else
        {
            $jobTypeSlug = Inflector::underscore($batch_upload_job->job_type);
            $partialAssigns = new BatchUpload_Application_DataContainer(array(
                'batch_upload_job' => $batch_upload_job,
                'page_title' => null,
            ));
            $oldStep = $batch_upload_job->step;
            $request = $this->getRequest();
            // Form submission: Fire the step-process hook
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
            // Form render: Fire the step-form hook
            if ($request->isGet() || $batch_upload_job->step != $oldStep)
            {
                fire_plugin_hook('batch_upload_' . $jobTypeSlug . '_step_form', array(
                    'job' => $batch_upload_job,
                    'view' => $this->view,
                    'request' => $request,
                    'partial_assigns' => $partialAssigns,
                ));
            }
            // Fetch the wizard view partial and render it as a partial
            $partial = self::VIEW_STEM . '/' . $jobTypeSlug . '/' . $batch_upload_job->step . '.php';
            $this->view->partial = $this->view->partial($partial, $partialAssigns->getData());
            $this->view->page_title = $partialAssigns->get('page_title');
        }
    }

    /**
     * Similar to wizard, except it responds exclusively in JSON.
     * Useful for implementing asynchronous processes.
     * GET/POST /batch-upload/jobs/ajax/:id
     */
    public function ajaxAction()
    {
        // Find the working job
        $batch_upload_job = $this->_helper->db->findById();
        // Don't proceed if not found
        if (!$batch_upload_job)
        {
            return $this->_forward('not-found', 'error');
        }
        // Fire the hook
        $jobType = Inflector::underscore($batch_upload_job->job_type);
        $response = new BatchUpload_Application_DataContainer(array(
            'step' => $batch_upload_job->step,
            'success' => true,
        ));
        $http = new BatchUpload_Application_DataContainer(array(
            'status' => 200,
            'headers' => array(),
        ));
        fire_plugin_hook('batch_upload_' . $jobType . '_step_ajax', array(
            'job' => $batch_upload_job,
            'view' => $this->view,
            'request' => $this->getRequest(),
            'response' => $response,
            'http' => $http,
            'get' => $_GET,
            'post' => $_POST,
            'files' => $_FILES,
        ));
        // Send back the processed data as JSON
        $r = $this->getResponse();
        foreach ($http->get('headers') as $header => $headerBody)
        {
            $r->setHeader($header, $headerBody);
        }
        $this->respondWithJson($response->getData(), $http->get('status'));
    }

    /**
     * Utility action for providing information on the given job.
     * Can be polled to detect change in step or finished status.
     */
    public function lookupAction()
    {
        $batch_upload_job = $this->_helper->db->findById();
        if (!$batch_upload_job)
        {
            return $this->_forward('not-found', 'error');
        }
        $this->respondWithJson($batch_upload_job);
    }

    /**
     * Utility action for refreshing the wizard.
     * Needed to silence the POST resubmission confirmation message.
     * GET /batch-upload/jobs/refresh/1
     */
    public function refreshAction()
    {
        $this->_helper->redirector('wizard', null, null, array('id' => $this->getParam('id')));
    }
}
