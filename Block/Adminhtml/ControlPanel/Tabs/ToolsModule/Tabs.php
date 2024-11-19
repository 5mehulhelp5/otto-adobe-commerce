<?php

namespace M2E\Otto\Block\Adminhtml\ControlPanel\Tabs\ToolsModule;

use M2E\Otto\Block\Adminhtml\Magento\Tabs\AbstractTabs;

class Tabs extends AbstractTabs
{
    public function _construct()
    {
        parent::_construct();

        $this->setId('controlPanelToolsModuleTabs');
        $this->setDestElementId('tools_module_tabs');
    }

    protected function _beforeToHtml()
    {
        $this->addTab(
            'magento',
            [
                'label' => __('Magento'),
                'title' => __('Magento'),
                'content' => $this->getLayout()->createBlock(
                    \M2E\Otto\Block\Adminhtml\ControlPanel\Tabs\Command\Group::class,
                    '',
                    [
                        'controllerName' => \M2E\Otto\Controller\Adminhtml\ControlPanel\Tools\Magento::class,
                        'route' => 'controlPanel_tools/magento',
                    ],
                )->toHtml(),
            ],
        );

        $this->addTab(
            'integration',
            [
                'label' => __('Integration'),
                'title' => __('Integration'),
                'content' => $this->getLayout()->createBlock(
                    \M2E\Otto\Block\Adminhtml\ControlPanel\Tabs\Command\Group::class,
                    '',
                    [
                        'controllerName' => \M2E\Otto\Controller\Adminhtml\ControlPanel\Module\Integration::class,
                        'route' => 'controlPanel_module/integration',
                    ],
                )->toHtml(),
            ],
        );

        return parent::_beforeToHtml();
    }
}
