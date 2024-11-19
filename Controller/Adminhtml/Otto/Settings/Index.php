<?php

declare(strict_types=1);

namespace M2E\Otto\Controller\Adminhtml\Otto\Settings;

class Index extends \M2E\Otto\Controller\Adminhtml\Otto\AbstractSettings
{
    protected function getLayoutType()
    {
        return self::LAYOUT_TWO_COLUMNS;
    }

    public function execute()
    {
        $activeTab = $this->getRequest()->getParam('active_tab', null);

        if ($activeTab === null) {
            $activeTab = \M2E\Otto\Block\Adminhtml\Otto\Settings\Tabs::TAB_ID_MAIN;
        }

        /** @var \M2E\Otto\Block\Adminhtml\Otto\Settings\Tabs $tabsBlock */
        $tabsBlock = $this->getLayout()->createBlock(
            \M2E\Otto\Block\Adminhtml\Otto\Settings\Tabs::class,
            '',
            [
                'data' => [
                    'active_tab' => $activeTab,
                ],
            ]
        );

        if ($this->isAjax()) {
            $this->setAjaxContent(
                $tabsBlock->getTabContent($tabsBlock->getActiveTabById($activeTab))
            );

            return $this->getResult();
        }

        $this->addLeft($tabsBlock);
        $this->addContent($this->getLayout()->createBlock(\M2E\Otto\Block\Adminhtml\Otto\Settings::class));

        $this->getResult()->getConfig()->getTitle()->prepend(__('Settings'));

        return $this->getResult();
    }
}
