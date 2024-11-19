<?php

declare(strict_types=1);

namespace M2E\Otto\Model\Otto\Template\Category;

class SnapshotBuilder extends \M2E\Otto\Model\ActiveRecord\SnapshotBuilder
{
    public function getSnapshot(): array
    {
        $data = [];

        foreach ($this->getModel()->getRelatedAttributes() as $attribute) {
            $data[$attribute->getCategoryGroupAttributeDictionaryId()] = $this->makeAttributeHash($attribute);
        }

        ksort($data);

        return ['attributes' => json_encode($data, JSON_THROW_ON_ERROR)];
    }

    private function makeAttributeHash(\M2E\Otto\Model\Category\Attribute $attribute)
    {
        return json_encode([
            $attribute->getCategoryGroupAttributeDictionaryId(),
            $attribute->getAttributeName(),
            $attribute->getAttributeType(),
            $attribute->getValueMode(),
            $attribute->getRecommendedValue(),
            $attribute->getCustomValue(),
            $attribute->getCustomAttributeValue(),
        ], JSON_THROW_ON_ERROR);
    }
}
