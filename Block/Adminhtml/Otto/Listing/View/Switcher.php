<?php

namespace M2E\Otto\Block\Adminhtml\Otto\Listing\View;

class Switcher extends \M2E\Otto\Block\Adminhtml\Listing\View\Switcher
{
    public const VIEW_MODE_OTTO = 'otto';
    public const VIEW_MODE_MAGENTO = 'magento';
    public const VIEW_MODE_SETTINGS = 'settings';

    public function getDefaultViewMode()
    {
        return self::VIEW_MODE_OTTO;
    }

    public function getTooltip()
    {
        return __(
            '<p>There are several <strong>View Modes</strong> available to you:</p>
            <ul>
            <li><p><strong>%channel_title</strong> - displays Product details with respect to %channel_title Item information.
            Using this Mode, you can easily filter down the list of Products based on %channel_title Item details as
            well as perform Actions to %channel_title Items in bulk (i.e. List, Revise, Relist, Stop, etc);</p></li>
            <li><p><strong>Settings</strong> - displays information about the Settings set for the Products
            (i.e. Selling Settings, %channel_title Categories, etc). Using this Mode, you can easily find Products by
             reference to the Settings they use as well as edit already defined Settings in bulk.</p></li>
            <li><p><strong>Magento</strong> - displays Products information with regard to Magento Catalog.
            Using this Mode, you can easily find Products based on Magento Product information
            (i.e. Magento QTY, Stock Status, etc);</p></li>
            </ul>',
            [
                'channel_title' => \M2E\Otto\Helper\Module::getChannelTitle()
            ]
        );
    }

    protected function loadItems()
    {
        $this->items = [
            'mode' => [
                'value' => [
                    [
                        'value' => self::VIEW_MODE_OTTO,
                        'label' => (string)__(\M2E\Otto\Helper\Module::getChannelTitle()),
                    ],
                    [
                        'value' => self::VIEW_MODE_SETTINGS,
                        'label' => (string)__('Settings'),
                    ],
                    [
                        'value' => self::VIEW_MODE_MAGENTO,
                        'label' => (string)__('Magento'),
                    ],
                ],
            ],
        ];
    }
}
