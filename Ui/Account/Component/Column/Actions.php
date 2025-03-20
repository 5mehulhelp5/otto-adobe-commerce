<?php

declare(strict_types=1);

namespace M2E\Otto\Ui\Account\Component\Column;

use Magento\Framework\View\Element\UiComponent\ContextInterface;
use Magento\Framework\View\Element\UiComponentFactory;
use Magento\Framework\UrlInterface;

class Actions extends \Magento\Ui\Component\Listing\Columns\Column
{
    private const PATH_EDIT = 'm2e_otto/otto_account/edit';
    private const PATH_DELETE = 'm2e_otto/otto_account/delete';

    private $urlBuilder;

    public function __construct(
        ContextInterface $context,
        UiComponentFactory $uiComponentFactory,
        UrlInterface $urlBuilder,
        array $components = [],
        array $data = []
    ) {
        parent::__construct($context, $uiComponentFactory, $components, $data);

        $this->urlBuilder = $urlBuilder;
    }

    public function prepareDataSource(array $dataSource)
    {
        if (isset($dataSource['data']['items'])) {
            foreach ($dataSource['data']['items'] as & $item) {
                $name = $this->getData('name');

                $item[$name]['edit'] = [
                    'href' => $this->urlBuilder->getUrl(self::PATH_EDIT, ['id' => $item['id']]),
                    'label' => __('Edit')
                ];

                $item[$name]['delete'] = [
                    'href' => $this->urlBuilder->getUrl(self::PATH_DELETE, ['id' => $item['id']]),
                    'label' => __('Delete'),
                    'confirm' => [
                        'title' => __('Confirmation'),
                        'message' =>             __(
                            '<p>You are about to delete your Otto seller account from M2E Otto. This will remove the
                    account-related Listings and Products from the extension and disconnect the synchronization.
                    Your listings on the channel will <b>not</b> be affected.</p>
                    <p>Please confirm if you would like to delete the account.</p>
                    <p>Note: once the account is no longer connected to your M2E Otto, please remember to delete it from
                    <a href="%href">M2E Accounts</a></p>',
                            ['href' => \M2E\Core\Helper\Module\Support::ACCOUNTS_URL]
                        )
                    ]
                ];
            }
        }
        return $dataSource;
    }
}
