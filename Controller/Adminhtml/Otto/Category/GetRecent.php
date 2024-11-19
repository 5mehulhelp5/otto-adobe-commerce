<?php

declare(strict_types=1);

namespace M2E\Otto\Controller\Adminhtml\Otto\Category;

class GetRecent extends \M2E\Otto\Controller\Adminhtml\Otto\AbstractCategory
{
    private \M2E\Otto\Model\Category\Repository $categoryRepository;

    public function __construct(
        \M2E\Otto\Model\Category\Repository $categoryRepository
    ) {
        parent::__construct();

        $this->categoryRepository = $categoryRepository;
    }

    public function execute()
    {
        $categories = $this->categoryRepository->getAll();

        $result = [];
        foreach ($categories as $category) {
            $result[] = [
                'id' => $category->getId(),
                'title' => $category->getTitle(),
            ];
        }

        $this->setJsonContent($result);

        return $this->getResult();
    }
}
