<?php

declare(strict_types=1);

namespace M2E\Otto\Controller\Adminhtml\ControlPanel\Module;

use M2E\Otto\Controller\Adminhtml\Context;
use M2E\Otto\Controller\Adminhtml\ControlPanel\AbstractCommand;

class Integration extends AbstractCommand
{
    private \Magento\Framework\Data\Form\FormKey $formKey;
    private \M2E\Otto\Model\ControlPanel\Module\Integration\RequestData $requestData;

    public function __construct(
        \Magento\Framework\Data\Form\FormKey $formKey,
        \M2E\Otto\Helper\View\ControlPanel $controlPanelHelper,
        \M2E\Otto\Model\ControlPanel\Module\Integration\RequestData $requestData,
        Context $context
    ) {
        parent::__construct($controlPanelHelper, $context);

        $this->formKey = $formKey;
        $this->requestData = $requestData;
    }

    /**
     * @title "Print Request Data"
     * @description "Calculate Allowed Action for Listing Product"
     */
    public function getRequestDataAction()
    {
        return $this->requestData->execute($this->getRequest());
    }

    /**
     * @title "Build Order Quote"
     * @description "Print Order Quote Data"
     * @throws \Magento\Framework\Exception\LocalizedException
     * @throws \M2E\Otto\Model\Exception
     * @throws \Exception
     */
    public function getPrintOrderQuoteDataAction()
    {
        $isPrint = (bool)$this->getRequest()->getParam('print');
        $orderId = $this->getRequest()->getParam('order_id');

        $buildResultHtml = '';
        if ($isPrint && $orderId) {
            $orderResource = $this->_objectManager->create(\M2E\Otto\Model\ResourceModel\Order::class);
            $order = $this->_objectManager->create(\M2E\Otto\Model\Order::class);

            $orderResource->load($order, (int)$orderId);

            if (!$order->getId()) {
                $this->getMessageManager()->addErrorMessage('Unable to load order instance.');

                return $this->_redirect($this->controlPanelHelper->getPageModuleTabUrl());
            }

            // Store must be initialized before products
            // ---------------------------------------
            $order->associateWithStore();
            $order->associateItemsWithProducts();
            // ---------------------------------------

            $proxy = $order->getProxy()->setStore($order->getStore());

            $magentoQuoteBuilder = $this
                ->_objectManager
                ->create(\M2E\Otto\Model\Magento\Quote\Builder::class, ['proxyOrder' => $proxy]);

            $magentoQuoteManager = $this
                ->_objectManager
                ->create(\M2E\Otto\Model\Magento\Quote\Manager::class);

            $quote = $magentoQuoteBuilder->build();

            $shippingAddressData = $quote->getShippingAddress()->getData();
            unset(
                $shippingAddressData['cached_items_all'],
                $shippingAddressData['cached_items_nominal'],
                $shippingAddressData['cached_items_nonnominal'],
            );
            $billingAddressData = $quote->getBillingAddress()->getData();
            unset(
                $billingAddressData['cached_items_all'],
                $billingAddressData['cached_items_nominal'],
                $billingAddressData['cached_items_nonnominal'],
            );
            $quoteData = $quote->getData();
            unset(
                $quoteData['items'],
                $quoteData['extension_attributes'],
            );

            $items = [];
            foreach ($quote->getAllItems() as $item) {
                $items[] = $item->getData();
            }

            $magentoQuoteManager->save($quote->setIsActive(false));

            $buildResultHtml = json_encode(
                json_decode(
                    json_encode([
                        'Grand Total' => $quote->getGrandTotal(),
                        'Shipping Amount' => $quote->getShippingAddress()->getShippingAmount(),
                        'Quote Data' => $quoteData,
                        'Shipping Address Data' => $shippingAddressData,
                        'Billing Address Data' => $billingAddressData,
                        'Items' => $items,
                    ]),
                    true,
                ),
                JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE,
            );
        }

        $formKey = $this->formKey->getFormKey();
        $actionUrl = $this->getUrl('*/*/*', ['action' => 'getPrintOrderQuoteData']);

        $formHtml = <<<HTML
<form method="get" enctype="multipart/form-data" action="$actionUrl">
    <input name="form_key" value="{$formKey}" type="hidden" />
    <input name="print" value="1" type="hidden" />
    <div>
        <label>Order ID: </label>
        <input name="order_id" value="$orderId" required>
        <button type="submit">Build</button>
    </div>
</form>
HTML;
        $resultHtml = $formHtml;
        if ($buildResultHtml !== '') {
            $resultHtml .= "<h3>Result</h3><div><pre>$buildResultHtml</pre></div>";
        }

        return $resultHtml;
    }

