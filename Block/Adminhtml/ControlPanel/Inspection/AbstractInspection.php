<?php

namespace M2E\Otto\Block\Adminhtml\ControlPanel\Inspection;

use M2E\Otto\Block\Adminhtml\Magento\AbstractBlock;

/**
 * Class \M2E\Otto\Block\Adminhtml\ControlPanel\Inspection\AbstractInspection
 */
abstract class AbstractInspection extends AbstractBlock
{
    //########################################

    public function isShown()
    {
        return true;
    }

    //########################################
}
