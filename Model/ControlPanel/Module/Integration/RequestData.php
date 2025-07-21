<?php

declare(strict_types=1);

namespace M2E\Otto\Model\ControlPanel\Module\Integration;

use M2E\Otto\Model\Otto\Listing\Product\Action;

class RequestData
{
    private const PARAM_PRODUCT_MAGENTO_SKU = 'listing_product_magento_sku';
    private const PARAM_CALCULATOR_ACTION = 'calculator_action';
    private const PARAM_PRINT = 'print';

    private \M2E\Otto\Model\Product\Repository $productRepository;
    private \M2E\Otto\Model\Otto\Listing\Product\Action\Type\ListAction\RequestFactory $listRequestFactory;
    private \M2E\Otto\Model\Otto\Listing\Product\Action\Type\Revise\RequestFactory $reviseRequestFactory;
    private \M2E\Otto\Model\Otto\Listing\Product\Action\Type\Relist\RequestFactory $relistRequestFactory;
    private \M2E\Otto\Model\Otto\Listing\Product\Action\Type\Stop\RequestFactory $stopRequestFactory;
    private \M2E\Otto\Model\Product\ActionCalculator $actionCalculator;
    private \Magento\Framework\Data\Form\FormKey $formKey;
    private \Magento\Framework\UrlInterface $url;
    private \Magento\Framework\Escaper $escaper;

    public function __construct(
        \M2E\Otto\Model\Product\Repository $productRepository,
        \M2E\Otto\Model\Otto\Listing\Product\Action\Type\ListAction\RequestFactory $listRequestFactory,
        \M2E\Otto\Model\Otto\Listing\Product\Action\Type\Revise\RequestFactory $reviseRequestFactory,
        \M2E\Otto\Model\Otto\Listing\Product\Action\Type\Relist\RequestFactory $relistRequestFactory,
        \M2E\Otto\Model\Otto\Listing\Product\Action\Type\Stop\RequestFactory $stopRequestFactory,
        \M2E\Otto\Model\Product\ActionCalculator $actionCalculator,
        \Magento\Framework\Data\Form\FormKey $formKey,
        \Magento\Framework\UrlInterface $url,
        \Magento\Framework\Escaper $escaper
    ) {
        $this->productRepository = $productRepository;
        $this->listRequestFactory = $listRequestFactory;
        $this->reviseRequestFactory = $reviseRequestFactory;
        $this->relistRequestFactory = $relistRequestFactory;
        $this->stopRequestFactory = $stopRequestFactory;
        $this->actionCalculator = $actionCalculator;
        $this->formKey = $formKey;
        $this->url = $url;
        $this->escaper = $escaper;
    }

    public function execute(\Magento\Framework\App\RequestInterface $request): string
    {
        $productMagentoSku = $request->getParam(self::PARAM_PRODUCT_MAGENTO_SKU, '');
        $calculatorAction = $request->getParam(self::PARAM_CALCULATOR_ACTION, 'auto');

        $body = $this->printFormForCalculateAction($productMagentoSku, $calculatorAction);

        if ($request->getParam(self::PARAM_PRINT)) {
            try {
                $listingProducts = $this->productRepository->findProductsByMagentoSku($productMagentoSku);

                foreach ($listingProducts as $listingProduct) {
                    if ($calculatorAction === 'list') {
                        $action = \M2E\Otto\Model\Product\Action::createList(
                            $listingProduct,
                            (new Action\Configurator())->enableAll()
                        );
                    } elseif ($calculatorAction === 'revise') {
                        $action = \M2E\Otto\Model\Product\Action::createRevise(
                            $listingProduct,
                            (new Action\Configurator())->enableAll()
                        );
                    } elseif ($calculatorAction === 'relist') {
                        $action = \M2E\Otto\Model\Product\Action::createRelist(
                            $listingProduct,
                            (new Action\Configurator())->enableAll()
                        );
                    } elseif ($calculatorAction === 'stop') {
                        $action = \M2E\Otto\Model\Product\Action::createStop(
                            $listingProduct,
                        );
                    } else {
                        $action = $this->actionCalculator->calculate(
                            $listingProduct,
                            true,
                            \M2E\Otto\Model\Product::STATUS_CHANGER_USER,
                        );
                    }

                    $body .= '<div>' . $this->printProductInfo($listingProduct, $action) . '</div>';
                }
            } catch (\Throwable $exception) {
                $body .= sprintf(
                    '<div style="margin: 20px 0">%s</div>',
                    $exception->getMessage()
                );
            }
        }

        return $this->renderHtml($body);
    }

