<?php

declare(strict_types=1);

namespace M2E\Otto\Model\Listing\Other;

class OttoProduct
{
    private const MARKETPLACE_STATUS_ONLINE = 'ONLINE';

    private int $accountId;
    private int $status;
    private string $productReference;
    private string $sku;
    private string $ean;
    private ?string $moin;
    private string $title;
    private string $currency;
    private float $price;
    private ?int $qty;
    private string $vat;
    private ?string $brandId;
    private string $category;
    private array $media;
    private array $delivery;
    private ?string $qtyActualizeDate;
    private string $priceActualizeDate;
    private ?string $productUrl;
    private ?string $marketplaceStatus;
    private ?string $shippingProfileId;

    public function __construct(
        int $accountId,
        int $status,
        string $productReference,
        string $sku,
        string $ean,
        ?string $moin,
        string $title,
        string $currency,
        float $price,
        ?int $qty,
        string $vat,
        ?string $brandId,
        string $category,
        array $media,
        array $delivery,
        ?string $productUrl,
        ?string $qtyActualizeDate,
        string $priceActualizeDate,
        ?string $marketplaceStatus,
        ?string $shippingProfileId
    ) {
        $this->accountId = $accountId;
        $this->status = $status;
        $this->productReference = $productReference;
        $this->sku = $sku;
        $this->moin = $moin;
        $this->ean = $ean;
        $this->title = $title;
        $this->currency = $currency;
        $this->price = $price;
        $this->qty = $qty;
        $this->vat = $vat;
        $this->brandId = $brandId;
        $this->category = $category;
        $this->media = $media;
        $this->delivery = $delivery;
        $this->qtyActualizeDate = $qtyActualizeDate;
        $this->priceActualizeDate = $priceActualizeDate;
        $this->productUrl = $productUrl;
        $this->marketplaceStatus = $marketplaceStatus;
        $this->shippingProfileId = $shippingProfileId;
    }

    public function getAccountId(): int
    {
        return $this->accountId;
    }

    public function getStatus(): int
    {
        return $this->status;
    }

    public function getProductReference(): string
    {
        return $this->productReference;
    }

    public function getSku(): string
    {
        return $this->sku;
    }

    public function getEan(): string
    {
        return $this->ean;
    }

    public function getMoin(): ?string
    {
        return $this->moin;
    }

    public function getTitle(): string
    {
        return $this->title;
    }

    public function getCurrency(): string
    {
        return $this->currency;
    }

    public function getPrice(): float
    {
        return $this->price;
    }

    public function getQty(): ?int
    {
        return $this->qty;
    }

    public function getVat(): string
    {
        return $this->vat;
    }

    public function getBrandId(): ?string
    {
        return $this->brandId;
    }

    public function getCategory(): string
    {
        return $this->category;
    }

    public function getMedia(): array
    {
        return $this->media;
    }

    public function getDelivery(): array
    {
        return $this->delivery;
    }

    public function getDeliveryType(): ?string
    {
        return $this->delivery['delivery_type'] ?? null;
    }

    public function getProductUrl(): ?string
    {
        return $this->productUrl;
    }

    public function getQtyActualizeDate(): ?string
    {
        return $this->qtyActualizeDate;
    }

    public function getPriceActualizeDate(): string
    {
        return $this->priceActualizeDate;
    }

    public function getShippingProfileId(): ?string
    {
        return $this->shippingProfileId;
    }

    public function isChannelProductComplete(): bool
    {
        return $this->isMarketplaceStatusOnline() && $this->isStatusActive();
    }

    public function isChannelProductInComplete(): bool
    {
        return !$this->isMarketplaceStatusOnline() && $this->isStatusActive();
    }

    public function isStatusActive(): bool
    {
        return $this->getStatus() === \M2E\Otto\Model\Product::STATUS_LISTED;
    }

    private function isMarketplaceStatusOnline(): bool
    {
        return $this->getMarketplaceStatus() === self::MARKETPLACE_STATUS_ONLINE;
    }

    private function getMarketplaceStatus(): ?string
    {
        return $this->marketplaceStatus;
    }
}
