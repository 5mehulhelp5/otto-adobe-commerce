<?php

declare(strict_types=1);

namespace M2E\Otto\Controller\Adminhtml\ControlPanel\Module;

use M2E\Otto\Controller\Adminhtml\Context;
use M2E\Otto\Controller\Adminhtml\ControlPanel\AbstractCommand;
use M2E\Otto\Model\Otto\Listing\Product\Action\Type;

class Integration extends AbstractCommand
{
    private \Magento\Framework\Data\Form\FormKey $formKey;
    /** @var \M2E\Otto\Model\Otto\Listing\Product\Action\Type\ListAction\RequestFactory */
    private Type\ListAction\RequestFactory $listRequestFactory;
    /** @var \M2E\Otto\Model\Otto\Listing\Product\Action\Type\Revise\RequestFactory */
    private Type\Revise\RequestFactory $reviseRequestFactory;
    /** @var \M2E\Otto\Model\Otto\Listing\Product\Action\Type\Relist\RequestFactory */
    private Type\Relist\RequestFactory $relistRequestFactory;
    private \M2E\Otto\Model\Product\Repository $productRepository;
    private \M2E\Otto\Model\Product\ActionCalculator $actionCalculator;
    /** @var \M2E\Otto\Model\Otto\Listing\Product\Action\Type\Stop\RequestFactory */
    private Type\Stop\RequestFactory $stopRequestFactory;
    private \M2E\Otto\Model\Otto\Listing\Product\Action\LogBufferFactory $logBufferFactory;

    public function __construct(
        \Magento\Framework\Data\Form\FormKey $formKey,
        \M2E\Otto\Helper\View\ControlPanel $controlPanelHelper,
        \M2E\Otto\Model\Otto\Listing\Product\Action\Type\ListAction\RequestFactory $listRequestFactory,
        \M2E\Otto\Model\Otto\Listing\Product\Action\Type\Revise\RequestFactory $reviseRequestFactory,
        \M2E\Otto\Model\Otto\Listing\Product\Action\Type\Relist\RequestFactory $relistRequestFactory,
        \M2E\Otto\Model\Otto\Listing\Product\Action\Type\Stop\RequestFactory $stopRequestFactory,
        \M2E\Otto\Model\Product\Repository $productRepository,
        \M2E\Otto\Model\Product\ActionCalculator $actionCalculator,
        \M2E\Otto\Model\Otto\Listing\Product\Action\LogBufferFactory $logBufferFactory,
        Context $context
    ) {
        parent::__construct($controlPanelHelper, $context);
        $this->formKey = $formKey;
        $this->listRequestFactory = $listRequestFactory;
        $this->reviseRequestFactory = $reviseRequestFactory;
        $this->relistRequestFactory = $relistRequestFactory;
        $this->productRepository = $productRepository;
        $this->actionCalculator = $actionCalculator;
        $this->stopRequestFactory = $stopRequestFactory;
        $this->logBufferFactory = $logBufferFactory;
    }

    /**
     * @title "Print Request Data"
     * @description "Calculate Allowed Action for Listing Product"
     */
    public function getRequestDataAction()
    {
        $httpRequest = $this->getRequest();

        $listingProductId = $httpRequest->getParam('listing_product_id', null);
        if ($listingProductId !== null) {
            $listingProductId = (int)$listingProductId;
        }

        $form = $this->printFormForCalculateAction($listingProductId);
        $html = "<div style='padding: 20px;background:#d3d3d3;position:sticky;top:0;width:100vw'>$form</div>";

        if ($httpRequest->getParam('print')) {
            try {
                $listingProduct = $this->productRepository->get((int)$listingProductId);
                $action = $this->actionCalculator->calculate(
                    $listingProduct,
                    true,
                    \M2E\Otto\Model\Product::STATUS_CHANGER_USER,
                );

                $html .= '<div>' . $this->printProductInfo($listingProduct, $action) . '</div>';
            } catch (\Throwable $exception) {
                $html .= sprintf(
                    '<div style="margin: 20px 0">%s</div>',
                    $exception->getMessage()
                );
            }
        }

        return $html;
    }

