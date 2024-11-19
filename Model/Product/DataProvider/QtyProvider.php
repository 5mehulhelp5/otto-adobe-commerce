<?php

declare(strict_types=1);

namespace M2E\Otto\Model\Product\DataProvider;

use M2E\Otto\Model\Magento\Product as MagentoProduct;

class QtyProvider implements DataBuilderInterface
{
    use DataBuilderHelpTrait;

    public const NICK = 'Qty';

    private \M2E\Otto\Model\Product\QtyCalculatorFactory $qtyCalculatorFactory;

    public function __construct(\M2E\Otto\Model\Product\QtyCalculatorFactory $qtyCalculatorFactory)
    {
        $this->qtyCalculatorFactory = $qtyCalculatorFactory;
    }

    public function getQty(\M2E\Otto\Model\Product $product): int
    {
        $calculator = $this->qtyCalculatorFactory->create($product);
        $calculator->setIsMagentoMode(false);

        $qty = (int)$calculator->getProductValue();

        $this->checkQtyWarnings($product);

        return $qty;
    }

    protected function checkQtyWarnings(\M2E\Otto\Model\Product $product): void
    {
        $qtyMode = $product->getSellingFormatTemplate()->getQtyMode();
        if (
            $qtyMode === \M2E\Otto\Model\Template\SellingFormat::QTY_MODE_PRODUCT_FIXED
            || $qtyMode === \M2E\Otto\Model\Template\SellingFormat::QTY_MODE_PRODUCT
        ) {
            $listingProductId = $product->getId();
            $productId = $product->getMagentoProductId();
            $storeId = $product->getListing()->getStoreId();

            if (!empty(MagentoProduct::$statistics[$listingProductId][$productId][$storeId]['qty'])) {
                $qtys = MagentoProduct::$statistics[$listingProductId][$productId][$storeId]['qty'];
                foreach ($qtys as $type => $override) {
                    $this->addQtyWarnings((int)$type);
                }
            }
        }
    }

    protected function addQtyWarnings(int $type): void
    {
        if ($type === MagentoProduct::FORCING_QTY_TYPE_MANAGE_STOCK_NO) {
            $this->addWarningMessage(
                'During the Quantity Calculation the Settings in the "Manage Stock No" ' .
                'field were taken into consideration.'
            );
        }

        if ($type === MagentoProduct::FORCING_QTY_TYPE_BACKORDERS) {
            $this->addWarningMessage(
                'During the Quantity Calculation the Settings in the "Backorders" ' .
                'field were taken into consideration.'
            );
        }
    }
}
