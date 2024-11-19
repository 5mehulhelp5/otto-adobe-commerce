<?php

declare(strict_types=1);

namespace M2E\Otto\Controller\Adminhtml\Synchronization\Log;

class Index extends \M2E\Otto\Controller\Adminhtml\Synchronization\AbstractLog
{
    public function execute()
    {
        $this->addContent(
            $this->getLayout()->createBlock(\M2E\Otto\Block\Adminhtml\Synchronization\Log::class)
        );
        $this->getResultPage()->getConfig()->getTitle()->prepend(__('Synchronization Logs'));

        return $this->getResult();
    }
}