    private function printFormForCalculateAction(?int $listingProductId): string
    {
        $formKey = $this->formKey->getFormKey();
        $actionUrl = $this->getUrl('*/*/*', ['action' => 'getRequestData']);

        return <<<HTML
<form style="margin: 0; font-size: 16px" method="get" enctype="multipart/form-data" action="$actionUrl">
    <input name="form_key" value="$formKey" type="hidden" />
    <input name="print" value="1" type="hidden" />

    <label style="display: inline-block;">
        Listing Product ID:
        <input name="listing_product_id" style="width: 200px;" required value="$listingProductId">
    </label>
    <div style="margin: 10px 0 0 0;">
        <button type="submit">Calculate Allowed Action</button>
    </div>
</form>
HTML;
    }

    private function printProductInfo(
        \M2E\Otto\Model\Product $product,
        \M2E\Otto\Model\Product\Action $action
    ): ?string {
        $calculateAction = 'Nothing';
        if ($action->isActionList()) {
            $calculateAction = 'List';
            $request = $this->listRequestFactory->create();
            $printResult = $this->printRequestData($request, $product, $action->getConfigurator());
        } elseif ($action->isActionRevise()) {
            $calculateAction = sprintf(
                'Revise (Reason (%s))',
                implode(' | ', $action->getConfigurator()->getAllowedDataTypes()),
            );
            $request = $this->reviseRequestFactory->create();
            $printResult = $this->printRequestData($request, $product, $action->getConfigurator());
        } elseif ($action->isActionStop()) {
            $calculateAction = 'Stop';
            $request = $this->stopRequestFactory->create();
            $printResult = $this->printRequestData($request, $product, $action->getConfigurator());
        } elseif ($action->isActionRelist()) {
            $calculateAction = 'Relist';
            $request = $this->relistRequestFactory->create();
            $printResult = $this->printRequestData($request, $product, $action->getConfigurator());
        } else {
            $printResult = 'Nothing action allowed.';
        }
        $currentStatusTitle = \M2E\Otto\Model\Product::getStatusTitle($product->getStatus());

        $productSku = $product->getMagentoProduct()->getSku();

        $listingTitle = $product->getListing()->getTitle();

        return <<<HTML
<style>
    table {
      border-collapse: collapse;
      width: 100%;
    }

    td, th {
      border: 1px solid #dddddd;
      text-align: left;
      padding: 8px;
    }

    tr:nth-child(even) {
      background-color: #f2f2f2;
    }

</style>
<table>
    <tr>
        <td>Listing</td>
        <td>$listingTitle</td>
    </tr>
    <tr>
        <td>Product (SKU)</td>
        <td>$productSku</td>
    </tr>
    <tr>
        <td>Current Product Status</td>
        <td>$currentStatusTitle</td>
    </tr>
    <tr>
        <td>Calculate Action</td>
        <td>$calculateAction</td>
    </tr>
    <tr>
        <td>Request Data</td>
        <td>$printResult</td>
    </tr>
</table>
HTML;
    }

    private function printRequestData(
        \M2E\Otto\Model\Otto\Listing\Product\Action\AbstractRequest $request,
        \M2E\Otto\Model\Product $product,
        \M2E\Otto\Model\Otto\Listing\Product\Action\Configurator $actionConfigurator
    ): string {
        return sprintf(
            '<pre>%s</pre>',
            htmlspecialchars(
                json_encode(
                    $request->build($product, $actionConfigurator, $this->logBufferFactory->create(), [])->toArray(),
                    JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_THROW_ON_ERROR,
                ),
                ENT_QUOTES | ENT_SUBSTITUTE | ENT_HTML401,
            ),
        );
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
