<?php

declare(strict_types=1);

namespace M2E\Otto\Model\Otto\Listing\Product\Action\Type\Revise;

class Logger
{
    private array $logs = [];
    private \Magento\Framework\Locale\CurrencyInterface $localeCurrency;

    private float $onlinePrice;
    private int $onlineQty;
    private string $onlineTitle;
    private string $onlineDescription;
    private string $onlineImagesData;
    private string $onlineCategoryName;
    private string $onlineCategoryAttributesData;
    private string $onlineBrandName;
    private ?string $onlineMpn;
    private ?string $onlineManufacturer;

    public function __construct(
        \Magento\Framework\Locale\CurrencyInterface $localeCurrency
    ) {
        $this->localeCurrency = $localeCurrency;
    }

    public function saveProductDataBeforeUpdate(\M2E\Otto\Model\Product $product): void
    {
        $this->onlinePrice = $product->getOnlineCurrentPrice();
        $this->onlineQty = $product->getOnlineQty();
        $this->onlineTitle = $product->getOnlineTitle();
        $this->onlineDescription = $product->getOnlineDescription();
        $this->onlineImagesData = $product->getOnlineImagesData();
        $this->onlineCategoryName = $product->getOnlineCategoryName();
        $this->onlineCategoryAttributesData = $product->getOnlineCategoryAttributesData();
        $this->onlineBrandName = $product->getOnlineBrandName();
        $this->onlineMpn = $product->getOnlineMpn();
        $this->onlineManufacturer = $product->getOnlineManufacturer();
    }

    public function collectSuccessMessages(\M2E\Otto\Model\Product $product): array
    {
        $this->generateMessageAboutChangePrice($product);
        $this->generateMessageAboutChangeQty($product);
        $this->generateMessageAboutChangeTitle($product);
        $this->generateMessageAboutChangeDescription($product);
        $this->generateMessageAboutChangeImages($product);
        $this->generateMessageAboutChangeCategories($product);
        $this->generateMessageAboutChangeBrand($product);
        $this->generateMessageAboutChangeMpnManufacturer($product);

        return $this->logs;
    }

    private function generateMessageAboutChangePrice(\M2E\Otto\Model\Product $product): void
    {
        $from = $this->onlinePrice;
        $to = $product->getOnlineCurrentPrice();
        if ($from === $to) {
            return;
        }

        $currencyCode = $product->getCurrencyCode();
        $currency = $this->localeCurrency->getCurrency($currencyCode);

        $this->logs[] = sprintf(
            'Product Price was revised from %s to %s',
            $currency->toCurrency($from),
            $currency->toCurrency($to)
        );
    }

    private function generateMessageAboutChangeQty(\M2E\Otto\Model\Product $product): void
    {
        $from = $this->onlineQty;
        $to = $product->getOnlineQty();
        if ($from === $to) {
            return;
        }

        $this->logs[] = sprintf('Product QTY was revised from %s to %s', $from, $to);
    }

    private function generateMessageAboutChangeTitle(\M2E\Otto\Model\Product $product): void
    {
        if ($this->onlineTitle !== $product->getOnlineTitle()) {
            $this->logs[] = 'Item was revised: Product Title was updated.';
        }
    }

    private function generateMessageAboutChangeDescription(\M2E\Otto\Model\Product $product): void
    {
        if ($this->onlineDescription !== $product->getOnlineDescription()) {
            $this->logs[] = 'Item was revised: Product Description was updated.';
        }
    }

    private function generateMessageAboutChangeImages(\M2E\Otto\Model\Product $product): void
    {
        if ($this->onlineImagesData !== $product->getOnlineImagesData()) {
            $this->logs[] = 'Item was revised: Product Images were updated.';
        }
    }

    private function generateMessageAboutChangeCategories(\M2E\Otto\Model\Product $product): void
    {
        if (
            $this->onlineCategoryName !== $product->getOnlineCategoryName()
            || $this->onlineCategoryAttributesData !== $product->getOnlineCategoryAttributesData()
        ) {
            $this->logs[] = 'Item was revised: Product Categories were updated.';
        }
    }

    private function generateMessageAboutChangeBrand(\M2E\Otto\Model\Product $product): void
    {
        if ($this->onlineBrandName !== $product->getOnlineBrandName()) {
            $this->logs[] = 'Item was revised: Product Brand was updated.';
        }
    }

    private function generateMessageAboutChangeMpnManufacturer(\M2E\Otto\Model\Product $product): void
    {
        if (
            $this->onlineMpn !== $product->getOnlineMpn()
            || $this->onlineManufacturer !== $product->getOnlineManufacturer()
        ) {
            $this->logs[] = 'Item was revised: Product MPN/Manufacturer were updated.';
        }
    }
}
