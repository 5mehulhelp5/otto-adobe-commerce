<?php

declare(strict_types=1);

namespace M2E\Otto\Block\Adminhtml\Otto\Log\Listing\Product;

use M2E\Otto\Block\Adminhtml\Log\Listing\Product\AbstractView;

class View extends AbstractView
{
    protected function getComponentMode()
    {
        return 'Otto';
    }

    protected function _toHtml()
    {
        $message = (string)__(
            'This Log contains information about the actions applied to M2E Otto Listings and related Items.'
        );
        $helpBlock = $this
            ->getLayout()
            ->createBlock(\M2E\Otto\Block\Adminhtml\HelpBlock::class)->setData([
                'content' => $message,
            ]);

        return $helpBlock->toHtml() . parent::_toHtml();
    }
}
