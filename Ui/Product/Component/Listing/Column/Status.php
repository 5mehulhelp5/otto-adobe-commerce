<?php

declare(strict_types=1);

namespace M2E\Otto\Ui\Product\Component\Listing\Column;

use M2E\Otto\Model\Product;

class Status extends \Magento\Ui\Component\Listing\Columns\Column
{
    private \M2E\Otto\Model\Product\Ui\RuntimeStorage $productUiRuntimeStorage;
    private \M2E\Otto\Model\ScheduledAction\Repository $scheduledActionRepository;
    private \M2E\Otto\Model\Product\LockRepository $lockRepository;

    public function __construct(
        \M2E\Otto\Model\Product\Ui\RuntimeStorage $productUiRuntimeStorage,
        \M2E\Otto\Model\ScheduledAction\Repository $scheduledActionRepository,
        \Magento\Framework\View\Element\UiComponent\ContextInterface $context,
        \Magento\Framework\View\Element\UiComponentFactory $uiComponentFactory,
        \M2E\Otto\Model\Product\LockRepository $lockRepository,
        array $components = [],
        array $data = []
    ) {
        parent::__construct($context, $uiComponentFactory, $components, $data);
        $this->lockRepository = $lockRepository;
        $this->productUiRuntimeStorage = $productUiRuntimeStorage;
        $this->scheduledActionRepository = $scheduledActionRepository;
    }

    public function prepareDataSource(array $dataSource): array
    {
        if (empty($dataSource['data']['items'])) {
            return $dataSource;
        }

        foreach ($dataSource['data']['items'] as &$row) {
            $product = $this->productUiRuntimeStorage->findProduct((int)$row['product_id']);
            if (empty($product)) {
                continue;
            }

            $html = '';
            $html .= $this->getCurrentStatus($product);
            $html .= $this->getScheduledTag($product);
            $html .= $this->getProgressTag($product);

            $row['product_status'] = $html;
        }

        return $dataSource;
    }

    private function getCurrentStatus(\M2E\Otto\Model\Product $product): string
    {
        if ($product->isProductIncomplete()) {
            return '<span style="color: orange;">' . Product::getIncompleteStatusTitle() . '</span>';
        }

        if ($product->isStatusNotListed()) {
            return '<span style="color: gray;">' . Product::getStatusTitle(Product::STATUS_NOT_LISTED) . '</span>';
        }

        if ($product->isStatusListed()) {
            return '<span style="color: green;">' . Product::getStatusTitle(Product::STATUS_LISTED) . '</span>';
        }

        if ($product->isStatusInactive()) {
            return '<span style="color: red;">' . Product::getStatusTitle(Product::STATUS_INACTIVE) . '</span>';
        }

        return '';
    }

    private function getScheduledTag(\M2E\Otto\Model\Product $product): string
    {
        $scheduledAction = $this->scheduledActionRepository->findByListingProductId($product->getId());
        if ($scheduledAction === null) {
            return '';
        }

        $html = '';

        switch ($scheduledAction->getActionType()) {
            case \M2E\Otto\Model\Product::ACTION_LIST:
                $html .= '<br/><span style="color: #605fff">[List is Scheduled...]</span>';
                break;

            case \M2E\Otto\Model\Product::ACTION_RELIST:
                $html .= '<br/><span style="color: #605fff">[Relist is Scheduled...]</span>';
                break;

            case \M2E\Otto\Model\Product::ACTION_REVISE:
                $html .= '<br/><span style="color: #605fff">[Revise is Scheduled...]</span>';
                break;

            case \M2E\Otto\Model\Product::ACTION_STOP:
                $html .= '<br/><span style="color: #605fff">[Stop is Scheduled...]</span>';
                break;

            case \M2E\Otto\Model\Product::ACTION_DELETE:
                $html .= '<br/><span style="color: #605fff">[Delete is Scheduled...]</span>';
                break;

            default:
                break;
        }

        return $html;
    }

    private function getProgressTag(\M2E\Otto\Model\Product $product): string
    {
        $html = '';

        $productLock = $this->lockRepository->findByProductId((int)$product->getId());
        if ($productLock === null) {
            return $html;
        }

        switch ($productLock->getInitiator()) {
            case \M2E\Otto\Model\Otto\Listing\Product\Action\Async\DefinitionsCollection::ACTION_LIST:
                $html .= '<br/><span style="color: #605fff">[List is in progress...]</span>';
                break;

            case \M2E\Otto\Model\Otto\Listing\Product\Action\Async\DefinitionsCollection::ACTION_RELIST:
                $html .= '<br/><span style="color: #605fff">[Relist is in progress...]</span>';
                break;

            case \M2E\Otto\Model\Otto\Listing\Product\Action\Async\DefinitionsCollection::ACTION_REVISE:
                $html .= '<br/><span style="color: #605fff">[Revise is in progress...]</span>';
                break;

            case \M2E\Otto\Model\Otto\Listing\Product\Action\Async\DefinitionsCollection::ACTION_STOP:
                $html .= '<br/><span style="color: #605fff">[Stop is in progress...]</span>';
                break;

            case \M2E\Otto\Model\Otto\Listing\Product\Action\Async\DefinitionsCollection::ACTION_DELETE:
                $html .= '<br/><span style="color: #605fff">[Delete is in progress...]</span>';
                break;

            default:
                break;
        }

        return $html;
    }
}
