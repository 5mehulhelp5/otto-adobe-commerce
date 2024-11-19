<?php

declare(strict_types=1);

namespace M2E\Otto\Model\Template\Description;

class Diff extends \M2E\Otto\Model\ActiveRecord\Diff
{
    public function isDifferent(): bool
    {
        return $this->isTitleDifferent()
            || $this->isDescriptionDifferent()
            || $this->isImagesDifferent()
            || $this->isBulletPointsDifferent();
    }

    public function isTitleDifferent(): bool
    {
        $keys = [
            \M2E\Otto\Model\ResourceModel\Template\Description::COLUMN_TITLE_MODE,
            \M2E\Otto\Model\ResourceModel\Template\Description::COLUMN_TITLE_TEMPLATE,
        ];

        return $this->isSettingsDifferent($keys);
    }

    public function isDescriptionDifferent(): bool
    {
        $keys = [
            \M2E\Otto\Model\ResourceModel\Template\Description::COLUMN_DESCRIPTION_MODE,
            \M2E\Otto\Model\ResourceModel\Template\Description::COLUMN_DESCRIPTION_TEMPLATE,
        ];

        return $this->isSettingsDifferent($keys);
    }

    public function isImagesDifferent(): bool
    {
        $keys = [
            \M2E\Otto\Model\ResourceModel\Template\Description::COLUMN_IMAGE_MAIN_MODE,
            \M2E\Otto\Model\ResourceModel\Template\Description::COLUMN_IMAGE_MAIN_ATTRIBUTE,

            \M2E\Otto\Model\ResourceModel\Template\Description::COLUMN_GALLERY_TYPE,
            \M2E\Otto\Model\ResourceModel\Template\Description::COLUMN_GALLERY_IMAGES_MODE,
            \M2E\Otto\Model\ResourceModel\Template\Description::COLUMN_GALLERY_IMAGES_ATTRIBUTE,
            \M2E\Otto\Model\ResourceModel\Template\Description::COLUMN_GALLERY_IMAGES_LIMIT,
        ];

        return $this->isSettingsDifferent($keys);
    }

    public function isBulletPointsDifferent(): bool
    {
        $keys = [
            \M2E\Otto\Model\ResourceModel\Template\Description::COLUMN_BULLET_POINTS,
        ];

        return $this->isSettingsDifferent($keys);
    }
}
