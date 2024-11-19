<?php

namespace M2E\Otto\Block\Adminhtml\ControlPanel\Tabs;

use M2E\Otto\Block\Adminhtml\Magento\Grid\AbstractContainer;

class Database extends AbstractContainer
{
    public function _construct()
    {
        parent::_construct();

        // Initialization block
        // ---------------------------------------
        $this->setId('controlPanelDatabase');

        $this->_controller = 'adminhtml_controlPanel_tabs_database';
        // ---------------------------------------

        $this->setTemplate('magento/grid/container/only_content.phtml');

        $this->removeButton('add');
    }
}
