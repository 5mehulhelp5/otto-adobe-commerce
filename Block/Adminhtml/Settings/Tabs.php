<?php

declare(strict_types=1);

namespace M2E\Otto\Block\Adminhtml\Settings;

class Tabs extends \M2E\Otto\Block\Adminhtml\Magento\Tabs\AbstractTabs
{
    public const TAB_ID_SYNCHRONIZATION = 'synchronization';

    protected function _construct()
    {
        parent::_construct();
        $this->setId('configuration_settings_tabs');
        $this->setDestElementId('tabs_container');
    }

    protected function _prepareLayout()
    {
        $this->css->addFile('settings.css');

        $this->setActiveTab($this->getData('active_tab'));

        return parent::_prepareLayout();
    }

    public function getActiveTabById($id)
    {
        return isset($this->_tabs[$id]) ? $this->_tabs[$id] : null;
    }

    protected function _beforeToHtml()
    {
        $this->jsTranslator->addTranslations([
            'Settings saved' => __('Settings saved'),
            'Error' => __('Error'),
        ]);
        $this->js->addRequireJs(
            [
                's' => 'Otto/Settings',
            ],
            <<<JS

        window.SettingsObj = new Settings();
JS
        );

        return parent::_beforeToHtml();
    }
}
