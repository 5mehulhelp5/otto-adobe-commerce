<?php

namespace M2E\Otto\Block\Adminhtml\Otto\Template\Category\View;

class Info extends \M2E\Otto\Block\Adminhtml\Widget\Info
{
    private \M2E\Otto\Model\Category $category;

    public function __construct(
        \M2E\Otto\Model\Category $category,
        \Magento\Framework\Math\Random $random,
        \M2E\Otto\Block\Adminhtml\Magento\Context\Template $context,
        array $data = []
    ) {
        parent::__construct($random, $context, $data);

        $this->category = $category;
    }

    protected function _prepareLayout()
    {
        $this->setInfo(
            [
                [
                    'label' => __('Category'),
                    'value' => $this->category->getTitle(),
                ],
            ]
        );

        return parent::_prepareLayout();
    }

    /*
     * To get "Category" block in center of screen
     */
    public function getInfoPartWidth($index)
    {
        if ($index === 0) {
            return '33%';
        }

        return '66%';
    }

    public function getInfoPartAlign($index)
    {
        return 'left';
    }
}
