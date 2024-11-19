<?php

declare(strict_types=1);

namespace M2E\Otto\Block\Adminhtml\Magento\Tabs;

use Magento\Backend\Block\Widget\Tabs;
use M2E\Otto\Block\Adminhtml\Traits;

abstract class AbstractTabs extends Tabs
{
    use Traits\BlockTrait;
    use Traits\RendererTrait;

    protected $_template = 'M2E_Otto::magento/tabs/default.phtml';

    public function __construct(
        \M2E\Otto\Block\Adminhtml\Magento\Context\Template $context,
        \Magento\Framework\Json\EncoderInterface $jsonEncoder,
        \Magento\Backend\Model\Auth\Session $authSession,
        array $data = []
    ) {
        $this->css = $context->getCss();
        $this->jsPhp = $context->getJsPhp();
        $this->js = $context->getJs();
        $this->jsTranslator = $context->getJsTranslator();
        $this->jsUrl = $context->getJsUrl();

        parent::__construct($context, $jsonEncoder, $authSession, $data);
    }
}
