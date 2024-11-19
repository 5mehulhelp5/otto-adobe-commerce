<?php

namespace M2E\Otto\Controller\Adminhtml\Otto\Template;

use M2E\Otto\Controller\Adminhtml\Otto\AbstractTemplate;

abstract class AbstractDescription extends AbstractTemplate
{
    protected \Magento\Framework\HTTP\PhpEnvironment\Request $phpEnvironmentRequest;

    protected \Magento\Catalog\Model\Product $productModel;

    public function __construct(
        \Magento\Framework\HTTP\PhpEnvironment\Request $phpEnvironmentRequest,
        \Magento\Catalog\Model\Product $productModel,
        \M2E\Otto\Model\Otto\Template\Manager $templateManager
    ) {
        parent::__construct($templateManager);
        $this->phpEnvironmentRequest = $phpEnvironmentRequest;
        $this->productModel = $productModel;
    }

    protected function isMagentoProductExists($id)
    {
        $productCollection = $this->productModel
            ->getCollection()
            ->addIdFilter($id);

        return (bool)$productCollection->getSize();
    }
}
