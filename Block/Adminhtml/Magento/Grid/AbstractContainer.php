<?php

declare(strict_types=1);

namespace M2E\Otto\Block\Adminhtml\Magento\Grid;

use Magento\Backend\Block\Widget\Grid\Container;
use M2E\Otto\Block\Adminhtml\Traits;

abstract class AbstractContainer extends Container
{
    use Traits\BlockTrait;
    use Traits\RendererTrait;

    public function __construct(
        \M2E\Otto\Block\Adminhtml\Magento\Context\Widget $context,
        array $data = []
    ) {
        $this->css = $context->getCss();
        $this->jsPhp = $context->getJsPhp();
        $this->js = $context->getJs();
        $this->jsTranslator = $context->getJsTranslator();
        $this->jsUrl = $context->getJsUrl();

        parent::__construct($context, $data);
    }

    protected function _construct()
    {
        parent::_construct();
        $this->_blockGroup = 'M2E_Otto';
    }

    protected function addGridBlock(string $gridClassName): self
    {
        if (!is_a($gridClassName, \M2E\Otto\Block\Adminhtml\Magento\Grid\AbstractGrid::class, true)) {
            throw new \M2E\Otto\Model\Exception\Logic(
                sprintf(
                    'Grid %s must implement %s',
                    $gridClassName,
                    \M2E\Otto\Block\Adminhtml\Magento\Grid\AbstractGrid::class,
                ),
            );
        }

        $this->addChild('grid', $gridClassName);

        /** @var \M2E\Otto\Block\Adminhtml\Magento\Grid\AbstractGrid $grid */
        $grid = $this->getChildBlock('grid');
        $grid->setSaveParametersInSession(true);

        return $this;
    }
}
