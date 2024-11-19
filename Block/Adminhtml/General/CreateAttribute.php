<?php

namespace M2E\Otto\Block\Adminhtml\General;

use M2E\Otto\Block\Adminhtml\Magento\Form\AbstractContainer;

class CreateAttribute extends AbstractContainer
{
    public function _construct()
    {
        parent::_construct();
        $this->_controller = 'adminhtml_general';
        $this->_mode = 'createAttribute';

        // Initialization block
        // ---------------------------------------
        $this->setId('generalCreateAttribute');
        // ---------------------------------------

        // Set buttons actions
        // ---------------------------------------
        $this->removeButton('back');
        $this->removeButton('reset');
        $this->removeButton('delete');
        $this->removeButton('save');
        $this->removeButton('edit');
        // ---------------------------------------
    }
}
