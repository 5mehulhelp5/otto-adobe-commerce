<?php

declare(strict_types=1);

namespace M2E\Otto\Controller\Adminhtml\Otto\Category;

class SaveCategoryData extends \M2E\Otto\Controller\Adminhtml\Otto\AbstractCategory
{
    private \M2E\Otto\Model\Category\AttributeFactory $attributeFactory;
    private \M2E\Otto\Model\Category\Repository $categoryRepository;
    private \M2E\Otto\Model\Category\Attribute\Manager $attributeManager;

    public function __construct(
        \M2E\Otto\Model\Category\AttributeFactory $attributeFactory,
        \M2E\Otto\Model\Category\Repository $categoryRepository,
        \M2E\Otto\Model\Category\Attribute\Manager $attributeManager
    ) {
        parent::__construct();

        $this->attributeFactory = $attributeFactory;
        $this->categoryRepository = $categoryRepository;
        $this->attributeManager = $attributeManager;
    }

    public function execute()
    {
        $post = $this->getRequest()->getPost()->toArray();

        if (empty($post['category_id'])) {
            $this->getMessageManager()->addError(__('Category not found.'));

            return $this->_redirect('*/*/index');
        }

        $category = $this->categoryRepository->find((int)$post['category_id']);
        $allAttributes = array_merge(
            array_values($post['real_attributes'] ?? []),
            array_values($post['custom_attributes'] ?? [])
        );

        $attributes = $this->getAttributes($category->getId(), $allAttributes);
        $this->attributeManager->createOrUpdateAttributes($attributes, $category);

        $this->messageManager->addSuccess(__('Category data was saved.'));

        if ($this->getRequest()->getParam('back') === 'edit') {
            return $this->_redirect('*/*/view', ['category_id' => $post['category_id']]);
        }

        if ($this->getRequest()->getParam('back') === 'categories_grid') {
            return $this->_redirect('*/otto_template_category/index');
        }

        return $this->_redirect('*/*/index');
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
}
