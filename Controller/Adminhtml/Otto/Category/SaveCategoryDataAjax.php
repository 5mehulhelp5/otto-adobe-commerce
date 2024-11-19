<?php

declare(strict_types=1);

namespace M2E\Otto\Controller\Adminhtml\Otto\Category;

class SaveCategoryDataAjax extends \M2E\Otto\Controller\Adminhtml\Otto\AbstractCategory
{
    private \M2E\Otto\Model\Category\AttributeFactory $attributeFactory;
    private \M2E\Otto\Model\Category\Repository $categoryRepository;
    private \M2E\Otto\Model\CategoryFactory $categoryFactory;
    private \Magento\Framework\App\ResourceConnection $resource;
    private \M2E\Otto\Model\Category\Attribute\Manager $attributeManager;

    public function __construct(
        \M2E\Otto\Model\Category\AttributeFactory $attributeFactory,
        \M2E\Otto\Model\Category\Repository $categoryRepository,
        \M2E\Otto\Model\CategoryFactory $categoryFactory,
        \Magento\Framework\App\ResourceConnection $resource,
        \M2E\Otto\Model\Category\Attribute\Manager $attributeManager
    ) {
        parent::__construct();

        $this->resource = $resource;
        $this->categoryFactory = $categoryFactory;
        $this->attributeFactory = $attributeFactory;
        $this->categoryRepository = $categoryRepository;
        $this->attributeManager = $attributeManager;
    }

    public function execute()
    {
        $post = $this->getRequest()->getPost()->toArray();

        if (empty($post['attributes'])) {
            throw new \M2E\Otto\Model\Exception\Logic('Invalid input');
        }

        try {
            $attributes = json_decode($post['attributes'], true);
            $attributes = array_merge(
                array_values($attributes['real_attributes'] ?? []),
                array_values($attributes['custom_attributes'] ?? [])
            );
            $categoryData = json_decode($post['category'], true);

            $isNewCategory = false;
            if (!empty($categoryData['categoryId'])) {
                $category = $this->categoryRepository->find((int)$categoryData['categoryId']);
            } else {
                $category = $this->categoryFactory->create()->create(
                    $categoryData['categoryGroupId'],
                    $categoryData['path'],
                    count($attributes),
                    $categoryData['has_required_attributes']
                );
                $isNewCategory = true;
            }

            $this->saveCategoryData($category, $attributes, $isNewCategory);
        } catch (\M2E\Otto\Model\Exception\Logic $e) {
            $this->setJsonContent(
                [
                    'success' => false,
                    'messages' => [
                        ['error' => 'Attributes not saved'],
                    ],
                ]
            );
        }

        $this->setJsonContent(
            [
                'success' => true,
                'category_id' => $category->getId()
            ]
        );

        return $this->getResult();
    }

    /**
     * @param int $categoryId
     * @param array $inputAttributes
     *
     * @return \M2E\Otto\Model\Category\Attribute[]
     */
    private function getAttributes(int $categoryId, array $inputAttributes): array
    {
        $attributes = [];
        foreach ($inputAttributes as $inputAttribute) {
            $recommendedValues = [];
            if (!empty($inputAttribute['value_otto_recommended'])) {
                $recommendedValues = $this->getRecommendedValues($inputAttribute['value_otto_recommended']);
            }
            $attributes[] = $this->attributeFactory->create()->create(
                $categoryId,
                $inputAttribute['attribute_type'],
                $inputAttribute['attribute_id'],
                $inputAttribute['attribute_name'],
                (int)$inputAttribute['value_mode'],
                $recommendedValues,
                $inputAttribute['value_custom_value'] ?? '',
                $inputAttribute['value_custom_attribute'] ?? ''
            );
        }

        return $attributes;
    }

    /**
     * @param array|string $inputValues
     *
     * @return string[]
     */
    private function getRecommendedValues($inputValues): array
    {
        if (is_string($inputValues)) {
            $inputValues = [$inputValues];
        }

        $values = [];
        foreach ($inputValues as $value) {
            if (!empty($value)) {
                $values[] = $value;
            }
        }

        return $values;
    }

    private function saveCategoryData($category, $attributes, $isNewCategory)
    {
        $transaction = $this->resource->getConnection()->beginTransaction();
        try {
            if ($isNewCategory) {
                $this->categoryRepository->save($category); //можно всегда сохранять
            }

            $allAttributes = $this->getAttributes($category->getId(), $attributes);
            $this->attributeManager->createOrUpdateAttributes($allAttributes, $category);
        } catch (\Throwable $exception) {
            $transaction->rollBack();
            throw $exception;
        }
        $transaction->commit();

        return $category;
    }
}
