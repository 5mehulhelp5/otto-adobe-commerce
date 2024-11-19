<?php

declare(strict_types=1);

namespace M2E\Otto\Controller\Adminhtml\Otto\Template;

class NewAction extends \M2E\Otto\Controller\Adminhtml\Otto\AbstractTemplate
{
    public function execute()
    {
        /** @var \Magento\Backend\Model\View\Result\Forward $resultForward */
        $resultForward = $this->resultFactory->create(\Magento\Framework\Controller\ResultFactory::TYPE_FORWARD);
        $resultForward->forward('edit');

        return $resultForward;
    }
}
