<?php

namespace M2E\Otto\Controller\Adminhtml\Order;

class ViewLogGrid extends \M2E\Otto\Controller\Adminhtml\AbstractOrder
{
    /** @var \M2E\Otto\Helper\Data\GlobalData */
    private $globalData;
    private \M2E\Otto\Model\Order\Repository $orderRepository;

    public function __construct(
        \M2E\Otto\Model\Order\Repository $orderRepository,
        \M2E\Otto\Helper\Data\GlobalData $globalData,
        \M2E\Otto\Controller\Adminhtml\Context $context
    ) {
        parent::__construct($context);

        $this->globalData = $globalData;
        $this->orderRepository = $orderRepository;
    }

    public function execute()
    {
        $id = $this->getRequest()->getParam('id');
        $order = $this->orderRepository->get($id);

        $this->globalData->setValue('order', $order);
        $grid = $this->getLayout()->createBlock(\M2E\Otto\Block\Adminhtml\Order\View\Log\Grid::class);

        $this->setAjaxContent($grid->toHtml());

        return $this->getResult();
    }
}
