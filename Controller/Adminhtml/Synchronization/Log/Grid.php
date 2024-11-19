<?php

declare(strict_types=1);

namespace M2E\Otto\Controller\Adminhtml\Synchronization\Log;

class Grid extends \M2E\Otto\Controller\Adminhtml\Synchronization\AbstractLog
{
    public function execute()
    {
        $this->setAjaxContent(
            $this->getLayout()->createBlock(\M2E\Otto\Block\Adminhtml\Synchronization\Log\Grid::class)
        );

        return $this->getResult();
    }
}