    /**
     * @title "Print Inspector Data"
     * @description "Print Inspector Data"
     * @new_line
     */
    public function getInspectorDataAction()
    {
        if ($this->getRequest()->getParam('print')) {
            $listingProductId = (int)$this->getRequest()->getParam('listing_product_id');

            $listingProduct = $this
                ->_objectManager
                ->create(\M2E\Otto\Model\Product\Repository::class)
                ->get($listingProductId);

            $instructionCollection = $this
                ->_objectManager
                ->create(\M2E\Otto\Model\ResourceModel\Instruction\CollectionFactory::class)
                ->create();

            $instructionCollection->addFieldToFilter('listing_product_id', $listingProductId);

            $instructions = [];
            foreach ($instructionCollection->getItems() as $instruction) {
                $instruction->setListingProduct($listingProduct);
                $instructions[$instruction->getId()] = $instruction;
            }

            $checkerInput = $this
                ->_objectManager
                ->create(\M2E\Otto\Model\Instruction\SynchronizationTemplate\Checker\InputFactory::class)
                ->create($listingProduct, $instructions);

            $html = '<pre>';

            $notListedChecker = $this
                ->_objectManager
                ->create(\M2E\Otto\Model\Instruction\SynchronizationTemplate\Checker\CheckerFactory::class)
                ->create(
                    \M2E\Otto\Model\Instruction\SynchronizationTemplate\Checker\NotListedChecker::class,
                    $checkerInput,
                );

            $html .= '<b>NotListed</b><br>';
            $html .= 'isAllowed: ' . json_encode($notListedChecker->isAllowed()) . '<br>';

            $inactiveChecker = $this
                ->_objectManager
                ->create(\M2E\Otto\Model\Instruction\SynchronizationTemplate\Checker\CheckerFactory::class)
                ->create(
                    \M2E\Otto\Model\Instruction\SynchronizationTemplate\Checker\InactiveChecker::class,
                    $checkerInput,
                );

            $html .= '<b>Inactive</b><br>';
            $html .= 'isAllowed: ' . json_encode($inactiveChecker->isAllowed()) . '<br>';

            $activeChecker = $this
                ->_objectManager
                ->create(\M2E\Otto\Model\Instruction\SynchronizationTemplate\Checker\CheckerFactory::class)
                ->create(
                    \M2E\Otto\Model\Instruction\SynchronizationTemplate\Checker\ActiveChecker::class,
                    $checkerInput,
                );

            $html .= '<b>Active</b><br>';
            $html .= 'isAllowed: ' . json_encode($activeChecker->isAllowed()) . '<br>';

            $magentoProduct = $listingProduct->getMagentoProduct();
            $html .= 'isStatusEnabled: ' . json_encode($magentoProduct->isStatusEnabled()) . '<br>';
            $html .= 'isStockAvailability: ' . json_encode($magentoProduct->isStockAvailability()) . '<br>';

            //--

            return $this->getResponse()->setBody($html);
        }

        $formKey = $this->formKey->getFormKey();
        $actionUrl = $this->getUrl('*/*/*', ['action' => 'getInspectorData']);

        return <<<HTML
<form method="get" enctype="multipart/form-data" action="{$actionUrl}">

    <div style="margin: 5px 0; width: 400px;">
        <label style="width: 170px; display: inline-block;">Listing Product ID: </label>
        <input name="listing_product_id" style="width: 200px;" required>
    </div>

    <input name="form_key" value="{$formKey}" type="hidden" />
    <input name="print" value="1" type="hidden" />

    <div style="margin: 10px 0; width: 365px; text-align: right;">
        <button type="submit">Show</button>
    </div>

</form>
HTML;
    }
}
