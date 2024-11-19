<?php

declare(strict_types=1);

namespace M2E\Otto\Model\Otto\Listing\Product\Action\Type\Revise;

use M2E\Otto\Model\Product\DataProvider\PriceProvider;
use M2E\Otto\Model\Product\DataProvider\QtyProvider;

class Response extends \M2E\Otto\Model\Otto\Listing\Product\Action\Type\AbstractResponse
{
    private \M2E\Otto\Model\Product\Repository $productRepository;
    protected \Magento\Framework\Locale\CurrencyInterface $localeCurrency;

    public function __construct(
        \M2E\Otto\Model\Product\Repository $productRepository,
        \Magento\Framework\Locale\CurrencyInterface $localeCurrency,
        \M2E\Otto\Model\Tag\ListingProduct\Buffer $tagBuffer,
        \M2E\Otto\Model\Otto\TagFactory $tagFactory
    ) {
        parent::__construct($tagBuffer, $tagFactory);

        $this->productRepository = $productRepository;
        $this->localeCurrency = $localeCurrency;
    }

    public function process(): void
    {
        $responseData = $this->getResponseData();
        if (!empty($responseData['products'][0]['messages'])) {
            $this->addTags($responseData['products'][0]['messages']);
        }

        if (!$this->validateProduct()) {
            return;
        }

        $this->processSuccess();
    }

    private function validateProduct(): bool
    {
        return $this->getResponseData()['products'][0]['sku'] === $this->getProduct()->getOttoProductSku();
    }

    protected function processSuccess(): void
    {
        $requestMetadata = $this->getRequestMetaData();
        $responseData = $this->getResponseData();

        $product = $this->getProduct();
        $configurator = $this->getConfigurator();

        $productResponseData = $responseData['products'][0];

        if (
            $this->isTriedUpdatePrice(
                isset($productResponseData['price']),
                isset($requestMetadata['price']) || isset($requestMetadata[PriceProvider::NICK]['price'])
            )
        ) {
            $priceUpdateStatus = $productResponseData['price'];
            $requestMetadataPrice = $requestMetadata['price'] ?? $requestMetadata[PriceProvider::NICK]['price'];
            if (!$priceUpdateStatus) {
                $this->getLogBuffer()->addFail('Price failed to be revised.');
            } else {
                $message = $this->generateMessageAboutChangePrice($product, $requestMetadataPrice);
                if ($message !== null) {
                    $this->getLogBuffer()->addSuccess($message);
                }

                $product->setOnlinePrice($requestMetadataPrice);
            }
        }

        if (
            $this->isTriedUpdateQty(
                isset($productResponseData['qty']),
                isset($requestMetadata['qty']) || isset($requestMetadata[QtyProvider::NICK]['qty'])
            )
        ) {
            $qtyUpdateStatus = $productResponseData['qty'];
            $requestMetadataQty = $requestMetadata['qty'] ?? $requestMetadata[QtyProvider::NICK]['qty'];
            if (!$qtyUpdateStatus) {
                $this->getLogBuffer()->addFail('Qty failed to be revised.');
            } else {
                $message = $this->generateMessageAboutChangeQty($product, $requestMetadataQty);
                if ($message !== null) {
                    $this->getLogBuffer()->addSuccess($message);
                }

                $product->setOnlineQty($requestMetadataQty);
            }
        }

        if ($this->isTriedUpdateDetails(isset($productResponseData['details']), isset($requestMetadata['details']))) {
            $detailUpdateStatus = $productResponseData['details'];
            if (!$detailUpdateStatus) {
                $this->getLogBuffer()->addFail('Details failed to be revised.');
            } else {
                $this->generateMessageAboutUpdatedDetails($configurator);

                $product
                    ->setOnlineBrandId($requestMetadata['details']['brand_id'])
                    ->setOnlineBrandName($requestMetadata['details']['brand_name'])
                    ->setOnlineTitle($requestMetadata['details']['title'])
                    ->setOnlineDescription($requestMetadata['details']['description_hash'])
                    ->setOnlineCategoryName($requestMetadata['details']['category_name'])
                    ->setOnlineCategoryAttributesData($requestMetadata['details']['category_attributes_hash'])
                    ->setOnlineImagesData($requestMetadata['details']['images_hash'])
                    ->setOnlineMpn($requestMetadata['details']['mpn'])
                    ->setOnlineManufacturer($requestMetadata['details']['manufacturer'])
                    ->setOnlineVat($requestMetadata['details']['vat'])
                    ->setOnlineEan($requestMetadata['details']['ean'])
                    ->setOnlineDeliveryData(
                        $requestMetadata['details']['delivery_type'],
                        $requestMetadata['details']['delivery_time'],
                    )
                    ->setOnlineShippingProfileId($requestMetadata['details']['shipping_profile_id'] ?? null)
                    ->setOnlineDeliveryType($requestMetadata['details']['delivery_type']);
            }
        }

        $product->setStatus(\M2E\Otto\Model\Product::STATUS_LISTED, $this->getStatusChanger());

        $product->removeBlockingByError();

        $this->productRepository->save($product);
    }

    private function isTriedUpdatePrice(bool $isPricePresentInResponse, bool $isSendPrice): bool
    {
        return $isPricePresentInResponse && $isSendPrice;
    }

    private function isTriedUpdateQty(bool $isQtyPresentInResponse, bool $isSendQty): bool
    {
        return $isQtyPresentInResponse && $isSendQty;
    }

    private function isTriedUpdateDetails(bool $isDetailsPresentInResponse, bool $isSendDetails): bool
    {
        return $isDetailsPresentInResponse && $isSendDetails;
    }

    private function generateMessageAboutChangePrice(\M2E\Otto\Model\Product $product, float $to): ?string
    {
        $from = $product->getOnlineCurrentPrice();
        if ($from === $to) {
            return null;
        }

        $currencyCode = $product->getCurrencyCode();
        $currency = $this->localeCurrency->getCurrency($currencyCode);

        return sprintf(
            'Price was revised from %s to %s',
            $currency->toCurrency($from),
            $currency->toCurrency($to)
        );
    }

    private function generateMessageAboutChangeQty(\M2E\Otto\Model\Product $product, int $to): ?string
    {
        $from = $product->getOnlineQty();
        if ($from === $to) {
            return null;
        }

        return sprintf('QTY was revised from %s to %s', $from, $to);
    }

    private function generateMessageAboutUpdatedDetails($configurator): void
    {
        if ($configurator->isTitleAllowed()) {
            $this->getLogBuffer()->addSuccess('Title was revised.');
        }

        if ($configurator->isDescriptionAllowed()) {
            $this->getLogBuffer()->addSuccess('Description was revised.');
        }

        if ($configurator->isImagesAllowed()) {
            $this->getLogBuffer()->addSuccess('Images were revised.');
        }

        if ($configurator->isCategoriesAllowed()) {
            $this->getLogBuffer()->addSuccess('Categories were revised.');
        }
    }

    public function generateResultMessage(): void
    {
        if (!$this->validateProduct()) {
            return;
        }

        $responseData = $this->getResponseData();

        foreach ($responseData['products'][0]['messages'] ?? [] as $messageData) {
            $this->getLogBuffer()->addFail($messageData['title']);
        }
    }
}
