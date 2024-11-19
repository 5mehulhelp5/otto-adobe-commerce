<?php

declare(strict_types=1);

namespace M2E\Otto\Controller\Adminhtml\Otto\Category;

class GetCountsOfAttributes extends \M2E\Otto\Controller\Adminhtml\Otto\AbstractCategory
{
    private \M2E\Otto\Model\Dictionary\Attribute\Repository $attributeDictionaryRepository;
    private \M2E\Otto\Model\Category\Repository $categoryRepository;
    private \M2E\Otto\Model\Category\Attribute\AttributeService $attributeService;

    public function __construct(
        \M2E\Otto\Model\Category\Repository $categoryRepository,
        \M2E\Otto\Model\Dictionary\Attribute\Repository $attributeDictionaryRepository,
        \M2E\Otto\Model\Category\Attribute\AttributeService $attributeService
    ) {
        parent::__construct();
        $this->attributeService = $attributeService;
        $this->categoryRepository = $categoryRepository;
        $this->attributeDictionaryRepository = $attributeDictionaryRepository;
    }

    public function execute()
    {
        $categoryGroupId = $this->getRequest()->getParam('category_group_id');
        $categoryId = $this->getRequest()->getParam('category_id');
        if (empty($categoryGroupId)) {
            throw new \M2E\Otto\Model\Exception\Logic('Invalid input');
        }

        $counts = [
            'used' => 0,
            'total' => 0,
        ];

        if ($category = $this->categoryRepository->find((int)$categoryId)) {
            $counts['used'] = $category->getUsedProductAttributes();
            $counts['total'] = $category->getTotalProductAttributes();
        } else {
            $counts['total'] = $this->attributeDictionaryRepository->getAttributesCountByCategoryGroupId($categoryGroupId)
                + $this->attributeService->countCustomAttributes();
        }

        $this->setJsonContent($counts);

        return $this->getResult();
    }
}
