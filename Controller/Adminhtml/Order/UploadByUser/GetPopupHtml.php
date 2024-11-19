<?php

declare(strict_types=1);

namespace M2E\Otto\Controller\Adminhtml\Order\UploadByUser;

class GetPopupHtml extends \M2E\Otto\Controller\Adminhtml\AbstractOrder
{
    public function execute()
    {
        /** @var \M2E\Otto\Block\Adminhtml\Order\UploadByUser\Popup $block */
        $block = $this->getLayout()->createBlock(\M2E\Otto\Block\Adminhtml\Order\UploadByUser\Popup::class);
        $this->setAjaxContent($block->toHtml());

        return $this->getResult();
    }
}
