<?php

declare(strict_types=1);

namespace M2E\Otto\Controller\Adminhtml\Otto\Category;

class GetCategoryAttributesHtml extends \M2E\Otto\Controller\Adminhtml\Otto\AbstractCategory
{
    private \M2E\Otto\Model\Category\Attribute\AttributeService $attributeService;

    public function __construct(
        \M2E\Otto\Model\Category\Attribute\AttributeService $attributeService
    ) {
        parent::__construct();

        $this->attributeService = $attributeService;
    }

    public function execute()
    {
        $categoryGroupId = $this->getRequest()->getParam('category_group_id');
        $categoryId = (int)$this->getRequest()->getParam('category_id');
        $title = $this->getRequest()->getParam('title');

        if (empty($categoryGroupId)) {
            throw new \M2E\Otto\Model\Exception\Logic('Invalid input');
        }

        $attributes = $this->attributeService->getProductAttributes($categoryGroupId, $categoryId);
        $customAttributes = $this->attributeService->getCustomAttributes($categoryId);

        /** @var \M2E\Otto\Block\Adminhtml\Otto\Template\Category\Chooser\Specific\Edit $attributes */
        $attributes = $this->getLayout()->createBlock(
            \M2E\Otto\Block\Adminhtml\Otto\Template\Category\Chooser\Specific\Edit::class,
            '',
            [
                'attributes' => $attributes,
                'customAttributes' => $customAttributes,
                'title' => $title
            ]
        );

        $attributes->prepareFormData();
        $this->setAjaxContent($attributes->toHtml());

        return $this->getResult();
    }
}
