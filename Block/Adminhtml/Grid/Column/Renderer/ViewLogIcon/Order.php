<?php

declare(strict_types=1);

namespace M2E\Otto\Block\Adminhtml\Grid\Column\Renderer\ViewLogIcon;

use M2E\Otto\Block\Adminhtml\Traits;

/**
 * Class  \M2E\Otto\Block\Adminhtml\Grid\Column\Renderer\ViewLogIcon\Order
 */
class Order extends \Magento\Backend\Block\Widget\Grid\Column\Renderer\Text
{
    use Traits\BlockTrait;

    protected \M2E\Otto\Helper\Factory $helperFactory;

    protected \M2E\Otto\Model\ActiveRecord\Factory $activeRecordFactory;

    //########################################
    private \M2E\Otto\Model\ResourceModel\Order\Log\CollectionFactory $orderLogCollectionFactory;

    public function __construct(
        \M2E\Otto\Model\ResourceModel\Order\Log\CollectionFactory $orderLogCollectionFactory,
        \M2E\Otto\Helper\Factory $helperFactory,
        \M2E\Otto\Model\ActiveRecord\Factory $activeRecordFactory,
        \Magento\Backend\Block\Context $context,
        array $data = []
    ) {
        parent::__construct($context, $data);

        $this->helperFactory = $helperFactory;
        $this->activeRecordFactory = $activeRecordFactory;
        $this->orderLogCollectionFactory = $orderLogCollectionFactory;
    }

    //########################################

    public function render(\Magento\Framework\DataObject $row)
    {
        $orderId = (int)$row->getId();

        // Prepare collection
        // ---------------------------------------
        $orderLogsCollection = $this->orderLogCollectionFactory->create();
        $orderLogsCollection->addFieldToFilter('order_id', $orderId);
        $orderLogsCollection->setOrder('id', 'DESC');
        $orderLogsCollection->getSelect()
                            ->limit(\M2E\Otto\Block\Adminhtml\Log\Grid\LastActions::ACTIONS_COUNT);

        if ($orderLogsCollection->getSize() === 0) {
            return '';
        }

        // ---------------------------------------

        $summary = $this->getLayout()->createBlock(\M2E\Otto\Block\Adminhtml\Order\Log\Grid\LastActions::class)
                        ->setData([
                            'entity_id' => $orderId,
                            'logs' => $orderLogsCollection->getItems(),
                            'view_help_handler' => 'OrderObj.viewOrderHelp',
                            'hide_help_handler' => 'OrderObj.hideOrderHelp',
                        ]);

        return $summary->toHtml();
    }

    //########################################
}
