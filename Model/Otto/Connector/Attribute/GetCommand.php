<?php

declare(strict_types=1);

namespace M2E\Otto\Model\Otto\Connector\Attribute;

use M2E\Otto\Model\Otto\Connector\Attribute\Attribute;
use M2E\Otto\Model\Otto\Connector\Attribute\Get;

class GetCommand implements \M2E\Core\Model\Connector\CommandInterface
{
    private string $categoryGroupId;

    public function __construct(string $categoryGroupId)
    {
        $this->categoryGroupId = $categoryGroupId;
    }

    public function getCommand(): array
    {
        return ['category', 'get', 'attributes'];
    }

    public function getRequestData(): array
    {
        return [
            'category_group_id' => $this->categoryGroupId,
        ];
    }

    public function parseResponse(\M2E\Core\Model\Connector\Response $response): Get\Response
    {
        $responseData = $response->getResponseData();

        $attributes = [];
        foreach ($responseData['attributes'] as $attributeData) {
            $attribute = new Attribute(
                $attributeData['title'],
                $attributeData['description'],
                $attributeData['type'],
                $attributeData['is_required'],
                $attributeData['is_multiple_selected'],
                $attributeData['allowed_values'],
                $attributeData['example_values'],
                $attributeData['relevance'],
                $attributeData['required_media_types'],
                $attributeData['unit']
            );

            $attributes[] = $attribute;
        }

        return new \M2E\Otto\Model\Otto\Connector\Attribute\Get\Response(
            $attributes
        );
    }
}
