<?php

declare(strict_types=1);

namespace M2E\Otto\Block\Adminhtml\Otto\Settings;

class Tabs extends \M2E\Otto\Block\Adminhtml\Settings\Tabs
{
    public const TAB_ID_MAIN = 'main';
    public const TAB_ID_MAPPING_ATTRIBUTES = 'mapping';

    protected function _prepareLayout()
    {
        $this->addTab(self::TAB_ID_MAIN, [
            'label' => __('Main'),
            'title' => __('Main'),
            'content' => $this->getLayout()
                              ->createBlock(\M2E\Otto\Block\Adminhtml\Otto\Settings\Tabs\Main::class)
                              ->toHtml(),
        ]);

        $this->addTab(self::TAB_ID_MAPPING_ATTRIBUTES, [
            'label' => __('Attribute Mapping'),
            'title' => __('Attribute Mapping'),
            'content' => $this->getLayout()->createBlock(
                \M2E\Otto\Block\Adminhtml\Otto\Settings\Tabs\AttributeMapping::class
            )->toHtml(),
        ]);

        return parent::_prepareLayout();
    }

    protected function _beforeToHtml()
    {
        $result = parent::_beforeToHtml();

        $urlForSetGpsrToCategory = $this->getUrl('*/otto_settings_attributeMapping/setGpsrToCategory');

        $this->js->addRequireJs(
            [
                's' => 'Otto/Otto/Settings',
            ],
            <<<JS
        window.OttoSettingsObj = new OttoSettings("$urlForSetGpsrToCategory");
JS
        );

        return $result;
    }
}
