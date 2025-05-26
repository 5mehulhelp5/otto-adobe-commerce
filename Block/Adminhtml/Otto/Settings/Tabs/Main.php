<?php

declare(strict_types=1);

namespace M2E\Otto\Block\Adminhtml\Otto\Settings\Tabs;

use M2E\Otto\Helper\Component\Otto\Configuration as ConfigurationHelper;

class Main extends \M2E\Otto\Block\Adminhtml\Magento\Form\AbstractForm
{
    protected \M2E\Core\Helper\Magento\Attribute $magentoAttributeHelper;
    private \M2E\Otto\Helper\Component\Otto\Configuration $configuration;

    public function __construct(
        \M2E\Core\Helper\Magento\Attribute $magentoAttributeHelper,
        \M2E\Otto\Block\Adminhtml\Magento\Context\Template $context,
        \Magento\Framework\Registry $registry,
        \Magento\Framework\Data\FormFactory $formFactory,
        \M2E\Otto\Helper\Component\Otto\Configuration $configuration,
        array $data = []
    ) {
        $this->magentoAttributeHelper = $magentoAttributeHelper;
        $this->configuration = $configuration;

        parent::__construct($context, $registry, $formFactory, $data);
    }

    protected function _prepareForm()
    {
        $form = $this->_formFactory->create();

        $configurationHelper = $this->configuration;

        $textAttributes = $this->magentoAttributeHelper->filterByInputTypes(
            $this->magentoAttributeHelper->getAll(),
            ['text', 'select', 'weight']
        );

        $fieldset = $form->addFieldset(
            'product_settings_fieldset',
            [
                'legend' => __('Product Identifier'),
                'collapsable' => false,
            ]
        );

        $fieldset->addField(
            'identifier_code_custom_attribute',
            'hidden',
            [
                'name' => 'identifier_code_custom_attribute',
                'value' => $configurationHelper->getIdentifierCodeCustomAttribute(),
            ]
        );

        $preparedAttributes = [];

        $warningToolTip = '';

        if (
            $configurationHelper->isIdentifierCodeModeCustomAttribute() &&
            !$this->magentoAttributeHelper->isExistInAttributesArray(
                $configurationHelper->getIdentifierCodeCustomAttribute(),
                $textAttributes
            ) &&
            $this->getData('identifier_code_custom_attribute') != ''
        ) {
            $warningText = __(
                <<<HTML
    Selected Magento Attribute is invalid.
    Please ensure that the Attribute exists in your Magento, has a relevant Input Type and it
    is included in all Attribute Sets.
    Otherwise, select a different Attribute from the drop-down.
HTML
            );

            $warningToolTip = __(
                <<<HTML
<span class="fix-magento-tooltip m2e-tooltip-grid-warning">
    {$this->getTooltipHtml((string)$warningText)}
</span>
HTML
            );

            $attrs = ['attribute_code' => $configurationHelper->getIdentifierCodeCustomAttribute()];
            $attrs['selected'] = 'selected';
            $preparedAttributes[] = [
                'attrs' => $attrs,
                'value' => ConfigurationHelper::IDENTIFIER_CODE_MODE_CUSTOM_ATTRIBUTE,
                'label' => $this->magentoAttributeHelper
                    ->getAttributeLabel($configurationHelper->getIdentifierCodeCustomAttribute()),
            ];
        }

        foreach ($textAttributes as $attribute) {
            $attrs = ['attribute_code' => $attribute['code']];

            if (
                $configurationHelper->isIdentifierCodeModeCustomAttribute() &&
                $attribute['code'] == $configurationHelper->getIdentifierCodeCustomAttribute()
            ) {
                $attrs['selected'] = 'selected';
            }
            $preparedAttributes[] = [
                'attrs' => $attrs,
                'value' => ConfigurationHelper::IDENTIFIER_CODE_MODE_CUSTOM_ATTRIBUTE,
                'label' => $attribute['label'],
            ];
        }

        $fieldset->addField(
            'identifier_code_mode',
            self::SELECT,
            [
                'name' => 'identifier_code_mode',
                'label' => __('EAN'),
                'class' => 'otto-identifier-code validator-required-when-visible',
                'values' => [
                    ConfigurationHelper::IDENTIFIER_CODE_MODE_NOT_SET => __('Not Set'),
                    [
                        'label' => __('Magento Attributes'),
                        'value' => $preparedAttributes,
                        'attrs' => [
                            'is_magento_attribute' => true,
                        ],
                    ],
                ],
                'value' => !$configurationHelper->isIdentifierCodeModeCustomAttribute()
                    ? $configurationHelper->getIdentifierCodeMode()
                    : '',
                'create_magento_attribute' => true,
                'tooltip' => __(
                    '%channel_title uses EAN to associate your Item with its catalog. Select Attribute where the
                     Product ID values are stored.',
                    ['channel_title' => \M2E\Otto\Helper\Module::getChannelTitle()]
                ),
                'after_element_html' => $warningToolTip,
                'required' => true,
            ]
        )->addCustomAttribute('allowed_attribute_types', 'text');

        $form->setUseContainer(true);
        $this->setForm($form);

        return parent::_prepareForm();
    }

    protected function _beforeToHtml()
    {
        $this->jsUrl->add(
            $this->getUrl('*/otto_settings/save'),
            \M2E\Otto\Block\Adminhtml\Otto\Settings\Tabs::TAB_ID_MAIN
        );

        $this->jsPhp->addConstants(
            \M2E\Otto\Helper\Data::getClassConstants(
                \M2E\Otto\Helper\Component\Otto\Configuration::class
            )
        );

        $this->js->add(
            <<<JS
require([
    'Otto/Otto/Settings/Main'
], function(){

    window.OttoSettingsMainObj = new OttoSettingsMain();

    $('identifier_code_mode').observe('change', OttoSettingsMainObj.identifier_code_mode_change);
});
JS
        );

        return parent::_beforeToHtml();
    }
}
