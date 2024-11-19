<?php

declare(strict_types=1);

namespace M2E\Otto\Block\Adminhtml\Category;

class CategoryChooser extends \M2E\Otto\Block\Adminhtml\Magento\AbstractBlock
{
    protected $_template = 'category/category_chooser.phtml';

    private ?int $selectedCategory;

    public function __construct(
        \M2E\Otto\Block\Adminhtml\Magento\Context\Template $context,
        ?int $selectedCategory,
        array $data = []
    ) {
        parent::__construct($context, $data);
        $this->selectedCategory = $selectedCategory;
    }

    public function getSelectedCategory(): ?int
    {
        return $this->selectedCategory;
    }
}
