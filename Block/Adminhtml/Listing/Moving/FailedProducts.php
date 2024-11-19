<?php

declare(strict_types=1);

namespace M2E\Otto\Block\Adminhtml\Listing\Moving;

class FailedProducts extends \M2E\Otto\Block\Adminhtml\Magento\AbstractContainer
{
    protected $_template = 'listing/moving/failedProducts.phtml';

    protected function _beforeToHtml()
    {
        $this->setChild(
            'failedProducts_grid',
            $this->getLayout()->createBlock(
                \M2E\Otto\Block\Adminhtml\Listing\Moving\FailedProducts\Grid::class,
                '',
                ['data' => ['grid_url' => $this->getData('grid_url')]]
            )
        );

        parent::_beforeToHtml();
    }
}
