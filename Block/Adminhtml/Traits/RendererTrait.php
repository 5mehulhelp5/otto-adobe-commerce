<?php

namespace M2E\Otto\Block\Adminhtml\Traits;

trait RendererTrait
{
    public \M2E\Otto\Block\Adminhtml\Magento\Renderer\JsPhpRenderer $jsPhp;

    public \M2E\Otto\Block\Adminhtml\Magento\Renderer\JsTranslatorRenderer $jsTranslator;

    public \M2E\Otto\Block\Adminhtml\Magento\Renderer\JsUrlRenderer $jsUrl;

    public \M2E\Otto\Block\Adminhtml\Magento\Renderer\JsRenderer $js;

    public \M2E\Otto\Block\Adminhtml\Magento\Renderer\CssRenderer $css;
}
