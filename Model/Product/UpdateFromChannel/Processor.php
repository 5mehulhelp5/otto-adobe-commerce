<?php

declare(strict_types=1);

namespace M2E\Otto\Model\Product\UpdateFromChannel;

use M2E\Otto\Model\Product;

class Processor
{
    private \M2E\Otto\Model\Product $product;
    private \M2E\Otto\Model\Listing\Other\OttoProduct $channelProduct;

    private array $instructionsData = [];
    /** @var \M2E\Otto\Model\Listing\Log\Record[] */
    private array $logs = [];

    public function __construct(
        \M2E\Otto\Model\Product $product,
        \M2E\Otto\Model\Listing\Other\OttoProduct $channelProduct
    ) {
        $this->product = $product;
        $this->channelProduct = $channelProduct;
    }

    public function processChanges(): ChangeResult
    {
        $isChangedProduct = $this->processProduct();

        return new ChangeResult(
            $this->product,
            $isChangedProduct,
            array_values($this->instructionsData),
            array_values($this->logs),
        );
    }

    private function isNeedUpdateQty(): bool
    {
        if ($this->product->getOnlineQty() === $this->channelProduct->getQty()) {
            return false;
        }

        return !$this->isNeedSkipQtyChange($this->product->getOnlineQty(), $this->channelProduct->getQty());
    }

    private function isNeedSkipQtyChange(int $currentQty, int $channelQty): bool
    {
        if ($channelQty > $currentQty) {
            return false;
        }

        return $currentQty < 5;
    }

    private function isNeedUpdatePrice(): bool
    {
        return $this->product->getOnlineCurrentPrice() !== $this->channelProduct->getPrice();
    }

    private function isNeedUpdateMoin(): bool
    {
        if (empty($this->channelProduct->getMoin())) {
            return false;
        }

        return $this->product->getOttoProductMoin() !== $this->channelProduct->getMoin();
    }

    private function isNeedUpdateProductUrl(): bool
    {
        return $this->product->getOttoProductUrl() !== $this->channelProduct->getProductUrl();
    }

    private function isNeedUpdateProductReference(): bool
    {
        if (empty($this->channelProduct->getProductReference())) {
            return false;
        }

        if (empty($this->product->getOnlineProductReference())) {
            return true;
        }

        return $this->product->getOnlineProductReference() !== $this->channelProduct->getProductReference();
    }

    private function isNeedUpdateOttoProductSku(): bool
    {
        return empty($this->product->getOttoProductSku());
    }

    private function isNeedUpdateProductStatus(): bool
    {
        return $this->product->getStatus() !== $this->channelProduct->getStatus();
    }

    private function isNeedUpdateProductValid(): bool
    {
        return $this->product->isProductIncomplete() === $this->channelProduct->isChannelProductComplete();
    }

    private function isNeedUpdateShippingProfileId(): bool
    {
        return !empty($this->channelProduct->getShippingProfileId())
            && $this->product->getOnlineShippingProfileId() !== $this->channelProduct->getShippingProfileId();
    }

    private function isNeedUpdateDeliveryType(): bool
    {
        return !empty($this->channelProduct->getDeliveryType())
            && $this->product->getOnlineDeliveryType() !== $this->channelProduct->getDeliveryType();
    }

