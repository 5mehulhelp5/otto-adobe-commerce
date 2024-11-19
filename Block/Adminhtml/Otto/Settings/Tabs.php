<?php

declare(strict_types=1);

namespace M2E\Otto\Block\Adminhtml\Otto\Settings;

class Tabs extends \M2E\Otto\Block\Adminhtml\Settings\Tabs
{
    public const TAB_ID_MAIN = 'main';

    protected function _prepareLayout()
    {
        $tab = [
            'label' => __('Main'),
            'title' => __('Main'),
            'content' => $this->getLayout()
                              ->createBlock(\M2E\Otto\Block\Adminhtml\Otto\Settings\Tabs\Main::class)
                              ->toHtml(),
        ];

        $this->addTab(self::TAB_ID_MAIN, $tab);

        return parent::_prepareLayout();
    }
}
