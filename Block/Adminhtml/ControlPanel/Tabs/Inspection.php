<?php

namespace M2E\Otto\Block\Adminhtml\ControlPanel\Tabs;

use M2E\Otto\Block\Adminhtml\Magento\AbstractBlock;

/**
 * Class \M2E\Otto\Block\Adminhtml\ControlPanel\Tabs\Inspection
 */
class Inspection extends AbstractBlock
{
    //########################################

    public function _construct()
    {
        parent::_construct();

        $this->setId('controlPanelInspection');
        $this->setTemplate('control_panel/tabs/inspection.phtml');
    }

    //########################################

    protected function _beforeToHtml()
    {
        $this->setChild(
            'inspections',
            $this->getLayout()->createBlock(\M2E\Otto\Block\Adminhtml\ControlPanel\Inspection\Grid::class)
        );

        return parent::_beforeToHtml();
    }
}
