<?php

namespace M2E\Otto\Block\Adminhtml\Otto\Template\Category;

class ViewFactory
{
    public function create(
        \Magento\Framework\View\LayoutInterface $layout,
        \M2E\Otto\Model\Category $category
    ): View {
        /** @var View $block */
        $block = $layout->createBlock(
            View::class,
            '',
            ['category' => $category]
        );

        return $block;
    }
}
