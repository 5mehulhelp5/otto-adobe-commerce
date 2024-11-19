<?php

declare(strict_types=1);

namespace M2E\Otto\Model\Template\Description;

use M2E\Otto\Model\Template\Description as DescriptionAlias;

class Builder extends \M2E\Otto\Model\Otto\Template\AbstractBuilder
{
    /**
     * @throws \M2E\Otto\Model\Exception\Logic
     */
    protected function prepareData(): array
    {
        $data = parent::prepareData();

        $defaultData = $this->getDefaultData();

        $data = \M2E\Otto\Helper\Data::arrayReplaceRecursive($defaultData, $data);

        if (isset($this->rawData['title_mode'])) {
            $data['title_mode'] = (int)$this->rawData['title_mode'];
        }

        if (isset($this->rawData['title_template'])) {
            $data['title_template'] = $this->rawData['title_template'];
        }

        if (isset($this->rawData['description_mode'])) {
            $data['description_mode'] = (int)$this->rawData['description_mode'];
        }

        if (isset($this->rawData['description_template'])) {
            $data['description_template'] = $this->rawData['description_template'];
        }

        if (isset($this->rawData['editor_type'])) {
            $data['editor_type'] = (int)$this->rawData['editor_type'];
        }

        if (isset($this->rawData['image_main_mode'])) {
            $data['image_main_mode'] = (int)$this->rawData['image_main_mode'];
        }

        if (isset($this->rawData['image_main_attribute'])) {
            $data['image_main_attribute'] = $this->rawData['image_main_attribute'];
        }

        if (isset($this->rawData['gallery_images_mode'])) {
            $data['gallery_images_mode'] = (int)$this->rawData['gallery_images_mode'];
        }

        if (isset($this->rawData['gallery_images_limit'])) {
            $data['gallery_images_limit'] = (int)$this->rawData['gallery_images_limit'];
        }

        if (isset($this->rawData['gallery_images_attribute'])) {
            $data['gallery_images_attribute'] = $this->rawData['gallery_images_attribute'];
        }

        $bulletPoints = [];
        if (isset($this->rawData['bullet_points'])) {
            foreach ($this->rawData['bullet_points'] as $bulletPoint) {
                if (empty($bulletPoint)) {
                    continue;
                }
                $bulletPoints[] = $bulletPoint;
            }
        }
        if (!empty($bulletPoints)) {
            $data['bullet_points'] = json_encode($bulletPoints);
        }

        return $data;
    }

    // ----------------------------------------

    /**
     * @throws \M2E\Otto\Model\Exception\Logic
     */
    public function getDefaultData(): array
    {
        return [
            'title_mode' => DescriptionAlias::TITLE_MODE_PRODUCT,
            'title_template' => '',
            'description_mode' => '',
            'description_template' => '',
            'editor_type' => DescriptionAlias::EDITOR_TYPE_SIMPLE,
            'image_main_mode' => DescriptionAlias::IMAGE_MAIN_MODE_PRODUCT,
            'image_main_attribute' => '',
            'gallery_images_mode' => DescriptionAlias::GALLERY_IMAGES_MODE_NONE,
            'gallery_images_limit' => 0,
            'gallery_images_attribute' => '',
            'variation_images_mode' => DescriptionAlias::VARIATION_IMAGES_MODE_PRODUCT,
            'variation_images_limit' => 1,
            'variation_images_attribute' => '',
            'default_image_url' => '',
            'variation_configurable_images' => \M2E\Otto\Helper\Json::encode([]),
            'bullet_points' => '[]',
        ];
    }
}
