<?php

declare(strict_types=1);

namespace M2E\Otto\Controller\Adminhtml\Product\Unmanaged\Moving;

class GetSelectedProducts extends \M2E\Otto\Controller\Adminhtml\AbstractListing
{
    private \Magento\Framework\Controller\Result\JsonFactory $resultJsonFactory;
    private \Magento\Ui\Component\MassAction\Filter $massActionFilter;
    private \M2E\Otto\Model\Listing\Other\Repository $otherRepository;

    public function __construct(
        \Magento\Ui\Component\MassAction\Filter $massActionFilter,
        \M2E\Otto\Model\Listing\Other\Repository $otherRepository,
        \Magento\Framework\Controller\Result\JsonFactory $resultJsonFactory,
        \Magento\Backend\App\Action\Context $context
    ) {
        parent::__construct($context);
        $this->resultJsonFactory = $resultJsonFactory;
        $this->massActionFilter = $massActionFilter;
        $this->otherRepository = $otherRepository;
    }

    public function execute()
    {
        $accountId = (int)$this->getRequest()->getParam('account_id');
        $products = $this->otherRepository->findForMovingByMassActionSelectedProducts(
            $this->massActionFilter,
            $accountId
        );
        $ids = [];
        foreach ($products as $product) {
            $ids[] = (int)$product->getId();
        }

        $response = [
            'selected_products' => $ids,
        ];

        if (empty($ids)) {
            $response['message'] = \__('Only Linked Products must be selected.');
        }

        return $this->resultJsonFactory->create()
                                       ->setData($response);
    }
}
