<?php

declare(strict_types=1);

namespace M2E\Otto\Controller\Adminhtml\ControlPanel;

use M2E\Otto\Helper\Module;
use Magento\Backend\App\Action;

/**
 * Class \M2E\Otto\Controller\Adminhtml\ControlPanel\InspectionTab
 */
class InspectionTab extends AbstractMain
{
    public function execute()
    {
        $block = $this->getLayout()->createBlock(
            \M2E\Otto\Block\Adminhtml\ControlPanel\Tabs\Inspection::class,
            ''
        );
        $this->setAjaxContent($block);

        return $this->getResult();
    }
}