    private function processProduct(): bool
    {
        $isChangedProduct = false;
        $productStatus = [];

        if ($this->isNeedUpdateQty()) {
            $this->addInstructionData(
                Product::INSTRUCTION_TYPE_CHANNEL_QTY_CHANGED,
                80,
            );

            $this->addLog(
                \M2E\Otto\Model\Listing\Log\Record::createSuccess(
                    (string)__(
                        'Product QTY was changed from %from to %to.',
                        [
                            'from' => $this->product->getOnlineQty(),
                            'to' => $this->channelProduct->getQty(),
                        ],
                    )
                ),
            );

            $this->product->setOnlineQty($this->channelProduct->getQty());

            $isChangedProduct = true;
        }

        if ($this->isNeedUpdatePrice()) {
            $this->addInstructionData(
                Product::INSTRUCTION_TYPE_CHANNEL_PRICE_CHANGED,
                60,
            );

            $this->addLog(
                \M2E\Otto\Model\Listing\Log\Record::createSuccess(
                    (string)__(
                        'Product Price was changed from %from to %to.',
                        [
                            'from' => $this->product->getOnlineCurrentPrice(),
                            'to' => $this->channelProduct->getPrice(),
                        ],
                    )
                ),
            );

            $this->product->setOnlinePrice($this->channelProduct->getPrice());

            $isChangedProduct = true;
        }

        if ($this->isNeedUpdateProductValid()) {
            $this->addInstructionData(
                Product::INSTRUCTION_TYPE_CHANNEL_STATUS_CHANGED,
                40,
            );

            if ($this->channelProduct->isChannelProductInComplete()) {
                $this->product->makeProductIncomplete();

                $this->addLog(
                    \M2E\Otto\Model\Listing\Log\Record::createSuccess(
                        (string)__(
                            'Product Status was changed from %from to %to.',
                            [
                                'from' =>  \M2E\Otto\Model\Product::getStatusTitle($this->product->getStatus()),
                                'to' => \M2E\Otto\Model\Product::getIncompleteStatusTitle(),
                            ],
                        )
                    ),
                );

                $productStatus[$this->product->getId()] = true;
            }

            if ($this->channelProduct->isChannelProductComplete()) {
                $this->product->makeProductComplete();

                $this->addLog(
                    \M2E\Otto\Model\Listing\Log\Record::createSuccess(
                        (string)__(
                            'Product Status was changed from %from to %to.',
                            [
                                'from' => \M2E\Otto\Model\Product::getIncompleteStatusTitle(),
                                'to' => \M2E\Otto\Model\Product::getStatusTitle($this->channelProduct->getStatus()),
                            ],
                        )
                    ),
                );

                $productStatus[$this->product->getId()] = true;
            }

            $isChangedProduct = true;
        }

        if ($this->isNeedUpdateProductStatus()) {
            $this->addInstructionData(
                Product::INSTRUCTION_TYPE_CHANNEL_STATUS_CHANGED,
                40,
            );

            if (!isset($productStatus[$this->product->getId()])) {
                $this->addLog(
                    \M2E\Otto\Model\Listing\Log\Record::createSuccess(
                        (string)__(
                            'Product Status was changed from %from to %to.',
                            [
                                'from' => \M2E\Otto\Model\Product::getStatusTitle($this->product->getStatus()),
                                'to' => \M2E\Otto\Model\Product::getStatusTitle($this->channelProduct->getStatus()),
                            ],
                        )
                    ),
                );
            }

            if (!$this->channelProduct->isStatusActive()) {
                $this->product->makeProductComplete();
            }

            $this->product->setStatus($this->channelProduct->getStatus(), \M2E\Otto\Model\Product::STATUS_CHANGER_COMPONENT);

            $isChangedProduct = true;
        }

        if ($this->isNeedUpdateMoin()) {
            $this->addLog(
                \M2E\Otto\Model\Listing\Log\Record::createSuccess(
                    (string)__(
                        'Product MOIN was changed from %from to %to.',
                        [
                            'from' => $this->product->getOttoProductMoin(),
                            'to' => $this->channelProduct->getMoin(),
                        ],
                    )
                ),
            );

            $this->product->setOttoProductMoin($this->channelProduct->getMoin());

            $isChangedProduct = true;
        }

        if ($this->isNeedUpdateShippingProfileId()) {
            $this->addInstructionData(
                Product::INSTRUCTION_TYPE_CHANNEL_SHIPPING_PROFILE_ID_CHANGED,
                40,
            );

            $this->product->setOnlineShippingProfileId($this->channelProduct->getShippingProfileId());

            $isChangedProduct = true;
        }

        if ($this->isNeedUpdateProductUrl()) {
            $this->product->setOttoProductUrl($this->channelProduct->getProductUrl());

            $isChangedProduct = true;
        }

        if ($this->isNeedUpdateProductReference()) {
            $this->product->setOnlineProductReference($this->channelProduct->getProductReference());

            $isChangedProduct = true;
        }

        if ($this->isNeedUpdateOttoProductSku()) {
            $this->product->setOttoProductSku($this->channelProduct->getSku());

            $isChangedProduct = true;
        }

        if ($this->isNeedUpdateDeliveryType()) {
            $this->product->setOnlineDeliveryType($this->channelProduct->getDeliveryType());

            $isChangedProduct = true;
        }

        return $isChangedProduct;
    }

    private function addInstructionData(string $type, int $priority): void
    {
        $this->instructionsData[$type] = [
            'listing_product_id' => $this->product->getId(),
            'type' => $type,
            'priority' => $priority,
            'initiator' => 'channel_changes_synchronization',
        ];
    }

    private function addLog(\M2E\Otto\Model\Listing\Log\Record $record): void
    {
        $this->logs[$record->getMessage()] = $record;
    }
}
