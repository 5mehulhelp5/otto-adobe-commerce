<?php

namespace M2E\Otto\Plugin\Order\Magento\Quote\Model\Quote\Item;

class ToOrderItem extends \M2E\Otto\Plugin\AbstractPlugin
{
    protected \Magento\Framework\Event\ManagerInterface $eventManager;

    public function __construct(
        \Magento\Framework\Event\ManagerInterface $eventManager
    ) {
        $this->eventManager = $eventManager;
    }

    public function aroundConvert($interceptor, \Closure $callback, ...$arguments)
    {
        return $this->execute('convert', $interceptor, $callback, $arguments);
    }

    protected function processConvert($interceptor, \Closure $callback, array $arguments)
    {
        $orderItem = $callback(...$arguments);
        $quoteItem = isset($arguments[0]) ? $arguments[0] : null;

        if (!($quoteItem instanceof \Magento\Quote\Model\Quote\Item)) {
            return $orderItem;
        }

        $this->eventManager->dispatch(
            'm2e_otto_sales_convert_quote_item_to_order_item',
            [
                'order_item' => $orderItem,
                'item' => $quoteItem,
            ]
        );

        return $orderItem;
    }
}
