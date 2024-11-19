<?php

declare(strict_types=1);

namespace M2E\Otto\Controller\Adminhtml\ControlPanel;

/**
 * Class \M2E\Otto\Controller\Adminhtml\ControlPanel\DatabaseTab
 */
class DatabaseTab extends AbstractMain
{
    public function execute()
    {
        $block = $this->getLayout()->createBlock(\M2E\Otto\Block\Adminhtml\ControlPanel\Tabs\Database::class, '');
        $this->setAjaxContent($block);

        return $this->getResult();
    }
}
