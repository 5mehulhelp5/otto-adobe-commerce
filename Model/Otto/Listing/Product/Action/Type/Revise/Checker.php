<?php

declare(strict_types=1);

namespace M2E\Otto\Model\Otto\Listing\Product\Action\Type\Revise;

class Checker
{
    public function isNeedReviseForTitle(\M2E\Otto\Model\Product $product): bool
    {
        if (!$this->isTitleReviseEnabled($product)) {
            return false;
        }

        return $product->getDataProvider()->getTitle()->getValue() !== $product->getOnlineTitle();
    }

    public function isNeedReviseForDescription(\M2E\Otto\Model\Product $product): bool
    {
        if (!$this->isDescriptionReviseEnabled($product)) {
            return false;
        }

        return $product->getDataProvider()->getDescription()->getValue()->hash !== $product->getOnlineDescription();
    }

    public function isNeedReviseForBrand(\M2E\Otto\Model\Product $product): bool
    {
        if (!$this->isCategoriesReviseEnabled($product)) {
            return false;
        }

        $providerResult = $product->getDataProvider()->getBrand();
        if (!$providerResult->isSuccess()) {
            return false;
        }

        $brandValue = $providerResult->getValue();

        return $brandValue->name !== $product->getOnlineBrandName();
    }

    public function isNeedReviseForImages(\M2E\Otto\Model\Product $product): bool
    {
        if (!$this->isImagesReviseEnabled($product)) {
            return false;
        }

        $provideImagesValue = $product->getDataProvider()->getImages()->getValue();

        return $provideImagesValue->imagesHash !== $product->getOnlineImagesData();
    }

    public function isNeedReviseForCategories(
        \M2E\Otto\Model\Product $product
    ): bool {
        if (!$this->isCategoriesReviseEnabled($product)) {
            return false;
        }

        $providerResult = $product->getDataProvider()->getCategory();
        if (!$providerResult->isSuccess()) {
            return false;
        }

        $provideCategoryValue = $providerResult->getValue();

        return $provideCategoryValue->title !== $product->getOnlineCategoryName()
            || $provideCategoryValue->attributesHash !== $product->getOnlineCategoryAttributesData();
    }

    public function isNeedReviseForMpnOrManufacturer(\M2E\Otto\Model\Product $product): bool
    {
        if (!$this->isCategoriesReviseEnabled($product)) {
            return false;
        }

        $providerResult = $product->getDataProvider()->getDetails();
        if (!$providerResult->isSuccess()) {
            return false;
        }

        $provideDetailsValue = $providerResult->getValue();

        return $provideDetailsValue->mpn !== $product->getOnlineMpn()
            || $provideDetailsValue->manufacturer !== $product->getOnlineManufacturer();
    }

    public function isNeedReviseForShippingProfile(\M2E\Otto\Model\Product $product): bool
    {
        $provideShippingProfileValue = $product->getDataProvider()->getDelivery()->getValue();

        return $provideShippingProfileValue->shippingProfileId !== $product->getOnlineShippingProfileId();
    }

    private function isTitleReviseEnabled(\M2E\Otto\Model\Product $product): bool
    {
        return $product->getSynchronizationTemplate()->isReviseUpdateTitle();
    }

    private function isDescriptionReviseEnabled(\M2E\Otto\Model\Product $product): bool
    {
        return $product->getSynchronizationTemplate()->isReviseUpdateDescription();
    }

    private function isImagesReviseEnabled(\M2E\Otto\Model\Product $product): bool
    {
        return $product->getSynchronizationTemplate()->isReviseUpdateImages();
    }

    private function isCategoriesReviseEnabled(\M2E\Otto\Model\Product $product): bool
    {
        return $product->getSynchronizationTemplate()->isReviseUpdateCategories();
    }
}
