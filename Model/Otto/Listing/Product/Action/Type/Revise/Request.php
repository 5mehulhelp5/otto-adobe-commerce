<?php

declare(strict_types=1);

namespace M2E\Otto\Model\Otto\Listing\Product\Action\Type\Revise;

use M2E\Otto\Model\Product\DataProvider\Category\Attribute as CategoryAttribute;

class Request extends \M2E\Otto\Model\Otto\Listing\Product\Action\AbstractRequest
{
    use \M2E\Otto\Model\Otto\Listing\Product\Action\RequestTrait;

    private array $metadata = [];

    public function getActionData(
        \M2E\Otto\Model\Product $product,
        \M2E\Otto\Model\Otto\Listing\Product\Action\Configurator $actionConfigurator,
        array $params
    ): array {
        $dataProvider = $product->getDataProvider();

        $priceData = $dataProvider->getPrice()->getValue();
        $categoryData = $dataProvider->getCategory()->getValue();
        $deliveryData = $dataProvider->getDelivery()->getValue();
        $detailsData = $dataProvider->getDetails()->getValue();

        $brandId = $product->getOnlineBrandId();
        $brandName = null;
        $brandResolverResult = $dataProvider->getBrand();
        if ($brandResolverResult->isSuccess()) {
            $brandId = $brandResolverResult->getValue()->id;
            $brandName = $brandResolverResult->getValue()->name;
        }

        $request = [
            'sku' => $product->getOttoProductSKU(),
            'last_modified_date' => \M2E\Otto\Helper\Date::createCurrentGmt()->format('Y-m-d H:i:s'),
            'price' => $priceData->price,
            'currency_code' => $priceData->currencyCode,
            'qty' => $dataProvider->getQty()->getValue(),
        ];

        $this->metadata = [
            'price' => $request['price'],
            'qty' => $request['qty'],

        ];

        if ($actionConfigurator->isDetailsAllowed()) {
            $request['details'] = [
                'ean' => $dataProvider->getEan()->getValue(),
                'product_reference' => $product->getOnlineProductReference(),
                'product_line' => $dataProvider->getTitle()->getValue(),
                'description' => $dataProvider->getDescription()->getValue()->description,
                'brand_id' => $brandId,
                'category' => $categoryData->title,
                'attributes' => array_map(
                    static function (CategoryAttribute $attribute) {
                        return [
                            'name' => $attribute->name,
                            'values' => $attribute->values,
                        ];
                    },
                    $categoryData->attributes
                ),
                'media_assets' => array_map(
                    static function (\M2E\Otto\Model\Product\DataProvider\Images\Image $image) {
                        return [
                            'type' => $image->type,
                            'location' => $image->location,
                        ];
                    },
                    $dataProvider->getImages()->getValue()->set
                ),
                'vat' => $dataProvider->getVat()->getValue(),
                'mpn' => $detailsData->mpn,
                'manufacturer' => $detailsData->manufacturer,
                'bullet_points' => $detailsData->bulletPoints,
            ];

            $this->metadata['details'] = [
                'brand_name' => $brandName,
                'brand_id' => $request['details']['brand_id'],
                'title' => $request['details']['product_line'],
                'description_hash' => $dataProvider->getDescription()->getValue()->hash,
                'category_name' => $request['details']['category'],
                'category_attributes_hash' => $categoryData->attributesHash,
                'images_hash' => $dataProvider->getImages()->getValue()->imagesHash,
                'mpn' => $request['details']['mpn'],
                'manufacturer' => $request['details']['manufacturer'],
                'vat' => $request['details']['vat'],
                'ean' => $request['details']['ean'],
                'delivery_type' => $deliveryData->deliveryType,
                'delivery_time' => $deliveryData->deliveryTime,
            ];

            if ($deliveryData->shippingProfileId !== null) {
                $request['details']['shipping_profile_id'] = $deliveryData->shippingProfileId;

                $this->metadata['details']['shipping_profile_id'] = $deliveryData->shippingProfileId;
            } else {
                $request['details']['delivery_type'] = $deliveryData->deliveryType;
                $request['details']['delivery_time'] = $deliveryData->deliveryTime;
            }
        }

        $this->processDataProviderLogs($dataProvider);

        return $request;
    }

    protected function getActionMetadata(): array
    {
        return $this->metadata;
    }
}
