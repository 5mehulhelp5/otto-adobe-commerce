<?php

declare(strict_types=1);

namespace M2E\Otto\Model\Magento\Product\Rule\Custom;

class TypeId extends AbstractModel
{
    private \Magento\Catalog\Model\Product\Type $type;
    private \M2E\Otto\Helper\Magento\Product $magentoProductHelper;

    public function __construct(
        \M2E\Otto\Helper\Magento\Product $magentoProductHelper,
        \Magento\Catalog\Model\Product\Type $type
    ) {
        $this->type = $type;
        $this->magentoProductHelper = $magentoProductHelper;
    }

    /**
     * @return string
     */
    public function getAttributeCode(): string
    {
        return 'type_id';
    }

    /**
     * @return string
     */
    public function getLabel(): string
    {
        return (string)__('Product Type');
    }

    public function getValueByProductInstance(\Magento\Catalog\Model\Product $product)
    {
        return $product->getTypeId();
    }

    /**
     * @return string
     */
    public function getInputType(): string
    {
        return 'select';
    }

    /**
     * @return string
     */
    public function getValueElementType(): string
    {
        return 'select';
    }

    /**
     * @return array
     */
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
