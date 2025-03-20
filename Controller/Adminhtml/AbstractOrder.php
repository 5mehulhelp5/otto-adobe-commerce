<?php

declare(strict_types=1);

namespace M2E\Otto\Controller\Adminhtml;

abstract class AbstractOrder extends AbstractMain
{
    protected function _isAllowed()
    {
        return $this->_authorization->isAllowed('M2E_Otto::sales');
    }

    /**
     * @throws \M2E\Otto\Model\Exception\Logic
     */
    protected function getProductOptionsDataFromPost(): array
    {
        $optionsData = $this->getRequest()->getParam('option_id');

        if ($optionsData === null || count($optionsData) == 0) {
            return [];
        }

        foreach ($optionsData as $optionId => $optionData) {
            $optionData = \M2E\Core\Helper\Json::decode($optionData);

            if (!isset($optionData['value_id']) || !isset($optionData['product_ids'])) {
                return [];
            }

            $optionsData[$optionId] = $optionData;
        }

        return $optionsData;
    }
}
