<?php

namespace M2E\Otto\Model\Otto\Order\Item;

class Builder extends \Magento\Framework\DataObject
{
    private \M2E\Otto\Model\ResourceModel\Order\Item\CollectionFactory $orderItemCollectionFactory;
    private \M2E\Otto\Model\Otto\Order\StatusResolver $statusResolver;

    public function __construct(
        \M2E\Otto\Model\Otto\Order\StatusResolver $statusResolver,
        \M2E\Otto\Model\ResourceModel\Order\Item\CollectionFactory $orderItemCollectionFactory
    ) {
        parent::__construct();
        $this->statusResolver = $statusResolver;
        $this->orderItemCollectionFactory = $orderItemCollectionFactory;
    }

    /**
     * @throws \M2E\Otto\Model\Exception\Logic
     */
    public function initialize(array $data): void
    {
        // Base
        $this->setData('order_id', $data['order_id']);
        $this->setData('otto_item_id', $data['position_item_id']);
        $this->setData('otto_product_sku', $data['product']['sku']);

        $this->setData('article_number', $data['product']['article_number']);
        $this->setData('title', $data['product']['title']);
        $this->setData('ean', $data['product']['ean']);
        $this->setData('status', $this->statusResolver->convertOttoOrderStatus($data['fulfillment_status']));

        // Price
        $originalPrice = (float)$data['gross_price']['amount'];
        if (isset($data['gross_reduced_price']) && $data['gross_reduced_price']['amount'] !== null) {
            $salePrice = (float)$data['gross_reduced_price']['amount'];
        } else {
            $salePrice = $originalPrice;
        }
        $this->setData('sale_price', $salePrice);
        $this->setData('original_price', $originalPrice);
        $this->setData('platform_discount', $data['discount'] ? (float)$data['discount']['amount'] : 0.0);

        // Taxes
        $taxRate = $data['product']['vat_rate'] / 100;
        $tax = $salePrice * (1 - 1 / (1 + $taxRate));
        $taxDetails = [
            'rate' => $data['product']['vat_rate'],
            'amount' => $tax,
        ];

        $this->setData('tax_details', \M2E\Core\Helper\Json::encode($taxDetails));

        // QTY always one
        $this->setData('qty_purchased', 1);

        // Tracking details
        $trackingDetails = [
            'shipping_carrier' => $data['tracking_info']['carrier'] ?? '',
            'shipping_carrier_service_code' => $data['tracking_info']['carrier_service_code'] ?? '',
            'tracking_number' => $data['tracking_info']['tracking_number'] ?? '',
            'ship_date' => $data['sentDate'] ?? '',
        ];

        $this->setData('tracking_details', \M2E\Core\Helper\Json::encode($trackingDetails));
    }

    public function process(): \M2E\Otto\Model\Order\Item
    {
        $collection = $this->orderItemCollectionFactory->create();
        $collection->addFieldToFilter('order_id', $this->getData('order_id')); //salesOrderId
        $collection->addFieldToFilter('otto_item_id', $this->getData('otto_item_id'));

        /** @var \M2E\Otto\Model\Order\Item $item */
        $item = $collection->getFirstItem();

        foreach ($this->getData() as $key => $value) {
            if (
                $item->isObjectNew()
                || ($item->hasData($key) && $item->getData($key) != $value)
            ) {
                $item->addData($this->getData());

                $item->save();

                break;
            }
        }

        return $item;
    }
}
