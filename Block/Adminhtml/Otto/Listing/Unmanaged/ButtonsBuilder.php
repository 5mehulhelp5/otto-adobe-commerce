<?php

declare(strict_types=1);

namespace M2E\Otto\Block\Adminhtml\Otto\Listing\Unmanaged;

class ButtonsBuilder extends \M2E\Otto\Block\Adminhtml\Magento\AbstractContainer
{
    public function _construct(): void
    {
        parent::_construct();

        $this->addButton('buttons_block', ['class_name' => ButtonsBlock::class]);
    }
}
