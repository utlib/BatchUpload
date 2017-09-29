<?php

/**
 * Form used in step #1 of the wizard for importing to new collections.
 * 
 * @package Form
 */
class BatchUpload_Form_CollectionCreate extends Omeka_Form
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
        $this->setAttrib('id', 'new_collection');
        $this->setAttrib('method', 'POST');
        // Target collection
        $this->addElement('text', 'name', array(
            'label' => __("Collection Name"),
            'description' => __("Name of the new collection to import into."),
            'required' => true,
        ));
    }
}
