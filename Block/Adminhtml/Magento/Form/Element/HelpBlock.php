<?php

declare(strict_types=1);

namespace M2E\Otto\Block\Adminhtml\Magento\Form\Element;

use Magento\Framework\Data\Form\Element\AbstractElement;
use Magento\Framework\Data\Form\Element\CollectionFactory;
use Magento\Framework\Data\Form\Element\Factory;
use Magento\Framework\Escaper;

/**
 * Class \M2E\Otto\Block\Adminhtml\Magento\Form\Element\HelpBlock
 */
class HelpBlock extends AbstractElement
{
    protected $layout;

    public function __construct(
        \Magento\Framework\View\LayoutInterface $layout,
        Factory $factoryElement,
        CollectionFactory $factoryCollection,
        Escaper $escaper,
        array $data = []
    ) {
        $this->layout = $layout;

        $this->setType('hidden');
        parent::__construct($factoryElement, $factoryCollection, $escaper, $data);
    }

    public function getElementHtml()
    {
        return $this->layout->createBlock(\M2E\Otto\Block\Adminhtml\HelpBlock::class)->addData([
            'id' => $this->getId(),
            'title' => $this->getData('title'),
            'content' => $this->getData('content'),
            'class' => $this->getClass(),
            'tooltiped' => $this->getData('tooltiped'),
            'no_hide' => $this->getData('no_hide'),
            'no_collapse' => $this->getData('no_collapse'),
            'style' => $this->getData('style'),
        ])->toHtml();
    }
}
