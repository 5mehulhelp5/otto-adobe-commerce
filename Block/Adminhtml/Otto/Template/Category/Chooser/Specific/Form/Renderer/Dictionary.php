<?php

namespace M2E\Otto\Block\Adminhtml\Otto\Template\Category\Chooser\Specific\Form\Renderer;

use Magento\Backend\Block\Widget\Form\Renderer\Fieldset\Element as MagentoElement;
use Magento\Framework\Data\Form\Element\AbstractElement;

class Dictionary extends MagentoElement
{
    protected AbstractElement $element;

    public function __construct(
        \M2E\Otto\Block\Adminhtml\Magento\Context\Template $context,
        array $data = []
    ) {
        parent::__construct($context, $data);

        $this->setTemplate('otto/template/category/chooser/specific/form/renderer/dictionary.phtml');
    }

    public function getElement()
    {
        return $this->element;
    }

    public function render(AbstractElement $element)
    {
        $this->element = $element;

        return $this->toHtml();
    }
}
