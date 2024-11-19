<?php

declare(strict_types=1);

namespace M2E\Otto\Model;

use M2E\Otto\Model\Listing;

interface ProductInterface
{
    public function getListing(): Listing;

    public function getSellingFormatTemplate(): \M2E\Otto\Model\Template\SellingFormat;
    public function getMagentoProduct(): \M2E\Otto\Model\Magento\Product\Cache;
}
