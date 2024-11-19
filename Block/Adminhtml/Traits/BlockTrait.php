<?php

namespace M2E\Otto\Block\Adminhtml\Traits;

trait BlockTrait
{
    public function __(...$args): string
    {
        return (string)__(...$args);
    }

    public function getTooltipHtml($content, $directionToRight = false, array $customClasses = [])
    {
        $directionToRightClass = $directionToRight ? 'Otto-field-tooltip-right' : '';

        $customClasses = !empty($customClasses) ? implode(' ', $customClasses) : '';

        return <<<HTML
<div class="Otto-field-tooltip admin__field-tooltip {$directionToRightClass} {$customClasses}">
    <a class="admin__field-tooltip-action" href="javascript://"></a>
    <div class="admin__field-tooltip-content">
        {$content}
    </div>
</div>
HTML;
    }

    public function appendHelpBlock($data)
    {
        return $this->getLayout()->addBlock(\M2E\Otto\Block\Adminhtml\HelpBlock::class, '', 'main.top')
                    ->setData(
                        $data,
                    );
    }

    /**
     * @param string $block
     * @param string $name
     *
     * @return \Magento\Framework\View\Element\BlockInterface
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function setPageActionsBlock(string $block, string $name = '')
    {
        return $this->getLayout()->addBlock($block, $name, 'page.main.actions');
    }
}
