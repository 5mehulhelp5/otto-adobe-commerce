<?php

declare(strict_types=1);

namespace M2E\Otto\Model\Product\DataProvider;

class EanProvider implements DataBuilderInterface
{
    use DataBuilderHelpTrait;

    public const NICK = 'Ean';

    private \M2E\Otto\Helper\Component\Otto\Configuration $configuration;
    private \M2E\Otto\Model\Magento\Product\Attribute\RetrieveValueFactory $magentoAttributeRetriever;

    public function __construct(
        \M2E\Otto\Helper\Component\Otto\Configuration $configuration,
        \M2E\Otto\Model\Magento\Product\Attribute\RetrieveValueFactory $magentoAttributeRetriever
    ) {
        $this->configuration = $configuration;
        $this->magentoAttributeRetriever = $magentoAttributeRetriever;
    }

    public function getEan(
        \M2E\Otto\Model\Product $product
    ): ?string {
        $this->searchNotFoundAttributes($product->getMagentoProduct());

        $eanAttributeCode = $this->configuration->getIdentifierCodeCustomAttribute();

        $attributeRetriever = $this->magentoAttributeRetriever->create($product->getMagentoProduct());
        $ean = $attributeRetriever->tryRetrieve($eanAttributeCode, 'Product Ean');
        if ($ean === null) {
            $this->addNotFoundAttributesToWarning($attributeRetriever);

            return null;
        }

        return $ean;
    }
}
