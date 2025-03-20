<?php

declare(strict_types=1);

namespace M2E\Otto\Block\Adminhtml\Otto\Settings\Tabs\AttributeMapping;

class GpsrAttributesFieldsetFill
{
    private \M2E\Core\Helper\Magento\Attribute $attributeHelper;
    private \M2E\Otto\Model\AttributeMapping\GpsrService $gpsrService;

    public function __construct(
        \M2E\Core\Helper\Magento\Attribute $attributeHelper,
        \M2E\Otto\Model\AttributeMapping\GpsrService $gpsrService
    ) {
        $this->attributeHelper = $attributeHelper;
        $this->gpsrService = $gpsrService;
    }

    public function fill(\Magento\Framework\Data\Form\Element\Fieldset $fieldset): void
    {
        $attributesTextType = $this->attributeHelper->filterAllAttrByInputTypes(['text', 'select']);

        $preparedAttributes = [];
        foreach ($attributesTextType as $attribute) {
            $preparedAttributes[] = [
                'value' => $attribute['code'],
                'label' => $attribute['label'],
            ];
        }

        foreach ($this->gpsrService->getAll() as $pair) {
            $config = [
                'label' => $pair->channelAttributeTitle,
                'title' => $pair->channelAttributeTitle,
                'name' => sprintf('gpsr_attributes[%s]', $pair->channelAttributeCode),
                'values' => [
                    '' => __('None'),
                    [
                        'label' => __('Magento Attributes'),
                        'value' => $preparedAttributes,
                    ],
                ],
                'value' => $pair->magentoAttributeCode ?? '',
            ];

            $fieldset->addField($pair->channelAttributeCode, 'select', $config);
        }
    }
}
