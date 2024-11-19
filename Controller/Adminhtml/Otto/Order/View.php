<?php

namespace M2E\Otto\Controller\Adminhtml\Otto\Order;

class View extends \M2E\Otto\Controller\Adminhtml\Otto\AbstractOrder
{
    private \M2E\Otto\Helper\Data\GlobalData $globalData;
    private \M2E\Otto\Model\Order\Repository $repository;

    public function __construct(
        \M2E\Otto\Helper\Data\GlobalData $globalData,
        \M2E\Otto\Model\Order\Repository $repository
    ) {
        parent::__construct();

        $this->globalData = $globalData;
        $this->repository = $repository;
    }

    public function execute()
    {
        $id = $this->getRequest()->getParam('id');
        $order = $this->repository->get((int)$id);

        $this->globalData->setValue('order', $order);

        $this->addContent(
            $this->getLayout()->createBlock(
                \M2E\Otto\Block\Adminhtml\Otto\Order\View::class
            )
        );

        $this->init();
        $this->getResultPage()->getConfig()->getTitle()->prepend(__('View Order Details'));

        return $this->getResult();
    }
}
