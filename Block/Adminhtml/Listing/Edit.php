<?php

namespace M2E\Otto\Block\Adminhtml\Listing;

use M2E\Otto\Block\Adminhtml\Magento\Form\AbstractContainer;

/**
 * Class \M2E\Otto\Block\Adminhtml\Listing\Edit
 */
class Edit extends AbstractContainer
{
    protected function _construct()
    {
        $this->_controller = 'adminhtml_listing';

        parent::_construct();

        $this->removeButton('back');
        $this->removeButton('reset');
        $this->removeButton('delete');
        $this->removeButton('save');
        $this->removeButton('edit');
    }
}
