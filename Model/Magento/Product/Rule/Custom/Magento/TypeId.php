<?php

declare(strict_types=1);

namespace M2E\Otto\Model\Magento\Product\Rule\Custom\Magento;

class TypeId extends \M2E\Otto\Model\Magento\Product\Rule\Custom\AbstractCustomFilter
{
    public const NICK = 'magento_type_id';

    private \Magento\Catalog\Model\Product\Type $type;
    private \M2E\Otto\Helper\Magento\Product $magentoProductHelper;

    public function __construct(
        \M2E\Otto\Helper\Magento\Product $magentoProductHelper,
        \Magento\Catalog\Model\Product\Type $type
    ) {
        $this->type = $type;
        $this->magentoProductHelper = $magentoProductHelper;
    }

    public function getLabel(): string
    {
        return (string)__('Product Type');
    }

    public function getValueByProductInstance(\Magento\Catalog\Model\Product $product)
    {
        return $product->getTypeId();
    }

    public function getInputType(): string
    {
        return \M2E\Otto\Model\Magento\Product\Rule\Condition\AbstractModel::VALUE_ELEMENT_TYPE_SELECT;
    }

    public function getValueElementType(): string
    {
        return \M2E\Otto\Model\Magento\Product\Rule\Condition\AbstractModel::VALUE_ELEMENT_TYPE_SELECT;
    }

    public function getOptions(): array
    {
        $magentoProductTypes = $this->type->getOptionArray();
        $knownTypes = $this->magentoProductHelper->getOriginKnownTypes();

        $options = [];
        foreach ($magentoProductTypes as $type => $magentoProductTypeLabel) {
            if (!in_array($type, $knownTypes)) {
                continue;
            }

            $options[] = [
                'value' => $type,
                'label' => $magentoProductTypeLabel,
            ];
        }

        return $options;
    }
}
