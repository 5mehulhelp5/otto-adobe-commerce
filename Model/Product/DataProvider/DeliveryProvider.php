<?php

declare(strict_types=1);

namespace M2E\Otto\Model\Product\DataProvider;

class DeliveryProvider implements DataBuilderInterface
{
    use DataBuilderHelpTrait;

    public const NICK = 'Delivery';

    private \M2E\Otto\Model\Magento\Product\Attribute\RetrieveValueFactory $magentoAttributeRetriever;

    public function __construct(
        \M2E\Otto\Model\Magento\Product\Attribute\RetrieveValueFactory $magentoAttributeRetriever
    ) {
        $this->magentoAttributeRetriever = $magentoAttributeRetriever;
    }

    public function getDelivery(\M2E\Otto\Model\Product $product): ?\M2E\Otto\Model\Product\DataProvider\Delivery\Value
    {
        $shippingPolicyProvider = $product->getShippingPolicyDataProvider();

        $deliveryTime = $shippingPolicyProvider->getShippingProfileId() !== null
            ? $shippingPolicyProvider->getHandlingTimeValue()
            : $this->resolveHandlingTime($shippingPolicyProvider, $product);

        return new \M2E\Otto\Model\Product\DataProvider\Delivery\Value(
            $shippingPolicyProvider->getShippingProfileId(),
            $shippingPolicyProvider->getType(),
            $deliveryTime
        );
    }

    private function resolveHandlingTime(
        \M2E\Otto\Model\Policy\ShippingDataProvider $shippingPolicyProvider,
        \M2E\Otto\Model\Product $product
    ): ?int {
        if ($shippingPolicyProvider->isHandlingTimeModeAttribute()) {
            $attributeRetriever = $this->magentoAttributeRetriever->create($product->getMagentoProduct());
            $value = $attributeRetriever->tryRetrieve(
                $shippingPolicyProvider->getHandlingTimeAttribute(),
                'Handling Time'
            );

            if ($value === null) {
                $this->addNotFoundAttributesToWarning($attributeRetriever);
                return null;
            }

            return (int)$value;
        }

        return $shippingPolicyProvider->getHandlingTimeValue();
    }
}
