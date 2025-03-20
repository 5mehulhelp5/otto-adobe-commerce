<?php

namespace M2E\Otto\Block\Adminhtml\Otto\Order\View;

use M2E\Otto\Block\Adminhtml\Magento\Grid\AbstractGrid;

class Item extends AbstractGrid
{
    protected \Magento\Catalog\Model\Product $productModel;
    protected \Magento\Tax\Model\Calculation $taxCalculator;
    private \M2E\Otto\Model\Order $order;
    private \M2E\Otto\Model\Currency $currency;
    private \M2E\Otto\Model\Magento\ProductFactory $ourMagentoProductFactory;
    private \M2E\Otto\Model\Order\Item\Repository $orderItemRepository;

    public function __construct(
        \M2E\Otto\Model\Order\Item\Repository $orderItemRepository,
        \M2E\Otto\Model\Magento\ProductFactory $ourMagentoProductFactory,
        \M2E\Otto\Model\Currency $currency,
        \Magento\Catalog\Model\Product $productModel,
        \Magento\Tax\Model\Calculation $taxCalculator,
        \M2E\Otto\Block\Adminhtml\Magento\Context\Template $context,
        \Magento\Backend\Helper\Data $backendHelper,
        \M2E\Otto\Model\Order $order,
        array $data = []
    ) {
        $this->productModel = $productModel;
        $this->taxCalculator = $taxCalculator;
        $this->order = $order;
        $this->currency = $currency;
        $this->ourMagentoProductFactory = $ourMagentoProductFactory;
        $this->orderItemRepository = $orderItemRepository;
        parent::__construct($context, $backendHelper, $data);
    }

    public function _construct()
    {
        parent::_construct();

        // Initialization block
        // ---------------------------------------
        $this->setId('ottoOrderViewItem');
        // ---------------------------------------

        // Set default values
        // ---------------------------------------
        $this->setDefaultSort('id');
        $this->setDefaultDir('DESC');
        $this->setPagerVisibility(false);
        $this->setFilterVisibility(false);
        $this->setUseAjax(true);
        $this->_defaultLimit = 200;
        // ---------------------------------------
    }

    protected function _prepareCollection()
    {
        $collection = $this->orderItemRepository->getGroupOrderItemsCollection($this->order->getId());
        $this->setCollection($collection);

        return parent::_prepareCollection();
    }

    protected function _prepareColumns()
    {
        $this->addColumn('products', [
            'header' => __('Product'),
            'align' => 'left',
            'width' => '*',
            'index' => 'product_id',
            'frame_callback' => [$this, 'callbackColumnProduct'],
        ]);

        $this->addColumn('stock_availability', [
            'header' => __('Stock Availability'),
            'width' => '100px',
            'filter' => false,
            'sortable' => false,
            'frame_callback' => [$this, 'callbackColumnIsInStock'],
        ]);

        $this->addColumn('original_price', [
            'header' => __('Original Price'),
            'align' => 'left',
            'width' => '80px',
            'filter' => false,
            'sortable' => false,
            'frame_callback' => [$this, 'callbackColumnOriginalPrice'],
        ]);

        $this->addColumn('discounts', [
            'header' => __('Discounts'),
            'align' => 'left',
            'width' => '80px',
            'filter' => false,
            'sortable' => false,
            'frame_callback' => [$this, 'callbackColumnDiscounts'],
        ]);

        $this->addColumn('sale_price', [
            'header' => __('Price'),
            'align' => 'left',
            'width' => '80px',
            'index' => 'sale_price',
            'filter' => false,
            'sortable' => false,
            'frame_callback' => [$this, 'callbackColumnPrice'],
        ]);

        $this->addColumn('qty_sold', [
            'header' => __('QTY'),
            'align' => 'left',
            'width' => '80px',
            'index' => 'qty_purchased',
            'filter' => false,
            'sortable' => false,
            'frame_callback' => [$this, 'callbackColumnQty'],
        ]);

        $this->addColumn('tax_percent', [
            'header' => __('Tax Percent'),
            'align' => 'left',
            'width' => '80px',
            'filter' => false,
            'sortable' => false,
            'frame_callback' => [$this, 'callbackColumnTaxPercent'],
        ]);

        $this->addColumn('row_total', [
            'header' => __('Row Total'),
            'align' => 'left',
            'width' => '80px',
            'index' => 'sale_price',
            'filter' => false,
            'sortable' => false,
            'frame_callback' => [$this, 'callbackColumnRowTotal'],
        ]);

        return parent::_prepareColumns();
    }

