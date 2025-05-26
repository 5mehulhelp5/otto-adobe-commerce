<?php

declare(strict_types=1);

namespace M2E\Otto\Controller\Adminhtml\Otto\Template;

use M2E\Otto\Controller\Adminhtml\Otto\AbstractTemplate;

class Index extends AbstractTemplate
{
    private \M2E\Otto\Model\Template\Shipping\ShippingService $shippingService;

    public function __construct(
        \M2E\Otto\Model\Template\Shipping\ShippingService $shippingService,
        \M2E\Otto\Model\Otto\Template\Manager $templateManager
    ) {
        parent::__construct($templateManager);

        $this->shippingService = $shippingService;
    }

    public function execute()
    {
        try {
            $this->shippingService->sync();
        } catch (\M2E\Otto\Model\Exception\AccountMissingPermissions $e) {
            $url = $this->getUrl('*/otto_account/edit', ['id' => $e->getAccount()->getId()]);
            $this->addExtendedErrorMessage(
                __(
                    'You are not authorized to access Shipping Profiles. ' .
                    'Please use the "Update Access Data" button in your <a href="%url">%channel_title Seller Account</a> to proceed.',
                    [
                        'url' => $url,
                        'channel_title' => \M2E\Otto\Helper\Module::getChannelTitle()
                    ]
                )
            );
        }

        $content = $this->getLayout()->createBlock(\M2E\Otto\Block\Adminhtml\Otto\Template::class);

        $this->getResult()->getConfig()->getTitle()->prepend('Policies');
        $this->addContent($content);

        return $this->getResult();
    }
}
