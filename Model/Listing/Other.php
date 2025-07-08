<?php

declare(strict_types=1);

namespace M2E\Otto\Model\Listing;

use M2E\Otto\Model\ResourceModel\Listing\Other as ListingOtherResource;

class Other extends \M2E\Otto\Model\ActiveRecord\AbstractModel
{
    private \M2E\Otto\Model\Account $account;
    private ?\M2E\Otto\Model\Magento\Product\Cache $magentoProductModel = null;
    private \M2E\Otto\Model\Account\Repository $accountRepository;
    private \M2E\Otto\Model\Magento\Product\CacheFactory $productCacheFactory;

    public function __construct(
        \M2E\Otto\Model\Account\Repository $accountRepository,
        \M2E\Otto\Model\Magento\Product\CacheFactory $productCacheFactory,
        \Magento\Framework\Model\Context $context,
        \Magento\Framework\Registry $registry,
        ?\Magento\Framework\Model\ResourceModel\AbstractResource $resource = null,
        ?\Magento\Framework\Data\Collection\AbstractDb $resourceCollection = null,
        array $data = []
    ) {
        parent::__construct(
            $context,
            $registry,
            $resource,
            $resourceCollection,
            $data,
        );
        $this->accountRepository = $accountRepository;
        $this->productCacheFactory = $productCacheFactory;
    }

    public function _construct(): void
    {
        parent::_construct();
        $this->_init(\M2E\Otto\Model\ResourceModel\Listing\Other::class);
    }

    public function init(
        \M2E\Otto\Model\Account $account,
        string $productReference,
        string $ean,
        ?string $moin,
        string $sku,
        int $status,
        string $title,
        string $currency,
        float $price,
        string $vat,
        ?int $qty,
        array $media,
        string $category,
        ?string $brandId,
        array $delivery,
        ?string $productUrl,
        ?string $qtyActualizeDate,
        string $priceActualizeDate,
        bool $isIncomplete,
        ?string $shippingProfileId
    ): self {
        $this
            ->setData(ListingOtherResource::COLUMN_ACCOUNT_ID, $account->getId())
            ->setData(ListingOtherResource::COLUMN_SKU, $sku)
            ->setData(ListingOtherResource::COLUMN_PRODUCT_REFERENCE, $productReference)
            ->setData(ListingOtherResource::COLUMN_EAN, $ean)
            ->setData(ListingOtherResource::COLUMN_STATUS, $status)
            ->setData(ListingOtherResource::COLUMN_TITLE, $title)
            ->setData(ListingOtherResource::COLUMN_CURRENCY, $currency)
            ->setData(ListingOtherResource::COLUMN_PRICE, $price)
            ->setData(ListingOtherResource::COLUMN_VAT, $vat)
            ->setData(ListingOtherResource::COLUMN_QTY, $qty)
            ->setData(ListingOtherResource::COLUMN_MEDIA, json_encode($media, JSON_THROW_ON_ERROR))
            ->setData(ListingOtherResource::COLUMN_CATEGORY, $category)
            ->setData(ListingOtherResource::COLUMN_BRAND_ID, (string)$brandId)
            ->setData(ListingOtherResource::COLUMN_DELIVERY, json_encode($delivery, JSON_THROW_ON_ERROR))
            ->setOttoProductMoin($moin)
            ->setOttoProductUrl($productUrl)
            ->setQtyActualizeDate($qtyActualizeDate)
            ->setPriceActualizeDate($priceActualizeDate)
            ->makeProductIncomplete($isIncomplete)
            ->setShippingProfileId($shippingProfileId);

        $this->loadAccount($account);

        return $this;
    }

    // ----------------------------------------

    public function loadAccount(\M2E\Otto\Model\Account $account): self
    {
        $this->account = $account;

        return $this;
    }

    public function getAccount(): \M2E\Otto\Model\Account
    {
        /** @psalm-suppress RedundantPropertyInitializationCheck */
        if (isset($this->account)) {
            return $this->account;
        }

        return $this->account = $this->accountRepository->get($this->getAccountId());
    }

    // ---------------------------------------

    /**
     * @return \M2E\Otto\Model\Magento\Product\Cache
     * @throws \M2E\Otto\Model\Exception
     */
    public function getMagentoProduct(): ?\M2E\Otto\Model\Magento\Product\Cache
    {
        if ($this->magentoProductModel) {
            return $this->magentoProductModel;
        }

        if (!$this->hasMagentoProductId()) {
            throw new \M2E\Otto\Model\Exception('Product id is not set');
        }

        return $this->magentoProductModel = $this->productCacheFactory->create()
                                                                      ->setStoreId($this->getRelatedStoreId())
                                                                      ->setProductId($this->getMagentoProductId());
    }

    // ----------------------------------------

    public function getAccountId(): int
    {
        return (int)$this->getData(ListingOtherResource::COLUMN_ACCOUNT_ID);
    }

    public function hasMagentoProductId(): bool
    {
        return !empty($this->getMagentoProductId());
    }

    public function getMagentoProductId(): int
    {
        return (int)$this->getData(ListingOtherResource::COLUMN_MAGENTO_PRODUCT_ID);
    }

    public function setTitle(string $value): void
    {
        $this->setData(ListingOtherResource::COLUMN_TITLE, $value);
    }

