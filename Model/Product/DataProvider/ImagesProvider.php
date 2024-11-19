<?php

declare(strict_types=1);

namespace M2E\Otto\Model\Product\DataProvider;

class ImagesProvider implements DataBuilderInterface
{
    use DataBuilderHelpTrait;

    public const NICK = 'Images';

    public function getImages(\M2E\Otto\Model\Product $product): Images\Value
    {
        $productImageSet = $product->getDescriptionTemplateSource()->getImageSet();

        $set = [];
        foreach ($productImageSet->getAll() as $productImage) {
            $set[] = new \M2E\Otto\Model\Product\DataProvider\Images\Image($productImage->getMediaType(), $productImage->getUrl());
        }

        $mainImage = null;
        $mainProductImage = $product->getDescriptionTemplateSource()->getMainImage();
        if ($mainProductImage !== null) {
            $mainImage = new \M2E\Otto\Model\Product\DataProvider\Images\Image(
                $mainProductImage->getMediaType(),
                $mainProductImage->getUrl()
            );
        }
        $imagesHash = $this->generateImagesHash($set);

        return new Images\Value(
            $mainImage,
            $set,
            $imagesHash
        );
    }

    /**
     * @param \M2E\Otto\Model\Product\DataProvider\Images\Image[] $set
     *
     * @return string
     */
    private function generateImagesHash(array $set): string
    {
        $flatImages = [];
        foreach ($set as $image) {
            $flatImages[] = $image->type . $image->location;
        }

        sort($flatImages);

        return sha1(json_encode($flatImages));
    }
}
