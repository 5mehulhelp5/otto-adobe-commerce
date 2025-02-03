<?php

namespace M2E\Otto\Model\Order\Item;

class ProxyObject
{
    private \M2E\Otto\Model\Order\Item $item;

    private int $qty;

    private float $price;

    private $subtotal;

    private array $additionalData = [];

    public function __construct(
        \M2E\Otto\Model\Order\Item $item
    ) {
        $this->item = $item;
        $this->subtotal = $this->getOriginalPrice() * $this->getOriginalQty();
    }

    public function getOriginalPrice(): float
    {
        return $this->item->getSalePrice();
    }

    /**
     * @return int
     */
    public function getOriginalQty(): int
    {
        return $this->item->getQtyPurchased();
    }

    public function getProxyOrder(): \M2E\Otto\Model\Order\ProxyObject
    {
        return $this->item->getOrder()->getProxy();
    }

    /**
     * @param \M2E\Otto\Model\Order\Item\ProxyObject $that
     *
     * @return bool
     */
    public function equals(\M2E\Otto\Model\Order\Item\ProxyObject $that)
    {
        if ($this->getProductId() === null || $that->getProductId() === null) {
            return false;
        }

        if ($this->getProductId() != $that->getProductId()) {
            return false;
        }

        $thisOptions = $this->getOptions();
        $thatOptions = $that->getOptions();

        $thisOptionsKeys = array_keys($thisOptions);
        $thatOptionsKeys = array_keys($thatOptions);

        $thisOptionsValues = array_values($thisOptions);
        $thatOptionsValues = array_values($thatOptions);

        if (
            count($thisOptions) != count($thatOptions)
            || count(array_diff($thisOptionsKeys, $thatOptionsKeys)) > 0
            || count(array_diff($thisOptionsValues, $thatOptionsValues)) > 0
        ) {
            return false;
        }

        // grouped products have no options, that's why we have to compare associated products
        $thisAssociatedProducts = $this->getAssociatedProducts();
        $thatAssociatedProducts = $that->getAssociatedProducts();

        if (
            count($thisAssociatedProducts) != count($thatAssociatedProducts)
            || count(array_diff($thisAssociatedProducts, $thatAssociatedProducts)) > 0
        ) {
            return false;
        }

        return true;
    }

    public function merge(\M2E\Otto\Model\Order\Item\ProxyObject $that)
    {
        $this->setQty($this->getQty() + $that->getOriginalQty());
        $this->subtotal += $that->getOriginalPrice() * $that->getOriginalQty();

        // merge additional data
        // ---------------------------------------
        $thisAdditionalData = $this->getAdditionalData();
        $thatAdditionalData = $that->getAdditionalData();

        $identifier = \M2E\Otto\Helper\Data::CUSTOM_IDENTIFIER;

        $thisAdditionalData[$identifier]['items'][] = $thatAdditionalData[$identifier]['items'][0];

        $this->additionalData = $thisAdditionalData;
        // ---------------------------------------
    }

    /**
     * @return bool
     * @throws \M2E\Otto\Model\Exception\Logic
     */
    public function pretendedToBeSimple()
    {
        return $this->item->pretendedToBeSimple();
    }

    public function getProduct()
    {
        return $this->item->getProduct();
    }

    public function getProductId()
    {
        return $this->item->getMagentoProductId();
    }

    public function getMagentoProduct()
    {
        return $this->item->getMagentoProduct();
    }

    public function getOptions()
    {
        return $this->item->getAssociatedOptions();
    }

    public function getAssociatedProducts()
    {
        return $this->item->getAssociatedProducts();
    }

    public function getBasePrice()
    {
        return $this->getProxyOrder()->convertPriceToBase($this->getPrice());
    }

    public function setPrice($price): void
    {
        if ($price <= 0) {
            throw new \InvalidArgumentException('Price cannot be less than zero.');
        }

        $this->price = $price;
    }

    public function getPrice(): float
    {
        /** @psalm-suppress RedundantPropertyInitializationCheck */
        if (isset($this->price)) {
            return $this->price;
        }

        return $this->subtotal / $this->getQty();
    }

    public function setQty($qty): void
    {
        if ((int)$qty <= 0) {
            throw new \InvalidArgumentException('QTY cannot be less than zero.');
        }

        $this->qty = (int)$qty;
    }

    public function getQty(): int
    {
        /** @psalm-suppress RedundantPropertyInitializationCheck */
        if (isset($this->qty)) {
            return $this->qty;
        }

        return $this->getOriginalQty();
    }

    public function hasTax(): bool
    {
        return $this->getProxyOrder()->hasTax();
    }

    public function isSalesTax()
    {
        return $this->getProxyOrder()->isSalesTax();
    }

    public function isVatTax()
    {
        return $this->getProxyOrder()->isVatTax();
    }

    /**
     * @return int|float
     */
    public function getTaxRate()
    {
        return $this->getProxyOrder()->getProductPriceTaxRate();
    }

    /**
     * @return \M2E\Otto\Model\Order\Tax\PriceTaxRateInterface|null
     */
    public function getProductPriceTaxRateObject(): ?\M2E\Otto\Model\Order\Tax\PriceTaxRateInterface
    {
        return $this->getProxyOrder()->getProductPriceTaxRateObject();
    }

    public function getGiftMessage()
    {
        return null;
    }

    public function getAdditionalData(): array
    {
        if (empty($this->additionalData)) {
            $this->additionalData[\M2E\Otto\Helper\Data::CUSTOM_IDENTIFIER]['pretended_to_be_simple']
                = $this->pretendedToBeSimple();
            $this->additionalData[\M2E\Otto\Helper\Data::CUSTOM_IDENTIFIER]['items'][] = [
                'item_id' => $this->item->getOttoItemId(),
            ];
        }

        return $this->additionalData;
    }
}
