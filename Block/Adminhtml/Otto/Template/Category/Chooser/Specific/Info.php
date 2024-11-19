<?php

namespace M2E\Otto\Block\Adminhtml\Otto\Template\Category\Chooser\Specific;

class Info extends \M2E\Otto\Block\Adminhtml\Widget\Info
{
    protected function _prepareLayout()
    {
        $this->setInfo(
            [
                [
                    'label' => __('Category'),
                    'value' => $this->getData('title'),
                ],
            ]
        );

        return parent::_prepareLayout();
    }
}
