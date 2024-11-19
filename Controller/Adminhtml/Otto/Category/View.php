<?php

namespace M2E\Otto\Controller\Adminhtml\Otto\Category;

class View extends \M2E\Otto\Controller\Adminhtml\Otto\AbstractCategory
{
    private \M2E\Otto\Model\Category\Repository $categoryRepository;
    private \M2E\Otto\Block\Adminhtml\Otto\Template\Category\ViewFactory $viewFactory;

    public function __construct(
        \M2E\Otto\Block\Adminhtml\Otto\Template\Category\ViewFactory $viewFactory,
        \M2E\Otto\Model\Category\Repository $categoryRepository
    ) {
        parent::__construct();

        $this->viewFactory = $viewFactory;
        $this->categoryRepository = $categoryRepository;
    }

    public function execute()
    {
        /**
         * tabs widget makes an redundant ajax call for tab content by clicking on it even when tab is just a link
         */
        if ($this->isAjax()) {
            return;
        }

        $categoryId = $this->getRequest()->getParam('category_id');
        $category = $this->categoryRepository->find((int)$categoryId);

        if ($category === null) {
            throw new \M2E\Otto\Model\Exception\Logic('Category not found');
        }

        $block = $this->viewFactory->create($this->getLayout(), $category);
        $this->addContent($block);
        $this->getResultPage()->getConfig()->getTitle()->prepend(__('Edit Category'));

        return $this->getResult();
    }
}
