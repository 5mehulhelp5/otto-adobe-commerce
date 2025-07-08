<?php

declare(strict_types=1);

namespace M2E\Otto\Block\Adminhtml\Otto\Account\Edit\Tabs;

use M2E\Otto\Block\Adminhtml\Magento\Form\AbstractForm;
use M2E\Otto\Model\Account\Settings\UnmanagedListings as UnmanagedListingsSettings;

class UnmanagedListing extends AbstractForm
{
    private ?\M2E\Otto\Model\Account $account;
    private \M2E\Core\Helper\Magento\Attribute $magentoAttributeHelper;

    public function __construct(
        \M2E\Core\Helper\Magento\Attribute $magentoAttributeHelper,
        \M2E\Otto\Block\Adminhtml\Magento\Context\Template $context,
        \Magento\Framework\Registry $registry,
        \Magento\Framework\Data\FormFactory $formFactory,
        ?\M2E\Otto\Model\Account $account = null,
        array $data = []
    ) {
        $this->magentoAttributeHelper = $magentoAttributeHelper;
        $this->account = $account;
        parent::__construct($context, $registry, $formFactory, $data);
    }

    protected function _prepareForm()
    {
        $unmanagedListingSettings = new \M2E\Otto\Model\Account\Settings\UnmanagedListings();
        if ($this->account !== null) {
            $unmanagedListingSettings = $this->account->getUnmanagedListingSettings();
        }

        $form = $this->_formFactory->create();

        $form->addField(
            'otto_accounts_other_listings',
            self::HELP_BLOCK,
            [
                'content' => __(
                    '<p>This tab of the Account settings contains main configurations ' .
                    'for the Unmanaged Listing management. You can set preferences whether you would like to ' .
                    'import Unmanaged Listings (Items that were Listed on %channel_title either directly on the ' .
                    'channel or with the help of other than %extension_title tool), automatically link them ' .
                    'to Magento Product, etc.</p>',
                    [
                        'channel_title' => \M2E\Otto\Helper\Module::getChannelTitle(),
                        'extension_title' => \M2E\Otto\Helper\Module::getExtensionTitle(),
                    ]
                ),
            ]
        );

        $fieldset = $form->addFieldset(
            'general',
            [
                'legend' => __('General'),
                'collapsable' => false,
            ]
        );

        $fieldset->addField(
            'other_listings_synchronization',
            'select',
            [
                'name' => 'other_listings_synchronization',
                'label' => __('Import Unmanaged Listings'),
                'values' => [
                    1 => __('Yes'),
                    0 => __('No'),
                ],
                'value' => (int)$unmanagedListingSettings->isSyncEnabled(),
                'tooltip' => __(
                    'Choose whether to import items that have been listed on %channel_title ' .
                    'either directly or using a tool other than %extension_title. %extension_title will ' .
                    'import only active %channel_title items.',
                    [
                        'channel_title' => \M2E\Otto\Helper\Module::getChannelTitle(),
                        'extension_title' => \M2E\Otto\Helper\Module::getExtensionTitle(),
                    ]
                ),
            ]
        );

        $fieldset->addField(
            'other_listings_mapping_mode',
            'select',
            [
                'container_id' => 'other_listings_mapping_mode_tr',
                'name' => 'other_listings_mapping_mode',
                'label' => __('Product Linking'),
                'class' => 'Otto-require-select-attribute',
                'values' => [
                    1 => __('Yes'),
                    0 => __('No'),
                ],
                'value' => (int)$unmanagedListingSettings->isMappingEnabled(),
                'tooltip' => __(
                    'Choose whether imported %channel_title Listings should automatically link to a
                    Product in your Magento Inventory.',
                    ['channel_title' => \M2E\Otto\Helper\Module::getChannelTitle()]
                ),
            ]
        );

        $fieldset = $form->addFieldset(
            'magento_block_otto_accounts_other_listings_product_mapping',
            [
                'legend' => __('Magento Product Linking Settings'),
                'collapsable' => true,
                'tooltip' => __(
                    '<p>In this section you can provide settings for automatic Linking of the newly
                    imported Unmanaged Listings to the appropriate Magento Products.</p><br>
                    <p>The imported Items are linked based on the correspondence between %channel_title Item
                    values and Magento Product Attribute values. </p>',
                    ['channel_title' => \M2E\Otto\Helper\Module::getChannelTitle()]
                ),
            ]
        );

        // ----------------------------------------
        $allAttributes = $this->magentoAttributeHelper->getAll();

        $attributes = $this->magentoAttributeHelper->filterByInputTypes(
            $allAttributes,
            [
                \M2E\Core\Helper\Magento\Attribute::ATTRIBUTE_FRONTEND_INPUT_TEXT,
                \M2E\Core\Helper\Magento\Attribute::ATTRIBUTE_FRONTEND_INPUT_TEXTAREA,
                \M2E\Core\Helper\Magento\Attribute::ATTRIBUTE_FRONTEND_INPUT_SELECT,
            ]
        );

        $preparedAttributes = [];
        foreach ($attributes as $attribute) {
            $attrs = ['attribute_code' => $attribute['code']];
            if (
                $unmanagedListingSettings->isMappingBySkuEnabled()
                && $unmanagedListingSettings->isMappingBySkuModeByAttribute()
                && $unmanagedListingSettings->getMappingAttributeBySku() === $attribute['code']
            ) {
                $attrs['selected'] = 'selected';
            }
            $preparedAttributes[] = [
                'attrs' => $attrs,
                'value' => UnmanagedListingsSettings::MAPPING_SKU_MODE_CUSTOM_ATTRIBUTE,
                'label' => $attribute['label'],
            ];
        }

        $fieldset->addField(
            'mapping_sku_mode',
            self::SELECT,
            [
                'name' => 'other_listings_mapping[sku][mode]',
                'label' => __('SKU'),
                'class' => 'attribute-mode-select',
                'style' => 'float:left; margin-right: 15px;',
                'values' => [
                    UnmanagedListingsSettings::MAPPING_SKU_MODE_NONE => (string)__('None'),
                    UnmanagedListingsSettings::MAPPING_SKU_MODE_DEFAULT => (string)__('Product SKU'),
                    UnmanagedListingsSettings::MAPPING_SKU_MODE_PRODUCT_ID => (string)__('Product ID'),
                    [
                        'label' => __('Magento Attributes'),
                        'value' => $preparedAttributes,
                        'attrs' => [
                            'is_magento_attribute' => true,
                        ],
                    ],
                ],
                'value' => !$unmanagedListingSettings->isMappingBySkuModeByAttribute() ? $unmanagedListingSettings->getMappingBySkuMode() : '',
                'create_magento_attribute' => true,
            ]
        )->setAfterElementHtml(
            <<<HTML
<div id="mapping_sku_priority">
    {$this->__('Priority')}: <input style="width: 50px;"
                                    name="other_listings_mapping[sku][priority]"
                                    value="{$unmanagedListingSettings->getPriorityForMappingBySku()}"
                                    type="text"
                                    class="input-text admin__control-text required-entry _required">
</div>
HTML
        )->addCustomAttribute('allowed_attribute_types', 'text,textarea,select');

        $fieldset->addField(
            'mapping_sku_attribute',
            'hidden',
            [
                'name' => 'other_listings_mapping[sku][attribute]',
                'value' => $unmanagedListingSettings->getMappingAttributeBySku(),
            ]
        );

        $preparedAttributes = [];
        foreach ($attributes as $attribute) {
            $attrs = ['attribute_code' => $attribute['code']];
            if (
                $unmanagedListingSettings->isMappingByEanEnabled()
                && $unmanagedListingSettings->isMappingByEanModeByAttribute()
                && $unmanagedListingSettings->getMappingAttributeByEan() === $attribute['code']
            ) {
                $attrs['selected'] = 'selected';
            }
            $preparedAttributes[] = [
                'attrs' => $attrs,
                'value' => UnmanagedListingsSettings::MAPPING_EAN_MODE_CUSTOM_ATTRIBUTE,
                'label' => $attribute['label'],
            ];
        }

        $fieldset->addField(
            'mapping_ean_mode',
            self::SELECT,
            [
                'name' => 'other_listings_mapping[ean][mode]',
                'label' => __('EAN'),
                'class' => 'attribute-mode-select',
                'style' => 'float:left; margin-right: 15px;',
                'values' => [
                    UnmanagedListingsSettings::MAPPING_EAN_MODE_NONE => __('None'),
                    [
                        'label' => __('Magento Attributes'),
                        'value' => $preparedAttributes,
                        'attrs' => [
                            'is_magento_attribute' => true,
                        ],
                    ],
                ],
                'value' => !$unmanagedListingSettings->isMappingByEanModeByAttribute() ? $unmanagedListingSettings->getMappingByEanMode() : '',
                'create_magento_attribute' => true,
            ]
        )->setAfterElementHtml(
            <<<HTML
<div id="mapping_ean_priority">
    {$this->__('Priority')}: <input style="width: 50px;"
                                    name="other_listings_mapping[ean][priority]"
                                    value="{$unmanagedListingSettings->getPriorityForMappingByEan()}"
                                    type="text"
                                    class="input-text admin__control-text required-entry _required">
</div>
HTML
        )->addCustomAttribute('allowed_attribute_types', 'text,textarea,select');

        $fieldset->addField(
            'mapping_ean_attribute',
            'hidden',
            [
                'name' => 'other_listings_mapping[ean][attribute]',
                'value' => $unmanagedListingSettings->getMappingAttributeByEan(),
            ]
        );

        $preparedAttributes = [];
        foreach ($attributes as $attribute) {
            $attrs = ['attribute_code' => $attribute['code']];
            if (
                $unmanagedListingSettings->isMappingByTitleEnabled()
                && $unmanagedListingSettings->isMappingByTitleModeByAttribute()
                && $unmanagedListingSettings->getMappingAttributeByTitle() === $attribute['code']
            ) {
                $attrs['selected'] = 'selected';
            }
            $preparedAttributes[] = [
                'attrs' => $attrs,
                'value' => UnmanagedListingsSettings::MAPPING_TITLE_MODE_CUSTOM_ATTRIBUTE,
                'label' => $attribute['label'],
            ];
        }

        $fieldset->addField(
            'mapping_title_mode',
            self::SELECT,
            [
                'name' => 'other_listings_mapping[title][mode]',
                'label' => __('Listing Title'),
                'class' => 'attribute-mode-select',
                'style' => 'float:left; margin-right: 15px;',
                'values' => [
                    UnmanagedListingsSettings::MAPPING_TITLE_MODE_NONE => __('None'),
                    UnmanagedListingsSettings::MAPPING_TITLE_MODE_DEFAULT => __('Product Name'),
                    [
                        'label' => __('Magento Attributes'),
                        'value' => $preparedAttributes,
                        'attrs' => [
                            'is_magento_attribute' => true,
                        ],
                    ],
                ],
                'value' => !$unmanagedListingSettings->isMappingByTitleModeByAttribute() ? $unmanagedListingSettings->getMappingByTitleMode() : '',
                'create_magento_attribute' => true,
            ]
        )->setAfterElementHtml(
            <<<HTML
<div id="mapping_title_priority">
    {$this->__('Priority')}: <input style="width: 50px;"
                                    name="other_listings_mapping[title][priority]"
                                    value="{$unmanagedListingSettings->getPriorityForMappingByTitle()}"
                                    type="text"
                                    class="input-text admin__control-text required-entry _required">
</div>
HTML
        )->addCustomAttribute('allowed_attribute_types', 'text,textarea,select');

        $fieldset->addField(
            'mapping_title_attribute',
            'hidden',
            [
                'name' => 'other_listings_mapping[title][attribute]',
                'value' => $unmanagedListingSettings->getMappingAttributeByTitle(),
            ]
        );

        $fieldset = $form->addFieldset(
            'magento_block_otto_accounts_other_listings_related_store_views',
            [
                'legend' => __('Related Store Views'),
                'collapsable' => true,
                'tooltip' => __(
                    'Choose the Magento Store View that you’d like to use for imported Channel Items'
                ),
            ]
        );

        $fieldset->addField(
            'related_store_id',
            self::STORE_SWITCHER,
            [
                'label' => $this->account->getTitle(),
                'value' => $unmanagedListingSettings->getRelatedStoreId(),
            ]
        );

        $this->setForm($form);

        return parent::_prepareForm();
    }
}