    /**
     * @param string $value
     * @param \M2E\Otto\Model\Order\Item $row
     * @param \Magento\Backend\Block\Widget\Grid\Column\Extended $column
     * @param bool $isExport
     *
     * @return string
     * @throws \M2E\Otto\Model\Exception\Logic
     */
    public function callbackColumnProduct($value, \M2E\Otto\Model\Order\Item $row, $column, $isExport)
    {
        $productLink = '';
        if ($row->isMappedWithMagentoProduct()) {
            $productUrl = $this->getUrl('catalog/product/edit', [
                'id' => $row->getMagentoProductId(),
                'store' => $row->getOrder()->getStoreId(),
            ]);
            $productLink = sprintf(
                '<a href="%s" target="_blank">%s</a>',
                $productUrl,
                __('View')
            );
        }

        $editLink = '';
        if (!$row->isMappedWithMagentoProduct()) {
            $onclick = "OrderEditItemObj.edit('{$this->getId()}', '{$row->getOrderItemsIds()}')";
            $editLink = sprintf(
                '<a class="gray" href="javascript:void(0);" onclick="%s">%s</a>',
                $onclick,
                __('Link to Magento Product')
            );
        }

        if (
            $row->isMappedWithMagentoProduct()
            && $row->getMagentoProduct()->isProductWithVariations()
        ) {
            $onclick = "OrderEditItemObj.edit('{$this->getId()}', '{$row->getOrderItemsIds()}',)";
            $editLink = sprintf(
                '<a class="gray" href="javascript:void(0);" onclick="%s">%s</a>',
                $onclick,
                __('Set Options')
            ) . '&nbsp;|&nbsp;';
        }

        $discardLink = '';
        if ($row->isMappedWithMagentoProduct()) {
            $onclick = "OrderEditItemObj.unassignProduct('{$this->getId()}', '{$row->getOrderItemsIds()}')";
            $discardLink = sprintf(
                '<a class="gray" href="javascript:void(0);" onclick="%s">%s</a>',
                $onclick,
                __('Unlink')
            );
        }

        $titleLine = sprintf(
            '<p><strong>%s</strong></p>',
            \M2E\Core\Helper\Data::escapeHtml($row->getTitle())
        );
        $skuLine = sprintf(
            '<p><strong>%s:</strong> %s</p>',
            __('SKU'),
            \M2E\Core\Helper\Data::escapeHtml($row->getSku())
        );
        $actionLine = sprintf(
            '<div style="float: left;">%s</div><div style="float: right;">%s%s</div>',
            $productLink,
            $editLink,
            $discardLink
        );

        if ($row->isStatusCancelled()) {
            $actionLine = sprintf(
                '<div><span class="canceled-order-item">%s</span></div>',
                __('Item was Canceled')
            );
        }

        if ($row->isStatusReturned()) {
            $actionLine = sprintf(
                '<div><span class="canceled-order-item">%s</span></div>',
                __('Item was Returned')
            );
        }

        return $titleLine . $skuLine . $actionLine;
    }

    public function callbackColumnIsInStock($value, \M2E\Otto\Model\Order\Item $row, $column, $isExport)
    {
        if (!$row->isMagentoProductExists()) {
            return '<span style="color: red;">' . __('Product Not Found') . '</span>';
        }

        if ($row->getMagentoProduct() === null) {
            return __('N/A');
        }

        if (!$row->getMagentoProduct()->isStockAvailability()) {
            return '<span style="color: red;">' . __('Out Of Stock') . '</span>';
        }

        return __('In Stock');
    }

    public function callbackColumnOriginalPrice($value, \M2E\Otto\Model\Order\Item $row, $column, $isExport)
    {
        $formattedPrice = __('N/A');

        $product = $row->getProduct();

        if ($product) {
            $magentoProduct = $this->ourMagentoProductFactory->create();
            $magentoProduct->setProduct($product);

            if ($magentoProduct->isGroupedType()) {
                $associatedProducts = $row->getAssociatedProducts();
                $price = $this->productModel
                    ->load(array_shift($associatedProducts))
                    ->getPrice();

                $formattedPrice = $this->order->getStore()->getCurrentCurrency()->format($price);
            } else {
                $formattedPrice = $this->order->getStore()
                                              ->getCurrentCurrency()
                                              ->format($row->getProduct()->getPrice());
            }
        }

        return $formattedPrice;
    }

    public function callbackColumnPrice($value, \M2E\Otto\Model\Order\Item $row, $column, $isExport)
    {
        return $this->currency->formatPrice(
            $this->order->getCurrency(),
            (float)$value
        );
    }

    public function callbackColumnQty($value, \M2E\Otto\Model\Order\Item $row, $column, $isExport)
    {
        return $row->getData('total_qty');
    }

    public function callbackColumnRowTotal($value, \M2E\Otto\Model\Order\Item $row, $column, $isExport)
    {
        $countItem = $row->getData('total_qty');
        $rowTotal = (float)$value * $countItem;

        return $this->currency->formatPrice(
            $this->order->getCurrency(),
            $rowTotal
        );
    }

    public function callbackColumnTaxPercent($value, \M2E\Otto\Model\Order\Item $row, $column, $isExport)
    {
        $taxDetails = $row->getTaxDetails();
        if ($taxDetails === []) {
            return '0%';
        }
        $rate = $taxDetails['rate'];

        return sprintf('%s%%', $rate);
    }

    public function callbackColumnDiscounts($value, \M2E\Otto\Model\Order\Item $row, $column, $isExport)
    {
        $platformDiscount = $row->getPlatformDiscount();
        $sellerDiscount = $row->getSellerDiscount();
        $orderCurrency = $this->order->getCurrency();

        if ($platformDiscount === 0.0 && $sellerDiscount === 0.0) {
            return $this->currency->formatPrice($orderCurrency, 0);
        }

        $text = '';
        if ($platformDiscount > 0) {
            $text .= sprintf(
                '<p>%s: <span>%s</span></p>',
                __('Platform'),
                $this->currency->formatPrice($orderCurrency, $platformDiscount)
            );
        }

        if ($sellerDiscount > 0) {
            $text .= sprintf(
                '<p>%s: <span>%s</span></p>',
                __('Seller'),
                $this->currency->formatPrice($orderCurrency, $sellerDiscount)
            );
        }

        return $text;
    }

    public function getRowUrl($item)
    {
        return '';
    }

    public function getGridUrl()
    {
        return $this->getUrl('*/*/orderItemGrid', ['_current' => true]);
    }
}
