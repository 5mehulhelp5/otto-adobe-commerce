<?php

namespace M2E\Otto\Block\Adminhtml\Magento\Renderer;

/**
 * @deprecated
 */
abstract class AbstractRenderer
{
    protected $helperFactory;

    //########################################

    public function __construct(
        \M2E\Otto\Helper\Factory $helperFactory
    ) {
        $this->helperFactory = $helperFactory;
    }

    //########################################

    abstract public function render();

    //########################################
}
