<?php

declare(strict_types=1);

namespace M2E\Otto\Model\Otto\Connector\Category;

class GetCommand implements \M2E\Otto\Model\Connector\CommandInterface
{
    public function getCommand(): array
    {
        return ['category', 'get', 'groups'];
    }

    public function getRequestData(): array
    {
        return [];
    }

    public function parseResponse(\M2E\Otto\Model\Connector\Response $response): Get\Response
    {
        $result = new Get\Response();
        foreach ($response->getResponseData()['category_groups'] as $categoryGroupData) {
            $result->addCategoryGroup(
                new CategoryGroup(
                    $categoryGroupData['id'],
                    $categoryGroupData['title'],
                    $categoryGroupData['product_title_pattern'],
                )
            );

            foreach ($categoryGroupData['categories'] as $categoryTitle) {
                $result->addCategory(new Category($categoryGroupData['id'], $categoryTitle));
            }
        }

        return $result;
    }
}
