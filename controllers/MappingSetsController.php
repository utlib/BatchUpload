<?php

/**
 * Controller for mapping sets.
 * 
 * @package controllers
 */
class BatchUpload_MappingSetsController extends BatchUpload_Application_AbstractActionController
{
    private $_ajaxRequiredActions = array(
    );

    private $_methodRequired = array(
    );
    
    public function init()
    {
        $this->_helper->db->setDefaultModelName('BatchUpload_MappingSet');
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
    
    public function browseAction()
    {
        parent::browseAction();
    }
    
    public function addAction()
    {
        parent::addAction();
    }
    
    public function editAction()
    {
        parent::editAction();
    }
}
