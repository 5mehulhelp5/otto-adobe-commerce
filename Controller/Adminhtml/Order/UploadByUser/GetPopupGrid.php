<?php

declare(strict_types=1);

namespace M2E\Otto\Controller\Adminhtml\Order\UploadByUser;

class GetPopupGrid extends \M2E\Otto\Controller\Adminhtml\AbstractOrder
{
    public function execute()
    {
        /** @var \M2E\Otto\Block\Adminhtml\Order\UploadByUser\Grid $block */
        $block = $this->getLayout()->createBlock(\M2E\Otto\Block\Adminhtml\Order\UploadByUser\Grid::class);
        $this->setAjaxContent($block->toHtml());

        return $this->getResult();
    }
}