    private function printFormForCalculateAction(
        string $productMagentoSku = '',
        string $selectedAction = 'auto'
    ): string {
        $formKey = $this->formKey->getFormKey();
        $actionUrl = $this->url->getUrl('*/*/*', ['action' => 'getRequestData']);

        $actionsList = [
            ['value' => 'auto', 'label' => 'Auto'],
            ['value' => 'list', 'label' => 'List'],
            ['value' => 'revise', 'label' => 'Revise'],
            ['value' => 'relist', 'label' => 'Relist'],
            ['value' => 'stop', 'label' => 'Stop'],
        ];

        $actionsOptions = '';
        foreach ($actionsList as $action) {
            $actionsOptions .= sprintf(
                '<option value="%s" %s>%s</option>',
                $action['value'],
                $selectedAction === $action['value'] ? 'selected' : '',
                $action['label']
            );
        }

        return <<<HTML
<div class="sticky-form-wrapper">
    <form method="get" enctype="multipart/form-data" action="$actionUrl">
        <input name="form_key" value="$formKey" type="hidden" />
        <input name="print" value="1" type="hidden" />

        <div class="form-row">
            <label for="product_id">Magento Product Sku:</label>
            <input id="product_id" name="listing_product_magento_sku" required value="$productMagentoSku">
        </div>
        <div class="form-row">
            <label for="calculator_action">Action:</label>
            <select id="calculator_action" name="calculator_action">$actionsOptions</select>
        </div>
        <div class="form-row">
            <button class="run" type="submit">Run</button>
        </div>
    </form>
</div>
HTML;
    }

    private function printProductInfo(
        \M2E\Otto\Model\Product $product,
        \M2E\Otto\Model\Product\Action $action
    ): ?string {
        $request = $this->getRequestInstanceByAction($action);
        $calculateAction = $this->getHumanAction($action);

        $requestDataString = 'Nothing action allowed.';
        $requestMetaDataString = 'Nothing action allowed.';

        if ($request !== null) {
            $requestData = $request->build(
                $product,
                $action->getConfigurator(),
                new \M2E\Otto\Model\Otto\Listing\Product\Action\LogBuffer(),
                []
            )->getData();

            $requestDataString = $this->printCodeBlock($requestData);
            $requestMetaDataString = $this->printCodeBlock($request->getMetadata());
        }

        $currentStatusTitle = \M2E\Otto\Model\Product::getStatusTitle($product->getStatus());
        $productSku = $product->getMagentoProduct()->getSku();
        $listingTitle = $product->getListing()->getTitle();

        return <<<HTML
<table>
    <tr><td>Listing</td><td>$listingTitle</td></tr>
    <tr><td>Product (SKU)</td><td>$productSku</td></tr>
    <tr><td>Current Product Status</td><td>$currentStatusTitle</td></tr>
    <tr><td>Calculate Action</td><td>$calculateAction</td></tr>
    <tr><td>Request Data</td><td>$requestDataString</td></tr>
    <tr><td>Request MetaData</td><td>$requestMetaDataString</td></tr>
</table>
HTML;
    }

    private function printCodeBlock(array $data): string
    {
        return sprintf(
            '<pre class="white-space_pre-wrap">%s</pre>',
            $this->escaper->escapeHtml(
                json_encode(
                    $data,
                    JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE | JSON_THROW_ON_ERROR,
                ),
                ENT_QUOTES | ENT_SUBSTITUTE | ENT_HTML401,
            ),
        );
    }

    private function renderHtml(string $body): string
    {
        return <<<HTML
<!DOCTYPE html>
<html lang="en">
  <head>
    <title>Otto Module Tools | Print Request Data</title>
    <style>
    * {
        box-sizing: border-box;
        margin: 0;
        padding: 0;
    }

    .sticky-form-wrapper {
        background: #d3d3d3;
        position: sticky;
        top: 0;
        width: 100%
    }

    form {
        padding: 10px;
        font-size: 16px;
        position: relative
    }

    .form-row:not(:last-child) {
        margin-bottom: 10px
    }

    .form-row label {
        display: inline-block;
        min-width: 100px
    }

    .form-row input, .form-row select {
        min-width: 200px
    }

    button.run {
        padding: 7px 15px; font-weight: 700
    }

    table {
      border-collapse: collapse;
      width: 100%;
    }

    td:first-child {
        width: 200px;
    }

    .white-space_pre-wrap {
        white-space: pre-wrap;
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
  </head>
  <body>$body</body>
</html>
HTML;
    }

    private function getRequestInstanceByAction(
        \M2E\Otto\Model\Product\Action $action
    ): ?\M2E\Otto\Model\Otto\Listing\Product\Action\AbstractRequest {
        if ($action->isActionList()) {
            return $this->listRequestFactory->create();
        }

        if ($action->isActionRevise()) {
            return $this->reviseRequestFactory->create();
        }

        if ($action->isActionStop()) {
            return $this->stopRequestFactory->create();
        }

        if ($action->isActionRelist()) {
            return $this->relistRequestFactory->create();
        }

        return null;
    }

    private function getHumanAction(\M2E\Otto\Model\Product\Action $action): string
    {
        if ($action->isActionList()) {
            return 'List';
        }

        if ($action->isActionRevise()) {
            return sprintf(
                'Revise (Reason (%s))',
                implode(' | ', $action->getConfigurator()->getAllowedDataTypes()),
            );
        }

        if ($action->isActionStop()) {
            return 'Stop';
        }

        if ($action->isActionRelist()) {
            return 'Relist';
        }

        return 'Nothing action allowed.';
    }
}
