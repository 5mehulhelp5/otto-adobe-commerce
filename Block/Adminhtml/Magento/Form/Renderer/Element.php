<?php

declare(strict_types=1);

namespace M2E\Otto\Block\Adminhtml\Magento\Form\Renderer;

use Magento\Backend\Block\Widget\Form\Renderer\Fieldset\Element as MagentoElement;
use Magento\Framework\Data\Form\Element\AbstractElement;

class Element extends MagentoElement
{
    protected function getTooltipHtml($content)
    {
        return <<<HTML
<div class="Otto-field-tooltip admin__field-tooltip">
    <a class="admin__field-tooltip-action" href="javascript://"></a>
    <div class="admin__field-tooltip-content">
        {$content}
    </div>
</div>
HTML;
    }

    public function render(AbstractElement $element)
    {
        $isRequired = $element->getData('required');

        if ($isRequired === true) {
            $element->removeClass('required-entry');
            $element->removeClass('_required');
            $element->setClass('Otto-required-when-visible ' . $element->getClass());
        }

        $tooltip = $element->getData('tooltip');

        if ($tooltip === null) {
            $element->addClass('Otto-field-without-tooltip');

            return parent::render($element);
        }

        $element->setAfterElementHtml(
            $element->getAfterElementHtml() . $this->getTooltipHtml($tooltip)
        );

        $element->addClass('Otto-field-with-tooltip');

        return parent::render($element);
    }

    /**
     * @param array|string $data
     * @param null $allowedTags
     *
     * @return array|string
     * @throws \M2E\Otto\Model\Exception\Logic
     * Starting from version 2.2.3 Magento forcibly escapes content of tooltips. But we are using HTML there
     */
    public function escapeHtml($data, $allowedTags = null)
    {
        return \M2E\Core\Helper\Data::escapeHtml(
            $data,
            ['div', 'a', 'strong', 'br', 'i', 'b', 'ul', 'li', 'p'],
            ENT_NOQUOTES
        );
    }
}
