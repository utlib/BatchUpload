<?php

/**
 * Form used in step #1 of the wizard for importing to an existing collection.
 *
 * @package Form
 */
class BatchUpload_Form_CollectionSelect extends Omeka_Form
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
        $this->setAttrib('id', 'collection_id_selector');
        $this->setAttrib('method', 'POST');
        // Target collection
        $this->addElement('select', 'target_id', array(
            'label' => __("Target Collection"),
            'description' => __("Select the collection to import items into."),
            'multiOptions' => get_table_options('Collection'),
            'required' => true,
        ));
    }
}
