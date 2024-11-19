<?php

declare(strict_types=1);

namespace M2E\Otto\Model\Product;

use M2E\Otto\Model\Product\Repository;

class AssignCategoryTemplateService
{
    private const INSTRUCTION_INITIATOR = 'assign_template_category';

    private \M2E\Otto\Model\Product\Repository $productRepository;
    private \M2E\Otto\Model\Category\Repository $categoryRepository;
    private \M2E\Otto\Model\InstructionService $instructionService;

    public function __construct(
        Repository $productRepository,
        \M2E\Otto\Model\Category\Repository $categoryRepository,
        \M2E\Otto\Model\InstructionService $instructionService
    ) {
        $this->instructionService = $instructionService;
        $this->categoryRepository = $categoryRepository;
        $this->productRepository = $productRepository;
    }

    public function assignToProductIds(array $listingProductsIds, int $categoryTemplateId): void
    {
        if (empty($listingProductsIds)) {
            return;
        }

        $products = $this->productRepository->findByIds($listingProductsIds);
        if (empty($products)) {
            return;
        }

        $instructions = [];
        foreach ($products as $listingProduct) {
            if ($listingProduct->getTemplateCategoryId() === $categoryTemplateId) {
                continue;
            }

            $category = $this->categoryRepository->get($categoryTemplateId);
            $category->installStateSaved();
            $this->categoryRepository->save($category);

            $listingProduct->setTemplateCategoryId($categoryTemplateId);
            $this->productRepository->save($listingProduct);

            $instructions[] = [
                'listing_product_id' => $listingProduct->getId(),
                'type' => \M2E\Otto\Model\Template\ChangeProcessorAbstract::INSTRUCTION_TYPE_CATEGORIES_DATA_CHANGED,
                'initiator' => self::INSTRUCTION_INITIATOR,
                'priority' => $listingProduct->getStatus() === \M2E\Otto\Model\Product::STATUS_NOT_LISTED
                    ? 5
                    : 30,
            ];
        }

        $this->instructionService->createBatch($instructions);
    }
}