    public function getTitle(): string
    {
        return (string)$this->getData(ListingOtherResource::COLUMN_TITLE);
    }

    public function getSku(): string
    {
        return (string)$this->getData(ListingOtherResource::COLUMN_SKU);
    }

    public function setSku(string $value): void
    {
        $this->setData(ListingOtherResource::COLUMN_SKU, $value);
    }

    public function getEan(): string
    {
        return (string)$this->getData(ListingOtherResource::COLUMN_EAN);
    }

    public function setOttoProductMoin(?string $moin): self
    {
        $this->setData(ListingOtherResource::COLUMN_MOIN, $moin);

        return $this;
    }

    public function getOttoProductMoin(): ?string
    {
        return $this->getData(ListingOtherResource::COLUMN_MOIN);
    }

    public function setPrice(float $value): void
    {
        $this->setData(ListingOtherResource::COLUMN_PRICE, $value);
    }

    public function getPrice(): float
    {
        return (float)$this->getData(ListingOtherResource::COLUMN_PRICE);
    }

    public function setQty(int $value): void
    {
        $this->setData(ListingOtherResource::COLUMN_QTY, $value);
    }

    public function getQty(): int
    {
        return (int)$this->getData(ListingOtherResource::COLUMN_QTY);
    }

    public function getVat(): int
    {
        return (int)$this->getData(ListingOtherResource::COLUMN_VAT);
    }

    public function getCurrency(): string
    {
        return (string)$this->getData(ListingOtherResource::COLUMN_CURRENCY);
    }

    public function getStatus(): int
    {
        return (int)$this->getData(ListingOtherResource::COLUMN_STATUS);
    }

    public function setStatus(int $status): self
    {
        $this->setData(ListingOtherResource::COLUMN_STATUS, $status);

        return $this;
    }

    public function getMedia(): array
    {
        $json = $this->getData(ListingOtherResource::COLUMN_MEDIA);
        if ($json === null) {
            return [];
        }

        return json_decode($json, true);
    }

    public function getCategory(): ?string
    {
        return $this->getData(ListingOtherResource::COLUMN_CATEGORY);
    }

    public function getShippingProfileId(): ?string
    {
        return $this->getData(ListingOtherResource::COLUMN_SHIPPING_PROFILE_ID);
    }

    public function setShippingProfileId(?string $shippingProfileId): self
    {
        $this->setData(ListingOtherResource::COLUMN_SHIPPING_PROFILE_ID, $shippingProfileId);

        return $this;
    }

    public function hasOttoProductUrl(): bool
    {
        return $this->getOttoProductUrl() !== null;
    }

    public function setOttoProductUrl(?string $url): self
    {
        $this->setData(ListingOtherResource::COLUMN_OTTO_PRODUCT_URL, $url);

        return $this;
    }

    public function getOttoProductUrl(): ?string
    {
        return $this->getData(ListingOtherResource::COLUMN_OTTO_PRODUCT_URL);
    }

    public function getProductReference(): string
    {
        return $this->getData(ListingOtherResource::COLUMN_PRODUCT_REFERENCE);
    }

    public function getDeliveryType(): ?string
    {
        $deliveryData = json_decode($this->getData(ListingOtherResource::COLUMN_DELIVERY), true);
        if (empty($deliveryData)) {
            return null;
        }

        return $deliveryData['delivery_type'] ?? null;
    }

    // ---------------------------------------

    public function mapToMagentoProduct(int $magentoProductId): void
    {
        $this->setData(ListingOtherResource::COLUMN_MAGENTO_PRODUCT_ID, $magentoProductId);
    }

    public function unmapProduct(): void
    {
        $this->setData(ListingOtherResource::COLUMN_MAGENTO_PRODUCT_ID, null);
    }

    // ---------------------------------------

    public function setMovedToListingProductId(int $id): void
    {
        $this->setData(ListingOtherResource::COLUMN_MOVED_TO_LISTING_PRODUCT_ID, $id);
    }

    private function getMovedToListingProductId(): int
    {
        return (int)$this->getData(ListingOtherResource::COLUMN_MOVED_TO_LISTING_PRODUCT_ID);
    }

    public function getRelatedStoreId(): int
    {
        return $this->getAccount()->getUnmanagedListingSettings()->getRelatedStoreId();
    }

    public function getQtyActualizeDate()
    {
        return $this->getData(ListingOtherResource::COLUMN_QTY_ACTUALIZE_DATE);
    }

    public function setQtyActualizeDate($qtyActualizeDate)
    {
        return $this->setData(ListingOtherResource::COLUMN_QTY_ACTUALIZE_DATE, $qtyActualizeDate);
    }

    public function getPriceActualizeDate()
    {
        return $this->getData(ListingOtherResource::COLUMN_PRICE_ACTUALIZE_DATE);
    }

    public function setPriceActualizeDate($priceActualizeDate)
    {
        return $this->setData(ListingOtherResource::COLUMN_PRICE_ACTUALIZE_DATE, $priceActualizeDate);
    }

    public function makeProductIncomplete(bool $isIncomplete): self
    {
        return $this->setData(ListingOtherResource::COLUMN_IS_INCOMPLETE, $isIncomplete);
    }

    public function isProductIncomplete(): bool
    {
        return (bool)$this->getData(ListingOtherResource::COLUMN_IS_INCOMPLETE);
    }
}
