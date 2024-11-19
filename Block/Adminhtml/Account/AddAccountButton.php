<?php

declare(strict_types=1);

namespace M2E\Otto\Block\Adminhtml\Account;

class AddAccountButton implements \Magento\Framework\View\Element\UiComponent\Control\ButtonProviderInterface
{
    private \Magento\Backend\Model\UrlInterface $urlBuilder;
    private \Magento\Framework\App\RequestInterface $request;

    public function __construct(
        \Magento\Backend\Model\UrlInterface $urlBuilder,
        \Magento\Framework\App\RequestInterface $request
    ) {
        $this->urlBuilder = $urlBuilder;
        $this->request = $request;
    }

    public function getButtonData()
    {
        $url = $this->getTargetUrl();
        $mode = $this->getMode();

        return [
            'label' => __('Add Account'),
            'class' => 'action-primary action-btn',
            'on_click' => '',
            'sort_order' => 4,
            'data_attribute' => [
                'mage-init' => [
                    'Otto/Account/AddButton' => [
                        'urlCreate' => $url,
                        'mode' => $mode
                    ],
                ],
            ],
        ];
    }

    private function getTargetUrl(): string
    {
        if ($this->request->getParam('install') !== null) {
            return $this->urlBuilder->getUrl('*/otto_account/beforeGetToken');
        }

        return $this->urlBuilder->getUrl('*/otto_account/beforeGetInstallationId');
    }

    private function getMode(): string
    {
        if ($this->request->getParam('mode') === \M2E\Otto\Model\Account::MODE_SANDBOX) {
            return \M2E\Otto\Model\Account::MODE_SANDBOX;
        }

        return \M2E\Otto\Model\Account::MODE_PRODUCTION;
    }
}
